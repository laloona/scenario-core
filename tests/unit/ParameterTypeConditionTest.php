<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Stateforge\Scenario\Core\ParameterTypeCondition;
use Stateforge\Scenario\Core\Tests\Files\MatchingCondition;

#[CoversClass(ParameterTypeCondition::class)]
#[Group('runtime')]
#[Small]
final class ParameterTypeConditionTest extends TestCase
{
    public function testMatchesCanBeImplementedBySubclass(): void
    {
        self::assertTrue((new MatchingCondition())->matches());
    }

    public function testConstructMethodIsFinal(): void
    {
        self::assertTrue((new ReflectionMethod(ParameterTypeCondition::class, '__construct'))->isFinal());
    }
}
