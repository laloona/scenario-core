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

use Scenario\Core\Runtime\Exception\CycleException;
use Scenario\Core\Runtime\Metadata\ExecutionType;

abstract class AttributeAudit
{
    /** @var list<string> */
    private array $audits = [];

    private function __clone()
    {
    }

    public function audit(string $scenario): void
    {
        if (in_array($scenario, $this->audits, true) === true) {
            throw new CycleException(
                $this->getSignature(),
                $scenario,
                array_reverse($this->audits, false),
                $this->getType(),
            );
        }

        $this->audits[] = $scenario;
    }

    abstract protected function getSignature(): string;

    abstract protected function getType(): ExecutionType;
}
