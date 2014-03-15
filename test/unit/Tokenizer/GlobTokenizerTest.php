<?php

namespace Globby\Tokenizer;

/**
 * @package Globby\Tokenizer
 */
class GlobTokenizerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var GlobTokenizer
     */
    protected $tokenizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $lexerFactory;

    protected function setUp() {
        $this->lexerFactory = $this->getMock('\Phlexy\LexerFactory');

        $this->tokenizer = new GlobTokenizer($this->lexerFactory);
    }

    public function testParse() {
        $pattern = '*';
        $expected = array(array(Tokenizer::T_WILDCARD_MULTI, 1, '*'));

        $lexer = $this->getMock('\Phlexy\Lexer\Stateful');

        $lexer->expects($this->once())
            ->method('lex')
            ->with($this->equalTo($pattern))
            ->will($this->returnValue($expected));

        $lexer->expects($this->once())
            ->method('hasPushedStates')
            ->will($this->returnValue(FALSE));

        $this->lexerFactory->expects($this->once())
            ->method('createLexer')
            ->with($this->isType('array'))
            ->will($this->returnValue($lexer));

        $result = $this->tokenizer->parse($pattern);
        $this->assertEquals($expected, $result);
    }

    public function testParse_LexingError() {
        $this->setExpectedException(
            '\Globby\Tokenizer\TokenizeException',
            'Lexing failed with error: invalid character'
        );

        $pattern = '*';

        $lexer = $this->getMock('\Phlexy\Lexer\Stateful');

        $lexer->expects($this->once())
            ->method('lex')
            ->will($this->throwException(new \Phlexy\LexingException('invalid character')));

        $this->lexerFactory->expects($this->once())
            ->method('createLexer')
            ->will($this->returnValue($lexer));

        $this->tokenizer->parse($pattern);
    }

    public function testParse_Incomplete() {
        $this->setExpectedException(
            '\Globby\Tokenizer\TokenizeException',
            'Premature end of pattern'
        );

        $pattern = '[';

        $lexer = $this->getMock('\Phlexy\Lexer\Stateful');

        $lexer->expects($this->once())
            ->method('lex')
            ->will($this->returnValue(array()));

        $lexer->expects($this->once())
            ->method('hasPushedStates')
            ->will($this->returnValue(TRUE));

        $this->lexerFactory->expects($this->once())
            ->method('createLexer')
            ->will($this->returnValue($lexer));

        $this->tokenizer->parse($pattern);
    }
}