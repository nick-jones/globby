# Globby

[![Build Status](https://travis-ci.org/nick-jones/Globby.svg?branch=master)](https://travis-ci.org/nick-jones/Globby) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nick-jones/Globby/badges/quality-score.png?s=fa89b9575c50a9e334a5894b425bdc12c85fa454)](https://scrutinizer-ci.com/g/nick-jones/Globby/) [![Code Coverage](https://scrutinizer-ci.com/g/nick-jones/Globby/badges/coverage.png?s=209893a51f5aa1747eb24265d796405e8f48903d)](https://scrutinizer-ci.com/g/nick-jones/Globby/) [![HHVM Status](http://hhvm.h4cc.de/badge/nick-jones/globby.svg)](http://hhvm.h4cc.de/package/nick-jones/globby)

Globby is a glob wildcard → regular expression translation library.

Before you endeavour on using this, do note that this is probably *not* what you want. Glob wildcard patterns
are extremely close to regex patterns; as such, if you wish to use pattern matching in your application, you are far
better off using [`preg_match()`](http://php.net/preg_match). Also note that if you wish to pattern match file paths,
then do of course use [`glob()`](http://php.net/glob), [`GlobIterator`](http://php.net/globiterator), or
[`fnmatch()`](http://php.net/fnmatch).

If you do have one of the limited set of use-cases this library can cater for, then please continue reading..

## About

Globby is able to compile glob wildcard patterns into regular expressions. The following features are supported:

* Multi-character wildcard (`*`)
* Single-character wildcard (`?`)
* Character groups/classes (`[abc]`)
* Negated character groups/classes (`[!abc]`, `[^abc]`)
* Character ranges (`a-z`, `0-9`, etc)
* POSIX character classes (`[:alpha:]`, `[:digit:]`, etc)
* Escape character (`\`)

It lacks support for collating symbols (e.g. `[.ch.]`) and equivalence class expressions (e.g. `[=a=]`).

Unlike many glob pattern → regex translation solutions, Globby does not perform naïve replacements on the pattern. The
translation process involves lexing the supplied pattern with [Phlexy](https://github.com/nikic/Phlexy), and then
building a regular expression based on the token output.

## Warnings

The glob pattern → regex translation process is slow, and intensive. So it's worth reminding at this stage, you probably
want [`preg_match()`](http://php.net/preg_match) instead.

The compile stage only occurs once for any given instance, so you perhaps need not worry so much for long running
applications working with a fixed set of patterns; the initial compiles will be intensive, but further matches will
utilise the cached regular expression.

If you were to use this in short-running applications (e.g. web applications), then you'd be well advised to wrap or
extend `Pattern` with an implementation that caches the glob pattern → regex translations, such as to avoid the compile
step on every relevant request (this is assuming the patterns are reasonably fixed.)

## Installation

You can install Globby via [composer](http://getcomposer.org):

`composer require nick-jones/globby`

## Usage

Simply create an instance of `\Globby\Pattern`, supplying the pattern in the constructor. The `toRegex()` method
will give you the regular expression equivalent of the pattern. An example:

```php
$pattern = new \Globby\Pattern('wow\[such\]?pat\*ter[nr][!,]!*wild[[:digit:]]');
var_dump($pattern->toRegex());
// result: string(48) "#^wow\[such\].pat\*ter[nr][^,]\!.*wild[[:digit:]]$#u"
```

For your convenience, the interface also provides a `match($value)` method that plugs the regular expression straight
into preg_match, indicating whether or not the supplied value matches the pattern. An example:

```php
$pattern = new \Globby\Pattern('wow\[such\]?pat\*ter[nr][!,]!*wild[[:digit:]]');
var_dump($pattern->match('wow[such]:pat*tern.!much.wild9'));
// result: bool(true)
```

If the supplied pattern is invalid, you are likely to encounter a `TokenizeException`. This can happen, for example, if
a character grouping remains open:

```php
$pattern = new \Globby\Pattern('[abc'); // should have been closed with an "]"
// result: exception 'Globby\Tokenizer\TokenizeException' with message 'Premature end of pattern'
```

## Tests

The unit and integration tests for Globby are built with PHPUnit. These are located within the [`test/unit/`](test/unit)
and [`test/integration/`](test/integration) directories respectively. These tests are configured by
[`phpunit.xml`](phpunit.xml) within the project root.

PHPUnit is listed as a development dependency for this project; as such, you can simply run `./vendor/bin/phpunit`
to execute the tests.

A simple functional test suite is also provided, refer to [`test/functional/`](test/functional/) for further
information.
