<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Metadata\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\Contract\DatabaseRefreshExecutorInterface;
use Stateforge\Scenario\Core\Runtime\Application\TestMethodState;
use Stateforge\Scenario\Core\Runtime\Exception\Application\TestMethodFailureException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ConnectionException;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use Stateforge\Scenario\Core\Tests\Unit\TestMethodStateMock;

#[CoversClass(RefreshDatabaseHandler::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(TestMethodState::class)]
#[UsesClass(TestMethodFailureException::class)]
#[UsesClass(ConnectionException::class)]
#[Group('runtime')]
#[Small]
final class RefreshDatabaseHandlerTest extends TestCase
{
    use TestMethodStateMock;

    protected function setUp(): void
    {
        $this->resetTestMethodState();
    }

    protected function tearDown(): void
    {
        $this->resetTestMethodState();
    }

    public function testExecutesConfiguredConnection(): void
    {
        $executor = $this->createMock(DatabaseRefreshExecutorInterface::class);
        $executor->expects(self::once())
            ->method('execute')
            ->with(self::callback(static function (RefreshDatabase $attribute): bool {
                return $attribute->connection === 'main';
            }));

        $handler = new RefreshDatabaseHandler($executor);
        $context = AttributeContext::getInstance(
            self::class,
            'testExecutesConfiguredConnection',
            ExecutionType::Up,
            false,
            null,
        );

        $handler->handle($context, new RefreshDatabase('main'));

        self::assertCount(1, $context->getAudits());
        self::assertStringContainsString('{"connection":"main"}', $context->getAudits()[0]);
    }

    public function testDryRunDoesNotExecuteConnection(): void
    {
        $executor = $this->createMock(DatabaseRefreshExecutorInterface::class);
        $executor->expects(self::never())
            ->method('execute');

        $handler = new RefreshDatabaseHandler($executor);
        $context = AttributeContext::getInstance(
            self::class,
            'testDryRunDoesNotExecuteConnection',
            ExecutionType::Up,
            true,
            null,
        );

        $handler->handle($context, new RefreshDatabase('main'));

        self::assertCount(1, $context->getAudits());
        self::assertStringContainsString('{"connection":"main"}', $context->getAudits()[0]);
    }

    public function testMissingConnectionRegistersFailure(): void
    {
        $executor = $this->createMock(DatabaseRefreshExecutorInterface::class);
        $executor->expects(self::once())
            ->method('execute')
            ->willThrowException(new ConnectionException('missing'));

        $handler = new RefreshDatabaseHandler($executor);
        $context = AttributeContext::getInstance(
            self::class,
            'testMissingConnectionRegistersFailure',
            ExecutionType::Up,
            false,
            null,
        );

        $handler->handle($context, new RefreshDatabase('missing'));

        $failure = (new TestMethodState())->failure($context->class, $context->method ?? '');
        self::assertInstanceOf(TestMethodFailureException::class, $failure);
        self::assertInstanceOf(ConnectionException::class, $failure->getPrevious());
        self::assertCount(1, $context->getAudits());
        self::assertStringContainsString('{"connection":"missing"}', $context->getAudits()[0]);
    }
}
