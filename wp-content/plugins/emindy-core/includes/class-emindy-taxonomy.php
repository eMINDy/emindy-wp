<?php
namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles registration and seeding of eMINDy custom taxonomies.
 *
 * Taxonomies are shared across the eMINDy custom post types so that videos,
 * exercises and articles can be filtered consistently.
 */
class Taxonomy {

	/**
	 * Plugin text domain.
	 */
	private const TEXT_DOMAIN = 'emindy-core';

	/**
	 * Bootstrap taxonomy registration and default term seeding.
	 *
	 * Intended to be called on the `init` hook from the main plugin bootstrap.
	 *
	 * @return void
	 */
	public static function register_all(): void {
		self::register_taxonomies();
		self::seed_default_terms();
	}

	/**
	 * Register all custom taxonomies used on eMINDy content types.
	 *
	 * @return void
	 */
	protected static function register_taxonomies(): void {
		$taxonomies = [
			// id           => [ plural label,               singular label,             hierarchical, rewrite slug ].
			'topic'        => [ 'Topics', 'Topic', true, 'topics' ],
			'technique'    => [ 'Techniques', 'Technique', true, 'techniques' ],
			'duration'     => [ 'Durations', 'Duration', false, 'duration' ],
			'format'       => [ 'Formats', 'Format', false, 'format' ],
			'use_case'     => [ 'Use Cases', 'Use Case', true, 'use-case' ],
			'level'        => [ 'Levels', 'Level', false, 'level' ],
			'a11y_feature' => [ 'Accessibility Features', 'Accessibility Feature', false, 'a11y' ],
		];

		/**
		 * Filter the taxonomy configuration before registration.
		 *
		 * Allows themes or extensions to add/remove/adjust taxonomy definitions.
		 *
		 * @param array $taxonomies Associative array of taxonomy definitions.
		 */
		$taxonomies = apply_filters( 'emindy_taxonomies', $taxonomies );

		/**
		 * Filter the list of post types that eMINDy taxonomies attach to.
		 *
		 * @param string[] $post_types Post types that use the shared taxonomies.
		 */
		$post_types = apply_filters(
			'emindy_taxonomy_post_types',
			[ 'em_exercise', 'em_video', 'em_article' ]
		);

		foreach ( $taxonomies as $key => $args ) {
			if ( ! is_array( $args ) || count( $args ) < 4 ) {
				continue;
			}

			list( $plural, $singular, $hierarchical, $rewrite_slug ) = $args;

			$taxonomy_slug = sanitize_key( $key );
			$rewrite_slug  = sanitize_title( $rewrite_slug );

			$labels = self::build_labels( (string) $plural, (string) $singular, (bool) $hierarchical );

			register_taxonomy(
				$taxonomy_slug,
				(array) $post_types,
				[
					'labels'                => $labels,
					'public'                => true,
					'publicly_queryable'    => true,
					'show_ui'               => true,
					'show_in_nav_menus'     => true,
					'show_admin_column'     => true,
					'show_in_quick_edit'    => true,
					'show_in_rest'          => true,
					'hierarchical'          => (bool) $hierarchical,
					'rewrite'               => [
						'slug'       => $rewrite_slug,
						'with_front' => false,
					],
					// Let WordPress manage capabilities for now; can be customized via filters if needed.
					'capabilities'          => [],
					'meta_box_cb'           => null,
					'query_var'             => true,
				]
			);
		}
	}

	/**
	 * Build a complete set of labels for a taxonomy.
	 *
	 * @param string $plural       Plural label.
	 * @param string $singular     Singular label.
	 * @param bool   $hierarchical Whether taxonomy is hierarchical.
	 *
	 * @return array<string,string>
	 */
	protected static function build_labels( string $plural, string $singular, bool $hierarchical ): array {
		$td = self::TEXT_DOMAIN;

		$labels = [
			'name'                       => __( $plural, $td ),
			'singular_name'              => __( $singular, $td ),
			'menu_name'                  => __( $plural, $td ),
			'search_items'               => sprintf(
				/* translators: %s: plural taxonomy label. */
				__( 'Search %s', $td ),
				$plural
			),
			'all_items'                  => sprintf(
				/* translators: %s: plural taxonomy label. */
				__( 'All %s', $td ),
				$plural
			),
			'edit_item'                  => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Edit %s', $td ),
				$singular
			),
			'view_item'                  => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'View %s', $td ),
				$singular
			),
			'update_item'                => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Update %s', $td ),
				$singular
			),
			'add_new_item'               => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Add New %s', $td ),
				$singular
			),
			'new_item_name'              => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'New %s', $td ),
				$singular
			),
			'not_found'                  => sprintf(
				/* translators: %s: plural taxonomy label (lowercase). */
				__( 'No %s found.', $td ),
				strtolower( $plural )
			),
			'back_to_items'              => sprintf(
				/* translators: %s: plural taxonomy label (lowercase). */
				__( '← Back to %s', $td ),
				strtolower( $plural )
			),
		];

		if ( $hierarchical ) {
			$labels['parent_item']       = sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Parent %s', $td ),
				$singular
			);
			$labels['parent_item_colon'] = sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Parent %s:', $td ),
				$singular
			);
		} else {
			$labels['popular_items']              = sprintf(
				/* translators: %s: plural taxonomy label. */
				__( 'Popular %s', $td ),
				$plural
			);
			$labels['separate_items_with_commas'] = sprintf(
				/* translators: %s: plural taxonomy label (lowercase). */
				__( 'Separate %s with commas', $td ),
				strtolower( $plural )
			);
			$labels['add_or_remove_items']        = sprintf(
				/* translators: %s: plural taxonomy label (lowercase). */
				__( 'Add or remove %s', $td ),
				strtolower( $plural )
			);
			$labels['choose_from_most_used']      = sprintf(
				/* translators: %s: plural taxonomy label (lowercase). */
				__( 'Choose from the most used %s', $td ),
				strtolower( $plural )
			);
		}

		// Remove any empty labels to avoid invalid values.
		return array_filter(
			$labels,
			static function ( $value ) {
				return null !== $value && '' !== $value;
			}
		);
	}

	/**
	 * Seed each taxonomy with a curated set of default terms.
	 *
	 * Terms are only inserted when they don't already exist, so this method is
	 * idempotent and safe to call multiple times (for example on init).
	 *
	 * @return void
	 */
	protected static function seed_default_terms(): void {
		$terms = [
			'topic'        => [
				// Core wellbeing themes used across videos, exercises and articles.
				[ 'Stress Relief', 'stress-relief' ],
				[ 'Anxiety & Clarity', 'anxiety-clarity' ],
				[ 'Confidence & Growth', 'confidence-growth' ],
				[ 'Quick Reset', 'quick-reset' ],
				[ 'Hope & Inspiration', 'hope-inspiration' ],
				[ 'Sleep & Focus', 'sleep-focus' ],
			],
			'technique'    => [
				// Techniques and modalities offered in practices.
				[ 'Breathing', 'breathing' ],
				[ 'Body Scan', 'body-scan' ],
				[ 'Grounding', 'grounding' ],
				[ 'Journaling', 'journaling' ],
				[ 'Affirmations', 'affirmations' ],
				[ 'Visualization', 'visualization' ],
				[ 'Sleep Routine', 'sleep-routine' ],
				[ 'Mindful Walking', 'mindful-walking' ],
			],
			'duration'     => [
				// Suggested practice lengths for filtering content.
				[ '30s', '30s' ],
				[ '1m', '1m' ],
				[ '2-5m', '2-5m' ],
				[ '6-10m', '6-10m' ],
				[ '10m+', '10m-plus' ],
			],
			'format'       => [
				// Content formats spanning media types.
				[ 'Video', 'video' ],
				[ 'Article', 'article' ],
				[ 'Worksheet', 'worksheet' ],
				[ 'Exercise', 'exercise' ],
				[ 'Test', 'test' ],
				[ 'Audio', 'audio' ],
				[ 'Checklist', 'checklist' ],
			],
			'use_case'     => [
				// Situational use cases.
				[ 'Morning', 'morning' ],
				[ 'Bedtime', 'bedtime' ],
				[ 'Work Break', 'work-break' ],
				[ 'Study Focus', 'study-focus' ],
				[ 'Commute', 'commute' ],
				[ 'Before Sleep', 'before-sleep' ],
				[ 'Focus Block', 'focus-block' ],
				[ 'Social Context', 'social-context' ],
			],
			'level'        => [
				// Experience levels for exercises.
				[ 'Beginner', 'beginner' ],
				[ 'Gentle', 'gentle' ],
				[ 'Intermediate', 'intermediate' ],
				[ 'Deep', 'deep' ],
			],
			'a11y_feature' => [
				// Accessibility features to flag alternative formats and support tools.
				[ 'Captions', 'captions' ],
				[ 'Transcript', 'transcript' ],
				[ 'Keyboard-friendly', 'keyboard-friendly' ],
				[ 'Low-vision friendly', 'low-vision-friendly' ],
				[ 'No-music version', 'no-music-version' ],
			],
		];

		/**
		 * Filter the default terms used to seed eMINDy taxonomies.
		 *
		 * @param array $terms Associative array: taxonomy => array of [name, slug].
		 */
		$terms = apply_filters( 'emindy_taxonomy_default_terms', $terms );

		foreach ( $terms as $taxonomy => $taxonomy_terms ) {
			$taxonomy = sanitize_key( $taxonomy );

			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			foreach ( (array) $taxonomy_terms as $term ) {
				if ( ! is_array( $term ) || count( $term ) < 2 ) {
					continue;
				}

				list( $name, $slug ) = $term;

				$name = (string) $name;
				$slug = sanitize_title( $slug );

				if ( term_exists( $slug, $taxonomy ) ) {
					continue;
				}

				wp_insert_term(
					__( $name, self::TEXT_DOMAIN ),
					$taxonomy,
					[
						'slug' => $slug,
					]
				);
			}
		}
	}
}
