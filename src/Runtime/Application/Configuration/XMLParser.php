<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Application\Configuration;

use DOMDocument;
use InvalidArgumentException;
use SplFileInfo;
use Stateforge\Scenario\Core\Runtime\Exception\Application\XMLParserException;
use function is_file;
use function libxml_clear_errors;
use function libxml_use_internal_errors;
use const LIBXML_NONET;

final class XMLParser
{
    public function __construct(private string $xsdPath)
    {
        if (is_file($this->xsdPath) === false) {
            throw new InvalidArgumentException('unable to find xsd file: ' . $this->xsdPath);
        }
    }

    public function parse(SplFileInfo $file): DOMDocument
    {
        $previousSetting = libxml_use_internal_errors(true);

        try {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = false;

            if ($doc->load($file->getPathname(), LIBXML_NONET) === false) {
                libxml_clear_errors();
                throw new XMLParserException('unable to load configuration xml');
            }

            if ($doc->schemaValidate($this->xsdPath) === false) {
                libxml_clear_errors();
                throw new XMLParserException('configuration xml does not validate');
            }

            return $doc;
        } finally {
            libxml_use_internal_errors($previousSetting);
        }
    }
}
