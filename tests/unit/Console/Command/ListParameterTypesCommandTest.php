<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\ListParameterTypesCommand;
use Stateforge\Scenario\Core\Console\Input;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\Input\Parser;
use Stateforge\Scenario\Core\Console\Input\Resolver;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\ParameterType;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeRegistry;
use Stateforge\Scenario\Core\Tests\Files\IntegerParameterType;
use Stateforge\Scenario\Core\Tests\Files\StringParameterType;

#[CoversClass(ListParameterTypesCommand::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(AsParameterType::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(Input::class)]
#[UsesClass(Option::class)]
#[UsesClass(Parser::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(ParameterTypeRegistry::class)]
#[UsesClass(Resolver::class)]
#[Group('console')]
#[Small]
final class ListParameterTypesCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);
    }

    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'List all registered parameter types.',
            (new ListParameterTypesCommand())->description(),
        );
    }

    public function testRunOutputsBuiltInParameterTypesWhenNoCustomTypesAreRegistered(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('headline')
            ->with('Built-in parameter types');
        $output->expects(self::once())
            ->method('table')
            ->with(
                ['type', 'description'],
                [
                    [ParameterType::class . '::Boolean', ParameterType::Boolean->value],
                    [ParameterType::class . '::Float', ParameterType::Float->value],
                    [ParameterType::class . '::Integer', ParameterType::Integer->value],
                    [ParameterType::class . '::String', ParameterType::String->value],
                ],
            );
        $output->expects(self::never())->method('warn');

        self::assertSame(Command::Success, (new ListParameterTypesCommand())->run($input, $output));
    }

    public function testRunOutputsBuiltInAndSortedRegisteredParameterTypesTable(): void
    {
        $registry = ParameterTypeRegistry::getInstance();
        $registry->register(StringParameterType::class, new AsParameterType('string'));
        $registry->register(IntegerParameterType::class, new AsParameterType('integer'));

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('warn');
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('headline')
            ->willReturnCallback(function (string $headline) use ($matcher): void {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertSame('Built-in parameter types', $headline),
                    2 => self::assertSame('Registered parameter types', $headline),
                    default => null,
                };
            });
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('table')
            ->willReturnCallback(function (array $headers, array $rows) use ($matcher): void {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        self::assertSame(['type', 'description'], $headers);
                        self::assertSame(
                            [
                                [ParameterType::class . '::Boolean', ParameterType::Boolean->value],
                                [ParameterType::class . '::Float', ParameterType::Float->value],
                                [ParameterType::class . '::Integer', ParameterType::Integer->value],
                                [ParameterType::class . '::String', ParameterType::String->value],
                            ],
                            $rows,
                        );
                        break;
                    case 2:
                        self::assertSame(['class', 'description'], $headers);
                        self::assertSame(
                            [
                                [IntegerParameterType::class, 'integer'],
                                [StringParameterType::class, 'string'],
                            ],
                            $rows,
                        );
                        break;
                }
            });

        self::assertSame(Command::Success, (new ListParameterTypesCommand())->run($input, $output));
    }
}
