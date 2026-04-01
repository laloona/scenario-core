<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Exception\Application\ApplicationFailureException;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Tests\Files\AnotherScenario;
use Scenario\Core\Tests\Files\ValidScenario;
use Scenario\Core\Tests\Unit\ApplicationStateMock;
use Scenario\Core\Tests\Unit\AttributeContextMock;
use Scenario\Core\Tests\Unit\HandlerRegistryMock;

#[CoversClass(AttributeProcessor::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(ApplicationFailureException::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(AttributeHandler::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(TestClassState::class)]
#[Group('runtime')]
#[Small]
final class AttributeProcessorTest extends TestCase
{
    use ApplicationStateMock;
    use AttributeContextMock;
    use HandlerRegistryMock;

    protected function setUp(): void
    {
        $this->resetApplicationState();
        $this->resetAttributeContext();
        $this->resetHandlerRegistry();
    }

    protected function tearDown(): void
    {
        $this->resetApplicationState();
        $this->resetAttributeContext();
        $this->resetHandlerRegistry();
    }

    public function testProcessCallsRegisteredHandlerForSupportedReflectionAttribute(): void
    {
        $handler = $this->createPartialMock(AttributeHandler::class, ['attributeName','execute']);
        $handler->expects(self::exactly(4))
            ->method('attributeName')
            ->willReturn(ApplyScenario::class);
        $handler->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (AttributeContext $context, object $metaData) {
                self::assertSame(ValidScenario::class, $context->class);
                self::assertSame('up', $context->method);
                self::assertSame(ExecutionType::Up, $context->executionType);
                self::assertFalse($context->dryRun);
                self::assertInstanceOf(ApplyScenario::class, $metaData);
                self::assertSame(ValidScenario::class, $metaData->id);
                self::assertSame([ 'param' => 'my value' ], $metaData->parameters);
            });

        HandlerRegistry::getInstance()->registerHandler($handler);
        (new AttributeProcessor())->process(
            AttributeContext::getInstance(
                ValidScenario::class,
                null,
                ExecutionType::Down,
                false,
                null,
            ),
            (new ReflectionClass(ValidScenario::class))->getAttributes(),
        );
    }

    public function testProcessAddsClassToApplicationStateForClassUpContextWhenApplicationFailed(): void
    {
        $handler = $this->createPartialMock(AttributeHandler::class, ['attributeName','execute']);
        $handler->expects(self::exactly(4))
            ->method('attributeName')
            ->willReturn(ApplyScenario::class);
        $handler->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (AttributeContext $context, object $metaData) {
                self::assertSame(ValidScenario::class, $context->class);
                self::assertSame('up', $context->method);
                self::assertSame(ExecutionType::Up, $context->executionType);
                self::assertFalse($context->dryRun);
                self::assertInstanceOf(ApplyScenario::class, $metaData);
                self::assertSame(ValidScenario::class, $metaData->id);
                self::assertSame([ 'param' => 'my value' ], $metaData->parameters);
            });

        HandlerRegistry::getInstance()->registerHandler($handler);
        (new ApplicationState())->fail(new RuntimeException('error'));

        (new AttributeProcessor())->process(
            AttributeContext::getInstance(
                ValidScenario::class,
                null,
                ExecutionType::Up,
                false,
                null,
            ),
            (new ReflectionClass(ValidScenario::class))->getAttributes(),
        );

        self::assertInstanceOf(
            ApplicationFailureException::class,
            (new ApplicationState())->failure(ValidScenario::class),
        );
        self::assertNull((new ApplicationState())->failure(AnotherScenario::class));
    }

    public function testProcessIgnoresAttributesWithoutRegisteredHandler(): void
    {
        $handler = $this->createPartialMock(AttributeHandler::class, ['attributeName','execute']);
        $handler->expects(self::exactly(2))
            ->method('attributeName')
            ->willReturn(Parameter::class);
        $handler->expects(self::never())
            ->method('execute');

        HandlerRegistry::getInstance()->registerHandler($handler);

        (new AttributeProcessor())->process(
            AttributeContext::getInstance(
                ValidScenario::class,
                null,
                ExecutionType::Up,
                false,
                null,
            ),
            (new ReflectionClass(ValidScenario::class))->getAttributes(),
        );

        self::assertNull((new ApplicationState())->failure(ValidScenario::class));
    }
}
