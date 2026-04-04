--TEST--
CLI generates a warning and ask for confirmation without quiet
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', false);
$_SERVER['argv'] = [
    'bin/scenario'
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

exit((new Stateforge\Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
[WARNING] Plaese don't use these commands on production systems as data will be modified.      
                                                                                                   

Do you want to continue? [yes / No]:
>
