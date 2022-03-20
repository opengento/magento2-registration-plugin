# Module for Magento 2

[![Latest Stable Version](https://img.shields.io/packagist/v/opengento/composer-registration-plugin.svg?style=flat-square)](https://packagist.org/packages/opengento/composer-registration-plugin)
[![License: MIT](https://img.shields.io/github/license/opengento/magento2-registration-plugin.svg?style=flat-square)](./LICENSE) 
[![Packagist](https://img.shields.io/packagist/dt/opengento/composer-registration-plugin.svg?style=flat-square)](https://packagist.org/packages/opengento/composer-registration-plugin/stats)
[![Packagist](https://img.shields.io/packagist/dm/opengento/composer-registration-plugin.svg?style=flat-square)](https://packagist.org/packages/opengento/composer-registration-plugin/stats)

This module add a global registration.php that replace the default glob search performed for each request to discover the components not installed from composer.

 - [Setup](#setup)
 - [Features](#features)
 - [Documentation](#documentation)
 - [Support](#support)
 - [Authors](#authors)
 - [License](#license)

## Setup

Magento 2 Open Source or Commerce edition is required.

###  Composer installation

Run the following composer command:

```
composer require opengento/composer-registration-plugin
```

## Features

This composer plugin will generate a global `registration.php` file for components in app & setup directories.

## Documentation

In order to use this plugin, edit your project `composer.json` file, replace the files of autoload with:

```json
{
  "autoload": {
    "files": [
      "app/etc/registration.php"
    ]
  }
}
```

In order to optimize you project, your autoload section should be the same:

```json
{
  "autoload": {
    "psr-4": {
      "Magento\\Setup\\": "setup/src/Magento/Setup/",
      "Zend\\Mvc\\Controller\\": "setup/src/Zend/Mvc/Controller/"
    }
  }
}
```

## Support

Raise a new [request](https://github.com/opengento/magento2-registration-plugin/issues) to the issue tracker.

## Authors

- **Opengento Community** - *Lead* - [![Twitter Follow](https://img.shields.io/twitter/follow/opengento.svg?style=social)](https://twitter.com/opengento)
- **Contributors** - *Contributor* - [![GitHub contributors](https://img.shields.io/github/contributors/opengento/magento2-registration-plugin.svg?style=flat-square)](https://github.com/opengento/magento2-registration-plugin/graphs/contributors)

## License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) details.

***That's all folks!***
