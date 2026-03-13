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

use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\Exception\DefinitionException;
use Scenario\Core\Runtime\Exception\RegistryException;

final class ScenarioRegistry extends Registry
{
    protected static ?ScenarioRegistry $instance = null;

    public static function getInstance(): self
    {
        static::$instance = static::$instance ?? new static();
        return static::$instance;
    }

    /**
     * @var array<class-string|string, ScenarioDefinition>
     */
    private array $registeredScenarios = [];

    public function register(ScenarioDefinition $definition): void
    {
        if (is_subclass_of($definition->class, ScenarioInterface::class) === false) {
            throw new DefinitionException($definition->class . ' is not a subclass of ' . ScenarioInterface::class);
        }

        if (isset($this->registeredScenarios[$definition->class]) === true) {
            throw new DefinitionException($definition->class . ' is already registered');
        }

        if ($definition->name !== null
            && $definition->name !== ''
            && isset($this->registeredScenarios[$definition->name]) === true) {
            throw new DefinitionException('scenario name ' . $definition->name . ' already registered for '. $this->registeredScenarios[$definition->name]->class);
        }

        $this->registeredScenarios[$definition->class] = $definition;
        if ($definition->name !== null
            && $definition->name !== '') {
            $this->registeredScenarios[$definition->name] = $definition;
        }
    }

    public function clear(): void
    {
        $this->registeredScenarios = [];
    }

    /**
     * @return array<class-string|string, ScenarioDefinition>
     */
    public function all(): array
    {
        return $this->registeredScenarios;
    }

    /**
     * @param class-string|string $id
     */
    public function resolve(string $id): ScenarioDefinition
    {
        if (isset($this->registeredScenarios[$id])) {
            return $this->registeredScenarios[$id];
        }

        throw new RegistryException('scenario ' . $id);
    }
}
