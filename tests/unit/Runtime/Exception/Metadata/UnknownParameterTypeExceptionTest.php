<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Exception\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\UnknownParameterTypeException;

#[CoversClass(UnknownParameterTypeException::class)]
#[Group('runtime')]
#[Small]
final class UnknownParameterTypeExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new UnknownParameterTypeException(
            'uuid',
        );

        self::assertSame(
            'parameter type uuid is not registered',
            $exception->getMessage(),
        );
    }
}
