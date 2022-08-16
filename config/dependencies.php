<?php

namespace ConductorMagento2PlatformSupport;

use ConductorAppOrchestration\Deploy\CodeDeploymentStateInterface;
use ConductorAppOrchestration\Maintenance\MaintenanceStrategyInterface;

return [
    'aliases' => [
        CodeDeploymentStateInterface::class => Deploy\CodeDeploymentState::class,
        MaintenanceStrategyInterface::class => AppMaintenanceStrategy::class,
    ],
];
