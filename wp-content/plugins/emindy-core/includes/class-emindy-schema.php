<?php
namespace EMINDY\Core;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central schema integration for eMINDy.
 *
 * - Hooks into Rank Math JSON-LD graph when available.
 * - Provides a lightweight wp_head fallback when Rank Math is not active.
 * - Exposes static builders for VideoObject, HowTo, and Article schema.
 */
class Schema {

	/**
	 * Register schema integrations and fallbacks.
	 *
	 * This wires Rank Math filters and, when Rank Math is absent,
	 * a lightweight wp_head fallback for custom CPT schema.
	 */
	public static function register() {
		// Integrate with Rank Math's JSON-LD graph when available.
		add_filter( 'rank_math/json_ld', [ __CLASS__, 'filter_rank_math_json_ld' ], 10, 2 );

		// Fallback output when Rank Math is not active.
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			add_action( 'wp_head', [ __CLASS__, 'output_jsonld' ], 20 );
		}
	}

	/**
	 * Filter the Rank Math JSON-LD graph.
	 *
	 * @param array $data   Existing JSON-LD data.
	 * @param mixed $jsonld Rank Math JSON-LD builder instance (unused).
	 *
	 * @return array
	 */
	public static function filter_rank_math_json_ld( $data, $jsonld ) {
		$site_url   = home_url( '/' );
		$site_name  = wp_strip_all_tags( get_bloginfo( 'name' ) );
		$inLanguage = static::language_code();

		$data = (array) $data;

		// Normalize / clean up root nodes.
		$data = static::remove_anonymous_root_nodes( $data );
		$data = static::add_organization( $data, $site_url, $site_name );
		$data = static::add_search_action( $data, $site_url, $site_name, $inLanguage );

		// 1) Search Results Page.
		if ( is_search() ) {
			$q = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

			$data['WebPage'] = [
				'@type'      => 'SearchResultsPage',
				'@id'        => $site_url . '#search',
				'name'       => __( 'Search results', 'emindy-core' ),
				'inLanguage' => $inLanguage,
				'isPartOf'   => [
					'@id' => $site_url . '#website',
				],
				'about'      => $q ? $q : null,
			];
		}

		// 2) Archives: CollectionPage + ItemList (avoid duplicates Rank Math may add).
		if ( is_archive() && ! is_search() ) {
			$data = static::handle_archives( $data, $site_url, $inLanguage );
		}

		// 3) Single CPTs → merge schema from central builders.
		if ( is_singular() ) {
			$post_obj = get_post();

			if ( $post_obj && is_singular( 'em_video' ) ) {
				$data = static::add_em_video( $data, $post_obj );
			}

			if ( $post_obj && is_singular( 'em_exercise' ) ) {
				$data = static::add_em_exercise( $data, $post_obj );
			}

			if ( $post_obj && is_singular( 'em_article' ) ) {
				$data = static::add_em_article( $data, $post_obj );
			}
		}

		// 4) Newsletter SubscribeAction schema.
		// Allow overriding the canonical slug via a filter.
		$newsletter_slug = apply_filters( 'emindy_newsletter_slug', 'newsletter' );

		if ( is_page( $newsletter_slug ) ) {
			$page_permalink = get_permalink();

			if ( $page_permalink ) {
				$data['WebPage'] = [
					'@type'           => 'WebPage',
					'@id'             => trailingslashit( $page_permalink ) . '#webpage',
					'name'            => __( 'Newsletter — eMINDy', 'emindy-core' ),
					'inLanguage'      => $inLanguage,
					'isPartOf'        => [
						'@id' => $site_url . '#website',
					],
					'about'           => 'EmailSubscription',
					'potentialAction' => [
						'@type'    => 'SubscribeAction',
						'target'   => $page_permalink,
						'agent'    => [
							'@type' => 'Person',
						],
						'recipient' => [
							'@id' => $site_url . '#org',
						],
					],
				];
			}
		}

		// Final cleanup to remove nulls / empties when helper exists.
		$data = static::cleanup_graph( $data );

		return $data;
	}

	/**
	 * Remove anonymous Organization and WebSite nodes to prevent duplicates.
	 *
	 * @param array $data Existing JSON-LD data.
	 * @return array
	 */
	protected static function remove_anonymous_root_nodes( array $data ) {
		foreach ( [ 'Organization', 'WebSite' ] as $key ) {
			if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) && empty( $data[ $key ]['@id'] ) ) {
				// Avoid duplicate anonymous nodes.
				unset( $data[ $key ] );
			}
		}

		return $data;
	}

	/**
	 * Populate Organization schema using site details and theme logo preferences.
	 *
	 * @param array  $data      Existing JSON-LD data.
	 * @param string $site_url  Site home URL.
	 * @param string $site_name Site name.
	 * @return array
	 */
	protected static function add_organization( array $data, $site_url, $site_name ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

		$same_as = array_values(
			array_filter(
				[
					'https://www.youtube.com/@emindy_official',
					// Social channels to enable when ready.
					// 'https://www.instagram.com/emindy_official',
					// 'https://www.tiktok.com/@emindy_official',
					// 'https://twitter.com/emindy_official',
				]
			)
		);

		$data['Organization'] = [
			'@type' => 'Organization',
			'@id'   => $site_url . '#org',
			'name'  => $site_name,
			'url'   => $site_url,
			'logo'  => $logo_url
				? [
					'@type' => 'ImageObject',
					'url'   => $logo_url,
				]
				: null,
			'sameAs' => $same_as ? $same_as : null,
		];

		return $data;
	}

	/**
	 * Add WebSite schema enriched with a SearchAction for on-site search.
	 *
	 * @param array  $data       Existing JSON-LD data.
	 * @param string $site_url   Site home URL.
	 * @param string $site_name  Site name.
	 * @param string $inLanguage Two-letter language code for schema output.
	 * @return array
	 */
	protected static function add_search_action( array $data, $site_url, $site_name, $inLanguage ) {
		$data['WebSite'] = [
			'@type'         => 'WebSite',
			'@id'           => $site_url . '#website',
			'url'           => $site_url,
			'name'          => $site_name,
			'inLanguage'    => $inLanguage,
			'publisher'     => [
				'@id' => $site_url . '#org',
			],
			'potentialAction' => [
				'@type'       => 'SearchAction',
				'target'      => $site_url . '?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			],
		];

		return $data;
	}

	/**
	 * Build archive CollectionPage and ItemList schema without duplicating Rank Math output.
	 *
	 * @param array  $data       Existing JSON-LD data.
	 * @param string $site_url   Site home URL.
	 * @param string $inLanguage Two-letter language code for schema output.
	 * @return array
	 */
	protected static function handle_archives( array $data, $site_url, $inLanguage ) {
		$archive_url = home_url( add_query_arg( [] ) );

		// Collection page node.
		if ( empty( $data['WebPage'] ) ) {
			$data['WebPage'] = [
				'@type'      => 'CollectionPage',
				'@id'        => trailingslashit( $archive_url ) . '#collection',
				'name'       => wp_strip_all_tags( get_the_archive_title() ),
				'inLanguage' => $inLanguage,
				'isPartOf'   => [
					'@id' => $site_url . '#website',
				],
			];
		}

		// ItemList node.
		if ( empty( $data['ItemList'] ) ) {
			global $wp_query;

			if ( isset( $wp_query->posts ) && is_array( $wp_query->posts ) && ! empty( $wp_query->posts ) ) {
				$position = 1;
				$items    = [];

				foreach ( $wp_query->posts as $p ) {
					if ( ! $p instanceof WP_Post ) {
						continue;
					}

					$items[] = [
						'@type'   => 'ListItem',
						'position'=> $position++,
						'url'     => get_permalink( $p ),
					];

					// Keep the list compact (first 10 results).
					if ( $position > 11 ) {
						break;
					}
				}

				if ( $items ) {
					$data['ItemList'] = [
						'@type'          => 'ItemList',
						'itemListOrder'  => 'https://schema.org/ItemListOrderAscending',
						'numberOfItems'  => count( $items ),
						'itemListElement'=> $items,
					];
				}
			}
		}

		return $data;
	}

	/**
	 * Add VideoObject schema for em_video posts using the central builder.
	 *
	 * @param array   $data     Existing JSON-LD data.
	 * @param WP_Post $post_obj Post object for the current singular.
	 * @return array
	 */
	protected static function add_em_video( array $data, WP_Post $post_obj ) {
		$video_schema = static::build_video_schema( $post_obj );

		if ( $video_schema ) {
			$data['VideoObject'] = $video_schema;
		}

		return $data;
	}

	/**
	 * Add HowTo schema for em_exercise posts using the central builder.
	 *
	 * @param array   $data     Existing JSON-LD data.
	 * @param WP_Post $post_obj Post object for the current singular.
	 * @return array
	 */
	protected static function add_em_exercise( array $data, WP_Post $post_obj ) {
		$howto_schema = static::build_exercise_howto_schema( $post_obj );

		if ( $howto_schema ) {
			$data['HowTo'] = $howto_schema;
		}

		return $data;
	}

	/**
	 * Add Article schema for em_article posts using the central builder.
	 *
	 * @param array   $data     Existing JSON-LD data.
	 * @param WP_Post $post_obj Post object for the current singular.
	 * @return array
	 */
	protected static function add_em_article( array $data, WP_Post $post_obj ) {
		$article_schema = static::build_article_schema( $post_obj );

		if ( $article_schema ) {
			$data['Article'] = $article_schema;
		}

		return $data;
	}

	/**
	 * Cleanup null values in the JSON-LD graph recursively when helper is available.
	 *
	 * @param array $data JSON-LD data.
	 * @return array
	 */
	protected static function cleanup_graph( array $data ) {
		if ( ! function_exists( 'emindy_array_filter_recursive' ) ) {
			return $data;
		}

		foreach ( $data as $key => $node ) {
			if ( is_array( $node ) ) {
				$data[ $key ] = emindy_array_filter_recursive( $node );
			}
		}

		return $data;
	}

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
		$title     = wp_strip_all_tags( get_the_title( $post ) );
		$desc_raw  = $post->post_excerpt ?: $post->post_content;
		$desc      = $desc_raw ? wp_trim_words( wp_strip_all_tags( $desc_raw ), 60, '…' ) : '';
		$image     = get_the_post_thumbnail_url( $post, 'full' );
		$in_lang   = static::language_code();
		$site_id   = home_url( '/' ) . '#website';
		$org_id    = home_url( '/' ) . '#org';

		// Steps meta: be defensive about helper availability.
		$steps_raw = get_post_meta( $post->ID, 'em_steps_json', true );

		if ( function_exists( 'json_decode_safe' ) ) {
			$steps_meta = json_decode_safe( $steps_raw );
		} else {
			$steps_meta = $steps_raw ? json_decode( $steps_raw, true ) : [];

			if ( ! is_array( $steps_meta ) ) {
				$steps_meta = [];
			}
		}

		$steps = [];

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
						'@type'        => 'HowToStep',
						'position'     => (int) $index + 1,
						'name'         => $step_name,
						'timeRequired' => $duration ? emindy_iso8601_duration( $duration ) : null,
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

			return array_values(
				array_filter(
					array_map(
						'sanitize_text_field',
						array_map( 'trim', $list )
					)
				)
			);
		};

		$supplies = $build_list( get_post_meta( $post->ID, 'em_supplies', true ) );
		$tools    = $build_list( get_post_meta( $post->ID, 'em_tools', true ) );
		$yield    = get_post_meta( $post->ID, 'em_yield', true );

		$schema = [
			'@type'       => 'HowTo',
			'@id'         => esc_url_raw( $permalink ) . '#howto',
			'name'        => $title,
			'description' => $desc,
			'inLanguage'  => $in_lang,
			'url'         => esc_url_raw( $permalink ),
			'publisher'   => [
				'@id' => esc_url_raw( $org_id ),
			],
			'isPartOf'    => [
				'@id' => esc_url_raw( $site_id ),
			],
			'image'       => $image
				? [
					'@type' => 'ImageObject',
					'url'   => esc_url_raw( $image ),
				]
				: null,
			'step'        => $steps,
			'totalTime'   => $total_seconds ? emindy_iso8601_duration( $total_seconds ) : null,
			'prepTime'    => $prep_seconds ? emindy_iso8601_duration( $prep_seconds ) : null,
			'performTime' => $perform_seconds ? emindy_iso8601_duration( $perform_seconds ) : null,
			'supply'      => $supplies
				? array_map(
					static function ( $item ) {
						return [
							'@type' => 'HowToSupply',
							'name'  => $item,
						];
					},
					$supplies
				)
				: null,
			'tool'        => $tools
				? array_map(
					static function ( $item ) {
						return [
							'@type' => 'HowToTool',
							'name'  => $item,
						];
					},
					$tools
				)
				: null,
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
		$title     = wp_strip_all_tags( get_the_title( $post ) );
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

		$chapters   = [];
		$chap_meta  = get_post_meta( $post->ID, 'em_chapters_json', true );
		$chap_filter = static function ( $chapter ) {
			$label = isset( $chapter['label'] ) ? wp_strip_all_tags( $chapter['label'] ) : '';
			$start = isset( $chapter['t'] ) ? emindy_seconds_from_ts( $chapter['t'] ) : 0;

			if ( '' === $label ) {
				return null;
			}

			return [
				'@type'       => 'Clip',
				'name'        => $label,
				'position'    => null,
				'startOffset' => $start,
			];
		};

		if ( $chap_meta ) {
			$chapters_array = json_decode( $chap_meta, true );

			if ( is_array( $chapters_array ) ) {
				$position = 1;

				foreach ( $chapters_array as $chapter ) {
					$clip = $chap_filter( $chapter );

					if ( null === $clip ) {
						continue;
					}

					$clip['position'] = $position++;
					$chapters[]       = $clip;
				}
			}
		}

		$schema = [
			'@type'          => 'VideoObject',
			'@id'            => esc_url_raw( $permalink ) . '#video',
			'name'           => $title,
			'description'    => $desc,
			'thumbnailUrl'   => $image ? esc_url_raw( $image ) : null,
			'uploadDate'     => $upload,
			'inLanguage'     => $in_lang,
			'url'            => esc_url_raw( $permalink ),
			'embedUrl'       => $embed ? esc_url_raw( $embed ) : null,
			'isFamilyFriendly'=> true,
			'hasPart'        => $chapters ?: null,
			'duration'       => $duration_iso,
			'publisher'      => [
				'@id' => esc_url_raw( $org_id ),
			],
			'isPartOf'       => [
				'@id' => esc_url_raw( $site_id ),
			],
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
		$title     = wp_strip_all_tags( get_the_title( $post ) );
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
			'@type'            => 'Article',
			'@id'              => esc_url_raw( $permalink ) . '#article',
			'headline'         => $title,
			'description'      => $desc,
			'inLanguage'       => $in_lang,
			'image'            => $image
				? [
					'@type' => 'ImageObject',
					'url'   => esc_url_raw( $image ),
				]
				: null,
			'author'           => $author
				? [
					'@type' => 'Person',
					'name'  => wp_strip_all_tags( $author ),
				]
				: null,
			'datePublished'    => $published,
			'dateModified'     => $modified,
			'url'              => esc_url_raw( $permalink ),
			'mainEntityOfPage' => esc_url_raw( $permalink ),
			'publisher'        => [
				'@id' => esc_url_raw( $org_id ),
			],
			'isPartOf'         => [
				'@id' => esc_url_raw( $site_id ),
			],
		];

		return function_exists( 'emindy_array_filter_recursive' ) ? emindy_array_filter_recursive( $schema ) : $schema;
	}

	/**
	 * Emit fallback JSON-LD when Rank Math is not active.
	 */
	public static function output_jsonld() {
		// Bail if Rank Math is active; let it handle output.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			return;
		}

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

		$schema = array_merge(
			[
				'@context' => 'https://schema.org',
			],
			$schema
		);

		printf(
			'<script type="application/ld+json">%s</script>',
			esc_html(
				wp_json_encode(
					$schema,
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
				)
			)
		);
	}
}
