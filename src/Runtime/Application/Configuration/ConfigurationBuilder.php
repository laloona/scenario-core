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
use DOMElement;
use DOMXPath;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Stateforge\Scenario\Core\Runtime\Exception\Application\ConnectionAlreadyExistsException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\SuiteAlreadyExistsException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\SuiteWithoutDirectoryException;

final class ConfigurationBuilder
{
    public function __construct(
        private ConfigurationFinder $finder,
        private XMLParser $parser,
    ) {
    }

    public function build(): Configuration
    {
        $file = $this->finder->find();

        if ($file === null) {
            return new DefaultConfiguration();
        }

        $configuration = new LoadedConfiguration(new DefaultConfiguration());
        $doc = $this->parser->parse($file);

        $this->attributes($configuration, $doc);
        $this->connections($configuration, $doc);
        $this->suites($configuration, $doc);

        return $configuration;
    }

    private function attributes(LoadedConfiguration $configuration, DOMDocument $doc): void
    {
        $root = $doc->documentElement;
        if ($root === null) {
            return;
        }

        if ($root->hasAttribute('bootstrap') === true) {
            $configuration->setBootstrap($root->getAttribute('bootstrap'));
        }
        if ($root->hasAttribute('cacheDirectory') === true) {
            $configuration->setCacheDirectory($root->getAttribute('cacheDirectory'));
        }
    }

    private function connections(LoadedConfiguration $configuration, DOMDocument $doc): void
    {
        /** @var array<string, ConnectionValue> $connectionObjects */
        $connectionObjects = [];
        $xpath = new DOMXPath($doc);
        $connections = $xpath->query('/scenario/database/connection');
        if ($connections === false) {
            $configuration->setConnections($connectionObjects);
            return;
        }

        foreach ($connections as $connectionNode) {
            if (!$connectionNode instanceof DOMElement) {
                continue;
            }

            $name = $connectionNode->attributes->getNamedItem('name')?->nodeValue;
            if (isset($connectionObjects[$name ?? '']) === true) {
                throw new ConnectionAlreadyExistsException($name ?? '');
            }

            $connectionObjects[$name ?? ''] = new ConnectionValue(
                $name,
                $connectionNode->textContent,
            );
        }

        $configuration->setConnections($connectionObjects);
    }

    private function suites(LoadedConfiguration $configuration, DOMDocument $doc): void
    {
        /** @var array<string, SuiteValue> $suiteObjects */
        $suiteObjects = [];
        $xpath = new DOMXPath($doc);
        $suites = $xpath->query('/scenario/suites/suite');
        if ($suites === false) {
            $configuration->setSuites($suiteObjects);
            return;
        }

        foreach ($suites as $suiteNode) {
            if (!$suiteNode instanceof DOMElement) {
                continue;
            }

            $name = $suiteNode->getAttribute('name');
            if ($name === '') {
                $name = 'main';
            }
            if (isset($suiteObjects[$name]) === true) {
                throw new SuiteAlreadyExistsException($name);
            }

            $dirNodes = $xpath->query('directory', $suiteNode);
            $directory = '';
            if ($dirNodes !== false) {
                $first = $dirNodes->item(0);
                if ($first !== null) {
                    $directory = $first->nodeValue ?? '';
                }
            }

            if ($directory === '') {
                throw new SuiteWithoutDirectoryException($name);
            }

            $suiteObjects[$name] = new SuiteValue($name, $directory);
        }

        $configuration->setSuites($suiteObjects);
    }
}
