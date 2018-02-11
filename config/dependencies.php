<?php

namespace ConductorMagento2PlatformSupport;

return [
    'invokables' => [
        \ConductorAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface::class => AppMaintenanceStrategyFactory::class,
    ]
];
