# Functional tests

The [`test-patterns.php`](test-patterns.php) script provides a means to test wildcard patterns as described in a
supplied input file (to be provided as an argument to the script). Example usage:

`php test-patterns.php patterns_general.json`

The script iterates over the patterns contained within the file, and validates all associated test cases. It will exit
with a non-zero status on failure.

## Pattern test file structure

The supplied file should contain JSON, and be structured as follows:

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
