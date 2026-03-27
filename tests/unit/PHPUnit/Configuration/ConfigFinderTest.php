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
use Scenario\Core\Runtime\Application;
use Scenario\Core\Tests\Unit\ApplicationMock;
use function file_put_contents;

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
        file_put_contents(Application::getRootDir() . '/phpunit.dist.xml', '<phpunit/>');
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<phpunit/>');

        self::assertSame(Application::getRootDir() . '/phpunit.dist.xml', (new ConfigFinder())->find());
    }

    public function testFallsBackToPhpUnitXml(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<phpunit/>');

        self::assertSame(Application::getRootDir() . '/phpunit.xml', (new ConfigFinder())->find());
    }
}
