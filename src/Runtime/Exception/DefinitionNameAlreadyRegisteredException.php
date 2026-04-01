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

final class DefinitionNameAlreadyRegisteredException extends DefinitionException
{
    /**
     * @param class-string $class
     */
    public function __construct(string $name, string $class)
    {
        parent::__construct(
            sprintf(
                'scenario name %s already registered for %s',
                $name,
                $class,
            ),
        );
    }
}
