<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Runtime\NamespaceResolver;

#[CoversClass(NamespaceResolver::class)]
#[Group('runtime')]
#[Small]
final class NamespaceResolverTest extends TestCase
{
    #[DataProvider('directoryProvider')]
    public function testConvertsDirectoryToNamespace(string $directory, string $expectedNamespace): void
    {
        $resolver = new NamespaceResolver();

        self::assertSame($expectedNamespace, $resolver->toNamespace($directory));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function directoryProvider(): array
    {
        return [
            'unix separators' => ['scenario/main', 'Scenario\Main'],
            'windows separators' => ['scenario\main', 'Scenario\Main'],
            'mixed separators' => ['tests/scenario\main', 'Tests\Scenario\Main'],
            'single directory' => ['scenarios', 'Scenarios'],
            'existing capitalization' => ['app/customScenarios', 'App\CustomScenarios'],
            'empty directory' => ['', ''],
        ];
    }
}
