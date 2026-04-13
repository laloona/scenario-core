<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Command;

use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\ParameterType;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeRegistry;
use function count;
use function ksort;

final class ListParameterTypesCommand extends CliCommand
{
    public function description(): string
    {
        return 'List all registered parameter types.';
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $output->headline('Built-in parameter types');
        $output->table(
            ['type', 'description'],
            [
                [ParameterType::class . '::Boolean', ParameterType::Boolean->value],
                [ParameterType::class . '::Float', ParameterType::Float->value],
                [ParameterType::class . '::Integer', ParameterType::Integer->value],
                [ParameterType::class . '::String', ParameterType::String->value],
            ],
        );

        $parameterTypes = ParameterTypeRegistry::getInstance()->all();
        if (count($parameterTypes) > 0) {
            ksort($parameterTypes);
            $table = [];
            foreach ($parameterTypes as $class => $asParameterType) {
                $table[] = [
                    $class,
                    $asParameterType->description,
                ];
            }

            $output->headline('Registered parameter types');
            $output->table(
                ['class', 'description'],
                $table,
            );
        }
        return Command::Success;
    }
}
