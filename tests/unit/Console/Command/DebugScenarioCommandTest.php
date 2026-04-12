<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\DebugScenarioCommand;
use Stateforge\Scenario\Core\Console\Input;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Stateforge\Scenario\Core\PHPUnit\Finder\DirectoryFinder;
use Stateforge\Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\TestClassState;
use Stateforge\Scenario\Core\Runtime\Application\TestMethodState;
use Stateforge\Scenario\Core\Runtime\ClassFinder;
use Stateforge\Scenario\Core\Runtime\Exception\RegistryException;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;
use Stateforge\Scenario\Core\Tests\Files\ValidScenario;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use Stateforge\Scenario\Core\Tests\Unit\AttributeContextMock;
use Stateforge\Scenario\Core\Tests\Unit\ScenarioRegistryMock;
use Stateforge\Scenario\Core\Tests\Unit\TestClassStateMock;
use Stateforge\Scenario\Core\Tests\Unit\TestMethodStateMock;
use function array_pop;
use function explode;
use function file_put_contents;
use function implode;
use function is_dir;
use function mkdir;
use function sort;
use function sprintf;
use function uniqid;

#[CoversClass(DebugScenarioCommand::class)]
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
final class DebugScenarioCommandTest extends TestCase
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
            (new DebugScenarioCommand(new ScenarioTestFinder()))->description(),
        );
    }

    public function testRunReturnsErrorWhenResolveRejectsFooOption(): void
    {
        $input = new Input([
            'scenario',
            'debug',
            '--foo=bar',
        ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');
        $output->expects(self::once())
            ->method('error');

        self::assertSame(Command::Error, (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output));
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
                ['class', null],
                ['method', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('error')
            ->with('No scenarios or unit tests were found, please create one.');

        self::assertSame(
            Command::Error,
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
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
                ['class', 'debug-scenario'],
                ['method', null],
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
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunDebugsDirectUnitTestFromInput(): void
    {
        $className = $this->createPhpUnitTestFixtureWithMethod(
            'DirectDebugTest',
            "#[\\Stateforge\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]\n    public function testDebuggable(): void\n    {\n    }\n",
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['class', $className],
                ['method', 'testDebuggable'],
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
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunSelectsUnitTestWhenNoScenariosExist(): void
    {
        $this->createPhpUnitTestFixtureWithClassAttribute(
            'SelectableDebugTest',
            "#[\\Stateforge\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]",
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
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunSelectsUnitTestClassWhenMultipleClassesExist(): void
    {
        $firstClass = $this->createPhpUnitTestFixtureWithClassAttribute(
            'FirstSelectableDebugTest',
            "#[\\Stateforge\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]",
        );
        $secondClass = $this->createPhpUnitTestFixtureWithClassAttribute(
            'SecondSelectableDebugTest',
            "#[\\Stateforge\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]",
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['class', null],
                ['method', null],
            ]);

        $orderedClasses = [$firstClass, $secondClass];
        sort($orderedClasses);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('choice')
            ->with('Which class would you like to debug?', $orderedClasses)
            ->willReturn('1');
        $output->expects(self::exactly(2))
            ->method('headline');
        $output->expects(self::exactly(2))
            ->method('writeln')
            ->with([]);
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Success,
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunSelectsMethodWhenMultipleMethodsExist(): void
    {
        $className = $this->createPhpUnitTestFixtureWithMethod(
            'MultiMethodDebugTest',
            <<<'PHP'
#[\Stateforge\Scenario\Core\Attribute\ApplyScenario('my-scenario')]
    public function testFirst(): void
    {
    }

    #[\Stateforge\Scenario\Core\Attribute\ApplyScenario('my-scenario')]
    public function testSecond(): void
    {
    }
PHP,
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['class', null],
                ['method', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('choice')
            ->with(
                sprintf('Which method would you like to debug from %s?', $className),
                ['testFirst', 'testSecond'],
            )
            ->willReturn('testSecond');
        $output->expects(self::exactly(4))
            ->method('headline');
        $output->expects(self::exactly(4))
            ->method('writeln')
            ->with([]);
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Success,
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunSelectsScenarioWhenScenariosAndTestsExist(): void
    {
        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'main',
                ValidScenario::class,
                new AsScenario('debug-scenario', 'test description'),
                [],
            ),
        );
        $this->createPhpUnitTestFixtureWithClassAttribute(
            'ScenarioChoiceDebugTest',
            "#[\\Stateforge\\Scenario\\Core\\Attribute\\ApplyScenario('my-scenario')]",
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['class', null],
                ['method', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::exactly(2))
            ->method('choice')
            ->willReturnMap([
                ['Which kind of class would you like to debug?', ['Scenario', 'Unit Test'], '0'],
                ['Which scenario would you like to debug?', [ValidScenario::class . ' (main)'], '0'],
            ]);
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
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    public function testRunSelectsScenarioWhenNoUnitTestsExist(): void
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
                ['class', null],
                ['method', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('choice')
            ->with('Which scenario would you like to debug?', [ValidScenario::class . ' (main)'])
            ->willReturn('0');
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
            (new DebugScenarioCommand(new ScenarioTestFinder()))->run($input, $output),
        );
    }

    /**
     * @param non-empty-string $classSuffix
     * @param non-empty-string $body
     * @return class-string
     */
    private function createPhpUnitTestFixtureWithMethod(string $classSuffix, string $body): string
    {
        $directory = Application::getRootDir() . '/tests/debug';
        if (is_dir(Application::getRootDir() . '/tests') === false) {
            mkdir(Application::getRootDir() . '/tests');
        }
        if (is_dir($directory) === false) {
            mkdir($directory);
        }

        $className = 'Stateforge\\Scenario\\Core\\Tests\\Temp\\' . $classSuffix . uniqid();
        $parts = explode('\\', $className);
        $shortName = array_pop($parts);
        $namespace = implode('\\', $parts);

        file_put_contents(
            $directory . '/' . $shortName . '.php',
            "<?php declare(strict_types=1);\n\nnamespace {$namespace};\n\nfinal class {$shortName} extends \\PHPUnit\\Framework\\TestCase\n{\n    {$body}}\n",
        );

        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            '<?xml version="1.0"?><phpunit><testsuites><testsuite name="unit"><directory>tests/debug</directory></testsuite></testsuites></phpunit>',
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
        $directory = Application::getRootDir() . '/tests/debug';
        if (is_dir(Application::getRootDir() . '/tests') === false) {
            mkdir(Application::getRootDir() . '/tests');
        }
        if (is_dir($directory) === false) {
            mkdir($directory);
        }

        $className = 'Stateforge\\Scenario\\Core\\Tests\\Temp\\' . $classSuffix . uniqid();
        $parts = explode('\\', $className);
        $shortName = array_pop($parts);
        $namespace = implode('\\', $parts);

        file_put_contents(
            $directory . '/' . $shortName . '.php',
            "<?php declare(strict_types=1);\n\nnamespace {$namespace};\n\n{$attribute}\nfinal class {$shortName} extends \\PHPUnit\\Framework\\TestCase\n{\n}\n",
        );

        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            '<?xml version="1.0"?><phpunit><testsuites><testsuite name="unit"><directory>tests/debug</directory></testsuite></testsuites></phpunit>',
        );

        /** @var class-string $className */
        return $className;
    }
}
