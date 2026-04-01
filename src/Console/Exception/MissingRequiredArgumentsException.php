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

final class MissingRequiredArgumentsException extends InputException
{
    /**
     * @param list<string> $arguments
     */
    public function __construct(array $arguments)
    {
        parent::__construct(
            sprintf(
                'required arguments [%s] are missing',
                implode(', ', $arguments),
            ),
        );
    }
}
