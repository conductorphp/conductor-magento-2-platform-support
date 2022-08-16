<?php

namespace ConductorMagento2PlatformSupport;

class ConfigProvider
{
    /**
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'application_orchestration' => [
                'platforms' => [
                    'magento2' => $this->getPlatformConfig(),
                ],
            ],
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    private function getPlatformConfig(): array
    {
        return array_merge(
            [
                'source_file_path' => dirname(__DIR__) . '/files',
            ],
            include __DIR__ . '/../config/platform.php'
        );
    }

    private function getDependencyConfig(): array
    {
        return require(__DIR__ . '/../config/dependencies.php');
    }

}
