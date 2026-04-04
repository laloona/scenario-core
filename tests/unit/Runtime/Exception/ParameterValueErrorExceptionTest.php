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
use Stateforge\Scenario\Core\Runtime\Exception\ParameterValueErrorException;

#[CoversClass(ParameterValueErrorException::class)]
#[Group('runtime')]
#[Small]
final class ParameterValueErrorExceptionTest extends TestCase
{
    public function testExceptionContainsMessageWithDefault(): void
    {
        $exception = new ParameterValueErrorException(
            'my_param',
            'int',
            'string',
            true,
        );

        self::assertSame(
            'wrong default value for parameter my_param, expected type int but got string',
            $exception->getMessage(),
        );
    }

    public function testExceptionContainsMessageWithoutDefault(): void
    {
        $exception = new ParameterValueErrorException(
            'my_param',
            'int',
            'string',
            false,
        );

        self::assertSame(
            'wrong value for parameter my_param, expected type int but got string',
            $exception->getMessage(),
        );
    }
}
