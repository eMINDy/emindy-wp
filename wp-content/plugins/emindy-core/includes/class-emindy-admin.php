<?php
/**
 * Admin UI and meta handling for the eMINDy Core plugin.
 *
 * @package EmindyCore
 */

declare( strict_types=1 );

namespace EMINDY\Core;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin UI: meta boxes, saving custom meta, assets, and notices.
 */
class Admin {

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	private const NONCE_FIELD = 'emindy_json_meta_nonce';

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'emindy_json_meta';

	/**
	 * Supported post types for JSON meta UI.
	 *
	 * @var string[]
	 */
	private const POST_TYPES = [ 'em_video', 'em_exercise' ];

	/**
	 * JSON meta keys handled by this class.
	 *
	 * @var string[]
	 */
	private const JSON_META_KEYS = [
		'em_chapters_json',
		'em_steps_json',
	];

	/**
	 * Exercise-only meta fields and their sanitization callbacks.
	 *
	 * @var array<string, callable|string>
	 */
	private const EXERCISE_META_FIELDS = [
		'em_total_seconds'   => 'absint',
		'em_prep_seconds'    => 'absint',
		'em_perform_seconds' => 'absint',
		'em_supplies'        => 'sanitize_text_field',
		'em_tools'           => 'sanitize_text_field',
		'em_yield'           => 'sanitize_text_field',
	];

	/**
	 * Register admin hooks.
	 *
	 * Intended to be called from the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'add_meta_boxes', [ __CLASS__, 'register_meta_boxes' ] );
		add_action( 'save_post', [ __CLASS__, 'save_meta' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ __CLASS__, 'missing_pages_notice' ] );
	}

	/**
	 * Register meta boxes for eMINDy post types.
	 *
	 * @return void
	 */
	public static function register_meta_boxes(): void {
		add_meta_box(
			'emindy_json_meta_video',
			esc_html__( 'eMINDy JSON Meta', 'emindy-core' ),
			[ __CLASS__, 'render_video_meta_box' ],
			'em_video',
			'normal',
			'default'
		);

		add_meta_box(
			'emindy_json_meta_exercise',
			esc_html__( 'eMINDy JSON Meta', 'emindy-core' ),
			[ __CLASS__, 'render_exercise_meta_box' ],
			'em_exercise',
			'normal',
			'default'
		);
	}

	/**
	 * Render meta box for video chapters JSON.
	 *
	 * @param WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public static function render_video_meta_box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$value = self::get_meta_string( $post->ID, 'em_chapters_json' );

		echo '<p><label for="em_chapters_json"><strong>' . esc_html__( 'Video chapters JSON (em_chapters_json)', 'emindy-core' ) . '</strong></label></p>';
		echo '<textarea style="width:100%;min-height:160px" id="em_chapters_json" name="em_chapters_json">'
			. esc_textarea( $value )
			. '</textarea>';
		echo '<p id="em_chapters_json_status" class="description" style="margin-top:6px;"></p>';
	}

	/**
	 * Render meta box for exercise steps JSON and HowTo metadata.
	 *
	 * @param WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public static function render_exercise_meta_box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$steps    = self::get_meta_string( $post->ID, 'em_steps_json' );
		$total    = self::get_meta_string( $post->ID, 'em_total_seconds' );
		$prep     = self::get_meta_string( $post->ID, 'em_prep_seconds' );
		$perform  = self::get_meta_string( $post->ID, 'em_perform_seconds' );
		$supplies = self::get_meta_string( $post->ID, 'em_supplies' );
		$tools    = self::get_meta_string( $post->ID, 'em_tools' );
		$yield    = self::get_meta_string( $post->ID, 'em_yield' );

		echo '<p><label for="em_steps_json"><strong>' . esc_html__( 'Exercise steps JSON (em_steps_json)', 'emindy-core' ) . '</strong></label></p>';
		echo '<textarea style="width:100%;min-height:160px" id="em_steps_json" name="em_steps_json">'
			. esc_textarea( $steps )
			. '</textarea>';
		echo '<p id="em_steps_json_status" class="description" style="margin-top:6px;"></p>';

		echo '<hr />';

		echo '<p><label for="em_total_seconds"><strong>' . esc_html__( 'Total time (seconds)', 'emindy-core' ) . '</strong></label><br />';
		echo '<input type="number" id="em_total_seconds" name="em_total_seconds" value="' . esc_attr( $total ) . '" placeholder="0" /></p>';

		echo '<p><label for="em_prep_seconds"><strong>' . esc_html__( 'Preparation time (seconds)', 'emindy-core' ) . '</strong></label><br />';
		echo '<input type="number" id="em_prep_seconds" name="em_prep_seconds" value="' . esc_attr( $prep ) . '" placeholder="0" /></p>';

		echo '<p><label for="em_perform_seconds"><strong>' . esc_html__( 'Perform time (seconds)', 'emindy-core' ) . '</strong></label><br />';
		echo '<input type="number" id="em_perform_seconds" name="em_perform_seconds" value="' . esc_attr( $perform ) . '" placeholder="0" /></p>';

		echo '<p><label for="em_supplies"><strong>' . esc_html__( 'Supplies (comma-separated or JSON array)', 'emindy-core' ) . '</strong></label><br />';
		echo '<input type="text" style="width:100%" id="em_supplies" name="em_supplies" value="' . esc_attr( $supplies ) . '" placeholder="' . esc_attr__( 'Mat, Strap', 'emindy-core' ) . '" /></p>';

		echo '<p><label for="em_tools"><strong>' . esc_html__( 'Tools (comma-separated or JSON array)', 'emindy-core' ) . '</strong></label><br />';
		echo '<input type="text" style="width:100%" id="em_tools" name="em_tools" value="' . esc_attr( $tools ) . '" placeholder="' . esc_attr__( 'Block, Timer', 'emindy-core' ) . '" /></p>';

		echo '<p><label for="em_yield"><strong>' . esc_html__( 'Yield (optional)', 'emindy-core' ) . '</strong></label><br />';
		echo '<input type="text" style="width:100%" id="em_yield" name="em_yield" value="' . esc_attr( $yield ) . '" placeholder="' . esc_attr__( 'e.g. Number of repetitions', 'emindy-core' ) . '" /></p>';
	}

	/**
	 * Save meta values.
	 *
	 * @param int     $post_id Saved post ID.
	 * @param WP_Post $post    Saved post object.
	 *
	 * @return void
	 */
	public static function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! self::is_supported_post_type( $post ) ) {
			return;
		}

		// Verify nonce.
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? (string) wp_unslash( $_POST[ self::NONCE_FIELD ] ) : '';
		if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		// Bail on autosave or revision.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		self::save_json_meta_fields( $post_id );

		if ( 'em_exercise' === $post->post_type ) {
			self::save_exercise_meta_fields( $post_id );
		}
	}

	/**
	 * Enqueue admin assets for relevant post edit screens.
	 *
	 * @param string $hook Current admin hook.
	 *
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, self::POST_TYPES, true ) ) {
			return;
		}

		if ( ! defined( 'EMINDY_CORE_URL' ) || ! defined( 'EMINDY_CORE_VERSION' ) ) {
			return;
		}

		wp_enqueue_script(
			'emindy-admin-json',
			EMINDY_CORE_URL . 'assets/js/admin-json.js',
			[ 'jquery' ],
			EMINDY_CORE_VERSION,
			true
		);

		wp_localize_script(
			'emindy-admin-json',
			'emindyAdmin',
			[
				'valid'   => esc_html__( 'Valid JSON ✔', 'emindy-core' ),
				'invalid' => esc_html__( 'Invalid JSON ✖', 'emindy-core' ),
			]
		);
	}

	/**
	 * Surface missing required pages to administrators using the shortcode output.
	 *
	 * @return void
	 */
	public static function missing_pages_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! shortcode_exists( 'em_admin_notice_missing_pages' ) ) {
			return;
		}

		// Reuse the shortcode to keep logic centralized.
		$notice = do_shortcode( '[em_admin_notice_missing_pages]' );

		if ( empty( $notice ) ) {
			return;
		}

		echo wp_kses_post( $notice );
	}

	/**
	 * Check if the post type is supported by this admin UI.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return bool
	 */
	private static function is_supported_post_type( WP_Post $post ): bool {
		return in_array( $post->post_type, self::POST_TYPES, true );
	}

	/**
	 * Get a meta value as a safe string.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 *
	 * @return string
	 */
	private static function get_meta_string( int $post_id, string $key ): string {
		$value = get_post_meta( $post_id, $key, true );

		if ( is_scalar( $value ) ) {
			return (string) $value;
		}

		return '';
	}

	/**
	 * Save JSON meta fields for the current post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	private static function save_json_meta_fields( int $post_id ): void {
		foreach ( self::JSON_META_KEYS as $key ) {
			if ( ! array_key_exists( $key, $_POST ) ) {
				continue;
			}

			$raw = (string) wp_unslash( $_POST[ $key ] );
			$value = $raw;

			if ( class_exists( Meta::class ) && method_exists( Meta::class, 'sanitize_json' ) ) {
				$value = Meta::sanitize_json( $raw );
			}

			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Save additional HowTo meta fields for exercises.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	private static function save_exercise_meta_fields( int $post_id ): void {
		foreach ( self::EXERCISE_META_FIELDS as $field_key => $callback ) {
			if ( ! array_key_exists( $field_key, $_POST ) ) {
				continue;
			}

			$raw       = (string) wp_unslash( $_POST[ $field_key ] );
			$sanitized = is_callable( $callback ) ? call_user_func( $callback, $raw ) : $raw;

			if ( '' === $sanitized || null === $sanitized ) {
				delete_post_meta( $post_id, $field_key );
			} else {
				update_post_meta( $post_id, $field_key, $sanitized );
			}
		}
	}
}
