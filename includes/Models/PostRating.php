<?php
/**
 * Post Rating Model
 *
 * @package PollQuest
 */

namespace PollQuest\Models;

/**
 * Class PostRating
 */
class PostRating {

	public $id;
	public $post_id;
	public $rating;
	public $user_id;
	public $created_at;

	/**
	 * @param object|null $db_row Database row.
	 */
	public function __construct( $db_row = null ) {
		if ( ! $db_row ) {
			return;
		}

		foreach ( get_object_vars( $db_row ) as $key => $value ) {
			$this->$key = $value;
		}
	}
}
