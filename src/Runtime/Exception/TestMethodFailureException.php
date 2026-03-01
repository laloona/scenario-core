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

use Throwable;

final class TestMethodFailureException extends FailureException
{
    public function __construct(string $method, Throwable $throwable)
    {
        parent::__construct(
            sprintf(
                'OnMethod "%s" failure: [%s]: %s',
                $method,
                get_class($throwable),
                $throwable->getMessage(),
            ),
            $throwable->getCode(),
            $throwable,
        );
    }
}
