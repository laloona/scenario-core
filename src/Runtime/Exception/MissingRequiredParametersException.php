<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Exception;

use function implode;
use function sprintf;

final class MissingRequiredParametersException extends Exception
{
    /**
     * @param list<string> $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct(
            sprintf(
                'required parameters [%s] are missing',
                implode(', ', $parameters),
            ),
        );
    }
}
