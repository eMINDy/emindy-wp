<?php
/**
 * Custom post types for the eMINDy platform.
 *
 * @package EmindyCore
 */

declare( strict_types=1 );

namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers custom post types used by the eMINDy platform.
 *
 * Videos, exercises and articles share a common taxonomy set so they can be
 * surfaced consistently across the site and via the REST API.
 */
final class CPT {

	/**
	 * Plugin text domain.
	 *
	 * @var string
	 */
	private const TEXT_DOMAIN = 'emindy-core';

	/**
	 * Taxonomies shared across all eMINDy post types.
	 *
	 * @var string[]
	 */
	private const SHARED_TAXONOMIES = [
		'topic',
		'technique',
		'duration',
		'format',
		'use_case',
		'level',
		'a11y_feature',
	];

	/**
	 * Register all custom post types.
	 *
	 * Intended to be called on the `init` hook.
	 *
	 * @return void
	 */
	public static function register_all(): void {
		if ( ! function_exists( 'register_post_type' ) ) {
			return;
		}

		self::register_exercise();
		self::register_video();
		self::register_article();
	}

	/**
	 * Base arguments shared by all eMINDy post types.
	 *
	 * @param array<string, mixed> $overrides Arguments to override defaults.
	 * @return array<string, mixed>
	 */
	private static function get_common_args( array $overrides = [] ): array {
		$defaults = [
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'publicly_queryable'    => true,
			'exclude_from_search'   => false,
			'hierarchical'          => false,
			'supports'              => [
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
			],
			'taxonomies'            => self::SHARED_TAXONOMIES,
			'has_archive'           => true,
			'rewrite'               => [
				'with_front' => false,
			],
			'show_in_rest'          => true,
			'rest_controller_class' => '\WP_REST_Posts_Controller',
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'delete_with_user'      => false,
		];

		/**
		 * Filter the base arguments used for all eMINDy post types.
		 *
		 * @param array<string, mixed> $defaults  Default arguments.
		 * @param array<string, mixed> $overrides Overrides passed in.
		 */
		$defaults = apply_filters( 'emindy_cpt_common_args', $defaults, $overrides );

		return array_replace_recursive( $defaults, $overrides );
	}

	/**
	 * Register the Exercise post type.
	 *
	 * @return void
	 */
	private static function register_exercise(): void {
		$labels = [
			'name'                  => _x( 'Exercises', 'post type general name', self::TEXT_DOMAIN ),
			'singular_name'         => _x( 'Exercise', 'post type singular name', self::TEXT_DOMAIN ),
			'menu_name'             => _x( 'Exercises', 'admin menu', self::TEXT_DOMAIN ),
			'name_admin_bar'        => _x( 'Exercise', 'add new on admin bar', self::TEXT_DOMAIN ),
			'add_new'               => _x( 'Add New', 'exercise', self::TEXT_DOMAIN ),
			'add_new_item'          => __( 'Add New Exercise', self::TEXT_DOMAIN ),
			'new_item'              => __( 'New Exercise', self::TEXT_DOMAIN ),
			'edit_item'             => __( 'Edit Exercise', self::TEXT_DOMAIN ),
			'view_item'             => __( 'View Exercise', self::TEXT_DOMAIN ),
			'all_items'             => __( 'All Exercises', self::TEXT_DOMAIN ),
			'search_items'          => __( 'Search Exercises', self::TEXT_DOMAIN ),
			'parent_item_colon'     => __( 'Parent Exercise:', self::TEXT_DOMAIN ),
			'not_found'             => __( 'No exercises found.', self::TEXT_DOMAIN ),
			'not_found_in_trash'    => __( 'No exercises found in Trash.', self::TEXT_DOMAIN ),
			'archives'              => __( 'Exercise Library', self::TEXT_DOMAIN ),
			'featured_image'        => __( 'Exercise Image', self::TEXT_DOMAIN ),
			'set_featured_image'    => __( 'Set exercise image', self::TEXT_DOMAIN ),
			'remove_featured_image' => __( 'Remove exercise image', self::TEXT_DOMAIN ),
			'use_featured_image'    => __( 'Use as exercise image', self::TEXT_DOMAIN ),
		];

		$args = self::get_common_args(
			[
				'labels'        => $labels,
				'menu_position' => 21,
				'menu_icon'     => 'dashicons-universal-access-alt',
				'has_archive'   => 'exercise-library',
				'rewrite'       => [
					'slug'       => 'exercise',
					'with_front' => false,
				],
				'show_in_rest'  => true,
				'rest_base'     => 'exercises',
			]
		);

		/**
		 * Filter the arguments used to register the Exercise post type.
		 *
		 * @param array<string, mixed> $args Arguments for the post type.
		 */
		$args = apply_filters( 'emindy_cpt_exercise_args', $args );

		register_post_type( 'em_exercise', $args );
	}

	/**
	 * Register the Video post type.
	 *
	 * @return void
	 */
	private static function register_video(): void {
		$labels = [
			'name'                  => _x( 'Videos', 'post type general name', self::TEXT_DOMAIN ),
			'singular_name'         => _x( 'Video', 'post type singular name', self::TEXT_DOMAIN ),
			'menu_name'             => _x( 'Videos', 'admin menu', self::TEXT_DOMAIN ),
			'name_admin_bar'        => _x( 'Video', 'add new on admin bar', self::TEXT_DOMAIN ),
			'add_new'               => _x( 'Add New', 'video', self::TEXT_DOMAIN ),
			'add_new_item'          => __( 'Add New Video', self::TEXT_DOMAIN ),
			'new_item'              => __( 'New Video', self::TEXT_DOMAIN ),
			'edit_item'             => __( 'Edit Video', self::TEXT_DOMAIN ),
			'view_item'             => __( 'View Video', self::TEXT_DOMAIN ),
			'all_items'             => __( 'All Videos', self::TEXT_DOMAIN ),
			'search_items'          => __( 'Search Videos', self::TEXT_DOMAIN ),
			'parent_item_colon'     => __( 'Parent Video:', self::TEXT_DOMAIN ),
			'not_found'             => __( 'No videos found.', self::TEXT_DOMAIN ),
			'not_found_in_trash'    => __( 'No videos found in Trash.', self::TEXT_DOMAIN ),
			'archives'              => __( 'Video Library', self::TEXT_DOMAIN ),
			'featured_image'        => __( 'Video Image', self::TEXT_DOMAIN ),
			'set_featured_image'    => __( 'Set video image', self::TEXT_DOMAIN ),
			'remove_featured_image' => __( 'Remove video image', self::TEXT_DOMAIN ),
			'use_featured_image'    => __( 'Use as video image', self::TEXT_DOMAIN ),
		];

		$args = self::get_common_args(
			[
				'labels'        => $labels,
				'menu_position' => 22,
				'menu_icon'     => 'dashicons-video-alt3',
				'has_archive'   => 'video-library',
				'rewrite'       => [
					'slug'       => 'video',
					'with_front' => false,
				],
				'show_in_rest'  => true,
				'rest_base'     => 'videos',
			]
		);

		/**
		 * Filter the arguments used to register the Video post type.
		 *
		 * @param array<string, mixed> $args Arguments for the post type.
		 */
		$args = apply_filters( 'emindy_cpt_video_args', $args );

		register_post_type( 'em_video', $args );
	}

	/**
	 * Register the Article post type.
	 *
	 * @return void
	 */
	private static function register_article(): void {
		$labels = [
			'name'                  => _x( 'Articles', 'post type general name', self::TEXT_DOMAIN ),
			'singular_name'         => _x( 'Article', 'post type singular name', self::TEXT_DOMAIN ),
			'menu_name'             => _x( 'Articles', 'admin menu', self::TEXT_DOMAIN ),
			'name_admin_bar'        => _x( 'Article', 'add new on admin bar', self::TEXT_DOMAIN ),
			'add_new'               => _x( 'Add New', 'article', self::TEXT_DOMAIN ),
			'add_new_item'          => __( 'Add New Article', self::TEXT_DOMAIN ),
			'new_item'              => __( 'New Article', self::TEXT_DOMAIN ),
			'edit_item'             => __( 'Edit Article', self::TEXT_DOMAIN ),
			'view_item'             => __( 'View Article', self::TEXT_DOMAIN ),
			'all_items'             => __( 'All Articles', self::TEXT_DOMAIN ),
			'search_items'          => __( 'Search Articles', self::TEXT_DOMAIN ),
			'parent_item_colon'     => __( 'Parent Article:', self::TEXT_DOMAIN ),
			'not_found'             => __( 'No articles found.', self::TEXT_DOMAIN ),
			'not_found_in_trash'    => __( 'No articles found in Trash.', self::TEXT_DOMAIN ),
			'archives'              => __( 'Article Library', self::TEXT_DOMAIN ),
			'featured_image'        => __( 'Article Image', self::TEXT_DOMAIN ),
			'set_featured_image'    => __( 'Set article image', self::TEXT_DOMAIN ),
			'remove_featured_image' => __( 'Remove article image', self::TEXT_DOMAIN ),
			'use_featured_image'    => __( 'Use as article image', self::TEXT_DOMAIN ),
		];

		$args = self::get_common_args(
			[
				'labels'        => $labels,
				'menu_position' => 23,
				'menu_icon'     => 'dashicons-media-text',
				'has_archive'   => 'article-library',
				'rewrite'       => [
					'slug'       => 'article',
					'with_front' => false,
				],
				'show_in_rest'  => true,
				'rest_base'     => 'articles',
			]
		);

		/**
		 * Filter the arguments used to register the Article post type.
		 *
		 * @param array<string, mixed> $args Arguments for the post type.
		 */
		$args = apply_filters( 'emindy_cpt_article_args', $args );

		register_post_type( 'em_article', $args );
	}
}
