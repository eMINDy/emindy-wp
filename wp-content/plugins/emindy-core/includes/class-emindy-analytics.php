<?php
/**
 * Lightweight analytics/event logging for the eMINDy platform.
 *
 * @package EmindyCore
 */

declare( strict_types=1 );

namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AJAX-based analytics tracking and custom table creation.
 *
 * This is intentionally minimal and focused on event logging for
 * exercises, videos and assessments. It does not provide a UI layer.
 */
class Analytics {

	/**
	 * Shared nonce action used for tracking requests.
	 *
	 * Keep this in sync with the front-end scripts.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'emindy_assess';

	/**
	 * Custom table name suffix (without the WP prefix).
	 *
	 * @var string
	 */
	private const TABLE_SUFFIX = 'emindy_analytics';

	/**
	 * Maximum length for stored IP addresses.
	 *
	 * @var int
	 */
	private const IP_MAX_LENGTH = 100;

	/**
	 * Maximum length for stored user agent strings.
	 *
	 * @var int
	 */
	private const UA_MAX_LENGTH = 255;

	/**
	 * Maximum length for the "type" column.
	 *
	 * @var int
	 */
	private const TYPE_MAX_LENGTH = 60;

	/**
	 * Register AJAX handlers.
	 *
	 * Intended to be called from the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_emindy_track', [ __CLASS__, 'track' ] );
		add_action( 'wp_ajax_nopriv_emindy_track', [ __CLASS__, 'track' ] );
	}

	/**
	 * Return the fully-qualified analytics table name.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string
	 */
	public static function table_name(): string {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_SUFFIX;
	}

	/**
	 * Whether analytics tracking is enabled.
	 *
	 * @return bool
	 */
	protected static function is_enabled(): bool {
		/**
		 * Filter whether analytics tracking is enabled.
		 *
		 * Returning false here will short-circuit all tracking.
		 *
		 * @param bool $enabled Default true.
		 */
		return (bool) apply_filters( 'emindy_analytics_enabled', true );
	}

	/**
	 * Create or update the analytics table on plugin activation.
	 *
	 * The table stores event logs with:
	 * - id (auto increment)
	 * - time (datetime, GMT)
	 * - type (event type)
	 * - label (short label/slug)
	 * - value (optional payload / JSON string)
	 * - post_id (related content ID)
	 * - ip (visitor IP address, anonymised via filter)
	 * - ua (user agent, truncated)
	 *
	 * @return void
	 */
	public static function install_table(): void {
		global $wpdb;

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL auto_increment,
			time datetime NOT NULL,
			type varchar(60) NOT NULL,
			label text NOT NULL,
			value text NOT NULL,
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			ip varchar(100) NOT NULL,
			ua text NOT NULL,
			PRIMARY KEY  (id),
			KEY type (type),
			KEY post_id (post_id),
			KEY time (time)
		) {$charset_collate};";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		dbDelta( $sql );
	}

	/**
	 * Drop the analytics table on uninstall (optional helper).
	 *
	 * Can be called from the main uninstall routine if desired.
	 *
	 * @return void
	 */
	public static function uninstall_table(): void {
		global $wpdb;

		$table = self::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}

	/**
	 * Ensure the analytics table exists.
	 *
	 * This is a defensive check in case activation did not run.
	 *
	 * @return void
	 */
	protected static function ensure_table_exists(): void {
		global $wpdb;

		$table = self::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $existing !== $table ) {
			self::install_table();
		}
	}

	/**
	 * Handle AJAX tracking events.
	 *
	 * Reads POST parameters and inserts a row in the analytics table.
	 * Expected POST fields:
	 * - type  (required, event type key)
	 * - label (optional, short label)
	 * - value (optional, payload / JSON string)
	 * - post  (optional, related post ID)
	 *
	 * Uses a shared nonce for security and returns a JSON response.
	 *
	 * @return void
	 */
	public static function track(): void {
		if ( ! wp_doing_ajax() ) {
			wp_send_json_error( 'not_ajax' );
		}

		if ( ! self::is_enabled() ) {
			wp_send_json_error( 'disabled' );
		}

		check_ajax_referer( self::NONCE_ACTION );

		$type_raw  = isset( $_POST['type'] ) ? (string) wp_unslash( $_POST['type'] ) : '';
		$label_raw = isset( $_POST['label'] ) ? (string) wp_unslash( $_POST['label'] ) : '';
		$value_raw = isset( $_POST['value'] ) ? (string) wp_unslash( $_POST['value'] ) : '';
		$post_raw  = isset( $_POST['post'] ) ? (string) wp_unslash( $_POST['post'] ) : '';

		$type = self::normalize_type( $type_raw );
		if ( '' === $type ) {
			wp_send_json_error( 'missing_type' );
		}

		$label   = self::normalize_label( $label_raw );
		$value   = self::normalize_value( $value_raw );
		$post_id = absint( $post_raw );

		self::ensure_table_exists();

		global $wpdb;

		$table = self::table_name();

		$ip = self::get_client_ip();
		$ua = self::get_user_agent();

		$data = [
			'time'    => current_time( 'mysql', true ),
			'type'    => $type,
			'label'   => $label,
			'value'   => $value,
			'post_id' => $post_id,
			'ip'      => $ip,
			'ua'      => $ua,
		];

		$formats = [ '%s', '%s', '%s', '%s', '%d', '%s', '%s' ];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->insert( $table, $data, $formats );

		if ( false === $inserted ) {
			wp_send_json_error( 'db_error' );
		}

		/**
		 * Fires after an analytics event has been successfully recorded.
		 *
		 * @param string $type    Event type.
		 * @param string $label   Event label.
		 * @param string $value   Event value/payload.
		 * @param int    $post_id Related post ID.
		 * @param array  $data    Full data array inserted into the DB.
		 */
		do_action( 'emindy_analytics_tracked', $type, $label, $value, $post_id, $data );

		wp_send_json_success( true );
	}

	/**
	 * Normalise and bound the event type string.
	 *
	 * @param string $type Raw type string.
	 *
	 * @return string
	 */
	protected static function normalize_type( string $type ): string {
		$type = sanitize_key( $type );

		if ( '' === $type ) {
			return '';
		}

		return substr( $type, 0, self::TYPE_MAX_LENGTH );
	}

	/**
	 * Normalise the label field.
	 *
	 * @param string $label Raw label.
	 *
	 * @return string
	 */
	protected static function normalize_label( string $label ): string {
		return sanitize_text_field( $label );
	}

	/**
	 * Normalise the value field (potential JSON/payload).
	 *
	 * @param string $value Raw value.
	 *
	 * @return string
	 */
	protected static function normalize_value( string $value ): string {
		return sanitize_textarea_field( $value );
	}

	/**
	 * Get anonymised and filtered client IP address.
	 *
	 * @return string
	 */
	protected static function get_client_ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

		if ( ! is_string( $ip ) ) {
			$ip = '';
		}

		$ip = sanitize_text_field( wp_unslash( $ip ) );

		if ( function_exists( 'wp_privacy_anonymize_ip' ) && '' !== $ip ) {
			$ip = (string) wp_privacy_anonymize_ip( $ip );
		}

		$ip = substr( $ip, 0, self::IP_MAX_LENGTH );

		/**
		 * Filter the IP stored in analytics logs.
		 *
		 * @param string $ip Anonymised IP string.
		 */
		$ip = (string) apply_filters( 'emindy_analytics_ip', $ip );

		return substr( $ip, 0, self::IP_MAX_LENGTH );
	}

	/**
	 * Get filtered and truncated user agent string.
	 *
	 * @return string
	 */
	protected static function get_user_agent(): string {
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

		if ( ! is_string( $ua ) ) {
			$ua = '';
		}

		$ua = sanitize_text_field( wp_unslash( $ua ) );
		$ua = substr( $ua, 0, self::UA_MAX_LENGTH );

		/**
		 * Filter the user agent stored in analytics logs.
		 *
		 * @param string $ua User agent string.
		 */
		$ua = (string) apply_filters( 'emindy_analytics_ua', $ua );

		return substr( $ua, 0, self::UA_MAX_LENGTH );
	}
}
