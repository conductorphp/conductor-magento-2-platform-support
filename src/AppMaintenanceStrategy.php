<?php

namespace DevopsToolMagento2PlatformSupport;

use DevopsToolAppOrchestration\ApplicationConfig;
use DevopsToolAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AppMaintenanceStrategy implements MaintenanceStrategyInterface, LoggerAwareInterface
{
    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;

    /**
     * AppMaintenanceStrategy constructor.
     *
     * @param ApplicationConfig $applicationConfig
     */
    public function __construct(ApplicationConfig $applicationConfig)
    {
        $this->applicationConfig = $applicationConfig;
    }

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
     * @param string|null $branch
     */
    public function enable(string $branch = null): void
    {
        // TODO: Implement enable() method.
        throw new \LogicException(__METHOD__ . ' not yet implemented.');
    }

    /**
     * @param string|null $branch
     */
    public function disable(string $branch = null): void
    {
        // TODO: Implement disable() method.
        throw new \LogicException(__METHOD__ . ' not yet implemented.');
    }

    /**
     * @param string|null $branch
     *
     * @return bool
     */
    public function isEnabled(string $branch = null): bool
    {
        // TODO: Implement isEnabled() method.
        throw new \LogicException(__METHOD__ . ' not yet implemented.');
    }
}
