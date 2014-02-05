<?php

error_reporting(E_ALL);

/**
 * Define the "wildcard_match" function, if undefined. This provides a mechanism for people to test their own
 * implementation of glob-style wildcard matching, if they so wish. To do so, simply define "wildcard_match" before
 * including this file, and then call suite() with a path to the appropriate file.
 */
if (!function_exists('wildcard_match')) {
    require_once __DIR__ . '/../../vendor/autoload.php';

    /**
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    function wildcard_match($pattern, $value) {
        $globby = new \Globby\Pattern($pattern);
        return $globby->match($value);
    }
}

/**
 * Test an array of patterns. The format must be:
 *
 * [
 *   "pattern": // glob style pattern to be tested
 *     [
 *       ["value", "value", ..] // values expecting a positive match
 *       ["value", "value", ..] // values expecting a negative match
 *     ]
 *   "pattern": .. // next pattern, etc
 * ]
 *
 * @param array $patterns
 */
function test(array $patterns) {
    foreach ($patterns as $pattern => $values) {
        foreach ($values[0] as $positive) {
            validate($pattern, $positive, TRUE);
        }

        foreach ($values[1] as $negative) {
            validate($pattern, $negative, FALSE);
        }
    }
}

/**
 * @param string $pattern
 * @param string $value
 * @param bool $expected
 * @throws \UnexpectedValueException
 */
function validate($pattern, $value, $expected) {
    $result = wildcard_match($pattern, $value);

    if ($result !== $expected) {
        echo 'F';

        $verb = $expected === FALSE ? 'should not' : 'should';
        $message = sprintf('Pattern "%s" %s match value "%s".', $pattern, $verb, $value);

        throw new \UnexpectedValueException($message);
    }

    echo '.';
}

/**
 * @param array $paths
 */
function testPaths(array $paths) {
    foreach ($paths as $path) {
        $patterns = json_decode(file_get_contents($path), true);
        echo "\n\n{$path}:\n";
        test($patterns);
    }
}

/**
 * @param array $paths
 */
function run(array $paths) {
    try {
        echo "Globby Tests";
        testPaths($paths);
        echo "\n\nOK! All passed.\n";
    }
    catch (Exception $e) {
        echo "\n\n{$e}\n\nFailed.\n";
        exit(1);
    }
}

if ($argc > 1) {
    array_shift($argv);
    $files = $argv;
}
else {
    $files = glob(__DIR__ . '/patterns_*.json');
}

run($files);