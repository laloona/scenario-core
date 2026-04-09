--TEST--
CLI debug command with name and custom parameter
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'debug',
    'first-scenario',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite: Stateforge\Suite\Scenario\Main\FirstScenario
-----------------------------------------------------------------


  first-scenario   My first scenario  


The following parameters are defined:
-------------------------------------


 ─────── ────────────────────────────────────────────────────────── ───────────── ────────── ──────────── ───────── 
  name    type                                                       description   required   repeatable   default  
 ─────── ────────────────────────────────────────────────────────── ───────────── ────────── ──────────── ───────── 
  myint   Stateforge\Suite\Scenario\Parameter\IntegerParameterType                 false      false        1        
 ─────── ────────────────────────────────────────────────────────── ───────────── ────────── ──────────── ───────── 


Audits from Stateforge\Suite\Scenario\Main\FirstScenario with execution up
--------------------------------------------------------------------------


Audits from Stateforge\Suite\Scenario\Main\FirstScenario::up with execution up
------------------------------------------------------------------------------


Audits from Stateforge\Suite\Scenario\Main\FirstScenario with execution down
----------------------------------------------------------------------------


Audits from Stateforge\Suite\Scenario\Main\FirstScenario::down with execution down
----------------------------------------------------------------------------------