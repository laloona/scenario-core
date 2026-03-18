<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Application\Configuration;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Scenario\Core\Runtime\Exception\Application\XMLParserException;
use SplFileInfo;
use function file_put_contents;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(XMLParser::class)]
#[UsesClass(XMLParserException::class)]
#[Group('runtime')]
#[Small]
final class XMLParserTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/scenario_xml_' . uniqid();
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (scandir($this->tmpDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                unlink($this->tmpDir . '/' . $file);
            }
        }

        rmdir($this->tmpDir);
    }

    public function testThrowsExceptionIfXsdDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new XMLParser($this->tmpDir . '/missing.xsd');
    }

    public function testThrowsExceptionIfXmlCannotBeLoaded(): void
    {
        $xsd = $this->tmpDir . '/schema.xsd';
        file_put_contents($xsd, $this->validXsd());

        $xml = $this->tmpDir . '/invalid.xml';
        file_put_contents($xml, 'not xml');

        $this->expectException(XMLParserException::class);

        (new XMLParser($xsd))->parse(new SplFileInfo($xml));
    }

    public function testThrowsExceptionIfXmlDoesNotValidate(): void
    {
        $xsd = $this->tmpDir . '/schema.xsd';
        file_put_contents($xsd, $this->validXsd());

        $xml = $this->tmpDir . '/invalid.xml';
        file_put_contents($xml, '<invalid></invalid>');

        $this->expectException(XMLParserException::class);

        (new XMLParser($xsd))->parse(new SplFileInfo($xml));
    }

    public function testParsesValidXml(): void
    {
        $xsd = $this->tmpDir . '/schema.xsd';
        file_put_contents($xsd, $this->validXsd());

        $xml = $this->tmpDir . '/valid.xml';
        file_put_contents($xml, '<root></root>');

        self::assertInstanceOf(DOMDocument::class, (new XMLParser($xsd))->parse(new SplFileInfo($xml)));
    }

    private function validXsd(): string
    {
        return <<<XSD
<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="root"/>
</xs:schema>
XSD;
    }
}
