<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {
  public static function register() {
    add_action('add_meta_boxes', [__CLASS__,'metabox']);
    add_action('save_post', [__CLASS__,'save'], 10, 2);
    add_action('admin_enqueue_scripts', [__CLASS__,'assets']);
  }

  public static function metabox() {
    add_meta_box('emindy_json_meta','eMINDy JSON Meta',[__CLASS__,'render'],'em_video','normal','default');
    add_meta_box('emindy_json_meta_ex','eMINDy JSON Meta',[__CLASS__,'render_ex'],'em_exercise','normal','default');
  }

  public static function render($post) {
    wp_nonce_field('emindy_json_meta','emindy_json_meta_nonce');
    $val = get_post_meta($post->ID,'em_chapters_json',true);
    echo '<p><label><strong>em_chapters_json</strong></label></p>';
    echo '<textarea style="width:100%;min-height:160px" id="em_chapters_json" name="em_chapters_json">'.esc_textarea($val).'</textarea>';
    echo '<p id="em_chapters_json_status" style="margin-top:6px;"></p>';
  }

  public static function render_ex($post) {
    wp_nonce_field('emindy_json_meta','emindy_json_meta_nonce');
    $val = get_post_meta($post->ID,'em_steps_json',true);
    echo '<p><label><strong>em_steps_json</strong></label></p>';
    echo '<textarea style="width:100%;min-height:160px" id="em_steps_json" name="em_steps_json">'.esc_textarea($val).'</textarea>';
    echo '<p id="em_steps_json_status" style="margin-top:6px;"></p>';

    // Additional HowTo metadata fields
    $total   = get_post_meta( $post->ID, 'em_total_seconds', true );
    $prep    = get_post_meta( $post->ID, 'em_prep_seconds', true );
    $perform = get_post_meta( $post->ID, 'em_perform_seconds', true );
    $supplies= get_post_meta( $post->ID, 'em_supplies', true );
    $tools   = get_post_meta( $post->ID, 'em_tools', true );
    $yield   = get_post_meta( $post->ID, 'em_yield', true );
    echo '<hr />';
    echo '<p><label for="em_total_seconds"><strong>'.esc_html__('Total time (seconds)','emindy-core').'</strong></label><br />';
    echo '<input type="number" id="em_total_seconds" name="em_total_seconds" value="'.esc_attr($total).'" placeholder="0" /></p>';
    echo '<p><label for="em_prep_seconds"><strong>'.esc_html__('Preparation time (seconds)','emindy-core').'</strong></label><br />';
    echo '<input type="number" id="em_prep_seconds" name="em_prep_seconds" value="'.esc_attr($prep).'" placeholder="0" /></p>';
    echo '<p><label for="em_perform_seconds"><strong>'.esc_html__('Perform time (seconds)','emindy-core').'</strong></label><br />';
    echo '<input type="number" id="em_perform_seconds" name="em_perform_seconds" value="'.esc_attr($perform).'" placeholder="0" /></p>';
    echo '<p><label for="em_supplies"><strong>'.esc_html__('Supplies (comma-separated or JSON array)','emindy-core').'</strong></label><br />';
    echo '<input type="text" style="width:100%" id="em_supplies" name="em_supplies" value="'.esc_attr($supplies).'" placeholder="Mat, Strap" /></p>';
    echo '<p><label for="em_tools"><strong>'.esc_html__('Tools (comma-separated or JSON array)','emindy-core').'</strong></label><br />';
    echo '<input type="text" style="width:100%" id="em_tools" name="em_tools" value="'.esc_attr($tools).'" placeholder="Block, Timer" /></p>';
    echo '<p><label for="em_yield"><strong>'.esc_html__('Yield (optional)','emindy-core').'</strong></label><br />';
    echo '<input type="text" style="width:100%" id="em_yield" name="em_yield" value="'.esc_attr($yield).'" placeholder="e.g. Number of repetitions" /></p>';
  }

  public static function save($post_id,$post){
    if ( !isset($_POST['emindy_json_meta_nonce']) || !wp_verify_nonce($_POST['emindy_json_meta_nonce'],'emindy_json_meta') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( !current_user_can('edit_post',$post_id) ) return;

    foreach (['em_chapters_json','em_steps_json'] as $key){
      if ( isset($_POST[$key]) ) {
        $val = (string) wp_unslash($_POST[$key]);
        $val = Meta::sanitize_json($val); // از کلاس Meta همین پلاگین
        update_post_meta($post_id, $key, $val);
      }
    }

    // Save additional HowTo meta fields for exercises
    $fields = [
      'em_total_seconds'   => 'intval',
      'em_prep_seconds'    => 'intval',
      'em_perform_seconds' => 'intval',
      'em_supplies'        => 'sanitize_text_field',
      'em_tools'           => 'sanitize_text_field',
      'em_yield'           => 'sanitize_text_field',
    ];
    foreach ( $fields as $field_key => $cb ) {
      if ( isset( $_POST[ $field_key ] ) ) {
        $raw = $_POST[ $field_key ];
        $sanitized = call_user_func( $cb, $raw );
        update_post_meta( $post_id, $field_key, $sanitized );
      }
    }
  }

  public static function assets($hook){
    if ( in_array($hook, ['post.php','post-new.php'], true) ){
      wp_enqueue_script('emindy-admin-json', EMINDY_CORE_URL.'assets/js/admin-json.js', ['jquery'], EMINDY_CORE_VERSION, true);
      wp_localize_script('emindy-admin-json','emindyAdmin',[
        'valid' => __('Valid JSON ✔','emindy-core'),
        'invalid' => __('Invalid JSON ✖','emindy-core')
      ]);
    }
  }
}
