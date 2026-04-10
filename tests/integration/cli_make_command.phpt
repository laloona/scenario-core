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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
Please select the type do would like to make.                                                  
                                                                                                   

Please select one of the following: default (0)
(0) scenario
(1) parameter type
(2) config
> 
                                                                                                   
    [ERROR] Scenario generation failed.