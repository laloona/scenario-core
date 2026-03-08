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

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

enum ParameterType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case Array = 'array';

    public function valid(mixed $value): bool
    {
        return match($this) {
            self::String => is_string($value),
            self::Integer => is_int($value),
            self::Float => is_float($value),
            self::Boolean => is_bool($value),
            self::Array => is_array($value),
        };
    }
}
