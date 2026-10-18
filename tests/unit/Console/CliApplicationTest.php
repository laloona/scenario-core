<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\CliApplication;
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\DebugScenarioCommand;
use Stateforge\Scenario\Core\Console\Input;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\Input\Parser;
use Stateforge\Scenario\Core\Console\Input\Resolver;
use Stateforge\Scenario\Core\Console\Output;
use Stateforge\Scenario\Core\Console\Output\SystemTerminal;
use Stateforge\Scenario\Core\Console\Output\Theme\AnsiStyler;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationStateMock;
use Stateforge\Scenario\Core\Tests\Unit\HandlerRegistryMock;

#[CoversClass(CliApplication::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(ApplyScenarioHandler::class)]
#[UsesClass(AttributeHandler::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(DebugScenarioCommand::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(Input::class)]
#[UsesClass(InputType::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(Option::class)]
#[UsesClass(Output::class)]
#[UsesClass(Parser::class)]
#[UsesClass(RefreshDatabaseHandler::class)]
#[UsesClass(Resolver::class)]
#[UsesClass(SystemTerminal::class)]
#[Group('console')]
#[Small]
final class CliApplicationTest extends TestCase
{
    use ApplicationMock;
    use ApplicationStateMock;
    use HandlerRegistryMock;

    protected function setUp(): void
    {
        $this->resetApplication();
        $this->resetApplicationState();
        $this->resetHandlerRegistry();
        $this->setConfiguration(new LoadedConfiguration(new DefaultConfiguration()));
    }

    protected function tearDown(): void
    {
        $this->resetApplication();
        $this->resetApplicationState();
        $this->resetHandlerRegistry();
    }

    public function testCommandRegistersCustomCommandThatRunExecutes(): void
    {
        $command = $this->createPartialMock(CliCommand::class, ['execute', 'description']);
        $command->expects(self::once())
            ->method('execute')
            ->with(self::isInstanceOf(CliInput::class), self::isInstanceOf(CliOutput::class))
            ->willReturn(Command::Success);
        $command->expects(self::never())->method('description');

        $other = $this->createPartialMock(CliCommand::class, ['execute', 'description']);
        $other->expects(self::never())->method('execute');
        $other->expects(self::never())->method('description');

        $application = new CliApplication();
        $application->command('custom', $command);
        $application->command('other', $other);

        self::assertSame(Command::Success->value, $application->run(['scenario', 'custom', '--quiet', '--force']));
    }

    public function testCommandReplacesPreviouslyRegisteredCommandWithSameName(): void
    {
        $original = $this->createPartialMock(CliCommand::class, ['execute', 'description']);
        $original->expects(self::never())->method('execute');
        $original->expects(self::never())->method('description');

        $replacement = $this->createPartialMock(CliCommand::class, ['execute', 'description']);
        $replacement->expects(self::once())->method('execute')->willReturn(Command::Error);
        $replacement->expects(self::never())->method('description');

        $application = new CliApplication();
        $application->command('custom', $original);
        $application->command('custom', $replacement);

        self::assertSame(Command::Error->value, $application->run(['scenario', 'custom', '--quiet', '--force']));
    }
}
