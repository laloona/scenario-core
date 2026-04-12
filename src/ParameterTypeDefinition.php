<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core;

use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;
use function get_class;
use function strrpos;
use function substr;

abstract class ParameterTypeDefinition
{
    public readonly string $name;
    public readonly string $value;

    final public function __construct()
    {
        $pos = strrpos(get_class($this), '\\');
        $this->name = $pos === false
            ? get_class($this)
            : substr(get_class($this), $pos + 1);
        $this->value = get_class($this);
    }

    final public function valid(mixed $value): bool
    {
        return $this->cast($value) !== null;
    }

    final public function asString(mixed $value): string|null
    {
        $value = $this->cast($value);
        if ($value === null) {
            return null;
        }

        return $this->getValueType($value)->asString();
    }

    abstract public function cast(mixed $value): string|int|float|bool|null;

    abstract protected function getValueType(mixed $value): BooleanType|FloatType|IntegerType|StringType;
}
