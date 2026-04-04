<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\PHPUnit\Configuration;

use DOMDocument;
use DOMXPath;
use Stateforge\Scenario\Core\PHPUnit\Extension;

final class Configured implements ConfiguredInterface
{
    public function __construct(private ConfigFinder $finder)
    {
    }

    /**
     * @phpstan-impure
     */
    public function isConfigured(): bool
    {
        $configFile = $this->finder->find();

        if ($configFile === null) {
            return false;
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
            return true;
        }

        return false;
    }
}
