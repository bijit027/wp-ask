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
		Registry::register( 'website-feedback', [
			'id'          => 'website-feedback',
			'title'       => 'Website Feedback',
			'description' => 'A general template to capture feedback about your website.',
			'icon'        => 'dashicons-admin-site-alt3',
			'category'    => 'general',
			'questions'   => [
				[
					'id'       => 'q1',
					'type'     => 'rating',
					'label'    => 'How would you rate your experience on our website today?',
					'required' => true,
				],
				[
					'id'       => 'q2',
					'type'     => 'textarea',
					'label'    => 'Please tell us what we could do to improve your experience.',
					'required' => false,
				],
			],
			'settings'    => [
				'color'        => '#4F46E5',
				'position'     => 'bottom-right',
				'confirmation' => [
					'type'    => 'message',
					'message' => 'Thank you for your feedback!',
				],
			],
		] );

		Registry::register( 'nps-survey', [
			'id'          => 'nps-survey',
			'title'       => 'NPS Survey',
			'description' => 'Measure Net Promoter Score to gauge customer loyalty.',
			'icon'        => 'dashicons-chart-bar',
			'category'    => 'nps',
			'questions'   => [
				[
					'id'       => 'q1',
					'type'     => 'nps',
					'label'    => 'How likely are you to recommend us to a friend or colleague?',
					'required' => true,
				],
				[
					'id'       => 'q2',
					'type'     => 'textarea',
					'label'    => 'What is the primary reason for your score?',
					'required' => false,
				],
			],
			'settings'    => [
				'color'        => '#4F46E5',
				'position'     => 'bottom-right',
				'confirmation' => [
					'type'    => 'message',
					'message' => 'Thanks! Your feedback helps us improve.',
				],
			],
		] );
		
		Registry::register( 'post-purchase', [
			'id'          => 'post-purchase',
			'title'       => 'Post-Purchase Review',
			'description' => 'Gather feedback immediately after a customer buys from you.',
			'icon'        => 'dashicons-cart',
			'category'    => 'ecommerce',
			'questions'   => [
				[
					'id'       => 'q1',
					'type'     => 'rating',
					'label'    => 'How satisfied are you with your shopping experience?',
					'required' => true,
				],
				[
					'id'       => 'q2',
					'type'     => 'radio',
					'label'    => 'What was the main reason you purchased from us today?',
					'options'  => [
						[ 'label' => 'Price', 'value' => 'Price' ],
						[ 'label' => 'Product Quality', 'value' => 'Quality' ],
						[ 'label' => 'Brand Reputation', 'value' => 'Brand' ],
						[ 'label' => 'Customer Service', 'value' => 'Service' ],
					],
					'required' => false,
				],
			],
			'settings'    => [
				'color'        => '#4F46E5',
				'position'     => 'bottom-right',
				'confirmation' => [
					'type'    => 'message',
					'message' => 'Thank you for your business and your feedback!',
				],
			],
		] );
	}
}
