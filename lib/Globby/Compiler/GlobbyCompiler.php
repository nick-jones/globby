<?php

namespace Globby\Compiler;
use Globby\Builder\Builder;
use Globby\Tokenizer\Tokenizer;

/**
 * Standard compiler for Globby. This utilises Tokenizer and Builder implementations to produce a regular expression.
 *
 * @package Globby\Compiler
 */
class GlobbyCompiler implements Compiler {
    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Tokenizer $tokenizer
     * @param Builder $builder
     */
    public function __construct(Tokenizer $tokenizer, Builder $builder) {
        $this->tokenizer = $tokenizer;
        $this->builder = $builder;
    }

    /**
     * @param string $pattern
     * @return string
     */
    public function compile($pattern) {
        $tokens = $this->tokenizer
            ->parse($pattern);

        $regex = $this->builder
            ->createFromTokens($tokens);

        return $regex;
    }
}