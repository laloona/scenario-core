<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Attribute;

use Attribute;
use Stateforge\Scenario\Core\ParameterTypeCondition as BaseParameterTypeCondition;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\InvalidParameterTypeConditionException;
use function is_subclass_of;

#[Attribute(Attribute::TARGET_CLASS)]
final class ParameterTypeCondition
{
    /**
     * @var class-string
     */
    public readonly string $condition;

    /**
     * @param class-string $condition
     */
    public function __construct(
        string $condition,
    ) {
        if (is_subclass_of($condition, BaseParameterTypeCondition::class) === false) {
            throw new InvalidParameterTypeConditionException($condition);
        }

        $this->condition = $condition;
    }
}
