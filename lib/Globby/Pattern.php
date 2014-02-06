<?php

namespace Globby;

use Globby\Compiler\Compiler;
use Globby\Compiler\GlobbyCompiler;
use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;
use Globby\Builder\RegexBuilder;
use Globby\Tokenizer\GlobTokenizer;

/**
 * This class provides a means to match a glob-style pattern against values, and also retrieve a regex equivalent for
 * use in functions such as preg_quote.
 *
 * @package Globby
 */
class Pattern {
    /**
     * This option forces the compile stage to only occur when necessary (i.e. on getRegex(), and not in the
     * class constructor.)
     */
    const OPTION_LAZY_COMPILE = 'lazy_compile';

    /**
     * Pattern value. Given the "compiled" regex is cached, this value should not be changed; at least, not without
     * clearing the regex value first.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The "compiled" regex value. This is cached to avoid repeating the intensive tokenize & building steps on
     * repeated calls to match()/getRegex().
     *
     * @var string
     */
    protected $regex;

    /**
     * @var array
     */
    protected $options = array(
        self::OPTION_LAZY_COMPILE => FALSE
    );

    /**
     * Object implementing the Compiler interface to perform the compile step.
     *
     * @var Compiler
     */
    protected $compiler;

    /**
     * Construction of a new Pattern object. The $compiler argument afford easy dependency injection; typically you
     * will not need to pass anything for this value.
     *
     * @param string $pattern The glob-style pattern to be represented
     * @param array $options A map of OPTION_* constants => value
     * @param Compiler $compiler Compiler instance. If one is not supplied, an instance of GlobbyCompiler will be used
     */
    public function __construct($pattern, array $options = array(), Compiler $compiler = NULL) {
        $this->pattern = $pattern;
        $this->options = $options + $this->options;
        $this->compiler = $compiler ?: $this->defaultCompiler();

        if (!$this->options[self::OPTION_LAZY_COMPILE]) {
            $this->regex = $this->compile();
        }
    }

    /**
     * Compile step. This utilises the internal Compiler instance to translate the pattern to a regular expression.
     *
     * @return string A regular expression constructed from the pattern held by this instance
     */
    protected function compile() {
         $regex = $this->compiler
            ->compile($this->pattern);

         return $regex;
    }

    /**
     * Indicates whether the supplied value matches the pattern represented by this instance.
     *
     * @param string $value The value to be checked against
     * @return bool TRUE if the value matched the pattern, FALSE otherwise
     */
    public function match($value) {
        $result = preg_match(
            $this->getRegex(),
            $value
        );

        return (bool) $result;
    }

    /**
     * Accessor for the 'pattern' property.
     *
     * @return string The pattern value originally supplied to this instance
     */
    public function getPattern() {
        return $this->pattern;
    }

    /**
     * Accessor for the 'regex' property. If it is uninitialised, the compile step is kicked-off.
     *
     * @return string A regular expression equivalent of the pattern value held by this instance
     */
    public function getRegex() {
        if (!$this->regex) {
            $this->regex = $this->compile();
        }

        return $this->regex;
    }

    /**
     * @return GlobbyCompiler
     */
    protected function defaultCompiler() {
        $tokenizer = $this->defaultTokenizer();
        $builder = $this->defaultBuilder();

        return new GlobbyCompiler($tokenizer, $builder);
    }

    /**
     * @return GlobTokenizer
     */
    protected function defaultTokenizer() {
        $factory = new UsingCompiledRegex(
            new LexerDataGenerator()
        );

        return new GlobTokenizer($factory);
    }

    /**
     * @return RegexBuilder
     */
    protected function defaultBuilder() {
        return new RegexBuilder();
    }
}