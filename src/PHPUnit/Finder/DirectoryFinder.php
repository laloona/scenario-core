<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\PHPUnit\Finder;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Stateforge\Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use function libxml_clear_errors;
use function libxml_use_internal_errors;
use function rtrim;
use const LIBXML_NONET;

final class DirectoryFinder
{
    public function __construct(private ConfigFinder $finder)
    {
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        $configFile = $this->finder->find();

        if ($configFile === null) {
            return [];
        }

        return $this->getDirectories($configFile);
    }

    /**
     * @return list<string>
     */
    private function getDirectories(string $configXmlFile): array
    {
        $previousSetting = libxml_use_internal_errors(true);

        try {
            $doc = new DOMDocument();
            if ($doc->load($configXmlFile, LIBXML_NONET) === false) {
                libxml_clear_errors();
                return [];
            }

            $xpath = new DOMXPath($doc);
            $suiteDirectories = $xpath->query('//testsuites/testsuite/directory');
            if ($suiteDirectories === false) {
                return [];
            }

            $directories = [];
            foreach ($suiteDirectories as $node) {
                if (!$node instanceof DOMElement) {
                    continue;
                }

                $directories[] = rtrim($node->textContent, '/');
            }

            return $directories;
        } finally {
            libxml_use_internal_errors($previousSetting);
        }
    }
}
