<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Input;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\Exception\MissingRequiredArgumentsException;
use Stateforge\Scenario\Core\Console\Exception\MissingRequiredOptionsException;
use Stateforge\Scenario\Core\Console\Exception\NotAllowedArgumentsException;
use Stateforge\Scenario\Core\Console\Exception\NotAllowedOptionsException;
use Stateforge\Scenario\Core\Console\Input\Argument;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\Input\Parser;
use Stateforge\Scenario\Core\Console\Input\Resolver;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(Resolver::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(Argument::class)]
#[UsesClass(Option::class)]
#[UsesClass(InputType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(MissingRequiredArgumentsException::class)]
#[UsesClass(MissingRequiredOptionsException::class)]
#[UsesClass(NotAllowedArgumentsException::class)]
#[UsesClass(NotAllowedOptionsException::class)]
#[UsesClass(Parser::class)]
#[UsesClass(StringType::class)]
#[Group('console')]
#[Small]
final class ResolverTest extends TestCase
{
    public function testResolveArgumentsCastsDefinedArguments(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
            'Scenario\\Demo',
            '10',
        ]));
        $resolver->defineArgument(new Argument('scenario', InputType::String, true));
        $resolver->defineArgument(new Argument('limit', InputType::Integer, true));

        self::assertSame([
            'scenario' => 'Scenario\\Demo',
            'limit' => 10,
        ], $resolver->resolveArguments());
    }

    public function testResolveOptionsCastsDefinedOptions(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
            '--down',
            '--tag=first',
            '--tag=second=value',
        ]));
        $resolver->defineOption(new Option('down', InputType::Boolean));
        $resolver->defineOption(new Option('tag', InputType::String, false, true));

        self::assertSame([
            'down' => true,
            'tag' => ['first', 'second=value'],
        ], $resolver->resolveOptions());
    }

    public function testResolveOptionsCastsDefinedOptionsWithRepeatableButOnlyOneGiven(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
            '--down',
            '--tag=first',
        ]));
        $resolver->defineOption(new Option('down', InputType::Boolean));
        $resolver->defineOption(new Option('tag', InputType::String, false, true));

        self::assertSame([
            'down' => true,
            'tag' => ['first'],
        ], $resolver->resolveOptions());
    }

    public function testResolveArgumentsThrowsForMissingRequiredArgument(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
        ]));
        $resolver->defineArgument(new Argument('scenario', InputType::String, true));

        $this->expectException(MissingRequiredArgumentsException::class);
        $resolver->resolveArguments();
    }

    public function testResolveArgumentsThrowsForUnexpectedArguments(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
            'Scenario\\Demo',
            'extra',
        ]));
        $resolver->defineArgument(new Argument('scenario', InputType::String, true));

        $this->expectException(NotAllowedArgumentsException::class);
        $resolver->resolveArguments();
    }

    public function testResolveOptionsThrowsForUnexpectedOptions(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
            '--unknown',
        ]));

        $this->expectException(NotAllowedOptionsException::class);
        $resolver->resolveOptions();
    }

    public function testResolveOptionsThrowsForMissingRequiredOption(): void
    {
        $resolver = new Resolver(new Parser([
            'scenario',
            'apply',
        ]));
        $resolver->defineOption(new Option('file', InputType::String, true));

        $this->expectException(MissingRequiredOptionsException::class);
        $resolver->resolveOptions();
    }
}
