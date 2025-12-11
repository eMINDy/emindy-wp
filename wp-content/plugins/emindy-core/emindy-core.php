<?php
/**
 * Plugin Name:       eMINDy Core
 * Description:       Core CPTs, taxonomies, shortcodes, schema, content injectors, newsletter, and lightweight analytics for eMINDy.
 * Version:           0.5.0
 * Author:            eMINDy
 * Text Domain:       emindy-core
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * -------------------------------------------------------------------------
 * Constants
 * -------------------------------------------------------------------------
 */

if ( ! defined( 'EMINDY_CORE_VERSION' ) ) {
	define( 'EMINDY_CORE_VERSION', '0.5.0' );
}

if ( ! defined( 'EMINDY_CORE_FILE' ) ) {
	define( 'EMINDY_CORE_FILE', __FILE__ );
}

if ( ! defined( 'EMINDY_CORE_BASENAME' ) ) {
	define( 'EMINDY_CORE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'EMINDY_CORE_PATH' ) ) {
	define( 'EMINDY_CORE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EMINDY_CORE_URL' ) ) {
	define( 'EMINDY_CORE_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'EMINDY_CORE_MIN_PHP' ) ) {
	define( 'EMINDY_CORE_MIN_PHP', '7.4' );
}

if ( ! defined( 'EMINDY_CORE_MIN_WP' ) ) {
	define( 'EMINDY_CORE_MIN_WP', '6.0' );
}

/**
 * -------------------------------------------------------------------------
 * Compatibility
 * -------------------------------------------------------------------------
 */

/**
 * Whether requirements are met.
 *
 * @return bool
 */
function emindy_core_requirements_met(): bool {
	$php_ok = version_compare( PHP_VERSION, EMINDY_CORE_MIN_PHP, '>=' );

	$wp_version = isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : '0.0';
	$wp_ok      = version_compare( $wp_version, EMINDY_CORE_MIN_WP, '>=' );

	return ( $php_ok && $wp_ok );
}

/**
 * Render admin notice when requirements are not met.
 *
 * @return void
 */
function emindy_core_requirements_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$php_ok = version_compare( PHP_VERSION, EMINDY_CORE_MIN_PHP, '>=' );

	$wp_version = isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : '0.0';
	$wp_ok      = version_compare( $wp_version, EMINDY_CORE_MIN_WP, '>=' );

	$lines = [];

	if ( ! $php_ok ) {
		$lines[] = sprintf(
			'PHP %1$s+ is required. Your server is running PHP %2$s.',
			esc_html( EMINDY_CORE_MIN_PHP ),
			esc_html( PHP_VERSION )
		);
	}

	if ( ! $wp_ok ) {
		$lines[] = sprintf(
			'WordPress %1$s+ is required. Your site is running WordPress %2$s.',
			esc_html( EMINDY_CORE_MIN_WP ),
			esc_html( $wp_version )
		);
	}

	if ( empty( $lines ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p><strong>eMINDy Core</strong> is inactive.</p><ul style="margin:0.5em 0 0.5em 1.2em;list-style:disc;">';
	foreach ( $lines as $line ) {
		echo '<li>' . esc_html( $line ) . '</li>';
	}
	echo '</ul></div>';
}

/**
 * Deactivate plugin on incompatible environments (admin only).
 *
 * @return void
 */
function emindy_core_maybe_deactivate_incompatible(): void {
	if ( emindy_core_requirements_met() ) {
		return;
	}

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Only attempt to deactivate in wp-admin where deactivate_plugins is available.
	if ( is_admin() ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( EMINDY_CORE_BASENAME );
		}

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

/**
 * -------------------------------------------------------------------------
 * Internal helpers
 * -------------------------------------------------------------------------
 */

/**
 * Whether we are in a debug-friendly environment.
 *
 * @return bool
 */
function emindy_core_is_debug(): bool {
	return ( defined( 'WP_DEBUG' ) && WP_DEBUG );
}

/**
 * Debug log helper (no-op unless WP_DEBUG is enabled).
 *
 * @param string $message Message to log.
 * @return void
 */
function emindy_core_debug_log( string $message ): void {
	if ( ! emindy_core_is_debug() ) {
		return;
	}

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( '[eMINDy Core] ' . $message );
}

/**
 * Require a file safely (logs when missing in debug environments).
 *
 * @param string $relative Relative path from plugin root.
 * @return void
 */
function emindy_core_require( string $relative ): void {
	$relative = ltrim( $relative, '/' );
	$path     = trailingslashit( EMINDY_CORE_PATH ) . $relative;

	if ( file_exists( $path ) ) {
		require_once $path;
		return;
	}

	emindy_core_debug_log( 'Missing required file: ' . $relative );
}

/**
 * Call a static method if it exists.
 *
 * @param string $class  Fully-qualified class name.
 * @param string $method Static method name.
 * @param array  $args   Arguments.
 * @return void
 */
function emindy_core_call( string $class, string $method, array $args = [] ): void {
	if ( ! class_exists( $class ) ) {
		return;
	}
	if ( ! is_callable( [ $class, $method ] ) ) {
		return;
	}

	call_user_func_array( [ $class, $method ], $args );
}

/**
 * -------------------------------------------------------------------------
 * Dependency loading
 * -------------------------------------------------------------------------
 */

function emindy_core_load_dependencies(): void {
	// Shared helpers.
	emindy_core_require( 'includes/helpers.php' );

	// Content model.
	emindy_core_require( 'includes/class-emindy-cpt.php' );
	emindy_core_require( 'includes/class-emindy-taxonomy.php' );
	emindy_core_require( 'includes/class-emindy-meta.php' );

	// Front-end & UX.
	emindy_core_require( 'includes/class-emindy-shortcodes.php' );
	emindy_core_require( 'includes/class-emindy-content-inject.php' );

	// Assessments, newsletter, analytics.
	emindy_core_require( 'includes/class-emindy-ajax.php' );
	emindy_core_require( 'includes/class-emindy-newsletter.php' );
	emindy_core_require( 'includes/class-emindy-analytics.php' );

	// SEO/schema.
	emindy_core_require( 'includes/class-emindy-schema.php' );

	// Admin & diagnostics.
	emindy_core_require( 'includes/class-emindy-admin.php' );
	emindy_core_require( 'includes/class-emindy-diagnostics.php' );
}

/**
 * -------------------------------------------------------------------------
 * Lifecycle
 * -------------------------------------------------------------------------
 */

function emindy_core_activate(): void {
	if ( ! emindy_core_requirements_met() ) {
		$message = sprintf(
			/* translators: 1: min WP version, 2: min PHP version */
			'eMINDy Core requires WordPress %1$s+ and PHP %2$s+.',
			EMINDY_CORE_MIN_WP,
			EMINDY_CORE_MIN_PHP
		);
		wp_die(
			esc_html( $message ),
			esc_html__( 'Plugin activation failed', 'emindy-core' ),
			[ 'back_link' => true ]
		);
	}

	emindy_core_load_dependencies();

	// Ensure CPTs/taxonomies exist before flushing rewrite rules.
	emindy_core_call( '\EMINDY\Core\CPT', 'register_all' );
	emindy_core_call( '\EMINDY\Core\Taxonomy', 'register_all' );

	// Create tables / run module activation hooks (if implemented).
	emindy_core_call( '\EMINDY\Core\Analytics', 'activate' );
	emindy_core_call( '\EMINDY\Core\Analytics', 'maybe_create_table' );
	emindy_core_call( '\EMINDY\Core\Newsletter', 'activate' );
	emindy_core_call( '\EMINDY\Core\Newsletter', 'maybe_create_table' );

	flush_rewrite_rules();

	update_option( 'emindy_core_version', EMINDY_CORE_VERSION, true );
}

function emindy_core_deactivate(): void {
	// Intentionally minimal: do not delete data on deactivation.
	flush_rewrite_rules();
}

function emindy_core_uninstall(): void {
	/**
	 * Fires before uninstall cleanup runs.
	 */
	do_action( 'emindy_core_before_uninstall' );

	if ( ! emindy_core_requirements_met() ) {
		// If environment is incompatible, avoid loading modules; do best-effort cleanup only.
		$delete_data = (bool) apply_filters( 'emindy_core_uninstall_delete_data', false );
		if ( $delete_data ) {
			global $wpdb;

			if ( isset( $wpdb ) && $wpdb instanceof wpdb ) {
				$tables = [
					$wpdb->prefix . 'emindy_analytics',
					$wpdb->prefix . 'emindy_newsletter',
				];

				foreach ( $tables as $table ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
				}
			}

			delete_option( 'emindy_core_version' );
		}

		/**
		 * Fires after uninstall cleanup runs.
		 */
		do_action( 'emindy_core_after_uninstall' );
		return;
	}

	emindy_core_load_dependencies();

	$delete_data = (bool) apply_filters( 'emindy_core_uninstall_delete_data', false );
	if ( $delete_data ) {
		// Prefer module-defined uninstall routines if available.
		emindy_core_call( '\EMINDY\Core\Analytics', 'uninstall' );
		emindy_core_call( '\EMINDY\Core\Newsletter', 'uninstall' );

		// Fallback: drop known tables (best-effort).
		global $wpdb;

		if ( isset( $wpdb ) && $wpdb instanceof wpdb ) {
			$tables = [
				$wpdb->prefix . 'emindy_analytics',
				$wpdb->prefix . 'emindy_newsletter',
			];

			foreach ( $tables as $table ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
			}
		}

		delete_option( 'emindy_core_version' );
	}

	/**
	 * Fires after uninstall cleanup runs.
	 */
	do_action( 'emindy_core_after_uninstall' );
}

register_activation_hook( __FILE__, 'emindy_core_activate' );
register_deactivation_hook( __FILE__, 'emindy_core_deactivate' );
register_uninstall_hook( __FILE__, 'emindy_core_uninstall' );

/**
 * -------------------------------------------------------------------------
 * i18n
 * -------------------------------------------------------------------------
 */

function emindy_core_load_textdomain(): void {
	load_plugin_textdomain(
		'emindy-core',
		false,
		dirname( EMINDY_CORE_BASENAME ) . '/languages'
	);
}

/**
 * -------------------------------------------------------------------------
 * Polylang: copy structured meta across translations
 * -------------------------------------------------------------------------
 */

/**
 * @param array $metas Meta keys Polylang should copy.
 * @return array
 */
function emindy_core_pll_copy_post_metas( array $metas ): array {
	$must_copy = [
		// Core structured JSON fields.
		'em_steps_json',
		'em_chapters_json',

		// HowTo/video duration fields (commonly used by UI/schema).
		'em_total_seconds',
		'em_prep_seconds',
		'em_perform_seconds',

		// HowTo supplies/tools/yield (schema/UI).
		'em_supplies',
		'em_tools',
		'em_yield',
	];

	$must_copy = (array) apply_filters( 'emindy_core_pll_meta_keys', $must_copy );

	foreach ( $must_copy as $key ) {
		if ( is_string( $key ) && '' !== $key && ! in_array( $key, $metas, true ) ) {
			$metas[] = $key;
		}
	}

	return $metas;
}

/**
 * -------------------------------------------------------------------------
 * 404 helpers (optional)
 * -------------------------------------------------------------------------
 */

function emindy_core_maybe_redirect_common_404s(): void {
	if ( ! is_404() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
	$path        = trim( $path, '/' );

	if ( '' === $path ) {
		return;
	}

	$default = [
		// If /blog/ is missing as a page, send to the Article archive (if it exists).
		'blog'    => function (): string {
			$link = get_post_type_archive_link( 'em_article' );
			return is_string( $link ) ? $link : '';
		},
		// If /library/ is missing as a page, send to the Videos archive (filter this to your preferred hub).
		'library' => function (): string {
			$link = get_post_type_archive_link( 'em_video' );
			return is_string( $link ) ? $link : '';
		},
	];

	$redirects = (array) apply_filters( 'emindy_core_404_redirects', $default );

	if ( ! array_key_exists( $path, $redirects ) ) {
		return;
	}

	$target = '';
	if ( is_string( $redirects[ $path ] ) ) {
		$target = $redirects[ $path ];
	} elseif ( is_callable( $redirects[ $path ] ) ) {
		$target = (string) call_user_func( $redirects[ $path ] );
	}

	$target = esc_url_raw( $target );
	if ( '' === $target ) {
		return;
	}

	nocache_headers();
	wp_safe_redirect( $target, 301 );
	exit;
}

/**
 * -------------------------------------------------------------------------
 * Public assets
 * -------------------------------------------------------------------------
 */

function emindy_core_register_public_assets(): void {
	$ver = EMINDY_CORE_VERSION;

	// Styles.
	wp_register_style( 'emindy-core', EMINDY_CORE_URL . 'assets/css/emindy-core.css', [], $ver );
	wp_register_style( 'emindy-core-fonts', EMINDY_CORE_URL . 'assets/fonts/fonts.css', [], $ver );
	wp_register_style( 'emindy-player', EMINDY_CORE_URL . 'assets/css/player.css', [ 'emindy-core' ], $ver );
	wp_register_style( 'emindy-assessments', EMINDY_CORE_URL . 'assets/css/assessments.css', [ 'emindy-core' ], $ver );

	// Scripts.
	wp_register_script( 'emindy-assess-core', EMINDY_CORE_URL . 'assets/js/assess-core.js', [], $ver, true );
	wp_register_script( 'emindy-phq9', EMINDY_CORE_URL . 'assets/js/phq9.js', [ 'emindy-assess-core' ], $ver, true );
	wp_register_script( 'emindy-gad7', EMINDY_CORE_URL . 'assets/js/gad7.js', [ 'emindy-assess-core' ], $ver, true );
	wp_register_script( 'emindy-player', EMINDY_CORE_URL . 'assets/js/player.js', [], $ver, true );
	wp_register_script( 'emindy-video-analytics', EMINDY_CORE_URL . 'assets/js/video-analytics.js', [], $ver, true );
}

/**
 * @param string $content    Post content.
 * @param array  $shortcodes Shortcode tags.
 * @return bool
 */
function emindy_core_content_has_shortcode_any( string $content, array $shortcodes ): bool {
	if ( '' === $content ) {
		return false;
	}
	if ( ! function_exists( 'has_shortcode' ) ) {
		return false;
	}

	foreach ( $shortcodes as $tag ) {
		if ( is_string( $tag ) && '' !== $tag && has_shortcode( $content, $tag ) ) {
			return true;
		}
	}

	return false;
}

function emindy_core_enqueue_public_assets(): void {
	wp_enqueue_style( 'emindy-core' );

	$post    = get_post();
	$content = ( $post && isset( $post->post_content ) ) ? (string) $post->post_content : '';

	$is_exercise = is_singular( 'em_exercise' );
	$is_video    = is_singular( 'em_video' );

	$needs_assessments = is_page( 'assessments' ) || emindy_core_content_has_shortcode_any(
		$content,
		[ 'em_phq9', 'em_gad7', 'em_assessment_result' ]
	);

	$needs_player = $is_exercise || emindy_core_content_has_shortcode_any(
		$content,
		[ 'em_player', 'em_exercise_steps' ]
	);

	$needs_chapters_or_video_tracking = $is_video || emindy_core_content_has_shortcode_any(
		$content,
		[ 'em_video_chapters' ]
	);

	$needs_fonts = ( $needs_assessments || $needs_player || $needs_chapters_or_video_tracking )
		&& (bool) apply_filters( 'emindy_core_enqueue_fonts', true );

	if ( $needs_fonts ) {
		wp_enqueue_style( 'emindy-core-fonts' );
	}

	if ( $needs_player ) {
		wp_enqueue_style( 'emindy-player' );
		wp_enqueue_script( 'emindy-player' );
	}

	if ( $needs_assessments ) {
		wp_enqueue_style( 'emindy-assessments' );
		wp_enqueue_script( 'emindy-assess-core' );

		// Load specific assessment UIs only if needed.
		if ( is_page( 'assessments' ) || emindy_core_content_has_shortcode_any( $content, [ 'em_phq9' ] ) ) {
			wp_enqueue_script( 'emindy-phq9' );
		}
		if ( is_page( 'assessments' ) || emindy_core_content_has_shortcode_any( $content, [ 'em_gad7' ] ) ) {
			wp_enqueue_script( 'emindy-gad7' );
		}

		$nonce_action = (string) apply_filters( 'emindy_core_ajax_nonce_action', 'emindy_assess' );

		$payload = [
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonceAction' => $nonce_action,
			'nonce'       => wp_create_nonce( $nonce_action ),
			'homeUrl'     => home_url( '/' ),
			'locale'      => get_locale(),
			'debug'       => emindy_core_is_debug(),
			'version'     => EMINDY_CORE_VERSION,
		];

		$inline = 'window.EMINDY_CORE = window.EMINDY_CORE || ' . wp_json_encode( $payload ) . ';';
		wp_add_inline_script( 'emindy-assess-core', $inline, 'before' );
	}

	if ( $needs_chapters_or_video_tracking ) {
		wp_enqueue_script( 'emindy-video-analytics' );
	}
}

/**
 * -------------------------------------------------------------------------
 * Schema fallback hook (optional)
 * -------------------------------------------------------------------------
 */

function emindy_core_schema_fallback_output(): void {
	if ( class_exists( '\EMINDY\Core\Schema' ) && is_callable( [ '\EMINDY\Core\Schema', 'maybe_output_fallback' ] ) ) {
		\EMINDY\Core\Schema::maybe_output_fallback();
	}
}

/**
 * -------------------------------------------------------------------------
 * Upgrade/migrations (lightweight)
 * -------------------------------------------------------------------------
 */

function emindy_core_maybe_upgrade(): void {
	$stored = (string) get_option( 'emindy_core_version', '' );
	if ( $stored === EMINDY_CORE_VERSION ) {
		return;
	}

	// Best-effort: allow modules to run migrations.
	emindy_core_call( '\EMINDY\Core\Analytics', 'maybe_create_table' );
	emindy_core_call( '\EMINDY\Core\Newsletter', 'maybe_create_table' );

	update_option( 'emindy_core_version', EMINDY_CORE_VERSION, true );
}

/**
 * -------------------------------------------------------------------------
 * Bootstrap
 * -------------------------------------------------------------------------
 */

function emindy_core_boot(): void {
	emindy_core_load_dependencies();

	// i18n.
	add_action( 'plugins_loaded', 'emindy_core_load_textdomain', 5 );

	// Upgrade checks.
	add_action( 'plugins_loaded', 'emindy_core_maybe_upgrade', 8 );

	// Polylang meta copy (safe even if Polylang is not active).
	add_filter( 'pll_copy_post_metas', 'emindy_core_pll_copy_post_metas' );

	// CPTs / taxonomies / meta.
	emindy_core_call( '\EMINDY\Core\CPT', 'register_all' );
	emindy_core_call( '\EMINDY\Core\Taxonomy', 'register_all' );
	emindy_core_call( '\EMINDY\Core\Meta', 'register' );

	// Shortcodes & front-end behavior.
	emindy_core_call( '\EMINDY\Core\Shortcodes', 'register_all' );
	emindy_core_call( '\EMINDY\Core\Content_Inject', 'register' );

	// Assessments/newsletter/analytics endpoints.
	emindy_core_call( '\EMINDY\Core\Ajax', 'register' );
	emindy_core_call( '\EMINDY\Core\Newsletter', 'register' );
	emindy_core_call( '\EMINDY\Core\Analytics', 'register' );

	// Schema integration.
	emindy_core_call( '\EMINDY\Core\Schema', 'register' );
	add_action( 'wp_head', 'emindy_core_schema_fallback_output', 1 );

	// Admin + diagnostics.
	emindy_core_call( '\EMINDY\Core\Admin', 'register' );
	emindy_core_call( '\EMINDY\Core\Diagnostics', 'register' );

	// Assets.
	add_action( 'wp_enqueue_scripts', 'emindy_core_register_public_assets', 5 );
	add_action( 'wp_enqueue_scripts', 'emindy_core_enqueue_public_assets', 20 );

	// Optional 404 redirects for common hubs.
	add_action( 'template_redirect', 'emindy_core_maybe_redirect_common_404s', 1 );
}

if ( emindy_core_requirements_met() ) {
	emindy_core_boot();
} else {
	add_action( 'admin_notices', 'emindy_core_requirements_notice' );
	add_action( 'admin_init', 'emindy_core_maybe_deactivate_incompatible' );
}
