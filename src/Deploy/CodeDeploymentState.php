<?php

namespace ConductorMagento2PlatformSupport\Deploy;

use ConductorAppOrchestration\Config\ApplicationConfig;
use ConductorAppOrchestration\Deploy\CodeDeploymentStateInterface;
use ConductorCore\Shell\Adapter\ShellAdapterInterface;

class CodeDeploymentState implements CodeDeploymentStateInterface
{
    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;
    /**
     * @var ShellAdapterInterface
     */
    private $shellAdapter;

    /**
     * CodeDeploymentState constructor.
     *
     * @param ApplicationConfig     $applicationConfig
     * @param ShellAdapterInterface $shellAdapter
     */
    public function __construct(ApplicationConfig $applicationConfig, ShellAdapterInterface $shellAdapter)
    {
        $this->applicationConfig = $applicationConfig;
        $this->shellAdapter = $shellAdapter;
    }

    /**
     * @param string|null $branch
     *
     * @return bool
     */
    public function codeDeployed(string $branch = null): bool
    {
        $codeRoot = $this->applicationConfig->getCodePath($branch);
        $command = '[[ -f ' . escapeshellcmd("$codeRoot/bin/magento") . ' ]] || exit 1';
        try {
            $this->shellAdapter->runShellCommand($command);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
