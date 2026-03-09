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
    /**
     * @var class-string|null
     */
    private static ?string $currentClass = null;

    /**
     * @var array<string, self>
     */
    private static array $instances = [];

    /**
     * @param class-string $class
     */
    public static function getInstance(
        string $class,
        ?string $method,
        ExecutionType $executionType,
        bool $dryRun,
    ): self {
        if (self::$currentClass !== $class) {
            self::$currentClass = $class;
            self::$instances = [];
        }

        if ($method === null) {
            if (isset(self::$instances[$executionType->value]) === false) {
                self::$instances[$executionType->value] = new self($class, $method, $executionType, $dryRun);
            }
            return self::$instances[$executionType->value];
        }

        if (isset(self::$instances[$method . '::' . $executionType->value]) === false) {
            self::$instances[$method . '::' . $executionType->value] = new self($class, $method, $executionType, $dryRun);
        }
        return self::$instances[$method . '::' . $executionType->value];
    }

    /** @var list<class-string> */
    private array $audits = [];

    /**
     * @param class-string $class
     */
    private function __construct(
        public readonly string $class,
        public readonly ?string $method,
        public readonly ExecutionType $executionType,
        public readonly bool $dryRun,
    ) {
    }

    private function __clone()
    {
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

    /**
     * @param class-string $scenario
     * @param array<string, mixed> $parameters
     * @throws CycleException
     */
    public function audit(string $scenario, array $parameters): void
    {
        $scenarioSignature = $scenario . (count($parameters) === 0 ? '' : json_encode($parameters, JSON_THROW_ON_ERROR));
        if (in_array($scenarioSignature, $this->audits, true) === true) {
            throw new CycleException(
                $this->class . ($this->method === null ? '' : '::' . $this->method),
                $scenarioSignature,
                [...$this->audits, $scenario],
                $this->executionType,
            );
        }

        $this->audits[] = $scenarioSignature;
    }

    /**
     * @return list<class-string>
     */
    public function getAudits(): array
    {
        return $this->audits;
    }
}
