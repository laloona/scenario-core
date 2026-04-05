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
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use function file_put_contents;
use const DIRECTORY_SEPARATOR;

#[CoversClass(ConfigFinder::class)]
#[UsesClass(Application::class)]
#[Group('phpunit')]
#[Small]
final class ConfigFinderTest extends TestCase
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

    public function testReturnsNullWhenNoPhpUnitConfigExists(): void
    {
        self::assertNull((new ConfigFinder())->find());
    }

    public function testPrefersPhpUnitDistXmlWhenPresent(): void
    {
        file_put_contents(Application::getRootDir() . DIRECTORY_SEPARATOR . 'phpunit.dist.xml', '<phpunit/>');
        file_put_contents(Application::getRootDir() . DIRECTORY_SEPARATOR . 'phpunit.xml', '<phpunit/>');

        self::assertSame(Application::getRootDir() . DIRECTORY_SEPARATOR . 'phpunit.dist.xml', (new ConfigFinder())->find());
    }

    public function testFallsBackToPhpUnitXml(): void
    {
        file_put_contents(Application::getRootDir() . DIRECTORY_SEPARATOR . 'phpunit.xml', '<phpunit/>');

        self::assertSame(Application::getRootDir() . DIRECTORY_SEPARATOR . 'phpunit.xml', (new ConfigFinder())->find());
    }
}
