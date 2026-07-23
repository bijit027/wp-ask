<?php
/**
 * Survey Repository
 *
 * @package PollQuest
 */

namespace PollQuest\Repositories;

use PollQuest\Models\Survey;

/**
 * Class SurveyRepository
 * 
 * Handles all database interactions for the pollquest_surveys table.
 */
class SurveyRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'pollquest_surveys';
	}

	/**
	 * Find a survey by ID.
	 *
	 * @param int $id
	 * @return Survey|null
	 */
	public function find( int $id ): ?Survey {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );

		if ( ! $row ) {
			return null;
		}

		return new Survey( $row );
	}

	/**
	 * Create a new survey.
	 *
	 * @param array $data
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		$format = $this->get_formats( $data );
		$result = $wpdb->insert( $this->table, $data, $format );

		if ( ! $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update a survey.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		$format = $this->get_formats( $data );
		$result = $wpdb->update(
			$this->table,
			$data,
			[ 'id' => $id ],
			$format,
			[ '%d' ]
		);

		return false !== $result;
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
