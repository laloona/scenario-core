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
Main Scenario Suite: Stateforge\Suite\Scenario\Main\ThirdScenario
-----------------------------------------------------------------


  third-scenario   My third scenario  


Audits from Stateforge\Suite\Scenario\Main\ThirdScenario with execution up
--------------------------------------------------------------------------


Audits from Stateforge\Suite\Scenario\Main\ThirdScenario::up with execution up
------------------------------------------------------------------------------

Stateforge\Suite\Scenario\Main\FirstScenario{"myint":9}

Audits from Stateforge\Suite\Scenario\Main\ThirdScenario with execution down
----------------------------------------------------------------------------


Audits from Stateforge\Suite\Scenario\Main\ThirdScenario::down with execution down
----------------------------------------------------------------------------------

Stateforge\Suite\Scenario\Main\FirstScenario{"myint":4}
Stateforge\Suite\Scenario\Main\SecondScenario{"param-1":5}