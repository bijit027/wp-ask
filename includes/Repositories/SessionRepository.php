<?php
/**
 * Session Repository
 *
 * @package PollQuest
 */

namespace PollQuest\Repositories;

use PollQuest\Models\Session;

/**
 * Class SessionRepository
 */
class SessionRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'pollquest_sessions';
	}

	/**
	 * Find a session by its unique hash.
	 *
	 * @param string $uid
	 * @return Session|null
	 */
	public function find_by_uid( string $uid ): ?Session {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE uid = %s", $uid ) );

		if ( ! $row ) {
			return null;
		}

		return new Session( $row );
	}

	/**
	 * Create a new session.
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
	 * Update an existing session.
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
