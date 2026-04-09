--TEST--
PHPUnit will apply an scenario with attributes and configured extension
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    '--configuration=' . __DIR__ . DIRECTORY_SEPARATOR . 'phpunit.xml',
    '--no-progress',
    '--do-not-cache-result',
    __DIR__ . '/tests/ScenarioTest.php'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Configuration: %sphpunit.xml

first scenario was applied with up and custom parameter 1
other scenario was applied with up
first scenario was applied with down and custom parameter 1
other scenario was applied with down
Time: %s, Memory: %s MB

OK (1 test, 0 assertions)