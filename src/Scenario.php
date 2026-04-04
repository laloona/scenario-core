<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core;

use Stateforge\Scenario\Core\Contract\ScenarioInterface;
use Stateforge\Scenario\Core\Runtime\ScenarioParameters;

abstract class Scenario implements ScenarioInterface
{
    private ScenarioParameters $parameters;

    final public function configure(ScenarioParameters $parameters): void
    {
        $this->parameters = $parameters;
    }

    final public function parameter(string $name): mixed
    {
        return $this->parameters->get($name);
    }

    public function down(): void
    {
    }
}
