<?php

namespace Globby;

use Globby\Tokenizer\TokenizeException;

/**
 * @package Globby
 */
class PatternIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithInvalidPattern()
    {
        $this->setExpectedException(TokenizeException::CLASS, 'Premature end of pattern');

        new Pattern('fo[o');
    }

    public function testMatchWithMatchingValue()
    {
        $pattern = new Pattern('foo*bar.ba[zr]?');

        $this->assertTrue($pattern->match('foo.bar.bazo'));
        $this->assertTrue($pattern->match('foo bar.barr'));
    }

    public function testMatchWithNonMatchingValue()
    {
        $pattern = new Pattern('foo*bar.ba[zr]?');

        $this->assertFalse($pattern->match('foo.bar-bazz'));
        $this->assertFalse($pattern->match('foo bar.baaz'));
        $this->assertFalse($pattern->match('foo.bar.baz'));
    }

    /**
     * @return array
     */
    public function regexDataProvider()
    {
        return [
            [
                'foo*bar.ba[zr]?[!1[:alpha:]4-59][^x]',
                '#^foo.*bar\.ba[zr].[^1[:alpha:]4-59][^x]$#u',
            ],
            [
                'foo*bar',
                '#^foo.*bar$#ui',
                [Pattern::OPTION_CASE_INSENSITIVE => true]
            ]
        ];
    }

    /**
     * @param string $pattern
     * @param string $expected
     * @param array $options
     * @dataProvider regexDataProvider
     */
    public function testToRegex($pattern, $expected, array $options = [])
    {
        $pattern = new Pattern($pattern, $options);
        $regex = $pattern->toRegex();

        $this->assertEquals($expected, $regex);
    }
}