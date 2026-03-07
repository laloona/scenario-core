<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core;

use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\ScenarioParameters;

abstract class Scenario implements ScenarioInterface
{
    private ScenarioParameters $parameters;

    protected function configure(): void
    {
    }

    final protected function require(string $name): void
    {
        $this->parameters->register($name, true);
    }

    final protected function optional(string $name): void
    {
        $this->parameters->register($name, false);
    }

    final public function resolve(ScenarioParameters $parameters): void
    {
        $this->parameters = $parameters;
        $this->configure();
        $this->parameters->resolve();
    }

    public function down(): void
    {
    }
}
