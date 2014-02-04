<?php

namespace Globby\Tokenizer;

/**
 * Classes implementing this interface must provide a means to produce an array of tokens via the parse() method.
 *
 * @package Globby\Tokenizer
 */
interface Tokenizer {
    /**
     * Any contiguous, non-special characters.
     *
     * Examples: "foo", "a", "foo bar", "foo \* bar"
     */
    const T_WORD = 1;

    /**
     * Start of a character grouping
     *
     * Example: "["
     */
    const T_GROUP_BEGIN = 2;

    /**
     * Start of a negated character grouping
     *
     * Example: "[!"
     */
    const T_GROUP_BEGIN_NEGATED = 3;

    /**
     * A character included within the current grouping
     *
     * Example: "a"
     */
    const T_GROUP_CHARACTER = 4;

    /**
     * A range represented in the current grouping
     *
     * Example: "A-Z"
     */
    const T_GROUP_RANGE = 5;

    /**
     * A character class in the current grouping
     *
     * Example: "[:lower:]", "[:alpha:]"
     */
    const T_GROUP_CHARACTER_CLASS = 6;

    /**
     * End of a character grouping
     *
     * Example: "]"
     */
    const T_GROUP_END = 7;

    /**
     * Multi-wildcard character
     *
     * Example: "*"
     */
    const T_WILDCARD_MULTI = 8;

    /**
     * Single-wildcard character
     *
     * Example: "?"
     */
    const T_WILDCARD_SINGLE = 9;

    /**
     * @param string $pattern
     * @return array
     */
    public function parse($pattern);
}