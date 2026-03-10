<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Input;

#[CoversClass(Input::class)]
#[Group('console')]
#[Small]
final class InputTest extends TestCase
{
    public function testParsesCommand(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);

        self::assertSame('apply', $input->command());
    }

    public function testParsesArgumentsAfterCommand(): void
    {
        $input = new Input([
            'scenario',
            'apply',
            'Scenario\\MyClass',
            'OtherArgument',
        ]);

        self::assertSame('Scenario\\MyClass', $input->argument('0'));
        self::assertSame('OtherArgument', $input->argument('1'));
        self::assertNull($input->argument('2'));
    }

    public function testParsesOptionWithValue(): void
    {
        $input = new Input([
            'scenario',
            'apply',
            '--file=my File',
        ]);

        self::assertSame('my File', $input->option('file'));
    }

    public function testParsesFlagOptionAsTrue(): void
    {
        $input = new Input([
            'scenario',
            'apply',
            '--force',
        ]);

        self::assertTrue($input->option('force'));
    }

    public function testRemovesOptionsFromArguments(): void
    {
        $input = new Input([
            'scenario',
            '--force',
            'apply',
            '--extend=something',
            'Scenario\\MyClass',
        ]);

        self::assertSame('apply', $input->command());
        self::assertSame('Scenario\\MyClass', $input->argument('0'));
        self::assertNull($input->argument('1'));

        self::assertTrue($input->option('force'));
        self::assertSame('something', $input->option('extend'));
    }

    public function testReturnsNullForUnknownCommandWhenMissing(): void
    {
        $input = new Input([
            'scenario',
        ]);

        self::assertNull($input->command());
    }

    public function testReturnsNullForUnknownArgument(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);

        self::assertNull($input->argument('0'));
    }

    public function testReturnsNullForUnknownOption(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);

        self::assertNull($input->option('unknown'));
    }

    public function testParsesMultipleOptionsAndArgumentsInMixedOrder(): void
    {
        $input = new Input([
            'scenario',
            '--force',
            'apply',
            'Scenario\\MyClass',
            '--down',
            'OtherArgument',
        ]);

        self::assertSame('apply', $input->command());
        self::assertSame('Scenario\\MyClass', $input->argument('0'));
        self::assertSame('OtherArgument', $input->argument('1'));

        self::assertTrue($input->option('force'));
        self::assertTrue($input->option('down'));
    }
}
