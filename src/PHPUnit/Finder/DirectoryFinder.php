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
use Scenario\Core\Application;

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
                return $this->getDirectories($file);
            }
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function getDirectories(string $configXmlFile): array
    {
        $dom = new DOMDocument();
        $dom->load($configXmlFile);

        $xpath = new DOMXPath($dom);
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
    }
}
