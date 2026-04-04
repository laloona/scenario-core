<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata\Handler;

use ReflectionException;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Contract\ScenarioBuilderInterface;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Stateforge\Scenario\Core\Runtime\ScenarioParameters;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;

final class ApplyScenarioHandler extends AttributeHandler
{
    public function __construct(private ScenarioBuilderInterface $builder)
    {
    }

    protected function attributeName(): string
    {
        return ApplyScenario::class;
    }

    protected function execute(AttributeContext $context, object $metaData): void
    {
        /** @var ApplyScenario $metaData */
        $scenario = ScenarioRegistry::getInstance()->resolve($metaData->id);

        $this->attributes($context, $scenario->class);

        $parameters = new ScenarioParameters($scenario->parameters, $metaData->parameters);

        $context->audit($scenario->class, $parameters->all());

        $scenarioInstance = $this->builder->build($scenario->class);
        $scenarioInstance->configure($parameters);

        if ($context->dryRun === true) {
            return;
        }

        match($context->executionType) {
            ExecutionType::Up => $scenarioInstance->up(),
            ExecutionType::Down => $scenarioInstance->down(),
        };
    }

    /**
     * @param class-string $className
     * @throws ReflectionException
     */
    private function attributes(AttributeContext $context, string $className): void
    {
        (new AttributeProcessor())->process(
            $context,
            (new ClassAttributeParser())->parse($className),
        );

        (new AttributeProcessor())->process(
            $context,
            (new MethodAttributeParser())->parse($className, $context->executionType->value),
        );
    }
}
