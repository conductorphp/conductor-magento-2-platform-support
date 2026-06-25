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