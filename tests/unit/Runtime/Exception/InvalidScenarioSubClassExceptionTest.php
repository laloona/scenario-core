<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Runtime\Exception\InvalidScenarioSubClassException;
use Scenario\Core\Tests\Files\InvalidScenario;

#[CoversClass(InvalidScenarioSubClassException::class)]
#[Group('runtime')]
#[Small]
final class InvalidScenarioSubClassExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new InvalidScenarioSubClassException(
            InvalidScenario::class,
        );

        self::assertSame(
            InvalidScenario::class . ' is not a subclass of Scenario\Core\Contract\ScenarioInterface',
            $exception->getMessage(),
        );
    }
}
