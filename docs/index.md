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

## Snapshot groups (CTAP-2161)

A snapshot or deploy plan leaves media paths and database tables out by group, written `@name` in an
asset's or a database's `excludes` list. Each group holds one **nature** of data, so a plan names
what it leaves out and why:

| Group | Media (`pub/media`) | Tables |
|---|---|---|
| `@generated` | `/css`, `/js` and their `_secure` twins | Indexer changelogs (`*_cl`), replicas, temp tables, report aggregates, analytics data |
| `@cache` | Resized image and placeholder caches | `cache`, `cache_tag` |
| `@scratch` | `/captcha`, `/tmp` | Logs, debug output, locks, cron history, sessions, visitors, queued messages, bulk operations, import scratch data, admin notifications |
| `@personal_data` | Customer and address file uploads, custom-option files, `/import` | Customers, addresses, admin users, sessions, orders, carts, invoices, shipments and their sequences, payment records, reviews, wishlists, alerts, newsletter subscribers |
| `@environment` | Nothing | Credentials (admin password hashes, OAuth and JWT tokens, integrations, vault payment tokens) and sitemap records |

A table can be of several natures, so it can sit in several groups: an admin user is personal data
and a credential, a session is short-lived and personal. Each group is complete for its nature, and a
plan listing several groups gets each table once. Patterns use `*` (`fnmatch` for tables, rsync rules
for paths, where a leading `/` anchors at the media root).

The groups cover Magento's own tables. Tables of third-party modules are not in them: list the
ones your application needs in a group of your own.

A **seed** for lower environments leaves out everything but the store's own data:

```yaml
upload-databases:
  class: ConductorAppOrchestration\Snapshot\Command\UploadDatabasesCommand
  options:
    databases:
      magento:
        excludes: [ '@generated', '@cache', '@scratch', '@personal_data', '@environment' ]
sync-assets:
  class: ConductorAppOrchestration\Snapshot\Command\SyncAssetsCommand
  options:
    assets:
      pub/media:
        location: shared
        ensure: directory
        excludes: [ '@generated', '@cache', '@scratch', '@personal_data' ]
```

A **media backup** keeps everything Magento cannot rebuild, customer uploads included:
`excludes: [ '@generated', '@cache', '@scratch' ]`.

### Deprecated groups

These keep working and expand exactly as before. A plan that names one logs a warning saying what
to use instead (with `conductor/application-orchestration` 4.7 or later). They are removed in the
next major.

| Group | Use instead |
|---|---|
| media `@core` | `@generated`, `@cache`, `@scratch`, and `@personal_data` for customer files and `/import` |
| media `@compiled` | `@generated` |
| media `@import` | `@personal_data` |
| `@common_modules` (media and tables) | The module groups your application needs (they stay), or a group of your own |
| tables `@core` | `@generated`, `@cache`, `@scratch`, `@personal_data`, `@environment` |
| `@logs` | `@scratch` |
| `@sessions` | `@scratch` and `@personal_data` |
| `@admin` | `@personal_data` and `@environment` |
| `@reports` | `@generated` |
| `@customers`, `@sales` | `@personal_data` |
| `@magento1` | Nothing: Magento 1 table names match no Magento 2 table |

The nature groups leave out at least what `@core` did, so moving a plan from `@core` to them never
starts copying something it used to drop. They also leave out more: customer uploads, `cache` and
`cache_tag`, `session`, queued messages, vault payment tokens, `jwt_auth_revoked`, and others that
`@core` missed.

Do not redefine a group this package ships: application config is laid over platform config with
`array_replace_recursive`, which replaces list entries by position.
