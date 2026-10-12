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
            self::assertSame(
                self::withLegacyAttributeNames($this->renderText($previous, $vars)),
                self::withLegacyAttributeNames($this->renderText($this->template(), $vars))
            );
        }
    }

    public function testDatabaseTlsWithoutACaUsesTheSystemTrustStore(): void
    {
        $empty = $this->file('database-ca.pem', '');
        $config = $this->render(['database_ssl' => '1', 'database_ssl_ca' => $empty]);

        $options = $config['db']['connection']['default']['driver_options'];
        self::assertSame(openssl_get_cert_locations()['default_cert_file'], $options[self::attribute('SSL_CA')]);
        self::assertTrue($options[self::attribute('SSL_VERIFY_SERVER_CERT')]);
        self::assertArrayNotHasKey(self::attribute('SSL_CERT'), $options);
    }

    public function testDatabaseTlsUsesARenderedCaRelativeToTheMagentoRoot(): void
    {
        $ca = $this->file('database-ca.pem', "-----BEGIN CERTIFICATE-----\nx\n-----END CERTIFICATE-----\n");
        $relative = substr($ca, strlen(BP) + 1);

        $options = $this->render(['database_ssl' => '1', 'database_ssl_ca' => $relative, 'database_ssl_verify_cert' => '0'])
            ['db']['connection']['default']['driver_options'];

        self::assertSame($ca, $options[self::attribute('SSL_CA')]);
        self::assertFalse($options[self::attribute('SSL_VERIFY_SERVER_CERT')]);
    }

    public function testDatabaseTlsWithAClientCertificateSetsEveryAttribute(): void
    {
        $pem = "-----BEGIN CERTIFICATE-----\nx\n-----END CERTIFICATE-----\n";
        [$ca, $cert, $key] = [$this->file('ca.pem', $pem), $this->file('cert.pem', $pem), $this->file('key.pem', $pem)];

        $options = $this->render(['database_ssl' => '1', 'database_ssl_ca' => $ca, 'database_ssl_cert' => $cert, 'database_ssl_key' => $key])
            ['db']['connection']['default']['driver_options'];

        self::assertSame([
            self::attribute('SSL_CA')                 => $ca,
            self::attribute('SSL_VERIFY_SERVER_CERT') => true,
            self::attribute('SSL_CERT')               => $cert,
            self::attribute('SSL_KEY')                => $key,
        ], $options);
    }

    /** Without the `database_ssl` flag, the certificate paths alone set the options, as before CTAP-2123. */
    public function testCertificatePathsWithoutTheFlagSetEveryAttribute(): void
    {
        $options = $this->render([
            'database_ssl_ca'          => '/etc/ssl/db-ca.pem',
            'database_ssl_cert'        => '/etc/ssl/db-cert.pem',
            'database_ssl_key'         => '/etc/ssl/db-key.pem',
            'database_ssl_verify_cert' => 0,
        ])['db']['connection']['default']['driver_options'];

        self::assertSame([
            self::attribute('SSL_CERT')               => '/etc/ssl/db-cert.pem',
            self::attribute('SSL_KEY')                => '/etc/ssl/db-key.pem',
            self::attribute('SSL_CA')                 => '/etc/ssl/db-ca.pem',
            self::attribute('SSL_VERIFY_SERVER_CERT') => false,
        ], $options);
    }

    /**
     * CTAP-2417. Each key is written as its Pdo\Mysql::ATTR_* name with the PDO::MYSQL_ATTR_* fallback, so
     * the same env.php runs on PHP 8.2 (no Pdo\Mysql class) and on 8.5 (old names deprecated). render()
     * fails on any deprecation the include raises; the 8.2 side can only be asserted from the text here.
     */
    public function testAttributeKeysFallBackToTheLegacyNames(): void
    {
        $text = $this->renderText($this->template(), [
            'database_ssl_ca' => '/etc/ssl/db-ca.pem', 'database_ssl_cert' => '/etc/ssl/db-cert.pem',
            'database_ssl_key' => '/etc/ssl/db-key.pem', 'database_ssl_verify_cert' => 1,
        ]);

        self::assertDoesNotMatchRegularExpression('/(?<!: )\\\\PDO::MYSQL_ATTR_/', $text);
        foreach (['SSL_CA', 'SSL_CERT', 'SSL_KEY', 'SSL_VERIFY_SERVER_CERT'] as $name) {
            self::assertStringContainsString(
                "(\\defined('Pdo\\Mysql::ATTR_$name') ? \\Pdo\\Mysql::ATTR_$name : \\PDO::MYSQL_ATTR_$name)",
                $text
            );
        }
    }

    public function testDatabaseTlsOffRendersNoDriverOptions(): void
    {
        self::assertArrayNotHasKey('driver_options', $this->render(['database_ssl' => '0'])['db']['connection']['default']);
    }

    /** A config passes the rendered paths whether or not TLS is on; the flag decides. */
    public function testDatabaseTlsOffIgnoresCertificatePaths(): void
    {
        $config = $this->render([
            'database_ssl'      => '0',
            'database_ssl_ca'   => 'var/tls/database-ca.pem',
            'database_ssl_cert' => 'var/tls/database-cert.pem',
            'database_ssl_key'  => 'var/tls/database-key.pem',
        ]);

        self::assertArrayNotHasKey('driver_options', $config['db']['connection']['default']);
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

    /** The attribute's value on this PHP, read without the PHP 8.5 deprecation. */
    private static function attribute(string $name): int
    {
        return \defined('Pdo\\Mysql::ATTR_' . $name)
            ? \constant('Pdo\\Mysql::ATTR_' . $name)
            : \constant('PDO::MYSQL_ATTR_' . $name);
    }

    /** Rendered text with each fallback expression written back as the PDO::MYSQL_ATTR_* name it replaced. */
    private static function withLegacyAttributeNames(string $text): string
    {
        return (string) preg_replace(
            "/\\(\\\\defined\\('Pdo\\\\Mysql::ATTR_(\\w+)'\\) \\? \\\\Pdo\\\\Mysql::ATTR_\\1 : (\\\\PDO::MYSQL_ATTR_\\1)\\)/",
            '$2',
            $text
        );
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
        // Magento turns a deprecation into an error, so including env.php must raise none (CTAP-2417).
        $deprecations = [];
        set_error_handler(static function (int $level, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;

            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);
        try {
            $config = include $file;
        } finally {
            restore_error_handler();
            unlink($file);
        }
        self::assertSame([], $deprecations);
        self::assertIsArray($config);

        return $config;
    }
}
