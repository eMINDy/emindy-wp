<?php
/**
 * AJAX endpoints for the eMINDy Core plugin.
 *
 * @package EmindyCore
 */

declare( strict_types=1 );

namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AJAX requests for assessments and related utilities.
 */
class Ajax {

	/**
	 * Nonce action used for all eMINDy AJAX endpoints.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'emindy_assess';

	/**
	 * Maximum number of assessment emails allowed per IP per hour.
	 *
	 * @var int
	 */
	private const RATE_LIMIT_MAX_PER_HOUR = 5;

	/**
	 * Transient key prefix for rate limiting.
	 *
	 * @var string
	 */
	private const RATE_LIMIT_TRANSIENT_PREFIX = 'emindy_rate_';

	/**
	 * Default rate-limit window in seconds (1 hour).
	 *
	 * @var int
	 */
	private const RATE_LIMIT_EXPIRATION = HOUR_IN_SECONDS;

	/**
	 * Supported assessment types for signed result URLs.
	 *
	 * @var string[]
	 */
	private const SUPPORTED_TYPES = [ 'phq9', 'gad7' ];

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_emindy_send_assessment', [ __CLASS__, 'send_assessment' ] );
		add_action( 'wp_ajax_nopriv_emindy_send_assessment', [ __CLASS__, 'send_assessment' ] );

		add_action( 'wp_ajax_emindy_sign_result', [ __CLASS__, 'sign_result' ] );
		add_action( 'wp_ajax_nopriv_emindy_sign_result', [ __CLASS__, 'sign_result' ] );
	}

	/**
	 * Sign assessment result URLs for verification.
	 *
	 * Expects `type` and `score` in POST and returns a signed URL that can be used
	 * to safely display the corresponding result page.
	 *
	 * @return void Outputs JSON and exits.
	 */
	public static function sign_result(): void {
		self::verify_nonce();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$type_raw = isset( $_POST['type'] ) ? wp_unslash( (string) $_POST['type'] ) : '';
		$type     = sanitize_key( $type_raw );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$score_raw = isset( $_POST['score'] ) ? wp_unslash( $_POST['score'] ) : '';
		$score     = is_numeric( $score_raw ) ? (int) $score_raw : -1;

		$supported_types = apply_filters( 'emindy_assessment_supported_types', self::SUPPORTED_TYPES );

		if ( ! in_array( $type, $supported_types, true ) || $score < 0 ) {
			wp_send_json_error( __( 'Invalid request', 'emindy-core' ) );
		}

		$url = self::build_signed_result_url( $type, $score );

		$response = [
			'url' => $url,
		];

		/**
		 * Filter the successful response of the sign_result endpoint.
		 *
		 * @param array  $response Response data.
		 * @param string $type     Assessment type.
		 * @param int    $score    Assessment score.
		 */
		$response = apply_filters( 'emindy_assessment_sign_result_response', $response, $type, $score );

		wp_send_json_success( $response );
	}

	/**
	 * Send a summary of assessment results via email.
	 *
	 * Expects `email`, `summary`, and `kind` fields in POST.
	 * Includes basic per-IP rate limiting (5 emails/hour by default).
	 *
	 * @return void Outputs JSON and exits.
	 */
	public static function send_assessment(): void {
		self::verify_nonce();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$email_raw = isset( $_POST['email'] ) ? wp_unslash( (string) $_POST['email'] ) : '';
		$email     = sanitize_email( $email_raw );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$summary_raw = isset( $_POST['summary'] ) ? wp_unslash( (string) $_POST['summary'] ) : '';
		$summary     = trim( wp_strip_all_tags( $summary_raw ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$kind_raw = isset( $_POST['kind'] ) ? wp_unslash( (string) $_POST['kind'] ) : '';
		$kind     = sanitize_key( $kind_raw );

		if ( ! is_email( $email ) ) {
			wp_send_json_error( __( 'Invalid email', 'emindy-core' ) );
		}

		if ( '' === $summary || '' === $kind ) {
			wp_send_json_error( __( 'Missing fields', 'emindy-core' ) );
		}

		if ( self::is_rate_limited() ) {
			wp_send_json_error( __( 'Too many requests, try later', 'emindy-core' ) );
		}

		/**
		 * Allow filtering or normalising the assessment kind.
		 *
		 * @param string $kind   Normalised kind.
		 * @param string $raw    Original kind string.
		 * @param string $email  Recipient email.
		 * @param string $summary Assessment summary.
		 */
		$kind = apply_filters( 'emindy_assessment_kind', $kind, $kind_raw, $email, $summary );

		$recipient = apply_filters( 'emindy_assessment_email_recipient', $email, $kind, $summary );

		if ( ! is_email( $recipient ) ) {
			wp_send_json_error( __( 'Invalid email', 'emindy-core' ) );
		}

		$subject = sprintf(
			/* translators: %s: assessment kind (e.g. PHQ9, GAD7). */
			__( 'Your %s summary', 'emindy-core' ),
			strtoupper( $kind )
		);

		/**
		 * Filter the email subject used for sending assessment summaries.
		 *
		 * @param string $subject  Email subject.
		 * @param string $kind     Assessment kind.
		 * @param string $email    Original email address.
		 * @param string $summary  Assessment summary.
		 */
		$subject = apply_filters( 'emindy_assessment_email_subject', $subject, $kind, $email, $summary );

		$body_parts = [
			$summary,
			home_url( '/' ),
		];

		$body = implode( "\n\n", array_filter( array_map( 'trim', $body_parts ) ) );

		/**
		 * Filter the email body used for sending assessment summaries.
		 *
		 * @param string $body     Email body.
		 * @param string $kind     Assessment kind.
		 * @param string $email    Original email address.
		 * @param string $summary  Assessment summary.
		 */
		$body = apply_filters( 'emindy_assessment_email_body', $body, $kind, $email, $summary );

		/**
		 * Filter additional email headers.
		 *
		 * @param array  $headers  Email headers.
		 * @param string $kind     Assessment kind.
		 * @param string $email    Original email address.
		 * @param string $summary  Assessment summary.
		 */
		$headers = (array) apply_filters( 'emindy_assessment_email_headers', [], $kind, $email, $summary );

		$sent = wp_mail( $recipient, $subject, $body, $headers );

		if ( $sent ) {
			wp_send_json_success( true );
		}

		wp_send_json_error( __( 'Send failed', 'emindy-core' ) );
	}

	/**
	 * Verify the shared AJAX nonce.
	 *
	 * @return void
	 */
	private static function verify_nonce(): void {
		check_ajax_referer( self::NONCE_ACTION );
	}

	/**
	 * Build a signed assessment result URL for a given type and score.
	 *
	 * @param string $type  Assessment type (e.g. phq9, gad7).
	 * @param int    $score Assessment score.
	 *
	 * @return string Signed result URL.
	 */
	private static function build_signed_result_url( string $type, int $score ): string {
		$secret  = wp_salt( 'auth' );
		$payload = $type . '|' . $score;
		$sig     = hash_hmac( 'sha256', $payload, $secret );

		$base_url = function_exists( __NAMESPACE__ . '\\assessment_result_base_url' )
			? assessment_result_base_url()
			: home_url( '/' );

		$url = add_query_arg(
			[
				'type'  => $type,
				'score' => $score,
				'sig'   => $sig,
			],
			$base_url
		);

		/**
		 * Filter the signed assessment result URL.
		 *
		 * @param string $url   Signed result URL.
		 * @param string $type  Assessment type.
		 * @param int    $score Assessment score.
		 * @param string $sig   Signature.
		 */
		return apply_filters( 'emindy_assessment_result_url', $url, $type, $score, $sig );
	}

	/**
	 * Determine whether the current request is rate-limited for assessment emails.
	 *
	 * @return bool True if limited, false otherwise.
	 */
	private static function is_rate_limited(): bool {
		$ip = self::get_client_ip();

		/**
		 * Filter the maximum number of emails allowed per IP per hour.
		 *
		 * @param int $max_per_hour Default limit.
		 */
		$max_per_hour = (int) apply_filters(
			'emindy_assessment_rate_limit_max_per_hour',
			self::RATE_LIMIT_MAX_PER_HOUR
		);

		/**
		 * Filter the rate-limit window (in seconds).
		 *
		 * @param int $expiration Default expiration.
		 */
		$expiration = (int) apply_filters(
			'emindy_assessment_rate_limit_expiration',
			self::RATE_LIMIT_EXPIRATION
		);

		if ( $max_per_hour <= 0 || $expiration <= 0 ) {
			return false;
		}

		$key = self::RATE_LIMIT_TRANSIENT_PREFIX . md5( $ip );
		$cnt = (int) get_transient( $key );

		if ( $cnt >= $max_per_hour ) {
			return true;
		}

		set_transient( $key, $cnt + 1, $expiration );

		return false;
	}

	/**
	 * Get an anonymised client IP address for rate limiting.
	 *
	 * @return string IP address string.
	 */
	private static function get_client_ip(): string {
		$ip = '';

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw_ip = wp_unslash( (string) $_SERVER['REMOTE_ADDR'] );
			$ip     = sanitize_text_field( $raw_ip );
		}

		if ( function_exists( 'wp_privacy_anonymize_ip' ) && '' !== $ip ) {
			$ip = (string) wp_privacy_anonymize_ip( $ip );
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$ip = '0.0.0.0';
		}

		/**
		 * Filter the client IP used for rate limiting.
		 *
		 * @param string $ip IP address.
		 */
		return (string) apply_filters( 'emindy_assessment_client_ip', $ip );
	}
}
