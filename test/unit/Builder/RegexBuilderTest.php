<?php

namespace Globby\Builder;

use Globby\Tokenizer\Tokenizer;

/**
 * @package Globby\Builder
 */
class RegexBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegexBuilder
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new RegexBuilder('u', '#');
    }

    public function tokenDataProvider()
    {
        return array(
            array(
                array(Tokenizer::T_WORD, 0, 'mock'),
                'mock'
            ),
            array(
                array(Tokenizer::T_WORD, 0, 'm\\ock*foo\[bar.\\\\test'),
                'm\\\\ock\*foo\[bar\.\\\\test'
            ),
            array(
                array(Tokenizer::T_GROUP_BEGIN, 0, '['),
                '['
            ),
            array(
                array(Tokenizer::T_GROUP_BEGIN_NEGATED, 0, '[!'),
                '[^'
            ),
            array(
                array(Tokenizer::T_GROUP_CHARACTER, 0, 'a'),
                'a'
            ),
            array(
                array(Tokenizer::T_GROUP_RANGE, 0, 'a-z'),
                'a-z'
            ),
            array(
                array(Tokenizer::T_GROUP_CHARACTER_CLASS, 0, '[:alpha:]'),
                '[:alpha:]'
            ),
            array(
                array(Tokenizer::T_GROUP_END, 0, ']'),
                ']'
            ),
            array(
                array(Tokenizer::T_WILDCARD_MULTI, 0, '*'),
                '.*'
            ),
            array(
                array(Tokenizer::T_WILDCARD_SINGLE, 0, '?'),
                '.'
            )
        );
    }

    /**
     * @param array $token
     * @param string $expected
     * @dataProvider tokenDataProvider
     */
    public function testCreateFromTokens(array $token, $expected)
    {
        $expected = '#^' . $expected . '$#u';
        $regex = $this->builder->createFromTokens(array($token));
        $this->assertEquals($expected, $regex);
    }

    /**
     * A more complex example, with multiple tokens being supplied to the builder.
     */
    public function testCreateFromTokens_Multiple()
    {
        $tokens = array(
            array(Tokenizer::T_WORD, 0, 'm*ock.foo\[bar'),
            array(Tokenizer::T_GROUP_BEGIN, 0, '['),
            array(Tokenizer::T_GROUP_CHARACTER, 0, '1'),
            array(Tokenizer::T_GROUP_RANGE, 0, 'a-z'),
            array(Tokenizer::T_GROUP_END, 0, ']'),
            array(Tokenizer::T_GROUP_BEGIN_NEGATED, 0, '[!'),
            array(Tokenizer::T_GROUP_RANGE, 0, '1-9'),
            array(Tokenizer::T_GROUP_CHARACTER_CLASS, 0, '[:alpha:]'),
            array(Tokenizer::T_GROUP_END, 0, ']'),
            array(Tokenizer::T_WORD, 0, 'foo'),
            array(Tokenizer::T_WILDCARD_MULTI, 0, '*'),
            array(Tokenizer::T_WORD, 0, 'ba'),
            array(Tokenizer::T_WILDCARD_SINGLE, 0, '?')
        );

        $expected = '#^m\*ock\.foo\[bar[1a-z][^1-9[:alpha:]]foo.*ba.$#u';

        $regex = $this->builder->createFromTokens($tokens);
        $this->assertEquals($expected, $regex);
    }

    public function testCreateFromTokens_InvalidToken()
    {
        $this->setExpectedException('\Globby\Builder\BuildException', 'No available translation for "☃"');

        $token = array(-1, 0, '☃');

        $this->builder->createFromTokens(array($token));
    }
}