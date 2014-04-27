<?php

namespace Globby\Tokenizer;

use Phlexy\Lexer\Stateful;
use Phlexy\Lexer;
use Phlexy\LexerFactory;
use Phlexy\LexingException;

/**
 * Tokenizer implementation that uses lexing to produce an array of token identifiers. This implements a lexer
 * definition for glob(7) style patterns, as (somewhat) documented by the glob(7) man page.
 *
 * Note that currently two aspects remain unimplemented: collating symbols and equivalence class expressions. It is
 * unknown whether these will be implemented as this stage, as they are rather archaic ways of dealing with locale
 * issues. Further information about these constructs is available here: http://stackoverflow.com/a/7175589
 *
 * @link http://man7.org/linux/man-pages/man7/glob.7.html
 * @package Globby\Tokenizer
 */
class GlobTokenizer implements Tokenizer
{
    /**
     * Phlexy lexer factory instance for creating new Lexer instances.
     *
     * @var LexerFactory
     */
    protected $factory;

    /**
     * @param LexerFactory $factory
     */
    public function __construct(LexerFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Parses the pattern to produce an array of token identifiers.
     *
     * @param string $pattern
     * @return array
     * @throws TokenizeException
     */
    public function parse($pattern)
    {
        $lexer = $this->createLexer();

        try {
            $result = $lexer->lex($pattern);
        } catch (LexingException $e) {
            $message = sprintf('Lexing failed with error: %s', $e->getMessage());
            throw new TokenizeException($message, 0, $e);
        }

        // If the lexer still has state, then it reached the end of the pattern prematurely. An example of when this
        // can happen is unclosed character groupings (e.g. "foo[a-z") - lack of closing bracket will mean the lexer
        // does not pop out of the IN_GROUP state.
        if ($lexer->hasPushedStates()) {
            throw new TokenizeException('Premature end of pattern');
        }

        return $result;
    }

    /**
     * Creates a new stateful lexer instance based on the internal lexer definition.
     *
     * @return Stateful
     */
    protected function createLexer()
    {
        return $this->factory
            ->createLexer($this->createDefinition());
    }

    /**
     * Returns a lexer definition for glob-style wildcard patterns. There are 3 possible states, all documented
     * in the appropriate methods that produce definitions for them.
     *
     * @return array
     */
    protected function createDefinition()
    {
        return array(
            'INITIAL' => $this->createInitialDefinition(),
            'IN_GROUP' => $this->createInGroupDefinition(),
            'IN_GROUP_SPECIAL_FIRST' => $this->createInGroupSpecialFirstDefinition()
        );
    }

    /**
     * Initial state. This is the default state when the lexer enters the pattern.
     *
     * @return array
     */
    protected function createInitialDefinition()
    {
        return array(
            /*
             * Match against non-special characters. To explain the components of this:
             *
             * [^\\\\*\[?]+(?:\\\\.[^\\\\*\[?]*)*
             *  - This matches non-special (\*[?) characters. If it matches one, it'll permit it to be an escape
             *    (i.e. \) if a character exists after it (which may be "special"). This then returns to the previous
             *    matching pattern, repeating the same rules until a non-escaped "special" character is hit.
             *
             * (?:\\\\.[^\\\\*\[?]*)+
             *  - This immediately looks for escape characters, as the previous component of this regex requires for the
             *    initial character to be non-special. This then returns to the standard rules the previous regex.
             */
            '[^\\\\*\[?]+(?:\\\\.[^\\\\*\[?]*)*|(?:\\\\.[^\\\\*\[?]*)+' => self::T_WORD,
            // Simple match of "*"
            '\*' => self::T_WILDCARD_MULTI,
            // Simple match of "?"
            '\?' => self::T_WILDCARD_SINGLE,
            /*
             * Matches against [. This also optionally matches "!" or "^" as the first character to provide negation
             * context. The positive lookahead provides a means to check for "]" as the first character. This is a
             * slightly nasty hack to deal with a rule that "]" can be permitted as a standard character in a character,
             * grouping so long as it's the first character. Once we move into the IN_GROUP state we have no such
             * context, so it must be checked at this level. To achieve this we lookahead for the character, and ensure
             * it's captured by adding a capturing group inside (regex lookarounds do not capture).
             */
            '\[([!^]?)(?=(\])?)' => function (Stateful $lexer, $matches) {
                // Determine the state to enter (depending on whether the lookahead succeeded, i.e. "]" is the first
                // character of the grouping)
                $state = isset($matches[2])
                    ? 'IN_GROUP_SPECIAL_FIRST'
                    : 'IN_GROUP';

                $lexer->pushState($state);

                // Negated grouping if the first character is "!"
                return $matches[1] !== ''
                    ? self::T_GROUP_BEGIN_NEGATED
                    : self::T_GROUP_BEGIN;
            }
        );
    }

    /**
     * In group state. This looks for standard characters, ranges, and POSIX character classes.
     *
     * @return array
     */
    protected function createInGroupDefinition()
    {
        return array(
            // Close of a grouping. Note that we avoid trouble with the "valid" inclusion of this character at the first
            // position by consuming it in the IN_GROUP_SPECIAL_FIRST state. Therefore, any further occurrences are
            // indeed valid closing of the current character grouping.
            '\]' => function (Stateful $lexer) {
                $lexer->popState();
                return self::T_GROUP_END;
            },
            // A range is CHAR1-CHAR2, where CHAR2 is not the closing of a group ("-" is permitted as the first or
            // last character of a character grouping.
            '.-[^\]]' => self::T_GROUP_RANGE,
            '\[:[a-z]+:\]' => self::T_GROUP_CHARACTER_CLASS,
            '.' => self::T_GROUP_CHARACTER
        );
    }

    /**
     * A state to (nastily) deal with the special-case of "]" being the first character in a grouping. When "]" is the
     * first character of a grouping, it does not mean "close this grouping" as usual, but is to be treated as a normal
     * character.
     *
     * @return array
     */
    protected function createInGroupSpecialFirstDefinition()
    {
        return array(
            // Handles cases where the range start character is a "]".
            '\]-[^\]]' => function (Stateful $lexer) {
                $lexer->swapState('IN_GROUP');
                return self::T_GROUP_RANGE;
            },
            // Consumes the single character as a T_GROUP_CHARACTER, and swaps back into the normal IN_GROUP state
            '\]' => function (Stateful $lexer) {
                $lexer->swapState('IN_GROUP');
                return self::T_GROUP_CHARACTER;
            }
        );
    }
}