<?php
/**
 * Heatmap Model
 *
 * @package InsightPulse
 */

namespace InsightPulse\Models;

/**
 * Class Heatmap
 */
class Heatmap {

	public $id;
	public $page_id;
	public $status;
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

	/**
	 * Convert to array for REST responses.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id'         => (int) $this->id,
			'page_id'    => (int) $this->page_id,
			'status'     => $this->status,
			'created_at' => $this->created_at,
		];
	}
}
