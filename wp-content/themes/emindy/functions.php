<?php
/**
 * Child theme bootstrap for eMINDy.
 *
 * Loads the child theme assets, registers supports and contains small
 * frontend/admin helpers. The guard below prevents direct access and
 * keeps the file from executing outside of WordPress.
 */

// Bail early if WordPress is not bootstrapped.
if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Enqueue child theme assets.
 */
function emindy_enqueue_child_assets(): void {
        wp_enqueue_style( 'emindy-child', get_stylesheet_uri(), [ 'twentytwentyfive-style' ], '0.4.0' );

        wp_enqueue_script(
                'emindy-dark-mode-toggle',
                get_stylesheet_directory_uri() . '/assets/js/dark-mode-toggle.js',
                [],
                '1.0',
                true
        );
}
add_action( 'wp_enqueue_scripts', 'emindy_enqueue_child_assets' );

/**
 * Register theme supports and translation domain.
 */
function emindy_setup_theme(): void {
        load_theme_textdomain( 'emindy', get_stylesheet_directory() . '/languages' );

        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
        add_theme_support( 'align-wide' );
        add_theme_support( 'editor-styles' );
        add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'emindy_setup_theme' );

/**
 * Ensure search and 404 pages are noindexed when Rank Math is active.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function emindy_rank_math_robots( array $robots ): array {
        if ( is_search() || is_404() ) {
                $robots['index']  = 'noindex';
                $robots['follow'] = 'follow';
        }

        return $robots;
}
add_filter( 'rank_math/frontend/robots', 'emindy_rank_math_robots' );

/**
 * Fallback robots meta when Rank Math is not available.
 */
function emindy_fallback_robots_meta(): void {
        if ( function_exists( 'rank_math' ) ) {
                return;
        }

        if ( is_search() || is_404() ) {
                printf(
                        '<meta name="robots" content="%s" />' . "\n",
                        esc_attr( 'noindex,follow' )
                );
        }
}
add_action( 'wp_head', 'emindy_fallback_robots_meta', 99 );

/**
 * Output a skip link for accessibility.
 */
function emindy_skip_link(): void {
        printf(
                '<a class="skip-link screen-reader-text" href="%1$s">%2$s</a>',
                esc_url( '#main-content' ),
                esc_html__( 'Skip to content', 'emindy' )
        );
}
add_action( 'wp_body_open', 'emindy_skip_link' );

/**
 * Render a reusable archive toolbar with search and topic filter.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function emindy_render_archive_toolbar( array $atts = [] ): string {
        $atts = shortcode_atts(
                [
                        'label'       => '',
                        'placeholder' => '',
                        'button'      => __( 'Search', 'emindy' ),
                        'post_type'   => '',
                ],
                $atts,
                'em_archive_toolbar'
        );

        $post_type = $atts['post_type'];

        if ( empty( $post_type ) ) {
                $queried_post_type = get_query_var( 'post_type' );

                if ( is_array( $queried_post_type ) ) {
                        $post_type = (string) reset( $queried_post_type );
                } elseif ( is_string( $queried_post_type ) && '' !== $queried_post_type ) {
                        $post_type = $queried_post_type;
                } else {
                        $post_type = get_post_type() ?: 'post';
                }
        }

        $post_type = sanitize_key( $post_type );
        $type_obj  = get_post_type_object( $post_type );
        $type_name = $type_obj && isset( $type_obj->labels->name ) ? $type_obj->labels->name : ucfirst( $post_type );

        $label       = '' !== $atts['label'] ? $atts['label'] : sprintf( __( 'Search %s', 'emindy' ), $type_name );
        $placeholder = '' !== $atts['placeholder'] ? $atts['placeholder'] : sprintf( __( 'Search %s…', 'emindy' ), strtolower( $type_name ) );
        $button      = $atts['button'];

        ob_start();
        ?>
  <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="em-archive-search" aria-label="<?php echo esc_attr( $label ); ?>" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:.25rem 0 .75rem">
    <label for="<?php echo esc_attr( $post_type ); ?>-s" class="sr-only"><?php echo esc_html( $label ); ?></label>
    <input id="<?php echo esc_attr( $post_type ); ?>-s" type="search" name="s" placeholder="<?php echo esc_attr( $placeholder ); ?>" inputmode="search" aria-label="<?php echo esc_attr( $label ); ?>" style="flex:1;min-width:220px;padding:.5rem .75rem;border-radius:.75rem;border:0">
    <input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">
    <button type="submit" aria-label="<?php echo esc_attr( $button ); ?>" style="padding:.55rem .9rem;border-radius:.75rem;border:0;background:#F4D483;color:#0A2A43;font-weight:600"><?php echo esc_html( $button ); ?></button>
  </form>

  <div class="wp-block-group" style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center">
    <p style="font-weight:600;margin:0"><?php echo esc_html__( 'Topics:', 'emindy' ); ?></p>
    <?php echo do_shortcode( '[em_topics_pills taxonomy="topic"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </div>
        <?php

        return (string) ob_get_clean();
}
add_shortcode( 'em_archive_toolbar', 'emindy_render_archive_toolbar' );

/**
 * Adjust em_video archive queries with topic filtering and search support.
 *
 * @param WP_Query $query Main query.
 */
function emindy_adjust_em_cpt_archives( WP_Query $query ): void {
        if ( is_admin() || ! $query->is_main_query() ) {
                return;
        }

        $library_post_types = [ 'em_video', 'em_exercise', 'em_article' ];

        foreach ( $library_post_types as $post_type ) {
                if ( $query->is_post_type_archive( $post_type ) ) {
                        $query->set( 'posts_per_page', 9 );

                        // Retrieve the topic from the querystring. If filter_input is not available,
                        // fall back to using $_GET with manual sanitization. This avoids
                        // fatal errors on hosts where the PHP filter extension is disabled.
                        $topic = null;
                        if ( function_exists( 'filter_input' ) ) {
                                $topic = filter_input( INPUT_GET, 'topic', FILTER_VALIDATE_INT );
                        }
                        if ( ! $topic && isset( $_GET['topic'] ) ) {
                                $topic = absint( wp_unslash( $_GET['topic'] ) );
                        }

                        // Keep taxonomy filter numeric even when the request is manipulated; non-numeric values are ignored.
                        if ( $topic ) {
                                $tax_query   = $query->get( 'tax_query', [] );
                                $tax_query[] = [
                                        'taxonomy' => 'topic',
                                        'field'    => 'term_id',
                                        'terms'    => $topic,
                                ];

                                $query->set( 'tax_query', $tax_query );
                        }

                        if ( isset( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                                $search = sanitize_text_field( wp_unslash( (string) $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                                $query->set( 's', $search );
                        }

                        break;
                }
        }
}
add_action( 'pre_get_posts', 'emindy_adjust_em_cpt_archives' );

/**
 * Highlight search terms in excerpts.
 *
 * @param string $excerpt Post excerpt.
 * @return string
 */
function emindy_highlight_search_terms( string $excerpt ): string {
        $search_query = get_search_query( false );

        if ( is_search() && '' !== $search_query ) {
                $quoted_query = preg_quote( $search_query, '/' );
                $highlighted  = preg_replace( '/(' . $quoted_query . ')/iu', '<mark>$1</mark>', $excerpt );

                if ( null !== $highlighted ) {
                        $excerpt = $highlighted;
                }

                $allowed_html = array_merge( wp_kses_allowed_html( 'post' ), [ 'mark' => [] ] );
                $excerpt      = (string) wp_kses( $excerpt, $allowed_html );
        }

        return (string) $excerpt;
}
add_filter( 'the_excerpt', 'emindy_highlight_search_terms' );

/**
 * Add resource hints for YouTube assets.
 *
 * @param array  $urls          Current URLs.
 * @param string $relation_type Relation type.
 * @return array
 */
function emindy_resource_hints( array $urls, string $relation_type ): array {
        if ( 'preconnect' === $relation_type ) {
                $urls[] = 'https://i.ytimg.com';
                $urls[] = 'https://www.youtube-nocookie.com';
                $urls[] = 'https://www.youtube.com';
                $urls[] = 'https://s.ytimg.com';
        }

        return array_unique( $urls );
}
add_filter( 'wp_resource_hints', 'emindy_resource_hints', 10, 2 );

/*
 * The taxonomy registration and default term insertion previously defined in this
 * child theme have been migrated to the emindy-core plugin.  Registering
 * taxonomies from both the theme and the plugin can cause conflicts and
 * duplicate definitions.  The core plugin now registers the `topic`, `technique`,
 * `duration`, `format`, `use_case`, `level` and `a11y_feature` taxonomies and
 * attaches them to the custom post types.  If you need to add or modify
 * terms, do so via the plugin, which also seeds defaults in a single place to
 * keep migrations predictable.
 */

/**
 * Print ItemList JSON-LD schema.
 *
 * @param string $title List name.
 * @param array  $items Array of [ 'name' => '', 'url' => '' ].
 */
function emindy_print_itemlist_jsonld( string $title, array $items ): void {
        $list = [];
        $pos  = 0;

        foreach ( $items as $item ) {
                if ( ! is_array( $item ) || empty( $item['name'] ) || empty( $item['url'] ) ) {
                        continue;
                }

                $pos++;
                $list[] = [
                        '@type'    => 'ListItem',
                        'position' => $pos,
                        'item'     => [
                                '@type' => 'WebPage',
                                'name'  => wp_strip_all_tags( (string) $item['name'] ),
                                'url'   => esc_url_raw( (string) $item['url'] ),
                        ],
                ];
        }

        if ( ! $list ) {
                return;
        }

        $graph = [
                '@context'        => 'https://schema.org',
                '@type'           => 'ItemList',
                'name'            => wp_strip_all_tags( $title ),
                'itemListElement' => $list,
        ];

        printf(
                '<script type="application/ld+json">%s</script>',
                wp_kses_post( wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) )
        );
}

define( 'EMINDY_PRIMARY_TOPIC_META', '_em_primary_topic' );

/**
 * Register the Primary Topic meta box.
 */
function emindy_register_primary_topic_metabox(): void {
        add_meta_box( 'emindy_primary_topic', __( 'Primary Topic', 'emindy' ), 'emindy_primary_topic_box', [ 'post', 'page' ], 'side', 'default' );
}
add_action( 'add_meta_boxes', 'emindy_register_primary_topic_metabox' );

/**
 * Render the Primary Topic meta box.
 *
 * @param WP_Post $post Current post object.
 */
function emindy_primary_topic_box( WP_Post $post ): void {
        $saved = (int) get_post_meta( $post->ID, EMINDY_PRIMARY_TOPIC_META, true );
        $terms = wp_get_post_terms( $post->ID, 'topic' );

        if ( is_wp_error( $terms ) ) {
                return;
        }

        echo '<p>' . esc_html__( 'Select the primary topic (required if topics are set):', 'emindy' ) . '</p>';
        echo '<select name="em_primary_topic" style="width:100%">';
        echo '<option value="">' . esc_html__( '— None —', 'emindy' ) . '</option>';

        foreach ( $terms as $term ) {
                printf(
                        '<option value="%1$d" %2$s>%3$s</option>',
                        (int) $term->term_id,
                        selected( $saved, (int) $term->term_id, false ),
                        esc_html( $term->name )
                );
        }

        echo '</select>';
        wp_nonce_field( 'em_primary_topic_save', 'em_primary_topic_nonce' );
}

/**
 * Save the Primary Topic meta value.
 *
 * @param int $post_id Post ID.
 */
function emindy_save_primary_topic( int $post_id ): void {
        if ( ! isset( $_POST['em_primary_topic_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['em_primary_topic_nonce'] ) ), 'em_primary_topic_save' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
        }

        if ( isset( $_POST['em_primary_topic'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                $primary = absint( wp_unslash( $_POST['em_primary_topic'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

                if ( $primary > 0 ) {
                        $term_assigned = has_term( $primary, 'topic', $post_id );

                        if ( ! is_wp_error( $term_assigned ) && $term_assigned ) {
                                update_post_meta( $post_id, EMINDY_PRIMARY_TOPIC_META, $primary );
                        }
                } else {
                        delete_post_meta( $post_id, EMINDY_PRIMARY_TOPIC_META );
                }
        }
}
add_action( 'save_post', 'emindy_save_primary_topic' );

/**
 * Warn editors when a primary topic is missing.
 */
function emindy_primary_topic_notice(): void {
        $screen = get_current_screen();

        if ( ! $screen || ! in_array( $screen->id, [ 'post', 'page', 'em_video', 'em_exercise', 'em_article' ], true ) ) {
                return;
        }

        $post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ( ! $post_id ) {
                return;
        }

        $topics = wp_get_post_terms( $post_id, 'topic', [ 'fields' => 'ids' ] );

        if ( is_wp_error( $topics ) ) {
                return;
        }

        if ( $topics && ! get_post_meta( $post_id, EMINDY_PRIMARY_TOPIC_META, true ) ) {
                printf(
                        '<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p></div>',
                        esc_html__( 'Primary Topic', 'emindy' ),
                        esc_html__( 'is not set. Please select one in the sidebar meta box for better recommendations & SEO.', 'emindy' )
                );
        }
}
add_action( 'admin_notices', 'emindy_primary_topic_notice' );

/**
 * Handle sorting for em_video archives and topic term pages.
 *
 * @param WP_Query $query Main query.
 */
function emindy_sort_em_video_archives( WP_Query $query ): void {
        if ( is_admin() || ! $query->is_main_query() ) {
                return;
        }

        $is_em_video_archive = $query->is_post_type_archive( 'em_video' );
        $is_topic_em_video  = $query->is_tax( 'topic' ) && 'em_video' === $query->get( 'post_type' );

        if ( $is_em_video_archive || $is_topic_em_video ) {
                // Retrieve sort parameter from query string. Fallback to $_GET when filter_input
                // is unavailable to avoid fatal errors on hosts without the filter extension.
                $sort = '';
                if ( function_exists( 'filter_input' ) ) {
                        $sort = filter_input( INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                }
                if ( '' === $sort && isset( $_GET['sort'] ) ) {
                        $sort = sanitize_text_field( wp_unslash( $_GET['sort'] ) );
                }
                $sort = 'alpha' === $sort ? 'alpha' : '';

                if ( 'alpha' === $sort ) {
                        $query->set( 'orderby', 'title' );
                        $query->set( 'order', 'ASC' );
                } else {
                        $query->set( 'orderby', 'date' );
                        $query->set( 'order', 'DESC' );
                }
        }
}
add_action( 'pre_get_posts', 'emindy_sort_em_video_archives' );
