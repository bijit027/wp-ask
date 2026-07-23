<?php
/**
 * Session Model
 *
 * @package PollQuest
 */

namespace PollQuest\Models;

/**
 * Class Session
 */
class Session {
	public $id;
	public $uid;
	public $user_id;
	public $email;
	public $full_name;
	public $response_count;
	public $created_at;
	public $updated_at;

	public function __construct( $db_row = null ) {
		if ( ! $db_row ) {
			return;
		}

		foreach ( get_object_vars( $db_row ) as $key => $value ) {
			$this->$key = $value;
		}
	}
}
