<?php
/**
 * Application Configuration.
 *
 * @package PollQuest
 */

namespace PollQuest\Config;

return [
	'slug'      => 'pollquest',
	'rest'      => [
		'namespace' => 'pollquest/v1',
	],
	'admin'     => [
		'menu_slug' => 'pollquest',
	],
	'frontend'  => [
		'script_handle' => 'pollquest-frontend',
		'style_handle'  => 'pollquest-style',
	],
	'meta_keys' => [
		'answers_count'   => 'answers_count',
		'reportable_data' => 'reportable_data',
		'ai_summary'      => 'ai_summary',
	],
];
