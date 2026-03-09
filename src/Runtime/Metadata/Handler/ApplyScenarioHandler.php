<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata\Handler;

use ReflectionException;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Contract\ScenarioBuilderInterface;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Runtime\ScenarioParameters;
use Scenario\Core\Runtime\ScenarioRegistry;

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
