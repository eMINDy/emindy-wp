<?php
namespace EMINDY\Core;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) exit;

class Schema {
/**
 * Determine language code for JSON-LD output.
 *
 * @return string
 */
protected static function language_code() {
if ( function_exists( 'pll_current_language' ) ) {
$slug = pll_current_language( 'slug' );
if ( $slug ) {
return $slug;
}
}

$locale = get_locale();
return $locale ? substr( $locale, 0, 2 ) : 'en';
}

/**
 * Build HowTo schema for an exercise post.
 *
 * @param WP_Post $post Exercise post object.
 * @return array|null JSON-LD array or null if not enough data.
 */
public static function build_exercise_howto_schema( WP_Post $post ) {
$permalink = get_permalink( $post );
$title     = get_the_title( $post );
$desc_raw  = $post->post_excerpt ?: $post->post_content;
$desc      = $desc_raw ? wp_trim_words( wp_strip_all_tags( $desc_raw ), 60, '…' ) : '';
$image     = get_the_post_thumbnail_url( $post, 'full' );
$in_lang   = static::language_code();
$site_id   = home_url( '/' ) . '#website';
$org_id    = home_url( '/' ) . '#org';

$steps_meta = json_decode_safe( get_post_meta( $post->ID, 'em_steps_json', true ) );
$steps      = [];

if ( is_array( $steps_meta ) ) {
foreach ( $steps_meta as $index => $step_value ) {
$step_name = '';
$duration  = null;

if ( is_string( $step_value ) ) {
$step_name = $step_value;
} elseif ( is_array( $step_value ) ) {
$step_name = $step_value['label'] ?? $step_value['title'] ?? '';
$duration  = isset( $step_value['duration'] ) ? (int) $step_value['duration'] : null;
}

$step_name = sanitize_text_field( $step_name );
if ( '' === $step_name ) {
continue;
}

$steps[] = array_filter(
[
'@type'       => 'HowToStep',
'position'    => $index + 1,
'name'        => $step_name,
'timeRequired'=> $duration ? emindy_iso8601_duration( $duration ) : null,
],
static function ( $value ) {
return null !== $value;
}
);
}
}

if ( empty( $steps ) ) {
return null;
}

$total_seconds   = (int) get_post_meta( $post->ID, 'em_total_seconds', true );
$prep_seconds    = (int) get_post_meta( $post->ID, 'em_prep_seconds', true );
$perform_seconds = (int) get_post_meta( $post->ID, 'em_perform_seconds', true );

$build_list = static function ( $raw ) {
if ( ! $raw ) {
return [];
}

$list = $raw;
if ( is_string( $raw ) ) {
$list = json_decode( $raw, true );
if ( JSON_ERROR_NONE !== json_last_error() ) {
$list = explode( ',', $raw );
}
}

if ( ! is_array( $list ) ) {
return [];
}

return array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', $list ) ) ) );
};

$supplies = $build_list( get_post_meta( $post->ID, 'em_supplies', true ) );
$tools    = $build_list( get_post_meta( $post->ID, 'em_tools', true ) );
$yield    = get_post_meta( $post->ID, 'em_yield', true );

$schema = [
'@type'       => 'HowTo',
'@id'         => $permalink . '#howto',
'name'        => $title,
'description' => $desc,
'inLanguage'  => $in_lang,
'url'         => $permalink,
'publisher'   => [ '@id' => $org_id ],
'isPartOf'    => [ '@id' => $site_id ],
'image'       => $image ? [ '@type' => 'ImageObject', 'url' => $image ] : null,
'step'        => $steps,
'totalTime'   => $total_seconds ? emindy_iso8601_duration( $total_seconds ) : null,
'prepTime'    => $prep_seconds ? emindy_iso8601_duration( $prep_seconds ) : null,
'performTime' => $perform_seconds ? emindy_iso8601_duration( $perform_seconds ) : null,
'supply'      => $supplies ? array_map( static function ( $item ) {
return [ '@type' => 'HowToSupply', 'name' => $item ];
}, $supplies ) : null,
'tool'        => $tools ? array_map( static function ( $item ) {
return [ '@type' => 'HowToTool', 'name' => $item ];
}, $tools ) : null,
'yield'       => $yield ? sanitize_text_field( $yield ) : null,
];

return function_exists( 'emindy_array_filter_recursive' ) ? emindy_array_filter_recursive( $schema ) : $schema;
}

/**
 * Build VideoObject schema for a video post.
 *
 * @param WP_Post $post Video post object.
 * @return array|null JSON-LD array or null if not enough data.
 */
public static function build_video_schema( WP_Post $post ) {
$permalink = get_permalink( $post );
$title     = get_the_title( $post );
$desc_raw  = $post->post_excerpt ?: $post->post_content;
$desc      = $desc_raw ? wp_trim_words( wp_strip_all_tags( $desc_raw ), 60, '…' ) : '';
$image     = get_the_post_thumbnail_url( $post, 'full' );
$upload    = get_the_date( 'c', $post );
$in_lang   = static::language_code();
$site_id   = home_url( '/' ) . '#website';
$org_id    = home_url( '/' ) . '#org';

$embed = get_post_meta( $post->ID, '_em_embed_url', true );
if ( ! $embed ) {
$yt_id = get_post_meta( $post->ID, 'em_youtube_id', true );
if ( $yt_id ) {
$embed = 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $yt_id );
}
}

$duration_seconds = (int) get_post_meta( $post->ID, 'em_duration_sec', true );
$duration_iso     = $duration_seconds ? emindy_iso8601_duration( $duration_seconds ) : null;

$chapters = [];
$chap_meta = get_post_meta( $post->ID, 'em_chapters_json', true );
if ( $chap_meta ) {
$chapters_array = json_decode( $chap_meta, true );
if ( is_array( $chapters_array ) ) {
$position = 1;
foreach ( $chapters_array as $chapter ) {
$label = isset( $chapter['label'] ) ? wp_strip_all_tags( $chapter['label'] ) : '';
$start = isset( $chapter['t'] ) ? emindy_seconds_from_ts( $chapter['t'] ) : 0;

if ( '' === $label ) {
continue;
}

$chapters[] = [
'@type'       => 'Clip',
'name'        => $label,
'position'    => $position++,
'startOffset' => $start,
];
}
}
}

$schema = [
'@type'           => 'VideoObject',
'@id'             => $permalink . '#video',
'name'            => $title,
'description'     => $desc,
'thumbnailUrl'    => $image ?: null,
'uploadDate'      => $upload,
'inLanguage'      => $in_lang,
'url'             => $permalink,
'embedUrl'        => $embed ?: null,
'isFamilyFriendly'=> true,
'hasPart'         => $chapters ?: null,
'duration'        => $duration_iso,
'publisher'       => [ '@id' => $org_id ],
'isPartOf'        => [ '@id' => $site_id ],
];

return function_exists( 'emindy_array_filter_recursive' ) ? emindy_array_filter_recursive( $schema ) : $schema;
}

/**
 * Build Article/BlogPosting schema for an article post.
 *
 * @param WP_Post $post Article post object.
 * @return array JSON-LD array.
 */
public static function build_article_schema( WP_Post $post ) {
$permalink = get_permalink( $post );
$title     = get_the_title( $post );
$desc_raw  = $post->post_excerpt ?: $post->post_content;
$desc      = $desc_raw ? wp_trim_words( wp_strip_all_tags( $desc_raw ), 60, '…' ) : '';
$image     = get_the_post_thumbnail_url( $post, 'full' );
$author    = get_the_author_meta( 'display_name', $post->post_author );
$published = get_the_date( 'c', $post );
$modified  = get_the_modified_date( 'c', $post );
$in_lang   = static::language_code();
$site_id   = home_url( '/' ) . '#website';
$org_id    = home_url( '/' ) . '#org';

$schema = [
'@type'           => 'Article',
'@id'             => $permalink . '#article',
'headline'        => $title,
'description'     => $desc,
'inLanguage'      => $in_lang,
'image'           => $image ? [ '@type' => 'ImageObject', 'url' => $image ] : null,
'author'          => $author ? [ '@type' => 'Person', 'name' => $author ] : null,
'datePublished'   => $published,
'dateModified'    => $modified,
'url'             => $permalink,
'mainEntityOfPage'=> $permalink,
'publisher'       => [ '@id' => $org_id ],
'isPartOf'        => [ '@id' => $site_id ],
];

return function_exists( 'emindy_array_filter_recursive' ) ? emindy_array_filter_recursive( $schema ) : $schema;
}

/**
 * Emit fallback JSON-LD when Rank Math is not active.
 */
public static function output_jsonld() {
if ( is_admin() || ! is_singular() ) {
return;
}

$post = get_post();
if ( ! $post instanceof WP_Post ) {
return;
}

$schema = null;
switch ( $post->post_type ) {
case 'em_video':
$schema = static::build_video_schema( $post );
break;
case 'em_exercise':
$schema = static::build_exercise_howto_schema( $post );
break;
case 'em_article':
$schema = static::build_article_schema( $post );
break;
default:
return;
}

if ( empty( $schema ) || ! is_array( $schema ) ) {
return;
}

$schema = array_merge( [ '@context' => 'https://schema.org' ], $schema );
echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
}
}
