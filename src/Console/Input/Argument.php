<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Input;

use Stateforge\Scenario\Core\Console\Exception\ArgumentValueErrorException;
use function gettype;

final class Argument
{
    private bool|float|int|string|null $default;

    public function __construct(
        public readonly string $name,
        public readonly InputType $type,
        public readonly bool $required = false,
        public readonly ?string $description = null,
        mixed $default = null,
    ) {
        try {
            $this->default = $default !== null ? $this->cast($default) : null;
            if ($default !== null
                && $this->default === null) {
                throw new ArgumentValueErrorException($this->name, $this->type->value, gettype($default), true);
            }
        } catch (ArgumentValueErrorException $exception) {
            throw new ArgumentValueErrorException($this->name, $this->type->value, gettype($default), true);
        }
    }

    public function cast(mixed $value): bool|float|int|string|null
    {
        if ($value === null) {
            if ($this->required === true) {
                throw new ArgumentValueErrorException($this->name, $this->type->value, gettype($value), false);
            }

            return $this->default;
        }

        $casted = $this->type->cast($value);
        if ($casted === null) {
            throw new ArgumentValueErrorException($this->name, $this->type->value, gettype($value), false);
        }

        return $casted;
    }
}
