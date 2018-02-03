<?php

namespace DevopsToolMagento2PlatformSupport;

use Symfony\Component\Yaml\Yaml;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
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

    /**
     * @return array
     */
    private function getDependencyConfig(): array
    {
        return require(__DIR__ . '/../config/dependencies.php');
    }

    /**
     * @return array
     */
    private function getPlatformConfig(): array
    {
        return array_merge(
            [
                'source_file_path' => dirname(__DIR__) . '/files',
            ],
            Yaml::parse(file_get_contents(__DIR__ . '/../config/platform.yaml'))
        );
    }

}
