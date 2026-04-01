<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Input;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Input\Parser;

#[CoversClass(Parser::class)]
#[Group('console')]
#[Small]
final class ParserTest extends TestCase
{
    public function testParsesCommandArgumentsAndOptionsInMixedOrder(): void
    {
        $parser = new Parser([
            'scenario',
            '--file=config.php',
            'apply',
            'Scenario\\Demo',
            '--down',
            '10',
        ]);

        self::assertSame('apply', $parser->command());
        self::assertSame(['Scenario\\Demo', '10'], $parser->arguments());
        self::assertSame([
            'file' => 'config.php',
            'down' => true,
        ], $parser->options());
    }

    public function testMarksForceOptionSeparatelyAndRemovesItFromOptions(): void
    {
        $parser = new Parser([
            'scenario',
            '--force',
            'apply',
        ]);

        self::assertTrue($parser->force());
        self::assertSame([], $parser->options());
    }

    public function xxtestCollectsRepeatedOptions(): void
    {
        $parser = new Parser([
            'scenario',
            'apply',
            '--tag=first',
            '--tag=second',
        ]);

        self::assertSame(['tag' => ['first', 'second']], $parser->options());
    }

    public function testReturnsNullCommandWhenOnlyCliNameIsProvided(): void
    {
        $parser = new Parser([
            'scenario',
        ]);

        self::assertNull($parser->command());
        self::assertSame([], $parser->arguments());
        self::assertSame([], $parser->options());
    }
}
