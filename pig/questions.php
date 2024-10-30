<?php

class PIG_Questions {
	private $registered_questions = array();
	private $group_id = false;

	function __construct() {
		// Run early so that if a filter is added manually it will override this by default
		add_filter( 'pig_group_id', array( $this, 'get_group_id' ), 9 );
	}

	function set_group_id( $group_id ) {
		$this->group_id = (int) $group_id;
	}

	function get_group_id() {
		return $this->group_id;
	}

	function register_question( $profile_field, $question_text, $possessive_name = false, $answer_callback = false ) {
		$question_data = array(
			'profile_field'   => $profile_field,
			'question_text'   => $question_text,
			'possessive_name' => (bool) $possessive_name,
			'answer_callback' => $answer_callback,
		);
		$question_key = md5( var_export( $question_data, true ) );

		$this->registered_questions[$question_key] = apply_filters( 'pig_register_question', $question_data );
	}

	function get_group_members() {
		// You MUST add a 'pig_group_id' filter for PIG to work
		$group_id = apply_filters( 'pig_group_id', false );
		if ( ! $group_id )
			return false;

		$bp_groups_member = new BP_Groups_Member;
		return apply_filters( 'pig_get_group_members', $bp_groups_member->get_group_member_ids( $group_id ) );
	}

	function get_random_user_id() {
		$first_signup = false;

		$group_member_ids = array_diff( $this->get_group_members(), array( get_current_user_id() ) );

		// Find the first signup, used to weight new users more heavily
		foreach ( $group_member_ids as $group_member_id ) {
			$member = get_user_by( 'id', $group_member_id );
			if ( ! $first_signup || strtotime( $member->user_registered ) < $first_signup )
				$first_signup = strtotime( $member->user_registered );
		}

		// Get all users and the difference between their signup dates and the oldest one
		// Find the largest difference in signup dates
		$members = array();
		$max_diff = 0;
		foreach ( $group_member_ids as $group_member_id ) {
			$member = get_user_by( 'id', $group_member_id );
			$member_diff = strtotime( $member->user_registered ) - $first_signup;
			$members[] = array( 'id' => $group_member_id, 'diff' => $member_diff );

			if ( $member_diff > $max_diff )
				$max_diff = $member_diff;
		}

		// Normalize signup date differences on a 0-1 scale
		foreach ( $members as $key => $member ) {
			$member['weight'] = $member['diff'] / $max_diff;
			$members[$key]  = $member;
		}

		// Weight new users more heavily than old users
		// Weight = ( normalized diff + 0.1 ) * 100
		$group_member_ids = array();
		foreach ( $members as $member ) {
			for ( $i = 0; $i < ( $member['weight'] + 0.1 ) * 100; $i++ )
				$group_member_ids[] = $member['id'];
		}

		return apply_filters( 'pig_get_random_user_id', $group_member_ids[array_rand( $group_member_ids )] );
	}

	function get_registered_questions() {
		return $this->registered_questions;
	}

	function get_random_question( $user_id ) {
		$questions = $this->get_registered_questions();

		// Sanity checker
		$no_infinite_loops = 0;
		$max_iterations = count( $this->get_group_members() );

		$valid_question = false;
		do {
			// Get a random question and its key
			$question_key = array_rand( $questions );

			// Make sure the question has a non-empty answer
			$valid_question = (bool) trim( $this->get_answer( $question_key, $user_id ) );
			$no_infinite_loops++;
		} while( ! $valid_question && $no_infinite_loops < $max_iterations );

		if ( ! $valid_question )
			return false;

		$question = $this->compose_question( $question_key, $user_id );
		return array( 'question' => $question, 'question_key' => $question_key, 'user_id' => $user_id );
	}

	function compose_question( $key, $user_id, $link_name = false ) {
		// Get the real name to use in the question
		$name = xprofile_get_field_data( 'Name', $user_id );

		if ( $this->registered_questions[$key]['possessive_name'] )
			$name = $this->make_noun_possessive( $name );

		if ( $link_name )
			$name = sprintf( '<a href="%s">%s</a>', bp_core_get_user_domain( $user_id ), $name );

		// Build the question text
		return sprintf( $this->registered_questions[$key]['question_text'], $name );
	}

	function get_potential_answers( $question_key, $user_id ) {
		// Real answer
		$correct_answer = trim( $this->get_answer( $question_key, $user_id ) );

		// Sanity checker
		$no_infinite_loops = 0;
		$max_iterations = $this->get_group_members();

		// Get other choices that are also real answers
		$other_answers = array();
		$answers_count = apply_filters( 'pig_potential_answers_count', 3, $question_key, $user_id );
		do {
			$random_answer = trim( $this->get_answer( $question_key, $this->get_random_user_id() ) );

			// Don't accidentally use the right answer, adn already-used answer, or an empty answer as another choice
			if ( strtolower( $random_answer ) != strtolower( $correct_answer ) && ! in_array( $random_answer, $other_answers ) && ! empty( $random_answer ) )
				$other_answers[] = $random_answer;

			$no_infinite_loops++;
		} while( count( $other_answers ) < $answers_count && $no_infinite_loops < $max_iterations );

		// Merge the right and wrong answers
		$all_answers = array_merge( $other_answers, (array) $correct_answer );

		// Randomize the order for easy usage
		shuffle( $all_answers );
		return apply_filters( 'pig_get_potential_answers', $all_answers, $question_key, $user_id );
	}

	function get_answer( $question_key, $user_id ) {
		$questions = $this->get_registered_questions();

		$answer = xprofile_get_field_data( $questions[$question_key]['profile_field'], $user_id );

		if ( $questions[$question_key]['answer_callback'] )
			$answer = call_user_func_array( $questions[$question_key]['answer_callback'], (array) $answer );

		return apply_filters( 'pig_get_answer', $answer, $question_key, $user_id );
	}

	function check_answer( $question_key, $user_id, $answer ) {
		$is_correct = $answer == $this->get_answer( $question_key, $user_id );

		return apply_filters( 'pig_check_answer', $is_correct, $question_key, $user_id, $answer );
	}

	function make_noun_possessive( $noun ) {
		$noun = trim( $noun );
		if ( 's' == substr( $noun, -1, 1 ) )
			$noun .= "'";
		else
			$noun .= "'s";

		return $noun;
	}
}

$pig_questions = new PIG_Questions;

function pig_register_question( $profile_field, $question_text, $possessive_name = false, $answer_callback = false ) {
	global $pig_questions;
	$pig_questions->register_question( $profile_field, $question_text, $possessive_name, $answer_callback );
}

function pig_set_group( $group_id ) {
	global $pig_questions;
	$pig_questions->set_group_id( $group_id );
}