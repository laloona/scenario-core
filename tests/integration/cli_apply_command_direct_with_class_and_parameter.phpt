--TEST--
CLI debug commands with class and parameter
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'apply',
    \Stateforge\Suite\Scenario\Main\SecondScenario::class,
    '--parameter=param-1=7',
    '--down',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
first scenario was applied with down and custom parameter 4
second scenario was applied with down and parameter 7