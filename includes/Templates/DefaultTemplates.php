<?php
/**
 * Default Templates
 *
 * @package WPAsk
 */

namespace InsightPulse\Templates;

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
				'title'       => __( 'Start from scratch', 'wpask' ),
				'description' => __( 'Create a completely custom survey with a blank canvas.', 'wpask' ),
				'icon'        => 'file-plus',
				'category'    => 'general',
				'is_pro'      => false,
				'questions'   => [],
				'settings'    => self::default_settings(),
			]
		);

		Registry::register(
			'website-feedback',
			[
				'id'          => 'website-feedback',
				'title'       => __( 'Website Feedback', 'wpask' ),
				'description' => __( 'A general template to capture feedback about your website.', 'wpask' ),
				'icon'        => 'globe',
				'category'    => 'general',
				'is_pro'      => false,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'rating',
						'label'    => __( 'How would you rate your experience on our website today?', 'wpask' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'Please tell us what we could do to improve your experience.', 'wpask' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thank you for your feedback!', 'wpask' )
				),
			]
		);

		Registry::register(
			'nps-survey',
			[
				'id'          => 'nps-survey',
				'title'       => __( 'NPS Survey', 'wpask' ),
				'description' => __( 'Measure Net Promoter Score to gauge customer loyalty.', 'wpask' ),
				'icon'        => 'trending-up',
				'category'    => 'nps',
				'is_pro'      => false,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'nps',
						'label'    => __( 'How likely are you to recommend us to a friend or colleague?', 'wpask' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'What is the primary reason for your score?', 'wpask' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thanks! Your feedback helps us improve.', 'wpask' )
				),
			]
		);

		Registry::register(
			'post-purchase',
			[
				'id'          => 'post-purchase',
				'title'       => __( 'Post-Purchase Review', 'wpask' ),
				'description' => __( 'Gather feedback immediately after a customer buys from you.', 'wpask' ),
				'icon'        => 'shopping-cart',
				'category'    => 'ecommerce',
				'is_pro'      => false,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'rating',
						'label'    => __( 'How satisfied are you with your shopping experience?', 'wpask' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'radio',
						'label'    => __( 'What was the main reason you purchased from us today?', 'wpask' ),
						'options'  => [
							__( 'Price', 'wpask' ),
							__( 'Product Quality', 'wpask' ),
							__( 'Brand Reputation', 'wpask' ),
							__( 'Customer Service', 'wpask' ),
						],
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thank you for your business and your feedback!', 'wpask' )
				),
			]
		);

		Registry::register(
			'lead-capture',
			[
				'id'          => 'lead-capture',
				'title'       => __( 'Lead Capture', 'wpask' ),
				'description' => __( 'Collect email addresses and interests from interested visitors.', 'wpask' ),
				'icon'        => 'mail',
				'category'    => 'leads',
				'is_pro'      => false,
				'questions'   => [
					[
						'id'       => 'q1',
						'type'     => 'email',
						'label'    => __( 'Enter your email to stay updated.', 'wpask' ),
						'required' => true,
					],
					[
						'id'       => 'q2',
						'type'     => 'text',
						'label'    => __( 'What topics interest you most?', 'wpask' ),
						'required' => false,
					],
				],
				'settings'    => self::default_settings(
					__( 'Thanks for subscribing!', 'wpask' )
				),
			]
		);

		Registry::register(
			'ecommerce-advanced',
			[
				'id'          => 'ecommerce-advanced',
				'title'       => __( 'eCommerce Store Survey', 'wpask' ),
				'description' => __( 'Uncover why visitors purchase — or abandon their cart.', 'wpask' ),
				'icon'        => 'store',
				'category'    => 'ecommerce',
				'is_pro'      => true,
				'questions'   => [],
				'settings'    => self::default_settings(),
			]
		);

		Registry::register(
			'customer-effort',
			[
				'id'          => 'customer-effort',
				'title'       => __( 'Customer Effort Score', 'wpask' ),
				'description' => __( 'Measure how easy it is for customers to get help or complete tasks.', 'wpask' ),
				'icon'        => 'headphones',
				'category'    => 'support',
				'is_pro'      => true,
				'questions'   => [],
				'settings'    => self::default_settings(),
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
				'message' => $message ?: __( 'Thank you for your feedback!', 'wpask' ),
			],
		];
	}
}
