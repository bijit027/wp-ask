<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* translators: %s: survey title */
printf(
	/* translators: %s: survey title */
	esc_html__( 'New Response to %s', 'pollquest' ),
	'<strong>' . esc_html( $survey_title ) . '</strong>'
);

echo "\n\n";

$notification_config_url = admin_url( 'admin.php?page=pollquest#/surveys/edit/' . $survey_id . '/notifications' );

/* translators: %1$s: blog name, %2$s: settings URL */
echo sprintf(
	/* translators: 1: site name, 2: settings link */
	esc_html__( 'You are receiving this PollQuest survey notification from %1$s. Adjust your settings here: %2$s.', 'pollquest' ),
	esc_html( get_bloginfo( 'name' ) ),
	'<a href="' . esc_url( $notification_config_url ) . '">' . esc_html__( 'here', 'pollquest' ) . '</a>'
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
printf(
	/* translators: %s: site URL */
	esc_html__( 'Sent from %s', 'pollquest' ),
	'<a href="' . esc_url( get_site_url() ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'
);