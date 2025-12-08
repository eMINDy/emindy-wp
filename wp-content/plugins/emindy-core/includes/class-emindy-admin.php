<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class Admin {
  /**
   * Register admin hooks.
   */
  public static function register() {
    add_action( 'add_meta_boxes', array( __CLASS__, 'metabox' ) );
    add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
    add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
  }

  /**
   * Register meta boxes for eMINDy post types.
   */
  public static function metabox() {
    add_meta_box( 'emindy_json_meta', esc_html__( 'eMINDy JSON Meta', 'emindy-core' ), array( __CLASS__, 'render' ), 'em_video', 'normal', 'default' );
    add_meta_box( 'emindy_json_meta_ex', esc_html__( 'eMINDy JSON Meta', 'emindy-core' ), array( __CLASS__, 'render_ex' ), 'em_exercise', 'normal', 'default' );
  }

  /**
   * Render meta box for video chapters JSON.
   *
   * @param \WP_Post $post Current post object.
   */
  public static function render( $post ) {
    wp_nonce_field( 'emindy_json_meta', 'emindy_json_meta_nonce' );
    $val = get_post_meta( $post->ID, 'em_chapters_json', true );

    echo '<p><label for="em_chapters_json"><strong>em_chapters_json</strong></label></p>';
    echo '<textarea style="width:100%;min-height:160px" id="em_chapters_json" name="em_chapters_json">' . esc_textarea( $val ) . '</textarea>';
    echo '<p id="em_chapters_json_status" style="margin-top:6px;"></p>';
  }

  /**
   * Render meta box for exercise steps JSON and HowTo metadata.
   *
   * @param \WP_Post $post Current post object.
   */
  public static function render_ex( $post ) {
    wp_nonce_field( 'emindy_json_meta', 'emindy_json_meta_nonce' );
    $val = get_post_meta( $post->ID, 'em_steps_json', true );

    echo '<p><label for="em_steps_json"><strong>em_steps_json</strong></label></p>';
    echo '<textarea style="width:100%;min-height:160px" id="em_steps_json" name="em_steps_json">' . esc_textarea( $val ) . '</textarea>';
    echo '<p id="em_steps_json_status" style="margin-top:6px;"></p>';

    // Additional HowTo metadata fields.
    $total    = get_post_meta( $post->ID, 'em_total_seconds', true );
    $prep     = get_post_meta( $post->ID, 'em_prep_seconds', true );
    $perform  = get_post_meta( $post->ID, 'em_perform_seconds', true );
    $supplies = get_post_meta( $post->ID, 'em_supplies', true );
    $tools    = get_post_meta( $post->ID, 'em_tools', true );
    $yield    = get_post_meta( $post->ID, 'em_yield', true );

    echo '<hr />';
    echo '<p><label for="em_total_seconds"><strong>' . esc_html__( 'Total time (seconds)', 'emindy-core' ) . '</strong></label><br />';
    echo '<input type="number" id="em_total_seconds" name="em_total_seconds" value="' . esc_attr( $total ) . '" placeholder="0" /></p>';
    echo '<p><label for="em_prep_seconds"><strong>' . esc_html__( 'Preparation time (seconds)', 'emindy-core' ) . '</strong></label><br />';
    echo '<input type="number" id="em_prep_seconds" name="em_prep_seconds" value="' . esc_attr( $prep ) . '" placeholder="0" /></p>';
    echo '<p><label for="em_perform_seconds"><strong>' . esc_html__( 'Perform time (seconds)', 'emindy-core' ) . '</strong></label><br />';
    echo '<input type="number" id="em_perform_seconds" name="em_perform_seconds" value="' . esc_attr( $perform ) . '" placeholder="0" /></p>';
    echo '<p><label for="em_supplies"><strong>' . esc_html__( 'Supplies (comma-separated or JSON array)', 'emindy-core' ) . '</strong></label><br />';
    echo '<input type="text" style="width:100%" id="em_supplies" name="em_supplies" value="' . esc_attr( $supplies ) . '" placeholder="' . esc_attr__( 'Mat, Strap', 'emindy-core' ) . '" /></p>';
    echo '<p><label for="em_tools"><strong>' . esc_html__( 'Tools (comma-separated or JSON array)', 'emindy-core' ) . '</strong></label><br />';
    echo '<input type="text" style="width:100%" id="em_tools" name="em_tools" value="' . esc_attr( $tools ) . '" placeholder="' . esc_attr__( 'Block, Timer', 'emindy-core' ) . '" /></p>';
    echo '<p><label for="em_yield"><strong>' . esc_html__( 'Yield (optional)', 'emindy-core' ) . '</strong></label><br />';
    echo '<input type="text" style="width:100%" id="em_yield" name="em_yield" value="' . esc_attr( $yield ) . '" placeholder="' . esc_attr__( 'e.g. Number of repetitions', 'emindy-core' ) . '" /></p>';
  }

  /**
   * Save meta values.
   *
   * @param int      $post_id Saved post ID.
   * @param \WP_Post $post    Saved post object.
   */
  public static function save( $post_id, $post ) {
    if ( empty( $post ) || ! in_array( $post->post_type, array( 'em_video', 'em_exercise' ), true ) ) {
      return;
    }

    $nonce = isset( $_POST['emindy_json_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['emindy_json_meta_nonce'] ) ) : '';
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'emindy_json_meta' ) ) {
      return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
      return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
    }

    foreach ( array( 'em_chapters_json', 'em_steps_json' ) as $key ) {
      if ( isset( $_POST[ $key ] ) ) {
        $val = (string) wp_unslash( $_POST[ $key ] );
        $val = Meta::sanitize_json( $val ); // از کلاس Meta همین پلاگین
        update_post_meta( $post_id, $key, $val );
      }
    }

    // Save additional HowTo meta fields for exercises.
    $fields = array(
      'em_total_seconds'   => 'absint',
      'em_prep_seconds'    => 'absint',
      'em_perform_seconds' => 'absint',
      'em_supplies'        => 'sanitize_text_field',
      'em_tools'           => 'sanitize_text_field',
      'em_yield'           => 'sanitize_text_field',
    );

    foreach ( $fields as $field_key => $callback ) {
      if ( isset( $_POST[ $field_key ] ) ) {
        $raw       = wp_unslash( $_POST[ $field_key ] );
        $sanitized = call_user_func( $callback, $raw );
        update_post_meta( $post_id, $field_key, $sanitized );
      }
    }
  }

  /**
   * Enqueue admin assets.
   *
   * @param string $hook Current admin hook.
   */
  public static function assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
      return;
    }

    $screen = get_current_screen();
    if ( ! $screen || ! in_array( $screen->post_type, array( 'em_video', 'em_exercise' ), true ) ) {
      return;
    }

    wp_enqueue_script( 'emindy-admin-json', EMINDY_CORE_URL . 'assets/js/admin-json.js', array( 'jquery' ), EMINDY_CORE_VERSION, true );
    wp_localize_script(
      'emindy-admin-json',
      'emindyAdmin',
      array(
        'valid'   => __( 'Valid JSON ✔', 'emindy-core' ),
        'invalid' => __( 'Invalid JSON ✖', 'emindy-core' ),
      )
    );
  }
}

