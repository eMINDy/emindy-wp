<?php
namespace EMINDY\Core;
if (!defined('ABSPATH')) exit;

class Analytics {
  /**
   * Register AJAX handlers.
   */
  public static function register(){
    add_action('wp_ajax_emindy_track', [__CLASS__,'track']);
    add_action('wp_ajax_nopriv_emindy_track', [__CLASS__,'track']);
  }

  /**
   * Create the analytics table on plugin activation.
   * The table stores event logs for analytics with columns:
   * id (auto increment), time (datetime), type, label, value,
   * post_id (int), ip (varchar), user_agent (text).
   */
  public static function install_table(){
    global $wpdb;
    $table = $wpdb->prefix . 'emindy_analytics';
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table} (
      id bigint(20) unsigned NOT NULL auto_increment,
      time datetime NOT NULL,
      type varchar(60) NOT NULL,
      label text NOT NULL,
      value text NOT NULL,
      post_id bigint(20) unsigned NOT NULL,
      ip varchar(100) NOT NULL,
      ua text NOT NULL,
      PRIMARY KEY  (id),
      KEY type (type),
      KEY post_id (post_id)
    ) {$charset_collate};";
    dbDelta( $sql );
  }

  /**
   * Handle AJAX tracking events.
   * Reads POST parameters and inserts a row in the analytics table.
   */
  public static function track(){
    check_ajax_referer('emindy_assess'); // uses the shared nonce

    $type   = isset($_POST['type'])   ? sanitize_key($_POST['type']) : '';
    $label  = isset($_POST['label'])  ? sanitize_text_field($_POST['label']) : '';
    $value  = isset($_POST['value'])  ? sanitize_text_field($_POST['value']) : '';
    $postId = isset($_POST['post'])   ? intval($_POST['post']) : 0;

    if ( ! $type ) wp_send_json_error('bad');

    global $wpdb;
    $table = $wpdb->prefix . 'emindy_analytics';
    $data = [
      'time'    => current_time('mysql', 1),
      'type'    => $type,
      'label'   => $label,
      'value'   => $value,
      'post_id' => $postId,
      'ip'      => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
      'ua'      => substr( isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '', 0, 255 ),
    ];
    $wpdb->insert( $table, $data );
    wp_send_json_success(true);
  }
}
