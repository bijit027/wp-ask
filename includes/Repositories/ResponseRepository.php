<?php
/**
 * Response Repository
 *
 * @package InsightPulse
 */

namespace InsightPulse\Repositories;

use InsightPulse\Models\Response;

/**
 * Class ResponseRepository
 */
class ResponseRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ipulse_responses';
	}

	/**
	 * Find a response by ID.
	 *
	 * @param int $id
	 * @return Response|null
	 */
	public function find( int $id ): ?Response {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id );
		$row = $wpdb->get_row( $sql );

		if ( ! $row ) {
			return null;
		}

		return new Response( $row );
	}

	/**
	 * Create a new response.
	 *
	 * @param array $data
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		$formats = $this->get_formats( $data );
		$result  = $wpdb->insert( $this->table, $data, $formats );

		if ( ! $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get formats for $wpdb based on data types.
	 *
	 * @param array $data
	 * @return array
	 */
	private function get_formats( array $data ): array {
		$formats = [];
		foreach ( $data as $key => $value ) {
			if ( is_int( $value ) ) {
				$formats[] = '%d';
			} elseif ( is_float( $value ) ) {
				$formats[] = '%f';
			} else {
				$formats[] = '%s';
			}
		}
		return $formats;
	}
}
