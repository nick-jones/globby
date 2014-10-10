<?php

namespace Globby\Compiler;

use Globby\Builder\Regex;
use Globby\Tokenizer\Glob;
use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;

/**
 * @package Globby\Compiler
 */
class GlobbyIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Globby::compile() with genuine (non-mocked) Tokenizer and Builder instances
     */
    public function testCompile()
    {
        $factory = new UsingCompiledRegex(
            new LexerDataGenerator()
        );

        $tokenizer = new Glob($factory);
        $builder = new Regex();

        $compiler = new Globby($tokenizer, $builder);

        $pattern = 'foo*bar.ba[zr]?[!1[:alpha:]4-59][^x]';
        $expected = '#^foo.*bar\.ba[zr].[^1[:alpha:]4-59][^x]$#u';

        $result = $compiler->compile($pattern);

        $this->assertEquals($expected, $result);
    }
}