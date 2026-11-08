<?php
declare(strict_types=1);
/**
 * All regression tests must run in a disposable copy, never in a deployment.
 * This guard also prevents accidental direct CLI invocation.
 */
$testProjectRoot = realpath(dirname(__DIR__));
$testAllowedRoot = realpath((string)getenv('ATELIER_TEST_ROOT'));
if (getenv('ATELIER_TEST_ISOLATED') !== '1'
    || $testProjectRoot === false || $testAllowedRoot === false
    || $testProjectRoot !== $testAllowedRoot
    || !is_file($testProjectRoot.'/.atelier-test-sandbox')
    || is_dir($testProjectRoot.'/.git')) {
    fwrite(STDERR, "Refusing unsafe regression execution: use scripts/full_regression.sh.\n");
    exit(78);
}
