<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Output\Theme;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Output\Theme\BackgroundColor;
use Scenario\Core\Console\Output\Theme\BoxType;
use Scenario\Core\Console\Output\Theme\ForegroundColor;

#[CoversClass(BoxType::class)]
#[UsesClass(BackgroundColor::class)]
#[UsesClass(ForegroundColor::class)]
#[Group('console')]
#[Small]
final class BoxTypeTest extends TestCase
{
    public function testPrefixMatchesType(): void
    {
        self::assertSame('[ERROR] ', BoxType::Error->prefix());
        self::assertSame('[OK] ', BoxType::Success->prefix());
        self::assertSame('[WARNING] ', BoxType::Warn->prefix());
        self::assertNull(BoxType::Question->prefix());
        self::assertNull(BoxType::Note->prefix());
    }

    public function testBackgroundMatchesType(): void
    {
        self::assertSame(BackgroundColor::Red, BoxType::Error->background());
        self::assertSame(BackgroundColor::Green, BoxType::Success->background());
        self::assertSame(BackgroundColor::Yellow, BoxType::Warn->background());
        self::assertSame(BackgroundColor::Blue, BoxType::Question->background());
        self::assertNull(BoxType::Note->background());
    }

    public function testForegroundMatchesType(): void
    {
        self::assertSame(ForegroundColor::White, BoxType::Error->foreground());
        self::assertSame(ForegroundColor::White, BoxType::Success->foreground());
        self::assertSame(ForegroundColor::Black, BoxType::Warn->foreground());
        self::assertSame(ForegroundColor::White, BoxType::Question->foreground());
        self::assertNull(BoxType::Note->foreground());
    }
}
