<?php

namespace Globby;

/**
 * @package Globby
 */
class PatternIntegrationTest extends \PHPUnit_Framework_TestCase {

    public function testConstruct_Invalid() {
        $this->setExpectedException('\Globby\Tokenizer\TokenizeException', 'Premature end of pattern');

        new Pattern('fo[o');
    }

    public function testMatch_Positive() {
        $pattern = new Pattern('foo*bar.ba[zr]?');

        $this->assertTrue($pattern->match('foo.bar.bazo'));
        $this->assertTrue($pattern->match('foo bar.barr'));
    }

    public function testMatch_Negative() {
        $pattern = new Pattern('foo*bar.ba[zr]?');

        $this->assertFalse($pattern->match('foo.bar-bazz'));
        $this->assertFalse($pattern->match('foo bar.baaz'));
        $this->assertFalse($pattern->match('foo.bar.baz'));
    }

    public function testToRegex() {
        $expected = '#^foo.*bar\.ba[zr].[^1[:alpha:]4-59][^x]$#u';

        $pattern = new Pattern('foo*bar.ba[zr]?[!1[:alpha:]4-59][^x]');
        $regex = $pattern->toRegex();

        $this->assertEquals($expected, $regex);
    }

    public function testToRegex_CaseInsensitive() {
        $expected = '#^foo.*bar$#ui';

        $pattern = new Pattern('foo*bar', array(Pattern::OPTION_CASE_INSENSITIVE => TRUE));
        $regex = $pattern->toRegex();

        $this->assertEquals($expected, $regex);
    }
}