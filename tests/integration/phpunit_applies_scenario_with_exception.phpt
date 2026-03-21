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

require_once 'bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
?>
--EXPECTF--
PHPUnit %s Sebastian Bergmann and contributors.

Runtime:       PHP %s
Configuration: /app/tests/integration/phpunit.xml

first scenario was applied with up
second scenario was applied with up and parameter 5
first scenario was applied with down
second scenario was applied with down and parameter 5
Time: %s, Memory: %s MB

There was 1 PHPUnit test runner warning:

1) Exception in third-party event subscriber: OnMethod "ScenarioTests\ScenarioExceptionTest::testScenario" failure: [Exception]: some error happend in up.
#0 /app/src/PHPUnit/Subscriber/FailureSubscriber.php(%s): Scenario\Core\Runtime\Application\TestMethodState->failure('ScenarioTests\\S...', Object(PHPUnit\Event\Code\TestMethod))
#1 /app/vendor/phpunit/phpunit/src/Event/Dispatcher/DirectDispatcher.php(%s): Scenario\Core\PHPUnit\Subscriber\FailureSubscriber->notify(Object(PHPUnit\Event\Test\Finished))
#2 /app/vendor/phpunit/phpunit/src/Event/Dispatcher/DeferringDispatcher.php(%s): PHPUnit\Event\DirectDispatcher->dispatch(Object(PHPUnit\Event\Test\Finished))
#3 /app/vendor/phpunit/phpunit/src/Event/Emitter/DispatchingEmitter.php(%s): PHPUnit\Event\DeferringDispatcher->dispatch(Object(PHPUnit\Event\Test\Finished))
#4 /app/vendor/phpunit/phpunit/src/Framework/TestRunner/TestRunner.php(%s): PHPUnit\Event\DispatchingEmitter->testFinished(Object(PHPUnit\Event\Code\TestMethod), %s)
#5 /app/vendor/phpunit/phpunit/src/Framework/TestCase.php(%s): PHPUnit\Framework\TestRunner->run(Object(ScenarioTests\ScenarioExceptionTest))
#6 /app/vendor/phpunit/phpunit/src/Framework/TestSuite.php(%s): PHPUnit\Framework\TestCase->run()
#7 /app/vendor/phpunit/phpunit/src/TextUI/TestRunner.php(%s): PHPUnit\Framework\TestSuite->run()
#8 /app/vendor/phpunit/phpunit/src/TextUI/Application.php(%s): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\NullResultCache), Object(PHPUnit\Framework\TestSuite))
#9 /app/tests/integration/phpunit_applies_scenario_with_exception.php(%s): PHPUnit\TextUI\Application->run(Array)
#10 Standard input code(%s): require('/app/tests/inte...')
#11 {main}

--

There was 1 error:

1) ScenarioTests\ScenarioExceptionTest::testScenario
Scenario\Core\Runtime\Exception\Application\TestMethodFailureException: OnMethod "ScenarioTests\ScenarioExceptionTest::testScenario" failure: [Exception]: some error happend in up.

/app/src/Runtime/Application/TestMethodState.php:%s
/app/src/PHPUnit/Subscriber/FailureSubscriber.php:%s

Caused by
Exception: some error happend in up.

/app/tests/integration/scenario/other/FailedScenario.php:%s
/app/src/Runtime/Metadata/Handler/ApplyScenarioHandler.php:%s
/app/src/Runtime/Metadata/Handler/AttributeHandler.php:%s
/app/src/Runtime/Metadata/AttributeProcessor.php:%s
/app/src/PHPUnit/Subscriber/TestMethodSubscriber.php:%s
/app/src/PHPUnit/Subscriber/TestMethodUpSubscriber.php:%s

ERRORS!
Tests: 1, Assertions: 0, Errors: 1, PHPUnit Warnings: 1.