<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Console\Command\CliCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Console\Command\DebugCommand;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Scenario\Core\PHPUnit\Finder\DirectoryFinder;
use Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\ClassFinder;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Runtime\ScenarioRegistry;
use Scenario\Core\Tests\Files\ValidScenario;
use Scenario\Core\Tests\Unit\ApplicationMock;
use Scenario\Core\Tests\Unit\AttributeContextMock;
use Scenario\Core\Tests\Unit\ScenarioRegistryMock;
use Scenario\Core\Tests\Unit\TestClassStateMock;
use Scenario\Core\Tests\Unit\TestMethodStateMock;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function uniqid;

#[CoversClass(DebugCommand::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(AttributeProcessor::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(ClassAttributeParser::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(ClassFinder::class)]
#[UsesClass(DirectoryFinder::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(MethodAttributeParser::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioRegistry::class)]
#[UsesClass(ScenarioTestFinder::class)]
#[UsesClass(TestClassState::class)]
#[UsesClass(TestMethodState::class)]
#[Group('console')]
#[Medium]
final class DebugCommandTest extends TestCase
{
    use ApplicationMock;
    use AttributeContextMock;
    use ScenarioRegistryMock;
    use TestClassStateMock;
    use TestMethodStateMock;

    protected function setUp(): void
    {
        $this->resetApplication();
        $this->createRootDir();
        $this->resetAttributeContext();
        $this->resetScenarioRegistry();
        $this->resetClassMethodState();
        $this->resetTestMethodState();
    }

    protected function tearDown(): void
    {
        $this->resetApplication();
        $this->resetAttributeContext();
        $this->resetScenarioRegistry();
        $this->resetClassMethodState();
        $this->resetTestMethodState();
        $this->removeRootDir();
    }

    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'Debugs a given scenario or Unit test.',
            (new DebugCommand(new ScenarioTestFinder()))->description(),
        );
    }

    public function testRunReturnsErrorWhenNoScenariosOrTestsWereFound(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', null],
                ['1', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('error')
            ->with('No scenarios or unit tests were found, please create one.');

        self::assertSame(
            Command::Error,
            (new DebugCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunDebugsDirectScenarioFromInput(): void
    {
        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'main',
                ValidScenario::class,
                new AsScenario('debug-scenario', 'test description'),
                [],
            ),
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', 'debug-scenario'],
                ['1', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::exactly(5))
            ->method('headline');
        $output->expects(self::once())
            ->method('table')
            ->with(
                null,
                [['debug-scenario', 'test description']],
                null,
                false,
            );
        $output->expects(self::exactly(4))
            ->method('writeln')
            ->with([]);
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Success,
            (new DebugCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunDebugsDirectUnitTestFromInput(): void
    {
        $className = $this->createPhpUnitTestFixtureWithMethod(
            'DirectDebugTest',
            "#[\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]\n    public function testDebuggable(): void\n    {\n    }\n",
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', $className],
                ['1', 'testDebuggable'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::exactly(4))
            ->method('headline');
        $output->expects(self::exactly(4))
            ->method('writeln')
            ->with([]);
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Error,
            (new DebugCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunSelectsUnitTestWhenNoScenariosExist(): void
    {
        $this->createPhpUnitTestFixtureWithClassAttribute(
            'SelectableDebugTest',
            "#[\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]",
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', null],
                ['1', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::exactly(2))
            ->method('headline');
        $output->expects(self::exactly(2))
            ->method('writeln')
            ->with([]);
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Success,
            (new DebugCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    /**
     * @param non-empty-string $classSuffix
     * @param non-empty-string $body
     * @return class-string
     */
    private function createPhpUnitTestFixtureWithMethod(string $classSuffix, string $body): string
    {
        $directory = Application::getRootDir() . '/tests/Debug';
        if (is_dir(Application::getRootDir() . '/tests') === false) {
            mkdir(Application::getRootDir() . '/tests');
        }
        if (is_dir($directory) === false) {
            mkdir($directory);
        }

        $className = 'Scenario\\Core\\Tests\\Temp\\' . $classSuffix . uniqid();
        $parts = explode('\\', $className);
        $shortName = array_pop($parts);
        $namespace = implode('\\', $parts);

        file_put_contents(
            $directory . '/' . $shortName . '.php',
            "<?php declare(strict_types=1);\n\nnamespace {$namespace};\n\nfinal class {$shortName} extends \\PHPUnit\\Framework\\TestCase\n{\n    {$body}}\n",
        );

        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            "<?xml version=\"1.0\"?><phpunit><testsuites><testsuite name=\"unit\"><directory>{$directory}</directory></testsuite></testsuites></phpunit>",
        );

        /** @var class-string $className */
        return $className;
    }

    /**
     * @param non-empty-string $classSuffix
     * @param non-empty-string $attribute
     * @return class-string
     */
    private function createPhpUnitTestFixtureWithClassAttribute(string $classSuffix, string $attribute): string
    {
        $directory = Application::getRootDir() . '/tests/Debug';
        if (is_dir(Application::getRootDir() . '/tests') === false) {
            mkdir(Application::getRootDir() . '/tests');
        }
        if (is_dir($directory) === false) {
            mkdir($directory);
        }

        $className = 'Scenario\\Core\\Tests\\Temp\\' . $classSuffix . uniqid();
        $parts = explode('\\', $className);
        $shortName = array_pop($parts);
        $namespace = implode('\\', $parts);

        file_put_contents(
            $directory . '/' . $shortName . '.php',
            "<?php declare(strict_types=1);\n\nnamespace {$namespace};\n\n{$attribute}\nfinal class {$shortName} extends \\PHPUnit\\Framework\\TestCase\n{\n}\n",
        );

        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            "<?xml version=\"1.0\"?><phpunit><testsuites><testsuite name=\"unit\"><directory>{$directory}</directory></testsuite></testsuites></phpunit>",
        );

        /** @var class-string $className */
        return $className;
    }
}
