<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\Exception\MissingRequiredArgumentsException;

#[CoversClass(MissingRequiredArgumentsException::class)]
#[Group('runtime')]
#[Small]
final class MissingRequiredArgumentsExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new MissingRequiredArgumentsException(
            ['arg1', 'arg2'],
        );

        self::assertSame(
            'required arguments [arg1, arg2] are missing',
            $exception->getMessage(),
        );
    }
}
