--TEST--
CLI list scenarios with suite filter
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'list',
    '--suite="Main Scenario Suite"',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite
-------------------


 ─────────────────────────────────────────────── ───────────────── ──────────────────── 
  class                                           name              description         
 ─────────────────────────────────────────────── ───────────────── ──────────────────── 
  Stateforge\Suite\Scenario\Main\FirstScenario    first-scenario    My first scenario   
  Stateforge\Suite\Scenario\Main\FourthScenario   fourth-scenario   My fourth scenario  
  Stateforge\Suite\Scenario\Main\SecondScenario   second-scenario   My second scenario  
  Stateforge\Suite\Scenario\Main\ThirdScenario    third-scenario    My third scenario   
 ─────────────────────────────────────────────── ───────────────── ────────────────────