--TEST--
CLI debug commands with class and custom parameter
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'apply',
    \Stateforge\Suite\Scenario\Main\FirstScenario::class,
    '--parameter=myint=6',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
first scenario was applied with up and custom parameter 6
