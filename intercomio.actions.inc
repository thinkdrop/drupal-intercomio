<?php
/**
 * @file
 * Actions configuration and callbacks for intercomio.module.
 */

/**
 * Implements hook_action_info().
 */
function intercomio_action_info() {
  return array(
    'intercomio_action_log_event' => array(
      'type' => 'user',
      'label' => t('Intercom.io : Log an event'),
      'configurable' => TRUE,
      'vbo_configurable' => TRUE,
      'triggers' => array('any'),
      'pass rows' => TRUE,
    ),
    'intercomio_action_tag_users' => array(
      'type' => 'user',
      'label' => t('Intercom.io : Add a tag'),
      'configurable' => TRUE,
      'vbo_configurable' => TRUE,
      'triggers' => array('any'),
      'pass rows' => TRUE,
    ),
    'intercomio_action_untag_users' => array(
      'type' => 'user',
      'label' => t('Intercom.io : Remove a tag'),
      'configurable' => TRUE,
      'vbo_configurable' => TRUE,
      'triggers' => array('any'),
      'pass rows' => TRUE,
    ),
  );
}

/**
 * Configuration form for intercomio_action_log_event actions.
 *
 * @see hook_action_info().
 */
function intercomio_action_log_event_form($context) {
  $form = array();
  $form['intercomio_event_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Event name'),
    '#description' => t('Name of the event you would like to log in Intercom.io.'),
    '#default_value' => isset($context['intercomio_event_name']) ? $context['intercomio_event_name'] : '',
    '#required' => TRUE,
  );
  return $form;
}

/**
 * Submit handler for intercomio_action_log_event action configuration form.
 *
 * @see hook_action_info().
 */
function intercomio_action_log_event_submit($form, &$form_state) {
  return array('intercomio_event_name' => $form_state['values']['intercomio_event_name']);
}

/**
 * Callback for handling intercomio_action_log_event events.
 *
 * @see hook_action_info().
 */
function intercomio_action_log_event($user, $context = array()) {
  $event_name = check_plain($context['intercomio_event_name']);
  $controller = intercomio_get_controller();
  if ($controller) {
    $controller->createEvent($user, ['event_name' => $event_name]);
  }
}

/**
 * Configuration form for intercomio_action_tag_users actions.
 *
 * @see hook_action_info().
 */
function intercomio_action_tag_users_form($context) {
  $form = array();
  $form['intercomio_tag'] = array(
    '#type' => 'textfield',
    '#title' => t('Tag name'),
    '#description' => t('Name of the tag you would like to add to useres in Intercom.io.'),
    '#default_value' => isset($context['intercomio_tag']) ? $context['intercomio_tag'] : '',
    '#required' => TRUE,
  );
  return $form;
}

/**
 * Submit handler for intercomio_action_tag_users action configuration form.
 *
 * @see hook_action_info().
 */
function intercomio_action_tag_users_submit($form, &$form_state) {
  return array('intercomio_tag' => $form_state['values']['intercomio_tag']);
}

/**
 * Callback for handling intercomio_action_tag_users events.
 *
 * @see hook_action_info().
 */
function intercomio_action_tag_users($user, $context = array()) {
  $tag_name = check_plain($context['intercomio_tag']);
  $controller = intercomio_get_controller();
  if ($controller) {
    $users = array(
      array('email' => $user->mail),
    );
    $controller->tagUsers($tag_name, $users);
  }
}

/**
 * Configuration form for intercomio_action_untag_users actions.
 *
 * @see hook_action_info().
 */
function intercomio_action_untag_users_form($context) {
  return intercomio_action_tag_users_form($context);
}

/**
 * Submit handler for intercomio_action_untag_users action configuration form.
 *
 * @see hook_action_info().
 */
function intercomio_action_untag_users_submit($form, &$form_state) {
  return intercomio_action_tag_users_submit($form, $form_state);
}

/**
 * Callback for handling intercomio_action_untag_users events.
 *
 * @see hook_action_info().
 */
function intercomio_action_untag_users($user, $context = array()) {
  $tag_name = check_plain($context['intercomio_tag']);
  $controller = intercomio_get_controller();
  if ($controller) {
    $users = array(
      array('email' => $user->mail),
    );
    $controller->untagUsers($tag_name, $users);
  }
}
