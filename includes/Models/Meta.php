<?php
/**
 * Meta Model
 *
 * @package PollQuest
 */

namespace PollQuest\Models;

/**
 * Class Meta
 */
class Meta {
	public $id;
	public $object_type;
	public $object_id;
	public $meta_key;
	public $meta_value;
	public $updated_at;

	public function __construct( $db_row = null ) {
		if ( ! $db_row ) {
			return;
		}

		foreach ( get_object_vars( $db_row ) as $key => $value ) {
			if ( 'meta_value' === $key && is_string( $value ) ) {
				$this->$key = json_decode( $value, true );
			} else {
				$this->$key = $value;
			}
		}
	}
}
