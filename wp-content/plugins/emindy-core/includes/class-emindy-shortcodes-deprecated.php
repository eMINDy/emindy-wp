<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Deprecated shortcode handlers preserved for legacy content.
 *
 * These handlers are isolated from the main Shortcodes class to clarify
 * their status and make removal simpler in a future release.
 */
class Shortcodes_Deprecated {
    /**
     * Register deprecated shortcodes.
     */
    public static function register_all() {
        add_shortcode( 'em_related_posts', [ __CLASS__, 'related_posts_alias' ] );
        add_shortcode( 'em_transcript', [ __CLASS__, 'transcript' ] );
        add_shortcode( 'em_video_filters', [ __CLASS__, 'video_filters' ] );
        add_shortcode( 'em_video_player', [ __CLASS__, 'video_player' ] );
    }

    /**
     * Legacy alias for [em_related_posts] pointing to the modern [em_related].
     *
     * @param array $atts Shortcode attributes (legacy: limit).
     * @return string HTML from the unified related renderer.
     * @deprecated 1.4.0 Use [em_related] instead.
     */
    public static function related_posts_alias( $atts = [] ) : string {
        if ( function_exists( '_deprecated_function' ) ) {
            _deprecated_function( '[em_related_posts]', '1.4.0', '[em_related]' );
        }

        return self::forward_related_posts_alias( $atts );
    }

    /**
     * Map legacy related attributes and call the canonical renderer.
     *
     * @param array $atts Shortcode attributes (legacy: limit).
     * @return string HTML from the unified related renderer.
     */
    protected static function forward_related_posts_alias( $atts = [] ) : string {
        $atts = is_array( $atts ) ? $atts : [];
        if ( isset( $atts['limit'] ) && ! isset( $atts['count'] ) ) {
            $atts['count'] = $atts['limit'];
        }

        return Shortcodes::related( $atts );
    }

    /**
     * Legacy transcript shortcode preserved for historic content.
     *
     * @deprecated 1.4.0 Use theme templates or blocks for transcripts instead of [em_transcript].
     */
    public static function transcript() : string {
        if ( function_exists( '_deprecated_function' ) ) {
            _deprecated_function( __METHOD__, '1.4.0' );
        }

        return self::render_transcript();
    }

    /**
     * Render the transcript disclosure and copy helper.
     */
    protected static function render_transcript() : string {
        $post = get_post();
        if ( ! $post ) {
            return '';
        }

        $txt = wpautop( wp_kses_post( $post->post_content ) );
        ob_start(); ?>
        <details class="em-transcript">
                <summary><?php echo esc_html__('Transcript','emindy-core'); ?></summary>
                <div class="em-transcript__body"><?php echo $txt; ?></div>
                <button type="button" class="em-transcript__copy"><?php echo esc_html__('Copy transcript','emindy-core'); ?></button>
        </details>
        <script>
        (function(){
          document.currentScript.previousElementSibling?.nextElementSibling?.addEventListener?.('click', async function(e){
            if(!e.target.matches('.em-transcript__copy')) return;
            const body = e.target.previousElementSibling;
            try{ await navigator.clipboard.writeText(body.innerText.trim()); e.target.textContent='Copied ✔'; setTimeout(()=>e.target.textContent='Copy transcript',1200);}catch(err){}
          });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the archive filter form for video listings.
     *
     * Provides a search box and topic dropdown, respecting any existing query
     * parameters from the request while sanitising the selected topic ID.
     *
     * @deprecated 1.4.0 Use template-level archive filters instead of [em_video_filters].
     * @return string HTML markup for the filter form.
     */
    public static function video_filters() : string {
        if ( function_exists( '_deprecated_function' ) ) {
            _deprecated_function( __METHOD__, '1.4.0' );
        }

        return self::render_video_filters();
    }

    /**
     * Render the archive filter form for video listings.
     *
     * @return string HTML markup for the filter form.
     */
    protected static function render_video_filters() : string {
        $action       = get_post_type_archive_link( 'em_video' );
        $nonce_action = 'em_video_filters';
        $nonce_name   = 'em_video_filters_nonce';

        $nonce_value = isset( $_GET[ $nonce_name ] ) ? sanitize_text_field( wp_unslash( $_GET[ $nonce_name ] ) ) : '';
        $has_nonce   = $nonce_value && wp_verify_nonce( $nonce_value, $nonce_action );

        $selected = ( $has_nonce && isset( $_GET['topic'] ) ) ? absint( wp_unslash( $_GET['topic'] ) ) : 0;
                ob_start(); ?>
                <form class="em-filters" method="get" action="<?php echo esc_url( $action ); ?>" aria-label="<?php echo esc_attr__('Filter videos','emindy-core'); ?>">
                        <label>
                                <span class="screen-reader-text"><?php echo esc_html__('Search','emindy-core'); ?></span>
                                <input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr__('Search videos…','emindy-core'); ?>">
                        </label>
                        <label>
                                <span class="screen-reader-text"><?php echo esc_html__('Topic','emindy-core'); ?></span>
                                <?php
                    // Use the unified `topic` taxonomy in the filters.  This dropdown
                    // displays all available topic terms for filtering the video
                    // archive.  The `taxonomy` argument should match the slug used in
                    // register_taxonomies().
                    wp_dropdown_categories([
                        'taxonomy'        => 'topic',
                        'name'            => 'topic',
                        'show_option_all' => __('All topics','emindy-core'),
                        'hide_empty'      => 0,
                        'selected'        => $selected,
                        'class'           => 'em-filter-select',
                    ]);
                                ?>
                        </label>
                        <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
                        <button type="submit" class="em-filter-submit"><?php echo esc_html__('Apply','emindy-core'); ?></button>
                        <?php if ( ! empty($_GET) ) : ?>
                                <a class="em-filter-reset" href="<?php echo esc_url( $action ); ?>"><?php echo esc_html__('Reset','emindy-core'); ?></a>
                        <?php endif; ?>
                </form>
                <?php
                return ob_get_clean();
        }

        /**
         * Legacy YouTube embed helper preserved for historic posts.
         *
         * @deprecated 1.4.0 Use core YouTube embeds or dedicated blocks instead of [em_video_player].
         */
        public static function video_player() : string {
                if ( function_exists( '_deprecated_function' ) ) {
                        _deprecated_function( __METHOD__, '1.4.0' );
                }

                return self::render_video_player();
        }

        /**
         * Render a YouTube video player.
         */
        protected static function render_video_player() : string {
                $post = get_post(); if (! $post) return '';
                $id = sanitize_text_field( trim( (string) get_post_meta($post->ID,'em_youtube_id',true) ) );
                if ( ! $id ) {
                        // کشف آیدی از اولین لینک یوتیوب در محتوا
                        $c = $post->post_content;
                        if ( preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/))([A-Za-z0-9_-]{6,})~', $c, $m) ) {
                                $id = sanitize_text_field( $m[1] );
                        }
                }
                if ( ! $id ) return '<div class="is-style-em-card"><p>'.esc_html__('No video ID found.','emindy-core').'</p></div>';

                // اگر Lyte هست
                if ( shortcode_exists('lyte') ) {
                        return do_shortcode('[lyte id="'.esc_attr($id).'"]');
                }

                // fallback iframe (nocookie)
                $src = 'https://www.youtube-nocookie.com/embed/'.rawurlencode($id).'?rel=0';
                return '<div class="em-video">
                <iframe loading="lazy" width="560" height="315" src="'.esc_url($src).'" title="'. esc_attr__( 'YouTube video', 'emindy-core' ) .'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
</div>';
        }
}
