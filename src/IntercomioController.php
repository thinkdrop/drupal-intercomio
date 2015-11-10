<?php
/**
 * @file
 * Contains IntercomioController.
 */

namespace Drupal\intercomio;

use Intercom\IntercomBasicAuthClient;
use Intercom\Exception\ServerErrorResponseException;
use Intercom\Exception\ClientErrorResponseException;

/**
 * Class IntercomioController
 *
 * Wrapper for the Intercom\IntercomBasicAuthClient API client that can do
 * things like static caching for API responses and provide Drupal aware error
 * handling.
 *
 * @todo: This should really be implementing an interface.
 */
class IntercomioController {

  // Statically cached API responses.
  private $admins;

  /**
   * Constructor.
   *
   * @param IntercomBasicAuthClient $api_client
   *   Client object for making requests to the intercom.io API.
   */
  public function __construct(IntercomBasicAuthClient $api_client) {
    $this->api_client = $api_client;
  }

  /**
   * Instantiate a new copy of this controller.
   *
   * Anytime you want to use this controller you should create a new object
   * with $controller = IntercomioController::create().
   *
   * Done this way to work better with Drupal 8's service container in the
   * future.
   *
   * @param IntercomBasicAuthClient $api_client
   *   Client object for making requests to the intercom.io API.
   *
   * @return static
   */
  public static function create(IntercomBasicAuthClient $api_client) {
    return new static($api_client);
  }

  /**
   * Create a new user in Intercom.io.
   *
   * The intercom.io does an upsert for this API so this command will either
   * create a new user object if it doesn't exist, or update an existing one
   * if the email address or id matches.
   *
   * @param array $data
   *   An array of properties used to create the new user object. Requires
   *   either an 'email' or 'id', or 'user_id' property. See the API docs for
   *   more information. https://doc.intercom.io/api/#users
   *
   * @return mixed
   *   Either the response from the API client, or FALSE on failure to get a
   *   response.
   */
  public function createUser(array $data) {
    try {
      $response = $this->api_client->createUser($data);
      return $response;
    }
    catch (ClientErrorResponseException $e) {
      // @todo: Add better error handling.
      return FALSE;
    }
    catch (ServerErrorResponseException $e) {
      watchdog('intercomio', 'Unable to create Intercom.io user. Server responded with @error', array('@error' => $e->getMessage()), WATCHDOG_WARNING);
      return FALSE;
    }
  }

  /**
   * Log an event in the Intercom.io API for the provided user.
   *
   * @param object $account
   *   The Drupal user account object that this event is being logged for.
   * @param array $event
   *   An associative array that describes the event to log.
   *   More info about possible keys: https://doc.intercom.io/api/#event-model
   *   Example:
   *   - event_name: (required) Name of the event
   *   - created_at: Unix timestamp to use for the event, will be set to the
   *     current time if omitted.
   *   - metadata: And array of additional metadata items to include with the
   *     event. Up-to five items allowed. See the link above for additional
   *     information about custom formatting of metadata items.
   *
   * @return mixed
   *   The API response, or FALSE if the request failed.
   */
  public function createEvent($account, array $event) {
    // Ensure we have an email address and user_id for this event, but let the
    // caller specify a custom value if they want to.
    $event['email'] = !isset($event['email']) ? $account->mail : $event['email'];
    $event['user_id'] = !isset($event['user_id']) ? $account->uid : $event['user_id'];

    $event['created_at'] = !isset($event['created_at']) ? REQUEST_TIME : $event['created_at'];

    try {
      // You can't log an event to a user that doesn't exist. So lets confirm
      // that the user is valid first.
      $this->createUser(['email' => $event['email'], 'user_id' => $event['user_id']]);
      $response = $this->api_client->createEvent($event);
      return $response;
    }
    catch (ClientErrorResponseException $e) {
      watchdog('intercomio', 'Unable to log Intercom.io event @event for user @user', array('@event' => $event['name'], '@user' => $event['email']), WATCHDOG_WARNING);
      return FALSE;
    }
    catch (ServerErrorResponseException $e) {
      watchdog('intercomio', 'Unable to create Intercom.io event. Server responded with @error', array('@error' => $e->getMessage()), WATCHDOG_WARNING);
      return FALSE;
    }
  }

  /**
   * Tag a list of users in Intercom.io.
   *
   * @param string $tag
   *   The tag to be added to the list of users provided.
   * @param array $users
   *   An array of arrays that can be used to identify a user in Intercom.io.
   *   The identifier can be one of:
   *   - id: The Intercom.io ID for the user
   *   - user_id: The Drupal user ID
   *   - email: The user's email address
   *
   *   Generally just using their email address should be adequate. If the user
   *   doesn't exist in Intercom.io they will be added.
   *
   * Example user list:
   * @code
   * $users = array(array('email' => 'test@example.com'), array('user_id' => $account->uid));
   * @endcode
   *
   * @return mixed
   *   The API response, or FALSE if the request failed.
   */
  public function tagUsers($tag, array $users) {
    try {
      // You can't tag a user that doesn't exist.
      foreach ($users as $user) {
        unset($user['untag']);
        $this->createUser($user);
      }

      $response = $this->api_client->tagUsers(array(
        'name' => $tag,
        'users' => $users,
      ));

      return $response;
    }
    catch (ClientErrorResponseException $e) {
      watchdog('intercomio', 'Unable to tag/un-tag users in Intercom.io with tag @tag', array('@event' => $tag), WATCHDOG_WARNING);
      return FALSE;
    }
    catch (ServerErrorResponseException $e) {
      watchdog('intercomio', 'Unable to Intercom.io tag/un-tag user. Server responded with @error', array('@error' => $e->getMessage()), WATCHDOG_WARNING);
      return FALSE;
    }
    return FALSE;
  }

  /**
   * Remove the tag from a list of users.
   *
   * @see IntercomioController::tagUsers();
   */
  public function untagUsers($tag, array $users) {
    // Loop through the list of users and flag them for tag removal.
    foreach ($users as $key => $value) {
      $users[$key] += array('untag' => TRUE);
    }

    // Then just use the tagUsers method to do the rest of the work.
    return $this->tagUsers($tag, $users);
  }

  /**
   * Get a list of admin accounts from Intercom.io.
   *
   * https://doc.intercom.io/api/#admins
   *
   * @return array|bool
   *   Either an array of data about Intercom.io admin accounts or FALSE on
   *   failure.
   */
  public function getAdmins() {
    // If we already looked up the info once, lets just use that rather than
    // ping the API again.
    if (is_array($this->admins)) {
      return $this->admins;
    }

    $this->admins = array();
    try {
      $response = $this->api_client->getAdmins();
      $this->admins = $response['admins'];
    }
    catch (ClientErrorResponseException $e) {
      // @todo: Add better error handling.
      return FALSE;
    }

    return $this->admins;
  }
}
