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
        return [
            [
                'foo\[bar\]',
                [
                    [Tokenizer::T_WORD, 1, 'foo\[bar\]']
                ]
            ],
            [
                'foo*bar',
                [
                    [Tokenizer::T_WORD, 1, 'foo'],
                    [Tokenizer::T_WILDCARD_MULTI, 1, '*'],
                    [Tokenizer::T_WORD, 1, 'bar']
                ]
            ],
            [
                'fo?',
                [
                    [Tokenizer::T_WORD, 1, 'fo'],
                    [Tokenizer::T_WILDCARD_SINGLE, 1, '?']
                ]
            ],
            [
                '[a-z]',
                [
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_RANGE, 1, 'a-z'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[!a-z]',
                [
                    [Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[!'],
                    [Tokenizer::T_GROUP_RANGE, 1, 'a-z'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[^a]',
                [
                    [Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[^'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'a'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[ab]',
                [
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'a'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'b'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[[:alpha:]]',
                [
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER_CLASS, 1, '[:alpha:]'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[]]',
                [
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER, 1, ']'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[]-_]',
                [
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_RANGE, 1, ']-_'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[][!]',
                [
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER, 1, ']'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER, 1, '!'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                '[!]a-]',
                [
                    [Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[!'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, ']'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'a'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, '-'],
                    [Tokenizer::T_GROUP_END, 1, ']']
                ]
            ],
            [
                'fo*[ob][!y][[:alpha:]]?*',
                [
                    [Tokenizer::T_WORD, 1, 'fo'],
                    [Tokenizer::T_WILDCARD_MULTI, 1, '*'],
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'o'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'b'],
                    [Tokenizer::T_GROUP_END, 1, ']'],
                    [Tokenizer::T_GROUP_BEGIN_NEGATED, 1, '[!'],
                    [Tokenizer::T_GROUP_CHARACTER, 1, 'y'],
                    [Tokenizer::T_GROUP_END, 1, ']'],
                    [Tokenizer::T_GROUP_BEGIN, 1, '['],
                    [Tokenizer::T_GROUP_CHARACTER_CLASS, 1, '[:alpha:]'],
                    [Tokenizer::T_GROUP_END, 1, ']'],
                    [Tokenizer::T_WILDCARD_SINGLE, 1, '?'],
                    [Tokenizer::T_WILDCARD_MULTI, 1, '*']
                ]
            ],
            [
                '\*foo',
                [
                    [Tokenizer::T_WORD, 1, '\*foo']
                ]
            ],
            [
                'foo\*',
                [
                    [Tokenizer::T_WORD, 1, 'foo\*']
                ]
            ],
            [
                '\*foo\*',
                [
                    [Tokenizer::T_WORD, 1, '\*foo\*']
                ]
            ],
            [
                '\*',
                [
                    [Tokenizer::T_WORD, 1, '\*']
                ]
            ]
        ];
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