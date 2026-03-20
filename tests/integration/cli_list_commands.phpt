--TEST--
CLI list commands when no command is given
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    '--quiet'
];

require_once 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
available commands
------------------


  debug     Debugs a given scenario or Unit test.                                                             
  apply     Applies a given scenario, use --up or --down to choose how the scenario should be applied.        
  list      List all available scenarios, use --suite="name of you suite" if you want to see just one suite.  
  make      Make a scenario or config file.                                                                   
  refresh   Executes the database refresh. Use --connection="connection_name" to specify given connection.
