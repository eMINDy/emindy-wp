<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {
  public static function register(){
    add_action('wp_ajax_emindy_send_assessment', [__CLASS__,'send_assessment']);
    add_action('wp_ajax_nopriv_emindy_send_assessment', [__CLASS__,'send_assessment']);

    add_action('wp_ajax_emindy_sign_result', [__CLASS__,'sign_result']);
    add_action('wp_ajax_nopriv_emindy_sign_result', [__CLASS__,'sign_result']);
  }

  public static function sign_result(){
    check_ajax_referer('emindy_assess');

    $type  = isset($_POST['type'])  ? sanitize_key($_POST['type']) : '';
    $score = isset($_POST['score']) ? (int) $_POST['score'] : -1;
    if ( ! in_array($type, ['phq9','gad7'], true) || $score < 0 ) {
      wp_send_json_error( __('Invalid request','emindy-core') );
    }
    $secret = wp_salt('auth');
    $sig = hash_hmac('sha256', $type.'|'.$score, $secret);
    $url = assessment_result_base_url() . '?type=' . rawurlencode($type) . '&score=' . (int)$score . '&sig=' . rawurlencode($sig);
    wp_send_json_success( [ 'url' => $url ] );
  }

  public static function send_assessment(){
    check_ajax_referer('emindy_assess');

    $email   = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $summary = isset($_POST['summary']) ? wp_strip_all_tags($_POST['summary']) : '';
    $kind    = isset($_POST['kind']) ? sanitize_key($_POST['kind']) : '';

    if ( ! is_email($email) ) wp_send_json_error( __('Invalid email','emindy-core') );
    if ( empty($summary) || empty($kind) ) wp_send_json_error( __('Missing fields','emindy-core') );

    // Rate limit: 5/hour per IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'emindy_rate_'.md5($ip);
    $cnt = (int) get_transient($key);
    if ($cnt >= 5) wp_send_json_error( __('Too many requests, try later','emindy-core') );
    set_transient($key, $cnt+1, HOUR_IN_SECONDS);

    $subject = sprintf( __('Your %s summary','emindy-core'), strtoupper($kind) );
    $body = $summary . "\n\n" . home_url('/');
    $sent = wp_mail( $email, $subject, $body );
    if ($sent) wp_send_json_success(true);
    wp_send_json_error( __('Send failed','emindy-core') );
  }
}
