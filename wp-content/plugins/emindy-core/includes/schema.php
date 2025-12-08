<?php
// Exit if accessed directly.
if ( ! defined('ABSPATH') ) exit;

/**
 * eMINDy JSON-LD Enhancements for Rank Math
 * - Adds/augments Organization, WebSite/SearchAction
 * - Adds CollectionPage/ItemList for archives
 * - Adds VideoObject for em_video
 * - Adds HowTo for em_exercise
 * - Adds SearchResultsPage on search
 */

add_filter('rank_math/json_ld', function($data, $jsonld){
  // Helpers
  $site_url   = home_url('/');
  $site_name  = get_bloginfo('name');
  $locale     = get_locale();               // e.g. en_US, fa_IR
  $lang_code  = substr($locale, 0, 2);      // e.g. en, fa
  $inLanguage = $lang_code ?: 'en';

  // 0) Ensure Organization + WebSite with SearchAction
  // Remove duplicates if present
  foreach (['Organization', 'WebSite'] as $k) {
    if ( isset($data[ $k ]) && is_array($data[$k]) && empty($data[$k]['@id']) ) {
      unset($data[$k]); // avoid duplicate anonymous nodes
    }
  }

  // Organization
  // logo: prefer your custom logo from customizer if set
  $logo_id  = get_theme_mod('custom_logo');
  $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
  $sameAs   = array_values(array_filter([
    'https://www.youtube.com/@emindy_official',
    // Add when ready:
    // 'https://www.instagram.com/emindy_official',
    // 'https://www.tiktok.com/@emindy_official',
    // 'https://twitter.com/emindy_official',
  ]));

  $data['Organization'] = [
    '@type' => 'Organization',
    '@id'   => $site_url . '#org',
    'name'  => $site_name,
    'url'   => $site_url,
    'logo'  => $logo_url ? [
      '@type' => 'ImageObject',
      'url'   => $logo_url
    ] : null,
    'sameAs'=> $sameAs ?: null,
  ];

  // WebSite + SearchAction
  $data['WebSite'] = [
    '@type' => 'WebSite',
    '@id'   => $site_url . '#website',
    'url'   => $site_url,
    'name'  => $site_name,
    'inLanguage' => $inLanguage,
    'publisher'  => ['@id' => $site_url . '#org'],
    'potentialAction' => [
      '@type' => 'SearchAction',
      'target'=> $site_url . '?s={search_term_string}',
      'query-input' => 'required name=search_term_string'
    ]
  ];

  // 1) Search Results Page
  if ( is_search() ) {
    $q = isset($_GET['s']) ? (string) wp_unslash($_GET['s']) : '';
    $data['WebPage'] = [
      '@type' => 'SearchResultsPage',
      '@id'   => $site_url . '#search',
      'name'  => 'Search results',
      'inLanguage' => $inLanguage,
      'isPartOf'   => ['@id' => $site_url . '#website'],
      'about'      => $q ? sanitize_text_field($q) : null,
    ];
  }

  // 2) Archives: CollectionPage + ItemList (avoid duplicates Rank Math may add)
  if ( is_archive() && ! is_search() ) {
    $archive_url = home_url( add_query_arg( [] ) );

    if ( empty( $data['WebPage'] ) ) {
      $data['WebPage'] = [
        '@type' => 'CollectionPage',
        '@id'   => trailingslashit( $archive_url ) . '#collection',
        'name'  => wp_strip_all_tags( get_the_archive_title() ),
        'inLanguage' => $inLanguage,
        'isPartOf'   => ['@id' => $site_url . '#website'],
      ];
    }

    // Build ItemList based on current loop (first page only for brevity)
    if ( empty( $data['ItemList'] ) ) {
      global $wp_query;
      if ( isset($wp_query->posts) && is_array($wp_query->posts) && !empty($wp_query->posts) ) {
        $position = 1;
        $items = [];
        foreach ($wp_query->posts as $p) {
          $items[] = [
            '@type'   => 'ListItem',
            'position'=> $position++,
            'url'     => get_permalink($p),
          ];
          if ($position > 11) break; // keep compact
        }
        $data['ItemList'] = [
          '@type' => 'ItemList',
          'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
          'numberOfItems' => count($items),
          'itemListElement'=> $items,
        ];
      }
    }
  }

  // 3) Single CPTs → merge schema from central builders
  if ( is_singular() ) {
    $post_obj = get_post();

    if ( $post_obj && is_singular('em_video') ) {
      $video_schema = \EMINDY\Core\Schema::build_video_schema( $post_obj );
      if ( $video_schema ) {
        $data['VideoObject'] = $video_schema;
      }
    }

    if ( $post_obj && is_singular('em_exercise') ) {
      $howto_schema = \EMINDY\Core\Schema::build_exercise_howto_schema( $post_obj );
      if ( $howto_schema ) {
        $data['HowTo'] = $howto_schema;
      }
    }

    if ( $post_obj && is_singular('em_article') ) {
      $article_schema = \EMINDY\Core\Schema::build_article_schema( $post_obj );
      if ( $article_schema ) {
        $data['Article'] = $article_schema;
      }
    }
  }

  // 6) Help/FAQ optional (only if you actually output Q&A on that page)
  // Example:
  // if ( is_page('help') ) { $data['FAQPage'] = ... }

  // Cleanup nulls
  foreach ($data as $k => $node) {
    if (is_array($node)) {
      $data[$k] = emindy_array_filter_recursive($node);
    }
  }
  return $data;
}, 10, 2);


// ===== Helpers =====

if (!function_exists('emindy_seconds_from_ts')) {
  function emindy_seconds_from_ts($t){
    // accepts "mm:ss" or seconds like "90"
    if (preg_match('/^\d+$/', (string)$t)) return (int)$t;
    if (strpos($t, ':') !== false) {
      $parts = array_reverse(array_map('intval', explode(':', $t)));
      $sec = 0;
      if (isset($parts[0])) $sec += $parts[0];
      if (isset($parts[1])) $sec += $parts[1] * 60;
      if (isset($parts[2])) $sec += $parts[2] * 3600;
      return $sec;
    }
    return 0;
  }
}

if (!function_exists('emindy_iso8601_duration')) {
  function emindy_iso8601_duration($seconds){
    $seconds = max(0, (int)$seconds);
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    $str = 'PT';
    if ($h) $str .= $h . 'H';
    if ($m) $str .= $m . 'M';
    if ($s || (!$h && !$m)) $str .= $s . 'S';
    return $str;
  }
}

if (!function_exists('emindy_extract_headings')) {
  function emindy_extract_headings($html, $tags = ['h2','h3']){
    $out = [];
    if (! $html) return $out;
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $selector = implode('|', array_map(fn($t) => '//'.$t, $tags));
    $nodes = $xpath->query($selector);
    if ($nodes && $nodes->length) {
      foreach ($nodes as $n) {
        $txt = trim($n->textContent);
        if ($txt !== '') $out[] = $txt;
        if (count($out) >= 10) break;
      }
    }
    return $out;
  }
}

if (!function_exists('emindy_array_filter_recursive')) {
  function emindy_array_filter_recursive($input){
    foreach ($input as $k => $v) {
      if (is_array($v)) $input[$k] = emindy_array_filter_recursive($v);
    }
    return array_filter($input, function($v){
      if ($v === null) return false;
      if (is_array($v) && empty($v)) return false;
      return true;
    });
  }
}

// داخل همان فیلتر rank_math/json_ld در بخش شرط صفحات:
if ( is_page() && function_exists('pll_current_language') ) {
  // اگر اسلاگ صفحه Newsletter شما چیز دیگری است، با شرط دلخواه عوض کنید
}
if ( is_page('newsletter') ) {
  $site_url = home_url('/');
  $data['WebPage'] = [
    '@type' => 'WebPage',
    '@id'   => trailingslashit( get_permalink() ) . '#webpage',
    'name'  => 'Newsletter — eMINDy',
    'inLanguage' => $inLanguage,
    'isPartOf'   => ['@id' => $site_url . '#website'],
    'about'      => 'EmailSubscription',
    'potentialAction' => [
      '@type' => 'SubscribeAction',
      'target'=> get_permalink(), // همان صفحه
      'agent' => ['@type'=>'Person'],
      'recipient' => ['@id' => $site_url . '#org']
    ]
  ];
}
