<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Contract\ScenarioInterface;
use Stateforge\Scenario\Core\Runtime\Exception\WrongScenarioSubclassException;
use Stateforge\Scenario\Core\Tests\Files\InvalidScenario;

#[CoversClass(WrongScenarioSubclassException::class)]
#[Group('runtime')]
#[Small]
final class WrongScenarioSubclassExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new WrongScenarioSubclassException(
            InvalidScenario::class,
            ScenarioInterface::class,
        );

        self::assertSame(
            'Stateforge\Scenario\Core\Tests\Files\InvalidScenario is not from type Stateforge\Scenario\Core\Contract\ScenarioInterface',
            $exception->getMessage(),
        );
    }
}
