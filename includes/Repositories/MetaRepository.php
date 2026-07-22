<?php
/**
 * Meta Repository
 *
 * @package InsightPulse
 */

namespace WPAsk\Repositories;

use WPAsk\Models\Meta;

/**
 * Class MetaRepository
 */
class MetaRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ipulse_meta';
	}

	/**
	 * Get meta value for an object.
	 *
	 * @param string $object_type
	 * @param int    $object_id
	 * @param string $meta_key
	 * @return mixed
	 */
	public function get_meta( string $object_type, int $object_id, string $meta_key ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare(
			"SELECT meta_value FROM {$this->table} WHERE object_type = %s AND object_id = %d AND meta_key = %s LIMIT 1",
			$object_type,
			$object_id,
			$meta_key
		);

		$value = $wpdb->get_var( $sql );

		if ( null === $value ) {
			return null;
		}

		// Try to decode JSON if possible
		$decoded = json_decode( $value, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $decoded;
		}

		return $value;
	}

	/**
	 * Update or create meta value.
	 *
	 * @param string $object_type
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 * @return bool
	 */
	public function update_meta( string $object_type, int $object_id, string $meta_key, $meta_value ): bool {
		global $wpdb;

		if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
			$meta_value = wp_json_encode( $meta_value );
		}

		// Check if exists
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare(
			"SELECT id FROM {$this->table} WHERE object_type = %s AND object_id = %d AND meta_key = %s LIMIT 1",
			$object_type,
			$object_id,
			$meta_key
		);

		$id = $wpdb->get_var( $sql );

		if ( $id ) {
			// Update
			$result = $wpdb->update(
				$this->table,
				[
					'meta_value' => $meta_value,
					'updated_at' => current_time( 'mysql' ),
				],
				[ 'id' => $id ],
				[ '%s', '%s' ],
				[ '%d' ]
			);
		} else {
			// Insert
			$result = $wpdb->insert(
				$this->table,
				[
					'object_type' => $object_type,
					'object_id'   => $object_id,
					'meta_key'    => $meta_key,
					'meta_value'  => $meta_value,
				],
				[ '%s', '%d', '%s', '%s' ]
			);
		}

		return false !== $result;
	}
}
