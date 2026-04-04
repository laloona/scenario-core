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
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\SwitchDryRunAttributeContextException;

#[CoversClass(SwitchDryRunAttributeContextException::class)]
#[Group('runtime')]
#[Small]
final class SwitchDryRunAttributeContextExceptionTest extends TestCase
{
    public function testExceptionContainsMessageToDryRun(): void
    {
        $exception = new SwitchDryRunAttributeContextException(
            true,
        );

        self::assertSame(
            'context switch not allowed, found switch from regular to dryRun',
            $exception->getMessage(),
        );
    }

    public function testExceptionContainsMessageFromDryRun(): void
    {
        $exception = new SwitchDryRunAttributeContextException(
            false,
        );

        self::assertSame(
            'context switch not allowed, found switch from dryRun to regular',
            $exception->getMessage(),
        );
    }
}
