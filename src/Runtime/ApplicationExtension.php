<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime;

abstract class ApplicationExtension
{
    public function prepare(): void
    {
    }

    public function boot(): void
    {
    }

    final public function bootstrap(): void
    {
        Application::extend($this);
    }
}
