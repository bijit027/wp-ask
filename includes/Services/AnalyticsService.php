<?php
/**
 * Analytics Service
 *
 * @package InsightPulse
 */

namespace InsightPulse\Services;

use InsightPulse\Repositories\MetaRepository;

/**
 * Class AnalyticsService
 * 
 * Handles pre-aggregated analytics (mirrors OpinionCraft Utility pattern).
 */
class AnalyticsService {

	/**
	 * @var MetaRepository
	 */
	private $meta_repo;

	public function __construct() {
		$this->meta_repo = new MetaRepository();
	}

	/**
	 * Increment counts for reportable answers (radio, checkbox, NPS, rating).
	 *
	 * @param int   $survey_id
	 * @param array $answers e.g., [ 'question_1' => 'Yes', 'question_2' => [ 'Option A', 'Option B' ] ]
	 */
	public function record_reportable_data( int $survey_id, array $answers ): void {
		if ( empty( $answers ) ) {
			return;
		}

		$meta_key        = 'reportable_data';
		$existing_values = $this->meta_repo->get_meta( 'survey', $survey_id, $meta_key ) ?: [];

		foreach ( $answers as $question_id => $value ) {
			if ( ! isset( $existing_values[ $question_id ] ) ) {
				$existing_values[ $question_id ] = [];
			}

			if ( is_array( $value ) ) {
				foreach ( $value as $v ) {
					if ( isset( $existing_values[ $question_id ][ $v ] ) ) {
						$existing_values[ $question_id ][ $v ]++;
					} else {
						$existing_values[ $question_id ][ $v ] = 1;
					}
				}
			} else {
				if ( isset( $existing_values[ $question_id ][ $value ] ) ) {
					$existing_values[ $question_id ][ $value ]++;
				} else {
					$existing_values[ $question_id ][ $value ] = 1;
				}
			}
		}

		$this->meta_repo->update_meta( 'survey', $survey_id, $meta_key, $existing_values );
	}

	/**
	 * Get the pre-aggregated reportable data for charts.
	 *
	 * @param int $survey_id
	 * @return array
	 */
	public function get_reportable_data( int $survey_id ): array {
		return $this->meta_repo->get_meta( 'survey', $survey_id, 'reportable_data' ) ?: [];
	}
}
