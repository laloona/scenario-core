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
use Stateforge\Scenario\Core\Runtime\Exception\NotAllowedParametersException;

#[CoversClass(NotAllowedParametersException::class)]
#[Group('runtime')]
#[Small]
final class NotAllowedParametersExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new NotAllowedParametersException(
            ['not_allowed_a', 'not_allowed_b', 'not_allowed_c'],
            ['allowed_a', 'allowed_b'],
        );

        self::assertSame(
            '[not_allowed_a, not_allowed_b, not_allowed_c] are not allowed, allowed parameters: allowed_a, allowed_b',
            $exception->getMessage(),
        );
    }
}
