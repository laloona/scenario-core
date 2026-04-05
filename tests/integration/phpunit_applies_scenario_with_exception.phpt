--TEST--
PHPUnit will apply an scenario which causes an exception
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    '--configuration=' . __DIR__ . DIRECTORY_SEPARATOR . 'phpunit.xml',
    '--no-progress',
    '--do-not-cache-result',
    __DIR__ . '/tests/ScenarioExceptionTest.php'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Configuration: %sphpunit.xml

first scenario was applied with up
second scenario was applied with up and parameter 5
first scenario was applied with down
second scenario was applied with down and parameter 5
Time: %s, Memory: %s MB

There was 1 PHPUnit test runner warning:

%A

--

There was 1 error:

1) Stateforge\ScenarioTests\ScenarioExceptionTest::testScenario
Stateforge\Scenario\Core\Runtime\Exception\Application\TestMethodFailureException: OnMethod "Stateforge\ScenarioTests\ScenarioExceptionTest::testScenario" failure: [Exception]: some error happend in up.

%A

Caused by
Exception: some error happend in up.

%A

ERRORS!
Tests: 1, Assertions: 0, Errors: 1, PHPUnit Warnings: 1.