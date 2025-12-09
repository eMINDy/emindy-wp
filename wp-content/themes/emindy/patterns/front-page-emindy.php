<?php
/**
 * Title: eMINDy — Home (Final, Blocks-Only)
 * Slug: emindy/home-final
 * Categories: emindy
 * Block Types: core/post-content
 * Inserter: true
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- wp:group {"className":"is-style-em-card","style":{"spacing":{"padding":{"top":"32px","bottom":"28px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<!-- wp:heading {"level":1} -->
<h1><?php echo esc_html__( 'Be your own therapist — gently 🌿', 'emindy' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'Short, practical tools for calm, clarity, and self-kindness. Learn, practice, and grow — at your own pace.', 'emindy' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/videos/' ) ); ?>"><?php echo esc_html__( 'Start with Videos', 'emindy' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/exercises/' ) ); ?>"><?php echo esc_html__( 'Try an Exercise', 'emindy' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/assessments/' ) ); ?>"><?php echo esc_html__( 'Take an Assessment', 'emindy' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"28px"}}}} -->
<h2><?php echo esc_html__( 'Find your path', 'emindy' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":"16px"}}} -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<!-- wp:heading {"level":3} -->
<h3><?php echo esc_html__( 'Stress Relief', 'emindy' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo esc_html__( '8-session stress program.', 'emindy' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><a href="<?php echo esc_url( home_url( '/topics/stress/' ) ); ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<h3><?php echo esc_html__( 'Anxiety', 'emindy' ); ?></h3>
<p><?php echo esc_html__( 'Worry & mind clarity tools.', 'emindy' ); ?></p>
<p><a href="<?php echo esc_url( home_url( '/topics/anxiety/' ) ); ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<h3><?php echo esc_html__( 'Confidence', 'emindy' ); ?></h3>
<p><?php echo esc_html__( 'Self-kindness & growth toolkit.', 'emindy' ); ?></p>
<p><a href="<?php echo esc_url( home_url( '/topics/confidence/' ) ); ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<h3><?php echo esc_html__( 'Quick Reset', 'emindy' ); ?></h3>
<p><?php echo esc_html__( '1-minute calm practices.', 'emindy' ); ?></p>
<p><a href="<?php echo esc_url( home_url( '/topics/quick-reset/' ) ); ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"28px"}}}} -->
<h2><?php echo esc_html__( 'Start here — Essentials', 'emindy' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[lyte id="YOUTUBE_ID"]
<!-- /wp:shortcode -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"24px"},"blockGap":"24px"}}} -->
<div class="wp-block-columns" style="margin-top:24px">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3><?php echo esc_html__( 'Latest Videos', 'emindy' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":601,"query":{"perPage":6,"pages":0,"offset":0,"postType":"em_video","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
<div class="wp-block-query">
<!-- wp:post-template -->
<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<!-- wp:post-title {"isLink":true} /-->
<!-- wp:post-excerpt {"moreText":"<?php echo esc_html__( 'View →', 'emindy' ); ?>"} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"left"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3><?php echo esc_html__( 'Latest Exercises', 'emindy' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":602,"query":{"perPage":6,"pages":0,"offset":0,"postType":"em_exercise","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
<div class="wp-block-query">
<!-- wp:post-template -->
<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
<!-- wp:post-title {"isLink":true} /-->
<!-- wp:post-excerpt {"moreText":"<?php echo esc_html__( 'Try →', 'emindy' ); ?>"} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"left"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:group {"className":"is-style-em-card","style":{"spacing":{"padding":{"top":"22px","bottom":"22px","left":"20px","right":"20px"},"margin":{"top":"28px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card" style="margin-top:28px">
<!-- wp:heading {"level":3} -->
<h3><?php echo esc_html__( 'Check-in with yourself', 'emindy' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo esc_html__( 'Simple, non-diagnostic wellness checks.', 'emindy' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/phq-9/' ) ); ?>"><?php echo esc_html__( 'PHQ-9', 'emindy' ); ?></a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/gad-7/' ) ); ?>"><?php echo esc_html__( 'GAD-7', 'emindy' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"24px"}}}} -->
<h3><?php echo esc_html__( 'Stay in practice', 'emindy' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[em_newsletter]
<!-- /wp:shortcode -->

<!-- wp:details {"style":{"spacing":{"margin":{"top":"16px"}}}} -->
<details><summary><?php echo esc_html__( 'Credits & Resources', 'emindy' ); ?></summary>
<p><?php echo esc_html__( 'Voice: ElevenLabs (licensed). Video: Pexels/Pixabay/Freepik. Sound: Freesound.org (CC). Guidance: ChatGPT (OpenAI, GPT-5).', 'emindy' ); ?></p>
</details>
<!-- /wp:details -->

<!-- wp:details {"style":{"spacing":{"margin":{"top":"12px"}}}} -->
<details><summary><?php echo esc_html__( 'Safety & Disclaimer', 'emindy' ); ?></summary>
<p><?php echo esc_html__( 'This platform is for education and self-help. It’s not medical advice. If you’re in crisis, please visit the Emergency page.', 'emindy' ); ?> <a href="<?php echo esc_url( home_url( '/emergency/' ) ); ?>"><?php echo esc_html__( 'Emergency', 'emindy' ); ?></a></p>
</details>
<!-- /wp:details -->
