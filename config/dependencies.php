<?php

namespace DevopsToolMagento2PlatformSupport;

return [
    'invokables' => [
        \DevopsToolAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface::class => AppMaintenanceStrategyFactory::class,
    ]
];
