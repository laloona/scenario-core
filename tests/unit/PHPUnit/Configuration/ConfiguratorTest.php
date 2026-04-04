<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\PHPUnit\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Stateforge\Scenario\Core\PHPUnit\Configuration\Configurator;
use Stateforge\Scenario\Core\PHPUnit\Configuration\Configured;
use Stateforge\Scenario\Core\PHPUnit\Extension;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function substr_count;

#[CoversClass(Configurator::class)]
#[UsesClass(Configured::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(Application::class)]
#[UsesClass(Extension::class)]
#[Group('phpunit')]
#[Small]
final class ConfiguratorTest extends TestCase
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

    public function testConfigureDoesNothingWhenNoConfigExists(): void
    {
        $finder = new ConfigFinder();

        (new Configurator($finder, new Configured($finder)))->configure();

        self::assertFalse(is_file(Application::getRootDir() . '/phpunit.xml'));
        self::assertFalse(is_file(Application::getRootDir() . '/phpunit.dist.xml'));
    }

    public function testConfigureAddsExtensionToPhpUnitXml(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><phpunit></phpunit>');

        $finder = new ConfigFinder();

        (new Configurator($finder, new Configured($finder)))->configure();

        $content = file_get_contents(Application::getRootDir() . '/phpunit.xml');
        self::assertIsString($content);
        self::assertStringContainsString(
            '<bootstrap class="' . Extension::class . '"/>',
            $content,
        );
    }

    public function testConfigureUsesExistingExtensionsNode(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><phpunit><extensions/></phpunit>');

        $finder = new ConfigFinder();

        (new Configurator($finder, new Configured($finder)))->configure();

        $content = file_get_contents(Application::getRootDir() . '/phpunit.xml');
        self::assertIsString($content);
        self::assertStringContainsString('<extensions>', $content);
        self::assertStringContainsString('<bootstrap class="' . Extension::class . '"/>', $content);
    }

    public function testConfigureSkipsWhenExtensionAlreadyExists(): void
    {
        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            '<?xml version="1.0"?><phpunit><extensions><bootstrap class="' . Extension::class . '"/></extensions></phpunit>',
        );

        $finder = new ConfigFinder();

        (new Configurator($finder, new Configured($finder)))->configure();
        $content = file_get_contents(Application::getRootDir() . '/phpunit.xml');

        self::assertIsString($content);
        self::assertSame(1, substr_count($content, Extension::class));
    }

    public function testConfigureDoesNothingWhenPhpUnitNodeIsMissing(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><configuration></configuration>');

        $finder = new ConfigFinder();

        (new Configurator($finder, new Configured($finder)))->configure();

        $content = file_get_contents(Application::getRootDir() . '/phpunit.xml');
        self::assertSame('<?xml version="1.0"?><configuration></configuration>', $content);
    }
}
