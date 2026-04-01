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
use Scenario\Core\Console\Input\ParameterParser;

#[CoversClass(ParameterParser::class)]
#[Group('console')]
#[Small]
final class ParameterParserTest extends TestCase
{
    public function testParsesSingleStringParameter(): void
    {
        self::assertSame(
            ['env' => 'test'],
            (new ParameterParser())->parse('env=test'),
        );
    }

    public function testParsesListOfParametersAndCollectsRepeatedNames(): void
    {
        self::assertSame(
            [
                'env' => 'test',
                'tag' => ['first', 'second', 'third=thing'],
            ],
            (new ParameterParser())->parse(['env=test', 'tag=first', 'tag=second', 'tag=third=thing']),
        );
    }

    public function testReturnsEmptyArrayForNullInput(): void
    {
        self::assertSame([], (new ParameterParser())->parse(null));
    }
}
