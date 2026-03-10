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
use RuntimeException;
use Scenario\Core\Runtime\Exception\ApplicationFailureException;

#[CoversClass(ApplicationFailureException::class)]
#[Group('runtime')]
#[Small]
final class ApplicationFailureExceptionTest extends TestCase
{
    public function testBuildsMessageCodeAndPreviousThrowable(): void
    {
        $previous = new RuntimeException('some error happened', 666);
        $exception = new ApplicationFailureException($previous);

        self::assertSame(
            'Scenario application failure: [RuntimeException]: some error happened',
            $exception->getMessage(),
        );
        self::assertSame(666, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
