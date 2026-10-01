<?php

declare(strict_types=1);

namespace ConductorMagento2PlatformSupportTest;

use PHPUnit\Framework\TestCase;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader as TwigArrayLoader;

/**
 * CTAP-2123. TLS for Magento's database, Redis and AMQP, set from the environment. Without the new
 * variables the rendered env.php is exactly what the previous template produced.
 */
class EnvPhpTlsTemplateTest extends TestCase
{
    private const BASE_VARS = [
        'encryption_key'    => 'test-key',
        'table_prefix'      => '',
        'database_name'     => 'test',
        'database_user'     => 'test',
        'database_password' => 'test',
        'redis_session_host' => 'redis', 'redis_session_port' => 6379,
        'redis_object_host'  => 'redis', 'redis_object_port'  => 6379,
        'redis_fpc_host'     => 'redis', 'redis_fpc_port'     => 6379,
        'amqp_enabled' => 1, 'amqp_ssl' => 1, 'amqp_ssl_cafile' => '/etc/ssl/mq.pem',
    ];

    private string $dir;

    protected function setUp(): void
    {
        if (!defined('BP')) {
            define('BP', sys_get_temp_dir());
        }
        $this->dir = BP . '/var/tls-' . bin2hex(random_bytes(4));
        mkdir($this->dir, 0700, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*') ?: []);
        rmdir($this->dir);
    }

    public function testWithoutTheNewVariablesTheOutputIsUnchanged(): void
    {
        $previous = (string) shell_exec(
            'git -C ' . escapeshellarg(__DIR__) . ' show origin/master:libs/conductor-magento-2-platform-support/files/app/etc/env.php.twig 2>/dev/null'
        );
        if ($previous === '') {
            self::markTestSkipped('origin/master is not available to compare against.');
        }

        foreach ([self::BASE_VARS, self::BASE_VARS + ['database_ssl_ca' => '/etc/ssl/db.pem', 'database_ssl_verify_cert' => 1]] as $vars) {
            self::assertSame($this->renderText($previous, $vars), $this->renderText($this->template(), $vars));
        }
    }

    public function testDatabaseTlsWithoutACaUsesTheSystemTrustStore(): void
    {
        $empty = $this->file('database-ca.pem', '');
        $config = $this->render(['database_ssl' => '1', 'database_ssl_ca' => $empty]);

        $options = $config['db']['connection']['default']['driver_options'];
        self::assertSame(openssl_get_cert_locations()['default_cert_file'], $options[\PDO::MYSQL_ATTR_SSL_CA]);
        self::assertTrue($options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
        self::assertArrayNotHasKey(\PDO::MYSQL_ATTR_SSL_CERT, $options);
    }

    public function testDatabaseTlsUsesARenderedCaRelativeToTheMagentoRoot(): void
    {
        $ca = $this->file('database-ca.pem', "-----BEGIN CERTIFICATE-----\nx\n-----END CERTIFICATE-----\n");
        $relative = substr($ca, strlen(BP) + 1);

        $options = $this->render(['database_ssl' => '1', 'database_ssl_ca' => $relative, 'database_ssl_verify_cert' => '0'])
            ['db']['connection']['default']['driver_options'];

        self::assertSame($ca, $options[\PDO::MYSQL_ATTR_SSL_CA]);
        self::assertFalse($options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
    }

    public function testDatabaseTlsOffRendersNoDriverOptions(): void
    {
        self::assertArrayNotHasKey('driver_options', $this->render(['database_ssl' => '0'])['db']['connection']['default']);
    }

    public function testRedisTlsUsesTheTlsScheme(): void
    {
        $config = $this->render(['redis_session_tls' => '1', 'redis_object_tls' => '1', 'redis_fpc_tls' => '1']);

        self::assertSame('tls://redis', $config['session']['redis']['host']);
        self::assertSame('tls://redis', $config['cache']['frontend']['default']['backend_options']['server']);
        self::assertSame('tls://redis', $config['cache']['frontend']['page_cache']['backend_options']['server']);
        self::assertSame('redis', $this->render(['redis_session_tls' => '0'])['session']['redis']['host']);
    }

    public function testAmqpVerificationAndAnEmptyCaFile(): void
    {
        $empty = $this->file('rabbitmq-ca.pem', '');
        $ssl = $this->render(['amqp_ssl_verify' => '0', 'amqp_ssl_cafile' => $empty])['queue']['amqp']['ssl_options'];

        self::assertSame(['verify_peer' => false, 'verify_peer_name' => false], $ssl);
    }

    private function file(string $name, string $content): string
    {
        file_put_contents($this->dir . '/' . $name, $content);

        return $this->dir . '/' . $name;
    }

    private function template(): string
    {
        return (string) file_get_contents(__DIR__ . '/../files/app/etc/env.php.twig');
    }

    private function renderText(string $template, array $vars): string
    {
        // As ApplicationSkeletonDeployer renders: no autoescaping.
        $twig = new TwigEnvironment(new TwigArrayLoader([]), ['debug' => true, 'autoescape' => false]);

        return $twig->createTemplate($template)->render(array_replace(self::BASE_VARS, $vars));
    }

    private function render(array $vars): array
    {
        $file = tempnam(sys_get_temp_dir(), 'env-php-') . '.php';
        file_put_contents($file, $this->renderText($this->template(), $vars));
        try {
            $config = include $file;
        } finally {
            unlink($file);
        }
        self::assertIsArray($config);

        return $config;
    }
}
