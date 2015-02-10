<?php

namespace Globby\Tokenizer;

use Globby\Tokenizer;

use Globby\Tokenizer\TokenizeException;
use Phlexy\Lexer\Stateful as StatefulLexer;
use Phlexy\LexerFactory;
use Phlexy\LexingException;

/**
 * @package Globby\Tokenizer
 */
class GlobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Glob
     */
    protected $tokenizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $lexerFactory;

    protected function setUp()
    {
        $this->lexerFactory = $this->getMock(LexerFactory::CLASS);

        $this->tokenizer = new Glob($this->lexerFactory);
    }

    public function testParse()
    {
        $pattern = '*';
        $expected = [[Tokenizer::T_WILDCARD_MULTI, 1, '*']];

        $lexer = $this->getMock(StatefulLexer::CLASS);

        $lexer->expects($this->once())
            ->method('lex')
            ->with($this->equalTo($pattern))
            ->will($this->returnValue($expected));

        $lexer->expects($this->once())
            ->method('hasPushedStates')
            ->will($this->returnValue(false));

        $this->lexerFactory->expects($this->once())
            ->method('createLexer')
            ->with($this->isType('array'))
            ->will($this->returnValue($lexer));

        $result = $this->tokenizer->parse($pattern);
        $this->assertEquals($expected, $result);
    }

    public function testParseWithLexingErrorThrown()
    {
        $this->setExpectedException(TokenizeException::CLASS, 'Lexing failed with error: invalid character');

        $pattern = '*';

        $lexer = $this->getMock(StatefulLexer::CLASS);

        $lexer->expects($this->once())
            ->method('lex')
            ->will($this->throwException(new LexingException('invalid character')));

        $this->lexerFactory->expects($this->once())
            ->method('createLexer')
            ->will($this->returnValue($lexer));

        $this->tokenizer->parse($pattern);
    }

    public function testParseWithRemainingPushedStates()
    {
        $this->setExpectedException(TokenizeException::CLASS, 'Premature end of pattern');

        $pattern = '[';

        $lexer = $this->getMock(StatefulLexer::CLASS);

        $lexer->expects($this->once())
            ->method('lex')
            ->will($this->returnValue([]));

        $lexer->expects($this->once())
            ->method('hasPushedStates')
            ->will($this->returnValue(true));

        $this->lexerFactory->expects($this->once())
            ->method('createLexer')
            ->will($this->returnValue($lexer));

        $this->tokenizer->parse($pattern);
    }
}