Conductor: Magento 2 Platform Support Changelog
==============================================

# 0.9.10 (unreleased)
- Updated opcache command in dist file to https, run php instead of php71, and include -L curl flag

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
- Updated conductor/application-orchestration require to ~0.9.3

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
