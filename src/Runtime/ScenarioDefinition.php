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

use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Attribute\Parameter;

final class ScenarioDefinition
{
    public readonly ?string $name;

    /**
     * @param class-string $class
     * @param list<Parameter> $parameters
     */
    public function __construct(
        public readonly string $suite,
        public readonly string $class,
        public readonly AsScenario $attribute,
        public readonly array $parameters,
    ) {
        $this->name = $this->attribute->name;
    }
}
