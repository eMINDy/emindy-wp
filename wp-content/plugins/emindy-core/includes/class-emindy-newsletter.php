<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter handling for eMINDy — minimal local collector with upgrade hooks.
 *
 * - Provides a local subscribers table.
 * - Renders a small, accessible subscription form.
 * - Exposes hooks for ESP integrations and email customization.
 *
 * @package EmindyCore
 */
class Newsletter {

	/**
	 * Admin-post action key.
	 *
	 * @var string
	 */
	const ACTION = 'emindy_newsletter_subscribe';

	/**
	 * Nonce action key.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'emindy_newsletter_subscribe';

	/**
	 * Newsletter table suffix.
	 *
	 * @var string
	 */
	const TABLE_SUFFIX = 'emindy_newsletter';

	/**
	 * Query flag key for success/error feedback.
	 *
	 * @var string
	 */
	const QUERY_STATUS_KEY = 'success';

	/**
	 * Register runtime hooks.
	 *
	 * This should be called from the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'init', [ __CLASS__, 'register_shortcode' ] );

		add_action( 'admin_post_' . self::ACTION, [ __CLASS__, 'handle' ] );
		add_action( 'admin_post_nopriv_' . self::ACTION, [ __CLASS__, 'handle' ] );
	}

	/**
	 * Create the newsletter table in the database.
	 *
	 * Intended to be called from the plugin activation hook.
	 *
	 * @return void
	 */
	public static function install_table() {
		global $wpdb;

		$table           = static::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			email VARCHAR(190) NOT NULL,
			name VARCHAR(190) NULL,
			consent TINYINT(1) DEFAULT 0,
			ip VARCHAR(64) NULL,
			ua TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY email_unique (email)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Get the fully-qualified newsletter table name.
	 *
	 * @return string
	 */
	protected static function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_SUFFIX;
	}

	/**
	 * Register the [em_newsletter_form] shortcode.
	 *
	 * @return void
	 */
	public static function register_shortcode() {
		add_shortcode( 'em_newsletter_form', [ __CLASS__, 'render_shortcode' ] );
	}

	/**
	 * Shortcode callback wrapper.
	 *
	 * @param array       $atts    Shortcode attributes (unused for now).
	 * @param string|null $content Enclosed content (unused).
	 * @return string
	 */
	public static function render_shortcode( $atts = [], $content = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return static::render_form();
	}

	/**
	 * Retrieve newsletter submission status from the query string.
	 *
	 * @return string '1' on success, '0' on validation error, or '' if none.
	 */
	protected static function get_status_flag() {
		$key    = self::QUERY_STATUS_KEY;
		$status = '';

		if ( function_exists( 'filter_input' ) ) {
			$filtered = filter_input( INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS );
			if ( is_string( $filtered ) && '' !== $filtered ) {
				$status = $filtered;
			}
		}

		if ( '' === $status && isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$status = sanitize_text_field( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $status;
	}

	/**
	 * Render the newsletter form and any status message.
	 *
	 * Can be reused by other components (e.g. Shortcodes::newsletter()).
	 *
	 * @return string
	 */
	public static function render_form() {
		$status    = static::get_status_flag();
		$has_error = ( '0' === $status );

		$action = esc_url( admin_url( 'admin-post.php' ) );
		$nonce  = wp_create_nonce( self::NONCE_ACTION );

		ob_start();

		// Success notice (no form to avoid double submits).
		if ( '1' === $status ) {
			?>
			<div class="em-newsletter__notice em-newsletter__notice--success" role="status" aria-live="polite">
				<strong><?php esc_html_e( 'Thank you!', 'emindy-core' ); ?></strong>
				<?php esc_html_e( 'Please check your inbox.', 'emindy-core' ); ?>
			</div>
			<?php
			return ob_get_clean();
		}

		// Error notice.
		if ( $has_error ) {
			?>
			<div
				class="em-newsletter__notice em-newsletter__notice--error"
				role="status"
				aria-live="polite"
				id="em-nl-error"
			>
				<?php esc_html_e( 'Please enter a valid email address.', 'emindy-core' ); ?>
			</div>
			<?php
		}
		?>
		<form action="<?php echo $action; ?>" method="post" class="em-newsletter-form" novalidate>
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>" />
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>" />

			<?php
			// Honeypot field for basic bot protection.
			?>
			<div class="em-newsletter-form__hp" aria-hidden="true">
				<label class="screen-reader-text" for="em-nl-hp">
					<?php esc_html_e( 'Leave this field empty', 'emindy-core' ); ?>
				</label>
				<input
					id="em-nl-hp"
					type="text"
					name="em_nl_hp"
					value=""
					tabindex="-1"
					autocomplete="off"
				/>
			</div>

			<div class="em-newsletter-form__row">
				<div class="em-newsletter-form__field em-newsletter-form__field--email">
					<label class="em-newsletter-form__label" for="em-nl-email">
						<?php esc_html_e( 'Email', 'emindy-core' ); ?>
					</label>
					<input
						id="em-nl-email"
						name="email"
						type="email"
						required
						autocomplete="email"
						placeholder="<?php echo esc_attr__( 'you@example.com', 'emindy-core' ); ?>"
						class="em-newsletter-form__input em-newsletter-form__input--email"
						<?php if ( $has_error ) : ?>
							aria-invalid="true"
							aria-describedby="em-nl-error"
						<?php endif; ?>
					/>
				</div>

				<div class="em-newsletter-form__field em-newsletter-form__field--name">
					<label class="em-newsletter-form__label" for="em-nl-name">
						<?php esc_html_e( 'Name', 'emindy-core' ); ?>
						<span class="em-newsletter-form__label-optional">
							<?php esc_html_e( '(optional)', 'emindy-core' ); ?>
						</span>
					</label>
					<input
						id="em-nl-name"
						name="name"
						type="text"
						autocomplete="name"
						placeholder="<?php echo esc_attr__( 'Your name', 'emindy-core' ); ?>"
						class="em-newsletter-form__input em-newsletter-form__input--name"
					/>
				</div>

				<div class="em-newsletter-form__field em-newsletter-form__field--submit">
					<label class="screen-reader-text" for="em-nl-submit">
						<?php esc_html_e( 'Subscribe', 'emindy-core' ); ?>
					</label>
					<button
						id="em-nl-submit"
						type="submit"
						class="em-newsletter-form__submit"
					>
						<?php esc_html_e( 'Subscribe', 'emindy-core' ); ?>
					</button>
				</div>
			</div>

			<div class="em-newsletter-form__consent">
				<label class="em-newsletter-form__consent-label">
					<input
						type="checkbox"
						name="consent"
						value="1"
						required
						class="em-newsletter-form__consent-input"
					/>
					<span class="em-newsletter-form__consent-text">
						<?php esc_html_e( 'I agree to receive weekly email updates from eMINDy and understand I can unsubscribe anytime.', 'emindy-core' ); ?>
					</span>
				</label>
			</div>
		</form>
		<?php

		return ob_get_clean();
	}

	/**
	 * Handle newsletter submission and trigger integration hook.
	 *
	 * @return void
	 */
	public static function handle() {
		// Only handle POST requests.
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
		if ( 'POST' !== $method ) {
			wp_safe_redirect( static::get_redirect_url( false ) );
			exit;
		}

		// Nonce validation.
		$nonce = static::get_post_value( '_wpnonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Security check failed', 'emindy-core' ), '', [ 'response' => 403 ] );
		}

		// Honeypot: bots filling this field will be silently rejected.
		$honeypot = static::get_post_value( 'em_nl_hp' );
		if ( '' !== $honeypot ) {
			wp_safe_redirect( static::get_redirect_url( false ) );
			exit;
		}

		// Input values.
		$email_raw = static::get_post_value( 'email' );
		$name_raw  = static::get_post_value( 'name' );
		$consent   = static::get_post_value( 'consent' ) ? 1 : 0;

		$email = sanitize_email( $email_raw );
		$name  = sanitize_text_field( $name_raw );

		if ( ! is_email( $email ) ) {
			wp_safe_redirect( static::get_redirect_url( false ) );
			exit;
		}

		global $wpdb;

		$table      = static::get_table_name();
		$user_agent = '';

		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 1000 );
		}

		$ip = static::get_client_ip();

		// Insert or update subscriber row.
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is trusted prefix.
			$wpdb->prepare(
				"INSERT INTO {$table} (email, name, consent, ip, ua)
				VALUES (%s, %s, %d, %s, %s)
				ON DUPLICATE KEY UPDATE
					name    = VALUES(name),
					consent = VALUES(consent),
					ip      = VALUES(ip),
					ua      = VALUES(ua)",
				$email,
				$name,
				$consent,
				$ip,
				$user_agent
			)
		);

		// Admin notification (optional, filterable).
		$admin_email = get_option( 'admin_email' );
		$admin_email = apply_filters( 'emindy_newsletter_admin_email', $admin_email, $email, $name, $consent );

		if ( $admin_email ) {
			$admin_subject = __( 'New eMINDy Newsletter Subscriber', 'emindy-core' );
			/* translators: 1: subscriber email, 2: subscriber name, 3: consent flag (0/1). */
			$admin_message = sprintf(
				__( "Email: %1\$s\nName: %2\$s\nConsent: %3\$d", 'emindy-core' ),
				$email,
				$name,
				$consent
			);

			wp_mail( $admin_email, $admin_subject, $admin_message );
		}

		// Hook for external ESP integrations (MailerLite, ConvertKit, Brevo…).
		$payload = [
			'email'   => $email,
			'name'    => $name,
			'consent' => $consent,
			'ip'      => $ip,
		];

		/**
		 * Fires when a user subscribes to the eMINDy newsletter.
		 *
		 * Allows integrating with external ESPs or CRMs.
		 *
		 * @param array $payload Subscription data.
		 */
		do_action( 'emindy_newsletter_subscribed', $payload );

		// Optionally send welcome email (filterable).
		$send_welcome = apply_filters( 'emindy_newsletter_send_welcome', true, $payload );

		if ( $send_welcome ) {
			$subject = __( 'Welcome to the Calm Circle — eMINDy', 'emindy-core' );
			$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
			$body    = static::build_welcome_email_body( $name );

			/**
			 * Filter the welcome email arguments before sending.
			 *
			 * @param array $args {
			 *     Arguments for wp_mail().
			 *
			 *     @type string       $to      Recipient email address.
			 *     @type string       $subject Email subject.
			 *     @type string       $body    Email body (HTML).
			 *     @type string|array $headers Email headers.
			 * }
			 * @param array $payload Subscription payload.
			 */
			$args = apply_filters(
				'emindy_newsletter_welcome_email_args',
				[
					'to'      => $email,
					'subject' => $subject,
					'body'    => $body,
					'headers' => $headers,
				],
				$payload
			);

			$to      = isset( $args['to'] ) ? $args['to'] : '';
			$subject = isset( $args['subject'] ) ? $args['subject'] : '';
			$body    = isset( $args['body'] ) ? $args['body'] : '';
			$headers = isset( $args['headers'] ) ? $args['headers'] : [];

			if ( $to && $subject && $body ) {
				wp_mail( $to, $subject, $body, $headers );
			}
		}

		// Redirect after success.
		wp_safe_redirect( static::get_redirect_url( true ) );
		exit;
	}

	/**
	 * Build the HTML welcome email body.
	 *
	 * @param string $name Subscriber name.
	 * @return string
	 */
	protected static function build_welcome_email_body( $name ) {
		$body  = '<p>' . esc_html__( 'Hi', 'emindy-core' );
		if ( $name ) {
			$body .= ' ' . esc_html( $name );
		}
		$body .= ',</p>';
		$body .= '<p>' . esc_html__( "Welcome to the Calm Circle. Every week, you'll receive a short calm practice and mindful tip.", 'emindy-core' ) . '</p>';
		$body .= '<p>';
		$body .= sprintf(
			wp_kses(
				__( 'Want to begin now? Try a 60-second reset: <a href="%s">Start here</a>.', 'emindy-core' ),
				[
					'a' => [
						'href' => [],
					],
				]
			),
			esc_url( home_url( '/em_video/1-minute-mindful-break/' ) )
		);
		$body .= '</p>';
		$body .= '<p>' . esc_html__( 'With care,', 'emindy-core' ) . '<br>eMINDy</p>';

		return $body;
	}

	/**
	 * Retrieve best-guess client IP.
	 *
	 * @return string
	 */
	protected static function get_client_ip() {
		$keys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
		];

		foreach ( $keys as $key ) {
			if ( empty( $_SERVER[ $key ] ) ) {
				continue;
			}

			$raw      = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			$ip_parts = explode( ',', $raw );
			$ip       = trim( $ip_parts[0] );

			$is_valid = false;

			if ( function_exists( 'filter_var' ) ) {
				$is_valid = (bool) filter_var( $ip, FILTER_VALIDATE_IP );
			} else {
				// Basic IPv4 validation: four octets 0-255 separated by dots.
				$is_valid = (bool) preg_match( '/^(?:\d{1,3}\.){3}\d{1,3}$/', $ip );
			}

			if ( $is_valid ) {
				return $ip;
			}
		}

		return '';
	}

	/**
	 * Get a raw POST value with fallbacks and minimal safety.
	 *
	 * @param string $key POST key.
	 * @return string
	 */
	protected static function get_post_value( $key ) {
		$value = null;

		if ( function_exists( 'filter_input' ) ) {
			$value = filter_input( INPUT_POST, $key, FILTER_UNSAFE_RAW );
		}

		if ( null === $value && isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( null === $value ) {
			return '';
		}

		if ( is_array( $value ) ) {
			return '';
		}

		return (string) $value;
	}

	/**
	 * Build the redirect URL after success or failure.
	 *
	 * @param bool $success Whether the operation succeeded.
	 * @return string
	 */
	protected static function get_redirect_url( $success ) {
		$default = home_url( '/newsletter/' );
		$referer = wp_get_referer();

		$base = $referer ? $referer : $default;

		/**
		 * Filter the base redirect URL for newsletter submissions.
		 *
		 * @param string $base_url Base URL for redirection.
		 * @param bool   $success  Whether the operation succeeded.
		 */
		$base = apply_filters( 'emindy_newsletter_redirect_base', $base, $success );

		$value = $success ? '1' : '0';

		$url = add_query_arg( self::QUERY_STATUS_KEY, $value, $base );

		/**
		 * Filter the final redirect URL after newsletter submission.
		 *
		 * @param string $url     Redirect URL.
		 * @param bool   $success Whether the operation succeeded.
		 */
		$filter = $success ? 'emindy_newsletter_redirect_success' : 'emindy_newsletter_redirect_failure';

		return apply_filters( $filter, $url, $success );
	}
}
