<?php

namespace Scenario\Other;

use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Scenario;

#[AsScenario('other-scenario')]
final class OtherScenario extends Scenario
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}
