<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata;

use ReflectionAttribute;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Exception\RegistryException;

final class AttributeProcessor
{
    /**
     * @param object[] $attributes
     */
    public function process(AttributeContext $context, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof ReflectionAttribute) {
                try {
                    $handler = HandlerRegistry::getInstance()
                        ->attributeHandler($attribute->getName());

                    if ($handler->supports($attribute->getName()) === false) {
                        continue;
                    }
                } catch (RegistryException $exception) {
                    continue;
                }

                if ($context->onClass() === true
                    && $context->executionType === ExecutionType::Up
                ) {
                    $applicationState = new ApplicationState();
                    if ($applicationState->isFailed() === true) {
                        $applicationState->addClass($context->class);
                    };
                }

                $handler->handle($context, $attribute->newInstance());
            }
        }
    }
}
