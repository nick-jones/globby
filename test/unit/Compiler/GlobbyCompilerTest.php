<?php

namespace Globby;

use Globby\Compiler\GlobbyCompiler;
use Globby\Tokenizer\Tokenizer;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GlobbyCompiler
     */
    protected $compiler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Tokenizer\Tokenizer
     */
    protected $tokenizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Builder\Builder
     */
    protected $builder;

    protected function setUp()
    {
        $this->tokenizer = $this->getMock('\Globby\Tokenizer\Tokenizer');
        $this->builder = $this->getMock('\Globby\Builder\Builder');

        $this->compiler = new GlobbyCompiler(
            $this->tokenizer,
            $this->builder
        );
    }

    public function testCompile()
    {
        $expected = '#foo.*bar#u';

        $patternValue = 'foo*bar';

        $patternTokens = array(
            array(Tokenizer::T_WORD, 1, 'foo'),
            array(Tokenizer::T_WILDCARD_MULTI, 1, '*'),
            array(Tokenizer::T_WORD, 1, 'bar'),
        );

        $this->tokenizer->expects($this->once())
            ->method('parse')
            ->with($patternValue)
            ->will($this->returnValue($patternTokens));

        $this->builder->expects($this->once())
            ->method('createFromTokens')
            ->with($patternTokens)
            ->will($this->returnValue($expected));

        $result = $this->compiler->compile($patternValue);

        $this->assertEquals($expected, $result);
    }
}