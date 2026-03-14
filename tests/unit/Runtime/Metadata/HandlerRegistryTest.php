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
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Tests\Unit\HandlerRegistryMock;

#[CoversClass(HandlerRegistry::class)]
#[UsesClass(AttributeHandler::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(HandlerRegistryException::class)]
#[UsesClass(RegistryException::class)]
#[Group('runtime')]
#[Small]
final class HandlerRegistryTest extends TestCase
{
    use HandlerRegistryMock;

    protected function tearDown(): void
    {
        $this->resetHandlerRegistry();
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $firstInstance = HandlerRegistry::getInstance();
        $secondInstance = HandlerRegistry::getInstance();

        self::assertSame($firstInstance, $secondInstance);
    }

    public function testRegisterHandlerAndResolveByAttributeName(): void
    {
        $handler = $this->getAttributeHandler();

        $registry = HandlerRegistry::getInstance();
        $registry->registerHandler($handler);

        self::assertSame($handler, $registry->attributeHandler(ApplyScenario::class));
    }

    public function testRegisterHandlerThrowsWhenSameAttributeRegisteredTwice(): void
    {
        $firstHandler = $this->getAttributeHandler();
        $secondHandler = $this->getAttributeHandler();

        $registry = HandlerRegistry::getInstance();
        $registry->registerHandler($firstHandler);

        $this->expectException(HandlerRegistryException::class);
        $this->expectExceptionMessage('Attribute ' . ApplyScenario::class . ' already registered.');

        $registry->registerHandler($secondHandler);
    }

    public function testAttributeHandlerThrowsRegistryExceptionWhenNotFound(): void
    {
        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('Attribute ' . AsScenario::class);

        HandlerRegistry::getInstance()->attributeHandler(AsScenario::class);
    }

    private function getAttributeHandler(): AttributeHandler
    {
        $handler = self::createStub(AttributeHandler::class);
        $handler->method('attributeName')->willReturn(ApplyScenario::class);

        return $handler;
    }
}
