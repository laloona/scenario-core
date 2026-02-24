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

final class ScenarioDefinition
{
    public readonly ?string $name;

    /**
     * @param class-string $class
     */
    public function __construct(
        public readonly string $suite,
        public readonly AsScenario $attribute,
        public readonly string $class,
    ) {
        $this->name = $this->attribute->name;
    }
}
