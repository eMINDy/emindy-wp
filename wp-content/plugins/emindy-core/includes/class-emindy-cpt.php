<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class CPT {
	public static function register_all() {
		self::exercise();
		self::video();
		self::article();
	}

	protected static function exercise() {
        register_post_type( 'em_exercise', [
            'labels' => [
                'name'          => __( 'Exercises', 'emindy-core' ),
                'singular_name' => __( 'Exercise', 'emindy-core' ),
            ],
            'public'       => true,
            'show_in_rest' => true,
            'menu_position'=> 21,
            'menu_icon'    => 'dashicons-universal-access-alt',
            'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
            'taxonomies'   => [ 'topic','technique','duration','format','use_case','level','a11y_feature' ],
            /*
             * Custom rewrite structure:
             *  - Single exercises live at /exercise/{slug}/
             *  - Archive lives at /exercise-library/
             */
            'has_archive'  => 'exercise-library',
            'rewrite'      => [ 'slug' => 'exercise', 'with_front' => false ],
        ] );
	}

	protected static function video() {
        register_post_type( 'em_video', [
            'labels' => [
                'name'          => __( 'Videos', 'emindy-core' ),
                'singular_name' => __( 'Video', 'emindy-core' ),
            ],
            'public'        => true,
            'show_in_rest'  => true,
            'menu_position' => 22,
            'menu_icon'     => 'dashicons-video-alt3',
            'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
            'taxonomies'    => [ 'topic','technique','duration','format','use_case','level','a11y_feature' ],
            /*
             * Custom rewrite structure:
             *  - Single videos live at /video/{slug}/
             *  - Archive lives at /video-library/
             */
            'has_archive'   => 'video-library',
            'rewrite'       => [ 'slug' => 'video', 'with_front' => false ],
        ] );
	}

	protected static function article() {
        register_post_type( 'em_article', [
            'labels' => [
                'name'          => __( 'Articles', 'emindy-core' ),
                'singular_name' => __( 'Article', 'emindy-core' ),
            ],
            'public'        => true,
            'show_in_rest'  => true,
            'menu_position' => 23,
            'menu_icon'     => 'dashicons-media-text',
            'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
            'taxonomies'    => [ 'topic','technique','duration','format','use_case','level','a11y_feature' ],
            /*
             * Custom rewrite structure:
             *  - Single articles live at /article/{slug}/
             *  - Archive lives at /article-library/
             */
            'has_archive'   => 'article-library',
            'rewrite'       => [ 'slug' => 'article', 'with_front' => false ],
        ] );
	}
}
