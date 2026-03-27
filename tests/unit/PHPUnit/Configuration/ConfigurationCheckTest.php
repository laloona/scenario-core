<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\PHPUnit\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Scenario\Core\PHPUnit\Configuration\ConfigurationCheck;
use Scenario\Core\PHPUnit\Extension;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Tests\Unit\ApplicationMock;
use function file_put_contents;

#[CoversClass(ConfigurationCheck::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(Application::class)]
#[UsesClass(Extension::class)]
#[Group('phpunit')]
#[Small]
final class ConfigurationCheckTest extends TestCase
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

    public function testIsConfiguredReturnsFalseWhenNoConfigExists(): void
    {
        self::assertFalse((new ConfigurationCheck(new ConfigFinder()))->isConfigured());
    }

    public function testIsConfiguredReturnsFalseWhenExtensionIsMissing(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><phpunit><extensions/></phpunit>');

        self::assertFalse((new ConfigurationCheck(new ConfigFinder()))->isConfigured());
    }

    public function testIsConfiguredReturnsTrueWhenExtensionExists(): void
    {
        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            '<?xml version="1.0"?><phpunit><extensions><bootstrap class="' . Extension::class . '"/></extensions></phpunit>',
        );

        self::assertTrue((new ConfigurationCheck(new ConfigFinder()))->isConfigured());
    }

    public function testIsConfiguredUsesPhpUnitDistXmlWhenPresent(): void
    {
        file_put_contents(
            Application::getRootDir() . '/phpunit.dist.xml',
            '<?xml version="1.0"?><phpunit><extensions><bootstrap class="' . Extension::class . '"/></extensions></phpunit>',
        );

        self::assertTrue((new ConfigurationCheck(new ConfigFinder()))->isConfigured());
    }
}
