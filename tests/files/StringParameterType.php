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

use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[AsParameterType('string')]
final class StringParameterType extends ParameterTypeDefinition
{
    public function cast(mixed $value): string|null
    {
        return $this->valueType($value)->value;
    }

    protected function valueType(mixed $value): StringType
    {
        return new StringType($value);
    }
}
