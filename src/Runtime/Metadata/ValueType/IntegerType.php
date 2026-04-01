<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata\ValueType;

use function filter_var;
use function is_int;
use function is_string;
use const FILTER_VALIDATE_INT;

final class IntegerType
{
    public readonly ?int $value;

    public function __construct(mixed $value)
    {
        $this->value = $this->cast($value);
    }

    private function cast(mixed $value): ?int
    {
        return is_int($value) === true
            ? $value
            : (is_string($value) === true && filter_var($value, FILTER_VALIDATE_INT) !== false
                ? (int) $value
                : null);
    }

    public function asString(): string|null
    {
        if ($this->value === null) {
            return null;
        }

        return (string)$this->value;
    }
}
