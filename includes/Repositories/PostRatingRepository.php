<?php
/**
 * Post Rating Repository
 *
 * @package InsightPulse
 */

namespace InsightPulse\Repositories;

use InsightPulse\Models\PostRating;

/**
 * Class PostRatingRepository
 */
class PostRatingRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ipulse_post_ratings';
	}

	/**
	 * Find a rating by ID.
	 *
	 * @param int $id Rating ID.
	 * @return PostRating|null
	 */
	public function find( int $id ): ?PostRating {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );

		return $row ? new PostRating( $row ) : null;
	}

	/**
	 * Find a user's rating for a post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id User ID.
	 * @return PostRating|null
	 */
	public function find_by_user( int $post_id, int $user_id ): ?PostRating {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE post_id = %d AND user_id = %d LIMIT 1",
				$post_id,
				$user_id
			)
		);

		return $row ? new PostRating( $row ) : null;
	}

	/**
	 * Create a rating row.
	 *
	 * @param array<string, mixed> $data Rating data.
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		$result = $wpdb->insert(
			$this->table,
			$data,
			[ '%d', '%d', '%d', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Update a rating row.
	 *
	 * @param int                  $id   Rating ID.
	 * @param array<string, mixed> $data Rating data.
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		return (bool) $wpdb->update(
			$this->table,
			$data,
			[ 'id' => $id ],
			[ '%d', '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Get aggregate stats for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	public function get_stats( int $post_id ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT rating, COUNT(*) AS total FROM {$this->table} WHERE post_id = %d GROUP BY rating",
				$post_id
			),
			ARRAY_A
		);

		$breakdown = [
			'0' => 0,
			'1' => 0,
			'2' => 0,
			'3' => 0,
			'4' => 0,
			'5' => 0,
		];
		$count   = 0;
		$sum     = 0;

		foreach ( (array) $rows as $row ) {
			$rating = (string) (int) $row['rating'];
			$total  = (int) $row['total'];

			if ( isset( $breakdown[ $rating ] ) ) {
				$breakdown[ $rating ] = $total;
			}

			$count += $total;
			$sum   += (int) $row['rating'] * $total;
		}

		return [
			'count'     => $count,
			'average'   => $count > 0 ? round( $sum / $count, 1 ) : 0,
			'breakdown' => $breakdown,
			'up'        => (int) ( $breakdown['1'] ?? 0 ),
			'down'      => (int) ( $breakdown['0'] ?? 0 ),
		];
	}
}
