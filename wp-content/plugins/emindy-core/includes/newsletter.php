<?php
/**
 * eMINDy Newsletter — minimal local collector with upgrade hooks.
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Create newsletter table on activation (called from plugin activation hook).
 *
 * @return void
 */
function emindy_newsletter_install_table() {
        global $wpdb;

        $table           = $wpdb->prefix . 'emindy_newsletter';
        $charset_collate = $wpdb->get_charset_collate();
        $sql             = "CREATE TABLE IF NOT EXISTS $table (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    name VARCHAR(190) NULL,
    consent TINYINT(1) DEFAULT 0,
    ip VARCHAR(64) NULL,
    ua TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email_unique (email)
  ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
}
// Removed the after_switch_theme hook. The table creation is now handled
// via register_activation_hook in the main plugin file. This prevents
// duplicate calls on every theme switch and follows WordPress best
// practices for installing custom tables【331235010806737†L68-L70】.

// 1) Shortcode: [em_newsletter_form]
add_shortcode(
        'em_newsletter_form',
        function () {
                $success = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_SPECIAL_CHARS );
                if ( '1' === $success ) {
                        return '<div class="em-success" role="status" aria-live="polite" style="background:#e9f7ef;border-radius:12px;padding:12px;margin-bottom:8px"><strong>' . esc_html__( 'Thank you!', 'emindy-core' ) . '</strong> ' . esc_html__( 'Please check your inbox.', 'emindy-core' ) . '</div>';
                }

                $action = esc_url( admin_url( 'admin-post.php' ) );
                $nonce  = wp_create_nonce( 'emindy_newsletter_subscribe' );
                ob_start();
                ?>
  <form action="<?php echo $action; ?>" method="post" class="em-newsletter-form" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:end">
    <input type="hidden" name="action" value="em_newsletter_subscribe">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">

    <div style="flex:1;min-width:200px">
      <label for="em-nl-email" style="display:block;margin-bottom:4px"><?php esc_html_e( 'Email', 'emindy-core' ); ?></label>
      <input id="em-nl-email" name="email" type="email" required autocomplete="email" placeholder="<?php echo esc_attr__( 'you@example.com', 'emindy-core' ); ?>" style="width:100%;padding:.6rem .8rem;border-radius:.6rem;border:1px solid #cfd8dc">
    </div>
    <div style="flex:1;min-width:160px">
      <label for="em-nl-name" style="display:block;margin-bottom:4px"><?php esc_html_e( 'Name', 'emindy-core' ); ?> <span style="opacity:.6"><?php esc_html_e( '(optional)', 'emindy-core' ); ?></span></label>
      <input id="em-nl-name" name="name" type="text" autocomplete="name" placeholder="<?php echo esc_attr__( 'Your name', 'emindy-core' ); ?>" style="width:100%;padding:.6rem .8rem;border-radius:.6rem;border:1px solid #cfd8dc">
    </div>
    <div style="min-width:160px">
      <label for="em-nl-submit" class="sr-only"><?php esc_html_e( 'Subscribe', 'emindy-core' ); ?></label>
      <button id="em-nl-submit" type="submit" style="width:100%;padding:.7rem 1rem;border:0;border-radius:.7rem;background:#F4D483;color:#0A2A43;font-weight:700;cursor:pointer"><?php esc_html_e( 'Subscribe', 'emindy-core' ); ?></button>
    </div>

    <div style="width:100%;margin-top:.25rem">
      <label style="display:flex;gap:.5rem;align-items:flex-start">
        <input type="checkbox" name="consent" value="1" required>
        <span><?php esc_html_e( 'I agree to receive weekly email updates from eMINDy and understand I can unsubscribe anytime.', 'emindy-core' ); ?></span>
      </label>
    </div>
  </form>
                <?php
                return ob_get_clean();
        }
);

// 2) Handler: save + notify + hook for integrations
add_action( 'admin_post_nopriv_em_newsletter_subscribe', 'emindy_newsletter_handle' );
add_action( 'admin_post_em_newsletter_subscribe', 'emindy_newsletter_handle' );

/**
 * Handle newsletter submission and trigger integration hook.
 *
 * @return void
 */
function emindy_newsletter_handle() {
        $nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_SPECIAL_CHARS );

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'emindy_newsletter_subscribe' ) ) {
                wp_die( esc_html__( 'Security check failed', 'emindy-core' ), '', array( 'response' => 403 ) );
        }

        $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $consent = isset( $_POST['consent'] ) ? 1 : 0;

        if ( ! is_email( $email ) ) {
                $failure_target = add_query_arg( 'success', '0', wp_get_referer() ?: home_url( '/newsletter/' ) );
                wp_safe_redirect( $failure_target );
                exit;
        }

        global $wpdb;
        $table      = $wpdb->prefix . 'emindy_newsletter';
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 1000 ) : '';

        $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is trusted prefix.
                $wpdb->prepare(
                        "INSERT INTO $table (email, name, consent, ip, ua) VALUES (%s,%s,%d,%s,%s)
     ON DUPLICATE KEY UPDATE name=VALUES(name), consent=VALUES(consent)",
                        $email,
                        $name,
                        $consent,
                        emindy_client_ip(),
                        $user_agent
                )
        );

        // Admin notify (optional).
        $admin = get_option( 'admin_email' );
        if ( $admin ) {
                $admin_subject = __( 'New eMINDy Newsletter Subscriber', 'emindy-core' );
                /* translators: 1: subscriber email, 2: subscriber name, 3: consent flag. */
                $admin_message = sprintf( __( "Email: %1\$s\nName: %2\$s\nConsent: %3\$d", 'emindy-core' ), $email, $name, $consent );
                wp_mail( $admin, $admin_subject, $admin_message );
        }

        // Hook: for external ESP integrations (MailerLite, ConvertKit, Brevo…).
        do_action(
                'emindy_newsletter_subscribed',
                array(
                        'email'   => $email,
                        'name'    => $name,
                        'consent' => $consent,
                )
        );

        // Send welcome email to subscriber (via Easy WP SMTP).
        $subject = __( 'Welcome to the Calm Circle — eMINDy', 'emindy-core' );
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $body    = '<p>' . esc_html__( 'Hi', 'emindy-core' ) . ( $name ? ' ' . esc_html( $name ) : '' ) . ',</p>'
                . '<p>' . esc_html__( 'Welcome to the Calm Circle. Every week, you\'ll receive a short calm practice and mindful tip.', 'emindy-core' ) . '</p>'
                . '<p>' . sprintf( wp_kses( __( 'Want to begin now? Try a 60-second reset: <a href="%s">Start here</a>.', 'emindy-core' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( home_url( '/em_video/1-minute-mindful-break/' ) ) ) . '</p>'
                . '<p>' . esc_html__( 'With care,', 'emindy-core' ) . '<br>eMINDy</p>';

        wp_mail( $email, $subject, $body, $headers );

        // Redirect.
        $target = add_query_arg( 'success', '1', home_url( '/newsletter/' ) );
        wp_safe_redirect( $target );
        exit;
}

/**
 * Retrieve best-guess client IP.
 *
 * @return string
 */
function emindy_client_ip() {
        foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $k ) {
                if ( ! empty( $_SERVER[ $k ] ) ) {
                        $ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $k ] ) ) )[0];
                        $ip = trim( $ip );

                        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                                return $ip;
                        }
                }
        }

        return '';
}
