<?php
/**
 * Session Service
 *
 * @package InsightPulse
 */

namespace PollQuest\Services;

use PollQuest\Models\Session;
use PollQuest\Repositories\SessionRepository;
use PollQuest\Utils\IpHelper;

/**
 * Class SessionService
 * 
 * Handles the logic for retrieving, creating, and updating visitor sessions.
 */
class SessionService {

	/**
	 * @var SessionRepository
	 */
	private $repository;

	public function __construct() {
		$this->repository = new SessionRepository();
	}

	/**
	 * Get the current session, or create one if it doesn't exist.
	 * Returns null if we shouldn't create one (e.g., just checking).
	 *
	 * @param bool  $will_create Whether to create a session if missing.
	 * @param array $user_data   Optional data to populate on creation (email, name).
	 * @return Session|null
	 */
	public function get_current_session( bool $will_create = false, array $user_data = [] ): ?Session {
		$user_id = get_current_user_id();

		// Check if there is a session cookie
		if ( isset( $_COOKIE['_ipulse_sid'] ) ) {
			$session_hash = sanitize_text_field( wp_unslash( $_COOKIE['_ipulse_sid'] ) );
			$session      = $this->repository->find_by_uid( $session_hash );

			if ( $session ) {
				// Upgrade anonymous session if user is now logged in
				if ( $user_id && ! $session->user_id ) {
					$this->repository->update( $session->id, [ 'user_id' => $user_id ] );
					$session->user_id = $user_id;
				}
				return $session;
			}
		}

		if ( ! $will_create ) {
			return null;
		}

		// Prepare new session data
		$name  = $user_data['full_name'] ?? '';
		$email = $user_data['email'] ?? '';

		if ( ! $email && $user_id ) {
			$user  = get_userdata( $user_id );
			$email = $user->user_email;
			$name  = trim( $user->first_name . ' ' . $user->last_name );
			if ( ! $name ) {
				$name = $user->display_name;
			}
		}

		$uid = md5( wp_generate_uuid4() . microtime( true ) . wp_rand( 1, 99999999 ) );

		$data = [
			'uid'       => $uid,
			'user_id'   => $user_id ?: null,
			'full_name' => $name,
			'email'     => $email,
		];

		// Remove empty string fields
		$data = array_filter( $data, function ( $value ) {
			return null !== $value && '' !== $value;
		} );

		$session_id = $this->repository->create( $data );

		if ( ! $session_id ) {
			return null;
		}

		// Set the cookie (90 days)
		setcookie(
			'_ipulse_sid',
			$uid,
			time() + ( 86400 * 90 ),
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl()
		);

		return $this->repository->find_by_uid( $uid );
	}

	/**
	 * Increment the response count for a session.
	 *
	 * @param Session $session
	 */
	public function increment_response_count( Session $session ): void {
		$new_count = $session->response_count + 1;
		$this->repository->update( $session->id, [ 'response_count' => $new_count ] );
	}
}
