<?php
/**
 * Heatmap Recording Model
 *
 * @package InsightPulse
 */

namespace PollQuest\Models;

/**
 * Class HeatmapRecording
 */
class HeatmapRecording {

	public $id;
	public $heatmap_id;
	public $click_data;
	public $created_at;

	/**
	 * @param object|null $db_row Database row.
	 */
	public function __construct( $db_row = null ) {
		if ( ! $db_row ) {
			return;
		}

		foreach ( get_object_vars( $db_row ) as $key => $value ) {
			if ( 'click_data' === $key && is_string( $value ) ) {
				$this->click_data = json_decode( $value, true );
			} else {
				$this->$key = $value;
			}
		}
	}
}
