<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {
    public static function register_all() {
        add_shortcode( 'em_player', [ __CLASS__, 'player' ] );
        add_shortcode( 'em_exercise_steps', [ __CLASS__, 'exercise_steps' ] );
        add_shortcode( 'em_lang_switcher', [ __CLASS__, 'lang_switcher' ] );
        add_shortcode( 'em_video_chapters', [ __CLASS__, 'video_chapters' ] );
        add_shortcode( 'em_phq9', [ __CLASS__, 'phq9' ] );
        add_shortcode( 'em_related', [ __CLASS__, 'related' ] );
        add_shortcode( 'em_related_posts', [ __CLASS__, 'related_posts_alias' ] );
        add_shortcode( 'em_newsletter', [ __CLASS__, 'newsletter' ] );
        add_shortcode( 'em_gad7', [ __CLASS__, 'gad7' ] );
        add_shortcode( 'em_assessment_result', [ __CLASS__, 'assessment_result' ] );
        add_shortcode( 'em_transcript', [ __CLASS__, 'transcript' ] );
        add_shortcode( 'em_video_filters', [ __CLASS__, 'video_filters' ] );
        add_shortcode( 'em_video_player', [ __CLASS__, 'video_player' ] );
    }

    /**
     * Retrieve and decode the step data for an exercise post.
     *
     * This helper centralises JSON parsing so shortcodes such as
     * [em_player] and [em_exercise_steps] share the same behaviour
     * and avoid duplicate logic.
     *
     * @param int $post_id Exercise post ID.
     * @return array Parsed steps array or an empty array on failure.
     */
    protected static function get_exercise_steps_data( int $post_id ) : array {
        if ( ! $post_id ) {
            return [];
        }

        $steps = json_decode_safe( get_post_meta( $post_id, 'em_steps_json', true ) ) ?: [];
        return is_array( $steps ) ? $steps : [];
    }

    /**
     * Normalise a single exercise step row for display.
     *
     * @param mixed $step Raw step item from em_steps_json.
     * @return array{text:string,duration:?int}|array Empty array when unusable.
     */
    protected static function normalize_exercise_step( $step ) : array {
        $text     = '';
        $duration = null;

        if ( is_array( $step ) ) {
            $text = $step['text'] ?? $step['label'] ?? $step['title'] ?? '';
            if ( isset( $step['duration'] ) && is_numeric( $step['duration'] ) ) {
                $duration = (int) $step['duration'];
            }
        } elseif ( is_scalar( $step ) ) {
            $text = (string) $step;
        }

        $text = is_string( $text ) ? trim( wp_strip_all_tags( $text ) ) : '';
        if ( '' === $text ) {
            return [];
        }

        return [
            'text'     => $text,
            'duration' => $duration,
        ];
    }

    public static function player( $atts = [], $content = '' ) : string {
        $post_id    = get_the_ID();
        $steps      = self::get_exercise_steps_data( (int) $post_id );
        $total      = is_array( $steps ) ? count( $steps ) : 0;
        $total_secs = 0;
        foreach ( $steps as $s ) {
            $total_secs += (int) ( $s['duration'] ?? 0 );
        }

        $steps_json = wp_json_encode( is_array( $steps ) ? array_values( $steps ) : [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

        ob_start(); ?>
        <div class="em-player" data-post="<?php echo esc_attr( $post_id ); ?>" data-steps="<?php echo esc_attr( $steps_json ); ?>">
                <h3 class="em-p__title"><?php echo esc_html__( 'Guided Practice', 'emindy-core' ); ?></h3>

                <div class="em-p__header" role="group" aria-label="<?php echo esc_attr__( 'Exercise Controls', 'emindy-core' ); ?>">
                        <div class="em-p__meta">
                                <span><?php echo esc_html__( 'Step', 'emindy-core' ); ?> <span class="em-p__cur">1</span>/<span class="em-p__total"><?php echo (int) $total; ?></span></span>
                                <span><?php echo esc_html__( 'Step time', 'emindy-core' ); ?>: <span class="em-p__step-time">0:00</span></span>
                                <span><?php echo esc_html__( 'Total', 'emindy-core' ); ?>: <span class="em-p__all-time"><?php echo esc_html( gmdate( 'i:s', max( 0, $total_secs ) ) ); ?></span></span>
                        </div>
                        <div class="em-p__controls">
                                <button type="button" class="em-p__btn em-p__prev" aria-label="<?php echo esc_attr__( 'Previous step', 'emindy-core' ); ?>">⟨</button>
                                <button type="button" class="em-p__btn em-p__play" aria-pressed="false"><?php echo esc_html__( 'Start', 'emindy-core' ); ?></button>
                                <button type="button" class="em-p__btn em-p__next" aria-label="<?php echo esc_attr__( 'Next step', 'emindy-core' ); ?>">⟩</button>
                                <button type="button" class="em-p__btn em-p__reset"><?php echo esc_html__( 'Reset', 'emindy-core' ); ?></button>
                        </div>
                </div>

                <div class="em-p__barwrap" aria-hidden="true"><div class="em-p__bar"></div></div>
                <div class="em-p__remain" aria-live="off">0:00</div>

                <ol class="em-p__list" aria-label="<?php echo esc_attr__( 'Steps', 'emindy-core' ); ?>"></ol>
                <div class="em-p__live" role="status" aria-live="polite"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render a read-only ordered list of exercise steps from em_steps_json.
     *
     * This helper is intentionally forgiving: if no valid steps are present it
     * returns an empty string without emitting warnings.
     *
     * @return string
     */
    public static function exercise_steps() : string {
        $post_id   = get_the_ID();
        $raw_steps = self::get_exercise_steps_data( (int) $post_id );

        $steps = [];
        foreach ( $raw_steps as $step ) {
            $normalized = self::normalize_exercise_step( $step );
            if ( ! empty( $normalized ) ) {
                $steps[] = $normalized;
            }
        }

        if ( empty( $steps ) ) {
            return '';
        }

        ob_start();
        ?>
        <ol class="em-exercise-steps">
                <?php foreach ( $steps as $step ) : ?>
                        <li class="em-exercise-steps__item">
                                <span class="em-exercise-steps__text"><?php echo esc_html( $step['text'] ); ?></span>
                                <?php if ( isset( $step['duration'] ) && $step['duration'] > 0 ) : ?>
                                        <small class="em-exercise-steps__meta"><?php echo esc_html__( 'Time', 'emindy-core' ); ?>: <?php echo esc_html( gmdate( 'i:s', max( 0, (int) $step['duration'] ) ) ); ?></small>
                                <?php endif; ?>
                        </li>
                <?php endforeach; ?>
        </ol>
        <?php
        return ob_get_clean();
    }

    public static function video_chapters() : string {
        $post_id  = get_the_ID();
        $chapters = json_decode_safe( get_post_meta( $post_id, 'em_chapters_json', true ) ) ?: [];
        $chapters = is_array( $chapters ) ? $chapters : [];
        $yt       = sanitize_text_field( (string) get_post_meta( $post_id, 'em_youtube_id', true ) ); // اگر داری از این متا استفاده کن

        ob_start(); ?>
        <div class="em-chapters">
                <h3><?php echo esc_html__( 'Chapters', 'emindy-core' ); ?></h3>
                <ol>
                        <?php foreach ( $chapters as $c ) :
                                $label_raw = isset( $c['label'] ) ? sanitize_text_field( (string) $c['label'] ) : '';
                                $label     = esc_html( $label_raw );
                                $sec       = (int) ( $c['time'] ?? 0 );
                                if ( $yt && $sec > 0 ) {
                                        $url = 'https://youtu.be/' . rawurlencode( $yt ) . '?t=' . $sec;
                                        echo '<li><a target="_blank" rel="noopener noreferrer" href="' . esc_url( $url ) . '">' . $label . ' — ' . esc_html( gmdate( 'i:s', $sec ) ) . '</a></li>';
                                } else {
                                        echo '<li>' . $label . '</li>';
                                }
                        endforeach; ?>
                </ol>
        </div>
        <?php
        return ob_get_clean();
    }

        /**
         * Render the PHQ-9 self-check form with translatable prompts.
         *
         * The paired JavaScript scorer keeps responses on the client only and
         * requires each radio group to be answered before calculating totals.
         *
         * @return string HTML markup for the PHQ-9 form.
         */
        public static function phq9() : string {
        ob_start(); ?>
        <form class="em-phq9" aria-describedby="em-phq9-desc">
                <p id="em-phq9-desc"><?php echo esc_html__('This is an educational check-in (not a diagnosis). Your responses are private on this device.','emindy-core'); ?></p>

		<ol class="em-phq9__list">
			<?php
			$qs = [
				__('Little interest or pleasure in doing things', 'emindy-core'),
				__('Feeling down, depressed, or hopeless', 'emindy-core'),
				__('Trouble falling or staying asleep, or sleeping too much', 'emindy-core'),
				__('Feeling tired or having little energy', 'emindy-core'),
				__('Poor appetite or overeating', 'emindy-core'),
				__('Feeling bad about yourself — or that you are a failure or have let yourself or your family down', 'emindy-core'),
				__('Trouble concentrating on things, such as reading or watching television', 'emindy-core'),
				__('Moving or speaking so slowly that other people could have noticed, or the opposite — being so fidgety or restless that you have been moving a lot more than usual', 'emindy-core'),
				__('Thoughts that you would be better off dead, or thoughts of hurting yourself', 'emindy-core'),
			];
			$opts = [
				['v'=>0,'t'=>__('Not at all','emindy-core')],
				['v'=>1,'t'=>__('Several days','emindy-core')],
				['v'=>2,'t'=>__('More than half the days','emindy-core')],
				['v'=>3,'t'=>__('Nearly every day','emindy-core')],
			];
			foreach ($qs as $i=>$q): ?>
			<li class="em-phq9__item">
				<fieldset>
					<legend><?php echo esc_html($q); ?></legend>
					<?php foreach ($opts as $j=>$o): 
						$id = 'phq9_q'.$i.'_o'.$j; ?>
						<label for="<?php echo esc_attr($id); ?>" class="em-phq9__opt">
							<input type="radio" name="phq9_q<?php echo (int)$i; ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo (int)$o['v']; ?>" required>
							<span><?php echo esc_html($o['t']); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			</li>
			<?php endforeach; ?>
		</ol>

		<div class="em-phq9__actions">
			<button type="submit" class="em-phq9__submit"><?php echo esc_html__('See my result','emindy-core'); ?></button>
			<button type="button" class="em-phq9__reset"><?php echo esc_html__('Reset','emindy-core'); ?></button>
		</div>

		<div class="em-phq9__result" role="region" aria-live="polite" hidden>
			<h3><?php echo esc_html__('Your result','emindy-core'); ?></h3>
			<p class="em-phq9__score"></p>
			<p class="em-phq9__note"><?php echo esc_html__('This check is educational and not a medical diagnosis. If you feel unsafe or in crisis, please visit the Emergency page.','emindy-core'); ?></p>
			<div class="em-phq9__share">
				<button type="button" class="em-phq9__print"><?php echo esc_html__('Print / Save PDF','emindy-core'); ?></button>
				<button type="button" class="em-phq9__copy"><?php echo esc_html__('Copy summary','emindy-core'); ?></button>
			    <button type="button" class="em-phq9__sharelink" data-kind="phq9"><?php echo esc_html__('Get shareable link','emindy-core'); ?></button>
                <button type="button" class="em-phq9__email" data-kind="phq9"><?php echo esc_html__('Email me the summary','emindy-core'); ?></button>
			</div>
		</div>
	</form>
	<?php
	return ob_get_clean();
}

	/**
	 * Render a grid of related content items for the current post.
	 *
	 * The shortcode matches content by shared taxonomies, prioritising the
	 * current topic (primary topic meta or assigned topic terms) and optional
	 * technique/format filters. Attributes mirror both legacy and refactored
	 * implementations: `post_type`, `taxonomy`, `count`, `topic`, `technique`,
	 * `format`, and `orderby`.
	 *
	 * @param array $atts Shortcode attributes controlling the related query.
	 * @return string HTML markup for the related content grid.
	 */
    public static function related( $atts = [] ) : string {
        $post = get_post();
        if ( ! $post ) {
            return '';
        }

        $defaults = [
            'post_type' => get_post_type() ?: 'post',
            'taxonomy'  => 'topic',
            'count'     => 4,
            'topic'     => 'current',
            'technique' => '',
            'format'    => '',
            'orderby'   => 'date',
        ];
        $a = shortcode_atts( $defaults, $atts, 'em_related' );

        $post_types = array_filter( array_map( 'sanitize_key', (array) $a['post_type'] ) );
        if ( empty( $post_types ) ) {
            $post_types = [ get_post_type() ?: 'post' ];
        }

        $taxonomy = $a['taxonomy'] ? sanitize_key( $a['taxonomy'] ) : '';
        $count    = max( 1, (int) $a['count'] );
        $orderby  = in_array( $a['orderby'], [ 'date', 'relevance', 'rand', 'menu_order', 'title' ], true ) ? $a['orderby'] : 'date';

        // Polylang: respect the current post language when available.
        $lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID ) : null;

        $tax_query     = [];
        $topic_term_id = null;

        // Resolve the topic filter. Prefer the primary topic meta if present to avoid
        // cross-linking unrelated content when multiple topics exist.
        if ( ! empty( $a['topic'] ) ) {
            if ( 'current' === $a['topic'] ) {
                $primary = (int) get_post_meta( $post->ID, EMINDY_PRIMARY_TOPIC_META, true );
                if ( $primary ) {
                    $topic_term_id = $primary;
                } else {
                    $terms = wp_get_post_terms( $post->ID, 'topic', [ 'fields' => 'ids' ] );
                    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                        $topic_term_id = (int) $terms[0];
                    }
                }
            } else {
                $topic_slug = sanitize_title( $a['topic'] );
                $topic_term = $topic_slug ? get_term_by( 'slug', $topic_slug, 'topic' ) : false;
                if ( $topic_term ) {
                    $topic_term_id = (int) $topic_term->term_id;
                }
            }
        }

        if ( $topic_term_id ) {
            $tax_query[] = [
                'taxonomy' => 'topic',
                'field'    => 'term_id',
                'terms'    => [ $topic_term_id ],
            ];
        } elseif ( $taxonomy ) {
            $terms = wp_get_object_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $terms,
                ];
            }
        }

        foreach ( [ 'technique', 'format' ] as $tax_slug ) {
            if ( empty( $a[ $tax_slug ] ) ) {
                continue;
            }
            $term_slug = sanitize_title( $a[ $tax_slug ] );
            $term      = $term_slug ? get_term_by( 'slug', $term_slug, $tax_slug ) : false;
            if ( $term ) {
                $tax_query[] = [
                    'taxonomy' => $tax_slug,
                    'field'    => 'term_id',
                    'terms'    => [ (int) $term->term_id ],
                ];
            }
        }

        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }

        $base_args = [
            'post_type'        => $post_types,
            'post__not_in'     => [ $post->ID ],
            'posts_per_page'   => $count,
            'orderby'          => $orderby,
            'order'            => 'DESC',
            'suppress_filters' => false,
            'no_found_rows'    => true,
        ];

        if ( ! empty( $tax_query ) ) {
            $base_args['tax_query'] = $tax_query;
        }

        if ( $lang && function_exists( 'pll_current_language' ) ) {
            $base_args['lang'] = $lang;
        }

        $candidates = [ $base_args ];

        // Deterministic fallback: search by the current title/excerpt to avoid relying on
        // user-supplied query vars or showing an empty block when taxonomy matches fail.
        $needle = sanitize_text_field( wp_strip_all_tags( $post->post_title . ' ' . get_the_excerpt( $post ) ) );
        if ( '' !== $needle ) {
            $search_args = $base_args;
            unset( $search_args['tax_query'] );
            $search_args['s']       = $needle;
            $search_args['orderby'] = 'relevance';
            $candidates[]           = $search_args;
        }

        $query = null;
        foreach ( $candidates as $args ) {
            $query = new \WP_Query( $args );
            if ( $query->have_posts() ) {
                break;
            }
        }

        if ( ! $query || ! $query->have_posts() ) {
            return '';
        }

        ob_start();
        echo '<div class="em-related-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<article class="is-style-em-card" style="padding:12px">';
            echo '<h4><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h4>';
            echo '<p>' . esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ) . '</p>';
            echo '</article>';
        }
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Legacy alias for [em_related_posts] pointing to the modern [em_related].
     *
     * The previous shortcode supported a `limit` attribute and returned a
     * simplified category-based grid. We map `limit` to the current `count`
     * parameter and delegate to related() so sites using the legacy shortcode
     * transparently receive the improved query. A deprecation notice is
     * triggered in debug environments to encourage migration.
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

        return self::related( $atts );
    }


    /**
     * Render the eMINDy newsletter form.
     *
     * The original implementation returned a placeholder string which
     * prevented the actual signup form from appearing on the front end.
     * This method now attempts to call the `em_newsletter_form()` function
     * defined in includes/newsletter.php.  If that function does not exist
     * (for example if the newsletter file is not loaded), we fall back to
     * invoking the `[em_newsletter_form]` shortcode.  If neither is
     * available, we display a friendly message so that administrators can
     * diagnose the issue.  Wrapping the form in a div allows theme
     * developers to style the newsletter consistently.
     *
     * @return string HTML markup for the newsletter form
     */
    public static function newsletter() : string {
        // Prefer the direct function to avoid nesting shortcodes and allow
        // developers to filter the output via hooks.
        if ( function_exists( 'em_newsletter_form' ) ) {
            $form = em_newsletter_form();
            return '<div class="em-newsletter">'. $form .'</div>';
        }
        // Fall back to do_shortcode if the function isn't loaded yet.
        if ( shortcode_exists( 'em_newsletter_form' ) ) {
            $form = do_shortcode( '[em_newsletter_form]' );
            return '<div class="em-newsletter">'. $form .'</div>';
        }
        // Last resort: display a translatable placeholder so site admins know
        // something is misconfigured.  Use esc_html__ to allow translation.
        return '<div class="em-newsletter">'. esc_html__( 'Newsletter form unavailable. Please ensure the eMINDy Core plugin is active.', 'emindy-core' ) .'</div>';
    }
    /**
     * Render the Polylang language switcher as a dropdown or chip list.
     *
     * @param array $atts Shortcode attributes controlling the display.
     * @return string HTML markup or empty string when Polylang is unavailable.
     */
    public static function lang_switcher( $atts = [] ) : string {
        if ( ! function_exists( 'pll_the_languages' ) ) return '';

	$defaults = [
		'show_flags'   => '1',
		'show_names'   => '1',
		'dropdown'     => '1',
		'hide_current' => '0',
	];
	$atts = shortcode_atts( $defaults, $atts, 'em_lang_switcher' );

	$langs = pll_the_languages( [
		'raw'                  => 1,
		'hide_if_no_translation' => 0,
		'display_names_as'     => 'name',
		'show_flags'           => ( $atts['show_flags'] === '1' ),
		'show_names'           => ( $atts['show_names'] === '1' ),
		'hide_current'         => ( $atts['hide_current'] === '1' ),
	] );

	if ( empty( $langs ) || ! is_array( $langs ) ) return '';

	ob_start();

	if ( $atts['dropdown'] === '1' ) : ?>
		<form class="em-lang-switcher em-lang-switcher--select" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'Language switcher', 'emindy-core' ); ?>">
			<label for="em-lang-select" class="screen-reader-text"><?php echo esc_html__( 'Select language', 'emindy-core' ); ?></label>
			<span class="em-select">
				<select id="em-lang-select" onchange="if(this.value){window.location.href=this.value;}" aria-label="<?php echo esc_attr__( 'Select language', 'emindy-core' ); ?>">
					<?php foreach ( $langs as $lang ) : ?>
						<option value="<?php echo esc_url( $lang['url'] ); ?>" <?php selected( ! empty( $lang['current'] ) ); ?>>
							<?php
							$label = '';
							if ( $atts['show_flags'] === '1' && ! empty( $lang['flag'] ) ) {
								$label .= wp_kses( $lang['flag'], [ 'img' => [ 'src'=>[], 'alt'=>[], 'width'=>[], 'height'=>[] ] ] ) . ' ';
							}
							if ( $atts['show_names'] === '1' && ! empty( $lang['name'] ) ) {
								$label .= esc_html( $lang['name'] );
							} else {
								$label .= esc_html( strtoupper( $lang['slug'] ) );
							}
							echo $label;
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</span>
		</form>
	<?php else : ?>
		<nav class="em-lang-switcher em-lang-switcher--chips" aria-label="<?php echo esc_attr__( 'Language switcher', 'emindy-core' ); ?>">
			<ul class="em-lang-switcher__list">
			<?php foreach ( $langs as $lang ) :
				$is_current  = ! empty( $lang['current'] );
				$label_text  = ( $atts['show_names'] === '1' && ! empty( $lang['name'] ) ) ? $lang['name'] : strtoupper( $lang['slug'] );
				$aria_label  = $is_current
					? sprintf( __( '%s (current language)', 'emindy-core' ), $label_text )
					: sprintf( __( 'Switch to %s', 'emindy-core' ), $label_text );
			?>
				<li class="em-lang-switcher__item<?php echo $is_current ? ' is-current' : ''; ?>">
					<a href="<?php echo esc_url( $lang['url'] ); ?>" class="em-lang-switcher__link" aria-label="<?php echo esc_attr( $aria_label ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
						<?php
						if ( $atts['show_flags'] === '1' && ! empty( $lang['flag'] ) ) {
							echo wp_kses( $lang['flag'], [ 'img' => [ 'src'=>[], 'alt'=>[], 'width'=>[], 'height'=>[], 'aria-hidden'=>[] ] ] );
						}
						echo '<span class="em-lang-switcher__name">' . esc_html( $label_text ) . '</span>';
						?>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	// --- ضدّ <br/> و <p> ناخواسته:
        $out = ob_get_clean();
        $out = shortcode_unautop( $out );            // حذف <p> و <br> تزریق شده
        $out = preg_replace( '/>\s+</', '><', $out); // جمع کردن فاصله‌ها بین تگ‌ها
        return trim( $out );
}

        /**
         * Render the GAD-7 self-check form mirroring the PHQ-9 markup.
         *
         * Questions mirror the official wording and are wrapped in required
         * radio groups so the client-side scorer receives complete data without
         * transmitting answers to the server.
         *
         * @return string HTML markup for the GAD-7 form.
         */
public static function gad7() : string {
        ob_start(); ?>
        <form class="em-phq9 em-gad7" aria-describedby="em-gad7-desc">
                <p id="em-gad7-desc"><?php echo esc_html__('Educational check-in (not a diagnosis). Your responses stay on this device.','emindy-core'); ?></p>

		<ol class="em-phq9__list">
			<?php
			$qs = [
				__('Feeling nervous, anxious, or on edge','emindy-core'),
				__('Not being able to stop or control worrying','emindy-core'),
				__('Worrying too much about different things','emindy-core'),
				__('Trouble relaxing','emindy-core'),
				__('Being so restless that it is hard to sit still','emindy-core'),
				__('Becoming easily annoyed or irritable','emindy-core'),
				__('Feeling afraid as if something awful might happen','emindy-core'),
			];
			$opts = [
				['v'=>0,'t'=>__('Not at all','emindy-core')],
				['v'=>1,'t'=>__('Several days','emindy-core')],
				['v'=>2,'t'=>__('More than half the days','emindy-core')],
				['v'=>3,'t'=>__('Nearly every day','emindy-core')],
			];
			foreach ($qs as $i=>$q): ?>
			<li class="em-phq9__item">
				<fieldset>
					<legend><?php echo esc_html($q); ?></legend>
					<?php foreach ($opts as $j=>$o):
						$id = 'gad7_q'.$i.'_o'.$j; ?>
						<label for="<?php echo esc_attr($id); ?>" class="em-phq9__opt">
							<input type="radio" name="gad7_q<?php echo (int)$i; ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo (int)$o['v']; ?>" required>
							<span><?php echo esc_html($o['t']); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			</li>
			<?php endforeach; ?>
		</ol>

		<div class="em-phq9__actions">
			<button type="submit" class="em-phq9__submit"><?php echo esc_html__('See my result','emindy-core'); ?></button>
			<button type="button" class="em-phq9__reset"><?php echo esc_html__('Reset','emindy-core'); ?></button>
		</div>

		<div class="em-phq9__result" role="region" aria-live="polite" hidden>
			<h3><?php echo esc_html__('Your result','emindy-core'); ?></h3>
			<p class="em-phq9__score"></p>
			<p class="em-phq9__note"><?php echo esc_html__('This check is educational and not a medical diagnosis. If you feel unsafe or in crisis, please visit the Emergency page.','emindy-core'); ?></p>
			<div class="em-phq9__share">
				<button type="button" class="em-phq9__print"><?php echo esc_html__('Print / Save PDF','emindy-core'); ?></button>
				<button type="button" class="em-phq9__copy"><?php echo esc_html__('Copy summary','emindy-core'); ?></button>
				<button type="button" class="em-phq9__sharelink" data-kind="gad7"><?php echo esc_html__('Get shareable link','emindy-core'); ?></button>
				<button type="button" class="em-phq9__email" data-kind="gad7"><?php echo esc_html__('Email me the summary','emindy-core'); ?></button>
			</div>
		</div>
	</form>
	<?php
	return ob_get_clean();
}

	/**
	 * Render the signed PHQ-9/GAD-7 assessment result card.
	 *
	 * Expects `type`, `score`, and `sig` query parameters signed with the
	 * WordPress auth salt. Outputs a translated summary or a graceful error when
	 * the signature is invalid.
	 *
	 * @return string HTML markup for the assessment result.
	 */
        public static function assessment_result() : string {
            $type_raw  = isset( $_GET['type'] ) ? wp_unslash( $_GET['type'] ) : '';
            $score_raw = isset( $_GET['score'] ) ? wp_unslash( $_GET['score'] ) : null;
            $sig_raw   = isset( $_GET['sig'] ) ? wp_unslash( $_GET['sig'] ) : '';

            $type       = sanitize_key( $type_raw );
            $score_val  = filter_var( $score_raw, FILTER_VALIDATE_INT );
            $score      = ( false === $score_val || null === $score_val ) ? -1 : absint( $score_val );
            $sig        = is_string( $sig_raw ) ? trim( $sig_raw ) : '';
            $valid_type = [ 'phq9' => 27, 'gad7' => 21 ];

            if ( '' === $type || ! isset( $valid_type[ $type ] ) ) {
                $type = '';
            }

            // Ensure the signature looks like a SHA-256 hex digest before comparing.
            if ( ! preg_match( '/^[a-f0-9]{64}$/i', $sig ) ) {
                $sig = '';
            }

            $max_score = $type ? $valid_type[ $type ] : 0;

            // The signed URL prevents tampering with the score/type when users share links.
            $secret = wp_salt( 'auth' );
            $calc   = $type ? hash_hmac( 'sha256', $type . '|' . $score, $secret ) : '';

            if ( ! $type || $score < 0 || $score > $max_score || ! $sig || ! hash_equals( $calc, $sig ) ) {
                return '<div class="em-phq9 is-style-em-card"><p>' . esc_html__( 'Invalid or missing result.', 'emindy-core' ) . '</p></div>';
            }

            $title = ( 'phq9' === $type ) ? __( 'PHQ-9 Result', 'emindy-core' ) : __( 'GAD-7 Result', 'emindy-core' );

            // Map the numeric score to a severity band.  Use translation functions on
            // the band names so they can be localised.  See
            // https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/【870389742309372†L0-L10】
            $band = '';
            if ( 'phq9' === $type ) {
                if ( $score <= 4 ) {
                    $band = __( 'Minimal', 'emindy-core' );
                } elseif ( $score <= 9 ) {
                    $band = __( 'Mild', 'emindy-core' );
                } elseif ( $score <= 14 ) {
                    $band = __( 'Moderate', 'emindy-core' );
                } elseif ( $score <= 19 ) {
                    $band = __( 'Moderately severe', 'emindy-core' );
                } else {
                    $band = __( 'Severe', 'emindy-core' );
                }
            } elseif ( 'gad7' === $type ) {
                if ( $score <= 4 ) {
                    $band = __( 'Minimal', 'emindy-core' );
                } elseif ( $score <= 9 ) {
                    $band = __( 'Mild', 'emindy-core' );
                } elseif ( $score <= 14 ) {
                    $band = __( 'Moderate', 'emindy-core' );
                } else {
                    $band = __( 'Severe', 'emindy-core' );
                }
            }

            ob_start(); ?>
                <div class="em-phq9 is-style-em-card">
                        <h2><?php echo esc_html( $title ); ?></h2>
                <p><?php
                    /*
                     * Wrap the score line in a translation call so that the entire
                     * sentence can be localised.  We use sprintf on the result of
                     * __() rather than on a literal string to allow translators to
                     * rearrange the placeholders as needed.
                     */
                    $score_line = sprintf( __( 'Score: %d / %d — %s', 'emindy-core' ), $score, $max_score, $band );
                    echo esc_html( $score_line );
                ?></p>
                        <p><?php echo esc_html__( 'This check is educational, not a diagnosis. If you feel unsafe or in crisis, please visit the Emergency page.', 'emindy-core' ); ?></p>
                        <p><a href="<?php echo esc_url( home_url( '/assessments/' ) ); ?>">&larr; <?php echo esc_html__( 'Back to assessments', 'emindy-core' ); ?></a></p>
                </div>
                <?php
                return ob_get_clean();
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

// [em_blog_categories pills="1"]
add_shortcode('em_blog_categories', function($atts){
  $a = shortcode_atts(['pills'=>'0'], $atts, 'em_blog_categories');
  $pills = ( isset( $a['pills'] ) && '1' === $a['pills'] );
  $cats = get_categories(['hide_empty'=>true]);
  if(!$cats) return '';
  $out = '<div class="em-cat-pills" style="display:flex;flex-wrap:wrap;gap:.5rem">';
  foreach($cats as $c){
    $url = esc_url(get_category_link($c->term_id));
    $name = esc_html($c->name);
    $out .= '<a href="'.$url.'" style="background:'.($pills ? '#F4D483' : 'none').';color:#0A2A43;padding:.4rem .8rem;border-radius:999px;text-decoration:none;font-weight:600">'.$name.'</a>';
  }
  return $out.'</div>';
});

// 2.1) [em_search_query] — نمایش عبارت جست‌وجو
// 2.1) Current query helper (اگر قبلاً داری، همین را نگه‌دار)
add_shortcode('em_search_query', function($atts){
  $a = shortcode_atts(['raw'=>'0','url'=>'0'], $atts, 'em_search_query');
  // Sanitize the search query to prevent injection and strip tags.
  $s = isset($_GET['s']) ? sanitize_text_field( (string) wp_unslash($_GET['s']) ) : '';
  if ($a['url'] === '1') return rawurlencode($s);
  if ($a['raw'] === '1') return esc_attr($s);
  return esc_html($s);
});

// 2.2) Search bar (فرم کامل)
add_shortcode('em_search_bar', function(){
  // Sanitize the search query to prevent injection and strip tags.
  $s = isset($_GET['s']) ? sanitize_text_field( (string) wp_unslash($_GET['s']) ) : '';
  $s_attr = esc_attr($s);
  $action = esc_url( home_url('/') );
  ob_start(); ?>
  <form role="search" method="get" action="<?php echo $action; ?>" class="em-search-bar" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:.25rem 0 .75rem">
    <label for="em-s" class="sr-only"><?php echo esc_html__( 'Search', 'emindy-core' ); ?></label>
    <input id="em-s" type="search" name="s" value="<?php echo $s_attr; ?>" placeholder="<?php echo esc_attr__( 'Search videos, exercises, articles, blog…', 'emindy-core' ); ?>" style="flex:1;min-width:260px;padding:.55rem .8rem;border-radius:.75rem;border:0">
    <button type="submit" style="padding:.6rem .95rem;border-radius:.75rem;border:0;background:#F4D483;color:#0A2A43;font-weight:600"><?php echo esc_html__( 'Search', 'emindy-core' ); ?></button>
  </form>
  <?php
  return ob_get_clean();
});

// 2.3) Quick filters
add_shortcode('em_quick_filters', function(){
  // Sanitize the search query to prevent injection and strip tags.
  $s    = isset($_GET['s']) ? sanitize_text_field( (string) wp_unslash($_GET['s']) ) : '';
  $home = home_url('/');

  $links = [
    add_query_arg( 's', $s, $home )                                                 => __( 'All', 'emindy-core' ),
    add_query_arg( [ 's' => $s, 'post_type' => 'em_video' ], $home )                => __( 'Videos', 'emindy-core' ),
    add_query_arg( [ 's' => $s, 'post_type' => 'em_exercise' ], $home )             => __( 'Exercises', 'emindy-core' ),
    add_query_arg( [ 's' => $s, 'post_type' => 'em_article' ], $home )              => __( 'Articles', 'emindy-core' ),
    add_query_arg( [ 's' => $s, 'post_type' => 'post' ], $home )                    => __( 'Blog', 'emindy-core' ),
  ];
  ob_start(); ?>
  <nav class="em-quick-filters" aria-label="<?php echo esc_attr__( 'Quick filters', 'emindy-core' ); ?>" style="display:flex;gap:.5rem;flex-wrap:wrap">
    <?php foreach($links as $url=>$label): ?>
      <a class="pill" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
    <?php endforeach; ?>
  </nav>
  <?php
  return ob_get_clean();
});


// 2.2) [em_result_badge] — Badge نوع محتوا برای آیتم فعلی
add_shortcode('em_result_badge', function(){
  $pt = sanitize_key( get_post_type() );
  $map = [
    'em_video'    => __( 'Video', 'emindy-core' ),
    'em_exercise' => __( 'Exercise', 'emindy-core' ),
    'em_article'  => __( 'Article', 'emindy-core' ),
    'post'        => __( 'Blog', 'emindy-core' ),
  ];
  $label = isset($map[$pt]) ? $map[$pt] : ucfirst($pt);
  return '<span class="em-badge em-badge-'.esc_attr($pt).'" aria-label="'. esc_attr__( 'Content type', 'emindy-core' ) .'">'.esc_html($label).'</span>';
});

// 2.3) [em_excerpt_highlight length="28"] — خلاصه با هایلایت
add_shortcode('em_excerpt_highlight', function($atts){
  $a = shortcode_atts(['length'=>'28'], $atts, 'em_excerpt_highlight');
  // Sanitize the search query to prevent injection and strip tags.
  $s = isset($_GET['s']) ? sanitize_text_field( (string) wp_unslash($_GET['s']) ) : '';
  $text = get_the_excerpt();
  if ( ! $text ) {
    $text = get_the_content('', false);
  }
  $text = wp_trim_words( wp_strip_all_tags( (string) $text ), (int) $a['length'], '…' );

  $s = trim($s);
  $highlighted = $text;

  if ($s !== '' && mb_strlen($s, 'UTF-8') <= 80) {
    $pattern = '/' . preg_quote($s, '/') . '/iu';
    $highlighted = @preg_replace($pattern, '<mark>$0</mark>', $text);
    if ($highlighted === null) {
      $highlighted = $text;
    }
  }

  $safe = wp_kses( $highlighted, [ 'mark' => [] ] );
  return '<p class="em-excerpt">'.$safe.'</p>';
});

/**
 * [em_result_count] – Search results listing with sanitised query input.
 *
 * Accepts `post_type` to filter the query and mirrors the front-end search
 * term from the request after stripping tags. Fetches only IDs to keep the
 * query lightweight before rendering the cards.
 */
add_shortcode('em_result_count', function($atts){
  $a = shortcode_atts(['post_type'=>'post'], $atts, 'em_result_count');
  $pt = is_array($a['post_type']) ? $a['post_type'] : [$a['post_type']];
  $pt = array_values(array_filter($pt, 'post_type_exists'));

  // Sanitize the search query to prevent injection and strip tags.
  $s = isset($_GET['s']) ? sanitize_text_field( (string) wp_unslash($_GET['s']) ) : '';
  $q = new \WP_Query([
    'post_type'        => $pt ?: 'post',
    's'                => $s,
    'posts_per_page'   => 1,
    'fields'           => 'ids',
    'no_found_rows'    => false,
    'suppress_filters' => false,
  ]);
  return (string) intval($q->found_posts);
});

// 2.5) [em_search_section post_type="em_video" per_page="4"]
add_shortcode('em_search_section', function($atts){
  $a = shortcode_atts(['post_type'=>'post','per_page'=>'4'], $atts, 'em_search_section');
  $pt = is_array($a['post_type']) ? $a['post_type'] : [$a['post_type']];
  $pt = array_values(array_filter($pt, 'post_type_exists'));
  // Sanitize the search query to prevent injection and strip tags.
  $s  = isset($_GET['s']) ? sanitize_text_field( (string) wp_unslash($_GET['s']) ) : '';

  $q = new \WP_Query([
    'post_type'        => $pt ?: 'post',
    's'                => $s,
    'posts_per_page'   => max(1, min(6, (int)$a['per_page'])),
    'orderby'          => 'date',
    'order'            => 'DESC',
    'no_found_rows'    => true,
    'suppress_filters' => false,
  ]);
  if (!$q->have_posts()) return '<p class="em-muted">'. esc_html__( 'No matches.', 'emindy-core' ) .'</p>';

  ob_start();
  echo '<div class="em-search-list">';
  while ($q->have_posts()){ $q->the_post();
    echo '<article class="em-search-item" style="display:flex;gap:1rem;align-items:center;margin:.6rem 0;padding:.6rem 0">';
      if (has_post_thumbnail()){
        echo '<a href="'.esc_url(get_permalink()).'">'.get_the_post_thumbnail(null,'medium',['style'=>'width:160px;height:auto;border-radius:12px']).'</a>';
      }
      echo '<div>';
        echo '<div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">';
          echo '<a href="'.esc_url(get_permalink()).'"><strong>'.esc_html(get_the_title()).'</strong></a>';
          if (shortcode_exists('em_result_badge')) echo do_shortcode('[em_result_badge]');
        echo '</div>';
        echo do_shortcode('[em_excerpt_highlight length="26"]');
      echo '</div>';
    echo '</article>';
  }
  echo '</div>';
  wp_reset_postdata();
  return ob_get_clean();
});

/**
 * [em_popular_posts] – Latest blog posts for quick navigation.
 *
 * Uses a constrained `WP_Query` with `no_found_rows` to reduce DB load when
 * the shortcode is embedded multiple times on a page.
 */
add_shortcode('em_popular_posts', function($atts){
  $a = shortcode_atts(['limit'=>'4'], $atts, 'em_popular_posts');
  $q = new \WP_Query([
    'post_type' => 'post',
    'posts_per_page' => max(1,(int)$a['limit']),
    'orderby'=>'date','order'=>'DESC',
    'no_found_rows'=>true
  ]);
  if(!$q->have_posts()) return '<p class="em-muted">'. esc_html__( 'No posts yet.', 'emindy-core' ) .'</p>';
  ob_start(); echo '<ul class="em-mini-list">';
  while($q->have_posts()){ $q->the_post();
    echo '<li><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></li>';
  }
  echo '</ul>'; wp_reset_postdata(); return ob_get_clean();
});

/**
 * [em_popular_videos] – Latest videos (analytics weighting can replace date).
 *
 * Leans on `no_found_rows` to avoid pagination calculations while keeping the
 * shortcode reusable in sidebars and templates.
 */
add_shortcode('em_popular_videos', function($atts){
  $a = shortcode_atts(['limit'=>'4'], $atts, 'em_popular_videos');
  $q = new \WP_Query([
    'post_type'=>'em_video','posts_per_page'=>max(1,(int)$a['limit']),
    'orderby'=>'date','order'=>'DESC','no_found_rows'=>true
  ]);
  if(!$q->have_posts()) return '<p class="em-muted">'. esc_html__( 'No videos yet.', 'emindy-core' ) .'</p>';
  ob_start(); echo '<ul class="em-mini-list">';
  while($q->have_posts()){ $q->the_post();
    echo '<li><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></li>';
  }
  echo '</ul>'; wp_reset_postdata(); return ob_get_clean();
});

/**
 * [em_popular_exercises] – Latest exercises listing with minimal query cost.
 *
 * Designed to be swapped for a views-based ordering later; keeps the query
 * intentionally light to avoid slowing archive pages.
 */
add_shortcode('em_popular_exercises', function($atts){
  $a = shortcode_atts(['limit'=>'4'], $atts, 'em_popular_exercises');
  $q = new \WP_Query([
    'post_type'=>'em_exercise','posts_per_page'=>max(1,(int)$a['limit']),
    'orderby'=>'date','order'=>'DESC','no_found_rows'=>true
  ]);
  if(!$q->have_posts()) return '<p class="em-muted">'. esc_html__( 'No exercises yet.', 'emindy-core' ) .'</p>';
  ob_start(); echo '<ul class="em-mini-list">';
  while($q->have_posts()){ $q->the_post();
    echo '<li><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></li>';
  }
  echo '</ul>'; wp_reset_postdata(); return ob_get_clean();
});

// 2.4) Mini sitemap (top hubs)
add_shortcode('em_sitemap_mini', function(){
  $links = [
    '/start-here/'  => __( 'Start Here — Essentials', 'emindy-core' ),
    '/library/'     => __( 'Library (All content)', 'emindy-core' ),
    '/em_video/'    => __( 'Videos', 'emindy-core' ),
    '/em_exercise/' => __( 'Exercises', 'emindy-core' ),
    '/articles/'    => __( 'Articles Hub', 'emindy-core' ),
    '/phq-9/'       => __( 'PHQ-9 Assessment', 'emindy-core' ),
    '/ask-for-help/'=> __( 'Ask for Help', 'emindy-core' ),
  ];
  $out = '<ul class="em-mini-list">';
  foreach($links as $url=>$label){
    $out .= '<li><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>';
  }
  return $out.'</ul>';
});

// 2.5) Report broken link (simple form -> email)
add_shortcode('em_report_link', function($atts){
  $a = shortcode_atts(['id'=>'report'], $atts, 'em_report_link');
  $uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
  $curr = esc_url_raw( home_url( $uri ) );
  $mailto = sanitize_email( antispambot( get_option('admin_email') ) );
  $subject = rawurlencode( __( 'Broken link report on eMINDy', 'emindy-core' ) );
  $body_template = __( "Broken URL:\n%s\n\nUser note:", 'emindy-core' );
  $body = rawurlencode( sprintf( $body_template, $curr ) );
  $href = 'mailto:' . rawurlencode( $mailto ) . '?subject=' . $subject . '&body=' . $body;
  return '<p id="'.esc_attr($a['id']).'"><a class="em-button" href="'. esc_url( $href ) .'">'. esc_html__( 'Report this link', 'emindy-core' ) .'</a></p>';
});

add_shortcode('em_reading_time', function(){
  $content = get_post_field('post_content', get_the_ID());
  $words = str_word_count( wp_strip_all_tags( $content ) );
  $minutes = max(1, ceil($words / 200)); // 200 wpm
  $estimate = sprintf( __( '~%d min read', 'emindy-core' ), $minutes );
  return '<span aria-label="'. esc_attr__( 'Estimated reading time', 'emindy-core' ) .'">'. esc_html( $estimate ) .'</span>';
});

add_shortcode('em_toc', function(){
  $html = apply_filters('the_content', get_post_field('post_content', get_the_ID()));
  if(!$html) return '';
  $dom = new \DOMDocument(); libxml_use_internal_errors(true);
  $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
  libxml_clear_errors();
  $xpath = new \DOMXPath($dom);
  $heads = $xpath->query('//h2|//h3');
  if(!$heads || $heads->length===0) return '';
  $items = [];
  foreach($heads as $h){
    $text = trim($h->textContent);
    if(!$text) continue;
    $id = $h->getAttribute('id');
    if(!$id){
      $id = sanitize_title($text);
      $h->setAttribute('id',$id);
    }
    $items[] = ['id'=>$id,'text'=>$text,'level'=>strtolower($h->nodeName)];
  }
  if(!$items) return '';
  $out = '<nav class="em-toc" aria-label="'. esc_attr__( 'Table of contents', 'emindy-core' ) .'"><ol>';
  foreach($items as $it){
    $pad = ($it['level']==='h3') ? ' style="margin-left:1rem"' : '';
    $out .= '<li'.$pad.'><a href="#'.esc_attr($it['id']).'">'.esc_html($it['text']).'</a></li>';
  }
  $out .= '</ol></nav>';
  return $out;
});

add_shortcode('em_share', function(){
  $permalink = get_permalink();
  if ( ! $permalink ) {
    return '';
  }

  $title = get_the_title() ?: '';

  $links = [
    'twitter'  => 'https://twitter.com/intent/tweet?' . http_build_query( [ 'url' => $permalink, 'text' => $title ] ),
    'facebook' => 'https://www.facebook.com/sharer/sharer.php?' . http_build_query( [ 'u' => $permalink ] ),
    'linkedin' => 'https://www.linkedin.com/shareArticle?' . http_build_query( [ 'mini' => 'true', 'url' => $permalink, 'title' => $title ] ),
    'whatsapp' => 'https://api.whatsapp.com/send?' . http_build_query( [ 'text' => trim( $title . ' ' . $permalink ) ] ),
  ];

  $out = '<div class="em-share" style="display:flex;flex-wrap:wrap;gap:.5rem">'
    .'<a class="em-button" href="'.esc_url( $links['twitter'] ).'" target="_blank" rel="noopener noreferrer">'. esc_html__( 'X/Twitter', 'emindy-core' ) .'</a>'
    .'<a class="em-button" href="'.esc_url( $links['facebook'] ).'" target="_blank" rel="noopener noreferrer">'. esc_html__( 'Facebook', 'emindy-core' ) .'</a>'
    .'<a class="em-button" href="'.esc_url( $links['linkedin'] ).'" target="_blank" rel="noopener noreferrer">'. esc_html__( 'LinkedIn', 'emindy-core' ) .'</a>'
    .'<a class="em-button" href="'.esc_url( $links['whatsapp'] ).'" target="_blank" rel="noopener noreferrer">'. esc_html__( 'WhatsApp', 'emindy-core' ) .'</a>'
    .'<button type="button" class="em-button" data-clipboard="'. esc_attr( $permalink ) .'" onclick="navigator.clipboard.writeText(this.getAttribute(\'data-clipboard\'))">'. esc_html__( 'Copy link', 'emindy-core' ) .'</button>'
    .'</div>';
  return $out;
});

// [em_i18n key="help_title" default="We couldn’t find that page."]
add_shortcode('em_i18n', function($atts){
  $a = shortcode_atts(['key'=>'','default'=>''], $atts, 'em_i18n');
  if(!$a['key']) return esc_html($a['default']);
  $val = function_exists('pll__') ? pll__($a['key']) : '';
  if(!$val) $val = $a['default'];
  return esc_html($val);
});

// [em_admin_notice_missing_pages]
add_shortcode('em_admin_notice_missing_pages', function(){
  if( ! current_user_can('manage_options') ) return '';
  $out = [];
  if( ! get_page_by_path('blog') ) {
    $out[] = esc_html__( 'Missing page: /blog (Posts landing)', 'emindy-core' );
  }
  if( ! get_page_by_path('library') ) {
    $out[] = esc_html__( 'Missing page: /library (Library hub)', 'emindy-core' );
  }
  if( empty($out) ) return '';
  $html = '<div class="em-admin-note" style="margin-top:1rem;padding:.8rem;border:1px solid #f5d27d;border-radius:8px;background:#fff9e8">';
  $html .= '<strong>' . esc_html__( 'Admin note:', 'emindy-core' ) . '</strong><ul style="margin:.4rem 0 0 .9rem">';
  foreach($out as $li){ $html .= '<li>'.$li.'</li>'; }
  $html .= '</ul><p>' . esc_html__( 'Tip: You can create them or set a redirect (see em_redirect_missing_pages).', 'emindy-core' ) . '</p></div>';
  return $html;
});

// [em_topics_pills taxonomy="topic"]
add_shortcode('em_topics_pills', function($atts){
  // Default to the `topic` taxonomy unless a custom taxonomy is provided.
  $a = shortcode_atts(['taxonomy'=>'topic'], $atts, 'em_topics_pills');
  $taxonomy = sanitize_key( $a['taxonomy'] );
  $terms = get_terms(['taxonomy'=>$taxonomy, 'hide_empty'=>true]);
  if (is_wp_error($terms) || empty($terms)) return '<span>'. esc_html__( 'No topics yet.', 'emindy-core' ) .'</span>';

  $out = '<div class="em-topic-pills" role="navigation" aria-label="'. esc_attr__( 'Filter by topic', 'emindy-core' ) .'">';
  $archive_url = get_post_type_archive_link('em_video');
  // "All" link
  $out .= '<a class="pill" href="'.esc_url($archive_url).'">'. esc_html__( 'All', 'emindy-core' ) .'</a>';
  foreach ($terms as $t) {
    $out .= '<a class="pill" href="'.esc_url(get_term_link($t)).'">'.esc_html($t->name).'</a>';
  }
  $out .= '</div>';
  return $out;
});
