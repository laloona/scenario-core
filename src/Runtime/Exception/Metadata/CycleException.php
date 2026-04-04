<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Exception\Metadata;

use Stateforge\Scenario\Core\Runtime\Exception\Exception;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use function array_values;
use function implode;
use function sprintf;

final class CycleException extends Exception
{
    /** @param list<string> $stack */
    public function __construct(string $root, string $cycle, array $stack, ExecutionType $executionType)
    {
        parent::__construct(
            sprintf(
                '%s: %s caused cycle in applied stack [%s] while applying %s',
                $root,
                $cycle,
                implode(' => ', array_values($stack)),
                $executionType->value,
            ),
        );
    }
}
