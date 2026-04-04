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

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Main Scenario Suite: Stateforge\Scenario\Main\FourthScenario
------------------------------------------------------------


  fourth-scenario   My fourth scenario  


Audits from Stateforge\Scenario\Main\FourthScenario with execution up
---------------------------------------------------------------------


Audits from Stateforge\Scenario\Main\FourthScenario::up with execution up
-------------------------------------------------------------------------

Stateforge\Scenario\Main\FirstScenario
Stateforge\Scenario\Main\ThirdScenario

                                                                                                   
    [ERROR] OnMethod "Stateforge\Scenario\Core\Runtime\Application\TestMethodState::up" failure:   
  [Stateforge\Scenario\Core\Runtime\Exception\Metadata\CycleException]:                            
  Stateforge\Scenario\Core\Runtime\Application\TestMethodState::up:                                
  Stateforge\Scenario\Main\FirstScenario caused cycle in applied stack                             
  [Stateforge\Scenario\Main\FirstScenario => Stateforge\Scenario\Main\ThirdScenario =>             
  Stateforge\Scenario\Main\FirstScenario] while applying up                                        
                                                                                                   


Audits from Stateforge\Scenario\Main\FourthScenario with execution down
-----------------------------------------------------------------------


Audits from Stateforge\Scenario\Main\FourthScenario::down with execution down
-----------------------------------------------------------------------------