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

use function sprintf;

final class RegistryException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                '%s is not registered',
                $name,
            ),
        );
    }
}
