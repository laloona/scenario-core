<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Exception\Metadata;

use Scenario\Core\Runtime\Exception\Exception;
use function sprintf;

final class ConnectionException extends Exception
{
    public function __construct(string $connection)
    {
        parent::__construct(
            sprintf(
                'Unknown connection "%s"',
                $connection,
            ),
        );
    }
}
