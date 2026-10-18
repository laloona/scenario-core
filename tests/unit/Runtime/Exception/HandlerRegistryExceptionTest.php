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
use Stateforge\Scenario\Core\Runtime\Exception\HandlerRegistryException;

#[CoversClass(HandlerRegistryException::class)]
#[Group('runtime')]
#[Small]
final class HandlerRegistryExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new HandlerRegistryException(
            'handler is already registered',
        );

        self::assertSame(
            'handler is already registered',
            $exception->getMessage(),
        );
    }
}
