<?php
/**
 * Plugin Name: eMINDy Core
 * Description: Core CPTs, taxonomies, shortcodes, schema, and content injectors for eMINDy.
 * Version: 0.1.0
 * Author: eMINDy
 * Text Domain: emindy-core
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'EMINDY_CORE_VERSION', '0.1.0' );
define( 'EMINDY_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'EMINDY_CORE_URL',  plugin_dir_url( __FILE__ ) );

require_once EMINDY_CORE_PATH . 'includes/helpers.php';
require_once EMINDY_CORE_PATH . 'includes/schema.php';
require_once EMINDY_CORE_PATH . 'includes/newsletter.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-cpt.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-taxonomy.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-shortcodes.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-content-inject.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-meta.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-schema.php';
require_once EMINDY_CORE_PATH . 'includes/class-emindy-admin.php';
require_once EMINDY_CORE_PATH.'includes/class-emindy-ajax.php';
\EMINDY\Core\Ajax::register();
require_once EMINDY_CORE_PATH.'includes/class-emindy-analytics.php';
\EMINDY\Core\Analytics::register();

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
function emindy_core_activate(){
  if ( ! function_exists( 'emindy_newsletter_install_table' ) ) {
    require_once EMINDY_CORE_PATH . 'includes/newsletter.php';
  }
  emindy_newsletter_install_table();

  // Create analytics table for tracking events
  if ( ! class_exists( '\\EMINDY\\Core\\Analytics' ) ) {
    require_once EMINDY_CORE_PATH . 'includes/class-emindy-analytics.php';
  }
  \EMINDY\Core\Analytics::install_table();

  // Flush rewrite rules on plugin activation to ensure custom post type slugs and archives are registered properly.
  flush_rewrite_rules();
}

/**
 * Uninstall the plugin: drop the newsletter table.
 *
 * If you prefer to keep subscriber data when uninstalling the plugin,
 * comment out or remove the DROP TABLE query below.
 */
function emindy_core_uninstall(){
  global $wpdb;
  $table = $wpdb->prefix . 'emindy_newsletter';
  $wpdb->query( "DROP TABLE IF EXISTS $table" );
}

register_activation_hook( __FILE__, 'emindy_core_activate' );
register_uninstall_hook( __FILE__, 'emindy_core_uninstall' );


add_action('init', function(){ \EMINDY\Core\Admin::register(); });

add_action( 'plugins_loaded', function () {
        load_plugin_textdomain( 'emindy-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Polylang: copy structured meta into new translations so editors start
        // with the same steps/chapters/timing values.
        if ( defined( 'POLYLANG_VERSION' ) || function_exists( 'pll_the_languages' ) ) {
                add_filter( 'pll_copy_post_metas', function( array $metas ) {
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
                } );
        }
} );

add_action( 'init', function () {
	\EMINDY\Core\CPT::register_all();
	\EMINDY\Core\Taxonomy::register_all();
} );

add_action('init', function(){
  \EMINDY\Core\Shortcodes::register_all();
}, 9);

add_action( 'init', function () {
	\EMINDY\Core\Content_Inject::register();
} );

add_action( 'init', function () {
	\EMINDY\Core\Meta::register();
} );

add_action( 'wp_enqueue_scripts', function () {
	// CSS
	wp_enqueue_style( 'emindy-core', EMINDY_CORE_URL . 'assets/css/emindy-core.css', [], EMINDY_CORE_VERSION );
	wp_enqueue_style( 'emindy-player', EMINDY_CORE_URL . 'assets/css/player.css', [], EMINDY_CORE_VERSION );
	// Core data for assessments (must load BEFORE phq9/gad7)
        wp_register_script( 'emindy-assess-core', EMINDY_CORE_URL . 'assets/js/assess-core.js', [], EMINDY_CORE_VERSION, true );
        wp_localize_script( 'emindy-assess-core', 'emindyAssess', [
        'ajax'        => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('emindy_assess'),
        'results_url' => \EMINDY\Core\assessment_result_base_url(),
    ] );
        wp_enqueue_script( 'emindy-assess-core' );
	// Player
	wp_enqueue_script( 'emindy-player', EMINDY_CORE_URL . 'assets/js/player.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
	// Assessments
	wp_enqueue_script( 'emindy-phq9', EMINDY_CORE_URL . 'assets/js/phq9.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
	wp_enqueue_script( 'emindy-gad7', EMINDY_CORE_URL . 'assets/js/gad7.js', [ 'emindy-assess-core' ], EMINDY_CORE_VERSION, true );
	wp_enqueue_script('emindy-video-analytics', EMINDY_CORE_URL.'assets/js/video-analytics.js', ['emindy-assess-core'], EMINDY_CORE_VERSION, true);
	
}, 20 );

/*
 * Output fallback JSON‑LD for single posts when the Rank Math SEO plugin is
 * not active.  Rank Math provides its own schema generator which our
 * `includes/schema.php` file extends to add VideoObject and HowTo details.
 * Emitting our own JSON‑LD in addition to Rank Math’s would result in
 * duplicate structured data, so this callback checks for the Rank Math
 * classes/functions and only runs when necessary.
 */
add_action( 'wp_head', function () {
    // Skip if the Rank Math plugin is loaded (class or helper function).
    if ( class_exists( '\\RankMath\\Plugin' ) || function_exists( 'rank_math' ) ) {
        return;
    }
    \EMINDY\Core\Schema::output_jsonld();
}, 99 );

add_action('wp_enqueue_scripts', function(){
  if (is_singular()){
    global $post;
    wp_add_inline_script('emindy-assess-core', 'window.em_post_id='. (int)($post->ID ?? 0) .';', 'before');
  }
}, 30);

// قبلاً این افزونه یک فیلتر ساده برای اضافه کردن VideoObject/HowTo/Article
// به خروجی Schema Rank Math داشت. این فیلتر اکنون حذف شده است زیرا فایل
// includes/schema.php یک نسخهٔ کامل‌تر و غنی از داده‌های ساخت‌یافته تولید
// می‌کند که از تکرار و تناقض جلوگیری می‌کند. اگر به هر دلیلی نیاز به
// بازگرداندن این فیلتر ساده داشتید می‌توانید آن را مجدداً تعریف کنید، اما
// توصیه می‌شود از نسخهٔ پیشرفته استفاده شود.

// Optional: smart redirects when certain key pages are missing
add_action('template_redirect', function(){
  if( ! is_404() ) return;

  $req = trim( parse_url( add_query_arg([]), PHP_URL_PATH ), '/' );

  // /library -> redirect to Articles Hub if library page is missing
  if( $req === 'library' && ! get_page_by_path('library') ){
    wp_safe_redirect( home_url('/articles/') , 301 ); exit;
  }

  // /blog -> if blog page missing, redirect to latest posts (home for posts)
  if( $req === 'blog' && ! get_page_by_path('blog') ){
    // if home template exists for posts, send there; else root.
    wp_safe_redirect( home_url('/') , 302 ); exit;
  }
});
