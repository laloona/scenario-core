<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime;

use Scenario\Core\Contract\ScenarioBuilderInterface;
use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\Exception\ScenarioBuilderException;

final class ScenarioBuilder implements ScenarioBuilderInterface
{
    /**
     * @param class-string $scenarioClass
     */
    public function build(string $scenarioClass): ScenarioInterface
    {
        $object = new $scenarioClass();
        if (is_subclass_of($object, ScenarioInterface::class) === false) {
            throw new ScenarioBuilderException($scenarioClass);
        }

        return $object;
    }
}
