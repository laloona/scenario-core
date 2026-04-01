<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Exception;

use function implode;
use function sprintf;

final class NotAllowedParametersException extends Exception
{
    /**
     * @param list<string> $parameters
     * @param list<string> $allowedParameters
     */
    public function __construct(array $parameters, array $allowedParameters)
    {
        parent::__construct(
            sprintf(
                '[%s] are not allowed, allowed parameters: %s',
                implode(', ', $parameters),
                implode(', ', $allowedParameters),
            ),
        );
    }
}
