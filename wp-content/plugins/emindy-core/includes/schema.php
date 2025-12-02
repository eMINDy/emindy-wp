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

  // 2) Archives: CollectionPage + ItemList
  if ( is_archive() && ! is_search() ) {
    $data['WebPage'] = [
      '@type' => 'CollectionPage',
      '@id'   => trailingslashit( get_permalink() ) . '#collection',
      'name'  => wp_strip_all_tags( get_the_archive_title() ),
      'inLanguage' => $inLanguage,
      'isPartOf'   => ['@id' => $site_url . '#website'],
    ];

    // Build ItemList based on current loop (first page only for brevity)
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
        if ($position > 25) break; // keep compact
      }
      $data['ItemList'] = [
        '@type' => 'ItemList',
        'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
        'numberOfItems' => count($items),
        'itemListElement'=> $items,
      ];
    }
  }

  // 3) Single em_video → VideoObject
  if ( is_singular('em_video') ) {
    $post_id   = get_the_ID();
    $thumb     = get_the_post_thumbnail_url($post_id, 'full');
    $title     = get_the_title($post_id);
    $desc      = wp_strip_all_tags( get_the_excerpt($post_id) ?: get_post_field('post_content', $post_id) );
    $date      = get_the_date('c', $post_id);
    $url       = get_permalink($post_id);

    // Try to infer YouTube embed URL.  First check a custom embed meta field
    // `_em_embed_url` (if set by a third‑party plugin).  If missing, fall back
    // to our own `em_youtube_id` meta or extract the ID from content and build
    // a YouTube no‑cookie embed URL.  Finally, if nothing is found, use the
    // permalink itself as a safe fallback.
    $embed = get_post_meta($post_id, '_em_embed_url', true);
    if ( ! $embed ) {
      $yt_id = get_post_meta( $post_id, 'em_youtube_id', true );
      if ( $yt_id ) {
        $embed = 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $yt_id );
      } else {
        // naive fallback – OK if you always use Lyte or embed YouTube links in content
        $embed = $url;
      }
    }

    // Chapters: stored as JSON in em_chapters_json (array of {t,label}), t in seconds or "mm:ss"
    $chapters = [];
    $chap_json = get_post_meta($post_id, 'em_chapters_json', true);
    if ( $chap_json ) {
      $arr = json_decode($chap_json, true);
      if ( is_array($arr) ) {
        $pos = 1;
        foreach ($arr as $c) {
          $label = isset($c['label']) ? wp_strip_all_tags($c['label']) : 'Chapter ' . $pos;
          $t     = isset($c['t']) ? (string)$c['t'] : '0';
          $chapters[] = [
            '@type'  => 'Clip',
            'name'   => $label,
            'startOffset' => emindy_seconds_from_ts($t),
            'position'    => $pos++,
          ];
        }
      }
    }

    // Duration if available (in seconds in meta `em_duration_sec`), else omit
    $duration = get_post_meta($post_id, 'em_duration_sec', true);
    $duration_iso = $duration ? emindy_iso8601_duration( (int)$duration ) : null;

    $data['VideoObject'] = array_filter([
      '@type'        => 'VideoObject',
      '@id'          => $url . '#video',
      'name'         => $title,
      'description'  => $desc ? wp_trim_words($desc, 60, '…') : null,
      'thumbnailUrl' => $thumb ?: null,
      'uploadDate'   => $date,
      'inLanguage'   => $inLanguage,
      'url'          => $url,
      'embedUrl'     => $embed,
      'isFamilyFriendly' => true,
      'hasPart'      => $chapters ?: null,
      'duration'     => $duration_iso,
      'publisher'    => ['@id' => $site_url . '#org'],
      'isPartOf'     => ['@id' => $site_url . '#website'],
    ]);
  }

  // 4) Single em_exercise → HowTo
  if ( is_singular('em_exercise') ) {
    $post_id = get_the_ID();
    $title   = get_the_title($post_id);
    $desc    = wp_strip_all_tags( get_the_excerpt($post_id) ?: get_post_field('post_content', $post_id) );
    $url     = get_permalink($post_id);
    $thumb   = get_the_post_thumbnail_url( $post_id, 'full' );

    // Steps source priority: (A) meta em_steps_json (array of step texts) → (B) h2 headings → (C) excerpt
    $steps = [];
    $steps_json = get_post_meta($post_id, 'em_steps_json', true);
    if ( $steps_json ) {
      $arr = json_decode($steps_json, true);
      if ( is_array($arr) ) {
        foreach ($arr as $i => $txt) {
          $steps[] = [
            '@type' => 'HowToStep',
            'position' => $i+1,
            'name' => wp_strip_all_tags( is_string($txt) ? $txt : ('Step '.($i+1)) ),
          ];
        }
      }
    }
    if ( empty($steps) ) {
      // Try headings as steps
      $content = get_post_field('post_content', $post_id);
      if ( $content ) {
        $h = emindy_extract_headings($content, ['h2','h3']);
        $pos = 1;
        foreach ($h as $htext) {
          $steps[] = ['@type'=>'HowToStep','position'=>$pos++,'name'=>$htext];
          if ($pos > 10) break;
        }
      }
    }
    if ( empty($steps) ) {
      $steps[] = ['@type'=>'HowToStep','position'=>1,'name'=> wp_trim_words($desc, 14, '…') ];
    }

    // Estimated times: total, prep, and perform times (in seconds stored in meta)
    $total_seconds   = (int) get_post_meta($post_id, 'em_total_seconds', true);
    $prep_seconds    = (int) get_post_meta($post_id, 'em_prep_seconds', true);
    $perform_seconds = (int) get_post_meta($post_id, 'em_perform_seconds', true);
    $total_iso   = $total_seconds   ? emindy_iso8601_duration( $total_seconds )   : null;
    $prep_iso    = $prep_seconds    ? emindy_iso8601_duration( $prep_seconds )    : null;
    $perform_iso = $perform_seconds ? emindy_iso8601_duration( $perform_seconds ) : null;

    // Supplies and tools: comma‑separated strings or serialized arrays
    $supply_meta = get_post_meta( $post_id, 'em_supplies', true );
    $tool_meta   = get_post_meta( $post_id, 'em_tools', true );
    $yield_val   = get_post_meta( $post_id, 'em_yield', true );
    $supply = [];
    if ( $supply_meta ) {
      // If stored as serialized array, json_decode handles both
      $arr = is_string( $supply_meta ) ? json_decode( $supply_meta, true ) : $supply_meta;
      if ( ! is_array( $arr ) ) {
        $arr = explode( ',', (string) $supply_meta );
      }
      foreach ( $arr as $it ) {
        $txt = trim( wp_strip_all_tags( $it ) );
        if ( $txt !== '' ) {
          $supply[] = [ '@type' => 'HowToSupply', 'name' => $txt ];
        }
      }
    }
    $tools = [];
    if ( $tool_meta ) {
      $arr = is_string( $tool_meta ) ? json_decode( $tool_meta, true ) : $tool_meta;
      if ( ! is_array( $arr ) ) {
        $arr = explode( ',', (string) $tool_meta );
      }
      foreach ( $arr as $it ) {
        $txt = trim( wp_strip_all_tags( $it ) );
        if ( $txt !== '' ) {
          $tools[] = [ '@type' => 'HowToTool', 'name' => $txt ];
        }
      }
    }

    $data['HowTo'] = array_filter([
      '@type'       => 'HowTo',
      '@id'         => $url . '#howto',
      'name'        => $title,
      'description' => $desc ? wp_trim_words( $desc, 60, '…' ) : null,
      'inLanguage'  => $inLanguage,
      'url'         => $url,
      'step'        => $steps,
      'totalTime'   => $total_iso,
      'prepTime'    => $prep_iso,
      'performTime' => $perform_iso,
      'supply'      => $supply ?: null,
      'tool'        => $tools ?: null,
      'yield'       => $yield_val ? wp_strip_all_tags( $yield_val ) : null,
      'isPartOf'    => [ '@id' => $site_url . '#website' ],
      'publisher'   => [ '@id' => $site_url . '#org' ],
      'image'       => $thumb ? [ '@type' => 'ImageObject', 'url' => $thumb ] : null,
    ]);
  }

  // 5) Single em_article → Article
  // Provide a rich Article schema with headline, author, image and publisher.  If
  // you have multiple authors per article you can extend this to output an array
  // of Person objects.  This implementation requires that the information shown
  // in the schema also exists on the front‑end, as per Google’s rich result
  // guidelines【505784285878935†L226-L230】.
  if ( is_singular('em_article') ) {
    $post_id = get_the_ID();
    $url     = get_permalink( $post_id );
    $title   = get_the_title( $post_id );
    $desc    = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
    $image   = get_the_post_thumbnail_url( $post_id, 'full' );
    $author  = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
    $date    = get_the_date( 'c', $post_id );
    $modified= get_the_modified_date( 'c', $post_id );
    $data['Article'] = array_filter([
      '@type'        => 'Article',
      '@id'          => $url . '#article',
      'headline'     => $title,
      'description'  => $desc ? wp_trim_words( $desc, 60, '…' ) : null,
      'inLanguage'   => $inLanguage,
      'image'        => $image ? [ '@type' => 'ImageObject', 'url' => $image ] : null,
      'author'       => $author ? [ '@type' => 'Person', 'name' => $author ] : null,
      'datePublished'=> $date,
      'dateModified' => $modified,
      'url'          => $url,
      'mainEntityOfPage' => $url,
      'publisher'    => [ '@id' => $site_url . '#org' ],
      'isPartOf'     => [ '@id' => $site_url . '#website' ],
    ]);
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
