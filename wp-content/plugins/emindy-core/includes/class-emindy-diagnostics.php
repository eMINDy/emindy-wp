<?php
/**
 * Diagnostic helpers to surface staging issues in debug logs.
 *
 * @package EmindyCore
 */

declare( strict_types=1 );

namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic helpers to surface staging issues in debug logs.
 *
 * Runs lightweight checks in staging when WP_DEBUG is enabled, to catch
 * missing classes or activation tables before they cause fatal errors
 * in production.
 */
final class Diagnostics {

	/**
	 * Log prefix used for all diagnostics messages.
	 *
	 * @var string
	 */
	private const LOG_PREFIX = '[eMINDy Core] ';

	/**
	 * Hook priority for the init callback.
	 *
	 * @var int
	 */
	private const INIT_PRIORITY = 5;

	/**
	 * Hook debug checks.
	 *
	 * Intended to be called from the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'init', [ __CLASS__, 'run_staging_checks' ], self::INIT_PRIORITY );
	}

	/**
	 * Run staging-only diagnostics when WP_DEBUG is enabled.
	 *
	 * Logs missing class definitions and custom tables that should be
	 * created by the activation hook. This helps catch fatals before they
	 * reach production.
	 *
	 * @return void
	 */
	public static function run_staging_checks(): void {
		if ( ! self::should_log() ) {
			return;
		}

		self::verify_classes();
		self::verify_tables();
	}

	/**
	 * Confirm all required classes are loaded.
	 *
	 * @return void
	 */
	private static function verify_classes(): void {
		$classes = [
			'\\EMINDY\\Core\\CPT',
			'\\EMINDY\\Core\\Taxonomy',
			'\\EMINDY\\Core\\Shortcodes',
			'\\EMINDY\\Core\\Content_Inject',
			'\\EMINDY\\Core\\Meta',
			'\\EMINDY\\Core\\Schema',
			'\\EMINDY\\Core\\Admin',
			'\\EMINDY\\Core\\Ajax',
			'\\EMINDY\\Core\\Analytics',
		];

		/**
		 * Filters the list of classes that diagnostics checks for.
		 *
		 * @param string[] $classes Fully-qualified class names.
		 */
		$classes = (array) apply_filters( 'emindy_diagnostics_required_classes', $classes );

		$missing = array_values(
			array_filter(
				$classes,
				static function ( string $class ): bool {
					return '' !== $class && ! class_exists( $class );
				}
			)
		);

		if ( ! empty( $missing ) ) {
			self::log( 'Missing required classes: ' . implode( ', ', $missing ) );
		}
	}

	/**
	 * Confirm activation-created tables exist.
	 *
	 * @return void
	 */
	private static function verify_tables(): void {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return;
		}

		$tables = [
			$wpdb->prefix . 'emindy_newsletter',
			$wpdb->prefix . 'emindy_analytics',
		];

		/**
		 * Filters the list of custom tables that diagnostics checks for.
		 *
		 * @param string[] $tables Table names including prefix.
		 */
		$tables = (array) apply_filters( 'emindy_diagnostics_required_tables', $tables );

		$missing = [];

		foreach ( $tables as $table ) {
			$table = (string) $table;

			if ( '' === $table ) {
				continue;
			}

			// We expect an exact match for the table name.
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$table
				)
			);

			if ( $table !== $exists ) {
				$missing[] = $table;
			}
		}

		if ( ! empty( $missing ) ) {
			self::log(
				'Missing custom tables: ' . implode( ', ', $missing ) . '. Reactivate the plugin to rerun the activation hook and recreate them.'
			);
		}
	}

	/**
	 * Whether diagnostics should be logged.
	 *
	 * @return bool
	 */
	private static function should_log(): bool {
		$environment = function_exists( 'wp_get_environment_type' )
			? wp_get_environment_type()
			: 'production';

		$debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;

		// Default: only log on staging with WP_DEBUG enabled.
		$should_log = ( 'staging' === $environment ) && $debug_enabled;

		/**
		 * Filters whether diagnostics should log in the current request.
		 *
		 * @param bool   $should_log  Calculated default value.
		 * @param string $environment Current environment type.
		 */
		return (bool) apply_filters( 'emindy_diagnostics_should_log', $should_log, $environment );
	}

	/**
	 * Write a message to the error log.
	 *
	 * @param string $message Message to log.
	 *
	 * @return void
	 */
	private static function log( string $message ): void {
		$message = trim( $message );

		if ( '' === $message ) {
			return;
		}

		error_log( self::LOG_PREFIX . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
