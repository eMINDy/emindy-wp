<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Schema {
	public static function output_jsonld() {
		if ( is_admin() || ! is_singular() ) return;

		global $post;
		$type = $post->post_type;
		$json = null;

		if ( $type === 'em_video' ) {
			$json = [
				'@context' => 'https://schema.org',
                'inLanguage' => function_exists('pll_current_language') ? pll_current_language('slug') : get_bloginfo('language'),
				'@type'    => 'VideoObject',
				'name'     => get_the_title(),
				'description' => \EMINDY\Core\safe_summary( get_the_ID(), 220 ),
				'thumbnailUrl' => get_the_post_thumbnail_url( $post, 'large' ),
				'uploadDate'   => get_the_date( 'c' ),
				'url'          => get_permalink(),
			];
        } elseif ( $type === 'em_exercise' ) {
            /*
             * Build a rich HowTo schema for exercises.  Pull values from post
             * meta if available and fall back gracefully to sensible defaults.
             */
            $post_id = get_the_ID();
            $lang = function_exists('pll_current_language') ? pll_current_language('slug') : get_bloginfo('language');
            // Compute total, prep and perform times from meta values.  Convert
            // seconds into ISO 8601 durations using the helper defined in
            // includes/schema.php.  If no total time is specified, fall back to
            // the sum of step durations or omit.
            $total_seconds   = (int) get_post_meta( $post_id, 'em_total_seconds', true );
            $prep_seconds    = (int) get_post_meta( $post_id, 'em_prep_seconds', true );
            $perform_seconds = (int) get_post_meta( $post_id, 'em_perform_seconds', true );
            $steps_meta      = json_decode_safe( get_post_meta( $post_id, 'em_steps_json', true ) ) ?: [];
            if ( ! $total_seconds && is_array( $steps_meta ) ) {
                foreach ( $steps_meta as $st ) {
                    $total_seconds += (int) ( $st['duration'] ?? 0 );
                }
            }
            $total_iso   = $total_seconds ? emindy_iso8601_duration( $total_seconds ) : null;
            $prep_iso    = $prep_seconds  ? emindy_iso8601_duration( $prep_seconds )  : null;
            $perform_iso = $perform_seconds? emindy_iso8601_duration( $perform_seconds ) : null;
            // Parse supplies and tools.  Accept JSON arrays or comma‑separated
            // strings in the meta fields.  Always return an array of strings.
            $parse_list = static function( $raw ) {
                if ( ! $raw ) return [];
                $raw = is_string( $raw ) ? trim( $raw ) : '';
                if ( $raw === '' ) return [];
                if ( $raw[0] === '[' ) {
                    $arr = json_decode( $raw, true );
                    if ( json_last_error() === JSON_ERROR_NONE && is_array( $arr ) ) {
                        return array_filter( array_map( 'sanitize_text_field', $arr ) );
                    }
                }
                // Comma separated list
                $parts = array_map( 'trim', explode( ',', $raw ) );
                return array_filter( array_map( 'sanitize_text_field', $parts ) );
            };
            $supplies_raw = get_post_meta( $post_id, 'em_supplies', true );
            $tools_raw    = get_post_meta( $post_id, 'em_tools', true );
            $yield_raw    = get_post_meta( $post_id, 'em_yield', true );
            $supplies = $parse_list( $supplies_raw );
            $tools    = $parse_list( $tools_raw );
            // Build the HowTo schema
            $json = [
                '@context'    => 'https://schema.org',
                'inLanguage'  => $lang,
                '@type'       => 'HowTo',
                'name'        => get_the_title(),
                'description' => \EMINDY\Core\safe_summary( $post_id, 220 ),
                'totalTime'   => $total_iso,
                // Only add prepTime/performTime when provided
                'prepTime'    => $prep_iso,
                'performTime' => $perform_iso,
                'supply'      => $supplies ?: null,
                'tool'        => $tools   ?: null,
                'yield'       => $yield_raw ? sanitize_text_field( $yield_raw ) : null,
                'step'        => [],
            ];
            $position = 0;
            foreach ( $steps_meta as $s ) {
                $position++;
                $step_name = isset( $s['label'] ) ? (string) $s['label'] : '';
                $step_sec  = isset( $s['duration'] ) ? (int) $s['duration'] : 0;
                $json['step'][] = array_filter([
                    '@type'       => 'HowToStep',
                    'position'    => $position,
                    'name'        => sanitize_text_field( $step_name ),
                    'timeRequired'=> $step_sec ? emindy_iso8601_duration( $step_sec ) : null,
                ], function( $v ) { return $v !== null; } );
            }
            // Remove empty values recursively (nulls, empty arrays) to keep
            // output compact.  Use helper from includes/schema.php if available.
            if ( function_exists( 'emindy_array_filter_recursive' ) ) {
                $json = emindy_array_filter_recursive( $json );
            }
        } elseif ( $type === 'em_article' ) {
			$json = [
				'@context' => 'https://schema.org',
                'inLanguage' => function_exists('pll_current_language') ? pll_current_language('slug') : get_bloginfo('language'),
				'@type'    => 'Article',
				'headline' => get_the_title(),
				'description' => \EMINDY\Core\safe_summary( get_the_ID(), 220 ),
				'datePublished' => get_the_date('c'),
				'url' => get_permalink(),
			];
		}

		if ( $json ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $json ) . '</script>';
		}
	}
}
