<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Metadata\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\ParameterType;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Exception\RegistryException;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\ScenarioBuilder;
use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use Stateforge\Scenario\Core\Runtime\ScenarioParameters;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;
use Stateforge\Scenario\Core\Tests\Files\TrackingScenario;
use Stateforge\Scenario\Core\Tests\Unit\ScenarioRegistryMock;

#[CoversClass(ApplyScenarioHandler::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(AttributeProcessor::class)]
#[UsesClass(ClassAttributeParser::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(MethodAttributeParser::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(ScenarioBuilder::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioParameters::class)]
#[UsesClass(ScenarioRegistry::class)]
#[Group('runtime')]
#[Small]
final class ApplyScenarioHandlerTest extends TestCase
{
    use ScenarioRegistryMock;

    protected function setUp(): void
    {
        $this->resetScenarioRegistry();
        TrackingScenario::$upCount = 0;
        TrackingScenario::$downCount = 0;
        TrackingScenario::$configuredParameters = null;
    }

    public function testUpExecutesScenarioAndConfiguresParameters(): void
    {
        $this->registerTrackingScenario();

        $context = AttributeContext::getInstance(
            self::class,
            'testUpExecutesScenarioAndConfiguresParameters',
            ExecutionType::Up,
            false,
            null,
        );

        (new ApplyScenarioHandler(new ScenarioBuilder()))
            ->handle(
                $context,
                new ApplyScenario('tracking', ['id' => '42']),
            );

        self::assertSame(1, TrackingScenario::$upCount);
        self::assertSame(0, TrackingScenario::$downCount);
        self::assertSame(['id' => 42], TrackingScenario::$configuredParameters);
    }

    public function testDownExecutesScenarioDownAndConfiguresParameters(): void
    {
        $this->registerTrackingScenario();

        $context = AttributeContext::getInstance(
            self::class,
            'testDownExecutesScenarioDownAndConfiguresParameters',
            ExecutionType::Down,
            false,
            null,
        );

        (new ApplyScenarioHandler(new ScenarioBuilder()))
            ->handle(
                $context,
                new ApplyScenario('tracking', ['id' => '7']),
            );

        self::assertSame(0, TrackingScenario::$upCount);
        self::assertSame(1, TrackingScenario::$downCount);
        self::assertSame(['id' => 7], TrackingScenario::$configuredParameters);
    }

    public function testDryRunDoesNotExecuteScenarioUpButConfiguresParameters(): void
    {
        $this->registerTrackingScenario();

        $context = AttributeContext::getInstance(
            self::class,
            'testDryRunDoesNotExecuteScenarioUpButConfiguresParameters',
            ExecutionType::Up,
            true,
            null,
        );

        (new ApplyScenarioHandler(new ScenarioBuilder()))
            ->handle(
                $context,
                new ApplyScenario('tracking', ['id' => '5']),
            );

        self::assertSame(0, TrackingScenario::$upCount);
        self::assertSame(0, TrackingScenario::$downCount);
        self::assertSame(['id' => 5], TrackingScenario::$configuredParameters);
    }

    public function testDryRunDoesNotExecuteScenarioDownButConfiguresParameters(): void
    {
        $this->registerTrackingScenario();

        $context = AttributeContext::getInstance(
            self::class,
            'testDryRunDoesNotExecuteScenarioDownButConfiguresParameters',
            ExecutionType::Down,
            true,
            null,
        );

        (new ApplyScenarioHandler(new ScenarioBuilder()))
            ->handle(
                $context,
                new ApplyScenario('tracking', ['id' => '5']),
            );

        self::assertSame(0, TrackingScenario::$upCount);
        self::assertSame(0, TrackingScenario::$downCount);
        self::assertSame(['id' => 5], TrackingScenario::$configuredParameters);
    }

    private function registerTrackingScenario(): void
    {
        $definition = new ScenarioDefinition(
            'main',
            TrackingScenario::class,
            new AsScenario('tracking'),
            [
                new Parameter('id', ParameterType::Integer),
            ],
        );

        ScenarioRegistry::getInstance()->register($definition);
    }
}
