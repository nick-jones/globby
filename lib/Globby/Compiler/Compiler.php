<?php

namespace Globby\Compiler;

/**
 * The job of a Compiler implementation is to translate a glob pattern expression to a perl compatible regular
 * expression.
 *
 * @package Globby\Compiler
 */
interface Compiler {
    /**
     * @param string $pattern
     * @return string
     */
    public function compile($pattern);
}