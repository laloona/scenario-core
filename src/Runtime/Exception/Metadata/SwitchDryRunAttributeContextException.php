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

final class SwitchDryRunAttributeContextException extends Exception
{
    public function __construct(bool $wantedRun)
    {
        parent::__construct(
            sprintf(
                'context switch not allowed, found switch from %s to %s',
                $wantedRun === true ? 'regular' : 'dryRun',
                $wantedRun === true ? 'dryRun' : 'regular',
            ),
        );
    }
}
