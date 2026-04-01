--TEST--
CLI debug commands with class and parameter and audit
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'apply',
    \Scenario\Main\SecondScenario::class,
    '--parameter=param-1=7',
    '--audit',
    '--down',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Scenario\Main\FirstScenario
first scenario was applied with down
Scenario\Main\SecondScenario{"param-1":7}
second scenario was applied with down and parameter 7