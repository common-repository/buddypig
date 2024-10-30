<?php

class PIG_Scores {
	const meta_key = 'pig_scores';

	function get_score( $user_id = false ) {
		if ( ! $user_id )
			$user_id = get_current_user_id();

		$scores = get_usermeta( $user_id, self::meta_key );

		// In case you haven't played yet
		if ( empty( $scores ) )
			$scores = array( 'correct' => 0, 'incorrect' => 0 );

		return apply_filters( 'pig_get_score', $scores, $user_id );
	}

	function increment_score( $correct_answer, $question_key, $question_subject_id, $user_id = false ) {
		global $pig_questions;

		if ( ! $user_id )
			$user_id = get_current_user_id();

		// Parse the result
		$result = ( (bool) $correct_answer ) ? 'correct' : 'incorrect';

		// Calculate your new scores
		$score = $this->get_score( $user_id );
		$score[$result]++;
		$score = apply_filters( 'pig_increment_score', $score, $correct_answer, $user_id );

		// Log an activity item
		$action = sprintf( "%s answered a question %s",
			sprintf( '<a href="%s">%s</a>', bp_core_get_user_domain( $user_id ), xprofile_get_field_data( 'Name', $user_id ) ),
			"{$result}ly"
		);
		$args = array(
			'user_id'   => $user_id,
			'component' => self::meta_key,
			'type'      => $result,
			'content'   => $pig_questions->compose_question( $question_key, $question_subject_id, true ),
			'item_id'   => $question_subject_id,
			'action'    => $action,
		);
		bp_activity_add( $args );

		do_action( 'pig_increment_score', $correct_answer, $question_key, $question_subject_id, $user_id );
		update_usermeta( get_current_user_id(), self::meta_key, $score );
	}
}

$pig_scores = new PIG_Scores;