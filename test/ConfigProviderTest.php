<?php

namespace ConductorMagento2PlatformSupportTest;

use ConductorMagento2PlatformSupport\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function setUp(): void
    {
        $this->configProvider = new ConfigProvider();
    }

    public function testInvoke()
    {
        $configProvider = $this->configProvider;
        $this->assertIsArray($configProvider());
    }

}
