# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - Unreleased

### Fixed

- Updated amqp `ssl` configuration setting to always populate, including when set to false. This fixes an issue where M2
  is trying to call trim() on an empty string, which is deprecated in future PHP versions.

## [1.0.0] - 2021-01-21

### Added

- Added support for the Magento 2 platform
