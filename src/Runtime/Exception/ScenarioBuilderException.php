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

use Scenario\Core\Contract\ScenarioInterface;

class ScenarioBuilderException extends Exception
{
    public function __construct(string $className)
    {
        parent::__construct(
            sprintf(
                'Given class %s doesn\'t implement interface "%s"',
                $className,
                ScenarioInterface::class,
            ),
        );
    }
}
