<?php
/**
 * Automatic content injection for eMINDy custom post types.
 *
 * @package EmindyCore
 */

declare( strict_types=1 );

namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles automatic injection of core shortcodes into eMINDy content types.
 *
 * Prepends the exercise player to exercises and appends video chapters
 * to videos on singular views of those post types.
 */
final class Content_Inject {

	/**
	 * Filter priority used for injecting content.
	 *
	 * @var int
	 */
	private const FILTER_PRIORITY = 9;

	/**
	 * Exercise post type.
	 *
	 * @var string
	 */
	private const POST_TYPE_EXERCISE = 'em_exercise';

	/**
	 * Video post type.
	 *
	 * @var string
	 */
	private const POST_TYPE_VIDEO = 'em_video';

	/**
	 * Exercise player shortcode tag.
	 *
	 * @var string
	 */
	private const SHORTCODE_PLAYER = 'em_player';

	/**
	 * Video chapters shortcode tag.
	 *
	 * @var string
	 */
	private const SHORTCODE_CHAPTERS = 'em_video_chapters';

	/**
	 * Register hooks.
	 *
	 * Intended to be called from the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register(): void {
		// Run slightly before the default priority so other filters
		// see the injected shortcodes as part of the content.
		add_filter( 'the_content', [ __CLASS__, 'inject' ], self::FILTER_PRIORITY );
	}

	/**
	 * Inject shortcode blocks into exercise and video content.
	 *
	 * @param string $content The post content.
	 * @return string Filtered content.
	 */
	public static function inject( string $content ): string {
		if ( '' === $content ) {
			return $content;
		}

		// Bail early for non-frontend-like contexts.
		if ( is_admin() || is_feed() || wp_doing_ajax() ) {
			return $content;
		}

		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return $content;
		}

		if ( is_embed() ) {
			return $content;
		}

		// Only act on the main loop for the primary query.
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post instanceof \WP_Post ) {
			return $content;
		}

		// Allow other code to short-circuit the injection.
		if ( ! apply_filters( 'emindy_content_inject_enabled', true, $post ) ) {
			return $content;
		}

		// Only handle the eMINDy core post types we care about.
		if ( ! in_array( $post->post_type, self::get_supported_post_types(), true ) ) {
			return $content;
		}

		if ( self::POST_TYPE_EXERCISE === $post->post_type ) {
			$content = self::inject_exercise_player( $content );
		}

		if ( self::POST_TYPE_VIDEO === $post->post_type ) {
			$content = self::inject_video_chapters( $content );
		}

		return $content;
	}

	/**
	 * Get the list of supported post types for automatic content injection.
	 *
	 * @return string[]
	 */
	private static function get_supported_post_types(): array {
		/**
		 * Filters the list of post types that receive automatic shortcode injection.
		 *
		 * @param string[] $post_types Supported post types.
		 */
		return (array) apply_filters(
			'emindy_content_inject_post_types',
			[
				self::POST_TYPE_EXERCISE,
				self::POST_TYPE_VIDEO,
			]
		);
	}

	/**
	 * Prepend the exercise player shortcode if not already present.
	 *
	 * @param string $content Post content.
	 * @return string Filtered content.
	 */
	private static function inject_exercise_player( string $content ): string {
		if ( has_shortcode( $content, self::SHORTCODE_PLAYER ) ) {
			return $content;
		}

		$shortcode = (string) apply_filters(
			'emindy_content_inject_player_shortcode',
			'[' . self::SHORTCODE_PLAYER . ']'
		);

		return $shortcode . "\n\n" . $content;
	}

	/**
	 * Append the video chapters shortcode if not already present.
	 *
	 * @param string $content Post content.
	 * @return string Filtered content.
	 */
	private static function inject_video_chapters( string $content ): string {
		if ( has_shortcode( $content, self::SHORTCODE_CHAPTERS ) ) {
			return $content;
		}

		$shortcode = (string) apply_filters(
			'emindy_content_inject_chapters_shortcode',
			'[' . self::SHORTCODE_CHAPTERS . ']'
		);

		return $content . "\n\n" . $shortcode;
	}
}
