<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console;

use Scenario\Core\Console\Output\Formatter\Align;
use Scenario\Core\Console\Output\Formatter\Box;
use Scenario\Core\Console\Output\Formatter\Confirm;
use Scenario\Core\Console\Output\Formatter\Table;
use Scenario\Core\Console\Output\Formatter\Text;
use Scenario\Core\Console\Output\TerminalIO;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\ForegroundColor;
use Scenario\Core\Contract\CliOutput;
use function array_keys;
use function array_values;
use function count;
use function in_array;
use function is_string;
use function str_pad;
use function str_repeat;
use function strlen;
use function strtolower;
use const PHP_EOL;
use const STR_PAD_LEFT;

final class Output implements CliOutput
{
    public function __construct(
        private AnsiStyler $ansiStyler,
        private TerminalIO $terminalIO,
    ) {
    }

    /**
     * @param string|list<string> $string
     */
    public function write(string|array $string): void
    {
        if (is_string($string) === true) {
            $string = [$string];
        }

        foreach ($string as $singleStrimg) {
            $this->terminalIO->write($singleStrimg);
        }
    }

    /**
     * @param string|list<string> $string
     */
    public function writeln(string|array $string): void
    {
        if (is_string($string) === true) {
            $string = [ $string ];
        }

        foreach ($string as $singleLine) {
            $this->write($singleLine . PHP_EOL);
        }
    }

    private function readln(): string
    {
        return $this->terminalIO->read();
    }

    public function ask(string $question, ?string $default = null, ?callable $validator = null): ?string
    {
        $text = new Text($this->ansiStyler);
        $this->question($question);
        if ($default !== null) {
            $this->write(
                $text->text(' [', null) .
                $text->text($default, ForegroundColor::Yellow) .
                $text->text(']', null),
            );
        }
        $this->write($text->info('> '));

        while (true) {
            $input = $this->readln();
            $input = $input === '' ? null : $input;

            if ($validator !== null
                && $validator($input) === false) {
                $this->writeln(
                    $text->text('Input was invalid, please try again:', ForegroundColor::Red),
                );
                $this->write(
                    $text->text('> ', ForegroundColor::Red),
                );

                continue;
            }

            return $input;
        }
    }

    public function confirm(string $question, bool $default = true): bool
    {
        $text = new Text($this->ansiStyler);
        while (true) {
            $this->writeln((new Confirm($this->ansiStyler))->question($question, $default));
            $this->write($text->info('> '));

            $input = strtolower($this->readln());
            $result = match(true) {
                $input === '' => $default,
                in_array($input, ['y', 'yes'], true) => true,
                in_array($input, ['n', 'no'], true) => false,
                default => null,
            };

            if ($result === null) {
                $this->writeln((new Confirm($this->ansiStyler))->error($default));
                continue;
            }

            return $result;
        }
    }

    /**
     * @param list<string> $choices
     */
    public function choice(string $question, array $choices, ?string $default = null): string
    {
        $text = new Text($this->ansiStyler);
        $this->question($question);

        $this->write($text->text('Please select one of the following:', ForegroundColor::Green));
        if ($default !== null) {
            $this->writeln(
                $text->text(' default (', null) .
                $text->text($default, ForegroundColor::Yellow) .
                $text->text(')', null),
            );
        } else {
            $this->writeln('');
        }
        $choices = array_values($choices);
        foreach ($choices as $key => $choice) {
            $this->writeln(
                $text->text('(', null) .
                $text->text(str_pad((string)$key, strlen((string)count($choices)), ' ', STR_PAD_LEFT), ForegroundColor::Yellow) .
                $text->text(') ', null) .
                $text->text($choice, ForegroundColor::Cyan),
            );
        }

        $this->write($text->info('> '));

        while (true) {
            $input = strtolower($this->readln());

            if ($input === ''
                && $default !== null) {
                return $default;
            }

            if (in_array((int)$input, array_keys($choices), true) === true) {
                return $input;
            }

            $this->writeln(
                $text->text('Please insert a number from 0 to ' . count($choices) - 1 . ':', ForegroundColor::Red),
            );
            $this->write(
                $text->text('> ', ForegroundColor::Red),
            );
        }
    }

    public function text(string $text): void
    {
        $this->writeln((new Text($this->ansiStyler))->text($text, null));
    }

    public function headline(string $text): void
    {
        $this->writeln('');
        $this->writeln((new Text($this->ansiStyler))->text($text, ForegroundColor::Yellow));
        $this->writeln((new Text($this->ansiStyler))->text(str_repeat('-', strlen($text)), ForegroundColor::Yellow));
        $this->writeln('');
    }

    /**
     * @param list<string>|null $headers
     * @param list<list<string|null>> $rows
     * @param list<Align>|null $align
     */
    public function table(?array $headers, array $rows, ?array $align = null, bool $showBorder = true): void
    {
        $this->writeln('');
        $this->writeln((new Table($this->ansiStyler))->generate($headers, $rows, $align, $showBorder) ?? '');
        $this->writeln('');
    }

    public function info(string $text): void
    {
        $this->writeln('');
        $this->writeln((new Text($this->ansiStyler))->info($text));
        $this->writeln('');
    }

    public function success(string $text): void
    {
        $this->writeln('');
        $this->writeln((new Box($this->ansiStyler))->success($text));
        $this->writeln('');
    }

    public function warn(string $text): void
    {
        $this->writeln('');
        $this->writeln((new Box($this->ansiStyler))->warn($text));
        $this->writeln('');
    }

    public function error(string $text): void
    {
        $this->writeln('');
        $this->writeln((new Box($this->ansiStyler))->error($text));
        $this->writeln('');
    }

    public function question(string $text): void
    {
        $this->writeln('');
        $this->writeln((new Box($this->ansiStyler))->question($text));
        $this->writeln('');
    }
}
