<?php

namespace Globby;

/**
 * Builder implementations should construct "something" (most likely a regex) from an array of tokens.
 *
 * @package Globby
 */
interface Builder
{
    /**
     * Create a (regex) pattern based on an array of tokens.
     *
     * @param array $tokens Tokens to be used for translation
     * @return string Translated pattern
     */
    public function createFromTokens(array $tokens);
}