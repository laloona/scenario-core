--TEST--
CLI debug command with class name and defined parameters
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'debug',
    \Stateforge\Suite\Scenario\Main\SecondScenario::class,
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite: Stateforge\Suite\Scenario\Main\SecondScenario
------------------------------------------------------------------


  second-scenario   My second scenario  


The following parameters are defined:
-------------------------------------


 ───────── ───────── ──────────────────── ────────── ──────────── ───────── 
  name      type      description          required   repeatable   default  
 ───────── ───────── ──────────────────── ────────── ──────────── ───────── 
  param-1   integer   My first parameter   true       false                 
 ───────── ───────── ──────────────────── ────────── ──────────── ───────── 


Audits from Stateforge\Suite\Scenario\Main\SecondScenario with execution up
---------------------------------------------------------------------------

Stateforge\Suite\Scenario\Main\FirstScenario{"myint":4}

Audits from Stateforge\Suite\Scenario\Main\SecondScenario::up with execution up
-------------------------------------------------------------------------------


Audits from Stateforge\Suite\Scenario\Main\SecondScenario with execution down
-----------------------------------------------------------------------------

Stateforge\Suite\Scenario\Main\FirstScenario{"myint":4}

Audits from Stateforge\Suite\Scenario\Main\SecondScenario::down with execution down
-----------------------------------------------------------------------------------