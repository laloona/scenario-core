--TEST--
CLI debug command with name and cycle
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'debug',
    'fourth-scenario',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite: Scenario\Main\FourthScenario
-------------------------------------------------


  fourth-scenario   My fourth scenario  


Audits from Scenario\Main\FourthScenario with execution up
----------------------------------------------------------


Audits from Scenario\Main\FourthScenario::up with execution up
--------------------------------------------------------------

Scenario\Main\FirstScenario
Scenario\Main\ThirdScenario

                                                                                                   
    [ERROR] OnMethod "Scenario\Core\Runtime\Application\TestMethodState::up" failure:              
  [Scenario\Core\Runtime\Exception\Metadata\CycleException]:                                       
  Scenario\Core\Runtime\Application\TestMethodState::up: Scenario\Main\FirstScenario caused        
  cycle in applied stack [Scenario\Main\FirstScenario => Scenario\Main\ThirdScenario =>            
  Scenario\Main\FirstScenario] while applying up                                                   
                                                                                                   


Audits from Scenario\Main\FourthScenario with execution down
------------------------------------------------------------


Audits from Scenario\Main\FourthScenario::down with execution down
------------------------------------------------------------------