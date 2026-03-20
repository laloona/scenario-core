--TEST--
CLI debug commands with class and parameter
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'apply',
    \Scenario\Main\SecondScenario::class,
    '--param-1=7',
    '--down',
    '--quiet'
];

require_once 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
first scenario was applied with down
second scenario was applied with down and parameter 7