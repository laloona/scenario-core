<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Contract;

interface CliInput
{
    public function command(): ?string;

    public function argument(string $name): null|bool|string;

    public function option(string $name): null|bool|string;
}
