--TEST--
CLI list scenarios
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'list',
    '--quiet'
];

require_once 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite
-------------------


 ────────────────────────────── ───────────────── ──────────────────── 
  class                          name              description         
 ────────────────────────────── ───────────────── ──────────────────── 
  Scenario\Main\ThirdScenario    third-scenario    My third scenario   
  Scenario\Main\FirstScenario    first-scenario    My first scenario   
  Scenario\Main\FourthScenario   fourth-scenario   My fourth scenario  
  Scenario\Main\SecondScenario   second-scenario   My second scenario  
 ────────────────────────────── ───────────────── ──────────────────── 


Other Scenario Suite
--------------------


 ─────────────────────────────── ───────────────── ───────────── 
  class                           name              description  
 ─────────────────────────────── ───────────────── ───────────── 
  Scenario\Other\FailedScenario   failed-scenario                
  Scenario\Other\OtherScenario    other-scenario                 
 ─────────────────────────────── ───────────────── ─────────────