<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Content_Inject {
    /**
     * Register hooks.
     */
    public static function register() {
        add_filter( 'the_content', [ __CLASS__, 'inject' ], 9 );
    }

    /**
     * Inject shortcode blocks into exercise and video content.
     *
     * @param string $content The post content.
     *
     * @return string
     */
    public static function inject( $content ) {
        if ( ! is_string( $content ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        if ( is_singular( 'em_exercise' ) ) {
            $content = '[em_player]' . "\n\n" . $content;
        }

        if ( is_singular( 'em_video' ) ) {
            $content = $content . "\n\n" . '[em_video_chapters]';
        }

        return $content;
    }
}
