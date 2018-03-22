<?php

namespace ConductorMagento2PlatformSupport;

return [
    'aliases' => [
        \ConductorAppOrchestration\Deploy\CodeDeploymentStateInterface::class => Deploy\CodeDeploymentState::class,
        \ConductorAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface::class => AppMaintenanceStrategy::class,
    ],
];
