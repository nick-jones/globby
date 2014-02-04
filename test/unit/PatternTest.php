<?php

namespace Globby;

use Globby\Tokenizer\Tokenizer;

class PatternTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Pattern
     */
    protected $pattern;

    /**
     * @var string
     */
    protected $patternValue = 'foo*bar';

    /**
     * @var array
     */
    protected $patternTokens = array(
        array(Tokenizer::T_WORD, 1, 'foo'),
        array(Tokenizer::T_WILDCARD_MULTI, 1, '*'),
        array(Tokenizer::T_WORD, 1, 'bar'),
    );

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Tokenizer\Tokenizer
     */
    protected $tokenizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Builder\Builder
     */
    protected $builder;

    protected function setUp() {
        $this->patternValue = 'foo*bar';
        $this->tokenizer = $this->getMock('\Globby\Tokenizer\Tokenizer');
        $this->builder = $this->getMock('\Globby\Builder\Builder');

        $options = array(
            Pattern::OPTION_LAZY_COMPILE => TRUE
        );

        $this->pattern = new Pattern(
            $this->patternValue,
            $options,
            $this->tokenizer,
            $this->builder
        );
    }

    /**
     * Expecting Tokenizer and Builder calls on construction.
     */
    public function testConstruct_NonLazy() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Tokenizer\Tokenizer $tokenizer */
        $tokenizer = $this->getMock('\Globby\Tokenizer\Tokenizer');

        $tokenizer->expects($this->once())
            ->method('parse')
            ->will($this->returnValue(array()));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Builder\Builder $builder */
        $builder = $this->getMock('\Globby\Builder\Builder');

        $builder->expects($this->once())
            ->method('createFromTokens');

        $options = array(
            Pattern::OPTION_LAZY_COMPILE => FALSE
        );

        new Pattern('x', $options, $tokenizer, $builder);
    }

    public function testGetRegex() {
        $expected = '#foo.*bar#u';

        $this->tokenizer->expects($this->once())
            ->method('parse')
            ->with($this->patternValue)
            ->will($this->returnValue($this->patternTokens));

        $this->builder->expects($this->once())
            ->method('createFromTokens')
            ->with($this->patternTokens)
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->pattern->getRegex());

        // Repeated call should not trigger another compile; the invocation counts enforce this assertion
        $this->assertEquals($expected, $this->pattern->getRegex());
    }

    public function testMatch() {
        $regex = '#^foo.*bar$#u';

        $this->tokenizer->expects($this->once())
            ->method('parse')
            ->will($this->returnValue($this->patternTokens));

        $this->builder->expects($this->once())
            ->method('createFromTokens')
            ->will($this->returnValue($regex));

        $this->assertTrue($this->pattern->match('foo-bar'));
        // Ensure the anchors are in place
        $this->assertFalse($this->pattern->match('-foo-bar'));
        $this->assertFalse($this->pattern->match('foo-bar-'));
    }

    public function testGetPattern() {
        $result = $this->pattern->getPattern();

        $this->assertEquals($this->patternValue, $result);
    }
}