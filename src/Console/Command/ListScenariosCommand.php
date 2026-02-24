<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Command;

use Scenario\Core\Console\Command\Renderer\ListScenarios;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;

final class ListScenariosCommand extends CliCommand
{
    public function description(): string
    {
        return 'List all available scenarios, use --suite="name of you suite" if you want to see just one suite.';
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        new ListScenarios()->render($input, $output);
        return Command::Success;
    }
}
