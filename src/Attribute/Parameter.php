<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Attribute;

use Attribute;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterNameErrorException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterValueErrorException;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterTypeRegistry;
use function array_values;
use function gettype;
use function implode;
use function is_array;
use function is_string;
use function preg_match;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Parameter
{
    /**
     * @var string|int|float|bool|null|list<string|int|float|bool|null>
     */
    public readonly string|int|float|bool|null|array $default;

    public readonly ParameterType|ParameterTypeDefinition $type;

    /**
     * @param ParameterType|class-string $type
     */
    public function __construct(
        public readonly string $name,
        ParameterType|string $type,
        public readonly ?string $description = null,
        public readonly bool $required = false,
        public readonly bool $repeatable = false,
        mixed $default = null,
    ) {
        if ($this->isValidName($name) === false) {
            throw new ParameterNameErrorException($name);
        }

        $this->type = (is_string($type) === true)
            ? ParameterTypeRegistry::getInstance()->resolve($type)
            : $type;

        if ($default !== null) {
            if ($this->repeatable === true) {
                if (is_array($default) === false) {
                    throw new ParameterValueErrorException($name, 'array', gettype($default), true);
                }

                /** @var list<string|int|float|bool|null> $default */
                $default = array_values($default);
                foreach ($default as &$value) {
                    if ($this->type->valid($value) === false) {
                        throw new ParameterValueErrorException($name, $this->type->value, gettype($value), true);
                    }

                    $value = $this->type->cast($value);
                }
            } else {
                if ($this->type->valid($default) === false) {
                    throw new ParameterValueErrorException($name, $this->type->value, gettype($default), true);
                }

                $default = $this->type->cast($default);
            }
        }

        $this->default = $default;
    }

    public function validate(mixed $value): bool
    {
        if ($this->required === false
            && $value === null) {
            return true;
        }

        if ($this->repeatable === true) {
            if (is_array($value) === false) {
                return false;
            }

            foreach ($value as $singleValue) {
                if ($this->type->valid($singleValue) === false) {
                    return false;
                }
            }

            return true;
        } else {
            return $this->type->valid($value);
        }
    }

    /**
     * @return string|int|float|bool|null|list<string|int|float|bool|null>
     */
    public function cast(mixed $value): string|int|float|bool|null|array
    {
        if ($this->repeatable === true
            && is_array($value) === true) {
            $casted = [];
            foreach ($value as $singleValue) {
                $casted[] = $this->type->cast($singleValue);
            }

            return $casted;
        }

        return $this->type->cast($value);
    }

    /**
     * @param string|int|float|bool|null|list<string|int|float|bool|null> $value
     */
    public function asString(string|int|float|bool|null|array $value): string|null
    {
        if ($this->repeatable === true
            && is_array($value) === true) {
            foreach ($value as &$singleValue) {
                $singleValue = $this->type->asString($singleValue);
            }

            return '[' . implode(',', $value) . ']';
        }

        return $this->type->asString($value);
    }

    private function isValidName(string $name): bool
    {
        // snake_case: foo_bar_baz
        if (preg_match('/^[a-z0-9]+(_[a-z0-9]+)*$/', $name) === 1) {
            return true;
        }

        // kebab-case: foo-bar-baz
        if (preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $name) === 1) {
            return true;
        }

        // camelCase: FooBarBaz
        if (preg_match('/^[a-z][a-zA-Z0-9]*$/', $name) === 1) {
            return true;
        }

        return false;
    }
}
