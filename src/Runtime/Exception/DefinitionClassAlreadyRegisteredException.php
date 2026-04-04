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

final class DefinitionClassAlreadyRegisteredException extends DefinitionException
{
    /**
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        parent::__construct(
            sprintf(
                '%s is already registered',
                $class,
            ),
        );
    }
}
