<?php
/**
 * Post Rating Service
 *
 * @package PollQuest
 */

namespace PollQuest\Services;

use PollQuest\Repositories\PostRatingRepository;
use PollQuest\Utils\IpHelper;
use WP_Error;

/**
 * Class PostRatingService
 */
class PostRatingService {

	/**
	 * @var PostRatingRepository
	 */
	private $repository;

	public function __construct() {
		$this->repository = new PostRatingRepository();
	}

	/**
	 * Get rating summary for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $type    stars|thumbs.
	 * @return array<string, mixed>|WP_Error
	 */
	public function get_summary( int $post_id, string $type = 'stars' ) {
		if ( ! get_post( $post_id ) ) {
			return new WP_Error( 'invalid_post', __( 'Post not found.', 'pollquest' ), [ 'status' => 404 ] );
		}

		$stats        = $this->repository->get_stats( $post_id );
		$user_id      = get_current_user_id();
		$user_rating  = null;

		if ( $user_id ) {
			$existing = $this->repository->find_by_user( $post_id, $user_id );
			if ( $existing ) {
				$user_rating = (int) $existing->rating;
			}
		}

		$summary = [
			'post_id'     => $post_id,
			'type'        => $type,
			'count'       => $stats['count'],
			'user_rating' => $user_rating,
		];

		if ( 'thumbs' === $type ) {
			$summary['up']   = $stats['up'];
			$summary['down'] = $stats['down'];
		} else {
			$summary['average']   = $stats['average'];
			$summary['breakdown'] = $stats['breakdown'];
		}

		return $summary;
	}

	/**
	 * Submit or update a post rating.
	 *
	 * @param int    $post_id Post ID.
	 * @param int    $rating  Rating value.
	 * @param string $type    stars|thumbs.
	 * @return array<string, mixed>|WP_Error
	 */
	public function submit( int $post_id, int $rating, string $type = 'stars' ) {
		if ( ! get_post( $post_id ) ) {
			return new WP_Error( 'invalid_post', __( 'Post not found.', 'pollquest' ), [ 'status' => 404 ] );
		}

		if ( ! $this->is_valid_rating( $rating, $type ) ) {
			return new WP_Error( 'invalid_rating', __( 'Invalid rating value.', 'pollquest' ), [ 'status' => 400 ] );
		}

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			$ip            = IpHelper::get_ip();
			$transient_key = 'pollquest_post_rating_' . md5( $ip . '_' . $post_id );
			if ( get_transient( $transient_key ) ) {
				return new WP_Error(
					'already_rated',
					__( 'You have already rated this post.', 'pollquest' ),
					[ 'status' => 429 ]
				);
			}
		}

		if ( $user_id ) {
			$existing = $this->repository->find_by_user( $post_id, $user_id );

			if ( $existing ) {
				$this->repository->update(
					(int) $existing->id,
					[
						'rating'     => $rating,
						'created_at' => current_time( 'mysql', true ),
					]
				);
			} else {
				$result = $this->repository->create(
					[
						'post_id'    => $post_id,
						'rating'     => $rating,
						'user_id'    => $user_id,
						'created_at' => current_time( 'mysql', true ),
					]
				);

				if ( ! $result ) {
					return new WP_Error( 'db_error', __( 'Could not save rating.', 'pollquest' ), [ 'status' => 500 ] );
				}
			}
		} else {
			$result = $this->repository->create(
				[
					'post_id'    => $post_id,
					'rating'     => $rating,
					'user_id'    => null,
					'created_at' => current_time( 'mysql', true ),
				]
			);

			if ( ! $result ) {
				return new WP_Error( 'db_error', __( 'Could not save rating.', 'pollquest' ), [ 'status' => 500 ] );
			}

			$ip            = IpHelper::get_ip();
			$transient_key = 'pollquest_post_rating_' . md5( $ip . '_' . $post_id );
			set_transient( $transient_key, 1, YEAR_IN_SECONDS );
		}

		return $this->get_summary( $post_id, $type );
	}

	/**
	 * Validate rating for the given widget type.
	 *
	 * @param int    $rating Rating value.
	 * @param string $type   stars|thumbs.
	 * @return bool
	 */
	private function is_valid_rating( int $rating, string $type ): bool {
		if ( 'thumbs' === $type ) {
			return in_array( $rating, [ 0, 1 ], true );
		}

		return $rating >= 1 && $rating <= 5;
	}
}
