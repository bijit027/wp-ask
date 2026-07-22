<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* translators: %s: survey title */
echo sprintf(
	esc_html__( 'New Response to %s', 'wpask' ),
	esc_html( $survey_title )
);


echo "\n\n";

$notification_config_url = admin_url( 'admin.php?page=wpask#/surveys/edit/' . $survey_id . '/notifications' );

/* translators: %1$s: blog name, %2$s: settings URL */
echo sprintf(
	esc_html__( 'You are receiving this WPAsk survey notification from %1$s. Adjust your settings here: %2$s.', 'wpask' ),
	esc_html( get_bloginfo( 'name' ) ),
	esc_url_raw( $notification_config_url )
);

echo "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

foreach ( $answers as $answer ) {
	echo "\t" . esc_html( $answer['question_title'] ) . "\n\n";
	echo "\t" . esc_html( $answer['value'] ) . "\n\n";
	echo "\t--------\n\n";
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: site URL */
echo sprintf(
	esc_html__( 'Sent from %s', 'wpask' ),
	esc_url_raw( get_site_url() )
);
