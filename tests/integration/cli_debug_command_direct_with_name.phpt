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

require_once 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite: Scenario\Main\ThirdScenario
------------------------------------------------


  third-scenario   My third scenario  


Audits from Scenario\Main\ThirdScenario with execution up
---------------------------------------------------------


Audits from Scenario\Main\ThirdScenario::up with execution up
-------------------------------------------------------------

Scenario\Main\FirstScenario

Audits from Scenario\Main\ThirdScenario with execution down
-----------------------------------------------------------


Audits from Scenario\Main\ThirdScenario::down with execution down
-----------------------------------------------------------------

Scenario\Main\FirstScenario
Scenario\Main\SecondScenario{"param-1":5}
