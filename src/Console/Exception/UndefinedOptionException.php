<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Exception;

use function sprintf;

final class UndefinedOptionException extends InputException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'option with name "%s" is not defined.',
                $name,
            ),
        );
    }
}
