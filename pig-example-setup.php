<?php

/**
 * This file is meant only as an example, not to be used
 *
 * Hopefully this gives you an idea of how you would setup PIG
 */

return; // Seriously, this won't do anything

function example_pig_setup_questions() {
	pig_register_question( 'Address', 'Where does %s live?', false, 'a8c_pig_dedupe_countries' );
	pig_register_question( 'Date of Birth', 'When was %s born?', false, 'a8c_pig_make_nice_dates' );
	pig_register_question( 'Alternate email', 'What is %s email address?', true );
	pig_register_question( 'Blog', 'What is %s blog URL?', true );
	pig_register_question( 'Skype', 'What is %s Skype username?', true );
	pig_register_question( 'Twitter', 'What is %s Twitter username?', true );

	// Our group
	pig_set_group( 1 );
}
add_action( 'pig_loaded', 'example_pig_setup_questions' );

function a8c_pig_make_nice_dates( $date ) {
	if ( $date )
		$date = date( 'F jS, Y', strtotime( $date ) );

	return $date;
}

function a8c_pig_dedupe_countries( $country ) {
	$usa_versions = array( 'US', 'USA', 'United States', 'U.S.A', 'U.S.', 'America', 'United States of America' );
	$uk_versions = array( 'UK', 'U.K.', 'United Kingdom' );
	foreach ( $usa_versions as $usa_version ) {
		if ( strtolower( $country ) == strtolower( $usa_version ) )
			return 'United States';
	}

	foreach ( $uk_versions as $uk_version ) {
		if ( strtolower( $country ) == strtolower( $uk_version ) )
			return 'United Kingdom';
	}

	return $country;
}