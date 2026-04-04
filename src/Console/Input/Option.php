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

use Stateforge\Scenario\Core\Console\Exception\OptionValueErrorException;
use function gettype;
use function is_array;

final class Option
{
    /**
     * @var null|bool|float|int|string|list<null|bool|float|int|string>
     */
    private bool|float|int|string|array|null $default;

    public function __construct(
        public readonly string $name,
        public readonly InputType $type,
        public readonly bool $required = false,
        public readonly bool $repeatable = false,
        public readonly ?string $description = null,
        mixed $default = null,
    ) {
        if ($default === null) {
            $this->default = null;
            return;
        }

        if ($this->repeatable === true) {
            if (is_array($default) === false) {
                throw new OptionValueErrorException($this->name, 'array', gettype($default), true);
            }

            $this->default = [];
            foreach ($default as $value) {
                $castedValue = $this->type->cast($value);
                if ($castedValue === null) {
                    throw new OptionValueErrorException($this->name, $this->type->value, gettype($value), true);
                }

                $this->default[] = $castedValue;
            }

            return;
        }

        try {
            $this->default = $this->cast($default);
        } catch (OptionValueErrorException $exception) {
            throw new OptionValueErrorException($this->name, $this->type->value, gettype($default), true);
        }
    }

    /**
     * @return null|bool|float|int|string|list<null|bool|float|int|string>
     */
    public function cast(mixed $value): bool|float|int|string|array|null
    {
        if ($value === null) {
            if ($this->required === true) {
                throw new OptionValueErrorException($this->name, $this->type->value, gettype($value), false);
            }

            return $this->default;
        }

        if ($this->repeatable === true) {
            if (is_array($value) === false) {
                throw new OptionValueErrorException($this->name, 'array', gettype($value), false);
            }

            /** @var list<null|bool|float|int|string> $casted */
            $casted = [];
            foreach ($value as $singleValue) {
                $castedValue = $this->type->cast($singleValue);
                if ($castedValue === null) {
                    throw new OptionValueErrorException($this->name, $this->type->value, gettype($singleValue), false);
                }

                $casted[] = $castedValue;
            }

            return $casted;
        }

        $casted = $this->type->cast($value);
        if ($casted === null) {
            throw new OptionValueErrorException($this->name, $this->type->value, gettype($value), false);
        }

        return $casted;
    }
}
