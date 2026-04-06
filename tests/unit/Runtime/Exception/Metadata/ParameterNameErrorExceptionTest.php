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
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterNameErrorException;

#[CoversClass(ParameterNameErrorException::class)]
#[Group('runtime')]
#[Small]
final class ParameterNameErrorExceptionTest extends TestCase
{
    public function testExceptionContainsMessage(): void
    {
        $exception = new ParameterNameErrorException(
            'my_param',
        );

        self::assertSame(
            'invalid parameter name "my_param". Use snake_case, kebab-case (lowercase alphanumeric, "_" or "-") or camelCase',
            $exception->getMessage(),
        );
    }
}
