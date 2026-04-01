<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Scenario\Core\Console\Command\CliCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Exception\Application\ApplicationFailureException;
use Scenario\Core\Tests\Unit\ApplicationStateMock;

#[CoversClass(CliCommand::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(ApplicationFailureException::class)]
#[UsesClass(Command::class)]
#[UsesClass(Option::class)]
#[Group('console')]
#[Small]
final class CliCommandTest extends TestCase
{
    use ApplicationStateMock;

    protected function setUp(): void
    {
        $this->resetApplicationState();
    }

    protected function tearDown(): void
    {
        $this->resetApplicationState();
    }

    public function testRunExecutesImmediatelyWhenQuietOptionIsTrue(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('warn');
        $output->expects(self::never())->method('confirm');

        $command = $this->createPartialMock(CliCommand::class, [ 'execute', 'description' ]);
        $command->expects(self::once())
            ->method('execute')
            ->willReturn(Command::Success);
        $command->expects(self::never())
            ->method('description');

        self::assertSame(Command::Success, $command->run($input, $output));
    }

    public function testRunReturnsErrorWhenUserDoesNotConfirm(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', false],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $output->expects(self::once())
            ->method('confirm')
            ->with('Do you want to continue?', false)
            ->willReturn(false);

        $command = $this->createPartialMock(CliCommand::class, [ 'execute', 'description' ]);
        $command->expects(self::never())
            ->method('execute');
        $command->expects(self::never())
            ->method('description');

        self::assertSame(Command::Error, $command->run($input, $output));
    }

    public function testRunExecutesAfterConfirmation(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', false],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())->method('warn');
        $output->expects(self::once())
            ->method('confirm')
            ->with('Do you want to continue?', false)
            ->willReturn(true);

        $command = $this->createPartialMock(CliCommand::class, [ 'execute', 'description' ]);
        $command->expects(self::once())
            ->method('execute')
            ->willReturn(Command::Success);
        $command->expects(self::never())
            ->method('description');

        self::assertSame(Command::Success, $command->run($input, $output));
    }

    public function testRunReturnsErrorWhenApplicationStateAlreadyFailed(): void
    {
        (new ApplicationState())->fail(new RuntimeException('boot failed'));

        $input = self::createStub(CliInput::class);
        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Exception was thrown: Scenario application failure'));

        $command = $this->createPartialMock(CliCommand::class, [ 'execute', 'description' ]);
        $command->expects(self::never())
            ->method('execute');
        $command->expects(self::never())
            ->method('description');

        self::assertSame(Command::Error, $command->run($input, $output));
    }

    public function testRunReturnsErrorWhenExecuteThrows(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Exception was thrown: execute failed'));

        $command = $this->createPartialMock(CliCommand::class, [ 'execute', 'description' ]);
        $command->expects(self::once())
            ->method('execute')
            ->willThrowException(new RuntimeException('execute failed'));
        $command->expects(self::never())
            ->method('description');

        self::assertSame(Command::Error, $command->run($input, $output));
    }
}
