<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the current request is for a video CPT.
 *
 * @return bool
 */
function is_video() {
	return is_singular( 'em_video' );
}

/**
 * Determine whether the current request is for an exercise CPT.
 *
 * @return bool
 */
function is_exercise() {
	return is_singular( 'em_exercise' );
}

/**
 * Determine whether the current request is for an article CPT.
 *
 * @return bool
 */
function is_article() {
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
	return home_url( '/assessment-result/' );
}

/**
 * Safely decode a JSON string into an associative array.
 *
 * @param string $json JSON string (possibly slashed).
 *
 * @return array|null
 */
function json_decode_safe( $json ) {
	if ( ! is_string( $json ) ) {
		return null;
	}

	$json = trim( wp_unslash( $json ) );

	if ( '' === $json ) {
		return null;
	}

	$data = json_decode( $json, true );

	return ( JSON_ERROR_NONE === json_last_error() && is_array( $data ) ) ? $data : null;
}

/**
 * Generate a short text summary for schema/Open Graph usage.
 *
 * @param int|null $post_id Post ID to summarize. Defaults to current post.
 * @param int      $len     Maximum character length.
 *
 * @return string
 */
function safe_summary( $post_id = null, $len = 220 ) {
	$post_id = $post_id ? absint( $post_id ) : null;
	$post    = $post_id ? get_post( $post_id ) : get_post();

	if ( ! $post instanceof \WP_Post ) {
		return '';
	}

	$length = absint( $len );
	$text   = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
	$text   = preg_replace( '/\s+/', ' ', trim( $text ) );

	return mb_substr( $text, 0, $length );
}
