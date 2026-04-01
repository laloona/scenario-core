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

use function sprintf;

final class ParameterValueErrorException extends Exception
{
    public function __construct(string $name, string $expected, string $actual, bool $default)
    {
        parent::__construct(
            sprintf(
                'wrong %svalue for parameter %s, expected type %s but got %s',
                $default === true ? 'default ' : '',
                $name,
                $expected,
                $actual,
            ),
        );
    }
}
