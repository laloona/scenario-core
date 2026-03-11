<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata;

use function filter_var;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function strtolower;

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
            self::String => is_string($value) === true ? preg_replace('/^["\']|["\']$/', '', $value) : null,

            self::Integer => is_int($value) === true
                ? $value
                : (is_string($value) === true && filter_var($value, FILTER_VALIDATE_INT) !== false
                    ? (int) $value
                    : null),

            self::Float => is_float($value) === true
                ? $value
                : (is_string($value) === true && filter_var($value, FILTER_VALIDATE_FLOAT) !== false
                    ? (float) $value
                    : null),

            self::Boolean => is_bool($value) === true
                ? $value
                : (is_string($value) === true
                    ? match (strtolower($value)) {
                        '1', 'true', 'yes', 'on' => true,
                        '0', 'false', 'no', 'off' => false,
                        default => null,
                    }
                    : null),
        };
    }

    public function asString(mixed $value): string|null
    {
        $value = $this->cast($value);
        if ($value === null) {
            return null;
        }

        return match ($this) {
            self::String, self::Integer, self::Float => (string)$value,
            self::Boolean => $value === true ? '1' : '0',
        };
    }
}
