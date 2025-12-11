<?php
/**
 * Helper functions for the eMINDy Core plugin.
 *
 * @package EmindyCore
 */

namespace EMINDY\Core {

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the current context is displaying an eMINDy video.
 *
 * When called without arguments this mirrors is_singular( 'em_video' ) for
 * the main query. When a post object / ID is provided it checks its post type.
 *
 * @param int|\WP_Post|null $post Optional post to check.
 * @return bool
 */
function is_video( $post = null ) {
	if ( null !== $post ) {
		$post = $post instanceof \WP_Post ? $post : get_post( $post );

		return ( $post instanceof \WP_Post ) && 'em_video' === $post->post_type;
	}

	return is_singular( 'em_video' );
}

/**
 * Determine whether the current context is displaying an eMINDy exercise.
 *
 * @param int|\WP_Post|null $post Optional post to check.
 * @return bool
 */
function is_exercise( $post = null ) {
	if ( null !== $post ) {
		$post = $post instanceof \WP_Post ? $post : get_post( $post );

		return ( $post instanceof \WP_Post ) && 'em_exercise' === $post->post_type;
	}

	return is_singular( 'em_exercise' );
}

/**
 * Determine whether the current context is displaying an eMINDy article.
 *
 * @param int|\WP_Post|null $post Optional post to check.
 * @return bool
 */
function is_article( $post = null ) {
	if ( null !== $post ) {
		$post = $post instanceof \WP_Post ? $post : get_post( $post );

		return ( $post instanceof \WP_Post ) && 'em_article' === $post->post_type;
	}

	return is_singular( 'em_article' );
}

/**
 * Base URL for the shared assessment result page.
 *
 * Note: The slug is currently `assessment-result` and must match
 * the page used to render [em_assessment_result].
 *
 * @return string
 */
function assessment_result_base_url() {
	$url = home_url( '/assessment-result/' );

	/**
	 * Filter the base URL used for the shared assessment result page.
	 *
	 * @param string $url Base URL.
	 */
	return apply_filters( 'emindy_assessment_result_base_url', $url );
}

/**
 * Safely decode a JSON string into an associative array.
 *
 * Accepts an already-decoded array and returns it as-is.
 *
 * @param string|array $json JSON string (possibly slashed) or array.
 * @return array|null
 */
function json_decode_safe( $json ) {
	if ( is_array( $json ) ) {
		return $json;
	}

	if ( ! is_string( $json ) ) {
		return null;
	}

	$json = trim( wp_unslash( $json ) );

	if ( '' === $json ) {
		return null;
	}

	$data = json_decode( $json, true );

	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
		return null;
	}

	return $data;
}

/**
 * Generate a short text summary for schema/Open Graph usage.
 *
 * @param int|null $post_id Post ID to summarize. Defaults to current post.
 * @param int      $len     Maximum character length.
 * @return string
 */
function safe_summary( $post_id = null, $len = 220 ) {
	$post_id = $post_id ? absint( $post_id ) : null;
	$post    = $post_id ? get_post( $post_id ) : get_post();

	if ( ! $post instanceof \WP_Post ) {
		return '';
	}

	$length = max( 1, (int) $len );

	$text = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
	$text = preg_replace( '/\s+/', ' ', trim( $text ) );

	if ( '' === $text ) {
		return '';
	}

	if ( function_exists( 'mb_substr' ) ) {
		$summary = mb_substr( $text, 0, $length );
	} else {
		$summary = substr( $text, 0, $length );
	}

	/**
	 * Filter the generated safe summary.
	 *
	 * @param string   $summary Summary text.
	 * @param \WP_Post $post    Post object.
	 * @param int      $length  Maximum length.
	 */
	return apply_filters( 'emindy_safe_summary', $summary, $post, $length );
}

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

	if ( false !== strpos( (string) $t, ':' ) ) {
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

} // End of namespace EMINDY\Core.

namespace {

if ( ! function_exists( 'emindy_seconds_from_ts' ) ) {
	/**
	 * Backwards compatible global wrapper for emindy_seconds_from_ts().
	 *
	 * @param string|int $t Time string or integer seconds.
	 * @return int
	 */
	function emindy_seconds_from_ts( $t ) {
		return \EMINDY\Core\emindy_seconds_from_ts( $t );
	}
}

if ( ! function_exists( 'emindy_iso8601_duration' ) ) {
	/**
	 * Backwards compatible global wrapper for emindy_iso8601_duration().
	 *
	 * @param int $seconds Seconds to convert.
	 * @return string
	 */
	function emindy_iso8601_duration( $seconds ) {
		return \EMINDY\Core\emindy_iso8601_duration( $seconds );
	}
}

if ( ! function_exists( 'emindy_array_filter_recursive' ) ) {
	/**
	 * Backwards compatible global wrapper for emindy_array_filter_recursive().
	 *
	 * @param array $input Array to filter.
	 * @return array
	 */
	function emindy_array_filter_recursive( $input ) {
		return \EMINDY\Core\emindy_array_filter_recursive( $input );
	}
}

}
