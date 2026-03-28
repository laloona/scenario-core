<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\PHPUnit\Finder;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Scenario\Core\PHPUnit\Finder\DirectoryFinder;
use Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\ClassFinder;
use Scenario\Core\Tests\Unit\ApplicationMock;
use function file_put_contents;
use function ksort;
use function mkdir;
use function uniqid;

#[CoversClass(ScenarioTestFinder::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(ClassFinder::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(DirectoryFinder::class)]
#[UsesClass(RefreshDatabase::class)]
#[Group('phpunit')]
#[Small]
final class ScenarioTestFinderTest extends TestCase
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

    public function testFindsConcreteTestCasesUsingScenarioAttributes(): void
    {
        mkdir(Application::getRootDir() . '/tests/unit', 0777, true);

        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            <<<XML
<?xml version="1.0"?>
<phpunit>
  <testsuites>
    <testsuite name="unit">
      <directory>tests/unit</directory>
    </testsuite>
  </testsuites>
</phpunit>
XML
        );

        $suffix = 'Fixture' . uniqid();

        file_put_contents(Application::getRootDir() . '/tests/unit/ClassLevelScenarioTest.php', <<<PHP
<?php declare(strict_types=1);

namespace Scenario\\Core\\Tests\\Fixtures\\{$suffix};

use PHPUnit\\Framework\\TestCase;
use Scenario\\Core\\Attribute\\ApplyScenario;

#[ApplyScenario('class-level')]
final class ClassLevelScenarioTest extends TestCase
{
    public function testItRuns(): void
    {
    }
}
PHP);

        file_put_contents(Application::getRootDir() . '/tests/unit/MethodLevelScenarioTest.php', <<<PHP
<?php declare(strict_types=1);

namespace Scenario\\Core\\Tests\\Fixtures\\{$suffix};

use PHPUnit\\Framework\\TestCase;
use Scenario\\Core\\Attribute\\ApplyScenario;
use Scenario\\Core\\Attribute\\RefreshDatabase;

final class MethodLevelScenarioTest extends TestCase
{
    #[RefreshDatabase]
    public function testRefreshesDatabase(): void
    {
    }

    #[ApplyScenario('another')]
    public function testAppliesScenario(): void
    {
    }
}
PHP);

        file_put_contents(Application::getRootDir() . '/tests/unit/AbstractScenarioTest.php', <<<PHP
<?php declare(strict_types=1);

namespace Scenario\\Core\\Tests\\Fixtures\\{$suffix};

use PHPUnit\\Framework\\TestCase;
use Scenario\\Core\\Attribute\\ApplyScenario;

#[ApplyScenario('abstract')]
abstract class AbstractScenarioTest extends TestCase
{
}
PHP);

        file_put_contents(Application::getRootDir() . '/tests/unit/Helper.php', <<<PHP
<?php declare(strict_types=1);

namespace Scenario\\Core\\Tests\\Fixtures\\{$suffix};

use Scenario\\Core\\Attribute\\ApplyScenario;

#[ApplyScenario('helper')]
final class Helper
{
}
PHP);

        $classes = (new ScenarioTestFinder())->all();
        ksort($classes);

        self::assertSame(
            [
                'Scenario\\Core\\Tests\\Fixtures\\' . $suffix . '\\ClassLevelScenarioTest' => [],
                'Scenario\\Core\\Tests\\Fixtures\\' . $suffix . '\\MethodLevelScenarioTest' => ['testRefreshesDatabase'],
            ],
            $classes,
        );
    }

    public function testIgnoresConcreteTestCasesWithoutScenarioAttributes(): void
    {
        mkdir(Application::getRootDir() . '/tests/unit', 0777, true);

        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            <<<XML
<?xml version="1.0"?>
<phpunit>
  <testsuites>
    <testsuite name="unit">
      <directory>tests/unit</directory>
    </testsuite>
  </testsuites>
</phpunit>
XML
        );

        $suffix = 'Fixture' . uniqid();

        file_put_contents(Application::getRootDir() . '/tests/unit/PlainTest.php', <<<PHP
<?php declare(strict_types=1);

namespace Scenario\\Core\\Tests\\Fixtures\\{$suffix};

use PHPUnit\\Framework\\TestCase;

final class PlainTest extends TestCase
{
    public function testItRuns(): void
    {
    }
}
PHP);

        self::assertSame([], (new ScenarioTestFinder())->all());
    }
}
