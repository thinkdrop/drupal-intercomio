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
and looking under the "Integrations" settings for your application.

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

## Logging Events

One of the most common things you'll want to do with the Intercom.io API is log
events for users of your site. For example, when someone logs in, or when they
perform any specific action on the site.

### Logging Events with Triggers

This module provides an action for logging events. This action can be configured
at `admin/config/system/actions`, and triggered using the Trigger module at
`admin/structure/trigger/node`, via Views Bulk Operations, or anything else that
can trigger events.

Example: Log an event named, "Left a comment" in Intercom.io every time someone
leaves a comment on your blog.

### Logging Events with Client Side JavaScript

This module adds the Intercom.io tracking JavaScript to every page requests As
such, you can write custom event tracking JavaScript in either a module or a
theme to do things like for example: Log an event on Intercom.io when a user
clicks the play button for a podcast.

More about the Intercom.io JavaScript API is available here:
http://docs.intercom.io/install-on-your-web-product/intercom-javascript-api

A simple Drupal example:

```js
(function ($, Intercom) {
  Drupal.behaviors.trackEvent = {
    attach: function() {
      $('a.podcast-play').click(function() {
        var metadata = {
          podcast_title: 'Example Podcast',
        };
        Intercom('trackEvent', 'played-podcast', metadata);
      });
    }
  }
})(jQuery, Intercom);
```

### Logging Events with Custom Code

This module provides a suite of tools to aid in logging events directly via PHP.

Here's an example of logging a "Logged in" event for a user.

```php
/**
 * Implements hook_user_login().
 */
function intercomio_user_login(&$edit, $account) {
  $controller = intercomio_get_controller();
  $controller->createEvent($account, ['event_name' => 'Logged in']);
}
```

For more about the information that can be included with an event see the
documentation for the Drupal\intercomio\IntercomioController::createEvent()
method, and the official API documentation https://doc.intercom.io/api/#event-model

## Tagging Users

Intercom.io allows you to apply one or more tags to a user in order to segment
your list. https://doc.intercom.io/api/#tags

### Tagging Users with Triggers

This module provides actions for adding and removing tags. These actions can be
configured at `admin/config/system/actions` and then triggered using the
Trigger module at `admin/structure/trigger/node` or with Views Bulk Operations,
or any other module that can trigger actions.

Example: Tag all users in the administrator role.

### Tagging Users with Custom Code

If you would like to tag users based on actions in your own code follow the
example below and read the documentation for the Drupal\intercomio\IntercomioController::tagUsers()
method.

```php
function mymodule_awesome_function($account) {
  $controller = intercomio_get_controller();
  $users = array(
    array('email' => $account->mail),
  );
  $controller->tagUsers('Awesome', $users);
}
```
