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
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Tests\Files\CustomAttributeHandler;

#[CoversClass(HandlerRegistry::class)]
#[UsesClass(CustomAttributeHandler::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(HandlerRegistryException::class)]
#[UsesClass(RegistryException::class)]
#[Group('runtime')]
#[Small]
final class HandlerRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        $registryInstance = new ReflectionClass(HandlerRegistry::getInstance())->getProperty('instance');
        $registryInstance->setValue(null, null);
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $firstInstance = HandlerRegistry::getInstance();
        $secondInstance = HandlerRegistry::getInstance();

        self::assertSame($firstInstance, $secondInstance);
    }

    public function testRegisterHandlerAndResolveByAttributeName(): void
    {
        $handler = new CustomAttributeHandler();

        $registry = HandlerRegistry::getInstance();
        $registry->registerHandler($handler);

        self::assertSame($handler, $registry->attributeHandler(ApplyScenario::class));
    }

    public function testRegisterHandlerThrowsWhenSameAttributeRegisteredTwice(): void
    {
        $firstHandler = new CustomAttributeHandler();
        $secondHandler = new CustomAttributeHandler();

        $registry = HandlerRegistry::getInstance();
        $registry->registerHandler($firstHandler);

        $this->expectException(HandlerRegistryException::class);
        $this->expectExceptionMessage('Attribute ' . ApplyScenario::class . ' already registered.');

        $registry->registerHandler($secondHandler);
    }

    public function testAttributeHandlerThrowsRegistryExceptionWhenNotFound(): void
    {
        $registry = HandlerRegistry::getInstance();

        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('Attribute ' . AsScenario::class);

        $registry->attributeHandler(AsScenario::class);
    }
}
