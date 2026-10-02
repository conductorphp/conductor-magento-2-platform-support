Conductor Magento 2 Platform Support Documentation
==================================================

This module adds [Magento 2](https://magento.com/) platform support for
[Conductor](https://github.com/conductorphp/conductor-core).

## Installation

```bash
composer require conductor/magento-2-platform-support
``` 

Run this command from your Conductor root to install the default build plan:

```bash
cp vendor/conductor/magento-2-platform-support/config/build-plans.php.dist config/autoload/build-plans.global.php
```

Run this command from your Conductor root to install the default snapshot plan:

```bash
cp vendor/conductor/magento-2-platform-support/config/snapshot-plans.php.dist config/autoload/snapshot-plans.global.php
```

Run this command from your Conductor root to install the default deployment plans:

```bash
cp vendor/conductor/magento-2-platform-support/config/deployment-plans.php.dist config/autoload/deployment-plans.global.php
```

## TLS template variables (CTAP-2123)

`app/etc/env.php.twig` takes these TLS variables. Every one is optional; without the variables added
in CTAP-2123, the rendered `env.php` is byte-for-byte what earlier versions produced.

| Variable | Since | Effect |
|---|---|---|
| `database_ssl` | CTAP-2123 | Turn TLS on for the database. Never falls back to plaintext: without a CA file, the system trust store (`openssl_get_cert_locations()`) is used. |
| `database_ssl_ca` | earlier | CA file. Relative paths are under the Magento root. An empty file means none. |
| `database_ssl_cert`, `database_ssl_key` | earlier | Client certificate and key, used together. |
| `database_ssl_verify_cert` | earlier | Verify the server; with `database_ssl` it defaults to on. |
| `redis_session_tls`, `redis_object_tls`, `redis_fpc_tls` | CTAP-2123 | Connect with the `tls://` scheme. |
| `amqp_ssl` | earlier | AMQPS. |
| `amqp_ssl_verify` | CTAP-2123 | Verify the broker, and accept empty certificate files as none. |
| `amqp_ssl_cafile`, `amqp_ssl_certfile`, `amqp_ssl_keyfile` | earlier | CA, client certificate and key. |

When `database_ssl` is passed it alone decides: off renders no driver options even with certificate
paths set, so a config can always pass the paths of its rendered certificate files. Without
`database_ssl`, the earlier behavior holds: driver options are written only when a certificate path
is set. Likewise, without `amqp_ssl_verify` the AMQP `ssl_options` are written as before.

**Redis has no per-connection CA.** `Cm_Cache_Backend_Redis` and the session handler pass the host
to Credis but expose no TLS context options, so a `tls://` connection verifies against PHP's default
trust store (`openssl.cafile`, or the system bundle). A private CA must be installed in the image's
trust store. Managed services with public certificates, such as ElastiCache, need nothing.

Certificate files are usually rendered by the project's conductor config from base64 environment
variables (`${DATABASE_TLS_CA|b64decode:-}`), the way JWT keys are; an unset variable renders an
empty file, which these variables read as "none".

## Media asset groups (CTAP-2146)

Snapshot and deployment plans exclude media paths by group, written `@name` in an asset's
`excludes` (or `includes`) list. Groups compose: list several, or mix them with literal paths.

| Group | Paths under `pub/media` | Regenerable |
|---|---|---|
| `@cache` | `/catalog/category/cache`, `/catalog/product/cache`, `/catalog/placeholder/cache` | Yes: resized images, rebuilt on request or by `catalog:images:resize` |
| `@compiled` | `/css`, `/css_secure`, `/js`, `/js_secure` | Yes: merged and minified CSS/JS, rebuilt on request |
| `@scratch` | `/captcha`, `/tmp` | Yes: short-lived files |
| `@import` | `/import` | **No**: import files are data |
| `@core` | all of the above | Mixed |

`@core` is the union of the other four and is what the distributed plans use, so a plan that
copies media between environments skips all of it.

A **media backup** should exclude only what Magento can regenerate, and keep `/import`:

```yaml
sync-assets:
  class: ConductorAppOrchestration\Snapshot\Command\SyncAssetsCommand
  options:
    assets:
      pub/media:
        location: shared
        ensure: directory
        excludes:
          - '@cache'
          - '@compiled'
          - '@scratch'
```

## Database table groups (CTAP-2147)

A snapshot or deploy plan leaves database tables out by group, written `@name` in a database's
`excludes` list. Table names may use `*` wildcards. Each group is one reason a table is excluded:

| Group | Holds | Rebuilt by Magento |
|---|---|---|
| `@generated` | Indexer changelogs (`*_cl`), replicas, temp tables, import scratch data, sitemap records | Yes |
| `@logs` | `*_log`, `*_debug`, `*_lock`, `cron_schedule`, `report_event` | Not needed |
| `@sessions` | Admin and persistent sessions, visitors, OAuth nonces | Not needed |
| `@reports` | Report aggregates (`*_aggregated*`, bestsellers, viewed products) and analytics data | Yes, by the report refresh and analytics jobs |
| `@admin` | Admin users and passwords, OAuth consumers and tokens, admin notifications | **No**: private |
| `@customers` | Customer accounts and addresses, newsletter subscribers, reviews, ratings, wishlists, alerts | **No**: private |
| `@sales` | Orders, invoices, shipments, credit memos, sequences, carts (quotes), payment records | **No**: private |
| `@magento1` | Magento 1 table names that do not exist on Magento 2 | Not applicable |
| `@core` | all of the above | Mixed |

`@core` is what the distributed plans use. Exclude a subset when a copy should keep some of the data.
For example, a copy for debugging an order problem can keep orders and customers:

```yaml
upload-databases:
  class: ConductorAppOrchestration\Snapshot\Command\UploadDatabasesCommand
  options:
    databases:
      magento:
        excludes:
          - '@generated'
          - '@logs'
          - '@sessions'
          - '@reports'
          - '@admin'
```

Such a snapshot holds customer data; store and share it accordingly.
