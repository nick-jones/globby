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
     * Default modifiers to be added to the constructed regular expression.
     */
    const DEFAULT_MODIFIERS = 'u';

    /**
     * Regex modifiers.
     *
     * @var string
     */
    protected $modifiers;

    /**
     * Regex delimiter. This must be escaped in various values.
     *
     * @var string
     */
    protected $delimiter;

    /**
     * Wildcard token -> regex value mapping.
     *
     * @var array
     */
    protected static $tokenValueMap = array(
        Tokenizer::T_WILDCARD_MULTI => '.*',
        Tokenizer::T_WILDCARD_SINGLE => '.',
        Tokenizer::T_GROUP_BEGIN => '[',
        Tokenizer::T_GROUP_BEGIN_NEGATED => '[^',
        Tokenizer::T_GROUP_END => ']'
    );

    /**
     * Wildcard token -> internal value translation callback mapping.
     *
     * @var array
     */
    protected static $tokenCallbackMap = array(
        Tokenizer::T_WORD => 'translateWordToken',
        Tokenizer::T_GROUP_CHARACTER => 'translateGroupCharacterToken',
        Tokenizer::T_GROUP_RANGE => 'translateGroupRangeToken',
        Tokenizer::T_GROUP_CHARACTER_CLASS => 'translateGroupCharacterClassToken'
    );

    /**
     * @param string $modifiers
     * @param string $delimiter
     */
    public function __construct($modifiers = self::DEFAULT_MODIFIERS, $delimiter = '#') {
        $this->modifiers = $modifiers;
        $this->delimiter = $delimiter;
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
        $result = null;

        if (array_key_exists($identifier, self::$tokenValueMap)) {
            $result = $this->translateTokenUsingMap($identifier);
        }
        else if (array_key_exists($identifier, self::$tokenCallbackMap)) {
            $result = $this->translateTokenUsingCallback($identifier, $value);
        }

        if ($result === NULL) {
            throw new BuildException(sprintf('No available translation for "%s"', $value));
        }

        return $result;
    }

    /**
     * Uses the simple token -> value mapping array.
     *
     * @param int $identifier
     * @return string
     */
    protected function translateTokenUsingMap($identifier) {
        return self::$tokenValueMap[$identifier];
    }

    /**
     * Calls an internal method to retrieve an appropriate value for use within the regex.
     *
     * @param int $identifier
     * @param string $value
     * @return string
     */
    protected function translateTokenUsingCallback($identifier, $value) {
        $method = self::$tokenCallbackMap[$identifier];

        return call_user_func(
            array($this, $method),
            $value
        );
    }

    /**
     * @param string $value
     * @return string
     */
    protected function translateWordToken($value) {
        // Remove now irrelevant escaping to allow us to apply preg_quote
        $value = preg_replace('#\\\\([*\[\]?\\\\])#', '\\1', $value);

        return preg_quote($value, $this->delimiter);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function translateGroupRangeToken($value) {
        // The entire value cannot be escaped, as the range symbol (-) is escaped by preg_quote
        $min = preg_quote($value[0], $this->delimiter);
        $max = preg_quote($value[2], $this->delimiter);

        return $min . '-' . $max;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function translateGroupCharacterToken($value) {
        return preg_quote($value, $this->delimiter);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function translateGroupCharacterClassToken($value) {
        // The value should be verified, implementation to be added.
        return $value;
    }
}