<?php

namespace Globby;

use Globby\Compiler\Globby;
use Globby\Tokenizer;

class GlobbyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Globby
     */
    protected $compiler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Tokenizer
     */
    protected $tokenizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Builder
     */
    protected $builder;

    protected function setUp()
    {
        $this->tokenizer = $this->getMock(Tokenizer::CLASS);
        $this->builder = $this->getMock(Builder::CLASS);

        $this->compiler = new Globby(
            $this->tokenizer,
            $this->builder
        );
    }

    public function testCompile()
    {
        $expected = '#foo.*bar#u';

        $patternValue = 'foo*bar';

        $patternTokens = [
            [Tokenizer::T_WORD, 1, 'foo'],
            [Tokenizer::T_WILDCARD_MULTI, 1, '*'],
            [Tokenizer::T_WORD, 1, 'bar'],
        ];

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