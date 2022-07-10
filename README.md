#Grin Module

[![M2 Coding Standard](https://github.com/grininc/grin-magento-module/actions/workflows/coding-standard.yml/badge.svg?branch=main)](https://github.com/grininc/grin-magento-module/actions/workflows/coding-standard.yml)
[![M2 Mess Detector](https://github.com/grininc/grin-magento-module/actions/workflows/mess-detector.yml/badge.svg?branch=main)](https://github.com/grininc/grin-magento-module/actions/workflows/mess-detector.yml)

![PHP Version](https://img.shields.io/badge/php-7.2%20|%207.3%20|%207.4%20|%208.1-blue)
<img src="https://img.shields.io/badge/magento-2.3.X-brightgreen.svg?logo=magento&longCache=true" alt="Supported Magento Versions" />
<img src="https://img.shields.io/badge/magento-2.4.X-brightgreen.svg?logo=magento&longCache=true" alt="Supported Magento Versions" />
![License](https://img.shields.io/github/license/drpayyne/docker-php)

Influencer marketing for ecommerce. For more information go to https://grin.co/


Install
-

1. composer config repositories.grin vcs https://github.com/grininc/grin-magento-module
2. composer require grin/module
3. bin/magento setup:upgrade
4. bin/magento setup:di:compile
5. bin/magento setup:static-content:deploy
6. bin/magento cache:flush

Update
-

1. composer update grin/module
2. bin/magento setup:upgrade
3. bin/magento setup:di:compile
4. bin/magento setup:static-content:deploy
5. bin/magento cache:flush

Some notes
-

- Grin uses composer to provide their extension to the end customer.
- The extension follows semver principles.
- Once the new implementation of Grin_Affiliate is ready to be installed and tested it will get version 2.0.x.
- If an end-user has the previous version of the extension installed via https://docs.magento.com/user-guide/v2.3/system/web-setup-wizard.html or manually in app/code, the extension must be removed before new installation.

Q&A
-

1. Issue: I updated the extension, but it seems like the old version is installed despite the fact that in the compose.lock file I see the updated version of the extension.

Possible solution: the extension uses a DB queue, which is run in shadow mode. So it might be that you did not kill the old process after the installation.
Try to execute `ps -aux | grep grin_module_webhook` and kill the process if any.

2. Issue: I am using Magento cloud instance and it seems like the 'webhook_grin_module' is not working.

Possible solution: in this case, according to Magento documentation, you need to make sure that the queue job is added into the /app/etc/env.php file like so:
```
...
    'cron_consumers_runner' => [
        'cron_run' => false,
        'max_messages' => 20000,
        'consumers' => [
            'consumer1',
            'consumer2',
            'grin_module_webhook',
            ...
        ]
    ],
...
```
