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
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Console\Command\CliCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Console\Command\RefreshDatabaseCommand;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Exception\Application\TestMethodFailureException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Tests\Unit\AttributeContextMock;
use Scenario\Core\Tests\Unit\HandlerRegistryMock;
use Scenario\Core\Tests\Unit\TestMethodStateMock;

#[CoversClass(RefreshDatabaseCommand::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(AttributeHandler::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(TestMethodState::class)]
#[UsesClass(TestMethodFailureException::class)]
#[Group('console')]
#[Small]
final class RefreshDatabaseCommandTest extends TestCase
{
    use AttributeContextMock;
    use HandlerRegistryMock;
    use TestMethodStateMock;

    protected function setUp(): void
    {
        $this->resetAttributeContext();
        $this->resetHandlerRegistry();
        $this->resetTestMethodState();
    }

    protected function tearDown(): void
    {
        $this->resetAttributeContext();
        $this->resetHandlerRegistry();
        $this->resetTestMethodState();
    }

    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'Executes the database refresh. Use --connection="connection_name" to specify given connection.',
            (new RefreshDatabaseCommand())->description(),
        );
    }

    public function testRunCallsRefreshDatabaseHandlerWithConnectionAndReturnsSuccess(): void
    {
        $handler = $this->createMock(AttributeHandler::class);
        $handler->expects(self::exactly(3))
            ->method('attributeName')
            ->willReturn(RefreshDatabase::class);
        $handler->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (AttributeContext $context, object $metaData) {
                self::assertSame(RefreshDatabaseCommand::class, $context->class);
                self::assertSame(RefreshDatabaseCommand::class . '::execute', $context->method);
                self::assertSame(ExecutionType::Up, $context->executionType);
                self::assertFalse($context->dryRun);
                self::assertInstanceOf(RefreshDatabase::class, $metaData);
                self::assertSame('default', $metaData->connection);
            });

        HandlerRegistry::getInstance()->registerHandler($handler);

        $input = $this->createMock(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['connection', 'default'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('success')
            ->with('Refresh executed.');
        $output->expects(self::never())
            ->method('error');

        (new RefreshDatabaseCommand())->run($input, $output);
    }

    public function testRunCallsRefreshDatabaseHandlerWithoutConnectionAndReturnsSuccess(): void
    {
        $handler = $this->createMock(AttributeHandler::class);
        $handler->expects(self::exactly(3))
            ->method('attributeName')
            ->willReturn(RefreshDatabase::class);
        $handler->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (AttributeContext $context, object $metaData) {
                self::assertSame(RefreshDatabaseCommand::class, $context->class);
                self::assertSame(RefreshDatabaseCommand::class . '::execute', $context->method);
                self::assertSame(ExecutionType::Up, $context->executionType);
                self::assertFalse($context->dryRun);
                self::assertInstanceOf(RefreshDatabase::class, $metaData);
                self::assertSame('default', $metaData->connection);
            });

        HandlerRegistry::getInstance()->registerHandler($handler);

        $input = $this->createMock(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('success')
            ->with('Refresh executed.');
        $output->expects(self::never())
            ->method('error');

        (new RefreshDatabaseCommand())->run($input, $output);
    }
}
