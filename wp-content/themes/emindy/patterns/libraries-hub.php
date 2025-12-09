<?php
/**
 * Title: Library Hub
 * Slug: emindy/libraries-hub
 * Description: Hub page for the eMINDy library that links users to videos, exercises, articles and blog posts.
 * Categories: emindy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!--
The library hub (e.g. /libraries/) provides an overview of all content types on eMINDy.
It offers a global search across videos, exercises, articles and posts, along with quick
entry cards for each category.  Use this hub to guide users to specific archives or
to the unified archive library (/archive-library/).
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained","contentSize":"1200px"}} -->
<main id="main-content" class="wp-block-group">
  <!-- Heading & intro -->
  <!-- wp:group {"tagName":"header","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
  <header class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1><?php echo esc_html__( 'Library', 'emindy' ); ?></h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Browse our entire collection of videos, exercises, articles and blog posts. Use the search below or choose a category.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </header>
  <!-- /wp:group -->

  <!-- Global search form -->
  <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"0.75rem","bottom":"0.75rem","left":"0.75rem","right":"0.75rem"},"margin":{"bottom":"1rem"}}}} -->
  <div class="wp-block-group is-style-em-card" style="padding:.75rem;margin-bottom:1rem">
    <!-- wp:html -->
    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="em-library-search" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:.25rem 0 .75rem">
      <label for="emlibrary-hub-s" class="sr-only"><?php echo esc_html__( 'Search the library', 'emindy' ); ?></label>
      <input id="emlibrary-hub-s" type="search" name="s" placeholder="<?php echo esc_attr__( 'Search videos, exercises, articles, blog…', 'emindy' ); ?>" style="flex:1;min-width:220px;padding:.5rem .75rem;border-radius:.75rem;border:1px solid var(--em-border);background:var(--em-card);color:var(--em-text)">
      <!-- hidden inputs to search across all supported post types: em_video, em_exercise, em_article and post -->
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'em_video' ); ?>">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'em_exercise' ); ?>">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'em_article' ); ?>">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'post' ); ?>">
      <button type="submit" style="padding:.55rem .9rem;border-radius:.75rem;border:0;background:var(--em-gold);color:var(--em-bg);font-weight:600">
        <?php echo esc_html__( 'Search', 'emindy' ); ?>
      </button>
    </form>
    <!-- /wp:html -->
  </div>
  <!-- /wp:group -->

  <!-- Category cards: Videos, Exercises, Articles, Blog -->
  <!-- wp:group {"layout":{"type":"constrained"}} -->
  <div class="wp-block-group">
    <!-- wp:columns {"style":{"spacing":{"blockGap":"1rem"}}} -->
    <div class="wp-block-columns">
      <!-- Videos card -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"1rem","bottom":"1rem","left":"1rem","right":"1rem"}}}} -->
        <div class="wp-block-group is-style-em-card" style="padding:1rem">
          <!-- wp:heading {"level":3} -->
          <h3><?php echo esc_html__( 'Videos', 'emindy' ); ?></h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph -->
          <p><?php echo esc_html__( 'Guided meditations and educational clips.', 'emindy' ); ?></p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph -->
          <p><a href="<?php echo esc_url( home_url( '/videos/' ) ); ?>"><?php echo esc_html__( 'Visit hub →', 'emindy' ); ?></a> | <a href="<?php echo esc_url( home_url( '/video-library/' ) ); ?>"><?php echo esc_html__( 'Full archive →', 'emindy' ); ?></a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
      <!-- Exercises card -->
      <div class="wp-block-column">
        <div class="wp-block-group is-style-em-card" style="padding:1rem">
          <h3><?php echo esc_html__( 'Exercises', 'emindy' ); ?></h3>
          <p><?php echo esc_html__( 'Practical tools for calm and clarity.', 'emindy' ); ?></p>
          <p><a href="<?php echo esc_url( home_url( '/exercises/' ) ); ?>"><?php echo esc_html__( 'Visit hub →', 'emindy' ); ?></a> | <a href="<?php echo esc_url( home_url( '/exercise-library/' ) ); ?>"><?php echo esc_html__( 'Full archive →', 'emindy' ); ?></a></p>
        </div>
      </div>
      <!-- /wp:column -->
      <!-- Articles card -->
      <div class="wp-block-column">
        <div class="wp-block-group is-style-em-card" style="padding:1rem">
          <h3><?php echo esc_html__( 'Articles', 'emindy' ); ?></h3>
          <p><?php echo esc_html__( 'Insights, stories and guides.', 'emindy' ); ?></p>
          <p><a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>"><?php echo esc_html__( 'Visit hub →', 'emindy' ); ?></a> | <a href="<?php echo esc_url( home_url( '/article-library/' ) ); ?>"><?php echo esc_html__( 'Full archive →', 'emindy' ); ?></a></p>
        </div>
      </div>
      <!-- /wp:column -->
      <!-- Blog posts card -->
      <div class="wp-block-column">
        <div class="wp-block-group is-style-em-card" style="padding:1rem">
          <h3><?php echo esc_html__( 'Blog', 'emindy' ); ?></h3>
          <p><?php echo esc_html__( 'Latest news, tips and announcements.', 'emindy' ); ?></p>
          <p><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php echo esc_html__( 'Visit blog →', 'emindy' ); ?></a></p>
        </div>
      </div>
      <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
  </div>
  <!-- /wp:group -->

  <!-- Combined archive call to action -->
  <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"1rem","bottom":"1rem","left":"1rem","right":"1rem"},"margin":{"top":"1rem"}}}} -->
  <div class="wp-block-group is-style-em-card" style="margin-top:1rem;padding:1rem">
    <!-- wp:heading {"level":3} -->
    <h3><?php echo esc_html__( 'Looking for everything at once?', 'emindy' ); ?></h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Jump straight to the complete archive where you can browse all videos, exercises, articles and posts together.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
      <!-- wp:button {"className":"is-style-fill"} -->
      <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/archive-library/' ) ); ?>"><?php echo esc_html__( 'Go to archive', 'emindy' ); ?></a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->

</main>
<!-- /wp:group -->
