<?php
/**
 * Application Configuration.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Config;

return [
	'slug'      => 'insightpulse',
	'rest'      => [
		'namespace' => 'insightpulse/v1',
	],
	'admin'     => [
		'menu_slug' => 'insightpulse',
	],
	'frontend'  => [
		'script_handle' => 'insightpulse-frontend',
		'style_handle'  => 'insightpulse-style',
	],
	'meta_keys' => [
		'answers_count'   => 'answers_count',
		'reportable_data' => 'reportable_data',
		'ai_summary'      => 'ai_summary',
	],
];
