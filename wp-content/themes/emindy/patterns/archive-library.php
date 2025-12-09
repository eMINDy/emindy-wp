<?php
/**
 * Title: Complete Archive
 * Slug: emindy/archive-library
 * Description: A comprehensive archive page listing all videos, exercises, articles and blog posts, with search and topic filtering.
 * Categories: emindy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!--
This pattern creates a single page that aggregates all content types on the site.
It features a global search form and topic pills for quick filtering. Each section
has its own query loop and heading so visitors can easily navigate through
videos, exercises, articles and blog posts.
The design relies on CSS variables for colours so dark mode works properly.
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained","contentSize":"1200px"}} -->
<main id="main-content" class="wp-block-group">
  <!-- Header -->
  <!-- wp:group {"tagName":"header","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
  <header class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1><?php echo esc_html__( 'Complete Archive', 'emindy' ); ?></h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Browse every piece of content on eMINDy: videos, exercises, articles and blog posts. Use search or filter by topic to narrow your results.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </header>
  <!-- /wp:group -->

  <!-- Search & topic filter -->
  <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"0.75rem","bottom":"0.75rem","left":"0.75rem","right":"0.75rem"},"margin":{"bottom":"1rem"}}}} -->
  <div class="wp-block-group is-style-em-card" style="padding:.75rem;margin-bottom:1rem">
    <!-- Global search form -->
    <!-- wp:html -->
    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="em-full-archive-search" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:.25rem 0 .75rem">
      <label for="emarchive-hub-s" class="sr-only"><?php echo esc_html__( 'Search the archive', 'emindy' ); ?></label>
      <input id="emarchive-hub-s" type="search" name="s" placeholder="<?php echo esc_attr__( 'Search videos, exercises, articles, blog…', 'emindy' ); ?>" style="flex:1;min-width:220px;padding:.5rem .75rem;border-radius:.75rem;border:1px solid var(--em-border);background:var(--em-card);color:var(--em-text)">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'em_video' ); ?>">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'em_exercise' ); ?>">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'em_article' ); ?>">
      <input type="hidden" name="post_type[]" value="<?php echo esc_attr( 'post' ); ?>">
      <button type="submit" style="padding:.55rem .9rem;border-radius:.75rem;border:0;background:var(--em-gold);color:var(--em-bg);font-weight:600">
        <?php echo esc_html__( 'Search', 'emindy' ); ?>
      </button>
    </form>
    <!-- /wp:html -->
    <!-- Topic pills -->
    <!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.5rem"}}} -->
    <div class="wp-block-group">
      <!-- wp:paragraph --><p><strong><?php echo esc_html__( 'Topics:', 'emindy' ); ?></strong></p><!-- /wp:paragraph -->
      <!-- wp:shortcode -->
      [em_topics_pills taxonomy="topic"]
      <!-- /wp:shortcode -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->

  <!-- Videos section -->
  <!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
  <div class="wp-block-group" style="margin-bottom:1rem">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Videos', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:query {"query":{"perPage":6,"postType":"em_video","order":"desc","orderBy":"date"},"displayLayout":{"type":"grid","columns":3}} -->
    <div class="wp-block-query">
      <!-- wp:post-template -->
      <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group is-style-em-card" style="overflow:hidden">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
        <!-- wp:group {"style":{"spacing":{"padding":{"top":"0.8rem","bottom":"0.8rem","left":"0.8rem","right":"0.8rem"}}}} -->
        <div class="wp-block-group" style="padding:.8rem">
          <!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"1.1rem","lineHeight":"1.3"}}} /-->
          <!-- wp:post-excerpt {"moreText":"<?php echo esc_attr__( 'Watch →', 'emindy' ); ?>","excerptLength":18} /-->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:group -->
      <!-- /wp:post-template -->
      <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
      <div class="wp-block-query-pagination">
        <!-- wp:query-pagination-previous /-->
        <!-- wp:query-pagination-numbers /-->
        <!-- wp:query-pagination-next /-->
      </div>
      <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->
    <!-- Link to full video archive -->
    <!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0.4rem"}}}} -->
    <p style="margin-top:.4rem"><a href="<?php echo esc_url( home_url( '/video-library/' ) ); ?>" class="em-button" style="background:var(--em-gold);color:var(--em-bg);padding:.5rem .9rem;border-radius:999px;font-weight:600;text-decoration:none"><?php echo esc_html__( 'More videos', 'emindy' ); ?> →</a></p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /Videos section -->

  <!-- Exercises section -->
  <!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
  <div class="wp-block-group" style="margin-bottom:1rem">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Exercises', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:query {"query":{"perPage":6,"postType":"em_exercise","order":"desc","orderBy":"date"},"displayLayout":{"type":"grid","columns":3}} -->
    <div class="wp-block-query">
      <!-- wp:post-template -->
      <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group is-style-em-card" style="overflow:hidden">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
        <!-- wp:group {"style":{"spacing":{"padding":{"top":"0.8rem","bottom":"0.8rem","left":"0.8rem","right":"0.8rem"}}}} -->
        <div class="wp-block-group" style="padding:.8rem">
          <!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"1.1rem","lineHeight":"1.3"}}} /-->
          <!-- wp:post-excerpt {"moreText":"<?php echo esc_attr__( 'Try →', 'emindy' ); ?>","excerptLength":18} /-->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:group -->
      <!-- /wp:post-template -->
      <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
      <div class="wp-block-query-pagination">
        <!-- wp:query-pagination-previous /-->
        <!-- wp:query-pagination-numbers /-->
        <!-- wp:query-pagination-next /-->
      </div>
      <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->
    <!-- Link to full exercise archive -->
    <!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0.4rem"}}}} -->
    <p style="margin-top:.4rem"><a href="<?php echo esc_url( home_url( '/exercise-library/' ) ); ?>" class="em-button" style="background:var(--em-gold);color:var(--em-bg);padding:.5rem .9rem;border-radius:999px;font-weight:600;text-decoration:none"><?php echo esc_html__( 'More exercises', 'emindy' ); ?> →</a></p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /Exercises section -->

  <!-- Articles section -->
  <!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
  <div class="wp-block-group" style="margin-bottom:1rem">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Articles', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:query {"query":{"perPage":6,"postType":"em_article","order":"desc","orderBy":"date"},"displayLayout":{"type":"grid","columns":3}} -->
    <div class="wp-block-query">
      <!-- wp:post-template -->
      <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group is-style-em-card" style="overflow:hidden">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
        <!-- wp:group {"style":{"spacing":{"padding":{"top":"0.8rem","bottom":"0.8rem","left":"0.8rem","right":"0.8rem"}}}} -->
        <div class="wp-block-group" style="padding:.8rem">
          <!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"1.1rem","lineHeight":"1.3"}}} /-->
          <!-- wp:post-excerpt {"moreText":"<?php echo esc_attr__( 'Read →', 'emindy' ); ?>","excerptLength":18} /-->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:group -->
      <!-- /wp:post-template -->
      <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
      <div class="wp-block-query-pagination">
        <!-- wp:query-pagination-previous /-->
        <!-- wp:query-pagination-numbers /-->
        <!-- wp:query-pagination-next /-->
      </div>
      <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->
    <!-- Link to full article archive -->
    <!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0.4rem"}}}} -->
    <p style="margin-top:.4rem"><a href="<?php echo esc_url( home_url( '/article-library/' ) ); ?>" class="em-button" style="background:var(--em-gold);color:var(--em-bg);padding:.5rem .9rem;border-radius:999px;font-weight:600;text-decoration:none"><?php echo esc_html__( 'More articles', 'emindy' ); ?> →</a></p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /Articles section -->

  <!-- Blog posts section -->
  <!-- wp:group {"layout":{"type":"constrained"}} -->
  <div class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Blog Posts', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:query {"query":{"perPage":6,"postType":"post","order":"desc","orderBy":"date"},"displayLayout":{"type":"grid","columns":3}} -->
    <div class="wp-block-query">
      <!-- wp:post-template -->
      <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group is-style-em-card" style="overflow:hidden">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
        <!-- wp:group {"style":{"spacing":{"padding":{"top":"0.8rem","bottom":"0.8rem","left":"0.8rem","right":"0.8rem"}}}} -->
        <div class="wp-block-group" style="padding:.8rem">
          <!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"1.1rem","lineHeight":"1.3"}}} /-->
          <!-- wp:post-excerpt {"moreText":"<?php echo esc_attr__( 'Read →', 'emindy' ); ?>","excerptLength":18} /-->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:group -->
      <!-- /wp:post-template -->
      <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
      <div class="wp-block-query-pagination">
        <!-- wp:query-pagination-previous /-->
        <!-- wp:query-pagination-numbers /-->
        <!-- wp:query-pagination-next /-->
      </div>
      <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->
    <!-- Link to blog archive -->
    <!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0.4rem"}}}} -->
    <p style="margin-top:.4rem"><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="em-button" style="background:var(--em-gold);color:var(--em-bg);padding:.5rem .9rem;border-radius:999px;font-weight:600;text-decoration:none"><?php echo esc_html__( 'More posts', 'emindy' ); ?> →</a></p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /Blog posts section -->

</main>
<!-- /wp:group -->
