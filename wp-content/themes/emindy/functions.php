<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function () {
  wp_enqueue_style( 'emindy-child', get_stylesheet_uri(), [ 'twentytwentyfive-style' ], '0.4.0' );
  // Enqueue the dark mode toggle script. It depends on no other scripts and
  // loads in the footer for better performance. See assets/js/dark-mode-toggle.js.
  wp_enqueue_script(
    'emindy-dark-mode-toggle',
    get_stylesheet_directory_uri() . '/assets/js/dark-mode-toggle.js',
    [],
    '1.0',
    true
  );
} );

// Theme supports
/*
 * Register basic theme supports and load the theme’s translation files.
 *
 * In addition to adding support for common WordPress features, we call
 * load_theme_textdomain() so that all translatable strings in the theme
 * (wrapped with __(), _e(), etc.) use the `emindy` text domain. Without
 * loading the text domain WordPress will not find the .mo files in the
 * languages directory and translations will not work【766128347407102†L100-L116】.
 */
add_action('after_setup_theme', function () {
    // Load translations from the `languages` folder in this child theme.
    load_theme_textdomain('emindy', get_stylesheet_directory() . '/languages');
    
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','gallery','caption','style','script']);
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');
});

// Robots: noindex برای جستجو و 404 (با Rank Math)
add_filter('rank_math/frontend/robots', function($robots){
    if ( is_search() || is_404() ) {
        $robots['index']  = 'noindex';
        $robots['follow'] = 'follow';
    }
    return $robots;
});

// Fallback اگر Rank Math نبود (ایمن)
add_action('wp_head', function () {
    if ( function_exists('rank_math') ) return;
    if ( is_search() || is_404() ) {
        echo '<meta name="robots" content="noindex,follow" />' . "\n";
    }
}, 99);


add_action('wp_body_open', function(){
  echo '<a class="skip-link screen-reader-text" href="#main-content">'.esc_html__('Skip to content','emindy').'</a>';
});

// آرشیو em_video: فیلتر موضوع + کنترل per_page + جستجو
add_action('pre_get_posts', function($q){
  if ( is_admin() || ! $q->is_main_query() ) return;

  if ( $q->is_post_type_archive('em_video') ) {
    $q->set('posts_per_page', 9);

    if (!empty($_GET['topic'])) {
      // Use the unified `topic` taxonomy instead of the old `em_topic` slug.
      $q->set('tax_query', [[
        'taxonomy' => 'topic',
        'field'    => 'term_id',
        'terms'    => (int) $_GET['topic'],
      ]]);
    }
    if ( isset($_GET['s']) ) {
      // Sanitize the search term from the query string.  wp_unslash() is
      // unnecessary here because WordPress handles request data and adds
      // slashes automatically.  Using sanitize_text_field() strips tags
      // and encodes entities to prevent injection and ensure safe output.
      $search = sanitize_text_field( (string) $_GET['s'] );
      $q->set('s', $search);
    }
  }
});

add_filter('the_excerpt', function($excerpt){
  if (is_search() && get_search_query()){
    $q = preg_quote(get_search_query(),'/');
    $excerpt = preg_replace('/('.$q.')/iu','<mark>$1</mark>',$excerpt);
  }
  return $excerpt;
});

// Preconnect/Preload برای Lyte/YouTube
add_filter('wp_resource_hints', function($urls, $relation_type){
  if ( 'preconnect' === $relation_type ) {
    $urls[] = 'https://i.ytimg.com';
    $urls[] = 'https://www.youtube-nocookie.com';
    $urls[] = 'https://www.youtube.com';
    $urls[] = 'https://s.ytimg.com';
  }
  return array_unique($urls);
}, 10, 2);


/*
 * The taxonomy registration and default term insertion previously defined in this
 * child theme have been migrated to the emindy-core plugin.  Registering
 * taxonomies from both the theme and the plugin can cause conflicts and
 * duplicate definitions【776845342899939†L80-L84】.  The core plugin now
 * registers the `topic`, `technique`, `duration`, `format`, `use_case`,
 * `level` and `a11y_feature` taxonomies and attaches them to the custom post
 * types.  If you need to add or modify terms, do so via the plugin.
 */


function emindy_print_itemlist_jsonld( $title, $items ) {
  // $items: array of ['name'=>'', 'url'=>'']
  $list = [];
  $pos = 0;
  foreach ($items as $it) {
    if ( empty($it['name']) || empty($it['url']) ) continue;
    $pos++;
    $list[] = [
      '@type' => 'ListItem',
      'position' => $pos,
      'item' => [
        '@type' => 'WebPage',
        'name'  => wp_strip_all_tags($it['name']),
        'url'   => esc_url_raw($it['url']),
      ]
    ];
  }

  if (!$list) return;

  $graph = [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => $title,
    'itemListElement' => $list
  ];

  echo '<script type="application/ld+json">'. wp_json_encode($graph, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE ) .'</script>';
}

// Meta key to store primary topic term_id
define('EMINDY_PRIMARY_TOPIC_META', '_em_primary_topic');

// Add meta box
add_action('add_meta_boxes', function(){
  add_meta_box('emindy_primary_topic', 'Primary Topic', 'emindy_primary_topic_box', ['post','page'], 'side', 'default');
});

function emindy_primary_topic_box($post){
  $saved = (int) get_post_meta($post->ID, EMINDY_PRIMARY_TOPIC_META, true);
  $terms = wp_get_post_terms($post->ID, 'topic');
  echo '<p>Select the primary topic (required if topics are set):</p>';
  echo '<select name="em_primary_topic" style="width:100%">';
  echo '<option value="">— None —</option>';
  foreach($terms as $t){
    printf(
      '<option value="%d" %s>%s</option>',
      $t->term_id,
      selected($saved, $t->term_id, false),
      esc_html($t->name)
    );
  }
  echo '</select>';
  wp_nonce_field('em_primary_topic_save','em_primary_topic_nonce');
}

// Save
add_action('save_post', function($post_id){
  if( !isset($_POST['em_primary_topic_nonce']) || !wp_verify_nonce($_POST['em_primary_topic_nonce'],'em_primary_topic_save') ) return;
  if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
  if( isset($_POST['em_primary_topic']) ){
    $primary = (int) $_POST['em_primary_topic'];
    update_post_meta($post_id, EMINDY_PRIMARY_TOPIC_META, $primary);
  }
});

add_action('admin_notices', function(){
  $screen = get_current_screen();
  if ( !in_array($screen->id, ['post','page','em_video','em_exercise','em_article']) ) return;
  $post_id = isset($_GET['post']) ? (int)$_GET['post'] : 0;
  if (!$post_id) return;
  $topics = wp_get_post_terms($post_id,'topic', ['fields'=>'ids']);
  if ($topics && !get_post_meta($post_id, '_em_primary_topic', true)) {
    echo '<div class="notice notice-warning"><p><strong>Primary Topic</strong> is not set. Please select one in the sidebar meta box for better recommendations & SEO.</p></div>';
  }
});


add_action('pre_get_posts', function($q){
  if ( is_admin() || !$q->is_main_query() ) return;
  if ( $q->is_post_type_archive('em_video') || ($q->is_tax('topic') && $q->get('post_type')==='em_video') ) {
    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
    if ($sort === 'alpha') {
      $q->set('orderby','title'); $q->set('order','ASC');
    } else { // default latest
      $q->set('orderby','date'); $q->set('order','DESC');
    }
  }
});

