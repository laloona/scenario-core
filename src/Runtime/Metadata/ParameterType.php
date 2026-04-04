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

use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

enum ParameterType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';

    public function valid(mixed $value): bool
    {
        return $this->cast($value) !== null;
    }

    public function cast(mixed $value): string|int|float|bool|null
    {
        if ($value === null) {
            return null;
        }

        return match ($this) {
            self::String => (new StringType($value))->value,
            self::Integer => (new IntegerType($value))->value,
            self::Float => (new FloatType($value))->value,
            self::Boolean => (new BooleanType($value))->value,
        };
    }

    public function asString(mixed $value): string|null
    {
        $value = $this->cast($value);
        if ($value === null) {
            return null;
        }

        return match ($this) {
            self::String => (new StringType($value))->asString(),
            self::Integer => (new IntegerType($value))->asString(),
            self::Float => (new FloatType($value))->asString(),
            self::Boolean => (new BooleanType($value))->asString(),
        };
    }
}
