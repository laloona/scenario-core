<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Input\Validate;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\Input\Validate\ClassNameValidation;

#[CoversClass(ClassNameValidation::class)]
#[Group('console')]
#[Small]
final class ClassNameValidationTest extends TestCase
{
    public function testValidateReturnsTrueForValidClassNames(): void
    {
        self::assertTrue(ClassNameValidation::validate('MyClass'));
        self::assertTrue(ClassNameValidation::validate('_InternalClass'));
        self::assertTrue(ClassNameValidation::validate('Class123'));
    }

    public function testValidateReturnsFalseForInvalidClassNames(): void
    {
        self::assertFalse(ClassNameValidation::validate('123Class'));
        self::assertFalse(ClassNameValidation::validate('My-Class'));
        self::assertFalse(ClassNameValidation::validate('My Class'));
        self::assertFalse(ClassNameValidation::validate(''));
    }
}
