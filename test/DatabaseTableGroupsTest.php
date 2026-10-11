<?php

namespace ConductorMagento2PlatformSupportTest;

use ConductorAppOrchestration\Config\SnapshotConfig;
use ConductorMagento2PlatformSupport\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * CTAP-2147. The database `@core` table group is split into one group per reason a table is excluded,
 * and stays their union, so a plan using `@core` excludes what it always did. Expanded through the
 * real SnapshotConfig.
 */
class DatabaseTableGroupsTest extends TestCase
{
    /**
     * The flat list @core held before the split, in its original order, without its three duplicate
     * entries and the stray "UNCLAIMED_PROPERTY".
     */
    private const CORE_BEFORE_SPLIT = [
        '*_cl',
        '*_debug',
        '*_log',
        '*_lock',
        '*_replica',
        '*_tmp',
        'adminnotification_inbox',
        'admin_passwords',
        'admin_system_messages',
        'admin_user',
        'admin_user_session',
        'api2_acl_attribute',
        'api2_acl_role',
        'api2_acl_rule',
        'api2_acl_user',
        'api_assert',
        'api_role',
        'api_rule',
        'api_session',
        'api_user',
        'catalog_compare_item',
        'catalogsearch_fulltext',
        'core_cache',
        'core_cache_tag',
        'core_session',
        'coupon_aggregated',
        'coupon_aggregated_order',
        'coupon_aggregated_updated',
        'cron_schedule',
        'customer_address_entity',
        'customer_address_entity_*',
        'customer_entity',
        'customer_entity_*',
        'customer_grid_flat',
        'customer_visitor',
        'dataflow_batch',
        'dataflow_batch_export',
        'dataflow_batch_import',
        'dataflow_import_data',
        'dataflow_profile_history',
        'dataflow_session',
        'dataintercept_api_debug',
        'downloadable_link_purchased',
        'downloadable_link_purchased_item',
        'enterprise_catalogpermissions_index',
        'enterprise_catalogpermissions_index_product',
        'enterprise_customer_sales_order',
        'enterprise_customer_sales_order_address',
        'enterprise_customer_quote',
        'enterprise_customer_quote_address',
        'enterprise_customerbalance',
        'enterprise_customerbalance_history',
        'enterprise_customersegment_customer',
        'enterprise_customersegment_event',
        'enterprise_giftcardaccount',
        'enterprise_giftcardaccount_history',
        'enterprise_giftcardaccount_pool',
        'enterprise_giftregistry_data',
        'enterprise_giftregistry_entity',
        'enterprise_giftregistry_item',
        'enterprise_giftregistry_label',
        'enterprise_giftregistry_person',
        'enterprise_giftregistry_type',
        'enterprise_giftregistry_type_info',
        'enterprise_invitation',
        'enterprise_invitation_status_history',
        'enterprise_invitation_track',
        'enterprise_logging_event',
        'enterprise_logging_event_changes',
        'enterprise_logging_event_track',
        'enterprise_reminder_rule',
        'enterprise_reminder_rule_coupon',
        'enterprise_reminder_rule_website',
        'enterprise_reward',
        'enterprise_reward_history',
        'enterprise_rma',
        'enterprise_rma_grid',
        'enterprise_rma_item_entity',
        'enterprise_rma_item_entity_datetime',
        'enterprise_rma_item_entity_decimal',
        'enterprise_rma_item_entity_int',
        'enterprise_rma_item_entity_text',
        'enterprise_rma_item_form_attribute',
        'enterprise_rma_shipping_label',
        'enterprise_rma_status_history',
        'enterprise_sales_creditmemo_grid_archive',
        'enterprise_sales_invoice_grid_archive',
        'enterprise_sales_order_grid_archive',
        'enterprise_sales_shipment_grid_archive',
        'enterprise_targetrule_index',
        'enterprise_targetrule_index_crosssell',
        'enterprise_targetrule_index_crosssell_product',
        'enterprise_targetrule_index_related',
        'enterprise_targetrule_index_related_production',
        'enterprise_targetrule_index_upsell',
        'enterprise_targetrule_index_upsell_product',
        'enterprise_url_rewrite',
        'enterprise_url_rewrite_category_cl',
        'enterprise_url_rewrite_product_cl',
        'enterprise_url_rewrite_redirect_cl',
        'gift_message',
        'importexport_importdata',
        'log_customer',
        'log_quote',
        'log_summary',
        'log_summary_type',
        'log_url',
        'log_url_info',
        'log_visitor',
        'log_visitor_info',
        'magento_sales_creditmemo_grid_archive',
        'magento_sales_invoice_grid_archive',
        'magento_sales_order_grid_archive',
        'magento_sales_shipment_grid_archive',
        'newsletter_problem',
        'newsletter_queue',
        'newsletter_queue_link',
        'newsletter_queue_store_link',
        'newsletter_subscriber',
        'newsletter_subscriber_backup',
        'oauth_consumer',
        'oauth_nonce',
        'oauth_token',
        'paypal_payment_transaction',
        'paypal_settlement_report',
        'paypal_settlement_report_row',
        'persistent_session',
        'poll_vote',
        'product_alert_price',
        'product_alert_stock',
        'quote',
        'quote_address',
        'quote_address_item',
        'quote_id_mask',
        'quote_item',
        'quote_item_option',
        'quote_payment',
        'quote_preview',
        'quote_shipping_rate',
        'rating_option_vote',
        'rating_option_vote_aggregated',
        'report_compared_product_index',
        'report_event',
        'report_viewed_product_aggregated_daily',
        'report_viewed_product_aggregated_monthly',
        'report_viewed_product_aggregated_yearly',
        'report_viewed_product_index',
        'reporting_counts',
        'reporting_module_status',
        'reporting_orders',
        'reporting_system_updates',
        'reporting_users',
        'review',
        'review_detail',
        'review_entity_summary',
        'review_store',
        'sales_bestsellers_aggregated_daily',
        'sales_bestsellers_aggregated_monthly',
        'sales_bestsellers_aggregated_yearly',
        'sales_billing_agreement',
        'sales_billing_agreement_order',
        'sales_creditmemo',
        'sales_creditmemo_comment',
        'sales_creditmemo_grid',
        'sales_creditmemo_item',
        'sales_invoice',
        'sales_invoice_comment',
        'sales_invoice_grid',
        'sales_invoice_item',
        'sales_order',
        'sales_order_address',
        'sales_order_grid',
        'sales_order_item',
        'sales_order_payment',
        'sales_order_status_history',
        'sales_shipment',
        'sales_shipment_comment',
        'sales_shipment_grid',
        'sales_shipment_item',
        'sales_shipment_track',
        'sales_invoiced_aggregated',
        'sales_invoiced_aggregated_order',
        'sales_order_aggregated_created',
        'sales_order_aggregated_updated',
        'sales_order_tax',
        'sales_order_tax_item',
        'sales_payment_transaction',
        'sales_recurring_profile',
        'sales_recurring_profile_order',
        'sales_refunded_aggregated',
        'sales_refunded_aggregated_order',
        'sales_shipping_aggregated',
        'sales_shipping_aggregated_order',
        'salesrule_coupon_usage',
        'salesrule_customer',
        'sequence_creditmemo_*',
        'sequence_invoice_*',
        'sequence_order_*',
        'sequence_rma_item_*',
        'sequence_shipment_*',
        'sitemap',
        'tag',
        'tag_properties',
        'tag_relation',
        'tag_summary',
        'tax_order_aggregated_created',
        'tax_order_aggregated_updated',
        'wishlist',
        'wishlist_item',
        'wishlist_item_option',
    ];

    /** The CTAP-2147 purpose groups, deprecated by CTAP-2161, and what @core lists itself. */
    private const PURPOSE_GROUPS = ['logs', 'sessions', 'admin', 'reports', 'customers', 'sales', 'magento1'];
    private const CORE_OWN_ENTRIES = ['*_cl', '*_replica', '*_tmp', 'importexport_importdata', 'sitemap'];

    /** CTAP-2161: groups by the nature of the data. They may overlap. */
    private const NATURE_GROUPS = ['generated', 'cache', 'scratch', 'personal_data', 'environment'];

    private SnapshotConfig $snapshotConfig;

    public function setUp(): void
    {
        $platform = (new ConfigProvider())()['application_orchestration']['platforms']['magento2'];
        $this->snapshotConfig = new SnapshotConfig($platform['snapshot']);
    }

    public function testCoreExcludesExactlyWhatItDidBefore(): void
    {
        $expected = self::CORE_BEFORE_SPLIT;
        sort($expected);

        $this->assertSame($expected, $this->snapshotConfig->expandDatabaseTableGroups(['@core']));
    }

    /** The purpose groups do not overlap, and with @core's own entries they make up @core. */
    public function testThePurposeGroupsPartitionCore(): void
    {
        $all = self::CORE_OWN_ENTRIES;
        foreach (self::PURPOSE_GROUPS as $group) {
            $tables = $this->snapshotConfig->expandDatabaseTableGroups(['@' . $group]);
            $this->assertNotEmpty($tables, "Group \"$group\" is empty.");
            $this->assertSame([], array_values(array_intersect($all, $tables)), "Group \"$group\" repeats a table.");
            $all = [...$all, ...$tables];
        }

        $this->assertCount(count(self::CORE_BEFORE_SPLIT), $all);
    }

    /**
     * Moving a plan from @core to the nature groups must not start copying a table @core left out.
     * Every Magento 2 entry of @core is matched by a nature group; the Magento 1 names match no table.
     */
    public function testTheNatureGroupsCoverEveryMagento2EntryOfCore(): void
    {
        $natures = $this->snapshotConfig->expandDatabaseTableGroups(
            array_map(static fn(string $g): string => '@' . $g, self::NATURE_GROUPS)
        );
        $magento1 = $this->snapshotConfig->expandDatabaseTableGroups(['@magento1']);

        $uncovered = array_values(array_filter(
            array_diff(self::CORE_BEFORE_SPLIT, $magento1),
            static fn(string $entry): bool => ! array_filter($natures, static fn(string $p): bool => fnmatch($p, $entry))
        ));

        $this->assertSame([], $uncovered);
    }

    public function testNatureGroupsHoldWhatTheirNameSays(): void
    {
        $expand = fn(string $group): array => $this->snapshotConfig->expandDatabaseTableGroups(["@$group"]);

        foreach (['*_cl', 'sales_bestsellers_aggregated_daily'] as $table) {
            $this->assertContains($table, $expand('generated'));
        }
        $this->assertSame(['cache', 'cache_tag'], $expand('cache'));
        foreach (['cron_schedule', 'session', 'queue_message', 'magento_bulk'] as $table) {
            $this->assertContains($table, $expand('scratch'));
        }
        foreach (['customer_entity', 'sales_order', 'quote', 'admin_user', 'session', 'vault_payment_token'] as $table) {
            $this->assertContains($table, $expand('personal_data'));
        }
        foreach (['oauth_token', 'oauth_consumer', 'integration', 'admin_passwords', 'sitemap'] as $table) {
            $this->assertContains($table, $expand('environment'));
        }
    }

    /** A table can be of several natures: an admin user is a person and a credential. */
    public function testNatureGroupsMayOverlap(): void
    {
        $this->assertContains('admin_user', $this->snapshotConfig->expandDatabaseTableGroups(['@personal_data']));
        $this->assertContains('admin_user', $this->snapshotConfig->expandDatabaseTableGroups(['@environment']));
        $this->assertSame(
            1,
            count(array_keys(
                $this->snapshotConfig->expandDatabaseTableGroups(['@personal_data', '@environment']),
                'admin_user'
            ))
        );
    }

    public function testCoreAndThePurposeGroupsAreDeprecated(): void
    {
        $this->assertSame(
            ['core', 'common_modules', ...self::PURPOSE_GROUPS],
            array_keys($this->snapshotConfig->deprecatedDatabaseTableGroups)
        );
    }

    /** A copy that keeps orders and customers but drops what Magento rebuilds and the noise. */
    public function testGroupsComposeInOneList(): void
    {
        $tables = $this->snapshotConfig->expandDatabaseTableGroups(['@generated', '@scratch', 'my_table']);

        $this->assertContains('*_cl', $tables);
        $this->assertContains('cron_schedule', $tables);
        $this->assertContains('sales_bestsellers_aggregated_daily', $tables);
        $this->assertContains('my_table', $tables);
        $this->assertNotContains('sales_order', $tables);
        $this->assertNotContains('customer_entity', $tables);
    }

    public function testPrivateDataGroups(): void
    {
        $this->assertContains('admin_user', $this->snapshotConfig->expandDatabaseTableGroups(['@admin']));
        $this->assertContains('customer_entity', $this->snapshotConfig->expandDatabaseTableGroups(['@customers']));
        $this->assertContains('sales_order', $this->snapshotConfig->expandDatabaseTableGroups(['@sales']));
        $this->assertContains('sequence_order_*', $this->snapshotConfig->expandDatabaseTableGroups(['@sales']));
    }
}
