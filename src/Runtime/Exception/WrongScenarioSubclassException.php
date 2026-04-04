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

final class WrongScenarioSubclassException extends Exception
{
    /**
     * @param class-string $scenarioClass
     * @param class-string $scenarioSubclass
     */
    public function __construct(string $scenarioClass, string $scenarioSubclass)
    {
        parent::__construct(
            sprintf(
                '%s is not from type %s',
                $scenarioClass,
                $scenarioSubclass,
            ),
        );
    }
}
