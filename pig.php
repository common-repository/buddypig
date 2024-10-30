<?php

/*
Plugin Name: P.I.G. (Personal Information Game)
Plugin URI: http://buddypig.com
Description: Personal Information Game.  Use profile information in BuddyPress to create a quiz game about other members in a group.
Version: 1.1.2
Author: Evan Solomon, Beau Lebens
*/

function pig_loader() {
	// We're going to set these to global instances of our classes
	global $pig_questions, $pig_scores;

	// Makes questions and checks answers
	require dirname( __FILE__ ) . '/pig/questions.php';

	// Creates the button
	require dirname( __FILE__ ) . '/pig/admin-bar.php';

	// Handles AJAX interaction
	require dirname( __FILE__ ) . '/pig/ajax-handler.php';

	// Adds PIG to the BuddyPress activity filters
	require dirname( __FILE__ ) . '/pig/activity.php';

	// Keeps track of correct and incorrect answers
	require dirname( __FILE__ ) . '/pig/scores.php';

	do_action( 'pig_loaded' );
}
add_action( 'bp_loaded', 'pig_loader' );
