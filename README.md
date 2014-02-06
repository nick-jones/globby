# Globby

Globby is a PHP wildcard library, providing glob wildcard pattern matching functionality.

Before you endeavour on using this, do note that this is probably *not* what you want. Glob wildcard patterns
are extremely close to regex patterns; as such, if you wish to use pattern matching in your application, you are far
better off using [`preg_match()`](http://php.net/preg_match). Also note that if you wish to pattern match file paths,
then do of course use [`glob()`](http://php.net/glob), [`GlobIterator`](http://php.net/globiterator), or
[`fnmatch()`](http://php.net/fnmatch).

If you do have one of the limited set of use-cases this library can cater for, then please continue reading..

## About

Globby provides almost feature-complete glob wildcard matching functionality. It achieves this by translating the
provided patterns into regular expressions. The following features are supported:

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

You can install Globby via [composer](http://getcomposer.org). Composer sets a `minimum-stability` of "stable" by
default, which is a slight issue for requiring Globby, as it relies on [Phlexy](https://github.com/nikic/Phlexy), which
currently has no stable releases. Here are a couple of composer.json configurations that will allow you to require
Globby without issue:

Explicitly require Phlexy with dev stability:

```json
"require": {
    "nick-jones/globby": "~0.2",
    "nikic/phlexy": "@dev"
}
```

Or lower `minimum-stability`, but `prefer-stable`:

```json
"minimum-stability": "dev",
"prefer-stable": true,

"require": {
    "nick-jones/globby": "~0.2"
}
```

## Usage

Simply create an instance of `\Globby\Pattern`, supplying the pattern in the constructor. The `match($value)` method
indicates whether or not the supplied value matches the pattern. An example:

```php
$pattern = new \Globby\Pattern('wow\[such\]?pat\*ter[nr][!,]!*wild[[:digit:]]');
var_dump($pattern->match('wow[such]:pat*tern.!much.wild9'));
// result: bool(true)
```

The `Pattern` interface also provides a means to fetch the regular expression, `getRegex()`. An example:

```php
$pattern = new \Globby\Pattern('wow\[such\]?pat\*ter[nr][!,]!*wild[[:digit:]]');
var_dump($pattern->getRegex());
// result: string(48) "#^wow\[such\].pat\*ter[nr][^,]\!.*wild[[:digit:]]$#u"
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