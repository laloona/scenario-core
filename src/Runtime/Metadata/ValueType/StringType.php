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

use function is_string;
use function preg_replace;

final class StringType
{
    public readonly ?string $value;

    public function __construct(mixed $value)
    {
        $this->value = $this->cast($value);
    }

    private function cast(mixed $value): ?string
    {
        return is_string($value) === true
            ? preg_replace('/^["\']|["\']$/', '', $value)
            : null;
    }

    public function asString(): string|null
    {
        if ($this->value === null) {
            return null;
        }

        return $this->value;
    }
}
