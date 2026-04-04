<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime;

use Stateforge\Scenario\Core\Contract\ScenarioBuilderInterface;
use Stateforge\Scenario\Core\Contract\ScenarioInterface;
use Stateforge\Scenario\Core\Runtime\Exception\WrongScenarioSubclassException;
use function is_subclass_of;

final class ScenarioBuilder implements ScenarioBuilderInterface
{
    /**
     * @param class-string $scenarioClass
     */
    public function build(string $scenarioClass): ScenarioInterface
    {
        $object = new $scenarioClass();
        if (is_subclass_of($object, ScenarioInterface::class) === false) {
            throw new WrongScenarioSubclassException($scenarioClass, ScenarioInterface::class);
        }

        return $object;
    }
}
