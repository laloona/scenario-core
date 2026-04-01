<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Exception\Application\TestMethodFailureException;
use Scenario\Core\Runtime\Exception\Metadata\ConnectionException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use function file_put_contents;
use function is_file;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(RefreshDatabaseHandler::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(Application::class)]
#[UsesClass(TestMethodState::class)]
#[UsesClass(TestMethodFailureException::class)]
#[UsesClass(ConnectionException::class)]
#[Group('runtime')]
#[Small]
final class RefreshDatabaseHandlerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/scenario_refresh_' . uniqid();
        mkdir($this->tempDir);

        $reflection = new ReflectionClass(Application::class);
        $rootDir = $reflection->getProperty('rootDir');
        $rootDir->setValue(null, $this->tempDir);

        $configuration = new LoadedConfiguration(new DefaultConfiguration());
        $configuration->setConnections([
            'main' => new ConnectionValue('main', 'db.php'),
        ]);

        $configProperty = $reflection->getProperty('configuration');
        $configProperty->setValue(null, $configuration);

        $this->resetTestMethodState();
        unset($GLOBALS['scenario_core_refresh']);
    }

    protected function tearDown(): void
    {
        foreach (scandir($this->tempDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                unlink($this->tempDir . '/' . $file);
            }
        }

        rmdir($this->tempDir);

        $reflection = new ReflectionClass(Application::class);
        $rootDir = $reflection->getProperty('rootDir');
        $rootDir->setValue(null, null);
        $configProperty = $reflection->getProperty('configuration');
        $configProperty->setValue(null, null);

        $this->resetTestMethodState();
        unset($GLOBALS['scenario_core_refresh']);
    }

    public function testExecutesConfiguredConnection(): void
    {
        $configFile = $this->tempDir . '/db.php';
        file_put_contents($configFile, '<?php $GLOBALS["scenario_core_refresh"] = ($GLOBALS["scenario_core_refresh"] ?? 0) + 1;');
        self::assertTrue(is_file($configFile));

        $handler = new RefreshDatabaseHandler();
        $context = AttributeContext::getInstance(
            self::class,
            'testExecutesConfiguredConnection',
            ExecutionType::Up,
            false,
            null,
        );

        $handler->handle($context, new RefreshDatabase('main'));

        self::assertSame(1, $GLOBALS['scenario_core_refresh']);
    }

    public function testDryRunDoesNotExecuteConnection(): void
    {
        $configFile = $this->tempDir . '/db.php';
        file_put_contents($configFile, '<?php $GLOBALS["scenario_core_refresh"] = ($GLOBALS["scenario_core_refresh"] ?? 0) + 1;');

        $handler = new RefreshDatabaseHandler();
        $context = AttributeContext::getInstance(
            self::class,
            'testDryRunDoesNotExecuteConnection',
            ExecutionType::Up,
            true,
            null,
        );

        $handler->handle($context, new RefreshDatabase('main'));

        self::assertFalse(isset($GLOBALS['scenario_core_refresh']));
    }

    public function testMissingConnectionRegistersFailure(): void
    {
        $handler = new RefreshDatabaseHandler();
        $context = AttributeContext::getInstance(
            self::class,
            'testMissingConnectionRegistersFailure',
            ExecutionType::Up,
            false,
            null,
        );

        $handler->handle($context, new RefreshDatabase('missing'));

        $failure = (new TestMethodState())->failure($context->class, $context->method ?? '');
        self::assertInstanceOf(TestMethodFailureException::class, $failure);
        self::assertInstanceOf(ConnectionException::class, $failure->getPrevious());
    }

    private function resetTestMethodState(): void
    {
        $reflection = new ReflectionClass(TestMethodState::class);
        $throwables = $reflection->getProperty('throwables');
        $throwables->setValue(null, []);
    }
}
