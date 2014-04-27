<?php

namespace Globby\Compiler;

use Globby\Builder\RegexBuilder;
use Globby\Tokenizer\GlobTokenizer;
use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;

/**
 * @package Globby\Compiler
 */
class GlobbyCompilerIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests GlobbyCompiler::compile() with genuine (non-mocked) Tokenizer and Builder instances
     */
    public function testCompile()
    {
        $factory = new UsingCompiledRegex(
            new LexerDataGenerator()
        );

        $tokenizer = new GlobTokenizer($factory);
        $builder = new RegexBuilder();

        $compiler = new GlobbyCompiler($tokenizer, $builder);

        $pattern = 'foo*bar.ba[zr]?[!1[:alpha:]4-59][^x]';
        $expected = '#^foo.*bar\.ba[zr].[^1[:alpha:]4-59][^x]$#u';

        $result = $compiler->compile($pattern);

        $this->assertEquals($expected, $result);
    }
}