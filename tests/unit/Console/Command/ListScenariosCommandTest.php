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
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\ListScenariosCommand;
use Stateforge\Scenario\Core\Console\Exception\NotAllowedOptionsException;
use Stateforge\Scenario\Core\Console\Input;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\Input\Parser;
use Stateforge\Scenario\Core\Console\Input\Resolver;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;
use Stateforge\Scenario\Core\Tests\Files\AnotherScenario;
use Stateforge\Scenario\Core\Tests\Files\ValidScenario;
use Stateforge\Scenario\Core\Tests\Unit\ScenarioRegistryMock;

#[CoversClass(ListScenariosCommand::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(Input::class)]
#[UsesClass(NotAllowedOptionsException::class)]
#[UsesClass(Option::class)]
#[UsesClass(Parser::class)]
#[UsesClass(Resolver::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioRegistry::class)]
#[Group('console')]
#[Small]
final class ListScenariosCommandTest extends TestCase
{
    use ScenarioRegistryMock;

    protected function tearDown(): void
    {
        $this->resetScenarioRegistry();
    }

    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'List all available scenarios, use --suite="name of you suite" if you want to see just one suite.',
            (new ListScenariosCommand())->description(),
        );
    }

    public function testRunReturnsErrorWhenResolveRejectsFooOption(): void
    {
        $input = new Input([
            'scenario',
            'list',
            '--foo=bar',
        ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');
        $output->expects(self::once())
            ->method('error');

        self::assertSame(Command::Error, (new ListScenariosCommand())->run($input, $output));
    }

    public function testRunWarnsWhenNoScenariosAreRegistered(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('No scenarios found. Please create one.');
        $output->expects(self::never())->method('headline');
        $output->expects(self::never())->method('table');

        self::assertSame(Command::Success, (new ListScenariosCommand())->run($input, $output));
    }

    public function testRunOutputsGroupedScenarioTablesPerSuite(): void
    {
        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'main',
                ValidScenario::class,
                new AsScenario('first', 'my first scenario in main'),
                [],
            ),
        );
        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'other',
                AnotherScenario::class,
                new AsScenario('second', 'my second scenario in other'),
                [],
            ),
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('headline')
            ->willReturnCallback(function (string $suite) use ($matcher): void {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertSame('other', $suite),
                    2 => self::assertSame('main', $suite),
                    default => null,
                };
            });

        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('table')
            ->willReturnCallback(function (?array $headers, array $rows) use ($matcher): void {
                self::assertSame(['class', 'name', 'description'], $headers);

                match ($matcher->numberOfInvocations()) {
                    1 => self::assertSame(
                        [[AnotherScenario::class, 'second', 'my second scenario in other']],
                        $rows,
                    ),
                    2 => self::assertSame(
                        [[ValidScenario::class, 'first', 'my first scenario in main']],
                        $rows,
                    ),
                    default => null,
                };
            });

        $output->expects(self::never())->method('warn');

        self::assertSame(Command::Success, (new ListScenariosCommand())->run($input, $output));
    }

    public function testRunFiltersScenariosBySuite(): void
    {
        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'main',
                ValidScenario::class,
                new AsScenario('first', 'my first scenario in main'),
                [],
            ),
        );
        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'other',
                AnotherScenario::class,
                new AsScenario('second', 'my second scenario in other'),
                [],
            ),
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['suite', 'other'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('headline')
            ->with('other');
        $output->expects(self::once())
            ->method('table')
            ->with(
                ['class', 'name', 'description'],
                [[AnotherScenario::class, 'second', 'my second scenario in other']],
            );
        $output->expects(self::never())->method('warn');

        self::assertSame(Command::Success, (new ListScenariosCommand())->run($input, $output));
    }
}
