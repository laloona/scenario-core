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
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Console\Command\ApplyScenarioCommand;
use Scenario\Core\Console\Command\CliCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Runtime\ScenarioRegistry;
use Scenario\Core\Tests\Files\AnotherScenario;
use Scenario\Core\Tests\Unit\AttributeContextMock;
use Scenario\Core\Tests\Unit\HandlerRegistryMock;
use Scenario\Core\Tests\Unit\ScenarioRegistryMock;
use Scenario\Core\Tests\Unit\TestClassStateMock;
use Scenario\Core\Tests\Unit\TestMethodStateMock;

#[CoversClass(ApplyScenarioCommand::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(AttributeHandler::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioRegistry::class)]
#[UsesClass(TestClassState::class)]
#[UsesClass(TestMethodState::class)]
#[Group('console')]
#[Medium]
final class ApplyScenarioCommandTest extends TestCase
{
    use AttributeContextMock;
    use HandlerRegistryMock;
    use ScenarioRegistryMock;
    use TestClassStateMock;
    use TestMethodStateMock;

    protected function setUp(): void
    {
        $this->resetAttributeContext();
        $this->resetHandlerRegistry();
        $this->resetScenarioRegistry();
        $this->resetClassMethodState();
        $this->resetTestMethodState();
    }

    protected function tearDown(): void
    {
        $this->resetAttributeContext();
        $this->resetHandlerRegistry();
        $this->resetScenarioRegistry();
        $this->resetClassMethodState();
        $this->resetTestMethodState();
    }

    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'Applies a given scenario, use --up or --down to choose how the scenario should be applied.',
            (new ApplyScenarioCommand())->description(),
        );
    }

    public function testRunAppliesScenarioDirectlyWithInputParameters(): void
    {
        $this->createScenarioRegistry('my value');

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['audit', true],
                ['up', null],
                ['down', null],
                ['parameter', 'param=my value'],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['scenario', 'my-scenario'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('error');
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');

        (new ApplyScenarioCommand())->run($input, $output);
    }

    public function testRunAppliesScenarioDirectlyWithInputRepeatableParametersWithOneGiven(): void
    {
        $this->createScenarioRegistry(['my value1']);

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['up', null],
                ['down', null],
                ['parameter', 'param=my value1'],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['scenario', 'my-scenario'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('error');
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');

        (new ApplyScenarioCommand())->run($input, $output);
    }

    public function testRunAppliesScenarioDirectlyWithInputRepeatableParametersWithTwoGiven(): void
    {
        $this->createScenarioRegistry(['my value1', 'my value2']);

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['up', null],
                ['down', null],
                ['parameter', ['param=my value1', 'param=my value2']],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['scenario', 'my-scenario'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('error');
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');

        (new ApplyScenarioCommand())->run($input, $output);
    }

    public function testRunReturnsErrorWhenUpAndDownAreBothProvided(): void
    {
        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['up', true],
                ['down', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $output->expects(self::once())
            ->method('confirm')
            ->with('Do you want to continue?')
            ->willReturn(true);
        $output->expects(self::once())
            ->method('error')
            ->with('You can just use either up or down scenarios.');

        self::assertSame(Command::Error, (new ApplyScenarioCommand())->run($input, $output));
    }

    public function testShowsErrorWhenNoScenariosAreRegistered(): void
    {
        ScenarioRegistry::getInstance()->clear();

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['up', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $output->expects(self::once())
            ->method('confirm')
            ->with('Do you want to continue?')
            ->willReturn(true);
        $output->expects(self::once())
            ->method('error')
            ->with('No scenarios were found, please create one.');

        self::assertSame(Command::Error, (new ApplyScenarioCommand())->run($input, $output));
    }

    public function testLeadsThroughAvailableScenariosWhenNoScenarioIsDirectlyGiven(): void
    {
        $this->createScenarioRegistry('my value');

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['up', null],
                ['down', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $output->expects(self::once())
            ->method('confirm')
            ->with('Do you want to continue?')
            ->willReturn(true);
        $output->expects(self::once())
            ->method('choice')
            ->with('Which scenario would you like to apply?', self::isArray())
            ->willReturn('0');
        $output->expects(self::once())
            ->method('ask')
            ->with('Please insert value for string parameter "param"')
            ->willReturn('my value');
        $output->expects(self::once())
            ->method('success')
            ->with('Scenario "' . AnotherScenario::class . '::up" was applied successfully.');

        self::assertSame(Command::Success, (new ApplyScenarioCommand())->run($input, $output));
    }

    public function testLeadsThroughAvailableScenariosWithRepeatableParametersWhenNoScenarioIsDirectlyGiven(): void
    {
        $this->createScenarioRegistry(['my value1', 'my value2']);

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['up', null],
                ['down', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $output->expects(self::exactly(3))
            ->method('confirm')
            ->with('Do you want to continue?')
            ->willReturnOnConsecutiveCalls(true, true, false);
        $output->expects(self::once())
            ->method('choice')
            ->with('Which scenario would you like to apply?', self::isArray())
            ->willReturn('0');
        $output->expects(self::exactly(2))
            ->method('ask')
            ->with('Please insert value for string parameter "param"')
            ->willReturnOnConsecutiveCalls('my value1', 'my value2');
        $output->expects(self::once())
            ->method('success')
            ->with('Scenario "' . AnotherScenario::class . '::up" was applied successfully.');

        self::assertSame(Command::Success, (new ApplyScenarioCommand())->run($input, $output));
    }

    /**
     * @param list<string>|string $parameter
     */
    private function createScenarioRegistry(array|string $parameter): void
    {
        $handler = $this->createPartialMock(AttributeHandler::class, ['attributeName','execute']);
        $handler->expects(self::exactly(3))
            ->method('attributeName')
            ->willReturn(ApplyScenario::class);
        $handler->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (AttributeContext $context, object $metaData) use ($parameter) {
                self::assertSame(ApplyScenarioCommand::class, $context->class);
                self::assertSame('up', $context->method);
                self::assertSame(ExecutionType::Up, $context->executionType);
                self::assertFalse($context->dryRun);
                self::assertInstanceOf(ApplyScenario::class, $metaData);
                self::assertSame(AnotherScenario::class, $metaData->id);
                self::assertArrayHasKey('param', $metaData->parameters);
                self::assertSame([ 'param' => $parameter ], $metaData->parameters);
            });

        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'main',
                AnotherScenario::class,
                new AsScenario('my-scenario'),
                [
                    new Parameter('param', ParameterType::String, null, false, is_array($parameter)),
                ],
            ),
        );
        HandlerRegistry::getInstance()->registerHandler($handler);
    }
}
