<?php

namespace ConductorMagento2PlatformSupportTest;

use ConductorAppOrchestration\Config\SnapshotConfig;
use ConductorMagento2PlatformSupport\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * CTAP-2146. The media `@core` group is split into `@cache`, `@compiled`, `@scratch` and `@import`,
 * and stays their union, so a plan using `@core` excludes what it always did while a media backup can
 * exclude only the regenerable paths and keep /import. Expanded through the real SnapshotConfig.
 */
class MediaAssetGroupsTest extends TestCase
{
    private SnapshotConfig $snapshotConfig;

    public function setUp(): void
    {
        $platform = (new ConfigProvider())()['application_orchestration']['platforms']['magento2'];
        $this->snapshotConfig = new SnapshotConfig($platform['snapshot']);
    }

    /** The exact list @core held before the split. */
    public function testCoreExcludesExactlyWhatItDidBefore(): void
    {
        $this->assertSame(
            [
                '/captcha',
                '/catalog/category/cache',
                '/catalog/placeholder/cache',
                '/catalog/product/cache',
                '/css',
                '/css_secure',
                '/import',
                '/js',
                '/js_secure',
                '/tmp',
            ],
            $this->snapshotConfig->expandAssetGroups(['@core'])
        );
    }

    public function testTheRegenerableGroupsComposeAndKeepImport(): void
    {
        $this->assertSame(
            [
                '/captcha',
                '/catalog/category/cache',
                '/catalog/placeholder/cache',
                '/catalog/product/cache',
                '/css',
                '/css_secure',
                '/js',
                '/js_secure',
                '/tmp',
            ],
            $this->snapshotConfig->expandAssetGroups(['@cache', '@compiled', '@scratch'])
        );
    }

    public function testEachGroupHoldsItsOwnPaths(): void
    {
        $this->assertSame(
            ['/catalog/category/cache', '/catalog/placeholder/cache', '/catalog/product/cache'],
            $this->snapshotConfig->expandAssetGroups(['@cache'])
        );
        $this->assertSame(['/css', '/css_secure', '/js', '/js_secure'], $this->snapshotConfig->expandAssetGroups(['@compiled']));
        $this->assertSame(['/captcha', '/tmp'], $this->snapshotConfig->expandAssetGroups(['@scratch']));
        $this->assertSame(['/import'], $this->snapshotConfig->expandAssetGroups(['@import']));
    }

    public function testAGroupComposesWithLiteralPaths(): void
    {
        $this->assertSame(
            ['/captcha', '/tmp', '/wysiwyg/.thumbs'],
            $this->snapshotConfig->expandAssetGroups(['@scratch', '/wysiwyg/.thumbs'])
        );
    }

    /** CTAP-2161: the media groups by nature. */
    public function testNatureGroups(): void
    {
        $expand = fn(string $group): array => $this->snapshotConfig->expandAssetGroups(["@$group"]);

        $this->assertSame(['/css', '/css_secure', '/js', '/js_secure'], $expand('generated'));
        $this->assertSame(
            ['/catalog/category/cache', '/catalog/placeholder/cache', '/catalog/product/cache'],
            $expand('cache')
        );
        $this->assertSame(['/captcha', '/tmp'], $expand('scratch'));
        $this->assertSame(['/custom_options', '/customer', '/customer_address', '/import'], $expand('personal_data'));
        $this->assertSame([], $expand('environment'));
    }

    /** Moving a plan from @core to the nature groups leaves out at least what @core did. */
    public function testTheNatureGroupsCoverCore(): void
    {
        $natures = $this->snapshotConfig->expandAssetGroups(
            ['@generated', '@cache', '@scratch', '@personal_data', '@environment']
        );

        $this->assertSame([], array_values(array_diff($this->snapshotConfig->expandAssetGroups(['@core']), $natures)));
    }

    public function testCoreAndTheModuleGroupsAreDeprecated(): void
    {
        $this->assertSame(
            ['core', 'compiled', 'import', 'common_modules'],
            array_keys($this->snapshotConfig->deprecatedAssetGroups)
        );
    }
}
