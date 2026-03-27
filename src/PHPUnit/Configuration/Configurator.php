<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit\Configuration;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Scenario\Core\PHPUnit\Extension;

final class Configurator
{
    public function __construct(private ConfigFinder $finder)
    {
    }

    public function configure(): void
    {
        $configFile = $this->finder->find();

        if ($configFile === null) {
            return;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        $dom->load($configFile);

        $xpath = new DOMXPath($dom);
        $extensionClass = Extension::class;

        $existing = $xpath->query("//extensions/bootstrap[@class='{$extensionClass}']");
        if ($existing === false
            || $existing->length > 0) {
            return;
        }

        $phpunitNode = $dom->getElementsByTagName('phpunit')->item(0);
        if ($phpunitNode === null) {
            return;
        }

        $extensions = $xpath->query('//extensions');
        if ($extensions === false
            || $extensions->length === 0) {
            $extensionsNode = $dom->createElement('extensions');
            $phpunitNode->appendChild($extensionsNode);
        } else {
            $extensionsNode = $extensions->item(0);
            if (!$extensionsNode instanceof DOMElement) {
                return;
            }
        }

        $bootstrapNode = $dom->createElement('bootstrap');
        $bootstrapNode->setAttribute('class', $extensionClass);
        $extensionsNode->appendChild($bootstrapNode);

        $dom->save($configFile);
    }
}
