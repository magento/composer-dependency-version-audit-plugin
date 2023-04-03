# Composer dependency version audit plugin

This composer plugin is used to protect Adobe Commerce merchants from Dependency confusion attacks. It will detect when a public version of a package 
at packagist.org has a higher version than the one available from a private like repo.magento.com. When you try to install/update packages with composer,
if it detects a potential issue, the plugin will give you a recommendation message and stop the process.

## Installation

```shell
composer require magento/composer-dependency-version-audit-plugin
```

## Usage

When you install/update composer, the composer plugin will stop the process if it detects a potential Dependency Confusion attack. 
In that case, composer install/update will fail with an error message similar to:

```composer log
Higher matching version x.x.x of package/name was found in public repository packagist.org than x.x.x in private.repo. 
Public package might've been taken over by a malicious entity; 
please investigate and update package requirement to match the version from the private repository.
```