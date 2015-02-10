<?php

namespace Globby\Builder;

use Globby\Tokenizer;

/**
 * @package Globby\Builder
 */
class RegexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Regex
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new Regex('u', '#');
    }

    public function tokenDataProvider()
    {
        return [
            [
                [Tokenizer::T_WORD, 0, 'mock'],
                'mock'
            ],
            [
                [Tokenizer::T_WORD, 0, 'm\\ock*foo\[bar.\\\\test'],
                'm\\\\ock\*foo\[bar\.\\\\test'
            ],
            [
                [Tokenizer::T_GROUP_BEGIN, 0, '['],
                '['
            ],
            [
                [Tokenizer::T_GROUP_BEGIN_NEGATED, 0, '[!'],
                '[^'
            ],
            [
                [Tokenizer::T_GROUP_CHARACTER, 0, 'a'],
                'a'
            ],
            [
                [Tokenizer::T_GROUP_RANGE, 0, 'a-z'],
                'a-z'
            ],
            [
                [Tokenizer::T_GROUP_CHARACTER_CLASS, 0, '[:alpha:]'],
                '[:alpha:]'
            ],
            [
                [Tokenizer::T_GROUP_END, 0, ']'],
                ']'
            ],
            [
                [Tokenizer::T_WILDCARD_MULTI, 0, '*'],
                '.*'
            ],
            [
                [Tokenizer::T_WILDCARD_SINGLE, 0, '?'],
                '.'
            ]
        ];
    }

    /**
     * @param array $token
     * @param string $expected
     * @dataProvider tokenDataProvider
     */
    public function testCreateFromTokens(array $token, $expected)
    {
        $expected = '#^' . $expected . '$#u';
        $regex = $this->builder->createFromTokens([$token]);
        $this->assertEquals($expected, $regex);
    }

    /**
     * A more complex example, with multiple tokens being supplied to the builder.
     */
    public function testCreateFromTokensWithMultipleTokens()
    {
        $tokens = [
            [Tokenizer::T_WORD, 0, 'm*ock.foo\[bar'],
            [Tokenizer::T_GROUP_BEGIN, 0, '['],
            [Tokenizer::T_GROUP_CHARACTER, 0, '1'],
            [Tokenizer::T_GROUP_RANGE, 0, 'a-z'],
            [Tokenizer::T_GROUP_END, 0, ']'],
            [Tokenizer::T_GROUP_BEGIN_NEGATED, 0, '[!'],
            [Tokenizer::T_GROUP_RANGE, 0, '1-9'],
            [Tokenizer::T_GROUP_CHARACTER_CLASS, 0, '[:alpha:]'],
            [Tokenizer::T_GROUP_END, 0, ']'],
            [Tokenizer::T_WORD, 0, 'foo'],
            [Tokenizer::T_WILDCARD_MULTI, 0, '*'],
            [Tokenizer::T_WORD, 0, 'ba'],
            [Tokenizer::T_WILDCARD_SINGLE, 0, '?']
        ];

        $expected = '#^m\*ock\.foo\[bar[1a-z][^1-9[:alpha:]]foo.*ba.$#u';

        $regex = $this->builder->createFromTokens($tokens);
        $this->assertEquals($expected, $regex);
    }

    public function testCreateFromTokensWithInvalidToken()
    {
        $this->setExpectedException(BuildException::CLASS, 'No available translation for "☃"');

        $token = [-1, 0, '☃'];

        $this->builder->createFromTokens([$token]);
    }
}