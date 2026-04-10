--TEST--
CLI list commands when no command is given
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    '--quiet'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
available commands
------------------


  apply     Applies a given scenario, use --up or --down to choose how the scenario should be applied.        
  debug     Debugs a given scenario or Unit test.                                                             
  install   Configure the extension for PHPUnit.                                                              
  list      List all available scenarios, use --suite="name of you suite" if you want to see just one suite.  
  make      Make a scenario, parameter type or config file.                                                   
  refresh   Executes the database refresh. Use --connection="connection_name" to specify given connection. 