<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata\ValueType;

use function is_bool;
use function is_string;
use function strtolower;

final class BooleanType
{
    public readonly ?bool $value;

    public function __construct(mixed $value)
    {
        $this->value = $this->cast($value);
    }

    private function cast(mixed $value): ?bool
    {
        return is_bool($value) === true
            ? $value
            : (is_string($value) === true
                ? match (strtolower($value)) {
                    '1', 'true', 'yes', 'on' => true,
                    '0', 'false', 'no', 'off' => false,
                    default => null,
                }
                : null);
    }

    public function asString(): string|null
    {
        if ($this->value === null) {
            return null;
        }

        return $this->value === true ? '1' : '0';
    }
}
