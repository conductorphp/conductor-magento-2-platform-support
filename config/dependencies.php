<?php

namespace ConductorMagento2PlatformSupport;

return [
    'factories' => [
        \ConductorAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface::class => AppMaintenanceStrategyFactory::class,
    ]
];
