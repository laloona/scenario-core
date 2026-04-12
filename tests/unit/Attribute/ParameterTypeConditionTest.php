<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\ParameterTypeCondition;
use Stateforge\Scenario\Core\ParameterTypeCondition as BaseParameterTypeCondition;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\InvalidParameterTypeConditionException;
use Stateforge\Scenario\Core\Tests\Files\MatchingCondition;
use stdClass;

#[CoversClass(ParameterTypeCondition::class)]
#[UsesClass(BaseParameterTypeCondition::class)]
#[UsesClass(InvalidParameterTypeConditionException::class)]
#[Group('attribute')]
#[Small]
final class ParameterTypeConditionTest extends TestCase
{
    public function testConstructStoresValidConditionClass(): void
    {
        $attribute = new ParameterTypeCondition(MatchingCondition::class);

        self::assertSame(MatchingCondition::class, $attribute->condition);
    }

    public function testConstructThrowsForInvalidConditionClass(): void
    {
        $this->expectException(InvalidParameterTypeConditionException::class);
        $this->expectExceptionMessage(
            'given stdClass is not a valid parameter type condition, must be extended from '
            . BaseParameterTypeCondition::class,
        );

        new ParameterTypeCondition(stdClass::class);
    }
}
