<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Tests\Files\AnotherScenario;
use Scenario\Core\Tests\Files\ValidScenario;
use Scenario\Core\Tests\Unit\ApplicationMock;

#[CoversClass(MethodAttributeParser::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(RefreshDatabase::class)]
#[Group('runtime')]
#[Small]
final class MethodAttributeParserTest extends TestCase
{
    use ApplicationMock;

    protected function setUp(): void
    {
        $this->setConfiguration(new LoadedConfiguration(new DefaultConfiguration()));
    }

    protected function tearDown(): void
    {
        $this->resetApplication();
    }

    public function testParseReturnsOnlyConfiguredMethodAttributes(): void
    {
        $attributes = new MethodAttributeParser()->parse(ValidScenario::class, 'up');

        self::assertCount(2, $attributes);
        self::assertSame(RefreshDatabase::class, $attributes[0]->getName());
        self::assertInstanceOf(RefreshDatabase::class, $attributes[0]->newInstance());
        self::assertSame(ApplyScenario::class, $attributes[1]->getName());
        self::assertInstanceOf(ApplyScenario::class, $attributes[1]->newInstance());
    }

    public function testParseReturnsEmptyArrayWhenNoConfiguredAttributeWasFound(): void
    {
        $attributes = new MethodAttributeParser()->parse(AnotherScenario::class, 'up');

        self::assertCount(0, $attributes);
    }
}
