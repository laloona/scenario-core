<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\PHPUnit\Finder;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Stateforge\Scenario\Core\PHPUnit\Finder\DirectoryFinder;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use function file_put_contents;

#[CoversClass(DirectoryFinder::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(Application::class)]
#[Group('phpunit')]
#[Small]
final class DirectoryFinderTest extends TestCase
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

    public function testReturnEmptyArrayWhenPhpunitXmlDoesNotExist(): void
    {
        self::assertSame([], (new DirectoryFinder(new ConfigFinder()))->all());
    }

    public function testReadsDirectoriesFromPhpunitXml(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', <<<XML
<?xml version="1.0"?>
<phpunit>
  <testsuites>
    <testsuite name="unit">
      <directory>tests/unit/</directory>
      <directory>tests/integration</directory>
    </testsuite>
  </testsuites>
</phpunit>
XML);

        self::assertSame(['tests/unit', 'tests/integration'], (new DirectoryFinder(new ConfigFinder()))->all());
    }

    public function testReturnEmptyArrayFromPhpunitXmlWhenIsInvalid(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '');

        self::assertSame([], (new DirectoryFinder(new ConfigFinder()))->all());
    }

    public function testReturnEmptyArrayFromPhpunitXmlWhenDirectoriesAreMissing(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', <<<XML
<?xml version="1.0"?>
<phpunit>
  <testsuites>
    <testsuite name="unit">
    </testsuite>
  </testsuites>
</phpunit>
XML);

        self::assertSame([], (new DirectoryFinder(new ConfigFinder()))->all());
    }
}
