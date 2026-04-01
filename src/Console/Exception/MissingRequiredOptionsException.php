<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Exception;

use function implode;
use function sprintf;

final class MissingRequiredOptionsException extends InputException
{
    /**
     * @param list<string> $options
     */
    public function __construct(array $options)
    {
        parent::__construct(
            sprintf(
                'required options [%s] are missing',
                implode(', ', $options),
            ),
        );
    }
}
