Conductor: Magento 2 Platform Support
====================================

This module adds [Magento 2](https://magento.com/) platform support for the 
[Conductor](https://github.com/conductorphp/conductor-core).

Conductor/application-orchestration Integration
-----------------------------------------------

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
