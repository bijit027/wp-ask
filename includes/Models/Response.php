<?php
/**
 * Response Model
 *
 * @package InsightPulse
 */

namespace PollQuest\Models;

/**
 * Class Response
 */
class Response {
	public $id;
	public $survey_id;
	public $session_id;
	public $user_id;
	public $serial;
	public $page_id;
	public $page_url;
	public $answers;
	public $context;
	public $email;
	public $full_name;
	public $ip_address;
	public $country;
	public $browser;
	public $platform;
	public $device;
	public $status;
	public $created_at;

	public function __construct( $db_row = null ) {
		if ( ! $db_row ) {
			return;
		}

		foreach ( get_object_vars( $db_row ) as $key => $value ) {
			if ( in_array( $key, [ 'answers', 'context' ] ) && is_string( $value ) ) {
				$this->$key = json_decode( $value, true );
			} else {
				$this->$key = $value;
			}
		}
	}
}
