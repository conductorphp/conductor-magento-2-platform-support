Conductor: Magento 2 Platform Support Changelog
==============================================

# 0.9.10
- Updated opcache command in dist file to https, run php instead of php71, and include -L curl flag
- Moved cache_type configuration to yaml config
- Moved defaults from platform.php into twig template where possible
- Added consideration for \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT option for db config
- Added id_prefix for redis caches
- Removed Magento Commerce Order Management (MCOM) support
- Added AMQP ssl configuration

# 0.9.9
- Added ability to set Magento store configuration via app/etc/env.php

# 0.9.8
- Fixed default MAGE_MODE value. Set to "production"

# 0.9.7
- updated env.php.twig max_concurrency to 20

# 0.9.6
- Added ability to set x-frame-options header in app/etc/env.php

# 0.9.5
- Added consideration for Magento Commerce Order Management

# 0.9.4
- Added document_root_is_pub setting to app/etc/env.php per 
  https://github.com/magento/magento2/pull/9094 and defaulted to true

# 0.9.3
- Fixed license per https://spdx.org/licenses/

# 0.9.2
- Added ability to disable redis session locking

# 0.9.1
- Updated conductor/application-orchestration require to ^1.0.0

# 0.9.0
- Tagging for initial consistency with other modules

# 0.2.0 (Unreleased)
- Renamed to Conductor
- Updated PHP version requirement to 7.1
- Updated config to work with ConductorAppOrchestration

# 0.1.1
- Added zf config-provider Composer setting

# 0.1.0
- Initial build copied over from Conductor
