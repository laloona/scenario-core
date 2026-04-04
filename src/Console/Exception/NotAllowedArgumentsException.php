<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Exception;

use function implode;
use function sprintf;

final class NotAllowedArgumentsException extends InputException
{
    /**
     * @param list<string> $arguments
     * @param list<string> $allowedArguments
     */
    public function __construct(array $arguments, array $allowedArguments)
    {
        parent::__construct(
            sprintf(
                '[%s] are not allowed, allowed arguments: %s',
                implode(', ', $arguments),
                implode(', ', $allowedArguments),
            ),
        );
    }
}
