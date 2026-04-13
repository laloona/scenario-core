<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata\Parameter;

use ReflectionClass;
use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\Attribute\ParameterTypeCondition;
use Stateforge\Scenario\Core\ParameterTypeCondition as BaseParameterTypeCondition;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\InvalidParameterTypeException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterTypeAlreadyRegisteredException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\UnknownParameterTypeException;
use function array_key_exists;
use function is_subclass_of;

final class ParameterTypeRegistry
{
    private static ?ParameterTypeRegistry $instance = null;

    public static function getInstance(): self
    {
        static::$instance = static::$instance ?? new static();
        return static::$instance;
    }

    /**
     * @var array<class-string, AsParameterType>
     */
    private array $registeredTypes = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param class-string $class
     */
    public function register(string $class, AsParameterType $asParameterType): void
    {
        if (is_subclass_of($class, ParameterTypeDefinition::class) === false) {
            throw new InvalidParameterTypeException($class);
        }

        if (array_key_exists($class, $this->registeredTypes) === true) {
            throw new ParameterTypeAlreadyRegisteredException($class);
        }

        $attributes = (new ReflectionClass($class))->getAttributes(ParameterTypeCondition::class);
        foreach ($attributes as $attribute) {
            /** @var BaseParameterTypeCondition $condition */
            $condition = new ($attribute->newInstance()->condition)();
            if ($condition->matches() === false) {
                return;
            }
        }

        $this->registeredTypes[$class] = $asParameterType;
    }

    public function resolve(string $class): ParameterTypeDefinition
    {
        if (array_key_exists($class, $this->registeredTypes) === false) {
            throw new UnknownParameterTypeException($class);
        }

        /** @var ParameterTypeDefinition $parameterType */
        $parameterType = new $class();
        return $parameterType;
    }

    /**
     * @return array<string, AsParameterType>
     */
    public function all(): array
    {
        return $this->registeredTypes;
    }
}
