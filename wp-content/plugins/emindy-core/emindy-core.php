<?php
/**
 * Plugin Name: eMINDy Core
 * Description: Core CPTs, taxonomies, shortcodes, schema, and content injectors for eMINDy.
 * Version: 0.5.0
 * Author: eMINDy
 * Text Domain: emindy-core
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

define( 'EMINDY_CORE_VERSION', '0.5.0' );
define( 'EMINDY_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'EMINDY_CORE_URL', plugin_dir_url( __FILE__ ) );

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
\EMINDY\Core\Ajax::register();
require_once EMINDY_CORE_PATH . 'includes/class-emindy-analytics.php';
\EMINDY\Core\Analytics::register();
require_once EMINDY_CORE_PATH . 'includes/class-emindy-diagnostics.php';
\EMINDY\Core\Diagnostics::register();

/*
 * Activation & uninstall hooks
 *
 * Register custom database tables when the plugin is activated and clean up
 * when it is uninstalled. According to WordPress best practices, custom
 * tables should be created during activation rather than on theme switch
 *【331235010806737†L68-L70】. Moving the table creation here ensures that
 * the `emindy_newsletter` table is only created once and avoids conflicts
 * if the theme changes.
 */

/**
 * Activate the plugin: create the newsletter table.
 *
 * Registering a named function here avoids issues with anonymous functions
 * being unavailable when WordPress tries to call the hook. The install
 * function is loaded if necessary and then invoked.
 */
function emindy_core_activate() {
        if ( ! function_exists( 'emindy_newsletter_install_table' ) ) {
                require_once EMINDY_CORE_PATH . 'includes/newsletter.php';
	}
        emindy_newsletter_install_table();

        // Create analytics table for tracking events.
        if ( ! class_exists( '\EMINDY\Core\Analytics' ) ) {
                require_once EMINDY_CORE_PATH . 'includes/class-emindy-analytics.php';
	}
        \EMINDY\Core\Analytics::install_table();

        // Ensure custom content types are registered before flushing rewrite rules.
        if ( class_exists( '\EMINDY\Core\CPT' ) && class_exists( '\EMINDY\Core\Taxonomy' ) ) {
                \EMINDY\Core\CPT::register_all();
                \EMINDY\Core\Taxonomy::register_all();
	}

        // Flush rewrite rules on plugin activation to ensure custom post type slugs and archives are registered properly.
        flush_rewrite_rules();
}

/**
 * Uninstall the plugin: drop the newsletter table.
 *
 * If you prefer to keep subscriber data when uninstalling the plugin,
 * comment out or remove the DROP TABLE query below.
 */
function emindy_core_uninstall() {
	global $wpdb;

	$tables = [
		$wpdb->prefix . 'emindy_newsletter',
		$wpdb->prefix . 'emindy_analytics',
	];

	foreach ( $tables as $table ) {
		$safe_table = preg_replace( '/[^A-Za-z0-9_]/', '', $table );

		if ( empty( $safe_table ) ) {
			continue;
		}

		$table_sql = sprintf( 'DROP TABLE IF EXISTS `%s`', esc_sql( $safe_table ) );
		$wpdb->query( $table_sql );
	}
}

register_activation_hook( __FILE__, 'emindy_core_activate' );
register_uninstall_hook( __FILE__, 'emindy_core_uninstall' );


/**
 * Register admin hooks.
 */
function emindy_core_register_admin() {
        \EMINDY\Core\Admin::register();
}

/**
 * Load translations and Polylang meta copy rules.
 */
function emindy_core_plugins_loaded() {
        load_plugin_textdomain( 'emindy-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Polylang: copy structured meta into new translations so editors start
        // with the same steps/chapters/timing values.
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
function emindy_core_polylang_copy_metas( array $metas ) {
        $extra = [
                'em_steps_json',
                'em_chapters_json',
                'em_total_seconds',
                'em_prep_seconds',
                'em_perform_seconds',
                'em_supplies',
                'em_tools',
                'em_yield',
        ];

        return array_values( array_unique( array_merge( $metas, $extra ) ) );
}

/**
 * Register custom post types and taxonomies.
 */
function emindy_core_register_content_types() {
        \EMINDY\Core\CPT::register_all();
        \EMINDY\Core\Taxonomy::register_all();
}

/**
 * Register public shortcodes.
 */
function emindy_core_register_shortcodes() {
        \EMINDY\Core\Shortcodes::register_all();
        \EMINDY\Core\Shortcodes_Deprecated::register_all();
}

/**
 * Register content injection hooks.
 */
function emindy_core_register_content_inject() {
        \EMINDY\Core\Content_Inject::register();
}

/**
 * Register post meta.
 */
function emindy_core_register_meta() {
        \EMINDY\Core\Meta::register();
}

/**
 * Enqueue public assets and localized data.
 */
function emindy_core_enqueue_assets() {
        // CSS used across multiple components.
        wp_enqueue_style( 'emindy-core', EMINDY_CORE_URL . 'assets/css/emindy-core.css', [], EMINDY_CORE_VERSION );

        $post          = get_post();
        $content       = $post ? (string) $post->post_content : '';
        $has_shortcode = static function ( string $shortcode ) use ( $content ) : bool {
                return $content && has_shortcode( $content, $shortcode );
        };

        $is_assessments_page    = is_page( 'assessments' );
        $needs_assessment_forms = $is_assessments_page || $has_shortcode( 'em_phq9' ) || $has_shortcode( 'em_gad7' ) || $has_shortcode( 'em_assessment_result' );
        $needs_player_assets    = is_singular( 'em_exercise' ) || $has_shortcode( 'em_player' ) || $has_shortcode( 'em_exercise_steps' );
        $needs_video_analytics  = is_singular( 'em_video' ) || $has_shortcode( 'em_video_chapters' ) || $has_shortcode( 'em_transcript' ) || $has_shortcode( 'em_video_player' );
        $needs_assess_core      = $needs_assessment_forms || $needs_player_assets || $needs_video_analytics;

        // Core data for assessments (must load BEFORE phq9/gad7/player/video analytics).
        if ( $needs_assess_core ) {
                wp_register_script( 'emindy-assess-core', EMINDY_CORE_URL . 'assets/js/assess-core.js', [], EMINDY_CORE_VERSION, true );
                wp_localize_script(
                        'emindy-assess-core',
                        'emindyAssess',
                        [
                                'ajax'        => admin_url( 'admin-ajax.php' ),
                                'nonce'       => wp_create_nonce( 'emindy_assess' ),
                                'results_url' => \EMINDY\Core\assessment_result_base_url(),
                        ]
                );
                wp_enqueue_script( 'emindy-assess-core' );
        }

        if ( $needs_player_assets ) {
                wp_enqueue_style( 'emindy-player', EMINDY_CORE_URL . 'assets/css/player.css', [], EMINDY_CORE_VERSION );
                wp_enqueue_script( 'emindy-player', EMINDY_CORE_URL . 'assets/js/player.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
        }

        if ( $needs_assessment_forms ) {
                wp_enqueue_script( 'emindy-phq9', EMINDY_CORE_URL . 'assets/js/phq9.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
                wp_enqueue_script( 'emindy-gad7', EMINDY_CORE_URL . 'assets/js/gad7.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
        }

        if ( $needs_video_analytics ) {
                wp_enqueue_script( 'emindy-video-analytics', EMINDY_CORE_URL . 'assets/js/video-analytics.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
        }
}


/**
 * Output fallback JSON-LD when Rank Math is unavailable.
 */
function emindy_core_output_schema_fallback() {
        // Skip if the Rank Math plugin is loaded (class or helper function).
        if ( class_exists( '\RankMath\Plugin' ) || function_exists( 'rank_math' ) ) {
                return;
	}

        \EMINDY\Core\Schema::output_jsonld();
}

/**
 * Expose the current post ID to front-end scripts.
 */
function emindy_core_enqueue_post_id() {
	if ( ! is_singular() ) {
		return;
	}

	if ( ! wp_script_is( 'emindy-assess-core', 'enqueued' ) && ! wp_script_is( 'emindy-assess-core', 'registered' ) ) {
		return;
	}

	$post_id = absint( get_queried_object_id() );

	wp_add_inline_script( 'emindy-assess-core', 'window.em_post_id=' . wp_json_encode( $post_id ) . ';', 'before' );
}

/**
 * Handle redirects for common missing slugs.
 */
function emindy_core_template_redirect_fallbacks() {
        if ( ! is_404() ) {
                return;
	}

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
        $req         = wp_parse_url( $request_uri, PHP_URL_PATH );
        $req         = is_string( $req ) ? trim( sanitize_text_field( $req ), '/' ) : '';

        // /library -> redirect to Articles Hub if library page is missing.
        if ( 'library' === $req && ! get_page_by_path( 'library' ) ) {
                wp_safe_redirect( home_url( '/articles/' ), 301 );
                exit;
	}

        // /blog -> if blog page missing, redirect to latest posts (home for posts).
        if ( 'blog' === $req && ! get_page_by_path( 'blog' ) ) {
                // If home template exists for posts, send there; else root.
                wp_safe_redirect( home_url( '/' ), 302 );
                exit;
	}
}

/*
 * Output fallback JSON‑LD for single posts when the Rank Math SEO plugin is
 * not active.  Rank Math provides its own schema generator which our
 * `includes/schema.php` file extends to add VideoObject and HowTo details.
 * Emitting our own JSON‑LD in addition to Rank Math’s would result in
 * duplicate structured data, so this callback checks for the Rank Math
 * classes/functions and only runs when necessary.
 */

// قبلاً این افزونه یک فیلتر ساده برای اضافه کردن VideoObject/HowTo/Article
// به خروجی Schema Rank Math داشت. این فیلتر اکنون حذف شده است زیرا فایل
// includes/schema.php یک نسخهٔ کامل‌تر و غنی از داده‌های ساخت‌یافته تولید
// می‌کند که از تکرار و تناقض جلوگیری می‌کند. اگر به هر دلیلی نیاز به
// بازگرداندن این فیلتر ساده داشتید می‌توانید آن را مجدداً تعریف کنید، اما
// توصیه می‌شود از نسخهٔ پیشرفته استفاده شود.

add_action( 'init', 'emindy_core_register_admin' );
add_action( 'plugins_loaded', 'emindy_core_plugins_loaded' );
add_action( 'init', 'emindy_core_register_content_types' );
add_action( 'init', 'emindy_core_register_shortcodes', 9 );
add_action( 'init', 'emindy_core_register_content_inject' );
add_action( 'init', 'emindy_core_register_meta' );
add_action( 'wp_enqueue_scripts', 'emindy_core_enqueue_assets', 20 );
add_action( 'wp_head', 'emindy_core_output_schema_fallback', 99 );
add_action( 'wp_enqueue_scripts', 'emindy_core_enqueue_post_id', 30 );
add_action( 'template_redirect', 'emindy_core_template_redirect_fallbacks' );
