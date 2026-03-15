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
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Runtime\ScenarioBuilder;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Runtime\ScenarioParameters;
use Scenario\Core\Runtime\ScenarioRegistry;
use Scenario\Core\Tests\Files\TrackingScenario;
use Scenario\Core\Tests\Unit\ScenarioRegistryMock;

#[CoversClass(ApplyScenarioHandler::class)]
#[UsesClass(Application::class)]
#[UsesClass(ScenarioBuilder::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioRegistry::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(AttributeContext::class)]
#[UsesClass(ExecutionType::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(AttributeProcessor::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(ClassAttributeParser::class)]
#[UsesClass(MethodAttributeParser::class)]
#[UsesClass(ScenarioParameters::class)]
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
        );

        new ApplyScenarioHandler(new ScenarioBuilder())
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
        );

        new ApplyScenarioHandler(new ScenarioBuilder())
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
        );

        new ApplyScenarioHandler(new ScenarioBuilder())
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
        );

        new ApplyScenarioHandler(new ScenarioBuilder())
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
            new AsScenario('tracking', null),
            [
                new Parameter('id', ParameterType::Integer),
            ],
        );

        ScenarioRegistry::getInstance()->register($definition);
    }
}
