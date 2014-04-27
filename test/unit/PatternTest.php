<?php

namespace Globby;

use Globby\Tokenizer\Tokenizer;

class PatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pattern
     */
    protected $pattern;

    /**
     * @var string
     */
    protected $patternValue = 'foo*bar';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Globby\Compiler\Compiler
     */
    protected $compiler;

    protected function setUp()
    {
        $this->compiler = $this->getMock('\Globby\Compiler\Compiler');

        $options = array(
            Pattern::OPTION_LAZY_COMPILE => true
        );

        $this->pattern = new Pattern(
            $this->patternValue,
            $options,
            $this->compiler
        );
    }

    /**
     * Expecting Tokenizer and Builder calls on construction.
     */
    public function testConstruct_NonLazy()
    {
        $this->compiler->expects($this->once())
            ->method('compile')
            ->will($this->returnValue('#^x$#u'));

        $options = array(
            Pattern::OPTION_LAZY_COMPILE => false
        );

        new Pattern('x', $options, $this->compiler);
    }

    public function testToRegex()
    {
        $expected = '#foo.*bar#u';

        $this->compiler->expects($this->once())
            ->method('compile')
            ->with($this->patternValue)
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->pattern->toRegex());

        // Repeated call should not trigger another compile; the invocation counts enforce this assertion
        $this->assertEquals($expected, $this->pattern->toRegex());
    }

    public function testMatch()
    {
        $regex = '#^foo.*bar$#u';

        $this->compiler->expects($this->once())
            ->method('compile')
            ->with($this->patternValue)
            ->will($this->returnValue($regex));

        $this->assertTrue($this->pattern->match('foo-bar'));
        $this->assertFalse($this->pattern->match('-foo-bar'));
        $this->assertFalse($this->pattern->match('foo-bar-'));
    }

    public function testGetPattern()
    {
        $result = $this->pattern->getPattern();

        $this->assertEquals($this->patternValue, $result);
    }
}