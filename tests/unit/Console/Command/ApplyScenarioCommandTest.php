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
use PHPUnit\Framework\Attributes\Small;
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
#[Small]
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
        $handler = $this->createPartialMock(AttributeHandler::class, ['attributeName','execute']);
        $handler->expects(self::exactly(3))
            ->method('attributeName')
            ->willReturn(ApplyScenario::class);
        $handler->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (AttributeContext $context, object $metaData) {
                self::assertSame(ApplyScenarioCommand::class, $context->class);
                self::assertSame('up', $context->method);
                self::assertSame(ExecutionType::Up, $context->executionType);
                self::assertFalse($context->dryRun);
                self::assertInstanceOf(ApplyScenario::class, $metaData);
                self::assertSame(AnotherScenario::class, $metaData->id);
                self::assertSame([ 'param' => 'my value' ], $metaData->parameters);
            });

        ScenarioRegistry::getInstance()->register(
            new ScenarioDefinition(
                'main',
                AnotherScenario::class,
                new AsScenario('my-scenario'),
                [
                    new Parameter('param', ParameterType::String),
                ],
            ),
        );
        HandlerRegistry::getInstance()->registerHandler($handler);

        $input = $this->createMock(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['up', null],
                ['down', null],
                ['param', 'my value'],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', 'my-scenario'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('error');
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');

        $result = (new ApplyScenarioCommand())->run($input, $output);
    }

    public function testRunReturnsErrorWhenUpAndDownAreBothProvided(): void
    {
        $input = $this->createMock(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
                ['up', true],
                ['down', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('error')
            ->with('You can just use either up or down scenarios.');

        self::assertSame(Command::Error, (new ApplyScenarioCommand())->run($input, $output));
    }
}
