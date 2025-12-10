<?php
// Basic WordPress-like stubs for isolated testing.

if ( ! class_exists( 'WP_Post' ) ) {
    class WP_Post {
        public $ID;
        public $post_excerpt = '';
        public $post_content = '';
        public $post_title = '';
        public $post_type = '';

        public function __construct( $id = 0 ) {
            $this->ID = $id;
        }
    }
}

if ( ! class_exists( 'WP_Query' ) ) {
    class WP_Query {
        public static $next_results = [];
        public static $last_args    = [];

        private $posts = [];
        private $index = 0;

        public function __construct( $args = [] ) {
            self::$last_args[] = $args;
            $this->posts       = array_shift( self::$next_results ) ?? [];
            $this->index       = 0;
        }

        public function have_posts() {
            return $this->index < count( $this->posts );
        }

        public function the_post() {
            global $post;
            $post = $this->posts[ $this->index ] ?? null;
            $this->index++;
        }
    }
}

// Simple global stores to configure return values in tests.
$GLOBALS['wp_testing_meta']          = [];
$GLOBALS['wp_testing_terms']         = [];
$GLOBALS['wp_testing_terms_by_slug'] = [];

if ( ! function_exists( 'get_post' ) ) {
    function get_post( $post_input = null ) {
        global $post;
        return $post_input instanceof WP_Post ? $post_input : ( $post ?? null );
    }
}

if ( ! function_exists( 'get_post_type' ) ) {
    function get_post_type( $post_input = null ) {
        global $post;
        $obj = $post_input instanceof WP_Post ? $post_input : ( $post ?? null );
        return $obj && ! empty( $obj->post_type ) ? $obj->post_type : 'post';
    }
}

if ( ! function_exists( 'get_permalink' ) ) {
    function get_permalink( $post_input = null ) {
        $id = $post_input instanceof WP_Post ? $post_input->ID : ( $GLOBALS['post']->ID ?? 0 );
        return 'https://example.com/?p=' . $id;
    }
}

if ( ! function_exists( 'get_the_title' ) ) {
    function get_the_title( $post_input = null ) {
        $obj = $post_input instanceof WP_Post ? $post_input : ( $GLOBALS['post'] ?? null );
        return $obj ? $obj->post_title : '';
    }
}

if ( ! function_exists( 'get_the_excerpt' ) ) {
    function get_the_excerpt( $post_input = null ) {
        $obj = $post_input instanceof WP_Post ? $post_input : ( $GLOBALS['post'] ?? null );
        return $obj ? $obj->post_excerpt : '';
    }
}

if ( ! function_exists( 'get_the_post_thumbnail_url' ) ) {
    function get_the_post_thumbnail_url( $post = null, $size = 'full' ) {
        return '';
    }
}

if ( ! function_exists( 'get_the_date' ) ) {
    function get_the_date( $format = 'c', $post = null ) {
        return '2024-01-01T00:00:00+00:00';
    }
}

if ( ! function_exists( 'get_post_meta' ) ) {
    function get_post_meta( $post_id, $key = '', $single = false ) {
        $meta = $GLOBALS['wp_testing_meta'];
        return $meta[ $post_id ][ $key ] ?? '';
    }
}

if ( ! function_exists( 'wp_get_post_terms' ) ) {
    function wp_get_post_terms( $post_id, $taxonomy, $args = [] ) {
        $terms = $GLOBALS['wp_testing_terms'];
        return $terms[ $post_id ][ $taxonomy ] ?? [];
    }
}

if ( ! function_exists( 'wp_get_object_terms' ) ) {
    function wp_get_object_terms( $post_id, $taxonomy, $args = [] ) {
        return wp_get_post_terms( $post_id, $taxonomy, $args );
    }
}

if ( ! function_exists( 'get_term_by' ) ) {
    function get_term_by( $field, $value, $taxonomy ) {
        if ( 'slug' !== $field ) {
            return false;
        }

        $lookup = $GLOBALS['wp_testing_terms_by_slug'];
        if ( isset( $lookup[ $taxonomy ][ $value ] ) ) {
            return (object) [ 'term_id' => $lookup[ $taxonomy ][ $value ], 'slug' => $value ];
        }

        return false;
    }
}

if ( ! function_exists( 'shortcode_atts' ) ) {
    function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
        return array_merge( $pairs, array_intersect_key( $atts, $pairs ) );
    }
}

if ( ! function_exists( 'add_shortcode' ) ) {
    function add_shortcode( $tag, $callback ) {}
}

if ( ! function_exists( 'shortcode_exists' ) ) {
    function shortcode_exists( $tag ) {
        return false;
    }
}

if ( ! function_exists( 'do_shortcode' ) ) {
    function do_shortcode( $content ) {
        return $content;
    }
}

if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        $key = strtolower( $key );
        return preg_replace( '/[^a-z0-9_\-]/', '', $key );
    }
}

if ( ! function_exists( 'sanitize_title' ) ) {
    function sanitize_title( $title ) {
        $title = strtolower( preg_replace( '/[^a-z0-9]+/', '-', (string) $title ) );
        return trim( $title, '-' );
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return is_string( $str ) ? trim( strip_tags( $str ) ) : '';
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return is_string( $text ) ? $text : '';
    }
}

if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = null ) {
        return esc_html( $text );
    }
}

if ( ! function_exists( 'esc_attr__' ) ) {
    function esc_attr__( $text, $domain = null ) {
        return esc_html( $text );
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return $url;
    }
}

if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $url ) {
        return $url;
    }
}

if ( ! function_exists( 'wp_trim_words' ) ) {
    function wp_trim_words( $text, $num_words = 55, $more = '...' ) {
        return $text;
    }
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    function wp_strip_all_tags( $text ) {
        return strip_tags( (string) $text );
    }
}

if ( ! function_exists( 'get_locale' ) ) {
    function get_locale() {
        return 'en_US';
    }
}

if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '/' ) {
        return 'https://example.com' . $path;
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) {
        return json_encode( $data );
    }
}

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return $value;
    }
}

if ( ! function_exists( 'shortcode_unautop' ) ) {
    function shortcode_unautop( $html ) {
        return $html;
    }
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
    function wp_reset_postdata() {}
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return false;
    }
}

if ( ! function_exists( 'emindy_iso8601_duration' ) ) {
    function emindy_iso8601_duration( $seconds ) {
        return 'PT' . (int) $seconds . 'S';
    }
}

if ( ! function_exists( 'emindy_seconds_from_ts' ) ) {
    function emindy_seconds_from_ts( $ts ) {
        return (int) $ts;
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = null ) {
        return $text;
    }
}
