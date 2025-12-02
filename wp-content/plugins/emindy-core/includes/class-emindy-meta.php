<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Meta {
	public static function register() {
		// Chapters for videos
		register_post_meta( 'em_video', 'em_chapters_json', [
			'type' => 'string',
			'single' => true,
			'show_in_rest' => true,
			'auth_callback' => function() { return current_user_can('edit_posts'); },
			'sanitize_callback' => [__CLASS__, 'sanitize_json'],
		] );
        // Steps for exercises
        register_post_meta( 'em_exercise', 'em_steps_json', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => [__CLASS__, 'sanitize_json'],
        ] );
        // Total time in seconds for HowTo exercises.  Used for HowTo.totalTime schema.
        register_post_meta( 'em_exercise', 'em_total_seconds', [
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => function( $value ) {
                return is_numeric( $value ) ? intval( $value ) : 0;
            },
        ] );
        // Preparation time in seconds (prepTime)
        register_post_meta( 'em_exercise', 'em_prep_seconds', [
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => function( $value ) {
                return is_numeric( $value ) ? intval( $value ) : 0;
            },
        ] );
        // Perform time in seconds (performTime)
        register_post_meta( 'em_exercise', 'em_perform_seconds', [
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => function( $value ) {
                return is_numeric( $value ) ? intval( $value ) : 0;
            },
        ] );
        // Supplies needed for the exercise.  Stored as string (comma separated) or JSON array.
        register_post_meta( 'em_exercise', 'em_supplies', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => function( $value ) {
                return is_string( $value ) ? sanitize_text_field( $value ) : '';
            },
        ] );
        // Tools needed for the exercise.  Stored as string or JSON.
        register_post_meta( 'em_exercise', 'em_tools', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => function( $value ) {
                return is_string( $value ) ? sanitize_text_field( $value ) : '';
            },
        ] );
        // Yield value (e.g. number of repetitions or result).  Stored as string.
        register_post_meta( 'em_exercise', 'em_yield', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
            'sanitize_callback' => function( $value ) {
                return is_string( $value ) ? sanitize_text_field( $value ) : '';
            },
        ] );
	}

	public static function sanitize_json( $value ) {
		$value = is_string($value) ? trim($value) : '';
		if ( $value === '' ) return '';
		$data = json_decode( $value, true );
		return ( json_last_error() === JSON_ERROR_NONE && is_array($data) ) ? wp_json_encode( $data ) : '';
	}
}
