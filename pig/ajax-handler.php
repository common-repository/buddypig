<?php

class PIGJAX {
	const nonce_action = 'pig_answer';
	function __construct() {
		add_action( 'wp_ajax_pig', array( $this, 'do_question' ) );
	}

	function do_question() {
		global $pig_questions;

		// Attempting to answer a question
		if ( $_POST['answer'] )
			$this->parse_answer();

		// Render a new question
		$question_data = $this->get_new_question();

		$question = $question_data['question'];
		$answers  = $question_data['answers'];
		$user_id  = $question_data['user_id'];

		echo $this->get_output( $question, $answers, $user_id );
		exit;
	}

	function parse_answer() {
		global $pig_questions;

		check_ajax_referer( self::nonce_action );

		$correct_answer = $pig_questions->get_answer( $_POST['question'], $_POST['user_id'] );

		if ( md5( $correct_answer ) == $_POST['answer'] )
			$this->do_correct_answer( $_POST['question'], $_POST['user_id'] );
		else
			$this->do_incorrect_answer( $_POST['question'], $_POST['user_id'] );
	}

	function do_correct_answer( $question_key, $user_id ) {
		global $pig_scores;

		$pig_scores->increment_score( true, $question_key, $user_id );

		$score    = $pig_scores->get_score();
		$username = get_user_by( 'id', $_POST['user_id'] )->user_login;

		$response = sprintf( "That's right! You have %d correct &amp; %d incorrect so far.",
			(int) $score['correct'],
			(int) $score['incorrect']
		);
		$response = apply_filters( 'pig_correct_response', $response, $answer, $score, $username );

		// Give them a reward every 10 correct answers
		echo '<div class="pig-correct' . ( 0 == $score['correct'] % 10 ? ' reward' : '' ) . '">' . $response . '</div>';
	}

	function do_incorrect_answer( $question_key, $user_id ) {
		global $pig_questions, $pig_scores;

		$pig_scores->increment_score( false, $question_key, $user_id );

		$name = xprofile_get_field_data( 'Name', $_POST['user_id'] );

		$response = sprintf( "WRONG! You don't know jack. Go check out <a href='%s'>%s</a> profile.",
			esc_url( bp_core_get_user_domain( $user_id ) ),
			esc_html( $pig_questions->make_noun_possessive( $name ) )
		);
		$response = apply_filters( 'pig_incorrect_response', $response, $answer, $pig_scores->get_score(), $user_id );

		echo '<div class="pig-incorrect">' . $response . '</div>';
	}

	function get_new_question() {
		global $pig_questions;

		$question = false;
		do {
			$user_id  = $pig_questions->get_random_user_id();
			$question = $pig_questions->get_random_question( $user_id );
		} while( ! $question );

		$answers = $pig_questions->get_potential_answers( $question['question_key'], $user_id );

		return array( 'question' => $question, 'answers' => $answers, 'user_id' => $user_id );
	}

	function get_output( $question, $answers, $user_id ) {
		$output  = '<div id="pig-panel-position">';
		$output .= '<div style="float:left;">' . get_avatar( $question['user_id'] ) . '</div>';
		$output .= '<div id="pig-question">';
		$output .= '<h3>' . esc_html( $question['question'] ) . '</h3>';
		$output .= '<ul id="pig-answers">';

		foreach ( $answers as $answer ) {
			$output .= '<li id="pig-answer-' . esc_js( md5( $answer ) ) . '">';
			$output .= '<a href="#" data-_ajax_nonce="' . wp_create_nonce( self::nonce_action ).  '" data-question="' . esc_attr( $question['question_key'] ) . '" data-answer="' . esc_attr( md5( $answer ) ) . '" data-user_id="' . esc_attr( $user_id ) . '" class="pig-answer">';
			$output .= esc_html( strip_tags( $answer ) );
			$output .= '</a>';
			$output .= '</li>';
		}

		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}
}


new PIGJAX();
