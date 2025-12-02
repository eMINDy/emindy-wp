<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Content_Inject {
	public static function register() {
		add_filter( 'the_content', [ __CLASS__, 'inject' ], 9 );
	}

	public static function inject( $content ) {
		if ( is_singular('em_exercise') && in_the_loop() && is_main_query() ) {
			$content = '[em_player]' . "\n\n" . $content;
		}
		if ( is_singular('em_video') && in_the_loop() && is_main_query() ) {
			$content = $content . "\n\n" . '[em_video_chapters]';
		}
		return $content;
	}
}
