<?php
/**
 * @file
 * Contains IntercomioController.
 */

namespace Drupal\intercomio;

use Intercom\IntercomBasicAuthClient;

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
