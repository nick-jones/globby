<?php

namespace Globby\Builder;

use Globby\Tokenizer\Tokenizer;

/**
 * This class consumes an array of pattern tokens and constructs a regular expression. It takes a rather naive approach
 * to this, as it assumes that the tokens are already ordered in a valid fashion; this should not be a problem,
 * however, as the tokenizer is reasonably strict in terms of character handling.
 *
 * @package Globby\Builder
 */
class RegexBuilder implements Builder {
    /**
     * Regex delimiter. This must be escaped in various values.
     *
     * @var string
     */
    protected $delimiter;

    /**
     * Regex modifiers.
     *
     * @var string
     */
    protected $modifiers;

    /**
     * @param string $delimiter
     * @param string $modifiers
     */
    public function __construct($delimiter = '#', $modifiers = 'u') {
        $this->delimiter = $delimiter;
        $this->modifiers = $modifiers;
    }

    /**
     * Creates a regular expression based on the supplied tokens. This return expression will be wrapped by the
     * value of the 'delimiter' property, and the modifiers contained within the 'modifiers' property will be appended
     * to the end.
     *
     * @param array $tokens
     * @return string
     */
    public function createFromTokens(array $tokens) {
        $buffer = $this->delimiter . '^';

        foreach ($tokens as $token) {
            $buffer .= $this->translateToken($token);
        }

        $buffer .= '$' . $this->delimiter . $this->modifiers;

        return $buffer;
    }

    /**
     * Translates a token into an appropriate regular expression construct. Various map directly to the same values;
     * others require some massaging and escaping to make them valid within the constructed in the regular expression.
     *
     * @param array $token
     * @return string
     * @throws BuildException
     */
    protected function translateToken(array $token) {
        $identifier = is_string($token[0]) ? NULL : $token[0];
        $value = $token[2];

        switch ($identifier) {
            case Tokenizer::T_WORD:
                // Remove now irrelevant escaping to allow us to apply preg_quote
                $value = preg_replace('#\\\\([*\[\]?\\\\])#', '\\1', $value);
                return preg_quote($value, $this->delimiter);

            case Tokenizer::T_GROUP_BEGIN_NEGATED:
                return '[^';

            case Tokenizer::T_WILDCARD_MULTI:
                return '.*';

            case Tokenizer::T_WILDCARD_SINGLE:
                return '.';

            case Tokenizer::T_GROUP_CHARACTER:
                return preg_quote($value, $this->delimiter);

            case Tokenizer::T_GROUP_RANGE:
                // The entire value cannot be escaped, as the range symbol (-) is escaped by preg_quote
                $min = preg_quote($value[0], $this->delimiter);
                $max = preg_quote($value[2], $this->delimiter);
                return $min . '-' . $max;

            case Tokenizer::T_GROUP_BEGIN:
            case Tokenizer::T_GROUP_END:
            case Tokenizer::T_GROUP_CHARACTER_CLASS:
                // All of the above are represented by the same characters in glob-style and regex patterns.
                return $value;
        }

        throw new BuildException(sprintf('No available translation for "%s"', $value));
    }
}