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
use Stateforge\Scenario\Core\Console\Exception\ArgumentValueErrorException;

#[CoversClass(ArgumentValueErrorException::class)]
#[Group('runtime')]
#[Small]
final class ArgumentValueErrorExceptionTest extends TestCase
{
    public function testExceptionContainsMessageWithDefault(): void
    {
        $exception = new ArgumentValueErrorException(
            'my_arg',
            'int',
            'string',
            true,
        );

        self::assertSame(
            'wrong default value for argument my_arg, expected type int but got string',
            $exception->getMessage(),
        );
    }

    public function testExceptionContainsMessageWithoutDefault(): void
    {
        $exception = new ArgumentValueErrorException(
            'my_arg',
            'int',
            'string',
            false,
        );

        self::assertSame(
            'wrong value for argument my_arg, expected type int but got string',
            $exception->getMessage(),
        );
    }
}
