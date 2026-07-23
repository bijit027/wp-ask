<?php
/**
 * Default Templates
 *
 * @package PollQuest
 */

namespace PollQuest\Templates;

/**
 * Class DefaultTemplates
 */
class DefaultTemplates {

	/**
	 * Register the default v1.0 templates.
	 */
	public static function register(): void {
		Registry::register(
			'blank',
			[
				'id'          => 'blank',
				'title'       => __( 'Start from scratch', 'pollquest' ),
				'description' => __( 'Create a completely custom survey with a blank canvas.', 'pollquest' ),
				'icon'        => 'file-plus',
				'category'    => 'general',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [],
				'settings'    => self::default_settings(),
			]
		);

		Registry::register(
			'website-feedback',
			[
				'id'          => 'website-feedback',
				'title'       => __( 'Website Feedback', 'pollquest' ),
				'description' => __( 'A general template to capture feedback about your website.', 'pollquest' ),
				'icon'        => 'globe',
				'category'    => 'general',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'rating',
						'label'    => __( 'How would you rate your experience on our website today?', 'pollquest' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'Please tell us what we could do to improve your experience.', 'pollquest' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thank you for your feedback!', 'pollquest' )
				),
			]
		);

		Registry::register(
			'nps-survey',
			[
				'id'          => 'nps-survey',
				'title'       => __( 'NPS Survey', 'pollquest' ),
				'description' => __( 'Measure Net Promoter Score to gauge customer loyalty.', 'pollquest' ),
				'icon'        => 'trending-up',
				'category'    => 'nps',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'nps',
						'label'    => __( 'How likely are you to recommend us to a friend or colleague?', 'pollquest' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'What is the primary reason for your score?', 'pollquest' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thanks! Your feedback helps us improve.', 'pollquest' )
				),
			]
		);

		Registry::register(
			'post-purchase',
			[
				'id'          => 'post-purchase',
				'title'       => __( 'Post-Purchase Review', 'pollquest' ),
				'description' => __( 'Gather feedback immediately after a customer buys from you.', 'pollquest' ),
				'icon'        => 'shopping-cart',
				'category'    => 'ecommerce',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'rating',
						'label'    => __( 'How satisfied are you with your shopping experience?', 'pollquest' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'radio',
						'label'    => __( 'What was the main reason you purchased from us today?', 'pollquest' ),
						'options'  => [
							__( 'Price', 'pollquest' ),
							__( 'Product Quality', 'pollquest' ),
							__( 'Brand Reputation', 'pollquest' ),
							__( 'Customer Service', 'pollquest' ),
						],
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thank you for your business and your feedback!', 'pollquest' )
				),
			]
		);

		Registry::register(
			'lead-capture',
			[
				'id'          => 'lead-capture',
				'title'       => __( 'Lead Capture', 'pollquest' ),
				'description' => __( 'Collect email addresses and interests from interested visitors.', 'pollquest' ),
				'icon'        => 'mail',
				'category'    => 'leads',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'email',
						'label'    => __( 'Enter your email to stay updated.', 'pollquest' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'What topics interest you most?', 'pollquest' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thanks for subscribing!', 'pollquest' )
				),
			]
		);

		// UserFeedback Lite inspired templates
		Registry::register(
			'website-experience',
			[
				'id'          => 'website-experience',
				'title'       => __( 'Website Experience', 'pollquest' ),
				'description' => __( 'Learn how customers currently rate your website experience.', 'pollquest' ),
				'icon'        => 'star',
				'category'    => 'general',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'rating',
						'label'    => __( 'On a scale of 1-5, how would you rate your experience?', 'pollquest' ),
						'required' => true,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thank you for rating your experience!', 'pollquest' )
				),
			]
		);

		Registry::register(
			'content-engagement',
			[
				'id'          => 'content-engagement',
				'title'       => __( 'Content Engagement', 'pollquest' ),
				'description' => __( 'Measure what content is engaging, and what content to create.', 'pollquest' ),
				'icon'        => 'file-text',
				'category'    => 'content',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'yesno',
						'label'    => __( 'Did you find this content engaging?', 'pollquest' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'What content would you like us to create?', 'pollquest' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thanks for your feedback on our content!', 'pollquest' )
				),
			]
		);

		Registry::register(
			'website-improvement',
			[
				'id'          => 'website-improvement',
				'title'       => __( 'Website Improvement', 'pollquest' ),
				'description' => __( 'See what users think about your website and gather improvement suggestions.', 'pollquest' ),
				'icon'        => 'message-square',
				'category'    => 'general',
				'is_pro'      => false,
				'is_available' => true,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'text',
						'label'    => __( 'What can we do to improve this website?', 'pollquest' ),
						'required' => true,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thank you for your improvement suggestions!', 'pollquest' )
				),
			]
		);
	}

	/**
	 * Default widget settings for templates.
	 *
	 * @param string $message Confirmation message.
	 * @return array<string, mixed>
	 */
	private static function default_settings( string $message = '' ): array {
		return [
			'color'        => '#6366f1',
			'position'     => 'bottom-right',
			'confirmation' => [
				'type'    => 'message',
				'message' => $message ?: __( 'Thank you for your feedback!', 'pollquest' ),
			],
		];
	}
}
