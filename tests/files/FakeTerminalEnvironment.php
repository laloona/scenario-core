<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Files;

use Scenario\Core\Console\Output\Theme\TerminalEnvironment;

final class FakeTerminalEnvironment implements TerminalEnvironment
{
    public function __construct(
        private readonly bool $noColor,
        private readonly ?bool $stdoutIsTty,
        private readonly ?string $columnsEnv,
        private readonly string $osFamily = 'Linux',
        private readonly ?string $shellOutput = null,
    ) {
    }

    public function noColorEnv(): bool
    {
        return $this->noColor;
    }

    public function stdoutIsTty(): ?bool
    {
        return $this->stdoutIsTty;
    }

    public function columnsEnv(): ?string
    {
        return $this->columnsEnv;
    }

    public function shellExec(string $command): ?string
    {
        return $this->shellOutput;
    }

    public function osFamily(): string
    {
        return $this->osFamily;
    }
}
