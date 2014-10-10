<?php

namespace Globby\Tokenizer;

use Globby\Tokenizer;
use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;

/**
 * @package Globby\Tokenizer
 */
class GlobIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The first elements of the arrays are a pattern to be pushed to the tokenizer. The second elements of the
     * arrays are the expected tokens to be produced.
     *
     * @return array
     */
    public function patternTokenProvider()
    {
        return array(
            array(
                'foo\[bar\]',
                array(
                    array(Tokenizer::T_WORD, 1, 'foo\[bar\]')
                )
            ),
            array(
                'foo*bar',
                array(
                    array(Tokenizer::T_WORD, 1, 'foo'),
                    array(Tokenizer::T_WILDCARD_MULTI, 1, '*'),
                    array(Tokenizer::T_WORD, 1, 'bar')
                )
            ),
            array(
                'fo?',
                array(
                    array(Tokenizer::T_WORD, 1, 'fo'),
                    array(Tokenizer::T_WILDCARD_SINGLE, 1, '?')
                )
            ),
            array(
                '[a-z]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_RANGE, 1, 'a-z'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[!a-z]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[!'),
                    array(Tokenizer::T_GROUP_RANGE, 1, 'a-z'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[^a]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[^'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'a'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[ab]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'a'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'b'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[[:alpha:]]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER_CLASS, 1, '[:alpha:]'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[]]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, ']'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[]-_]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_RANGE, 1, ']-_'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[][!]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, ']'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, '!'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                '[!]a-]',
                array(
                    array(Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[!'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, ']'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'a'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, '-'),
                    array(Tokenizer::T_GROUP_END, 1, ']')
                )
            ),
            array(
                'fo*[ob][!y][[:alpha:]]?*',
                array(
                    array(Tokenizer::T_WORD, 1, 'fo'),
                    array(Tokenizer::T_WILDCARD_MULTI, 1, '*'),
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'o'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'b'),
                    array(Tokenizer::T_GROUP_END, 1, ']'),
                    array(Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[!'),
                    array(Tokenizer::T_GROUP_CHARACTER, 1, 'y'),
                    array(Tokenizer::T_GROUP_END, 1, ']'),
                    array(Tokenizer::T_GROUP_BEGIN, 1, '['),
                    array(Tokenizer::T_GROUP_CHARACTER_CLASS, 1, '[:alpha:]'),
                    array(Tokenizer::T_GROUP_END, 1, ']'),
                    array(Tokenizer::T_WILDCARD_SINGLE, 1, '?'),
                    array(Tokenizer::T_WILDCARD_MULTI, 1, '*')
                )
            ),
            array(
                '\*foo',
                array(
                    array(Tokenizer::T_WORD, 1, '\*foo')
                )
            ),
            array(
                'foo\*',
                array(
                    array(Tokenizer::T_WORD, 1, 'foo\*')
                )
            ),
            array(
                '\*foo\*',
                array(
                    array(Tokenizer::T_WORD, 1, '\*foo\*')
                )
            ),
            array(
                '\*',
                array(
                    array(Tokenizer::T_WORD, 1, '\*')
                )
            )
        );
    }

    /**
     * All pattern expressions are converted to tokens using a genuine (i.e. non-mocked) Lexer instance.
     *
     * @dataProvider patternTokenProvider
     */
    public function testParse($pattern, $expected)
    {
        $factory = new UsingCompiledRegex(
            new LexerDataGenerator()
        );

        $tokenizer = new Glob($factory);
        $tokens = $tokenizer->parse($pattern);

        $this->assertEquals($expected, $tokens);
    }
}