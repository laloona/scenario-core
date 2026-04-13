--TEST--
CLI list parameter types
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'parameter',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Built-in parameter types
------------------------


 ───────────────────────────────────────────────── ───────────── 
  type                                              description  
 ───────────────────────────────────────────────── ───────────── 
  Stateforge\Scenario\Core\ParameterType::Boolean   boolean      
  Stateforge\Scenario\Core\ParameterType::Float     float        
  Stateforge\Scenario\Core\ParameterType::Integer   integer      
  Stateforge\Scenario\Core\ParameterType::String    string       
 ───────────────────────────────────────────────── ───────────── 


Registered parameter types
--------------------------


 ────────────────────────────────────────────────────────── ───────────── 
  class                                                      description  
 ────────────────────────────────────────────────────────── ───────────── 
  Stateforge\Suite\Scenario\Parameter\IntegerParameterType                
 ────────────────────────────────────────────────────────── ─────────────