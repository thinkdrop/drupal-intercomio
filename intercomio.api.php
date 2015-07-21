<?php
/**
 * @file
 * Intercom.io module hook documentation.
 */

/**
 * Provide a unique ID for each custom data setting to track.
 *
 * Return an array listing the custom data attributes that you would like to
 * allow intercom.io to track. The items in this array need to be unique so it's
 * a good idea to prefix the keys with your module's name.
 *
 * The keys of the array are unique strings used to identify a custom data
 * attribute and will be used as the key of JSON object passed to the
 * intercom.io JavaScript tracking code. The values of the array are the human
 * readable label for the custom data attribute that will be displayed in the
 * admin UI when allowing administrators to enable/disable tracking of specific
 * attributes.
 *
 * @return array
 */
function hook_intercomio_custom_data_info() {
  return array(
    'mymodule_plan' => t('Membership plan name'),
    'mymodule_price' => t('Membership plan price'),
  );
}

/**
 * Insert values to be tracked for custom data attributes.
 *
 * Calculate and provide the values for custom data attributes that should be
 * sent to intercom.io for the current user.
 *
 * @param array $keys
 *   An array of custom attributes that are currently enabled for tracking. The
 *   IDs come from hook_intercomio_custom_data_info() and appear in this array
 *   if an admin has enabled tracking for that attribute. Use this array to
 *   determine which custom items should be tracked. If you provided a new
 *   attribute in hook_intercomio_custom_data_info() but the element doesn't
 *   appear in $keys array that means that tracking for that attribute is
 *   currently disabled.
 *
 * @return array $data
 *   An associative array of values for custom data attributes keyed by the
 *   unique attribute name as determined by hook_intercomio_custom_data_info().
 *   These values will be appended to the intercom.io user/contact profile.
 *
 */
function hook_intercomio_custom_data($keys) {
  $data = array();

  if (in_array('mymodule_price', $keys)) {
    $data['mymodule_price'] = '$33.22';
  }

  if (in_array('mymodule_plan', $keys)) {
    $data['mymodule_plan'] = 'Premium Plan';
  }

  return $data;
}
