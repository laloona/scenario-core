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
use Stateforge\Scenario\Core\Console\Exception\OptionValueErrorException;

#[CoversClass(OptionValueErrorException::class)]
#[Group('runtime')]
#[Small]
final class OptionValueErrorExceptionTest extends TestCase
{
    public function testExceptionContainsMessageWithDefault(): void
    {
        $exception = new OptionValueErrorException(
            'my_option',
            'int',
            'string',
            true,
        );

        self::assertSame(
            'wrong default value for option my_option, expected type int but got string',
            $exception->getMessage(),
        );
    }

    public function testExceptionContainsMessageWithoutDefault(): void
    {
        $exception = new OptionValueErrorException(
            'my_option',
            'int',
            'string',
            false,
        );

        self::assertSame(
            'wrong value for option my_option, expected type int but got string',
            $exception->getMessage(),
        );
    }
}
