<?php

function pig_activity_filter() {
	global $pig_scores;

	$title = apply_filters( 'pig_activity_filter_title', 'PIG' );
	$value = $pig_scores::meta_key;

	echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $title ) . '</option>';
}
add_action( 'bp_member_activity_filter_options', 'pig_activity_filter' );
