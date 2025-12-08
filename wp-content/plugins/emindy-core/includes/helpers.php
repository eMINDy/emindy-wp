<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

function is_video()    { return is_singular('em_video'); }
function is_exercise() { return is_singular('em_exercise'); }
function is_article()  { return is_singular('em_article'); }

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

/** Safe JSON decode returning array|null */
function json_decode_safe( $json ) {
        if ( ! is_string( $json ) || $json === '' ) return null;
        $data = json_decode( $json, true );
        return ( json_last_error() === JSON_ERROR_NONE && is_array( $data ) ) ? $data : null;
}

/** Short text for schema/og */
function safe_summary( $post_id = null, $len = 220 ) {
	$p = $post_id ? get_post( $post_id ) : get_post();
	if ( ! $p ) return '';
	$txt = $p->post_excerpt ?: wp_strip_all_tags( $p->post_content );
	$txt = preg_replace('/\s+/',' ', trim($txt) );
	return mb_substr( $txt, 0, $len );
}
