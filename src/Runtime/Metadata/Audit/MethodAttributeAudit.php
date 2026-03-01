<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata\Audit;

use Scenario\Core\Runtime\Metadata\ExecutionType;

final class MethodAttributeAudit extends AttributeAudit
{
    protected static ?MethodAttributeAudit $instance = null;

    public static function getInstance(string $className, string $method, ExecutionType $executionType): self
    {
        if (self::$instance === null
            || self::$instance->className !== $className
            || self::$instance->method !== $method
            || self::$instance->executionType !== $executionType) {
            self::$instance = new self($className, $method, $executionType);
        }

        return self::$instance;
    }

    private function __construct(
        public readonly string $className,
        public readonly string $method,
        public readonly ExecutionType $executionType,
    ) {
    }

    protected function getSignature(): string
    {
        return $this->className . '::' . $this->method;
    }

    protected function getType(): ExecutionType
    {
        return $this->executionType;
    }
}
