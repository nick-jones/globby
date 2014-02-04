<?php

namespace Globby;

use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;
use Globby\Builder\Builder;
use Globby\Builder\RegexBuilder;
use Globby\Tokenizer\GlobTokenizer;
use Globby\Tokenizer\Tokenizer;

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
     * Object implementing the Tokenizer interface to perform the tokenize step.
     *
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * Object implementing the Builder interface to perform the regex building step.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Construction of a new Pattern object. The $tokenizer and $builder arguments afford easy dependency injection;
     * typically you will not need to pass anything for these values.
     *
     * @param string $pattern The glob-style pattern to be represented
     * @param array $options
     * @param Tokenizer $tokenizer
     * @param Builder $builder
     */
    public function __construct($pattern, array $options = array(),
                                Tokenizer $tokenizer = NULL, Builder $builder = NULL) {

        $this->pattern = $pattern;
        $this->options = $options + $this->options;
        $this->tokenizer = $tokenizer ?: $this->defaultTokenizer();
        $this->builder = $builder ?: $this->defaultBuilder();

        if (!$this->options[self::OPTION_LAZY_COMPILE]) {
            $this->compile();
        }
    }

    /**
     * Compile step. This utilises the Tokenizer and Builder instances to produce a regular expression from the
     * internal pattern value.
     */
    protected function compile() {
        $tokens = $this->tokenizer
            ->parse($this->pattern);

        $this->regex = $this->builder
            ->createFromTokens($tokens);
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
     * @return string
     */
    public function getPattern() {
        return $this->pattern;
    }

    /**
     * Accessor for the 'regex' property. If it is uninitialised, the compile step is kicked-off.
     *
     * @return string
     */
    public function getRegex() {
        if (!$this->regex) {
            $this->compile();
        }

        return $this->regex;
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