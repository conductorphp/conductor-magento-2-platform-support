<?php

namespace DevopsToolMagento2PlatformSupport;

use DevopsToolAppOrchestration\ApplicationConfig;
use DevopsToolAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AppMaintenanceStrategy implements MaintenanceStrategyInterface, LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param ApplicationConfig $application
     * @param string|null       $branch
     */
    public function enable(ApplicationConfig $application, string $branch = null): void
    {
        // TODO: Implement enable() method.
        throw new \LogicException(__METHOD__ . ' not yet implemented.');
    }

    /**
     * @param ApplicationConfig $application
     * @param string|null       $branch
     */
    public function disable(ApplicationConfig $application, string $branch = null): void
    {
        // TODO: Implement disable() method.
        throw new \LogicException(__METHOD__ . ' not yet implemented.');
    }

    /**
     * @param ApplicationConfig $application
     * @param string|null       $branch
     *
     * @return bool
     */
    public function isEnabled(ApplicationConfig $application, string $branch = null): bool
    {
        // TODO: Implement isEnabled() method.
        throw new \LogicException(__METHOD__ . ' not yet implemented.');
    }
}
