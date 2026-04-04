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

final class NotAllowedOptionsException extends InputException
{
    /**
     * @param list<string> $options
     * @param list<string> $allowedOptions
     */
    public function __construct(array $options, array $allowedOptions)
    {
        parent::__construct(
            sprintf(
                '[%s] are not allowed, allowed options: %s',
                implode(', ', $options),
                implode(', ', $allowedOptions),
            ),
        );
    }
}
