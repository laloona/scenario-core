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
use function sprintf;

final class ParameterNameErrorException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'invalid parameter name "%s". Use snake_case, kebab-case (lowercase alphanumeric, "_" or "-") or camelCase',
                $name,
            ),
        );
    }
}
