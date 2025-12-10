<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers meta fields for eMINDy custom post types.
 */
class Meta {
	/**
	 * Register all custom meta fields.
	 *
	 * @return void
	 */
	public static function register() {
		$auth_callback = [ __CLASS__, 'can_edit_meta' ];

		// Reuse a single auth callback so only editors can modify REST meta
		// values across all CPT fields registered below. All meta is exposed to
		// REST so the block editor and API clients can edit structured fields.

		// Chapters for videos.
		register_post_meta(
			'em_video',
			'em_chapters_json',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_json' ],
			]
		);

		// Steps for exercises.
		register_post_meta(
			'em_exercise',
			'em_steps_json',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_json' ],
			]
		);

		// Total time in seconds for HowTo exercises. Used for HowTo.totalTime schema.
		register_post_meta(
			'em_exercise',
			'em_total_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_integer_meta' ],
			]
		);

		// Preparation time in seconds (prepTime).
		register_post_meta(
			'em_exercise',
			'em_prep_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_integer_meta' ],
			]
		);

		// Perform time in seconds (performTime).
		register_post_meta(
			'em_exercise',
			'em_perform_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_integer_meta' ],
			]
		);

		// Supplies needed for the exercise. Stored as string (comma separated) or JSON array.
		register_post_meta(
			'em_exercise',
			'em_supplies',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_string_meta' ],
			]
		);

		// Tools needed for the exercise. Stored as string or JSON.
		register_post_meta(
			'em_exercise',
			'em_tools',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_string_meta' ],
			]
		);

		// Yield value (e.g. number of repetitions or result). Stored as string.
		register_post_meta(
			'em_exercise',
			'em_yield',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_string_meta' ],
			]
		);
	}


	/**
	 * Sanitize JSON string or array values for meta storage.
	 *
	 * @param mixed $value Raw value from request or database.
	 * @return string Sanitized JSON string or empty string on failure.
	 */
	public static function sanitize_json( $value ) {
		if ( is_string( $value ) ) {
			$value = trim( wp_unslash( $value ) );
			if ( '' === $value ) {
				return '';
			}

			$data = json_decode( $value, true );
			if ( JSON_ERROR_NONE !== json_last_error() ) {
				return '';
			}
		} elseif ( is_array( $value ) ) {
			$data = $value;
		} else {
			return '';
		}

		if ( ! is_array( $data ) ) {
			return '';
		}

                $sanitized = map_deep(
                        $data,
                        static function ( $item ) {
                                if ( is_string( $item ) ) {
                                        return sanitize_text_field( $item );
                                }

                                if ( is_numeric( $item ) ) {
                                        return $item + 0;
                                }

                                // Preserve nested array structure while
                                // stripping tags and coercing scalars so JSON
                                // consumers cannot inject markup.
                                return $item;
                        }
                );

		return wp_json_encode( $sanitized );
	}

	/**
	 * Sanitize integer meta values, ensuring non-negative integers.
	 *
	 * @param mixed $value Raw value from request or database.
	 * @return int Sanitized integer value.
	 */
	public static function sanitize_integer_meta( $value ) {
		return is_numeric( $value ) ? absint( $value ) : 0;
	}

	/**
	 * Sanitize string meta values that may be provided as strings or arrays.
	 *
	 * @param mixed $value Raw value from request or database.
	 * @return string Sanitized string or JSON-encoded sanitized array.
	 */
	public static function sanitize_string_meta( $value ) {
		if ( is_array( $value ) ) {
			return wp_json_encode( map_deep( $value, 'sanitize_text_field' ) );
		}

		return is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : '';
	}

	/**
	 * Determine whether the current user can edit meta values.
	 *
	 * @return bool
	 */
	public static function can_edit_meta() {
		return current_user_can( 'edit_posts' );
	}
}
