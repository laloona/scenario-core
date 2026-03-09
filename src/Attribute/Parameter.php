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
use Scenario\Core\Runtime\Exception\ParameterValueErrorException;
use Scenario\Core\Runtime\Metadata\ParameterType;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Parameter
{
    public readonly string|int|float|bool|null $default;

    public function __construct(
        public readonly string $name,
        public readonly ParameterType $type,
        public readonly ?string $description = null,
        public readonly bool $required = false,
        mixed $default = null,
    ) {
        if ($default !== null
            && $type->valid($default) === false) {
            throw new ParameterValueErrorException($name, $type->value, gettype($default), true);
        }

        $this->default = $type->cast($default);
    }

    public function validate(mixed $value): bool
    {
        if ($this->required === false
            && $value === null) {
            return true;
        }

        return $this->type->valid($value);
    }
}
