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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Exception\MissingRequiredArgumentsException;
use Scenario\Core\Console\Exception\NotAllowedArgumentsException;
use Scenario\Core\Console\Exception\NotAllowedOptionsException;
use Scenario\Core\Console\Exception\UndefinedArgumentException;
use Scenario\Core\Console\Exception\UndefinedOptionException;
use Scenario\Core\Console\Input;
use Scenario\Core\Console\Input\Argument;
use Scenario\Core\Console\Input\InputType;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Console\Input\Parser;
use Scenario\Core\Console\Input\Resolver;
use Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(Input::class)]
#[UsesClass(Argument::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(InputType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(MissingRequiredArgumentsException::class)]
#[UsesClass(NotAllowedArgumentsException::class)]
#[UsesClass(NotAllowedOptionsException::class)]
#[UsesClass(Option::class)]
#[UsesClass(Parser::class)]
#[UsesClass(Resolver::class)]
#[UsesClass(StringType::class)]
#[UsesClass(UndefinedArgumentException::class)]
#[UsesClass(UndefinedOptionException::class)]
#[Group('console')]
#[Small]
final class InputTest extends TestCase
{
    public function testReturnsCommandWithoutResolving(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);

        self::assertSame('apply', $input->command());
    }

    public function testReturnsForceFlagWithoutResolving(): void
    {
        $input = new Input([
            'scenario',
            '--force',
            'apply',
        ]);

        self::assertTrue($input->force());
    }

    public function testResolvesDefinedArgumentsAndOptions(): void
    {
        $input = new Input([
            'scenario',
            '--down=true',
            '--file=my File',
            'apply',
            'Scenario\\MyClass',
            '10',
        ]);
        $input->defineArgument(new Argument('scenario', InputType::String, true));
        $input->defineArgument(new Argument('limit', InputType::Integer, true));
        $input->defineOption(new Option('down', InputType::Boolean));
        $input->defineOption(new Option('file', InputType::String));

        $input->resolve();

        self::assertSame('Scenario\\MyClass', $input->argument('scenario'));
        self::assertSame(10, $input->argument('limit'));
        self::assertTrue($input->option('down'));
        self::assertSame('my File', $input->option('file'));
    }

    public function testThrowsForUndefinedArgumentAfterResolve(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);
        $input->resolve();

        $this->expectException(UndefinedArgumentException::class);
        $input->argument('missing');
    }

    public function testThrowsForUndefinedOptionAfterResolve(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);
        $input->resolve();

        $this->expectException(UndefinedOptionException::class);
        $input->option('missing');
    }

    public function testThrowsWhenRequiredArgumentIsMissing(): void
    {
        $input = new Input([
            'scenario',
            'apply',
        ]);
        $input->defineArgument(new Argument('scenario', InputType::String, true));

        $this->expectException(MissingRequiredArgumentsException::class);
        $input->resolve();
    }

    public function testThrowsWhenAdditionalArgumentsArePassed(): void
    {
        $input = new Input([
            'scenario',
            'apply',
            'Scenario\\MyClass',
            'extra',
        ]);
        $input->defineArgument(new Argument('scenario', InputType::String, true));

        $this->expectException(NotAllowedArgumentsException::class);
        $input->resolve();
    }

    public function testThrowsWhenAdditionalOptionsArePassed(): void
    {
        $input = new Input([
            'scenario',
            'apply',
            '--unknown=true',
        ]);

        $this->expectException(NotAllowedOptionsException::class);
        $input->resolve();
    }
}
