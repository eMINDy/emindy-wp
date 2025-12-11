<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and sanitizes meta fields for eMINDy custom post types.
 *
 * These meta fields power the exercise player, video chapters, and
 * structured data output (schema) for search engines.
 */
class Meta {

	/**
	 * Bootstrap registration of all custom meta fields.
	 *
	 * Intended to be called on the `init` hook by the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( ! function_exists( 'register_post_meta' ) ) {
			return;
		}

		// Shared auth callback so only editors can modify REST-exposed meta.
		$auth_callback = [ __CLASS__, 'can_edit_meta' ];

		// Video chapters (JSON).
		\register_post_meta(
			'em_video',
			'em_chapters_json',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_json' ],
				'description'       => 'JSON-encoded chapters for video navigation and schema.',
			]
		);

		// Steps for exercises (JSON).
		\register_post_meta(
			'em_exercise',
			'em_steps_json',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_json' ],
				'description'       => 'JSON-encoded steps for the exercise player and schema.',
			]
		);

		// Total time in seconds (HowTo.totalTime).
		\register_post_meta(
			'em_exercise',
			'em_total_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_integer_meta' ],
				'description'       => 'Total duration of the exercise in seconds.',
			]
		);

		// Preparation time in seconds (prepTime).
		\register_post_meta(
			'em_exercise',
			'em_prep_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_integer_meta' ],
				'description'       => 'Preparation time in seconds before starting the exercise.',
			]
		);

		// Perform time in seconds (performTime).
		\register_post_meta(
			'em_exercise',
			'em_perform_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_integer_meta' ],
				'description'       => 'Active performance time in seconds for the exercise.',
			]
		);

		// Supplies needed (comma-separated string or JSON array).
		\register_post_meta(
			'em_exercise',
			'em_supplies',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_string_meta' ],
				'description'       => 'Supplies required to complete the exercise (string or JSON).',
			]
		);

		// Tools needed (string or JSON).
		\register_post_meta(
			'em_exercise',
			'em_tools',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_string_meta' ],
				'description'       => 'Tools required for the exercise (string or JSON).',
			]
		);

		// Exercise yield/output.
		\register_post_meta(
			'em_exercise',
			'em_yield',
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => [ __CLASS__, 'sanitize_string_meta' ],
				'description'       => 'Yield/output of the exercise (e.g. repetitions, result label).',
			]
		);
	}

	/**
	 * Sanitize JSON string or array values for meta storage.
	 *
	 * @param mixed $value Raw value from the request or database.
	 * @return string Sanitized JSON string or empty string on failure.
	 */
	public static function sanitize_json( $value ): string {
		if ( is_string( $value ) ) {
			$value = trim( \wp_unslash( $value ) );

			if ( '' === $value ) {
				return '';
			}

			$data = \json_decode( $value, true );

			if ( JSON_ERROR_NONE !== \json_last_error() ) {
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

		// Preserve nested structure while sanitizing scalar values.
		$sanitized = \map_deep(
			$data,
			static function ( $item ) {
				if ( is_string( $item ) ) {
					return \sanitize_text_field( $item );
				}

				if ( is_numeric( $item ) ) {
					// Coerce numeric values to their appropriate scalar type.
					return $item + 0;
				}

				// Non-scalar values (arrays/objects) are returned as-is so structure is preserved.
				return $item;
			}
		);

		return \wp_json_encode( $sanitized );
	}

	/**
	 * Sanitize integer meta values, ensuring non-negative integers.
	 *
	 * @param mixed $value Raw value from the request or database.
	 * @return int Sanitized integer value.
	 */
	public static function sanitize_integer_meta( $value ): int {
		return is_numeric( $value ) ? \absint( $value ) : 0;
	}

	/**
	 * Sanitize string meta values that may be provided as strings or arrays.
	 *
	 * @param mixed $value Raw value from the request or database.
	 * @return string Sanitized string or JSON-encoded sanitized array.
	 */
	public static function sanitize_string_meta( $value ): string {
		if ( is_array( $value ) ) {
			$sanitized = \map_deep(
				$value,
				static function ( $item ) {
					if ( is_string( $item ) ) {
						return \sanitize_text_field( $item );
					}

					if ( is_numeric( $item ) ) {
						return $item + 0;
					}

					return $item;
				}
			);

			return \wp_json_encode( $sanitized );
		}

		if ( is_string( $value ) ) {
			return \sanitize_text_field( \wp_unslash( $value ) );
		}

		if ( is_numeric( $value ) ) {
			return (string) ( $value + 0 );
		}

		return '';
	}

	/**
	 * Determine whether the current user can edit meta values.
	 *
	 * Used as `auth_callback` for all registered meta.
	 *
	 * @return bool
	 */
	public static function can_edit_meta(): bool {
		return \current_user_can( 'edit_posts' );
	}
}
