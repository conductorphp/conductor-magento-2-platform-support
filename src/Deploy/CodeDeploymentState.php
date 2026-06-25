<?php

namespace ConductorMagento2PlatformSupport\Deploy;

use ConductorAppOrchestration\Config\ApplicationConfig;
use ConductorAppOrchestration\Deploy\CodeDeploymentStateInterface;
use ConductorCore\Shell\Adapter\ShellAdapterInterface;
use Exception;

class CodeDeploymentState implements CodeDeploymentStateInterface
{
    private ApplicationConfig $applicationConfig;
    private ShellAdapterInterface $shellAdapter;

    public function __construct(ApplicationConfig $applicationConfig, ShellAdapterInterface $shellAdapter)
    {
        $this->applicationConfig = $applicationConfig;
        $this->shellAdapter = $shellAdapter;
    }

    public function codeDeployed(): bool
    {
        $codeRoot = $this->applicationConfig->getCurrentPath();
        $command = '[[ -f ' . escapeshellcmd("$codeRoot/bin/magento") . ' ]] || exit 1';
        try {
            $this->shellAdapter->runShellCommand($command);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
