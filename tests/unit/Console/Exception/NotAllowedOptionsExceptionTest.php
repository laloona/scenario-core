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
use Scenario\Core\Console\Exception\NotAllowedOptionsException;

#[CoversClass(NotAllowedOptionsException::class)]
#[Group('runtime')]
#[Small]
final class NotAllowedOptionsExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new NotAllowedOptionsException(
            ['not_allowed_a', 'not_allowed_b', 'not_allowed_c'],
            ['allowed_a', 'allowed_b'],
        );

        self::assertSame(
            '[not_allowed_a, not_allowed_b, not_allowed_c] are not allowed, allowed options: allowed_a, allowed_b',
            $exception->getMessage(),
        );
    }
}
