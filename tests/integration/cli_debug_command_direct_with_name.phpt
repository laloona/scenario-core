--TEST--
CLI debug commands with name
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'debug',
    'third-scenario',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite: Stateforge\Scenario\Main\ThirdScenario
-----------------------------------------------------------


  third-scenario   My third scenario  


Audits from Stateforge\Scenario\Main\ThirdScenario with execution up
--------------------------------------------------------------------


Audits from Stateforge\Scenario\Main\ThirdScenario::up with execution up
------------------------------------------------------------------------

Stateforge\Scenario\Main\FirstScenario

Audits from Stateforge\Scenario\Main\ThirdScenario with execution down
----------------------------------------------------------------------


Audits from Stateforge\Scenario\Main\ThirdScenario::down with execution down
----------------------------------------------------------------------------

Stateforge\Scenario\Main\FirstScenario
Stateforge\Scenario\Main\SecondScenario{"param-1":5}