<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Exception\Application;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Runtime\Exception\Application\TestMethodFailureException;

#[CoversClass(TestMethodFailureException::class)]
#[Group('runtime')]
#[Small]
final class TestMethodFailureExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new TestMethodFailureException(
            'MyScenario::myMethod',
            new Exception('some error'),
        );

        self::assertSame(
            'OnMethod "MyScenario::myMethod" failure: [Exception]: some error',
            $exception->getMessage(),
        );
    }
}
