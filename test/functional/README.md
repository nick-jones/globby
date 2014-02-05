# Functional tests

The [`test-patterns.php`](test-patterns.php) script provides a means to test wildcard patterns as described in
supplied input files (to be provided as arguments to the script). Example usage:

`php test-patterns.php patterns_general.json patters_glob_man_page.json`

If no arguments are supplied, it will test all `patterns_*.json` files in this directory.

`php test-patterns.php`

The script iterates over the patterns contained within the files, and validates all associated test cases. It will exit
with a non-zero status on failure.

## Pattern test file structure

The supplied files should contain JSON, and be structured as follows:

```
{
    "$pattern": [
        ["$positive_value", "$positive_value_2", ...],
        ["$negative_value", "$negative_value_2", ...]
    ],
    ...
}
```

The `$pattern` value should contain the glob wildcard pattern to be verified. The positive array should contain values
that are expected to match that pattern. The negative array should contain values that do not.

## Files

Brief descriptions of the bundled pattern tests:

* [`patterns_general.json`](patterns_general.json) - this contains hand-written test patterns of varying degrees of
complexity
* [`patterns_glob_man_page.json`](patterns_glob_man_page.json) - this contains various complex pattern examples as
presented in the [glob(7) man page](http://man7.org/linux/man-pages/man7/glob.7.html)

## Testing your own implementation

Should you wish to use this script to validate your own glob-style pattern matching implementation, then please follow
the following steps:

* Create a PHP file somewhere, and define a function "wildcard_match". The function signature must be:
`wildcard_match($pattern, $value)`, and it must return a boolean.
* Require/include the `test-patterns.php` contained within this directory (it must be *after* defining "wildcard_match")
* Run the PHP script

Example:

```php
<?php

function wildcard_match($pattern, $value) {
    // potluck
    return (bool) rand(0, 1);
}

require_once '/tmp/globby/test/functional/test-patterns.php';

?>
```

Which is very likely to output a failure:

```
$ php test.php
Globby Tests

/tmp/globby/test/functional/patterns_general.json:
.F

exception 'UnexpectedValueException' with message 'Pattern "foo" should not match value "fob".' in /tmp/globby/test/functional/test-patterns.php:65
Stack trace:
#0 /tmp/globby/test/functional/test-patterns.php(45): validate('foo', 'fob', false)
#1 /tmp/globby/test/functional/test-patterns.php(78): test(Array)
#2 /tmp/globby/test/functional/test-patterns.php(88): testPaths(Array)
#3 /tmp/globby/test/functional/test-patterns.php(105): run(Array)
#4 /tmp/test.php(7): require_once('/tmp/globby/tes...')
#5 {main}

Failed.
```