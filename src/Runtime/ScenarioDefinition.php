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

use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Attribute\Parameter;

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

    public function isSame(string $name): bool
    {
        return ($this->class === $name
                || $this->name === $name);
    }
}
