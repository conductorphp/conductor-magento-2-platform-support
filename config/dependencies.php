<?php

namespace ConductorMagento2PlatformSupport;

return [
    'aliases' => [
        \ConductorAppOrchestration\Deploy\CodeDeploymentStateInterface::class => Deploy\CodeDeploymentState::class,
        \ConductorAppOrchestration\Maintenance\MaintenanceStrategyInterface::class => AppMaintenanceStrategy::class,
    ],
];
