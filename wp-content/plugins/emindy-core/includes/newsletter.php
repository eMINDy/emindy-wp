<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * eMINDy Newsletter — minimal local collector with upgrade hooks
 */

// 0) Create table on activation (called from plugin activation hook)
function emindy_newsletter_install_table(){
  global $wpdb;
  $table = $wpdb->prefix . 'emindy_newsletter';
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE IF NOT EXISTS $table (
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
  dbDelta($sql);
}
// Removed the after_switch_theme hook. The table creation is now handled
// via register_activation_hook in the main plugin file. This prevents
// duplicate calls on every theme switch and follows WordPress best
// practices for installing custom tables【331235010806737†L68-L70】.

// 1) Shortcode: [em_newsletter_form]
add_shortcode('em_newsletter_form', function(){
  $success = isset($_GET['success']) && $_GET['success'] == '1';
  if ($success){
    return '<div class="em-success" role="status" aria-live="polite" style="background:#e9f7ef;border-radius:12px;padding:12px;margin-bottom:8px"><strong>Thank you!</strong> Please check your inbox.</div>';
  }

  $action = esc_url( admin_url('admin-post.php') );
  $nonce  = wp_create_nonce('emindy_newsletter_subscribe');
  ob_start(); ?>
  <form action="<?php echo $action; ?>" method="post" class="em-newsletter-form" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:end">
    <input type="hidden" name="action" value="em_newsletter_subscribe">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>">

    <div style="flex:1;min-width:200px">
      <label for="em-nl-email" style="display:block;margin-bottom:4px">Email</label>
      <input id="em-nl-email" name="email" type="email" required autocomplete="email" placeholder="you@example.com" style="width:100%;padding:.6rem .8rem;border-radius:.6rem;border:1px solid #cfd8dc">
    </div>
    <div style="flex:1;min-width:160px">
      <label for="em-nl-name" style="display:block;margin-bottom:4px">Name <span style="opacity:.6">(optional)</span></label>
      <input id="em-nl-name" name="name" type="text" autocomplete="name" placeholder="Your name" style="width:100%;padding:.6rem .8rem;border-radius:.6rem;border:1px solid #cfd8dc">
    </div>
    <div style="min-width:160px">
      <label for="em-nl-submit" class="sr-only">Subscribe</label>
      <button id="em-nl-submit" type="submit" style="width:100%;padding:.7rem 1rem;border:0;border-radius:.7rem;background:#F4D483;color:#0A2A43;font-weight:700;cursor:pointer">Subscribe</button>
    </div>

    <div style="width:100%;margin-top:.25rem">
      <label style="display:flex;gap:.5rem;align-items:flex-start">
        <input type="checkbox" name="consent" value="1" required>
        <span>I agree to receive weekly email updates from eMINDy and understand I can unsubscribe anytime.</span>
      </label>
    </div>
  </form>
  <?php
  return ob_get_clean();
});

// 2) Handler: save + notify + hook for integrations
add_action('admin_post_nopriv_em_newsletter_subscribe', 'emindy_newsletter_handle');
add_action('admin_post_em_newsletter_subscribe', 'emindy_newsletter_handle');

function emindy_newsletter_handle(){
  if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'emindy_newsletter_subscribe') ) {
    wp_die('Security check failed', 403);
  }
  $email   = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
  $name    = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
  $consent = isset($_POST['consent']) ? 1 : 0;

  if ( ! is_email($email) ) {
    wp_safe_redirect( add_query_arg('success','0', wp_get_referer() ?: home_url('/newsletter/') ) );
    exit;
  }

  global $wpdb;
  $table = $wpdb->prefix . 'emindy_newsletter';
  $wpdb->query( $wpdb->prepare(
    "INSERT INTO $table (email, name, consent, ip, ua) VALUES (%s,%s,%d,%s,%s)
     ON DUPLICATE KEY UPDATE name=VALUES(name), consent=VALUES(consent)",
    $email, $name, $consent, emindy_client_ip(), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 1000)
  ));

  // Admin notify (optional)
  $admin = get_option('admin_email');
  if ($admin){
    wp_mail($admin, 'New eMINDy Newsletter Subscriber', "Email: $email\nName: $name\nConsent: $consent");
  }

  // Hook: for external ESP integrations (MailerLite, ConvertKit, Brevo…)
  do_action('emindy_newsletter_subscribed', [
    'email'   => $email,
    'name'    => $name,
    'consent' => $consent,
  ]);

  // Send welcome email to subscriber (via Easy WP SMTP)
  $subject = 'Welcome to the Calm Circle — eMINDy';
  $headers = ['Content-Type: text/html; charset=UTF-8'];
  $body = '<p>Hi'. ( $name ? ' ' . esc_html($name) : '' ) .',</p>
  <p>Welcome to the Calm Circle. Every week, you\'ll receive a short calm practice and mindful tip.</p>
  <p>Want to begin now? Try a 60-second reset: <a href="'. esc_url(home_url('/em_video/1-minute-mindful-break/')) .'">Start here</a>.</p>
  <p>With care,<br>eMINDy</p>';

  wp_mail($email, $subject, $body, $headers);

  // Redirect
  $target = add_query_arg('success','1', home_url('/newsletter/') );
  wp_safe_redirect($target);
  exit;
}

function emindy_client_ip(){
  foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k){
    if (!empty($_SERVER[$k])) {
      $ip = explode(',', $_SERVER[$k])[0];
      return sanitize_text_field(trim($ip));
    }
  }
  return '';
}

