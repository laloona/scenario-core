--TEST--
CLI make command will fail because blueprints are not found
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario',
    'make',
    '--quiet'
];

require_once 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Please select the type do would like to make.                                                  
                                                                                                   

Please select one of the following: default (0)
(0) scenario
(1) config
> 
                                                                                                   
    [ERROR] Scenario generation failed.