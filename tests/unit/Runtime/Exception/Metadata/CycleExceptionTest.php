<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Exception\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Runtime\Exception\Metadata\CycleException;
use Scenario\Core\Runtime\Metadata\ExecutionType;

#[CoversClass(CycleException::class)]
#[Group('runtime')]
#[Small]
final class CycleExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new CycleException(
            'RootScenario',
            'CycleScenario',
            ['A', 'B', 'C'],
            ExecutionType::Up,
        );

        self::assertSame(
            'RootScenario: CycleScenario caused cycle in applied stack [A => B => C] while applying up',
            $exception->getMessage(),
        );
    }
}
