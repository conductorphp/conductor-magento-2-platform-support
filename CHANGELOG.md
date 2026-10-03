[4.4.0](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/4.3.0...4.4.0) (2026-10-03)

### Features
* groups by nature; deprecate @core and the module groups (CTAP-2161) ([98191dc](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/98191dce959d0b1c85dc746a58508bed96809b3b))

<!--- CHANGELOG SPLIT MARKER -->

[4.3.0](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/4.2.0...4.3.0) (2026-10-02)

### Features
* the database @core table group into one group per reason (CTAP-2147) ([e12a3b5](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/e12a3b51d325ae46138049a85341e90b95505626))

<!--- CHANGELOG SPLIT MARKER -->

[4.2.0](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/4.1.1...4.2.0) (2026-10-02)

### Features
* media @core into cache, compiled, scratch and import groups (CTAP-2146) ([c16c769](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/c16c769166016b3e59aef244792f86fd39ec22e3))

<!--- CHANGELOG SPLIT MARKER -->

[4.1.1](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/4.1.0...4.1.1) (2026-10-01)

### Bug Fixes
* off ignores certificate paths (CTAP-2124) ([9801f04](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/9801f04f1b724c7f81d80c4b5026684a28ae62c2))

<!--- CHANGELOG SPLIT MARKER -->

[4.1.0](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/4.0.0...4.1.0) (2026-10-01)

### Features
* for Redis, and database/AMQP TLS from the environment (CTAP-2123) ([00b6129](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/00b612965b9ea39207917d408f7ea9959f5b8d28))

<!--- CHANGELOG SPLIT MARKER -->

[4.0.0](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/3.1.2...4.0.0) (2026-09-08)


<!--- CHANGELOG SPLIT MARKER -->

[3.1.2](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/3.1.1...3.1.2) (2026-08-28)

### Bug Fixes
* amqp_max_messages in env.php (CTAP-1544) ([0dea4e2](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/0dea4e23618f04ab70bee92250d74efe3ed4fe5c))

<!--- CHANGELOG SPLIT MARKER -->

[3.1.1](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/3.1.0...3.1.1) (2026-08-11)

### Bug Fixes
* to phpunit 13 (CTAP-1226) ([e698600](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/e698600e2bb3d5b2aa22e858ac8d3b8282d1189a))

<!--- CHANGELOG SPLIT MARKER -->

[3.1.0](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/3.0.0...3.1.0) (2026-08-10)

### Features
* PHP 8.4.1+ (CTAP-1224) ([bef747b](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/bef747b3e70885536b81732ee09dba7719c9c8d2))

<!--- CHANGELOG SPLIT MARKER -->

[2.0.1](https://github.com/conductorphp/conductor-magento-2-platform-support/compare/2.0.0...2.0.1) (2026-06-25)

### Bug Fixes
* 8.2-8.5 support ([068a88a](https://github.com/conductorphp/conductor-magento-2-platform-support/commit/068a88a6727d320c421d7806a574739997fa1877))

<!--- CHANGELOG SPLIT MARKER -->

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - Unreleased

### Added

- Added support for PHP 8.2

### Removed

- Removed support for PHP 8.0 and below

## [1.1.0] - Unreleased

### Added

- Added support for PHP 8.0 and 8.1

## [1.0.1] - Unreleased

### Fixed

- Updated amqp `ssl` configuration setting to always populate, including when set to false. This fixes an issue where M2
  is trying to call trim() on an empty string, which is deprecated in future PHP versions.

## [1.0.0] - 2021-01-21

### Added

- Added support for the Magento 2 platform