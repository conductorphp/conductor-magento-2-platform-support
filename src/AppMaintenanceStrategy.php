<?php

namespace ConductorMagento2PlatformSupport;

use ConductorAppOrchestration\Config\ApplicationConfig;
use ConductorAppOrchestration\Maintenance\MaintenanceStrategyInterface;
use ConductorCore\Shell\ShellAdapterManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AppMaintenanceStrategy implements MaintenanceStrategyInterface, LoggerAwareInterface
{
    private ApplicationConfig $applicationConfig;
    private ShellAdapterManager $shellAdapterManager;

    public function __construct(ApplicationConfig $applicationConfig, ShellAdapterManager $shellAdapterManager)
    {
        $this->applicationConfig = $applicationConfig;
        $this->shellAdapterManager = $shellAdapterManager;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function enable(string $buildId = null): void
    {
        $currentWorkingDir = $buildId ? $this->applicationConfig->getCodePath($buildId) : $this->applicationConfig->getCurrentPath();
        $shellAdapterNames = $this->applicationConfig->getMaintenanceShellAdapters();
        foreach ($shellAdapterNames as $shellAdapterName) {
            $shellAdapter = $this->shellAdapterManager->getAdapter($shellAdapterName);
            $shellAdapter->runShellCommand('bin/magento maintenance:enable', $currentWorkingDir);
        }
    }

    public function disable(string $buildId = null): void
    {
        $currentWorkingDir = $buildId ? $this->applicationConfig->getCodePath($buildId) : $this->applicationConfig->getCurrentPath();
        $shellAdapterNames = $this->applicationConfig->getMaintenanceShellAdapters();
        foreach ($shellAdapterNames as $shellAdapterName) {
            $shellAdapter = $this->shellAdapterManager->getAdapter($shellAdapterName);
            $shellAdapter->runShellCommand('bin/magento maintenance:disable', $currentWorkingDir);
        }
    }

    public function isEnabled(string $buildId = null): bool
    {
        $currentWorkingDir = $buildId ? $this->applicationConfig->getCodePath($buildId) : $this->applicationConfig->getCurrentPath();
        $shellAdapterNames = $this->applicationConfig->getMaintenanceShellAdapters();

        $serversInMaintenance = [];
        // @todo Run these checks in parallel with amphp
        foreach ($shellAdapterNames as $shellAdapterName) {
            $shellAdapter = $this->shellAdapterManager->getAdapter($shellAdapterName);
            $output = $shellAdapter->runShellCommand('bin/magento maintenance:status', $currentWorkingDir);

            if (false !== strpos($output, 'is active')) {
                $serversInMaintenance[$shellAdapterName] = true;
            }
        }

        if (!$serversInMaintenance) {
            return false;
        }

        if (count($serversInMaintenance) === count($shellAdapterNames)) {
            return true;
        }

        $serversNotInMaintenance = array_diff_key($shellAdapterNames, $serversInMaintenance);
        throw new Exception\RuntimeException(sprintf(
            'Maintenance mode enabled, but servers "%s" are missing maintenance flag.',
            implode(', ', array_keys($serversNotInMaintenance))
        ));
    }

}
