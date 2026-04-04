<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Application\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use UnexpectedValueException;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(ConfigurationFinder::class)]
#[UsesClass(Application::class)]
#[Group('runtime')]
#[Small]
final class ConfigurationFinderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/scenario_test_' . uniqid();
        mkdir($this->tempDir);

        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, $this->tempDir);
    }

    protected function tearDown(): void
    {
        foreach (scandir($this->tempDir) as $file) {
            if ($file !== '.'
                && $file !== '..') {
                unlink($this->tempDir . '/' . $file);
            }
        }

        rmdir($this->tempDir);

        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, null);
    }

    public function testReturnsNullWhenNoConfigurationExists(): void
    {
        self::assertNull((new ConfigurationFinder())->find());
    }

    public function testFindsScenarioXml(): void
    {
        file_put_contents($this->tempDir . '/scenario.xml', '<scenario/>');

        $result = (new ConfigurationFinder())->find();

        self::assertInstanceOf(SplFileInfo::class, $result);
        self::assertSame('scenario.xml', $result->getFilename());
    }

    public function testFindsScenarioDistXml(): void
    {
        file_put_contents($this->tempDir . '/scenario.dist.xml', '<scenario/>');

        $result = (new ConfigurationFinder())->find();

        self::assertInstanceOf(SplFileInfo::class, $result);
        self::assertSame('scenario.dist.xml', $result->getFilename());
    }

    public function testReturnsNullWhenDirectoryIsInvalid(): void
    {
        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, $this->tempDir . '/missing');

        try {
            self::assertNull((new ConfigurationFinder())->find());
        } catch (UnexpectedValueException $exception) {
            self::fail('UnexpectedValueException should be handled internally.');
        }
    }
}
