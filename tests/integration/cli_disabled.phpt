--TEST--
CLI is disabled without force
--FILE--
<?php declare(strict_types=1);

define('SCENARIO_CLI_DISABLED', true);
$_SERVER['argv'] = [
    'bin/scenario'
];

require_once 'bootstrap.php';

exit((new Scenario\Core\Console\CliApplication())->run($_SERVER['argv']));
?>
--EXPECT--
CLI DISABLED!