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

use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Throwable;

abstract class AttributeHandler
{
    final public function supports(?string $attributeName): bool|string
    {
        return $attributeName === null
            ? $this->attributeName()
            : $attributeName === $this->attributeName();
    }

    final public function handle(AttributeContext $context, object $metaData): void
    {
        if ($this->supports(get_class($metaData)) === true) {
            try {
                $this->execute($context, $metaData);
            } catch (Throwable $throwable) {
                match (true) {
                    $context->onClass() => new TestClassState()->fail($context->class, $throwable),
                    $context->onMethod() => new TestMethodState()->fail($context->class, $context->method ?? '', $throwable),
                };
            }
        }
    }

    abstract protected function attributeName(): string;

    abstract protected function execute(AttributeContext $context, object $metaData): void;
}
