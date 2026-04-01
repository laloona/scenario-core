<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Scenario\Core\Tests\Unit\TestMethodStateMock;
use stdClass;

#[CoversClass(AttributeHandler::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(TestMethodState::class)]
#[Group('runtime')]
#[Small]
final class AttributeHandlerTest extends TestCase
{
    use TestMethodStateMock;

    protected function tearDown(): void
    {
        $this->resetTestMethodState();
    }

    public function testSupportsReturnsAttributeNameAndMatches(): void
    {
        $handler = self::createStub(AttributeHandler::class);
        $handler->method('attributeName')->willReturn(ApplyScenario::class);

        self::assertSame(ApplyScenario::class, $handler->supports(null));
        self::assertTrue($handler->supports(ApplyScenario::class));
        self::assertFalse($handler->supports(stdClass::class));
    }

    public function testHandleIgnoresUnsupportedMetaData(): void
    {
        $handler = self::createStub(AttributeHandler::class);
        $handler->method('attributeName')->willReturn(ApplyScenario::class);

        $context = AttributeContext::getInstance(
            self::class,
            'testHandleIgnoresUnsupportedMetaData',
            ExecutionType::Up,
            false,
            null,
        );

        $handler->handle($context, new stdClass());

        self::assertNull((new TestMethodState())->failure($context->class, $context->method ?? ''));
    }
}
