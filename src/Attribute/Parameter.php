<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Attribute;

use Attribute;
use Scenario\Core\Runtime\Metadata\ParameterType;

#[Attribute(Attribute::TARGET_CLASS)]
final class Parameter
{
    public function __construct(
        public readonly string $name,
        public readonly ParameterType $type,
        public readonly ?string $description = null,
        public readonly bool $required = false,
        public readonly mixed $default = null,
    ) {
    }

    public function validate(mixed $value): bool
    {
        if ($this->required === true
            && $value === null) {
            return false;
        }

        return $this->type->valid($value);
    }
}
