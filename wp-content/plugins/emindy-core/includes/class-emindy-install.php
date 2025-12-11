<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles installation, upgrades, and cleanup for the eMINDy Core plugin.
 *
 * This class is intended to be wired from the main plugin file:
 *
 * register_activation_hook( __FILE__, [ '\EMINDY\Core\Install', 'activate' ] );
 * register_deactivation_hook( __FILE__, [ '\EMINDY\Core\Install', 'deactivate' ] );
 * register_uninstall_hook( __FILE__, [ '\EMINDY\Core\Install', 'uninstall' ] );
 *
 * @package EmindyCore
 */
class Install {

	/**
	 * Option key storing the current schema / install version.
	 *
	 * @var string
	 */
	const VERSION_OPTION = 'emindy_core_version';

	/**
	 * Current schema / install version.
	 *
	 * Bump this when changing table structures or install routines.
	 *
	 * @var string
	 */
	const DB_VERSION = '0.5.0';

	/**
	 * Plugin activation callback.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 * @return void
	 */
	public static function activate( $network_wide = false ) {
		$network_wide = (bool) $network_wide;

		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);

			foreach ( $site_ids as $site_id ) {
				switch_to_blog( (int) $site_id );
				self::single_site_activate();
				restore_current_blog();
			}
		} else {
			self::single_site_activate();
		}
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * Only flushes rewrite rules; it does not drop any data.
	 *
	 * @param bool $network_wide Whether the plugin is being deactivated network-wide.
	 * @return void
	 */
	public static function deactivate( $network_wide = false ) {
		$network_wide = (bool) $network_wide;

		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);

			foreach ( $site_ids as $site_id ) {
				switch_to_blog( (int) $site_id );
				self::ensure_rewrite_rules();
				restore_current_blog();
			}
		} else {
			self::ensure_rewrite_rules();
		}
	}

	/**
	 * Uninstall callback.
	 *
	 * This is called via register_uninstall_hook() from the main plugin file.
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			return;
		}

		if ( is_multisite() ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);

			foreach ( $site_ids as $site_id ) {
				switch_to_blog( (int) $site_id );
				self::single_site_uninstall();
				restore_current_blog();
			}
		} else {
			self::single_site_uninstall();
		}
	}

	/**
	 * Run upgrade routines when needed.
	 *
	 * Intended to be called on plugins_loaded for the current site:
	 * Install::maybe_upgrade();
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$stored_version = get_option( self::VERSION_OPTION );

		if ( ! $stored_version ) {
			// Fresh install will be handled on activation.
			return;
		}

		if ( version_compare( $stored_version, self::DB_VERSION, '>=' ) ) {
			return;
		}

		// Run schema updates.
		self::create_or_update_tables();

		// Ensure CPTs/taxonomies and default terms exist, and refresh rewrites.
		self::maybe_register_objects();
		self::seed_default_terms();
		self::ensure_rewrite_rules();

		// Future version-specific migrations can be added here, e.g.:
		// if ( version_compare( $stored_version, '0.4.0', '<' ) ) { ... }

		update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Run activation logic for a single site.
	 *
	 * @return void
	 */
	protected static function single_site_activate() {
		self::create_or_update_tables();
		self::maybe_register_objects();
		self::seed_default_terms();
		self::ensure_rewrite_rules();

		update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Run uninstall logic for a single site.
	 *
	 * @return void
	 */
	protected static function single_site_uninstall() {
		global $wpdb;

		// Allow developers to preserve data on uninstall.
		$preserve_tables = (bool) apply_filters( 'emindy_core_preserve_tables_on_uninstall', false );

		/**
		 * Fires before eMINDy Core data is removed on uninstall.
		 *
		 * @param bool $preserve_tables Whether custom tables are being preserved.
		 */
		do_action( 'emindy_core_before_uninstall', $preserve_tables );

		if ( ! $preserve_tables ) {
			$tables = array(
				$wpdb->prefix . 'emindy_newsletter',
				$wpdb->prefix . 'emindy_analytics',
			);

			foreach ( $tables as $table_name ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
			}
		}

		delete_option( self::VERSION_OPTION );

		/**
		 * Fires after eMINDy Core data has been removed on uninstall.
		 *
		 * @param bool $preserve_tables Whether custom tables were preserved.
		 */
		do_action( 'emindy_core_after_uninstall', $preserve_tables );
	}

	/**
	 * Create or update custom database tables via dbDelta().
	 *
	 * @return void
	 */
	protected static function create_or_update_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate  = $wpdb->get_charset_collate();
		$newsletter_table = $wpdb->prefix . 'emindy_newsletter';
		$analytics_table  = $wpdb->prefix . 'emindy_analytics';

		$sql_newsletter = "CREATE TABLE {$newsletter_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			name varchar(255) NOT NULL DEFAULT '',
			consent tinyint(1) unsigned NOT NULL DEFAULT 0,
			locale varchar(32) NOT NULL DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) {$charset_collate};";

		$sql_analytics = "CREATE TABLE {$analytics_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_type varchar(64) NOT NULL,
			event_label varchar(255) NOT NULL DEFAULT '',
			event_value varchar(255) NOT NULL DEFAULT '',
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			user_ip varchar(45) NOT NULL DEFAULT '',
			user_agent varchar(255) NOT NULL DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_type (event_type),
			KEY post_id (post_id)
		) {$charset_collate};";

		/**
		 * Filter the CREATE TABLE statements used for eMINDy Core.
		 *
		 * This allows extensions to adjust or add table definitions that
		 * will be passed through dbDelta().
		 *
		 * @param array  $schemas         Associative array of table identifier => SQL.
		 * @param string $charset_collate The charset/collation string for the current DB.
		 */
		$schemas = (array) apply_filters(
			'emindy_core_table_schema',
			array(
				'emindy_newsletter' => $sql_newsletter,
				'emindy_analytics'  => $sql_analytics,
			),
			$charset_collate
		);

		foreach ( $schemas as $sql ) {
			if ( ! is_string( $sql ) || '' === trim( $sql ) ) {
				continue;
			}

			dbDelta( $sql );
		}
	}

	/**
	 * Ensure CPTs and taxonomies are registered before using them.
	 *
	 * Safe to call multiple times.
	 *
	 * @return void
	 */
	protected static function maybe_register_objects() {
		// Register custom post types.
		if ( class_exists( __NAMESPACE__ . '\\CPT' ) && method_exists( __NAMESPACE__ . '\\CPT', 'register' ) ) {
			CPT::register();
		}

		// Register taxonomies.
		if ( class_exists( __NAMESPACE__ . '\\Taxonomy' ) && method_exists( __NAMESPACE__ . '\\Taxonomy', 'register' ) ) {
			Taxonomy::register();
		}
	}

	/**
	 * Seed default taxonomy terms for the platform.
	 *
	 * This only inserts a term if it does not already exist.
	 *
	 * @return void
	 */
	protected static function seed_default_terms() {
		$taxonomies = self::get_default_terms_map();

		foreach ( $taxonomies as $taxonomy => $terms ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			foreach ( $terms as $slug => $label ) {
				$existing = term_exists( $slug, $taxonomy );

				if ( $existing && ! is_wp_error( $existing ) ) {
					continue;
				}

				wp_insert_term(
					$label,
					$taxonomy,
					array(
						'slug' => $slug,
					)
				);
			}
		}
	}

	/**
	 * Map of taxonomy => [ slug => label ] default terms.
	 *
	 * @return array<string, array<string, string>>
	 */
	protected static function get_default_terms_map() {
		$defaults = array(
			// Broad topics.
			'topic'        => array(
				'stress-relief'          => __( 'Stress Relief', 'emindy-core' ),
				'sleep-and-rest'         => __( 'Sleep & Rest', 'emindy-core' ),
				'confidence-and-growth'  => __( 'Confidence & Growth', 'emindy-core' ),
				'focus-and-productivity' => __( 'Focus & Productivity', 'emindy-core' ),
				'anxiety-support'        => __( 'Anxiety Support', 'emindy-core' ),
			),
			// Techniques / methods.
			'technique'    => array(
				'breathing'     => __( 'Breathing', 'emindy-core' ),
				'body-scan'     => __( 'Body Scan', 'emindy-core' ),
				'journaling'    => __( 'Journaling', 'emindy-core' ),
				'visualization' => __( 'Visualization', 'emindy-core' ),
				'mindfulness'   => __( 'Mindfulness', 'emindy-core' ),
			),
			// Duration ranges.
			'duration'     => array(
				'30s'      => __( '30 seconds', 'emindy-core' ),
				'1m'       => __( '1 minute', 'emindy-core' ),
				'2-5m'     => __( '2–5 minutes', 'emindy-core' ),
				'10m-plus' => __( '10+ minutes', 'emindy-core' ),
			),
			// Content format / type.
			'format'       => array(
				'video'     => __( 'Video', 'emindy-core' ),
				'article'   => __( 'Article', 'emindy-core' ),
				'audio'     => __( 'Audio', 'emindy-core' ),
				'exercise'  => __( 'Exercise', 'emindy-core' ),
				'worksheet' => __( 'Worksheet', 'emindy-core' ),
			),
			// Situational usage.
			'use_case'     => array(
				'morning'       => __( 'Morning', 'emindy-core' ),
				'bedtime'       => __( 'Bedtime', 'emindy-core' ),
				'work-break'    => __( 'Work Break', 'emindy-core' ),
				'study-focus'   => __( 'Study Focus', 'emindy-core' ),
				'anytime-reset' => __( 'Anytime Reset', 'emindy-core' ),
			),
			// Difficulty / depth.
			'level'        => array(
				'beginner'     => __( 'Beginner', 'emindy-core' ),
				'intermediate' => __( 'Intermediate', 'emindy-core' ),
				'deep'         => __( 'Deep', 'emindy-core' ),
			),
			// Accessibility features.
			'a11y_feature' => array(
				'captions'          => __( 'Captions', 'emindy-core' ),
				'transcript'        => __( 'Transcript available', 'emindy-core' ),
				'no-music'          => __( 'No music version', 'emindy-core' ),
				'high-contrast'     => __( 'High contrast', 'emindy-core' ),
				'screen-reader-opt' => __( 'Screen reader optimised', 'emindy-core' ),
			),
		);

		/**
		 * Filter the default taxonomy terms seeded on activation.
		 *
		 * @param array $defaults Default taxonomy => terms map.
		 */
		return (array) apply_filters( 'emindy_core_default_terms', $defaults );
	}

	/**
	 * Flush rewrite rules for the current site.
	 *
	 * @return void
	 */
	protected static function ensure_rewrite_rules() {
		// Make sure CPTs and taxonomies are registered before flushing.
		self::maybe_register_objects();

		flush_rewrite_rules();
	}
}
