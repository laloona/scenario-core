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
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\ListCommands;
use Stateforge\Scenario\Core\Console\Exception\NotAllowedOptionsException;
use Stateforge\Scenario\Core\Console\Input;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\Input\Parser;
use Stateforge\Scenario\Core\Console\Input\Resolver;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;

#[CoversClass(ListCommands::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(Input::class)]
#[UsesClass(NotAllowedOptionsException::class)]
#[UsesClass(Option::class)]
#[UsesClass(Parser::class)]
#[UsesClass(Resolver::class)]
#[Group('console')]
#[Small]
final class ListCommandsTest extends TestCase
{
    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'List all available commands',
            (new ListCommands([]))->description(),
        );
    }

    public function testRunReturnsErrorWhenResolveRejectsFooOption(): void
    {
        $input = new Input([
            'scenario',
            '--foo=bar',
        ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');
        $output->expects(self::once())
            ->method('error');

        self::assertSame(Command::Error, (new ListCommands([]))->run($input, $output));
    }

    public function testRunListsAllDefinedCommands(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('command')->willReturn(null);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('writeln')
            ->with('');
        $output->expects(self::never())
            ->method('error');
        $output->expects(self::once())
            ->method('headline')
            ->with('available commands');
        $output->expects(self::once())
            ->method('table')
            ->with(
                null,
                [
                    ['first', 'first command'],
                    ['second', 'second command'],
                ],
                null,
                false,
            );

        $first = $this->createMock(CliCommand::class);
        $first->expects(self::once())
            ->method('description')
            ->willReturn('first command');
        $second = $this->createMock(CliCommand::class);
        $second->expects(self::once())
            ->method('description')
            ->willReturn('second command');

        $result = (new ListCommands([
            'first' => $first,
            'second' => $second,
        ]))->run($input, $output);

        self::assertSame(Command::Success, $result);
    }

    public function testRunShowsUnknownCommandErrorBeforePrintingListOfCommands(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('command')->willReturn('unknown');

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::exactly(2))
            ->method('writeln')
            ->with('');
        $output->expects(self::once())
            ->method('error')
            ->with('The command "unknown" is unknown.');
        $output->expects(self::once())
            ->method('headline')
            ->with('available commands');
        $output->expects(self::once())
            ->method('table')
            ->with(
                null,
                [
                    ['first', 'first command'],
                ],
                null,
                false,
            );

        $first = $this->createMock(CliCommand::class);
        $first->expects(self::once())
            ->method('description')
            ->willReturn('first command');

        $result = (new ListCommands([
            'first' => $first,
        ]))->run($input, $output);

        self::assertSame(Command::Success, $result);
    }
}
