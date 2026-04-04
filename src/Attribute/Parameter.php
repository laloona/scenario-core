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
use Stateforge\Scenario\Core\Runtime\Exception\ParameterValueErrorException;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;
use function array_values;
use function gettype;
use function implode;
use function is_array;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Parameter
{
    /**
     * @var string|int|float|bool|null|list<string|int|float|bool|null>
     */
    public readonly string|int|float|bool|null|array $default;

    public function __construct(
        public readonly string $name,
        public readonly ParameterType $type,
        public readonly ?string $description = null,
        public readonly bool $required = false,
        public readonly bool $repeatable = false,
        mixed $default = null,
    ) {
        if ($default !== null) {
            if ($this->repeatable === true) {
                if (is_array($default) === false) {
                    throw new ParameterValueErrorException($name, 'array', gettype($default), true);
                }

                /** @var list<string|int|float|bool|null> $default */
                $default = array_values($default);
                foreach ($default as &$value) {
                    if ($type->valid($value) === false) {
                        throw new ParameterValueErrorException($name, $type->value, gettype($value), true);
                    }

                    $value = $type->cast($value);
                }
            } else {
                if ($type->valid($default) === false) {
                    throw new ParameterValueErrorException($name, $type->value, gettype($default), true);
                }

                $default = $type->cast($default);
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
}
