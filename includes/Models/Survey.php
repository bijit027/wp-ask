<?php
/**
 * Survey Model
 *
 * @package InsightPulse
 */

namespace InsightPulse\Models;

/**
 * Class Survey
 * 
 * Simple DTO representing a Survey row.
 */
class Survey {
	public $id;
	public $title;
	public $status;
	public $type;
	public $questions;
	public $settings;
	public $targeting;
	public $notifications;
	public $impressions;
	public $publish_at;
	public $created_at;

	/**
	 * Cast JSON strings back to arrays/objects when instantiating.
	 */
	public function __construct( $db_row = null ) {
		if ( ! $db_row ) {
			return;
		}

		foreach ( get_object_vars( $db_row ) as $key => $value ) {
			if ( in_array( $key, [ 'questions', 'settings', 'targeting', 'notifications' ] ) && is_string( $value ) ) {
				$this->$key = json_decode( $value, true );
			} else {
				$this->$key = $value;
			}
		}
	}
}
