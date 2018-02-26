<?php

namespace ConductorMagento2PlatformSupport;

use ConductorAppOrchestration\ApplicationConfig;
use ConductorAppOrchestration\MaintenanceStrategy\MaintenanceStrategyInterface;
use ConductorCore\Shell\ShellAdapterManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AppMaintenanceStrategy implements MaintenanceStrategyInterface, LoggerAwareInterface
{
    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;
    /**
     * @var ShellAdapterManager
     */
    private $shellAdapterManager;

    /**
     * AppMaintenanceStrategy constructor.
     *
     * @param ApplicationConfig $applicationConfig
     */
    public function __construct(ApplicationConfig $applicationConfig, ShellAdapterManager $shellAdapterManager)
    {
        $this->applicationConfig = $applicationConfig;
        $this->shellAdapterManager = $shellAdapterManager;
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
     * @param string|null       $branch
     */
    public function enable(string $branch = null): void
    {
        $shellAdapterNames = $this->applicationConfig->getMaintenanceShellAdapters();
        foreach ($shellAdapterNames as $shellAdapterName) {
            $shellAdapter = $this->shellAdapterManager->getAdapter($shellAdapterName);
            $shellAdapter->runShellCommand('bin/magento maintenance:enable', $this->applicationConfig->getCodePath());
        }
    }

    /**
     * @param string|null       $branch
     */
    public function disable(string $branch = null): void
    {
        $shellAdapterNames = $this->applicationConfig->getMaintenanceShellAdapters();
        foreach ($shellAdapterNames as $shellAdapterName) {
            $shellAdapter = $this->shellAdapterManager->getAdapter($shellAdapterName);
            $shellAdapter->runShellCommand('bin/magento maintenance:disable', $this->applicationConfig->getCodePath());
        }
    }

    /**
     * @param string|null       $branch
     *
     * @return bool
     */
    public function isEnabled(string $branch = null): bool
    {
        $shellAdapterNames = $this->applicationConfig->getMaintenanceShellAdapters();

        $serversInMaintenance = [];
        // @todo Run these checks in parallel with amphp
        foreach ($shellAdapterNames as $shellAdapterName) {
            $shellAdapter = $this->shellAdapterManager->getAdapter($shellAdapterName);
            $output = $shellAdapter->runShellCommand('bin/magento maintenance:status', $this->applicationConfig->getCodePath());

            if (false !== strpos($output, 'is active')) {
                $serversInMaintenance[$shellAdapterName] = true;
            }
        }

        if (!$serversInMaintenance) {
            return false;
        }

        if (count($serversInMaintenance) == count($shellAdapterNames)) {
            return true;
        }

        $serversNotInMaintenance = array_diff_key($shellAdapterNames, $serversInMaintenance);
        throw new Exception\RuntimeException(sprintf(
            'Maintenance mode enabled, but servers "%s" are missing maintenance flag.',
            implode(', ', array_keys($serversNotInMaintenance))
        ));
    }

}
