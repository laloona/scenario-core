<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Exception\Application;

use Throwable;
use function get_class;
use function sprintf;

final class ApplicationFailureException extends FailureException
{
    public function __construct(Throwable $throwable)
    {
        parent::__construct(
            sprintf(
                'Scenario application failure: [%s]: %s',
                get_class($throwable),
                $throwable->getMessage(),
            ),
            $throwable->getCode(),
            $throwable,
        );
    }
}
