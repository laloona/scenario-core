<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Files;

use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;

final class CustomAttributeHandler extends AttributeHandler
{
    protected function attributeName(): string
    {
        return ApplyScenario::class;
    }

    protected function execute(AttributeContext $context, object $metaData): void
    {
        // Nothing to do
    }
}
