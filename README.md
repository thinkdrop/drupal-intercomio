## Intercom.io Integration

This module provides integration with the Intercom.io customer engagement
platform.

## Requirements
- [Composer Manager](https://www.drupal.org/project/composer_manager)
- [intercom-php](https://github.com/intercom/intercom-php)

## Installation
- Download and install Composer Manager and Intercom.io Drupal modules.
- Enable the Intercom.io module. It's easiest to use drush so it will
  automatically download the Intercom.io PHP SDK.
- Configure the Intercom.io credentials and other settings at
  `admin/config/services/intercomio`

For more information about using Composer Manager to install dependencies see
https://www.drupal.org/node/2405805.

## Configuration

Most of the module's configuration can be handled at `admin/config/services/intercomio`.
At a minimum you'll need an http://intercom.io App ID and API Key in order to
start tracking customer data. Both can be obtained by signing in to Intercom.io
and looking under the "Intergrations" settings for your application.

## Updating

If you're updating from an older -dev version of the module you'll need to take
a couple of extra steps to ensure that all the correct Composer libraries are
present. This module uses Composer Manager to manage it's composer.json file.
You can read more about using the Composer Manager here: https://www.drupal.org/node/2405805

For this update you'll need to run the following drush commands after updating
the module:

- drush dl composer_manager
- drush en --yes composer_manager
- drush composer-json-rebuild
- drush composer-manager update

This will download the required Composer libraries to your sites/all/libraries
folder, and you can then include them in your project. Alternately you can view
the status of managed composer libraries, and re-build your global composer.json
file at `admin/config/system/composer-manager`.
