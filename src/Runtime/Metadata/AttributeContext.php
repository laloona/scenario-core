<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata;

use Scenario\Core\Runtime\Exception\CycleException;

final class AttributeContext
{
    /** @var list<string> */
    private array $audits = [];

    /**
     * @param class-string $class
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $method,
        public readonly ExecutionType $executionType,
    ) {
    }

    public function target(): ContextTarget
    {
        return $this->method === null
            ? ContextTarget::OnClass
            : ContextTarget::OnMethod;
    }

    public function onClass(): bool
    {
        return $this->target() === ContextTarget::OnClass;
    }

    public function onMethod(): bool
    {
        return $this->target() === ContextTarget::OnMethod;
    }

    public function audit(string $scenario): void
    {
        if (in_array($scenario, $this->audits, true) === true) {
            $this->audits[] = $scenario;
            throw new CycleException(
                $this->class . ($this->method === null ? '' : '::' . $this->method),
                $scenario,
                $this->audits,
                $this->executionType
            );
        }

        $this->audits[] = $scenario;
    }

    /**
     * @return list<string>
     */
    public function getAudits(): array
    {
        return $this->audits;
    }
}
