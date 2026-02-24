<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime;

abstract class Registry
{
    abstract public static function getInstance(): Registry;

    protected function __construct()
    {
    }

    private function __clone()
    {
    }
}
