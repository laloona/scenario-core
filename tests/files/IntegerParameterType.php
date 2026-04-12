<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Files;

use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;

final class IntegerParameterType extends ParameterTypeDefinition
{
    public function cast(mixed $value): int|null
    {
        return $this->getValueType($value)->value;
    }

    protected function getValue(): string
    {
        return 'test-parameter';
    }

    protected function getValueType(mixed $value): IntegerType
    {
        return new IntegerType($value);
    }
}
