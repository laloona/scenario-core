<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console;

use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use function count;
use function ksort;

final class ScenarioSuiteFilter
{
    /**
     * @param array<string, ScenarioDefinition> $scenarios
     * @return array<string, ScenarioDefinition>
     */
    public function filter(array $scenarios, ?string $suite): array
    {
        if (count($scenarios) === 0) {
            return [];
        }

        $filtered = [];
        if ($suite !== null) {
            foreach ($scenarios as $name => $scenario) {
                if ($scenario->suite === $suite) {
                    $filtered[$name] = $scenario;
                }
            }
            $scenarios = $filtered;
            unset($filtered);
        }
        ksort($scenarios);

        return $scenarios;
    }
}
