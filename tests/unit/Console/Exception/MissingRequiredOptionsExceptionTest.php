<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Exception\MissingRequiredOptionsException;

#[CoversClass(MissingRequiredOptionsException::class)]
#[Group('runtime')]
#[Small]
final class MissingRequiredOptionsExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new MissingRequiredOptionsException(
            ['option1', 'option2'],
        );

        self::assertSame(
            'required options [option1, option2] are missing',
            $exception->getMessage(),
        );
    }
}
