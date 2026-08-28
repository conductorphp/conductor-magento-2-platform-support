<?php

declare(strict_types=1);

namespace ConductorMagento2PlatformSupportTest;

use PHPUnit\Framework\TestCase;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader as TwigArrayLoader;

/**
 * Renders the shipped app/etc/env.php.twig the same way
 * ConductorAppOrchestration\Deploy\ApplicationSkeletonDeployer does, so that a
 * template var declared in a project's global.yaml is proven to reach the
 * rendered env.php rather than being silently inert (CTAP-1544).
 */
class EnvPhpTemplateTest extends TestCase
{
    /** Minimum vars the template needs to render a valid env.php. */
    private const BASE_VARS = [
        'encryption_key' => 'test-key',
        'table_prefix' => '',
        'database_name' => 'test',
        'database_user' => 'test',
        'database_password' => 'test',
    ];

    public function testMaxMessagesDefaultsToMagentoBuiltIn(): void
    {
        $config = $this->render([]);

        $this->assertSame(10000, $config['cron_consumers_runner']['max_messages']);
    }

    public function testMaxMessagesIsCappedByTemplateVar(): void
    {
        $config = $this->render(['amqp_max_messages' => 500]);

        $this->assertSame(500, $config['cron_consumers_runner']['max_messages']);
    }

    /**
     * Magento treats a falsy max_messages as "no --max-messages flag", i.e.
     * unbounded. Zero must survive the |default() filter to mean that.
     */
    public function testMaxMessagesOfZeroIsPreservedAsUnbounded(): void
    {
        $config = $this->render(['amqp_max_messages' => 0]);

        $this->assertSame(0, $config['cron_consumers_runner']['max_messages']);
    }

    /**
     * Idle consumers must be able to exit so they release the cron wrapper's
     * lock, which means a configured 0 may not be flipped back to the default 1.
     */
    public function testConsumersWaitForMessagesOfZeroIsPreserved(): void
    {
        $config = $this->render([
            'amqp_enabled' => 1,
            'amqp_consumers_wait_for_messages' => 0,
        ]);

        $this->assertSame(0, $config['queue']['consumers_wait_for_messages']);
    }

    private function render(array $templateVars): array
    {
        $template = __DIR__ . '/../files/app/etc/env.php.twig';
        $this->assertFileExists($template);

        $twig = new TwigEnvironment(new TwigArrayLoader([]), ['debug' => true]);
        $rendered = $twig->createTemplate(file_get_contents($template))
            ->render(array_replace(self::BASE_VARS, $templateVars));

        $file = tempnam(sys_get_temp_dir(), 'env-php-') . '.php';
        file_put_contents($file, $rendered);

        try {
            $config = include $file;
        } finally {
            unlink($file);
        }

        $this->assertIsArray($config, "Rendered env.php did not return an array:\n$rendered");

        return $config;
    }
}
