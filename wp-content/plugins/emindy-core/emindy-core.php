<?php
/**
 * Plugin Name:       eMINDy Core
 * Description:       Core CPTs, taxonomies, shortcodes, schema, content injectors and analytics for eMINDy.
 * Version:           0.5.0
 * Author:            eMINDy
 * Text Domain:       emindy-core
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

declare( strict_types=1 );

use EMINDY\Core\Admin;
use EMINDY\Core\Ajax;
use EMINDY\Core\Analytics;
use EMINDY\Core\CPT;
use EMINDY\Core\Content_Inject;
use EMINDY\Core\Diagnostics;
use EMINDY\Core\Meta;
use EMINDY\Core\Schema;
use EMINDY\Core\Shortcodes;
use EMINDY\Core\Shortcodes_Deprecated;
use EMINDY\Core\Taxonomy;
use function EMINDY\Core\assessment_result_base_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EMINDY_CORE_VERSION' ) ) {
	define( 'EMINDY_CORE_VERSION', '0.5.0' );
}

if ( ! defined( 'EMINDY_CORE_PATH' ) ) {
	define( 'EMINDY_CORE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EMINDY_CORE_URL' ) ) {
	define( 'EMINDY_CORE_URL', plugin_dir_url( __FILE__ ) );
}

require_once EMINDY_CORE_PATH . 'includes/helpers.php';
require_once EMINDY_CORE_PATH . 'includes/schema.php';
require_once EMINDY_CORE_PATH . 'includes/newsletter.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-cpt.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-taxonomy.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-shortcodes-deprecated.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-shortcodes.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-content-inject.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-meta.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-schema.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-admin.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-ajax.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-analytics.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-diagnostics.php';

Ajax::register();
Analytics::register();
Diagnostics::register();

/**
 * Run install tasks on plugin activation.
 *
 * @return void
 */
function emindy_core_activate(): void {
	// Newsletter table.
	if ( ! function_exists( 'emindy_newsletter_install_table' ) ) {
		require_once EMINDY_CORE_PATH . 'includes/newsletter.php';
	}

	emindy_newsletter_install_table();

	// Analytics table.
	if ( ! class_exists( Analytics::class ) ) {
		require_once EMINDY_CORE_PATH . 'includes/class-emindy-analytics.php';
	}

	Analytics::install_table();

	// Register CPTs and taxonomies before flushing rewrites.
	if ( class_exists( CPT::class ) && class_exists( Taxonomy::class ) ) {
		CPT::register_all();
		Taxonomy::register_all();
	}

	flush_rewrite_rules();
}

/**
 * Run cleanup tasks on plugin uninstall.
 *
 * @return void
 */
function emindy_core_uninstall(): void {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'emindy_newsletter',
		$wpdb->prefix . 'emindy_analytics',
	);

	foreach ( $tables as $table ) {
		$safe_table = preg_replace( '/[^A-Za-z0-9_]/', '', $table );

		if ( ! $safe_table ) {
			continue;
		}

		$sql = sprintf( 'DROP TABLE IF EXISTS `%s`', esc_sql( $safe_table ) );
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

register_activation_hook( __FILE__, 'emindy_core_activate' );
register_uninstall_hook( __FILE__, 'emindy_core_uninstall' );

/**
 * Register admin hooks.
 *
 * @return void
 */
function emindy_core_register_admin(): void {
	Admin::register();
}

/**
 * Load translations and Polylang meta copy rules.
 *
 * @return void
 */
function emindy_core_plugins_loaded(): void {
	load_plugin_textdomain( 'emindy-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	if ( defined( 'POLYLANG_VERSION' ) || function_exists( 'pll_the_languages' ) ) {
		add_filter( 'pll_copy_post_metas', 'emindy_core_polylang_copy_metas' );
	}
}

/**
 * Ensure Polylang copies structured meta fields for translations.
 *
 * @param array $metas Meta keys Polylang copies by default.
 * @return array
 */
function emindy_core_polylang_copy_metas( array $metas ): array {
	$extra = array(
		'em_steps_json',
		'em_chapters_json',
		'em_total_seconds',
		'em_prep_seconds',
		'em_perform_seconds',
		'em_supplies',
		'em_tools',
		'em_yield',
	);

	return array_values( array_unique( array_merge( $metas, $extra ) ) );
}

/**
 * Register custom post types and taxonomies.
 *
 * @return void
 */
function emindy_core_register_content_types(): void {
	CPT::register_all();
	Taxonomy::register_all();
}

/**
 * Register public shortcodes.
 *
 * @return void
 */
function emindy_core_register_shortcodes(): void {
	Shortcodes::register_all();
	Shortcodes_Deprecated::register_all();
}

/**
 * Register content injection hooks.
 *
 * @return void
 */
function emindy_core_register_content_inject(): void {
	Content_Inject::register();
}

/**
 * Register post meta.
 *
 * @return void
 */
function emindy_core_register_meta(): void {
	Meta::register();
}

/**
 * Enqueue public assets and localized data.
 *
 * @return void
 */
function emindy_core_enqueue_assets(): void {
	// Main shared CSS.
	wp_enqueue_style(
		'emindy-core',
		EMINDY_CORE_URL . 'assets/css/emindy-core.css',
		array(),
		EMINDY_CORE_VERSION
	);

	$post    = get_post();
	$content = $post ? (string) $post->post_content : '';

	$has_shortcode = static function ( string $shortcode ) use ( $content ): bool {
		return ( '' !== $content ) && has_shortcode( $content, $shortcode );
	};

	$is_assessments_page    = is_page( 'assessments' );
	$needs_assessment_forms = $is_assessments_page
		|| $has_shortcode( 'em_phq9' )
		|| $has_shortcode( 'em_gad7' )
		|| $has_shortcode( 'em_assessment_result' );

	$needs_player_assets   = is_singular( 'em_exercise' ) || $has_shortcode( 'em_player' ) || $has_shortcode( 'em_exercise_steps' );
	$needs_video_analytics = is_singular( 'em_video' )
		|| $has_shortcode( 'em_video_chapters' )
		|| $has_shortcode( 'em_transcript' )
		|| $has_shortcode( 'em_video_player' );

	$needs_assess_core = $needs_assessment_forms || $needs_player_assets || $needs_video_analytics;

	// Core data for assessments (must load BEFORE PHQ-9/GAD-7/player/video analytics).
	if ( $needs_assess_core ) {
		wp_register_script(
			'emindy-assess-core',
			EMINDY_CORE_URL . 'assets/js/assess-core.js',
			array(),
			EMINDY_CORE_VERSION,
			true
		);

		wp_localize_script(
			'emindy-assess-core',
			'emindyAssess',
			array(
				'ajax'        => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'emindy_assess' ),
				'results_url' => assessment_result_base_url(),
			)
		);

		wp_enqueue_script( 'emindy-assess-core' );
	}

	if ( $needs_player_assets ) {
		wp_enqueue_style(
			'emindy-player',
			EMINDY_CORE_URL . 'assets/css/player.css',
			array(),
			EMINDY_CORE_VERSION
		);

		wp_enqueue_script(
			'emindy-player',
			EMINDY_CORE_URL . 'assets/js/player.js',
			array( 'emindy-assess-core' ),
			EMINDY_CORE_VERSION,
			true
		);
	}

	if ( $needs_assessment_forms ) {
		wp_enqueue_script(
			'emindy-phq9',
			EMINDY_CORE_URL . 'assets/js/phq9.js',
			array( 'emindy-assess-core' ),
			EMINDY_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'emindy-gad7',
			EMINDY_CORE_URL . 'assets/js/gad7.js',
			array( 'emindy-assess-core' ),
			EMINDY_CORE_VERSION,
			true
		);
	}

	if ( $needs_video_analytics ) {
		wp_enqueue_script(
			'emindy-video-analytics',
			EMINDY_CORE_URL . 'assets/js/video-analytics.js',
			array( 'emindy-assess-core' ),
			EMINDY_CORE_VERSION,
			true
		);
	}
}

/**
 * Output fallback JSON-LD when Rank Math is unavailable.
 *
 * @return void
 */
function emindy_core_output_schema_fallback(): void {
	if ( class_exists( '\RankMath\Plugin' ) || function_exists( 'rank_math' ) ) {
		return;
	}

	Schema::output_jsonld();
}

/**
 * Expose the current post ID to front-end scripts.
 *
 * @return void
 */
function emindy_core_enqueue_post_id(): void {
	if ( ! is_singular() ) {
		return;
	}

	if ( ! wp_script_is( 'emindy-assess-core', 'enqueued' ) && ! wp_script_is( 'emindy-assess-core', 'registered' ) ) {
		return;
	}

	$post_id = absint( get_queried_object_id() );

	if ( $post_id <= 0 ) {
		return;
	}

	wp_add_inline_script(
		'emindy-assess-core',
		'window.em_post_id=' . wp_json_encode( $post_id ) . ';',
		'before'
	);
}

/**
 * Handle redirects for common missing slugs.
 *
 * @return void
 */
function emindy_core_template_redirect_fallbacks(): void {
	if ( ! is_404() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $request_uri ) : '';
	$path        = wp_parse_url( $request_uri, PHP_URL_PATH );
	$path        = is_string( $path ) ? trim( sanitize_text_field( $path ), '/' ) : '';

	// /library -> redirect to Articles hub if a "library" page is missing.
	if ( 'library' === $path && ! get_page_by_path( 'library' ) ) {
		wp_safe_redirect( home_url( '/articles/' ), 301 );
		exit;
	}

	// /blog -> redirect to latest posts if a "blog" page is missing.
	if ( 'blog' === $path && ! get_page_by_path( 'blog' ) ) {
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}
}

add_action( 'init', 'emindy_core_register_admin' );
add_action( 'plugins_loaded', 'emindy_core_plugins_loaded' );
add_action( 'init', 'emindy_core_register_content_types' );
add_action( 'init', 'emindy_core_register_meta' );
add_action( 'init', 'emindy_core_register_shortcodes', 9 );
add_action( 'init', 'emindy_core_register_content_inject' );
add_action( 'wp_enqueue_scripts', 'emindy_core_enqueue_assets', 20 );
add_action( 'wp_enqueue_scripts', 'emindy_core_enqueue_post_id', 30 );
add_action( 'wp_head', 'emindy_core_output_schema_fallback', 99 );
add_action( 'template_redirect', 'emindy_core_template_redirect_fallbacks' );
