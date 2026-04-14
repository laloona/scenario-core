<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ApplicationExtension;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;

#[CoversClass(ApplicationExtension::class)]
#[UsesClass(Application::class)]
#[Group('runtime')]
#[Small]
final class ApplicationExtensionTest extends TestCase
{
    use ApplicationMock;

    protected function setUp(): void
    {
        $this->resetApplication();
        $this->createRootDir();
    }

    protected function tearDown(): void
    {
        $this->resetApplication();
        $this->removeRootDir();
    }

    public function testBootstrapRegistersExtensionAtApplication(): void
    {
        $extension = self::createPartialMock(ApplicationExtension::class, ['prepare', 'boot']);
        $extension->expects(self::never())->method('prepare');
        $extension->expects(self::never())->method('boot');
        $extension->bootstrap();

        $property = (new ReflectionClass(Application::class))->getProperty('extension');
        self::assertSame($extension, $property->getValue());
    }

    public function testAvailablePrepareAndBootMethod(): void
    {
        $extension = new ReflectionClass(ApplicationExtension::class);

        self::assertTrue($extension->getMethod('prepare')->isPublic());
        self::assertFalse($extension->getMethod('prepare')->isFinal());
        self::assertTrue($extension->getMethod('boot')->isPublic());
        self::assertFalse($extension->getMethod('boot')->isFinal());
        self::assertTrue($extension->getMethod('bootstrap')->isPublic());
        self::assertTrue($extension->getMethod('bootstrap')->isFinal());
    }
}
