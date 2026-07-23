<?php

/**
 * Email Response Notifications main class.
 *
 * @since 1.0.0
 */
namespace PollQuest\Emails;

use PollQuest\Models\Survey;
use PollQuest\Models\Response;

class ResponseNotification {

	/**
	 * Email template to use for this class.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $email_template = 'response-notification';

	/**
	 * Test email template
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $test_email_template = 'response-notification';

	/**
	 * Email options
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $email_options = array();

	/**
	 * The survey this notification is for
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private $survey;

	/**
	 * The response this notification is for
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private $response;

	/**
	 * Notification logic config
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $survey, $response ) {

		$this->survey   = $survey;
		$this->response = $response;
		$this->config   = $survey->notifications->email;
	}

	private function set_options() {
		$options                    = array();
		$options['email_addresses'] = $this->config->addresses;
		$options['html_template']   = get_option( 'summaries_html_template' );
		$options['header_image']    = get_option( 'notifications_header_image' );

		$this->email_options = $options;
	}

	/**
	 * Send email
	 *
	 * @return void
	 */
	private function send() {
		$email            = array();
		$email['subject'] = $this->get_email_subject();
		$email['address'] = $this->get_email_addresses();
		$email['address'] = array_map( 'sanitize_email', $email['address'] );

		// Create new email.
		$emails = new WPEmails( $this->email_template );
		$emails->set_initial_args( $this->get_template_args() );

		// check if html template option is enabled
		if ( ! $this->is_enabled_html_template() ) {
			$emails->__set( 'html', false );
		}

		// Go.
		foreach ( $email['address'] as $address ) {
			if ( ! $emails->send( trim( $address ), $email['subject'] ) ) {
				wp_send_json_error();
			}
		}
	}

	/**
	 * Check if email should be sent
	 *
	 * @return void
	 */
	public function maybe_send() {
		// if ( !$this->config->active || empty( $this->config->addresses ) ) {
		// If notifications aren't active, or no emails are set, bail...
		// return;
		// }

		$logic = $this->config->logic;

		if ( $logic->enable && sizeof( $logic->conditions ) > 0 ) {
			$send = true;

			foreach ( $logic->conditions as $condition ) {
				$question_id = $condition->question_id;

				$answer = $this->get_question_answer( $question_id );

				if ( empty( $answer ) ) {
					break;
				}

				$symbol          = $condition->compare;
				$submitted_value = $answer->value;
				$compare_to      = $condition->value;

				$send = $send && pollquest_check_logic( $symbol, $submitted_value, $compare_to );
			}

			if ( ! $send ) {
				return;
			}
		}

		$this->set_options();
		$this->send();
	}

	/**
	 * Get the email header image.
	 *
	 * @since 1.0.0
	 *
	 * @return string The email from address.
	 */
	public function get_header_image() {
		// set default header image
		$img = array(
			'url' => plugins_url( 'assets/img/emails/userfeedback-logo.png', POLLQUEST_PLUGIN_FILE ),
			'2x'  => '', // plugins_url( "assets/img/emails/logo-MonsterInsights@2x.png", POLLQUEST_PLUGIN_FILE ),
		);

		if ( ! empty( $this->email_options['header_image'] ) ) {
			$img['url'] = $this->email_options['header_image'];
			$img['2x']  = '';
		}

		return apply_filters( 'pollquest_email_header_image', $img );
	}

	/**
	 * Get email subject
	 *
	 * @since 1.0.0
	 */
	public function get_email_subject() {

		$site_url        = get_site_url();
		$site_url_parsed = wp_parse_url( $site_url );

		// Translators: The domain of the site is appended to the subject.
		$subject = sprintf( __( 'New UserFeedback Response - %s', 'pollquest' ), $this->survey->title );

		return apply_filters( 'pollquest_emails_new_response_subject', $subject, $this->survey, $this->response );
	}

	/**
	 * Get email addresses to send
	 *
	 * @since 1.0.0
	 */
	public function get_email_addresses() {
		$emails          = array();
		$email_addresses = $this->config->active ? explode( ',', $this->config->addresses ) : array();

		if ( ! empty( $email_addresses ) ) {
			foreach ( $email_addresses as $email_address ) {
				if ( ! empty( $email_address ) && is_email( $email_address ) ) {
					$emails[] = $email_address;
				}
			}
		}

		return apply_filters( 'pollquest_email_notification_addresses', $emails, $this->survey, $this->response );
	}

	/**
	 * Check if html template option is turned on
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_enabled_html_template() {
		$value = true;
		if ( false === $this->email_options['html_template'] ) {
			$value = false;
		}
		return apply_filters( 'pollquest_email_html_template', $value, $this );
	}

	/**
	 * Get email summaries template arguments
	 *
	 * @since 1.0.0
	 */
	private function get_template_args() {

		$args['preview_title'] = $this->get_email_subject();
		$args['header_image']  = $this->get_header_image();
		$args['survey_id']     = $this->survey->id;
		$args['survey_title']  = $this->survey->title;
		/* translators: %s: survey title (HTML tags are allowed) */
		$args['title'] = sprintf(
			/* translators: %s: survey title */
			esc_html__( 'New Response to %s', 'pollquest' ),
			esc_html( $this->survey->title )
		);

		$survey_id               = $this->survey->id;
		$notification_config_url = admin_url( 'admin.php?page=pollquest#surveys/edit/' . $survey_id );

		/* translators: %1$s: blog name, %2$s: settings URL (HTML tags are allowed) */
		$args['description'] =
		sprintf(
			/* translators: 1: site name, 2: settings URL */
			esc_html__( 'You are receiving this PollQuest survey notification from %1$s. Adjust your settings here: %2$s', 'pollquest' ),
			'<strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>',
			'<a href="' . esc_url( $notification_config_url ) . '">' . esc_html__( 'Adjust your settings here', 'pollquest' ) . '</a>'
		);

		$args['answers']          = $this->get_answers();
		$args['settings_tab_url'] = $notification_config_url;

		return apply_filters( 'pollquest_email_notification_template_args', $args );
	}

	/**
	 * Get response answers
	 *
	 * @return array|array[]
	 */
	private function get_answers() {
		$questions = $this->survey->questions;

		$answers = array_map(
			function( $question ) {
				return array(
					'question_id'    => $question->id,
					'question_title' => $question->title,
					'type'           => $question->type,
					'value'          => $this->get_question_answer_html( $question ),
				);
			},
			$questions
		);

		return $answers;
	}

	private function get_question_answer( $question_id ) {
		$answers = $this->response->answers;

		if ( ! is_array( $answers ) ) {
			return null;
		}

		// Find answer...
		foreach ( $answers as $answer ) {
			if ( isset($answer['question_id']) && $answer['question_id'] === $question_id ) {
				return $answer;
			}
		}

		return null;
	}

	/**
	 * Get processed question answer
	 *
	 * @param $question
	 * @return string
	 */
	private function get_question_answer_html( $question ) {
		$answers = $this->response->answers;

		// Convert question to object if it's an array
		$q_id = is_array($question) ? $question['id'] : $question->id;
		$q_type = is_array($question) ? $question['type'] : $question->type;

		$found_answer = $this->get_question_answer( $q_id );

		/* translators: %1$s: opening HTML tag, %2$s: closing HTML tag (HTML tags are allowed) */
		$skipped_content = sprintf(
			/* translators: 1: opening HTML tags, 2: closing HTML tags */
			__( '%1$sSkipped%2$s', 'pollquest' ),
			'<small><i>',
			'</i></small>'
		);

		if ( empty( $found_answer ) ) {
			return $skipped_content;
		}

		$raw_value = $found_answer['value'] ?? null;

		// If answer is null, the question was skipped...
		if ( empty( $raw_value ) ) {
			return $skipped_content;
		};
		$value = $raw_value;

		if ( $q_type === 'email' ) {
			$value = sprintf(
				'<a href="mailto:%1$s">%1$s</a>',
				$raw_value
			);
		} elseif ( $q_type === 'nps' ) {
			$value = $value . '<small>/10</small>';
		} elseif ( $q_type === 'checkbox' ) {
			$value = is_array($value) ? implode( ', ', $value ) : $value;
		} elseif ( $q_type === 'star-rating' || $q_type === 'rating' ) {
			/* translators: %s: rating value */
			$value = sprintf( __( '%s stars', 'pollquest' ), $value );
		}

		if ( ! empty( $found_answer['extra'] ) ) {

			foreach ( $found_answer['extra'] as $attr => $extra_value ) {
				$value .= sprintf(
					'<br/><p>- %s: <i>%s</i></p>',
					$attr,
					$extra_value
				);
			}
		}

		return $value;
	}
}
