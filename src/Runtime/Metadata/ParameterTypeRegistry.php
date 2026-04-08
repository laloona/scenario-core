<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata;

use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterTypeAlreadyRegisteredException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\UnknownParameterTypeException;
use Symfony\Component\DependencyInjection\Exception\InvalidParameterTypeException;
use function in_array;
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
     * @var list<class-string>
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
    public function register(string $class): void
    {
        if (is_subclass_of($class, ParameterTypeDefinition::class) === false) {
            throw new InvalidParameterTypeException($class);
        }

        if (in_array($class, $this->registeredTypes, true) === true) {
            throw new ParameterTypeAlreadyRegisteredException($class);
        }

        $this->registeredTypes[] = $class;
    }

    public function resolve(string $class): ParameterTypeDefinition
    {
        if (in_array($class, $this->registeredTypes, true) === false) {
            throw new UnknownParameterTypeException($class);
        }

        return new $class();
    }
}
