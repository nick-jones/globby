<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Globby\Pattern;

if ($argc !== 2 || !is_readable($argv[1])) {
    echo sprintf("Usage: %s [path]\n", $argv[0]);
    exit(1);
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
        $globby = new Pattern($pattern);

        foreach (array_shift($values) as $positive) {
            validate($globby, $positive, TRUE);
        }

        foreach (array_shift($values) as $negative) {
            validate($globby, $negative, FALSE);
        }
    }
}

/**
 * @param Pattern $pattern
 * @param string $value
 * @param bool $expected
 * @throws \UnexpectedValueException
 */
function validate(Pattern $pattern, $value, $expected) {
    $result = $pattern->match($value);

    if ($result !== $expected) {
        echo 'F';

        $message = sprintf(
            'Pattern "%s" %s match value "%s". Compiled expression is: "%s"',
            $pattern->getPattern(),
            $expected === FALSE ? 'should not' : 'should',
            $value,
            $pattern->getRegex()
        );

        throw new \UnexpectedValueException($message);
    }

    echo '.';
}

$patterns = json_decode(file_get_contents($argv[1]), true);

try {
    test($patterns);
    echo "\n\nOK!\n";
}
catch (Exception $e) {
    echo "\n\n{$e}\n";
    exit(1);
}