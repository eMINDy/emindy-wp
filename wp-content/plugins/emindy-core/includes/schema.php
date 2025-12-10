<?php
/**
 * EMINDy JSON-LD Enhancements for Rank Math.
 *
 * - Adds/augments Organization, WebSite/SearchAction
 * - Adds CollectionPage/ItemList for archives
 * - Adds VideoObject for em_video
 * - Adds HowTo for em_exercise
 * - Adds SearchResultsPage on search
 *
 * @package EmindyCore
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Filter Rank Math JSON-LD payload with eMINDy additions.
 *
 * @param array $data   Existing JSON-LD data.
 * @param array $jsonld Rank Math JSON-LD builder instance (unused).
 *
 * @return array
 */
function emindy_filter_rank_math_json_ld( $data, $jsonld ) {
  $site_url   = home_url( '/' );
  $site_name  = wp_strip_all_tags( get_bloginfo( 'name' ) );
  $locale     = sanitize_text_field( (string) get_locale() ); // e.g. en_US, fa_IR.
  $lang_code  = substr( $locale, 0, 2 );                      // e.g. en, fa.
  $inLanguage = $lang_code ? $lang_code : 'en';

  $data = emindy_schema_remove_anonymous_root_nodes( $data );
  $data = emindy_schema_add_organization( $data, $site_url, $site_name );
  $data = emindy_schema_add_search_action( $data, $site_url, $site_name, $inLanguage );

  // 1) Search Results Page.
  if ( is_search() ) {
    $q = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
    $data['WebPage'] = array(
      '@type'      => 'SearchResultsPage',
      '@id'        => $site_url . '#search',
      'name'       => __( 'Search results', 'emindy-core' ),
      'inLanguage' => $inLanguage,
      'isPartOf'   => array( '@id' => $site_url . '#website' ),
      'about'      => $q ? $q : null,
    );
  }

  // 2) Archives: CollectionPage + ItemList (avoid duplicates Rank Math may add).
  if ( is_archive() && ! is_search() ) {
    $data = emindy_schema_handle_archives( $data, $site_url, $inLanguage );
  }

  // 3) Single CPTs → merge schema from central builders.
  if ( is_singular() ) {
    $post_obj = get_post();

    if ( $post_obj && is_singular( 'em_video' ) ) {
      $data = emindy_schema_add_em_video( $data, $post_obj );
    }

    if ( $post_obj && is_singular( 'em_exercise' ) ) {
      $data = emindy_schema_add_em_exercise( $data, $post_obj );
    }

    if ( $post_obj && is_singular( 'em_article' ) ) {
      $data = emindy_schema_add_em_article( $data, $post_obj );
    }
  }

  // Newsletter SubscribeAction schema (guarded to avoid notices when Polylang is absent).
  if ( is_page() && function_exists( 'pll_current_language' ) ) {
    // If your Newsletter slug differs, adjust the conditional as needed.
  }

  if ( is_page( 'newsletter' ) ) {
    $page_permalink = get_permalink();

    $data['WebPage'] = array(
      '@type'           => 'WebPage',
      '@id'             => trailingslashit( $page_permalink ) . '#webpage',
      'name'            => __( 'Newsletter — eMINDy', 'emindy-core' ),
      'inLanguage'      => $inLanguage,
      'isPartOf'        => array( '@id' => $site_url . '#website' ),
      'about'           => 'EmailSubscription',
      'potentialAction' => array(
        '@type'     => 'SubscribeAction',
        'target'    => $page_permalink,
        'agent'     => array( '@type' => 'Person' ),
        'recipient' => array( '@id' => $site_url . '#org' ),
      ),
    );
  }

  // Cleanup nulls.
  foreach ( $data as $k => $node ) {
    if ( is_array( $node ) ) {
      $data[ $k ] = emindy_array_filter_recursive( $node );
    }
  }

  return $data;
}

/**
 * Remove anonymous Organization and WebSite nodes to prevent duplicates.
 *
 * @param array $data Existing JSON-LD data.
 * @return array
 */
function emindy_schema_remove_anonymous_root_nodes( $data ) {
  foreach ( array( 'Organization', 'WebSite' ) as $k ) {
    if ( isset( $data[ $k ] ) && is_array( $data[ $k ] ) && empty( $data[ $k ]['@id'] ) ) {
      unset( $data[ $k ] ); // Avoid duplicate anonymous nodes.
    }
  }

  return $data;
}

/**
 * Populate Organization schema using site details and theme logo preferences.
 *
 * @param array  $data      Existing JSON-LD data.
 * @param string $site_url  Site home URL.
 * @param string $site_name Site name.
 * @return array
 */
function emindy_schema_add_organization( $data, $site_url, $site_name ) {
  $logo_id  = get_theme_mod( 'custom_logo' );
  $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
  $same_as  = array_values(
    array_filter(
      array(
        'https://www.youtube.com/@emindy_official',
        // Social channels to enable when ready.
        // 'https://www.instagram.com/emindy_official',
        // 'https://www.tiktok.com/@emindy_official',
        // 'https://twitter.com/emindy_official', Placeholder.
      )
    )
  );

  $data['Organization'] = array(
    '@type'  => 'Organization',
    '@id'    => $site_url . '#org',
    'name'   => $site_name,
    'url'    => $site_url,
    'logo'   => $logo_url ? array(
      '@type' => 'ImageObject',
      'url'   => $logo_url,
    ) : null,
    'sameAs' => $same_as ? $same_as : null,
  );

  return $data;
}

/**
 * Add WebSite schema enriched with a SearchAction for on-site search.
 *
 * @param array  $data       Existing JSON-LD data.
 * @param string $site_url   Site home URL.
 * @param string $site_name  Site name.
 * @param string $inLanguage Two-letter language code for schema output.
 * @return array
 */
function emindy_schema_add_search_action( $data, $site_url, $site_name, $inLanguage ) {
  $data['WebSite'] = array(
    '@type'           => 'WebSite',
    '@id'             => $site_url . '#website',
    'url'             => $site_url,
    'name'            => $site_name,
    'inLanguage'      => $inLanguage,
    'publisher'       => array( '@id' => $site_url . '#org' ),
    'potentialAction' => array(
      '@type'       => 'SearchAction',
      'target'      => $site_url . '?s={search_term_string}',
      'query-input' => 'required name=search_term_string',
    ),
  );

  return $data;
}

/**
 * Build archive CollectionPage and ItemList schema without duplicating Rank Math output.
 *
 * @param array  $data       Existing JSON-LD data.
 * @param string $site_url   Site home URL.
 * @param string $inLanguage Two-letter language code for schema output.
 * @return array
 */
function emindy_schema_handle_archives( $data, $site_url, $inLanguage ) {
  $archive_url = home_url( add_query_arg( array() ) );

  if ( empty( $data['WebPage'] ) ) {
    $data['WebPage'] = array(
      '@type'      => 'CollectionPage',
      '@id'        => trailingslashit( $archive_url ) . '#collection',
      'name'       => wp_strip_all_tags( get_the_archive_title() ),
      'inLanguage' => $inLanguage,
      'isPartOf'   => array( '@id' => $site_url . '#website' ),
    );
  }

  if ( empty( $data['ItemList'] ) ) {
    global $wp_query;

    if ( isset( $wp_query->posts ) && is_array( $wp_query->posts ) && ! empty( $wp_query->posts ) ) {
      $position = 1;
      $items    = array();

      foreach ( $wp_query->posts as $p ) {
        $items[] = array(
          '@type'    => 'ListItem',
          'position' => $position++,
          'url'      => get_permalink( $p ),
        );

        if ( $position > 11 ) {
          break; // Keep compact.
        }
      }

      $data['ItemList'] = array(
        '@type'           => 'ItemList',
        'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
        'numberOfItems'   => count( $items ),
        'itemListElement' => $items,
      );
    }
  }

  return $data;
}

/**
 * Add VideoObject schema for em_video posts using the central builder.
 *
 * @param array   $data     Existing JSON-LD data.
 * @param WP_Post $post_obj Post object for the current singular.
 * @return array
 */
function emindy_schema_add_em_video( $data, $post_obj ) {
  $video_schema = \EMINDY\Core\Schema::build_video_schema( $post_obj );

  if ( $video_schema ) {
    $data['VideoObject'] = $video_schema;
  }

  return $data;
}

/**
 * Add HowTo schema for em_exercise posts using the central builder.
 *
 * @param array   $data     Existing JSON-LD data.
 * @param WP_Post $post_obj Post object for the current singular.
 * @return array
 */
function emindy_schema_add_em_exercise( $data, $post_obj ) {
  $howto_schema = \EMINDY\Core\Schema::build_exercise_howto_schema( $post_obj );

  if ( $howto_schema ) {
    $data['HowTo'] = $howto_schema;
  }

  return $data;
}

/**
 * Add Article schema for em_article posts using the central builder.
 *
 * @param array   $data     Existing JSON-LD data.
 * @param WP_Post $post_obj Post object for the current singular.
 * @return array
 */
function emindy_schema_add_em_article( $data, $post_obj ) {
  $article_schema = \EMINDY\Core\Schema::build_article_schema( $post_obj );

  if ( $article_schema ) {
    $data['Article'] = $article_schema;
  }

  return $data;
}
add_filter( 'rank_math/json_ld', 'emindy_filter_rank_math_json_ld', 10, 2 );


// ===== Helpers =====

if ( ! function_exists( 'emindy_seconds_from_ts' ) ) {
  /**
   * Convert a time string (mm:ss or hh:mm:ss) to seconds.
   *
   * @param string|int $t Time string or integer seconds.
   * @return int
   */
  function emindy_seconds_from_ts( $t ) {
    if ( preg_match( '/^\d+$/', (string) $t ) ) {
      return (int) $t;
    }

    if ( strpos( (string) $t, ':' ) !== false ) {
      $parts = array_reverse( array_map( 'intval', explode( ':', $t ) ) );
      $sec   = 0;

      if ( isset( $parts[0] ) ) {
        $sec += $parts[0];
      }
      if ( isset( $parts[1] ) ) {
        $sec += $parts[1] * 60;
      }
      if ( isset( $parts[2] ) ) {
        $sec += $parts[2] * 3600;
      }

      return $sec;
    }

    return 0;
  }
}

if ( ! function_exists( 'emindy_iso8601_duration' ) ) {
  /**
   * Convert seconds to ISO 8601 duration format.
   *
   * @param int $seconds Seconds to convert.
   * @return string
   */
  function emindy_iso8601_duration( $seconds ) {
    $seconds = max( 0, (int) $seconds );
    $h       = floor( $seconds / 3600 );
    $m       = floor( ( $seconds % 3600 ) / 60 );
    $s       = $seconds % 60;
    $str     = 'PT';

    if ( $h ) {
      $str .= $h . 'H';
    }
    if ( $m ) {
      $str .= $m . 'M';
    }
    if ( $s || ( ! $h && ! $m ) ) {
      $str .= $s . 'S';
    }

    return $str;
  }
}

if ( ! function_exists( 'emindy_extract_headings' ) ) {
  /**
   * Extract headings from HTML content.
   *
   * @param string $html HTML content.
   * @param array  $tags Heading tags to extract.
   * @return array
   */
  function emindy_extract_headings( $html, $tags = array( 'h2', 'h3' ) ) {
    $out = array();

    if ( ! $html ) {
      return $out;
    }

    libxml_use_internal_errors( true );
    $dom = new DOMDocument( '1.0', 'UTF-8' );
    $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();
    $xpath    = new DOMXPath( $dom );
    $selector = implode(
    '|',
    array_map(
        function ( $t ) {
            return '//' . $t;
        },
        $tags
    )
);
    $nodes    = $xpath->query( $selector );

    if ( $nodes && $nodes->length ) {
      foreach ( $nodes as $n ) {
        $txt = trim( $n->textContent );

        if ( '' !== $txt ) {
          $out[] = $txt;
        }

        if ( count( $out ) >= 10 ) {
          break;
        }
      }
    }

    return $out;
  }
}

if ( ! function_exists( 'emindy_array_filter_recursive' ) ) {
  /**
   * Recursively filter empty and null values from an array.
   *
   * @param array $input Array to filter.
   * @return array
   */
  function emindy_array_filter_recursive( $input ) {
    foreach ( $input as $k => $v ) {
      if ( is_array( $v ) ) {
        $input[ $k ] = emindy_array_filter_recursive( $v );
      }
    }

    return array_filter(
      $input,
      function ( $v ) {
        if ( null === $v ) {
          return false;
        }
        if ( is_array( $v ) && empty( $v ) ) {
          return false;
        }

        return true;
      }
    );
  }
}
