<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit\Finder;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Scenario\Core\Runtime\Application;
use function libxml_clear_errors;
use function libxml_use_internal_errors;

final class DirectoryFinder
{
    /**
     * @return list<string>
     */
    public function all(): array
    {
        $files = [ 'phpunit.dist.xml', 'phpunit.xml' ];
        foreach ($files as $file) {
            if (is_file(Application::getRootDir() . DIRECTORY_SEPARATOR . $file)) {
                return $this->getDirectories(Application::getRootDir() . DIRECTORY_SEPARATOR . $file);
            }
        }

        return [];
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
