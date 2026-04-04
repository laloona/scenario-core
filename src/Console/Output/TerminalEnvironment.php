<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Output;

interface TerminalEnvironment
{
    public function noColorEnv(): bool;

    public function isTty(): ?bool;

    public function columnsEnv(): ?string;

    public function shellExec(string $command): ?string;

    public function osFamily(): string;
}
