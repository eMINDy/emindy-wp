<?php
/**
 * Title: Video Hub
 * Slug: emindy/video-hub
 * Description: Hub page layout for videos – includes search, topic filters, and recent videos grid.
 * Categories: emindy
 */
?>

<!--
This pattern creates a hub page for videos (e.g. at /videos/) distinct from the full archive (/video-library/).  
It uses semantic HTML and provides a search form scoped to the video post type, topic filters, and a grid of recently added videos.  
Colours rely on CSS variables so that dark mode toggling works consistently.
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained","contentSize":"1200px"}} -->
<main id="main-content" class="wp-block-group">
  <!-- Page heading -->
  <!-- wp:group {"tagName":"header","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
  <header class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1><?php echo esc_html__( 'Videos', 'emindy' ); ?></h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Browse our library of mindfulness and wellbeing videos. Use search or filter by topic to find what you need.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </header>
  <!-- /wp:group -->

  <!-- Search & filters toolbar -->
  <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"0.75rem","bottom":"0.75rem","left":"0.75rem","right":"0.75rem"},"margin":{"bottom":"1rem"}}}} -->
  <div class="wp-block-group is-style-em-card" style="padding:.75rem;margin-bottom:1rem">
    <!-- Search form scoped to video post type -->
    <!-- wp:html -->
    <form role="search" method="get" action="/" class="em-video-search" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:.25rem 0 .75rem">
      <label for="emvideo-hub-s" class="sr-only">Search videos</label>
      <input id="emvideo-hub-s" type="search" name="s" placeholder="<?php echo esc_attr__( 'Search videos…', 'emindy' ); ?>" style="flex:1;min-width:220px;padding:.5rem .75rem;border-radius:.75rem;border:1px solid var(--em-border);background:var(--em-card);color:var(--em-text)">
      <input type="hidden" name="post_type" value="em_video">
      <button type="submit" style="padding:.55rem .9rem;border-radius:.75rem;border:0;background:var(--em-gold);color:var(--em-bg);font-weight:600">
        <?php echo esc_html__( 'Search', 'emindy' ); ?>
      </button>
    </form>
    <!-- /wp:html -->
    <!-- Topic filters -->
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

  <!-- Recent videos grid -->
  <!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"bottom":"0.5rem"}}}} -->
  <h2><?php echo esc_html__( 'Recently Added Videos', 'emindy' ); ?></h2>
  <!-- /wp:heading -->
  <!-- wp:query {"query":{"perPage":9,"postType":"em_video","order":"desc","orderBy":"date"},"displayLayout":{"type":"grid","columns":3},"layout":{"type":"constrained","contentSize":"1200px"}} -->
  <div class="wp-block-query">
    <!-- wp:post-template -->
    <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
    <div class="wp-block-group is-style-em-card" style="overflow:hidden">
      <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
      <!-- wp:group {"style":{"spacing":{"padding":{"top":"0.8rem","bottom":"0.8rem","left":"0.8rem","right":"0.8rem"}}}} -->
      <div class="wp-block-group" style="padding:.8rem">
        <!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"1.1rem","lineHeight":"1.3"}}} /-->
        <!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.5rem"},"typography":{"fontSize":"0.9rem"}}} -->
        <div class="wp-block-group" style="font-size:.9rem">
          <!-- wp:post-date {"format":"M j, Y"} /-->
          <!-- wp:paragraph --><p>·</p><!-- /wp:paragraph -->
          <!-- wp:terms {"term":"topic"} /-->
        </div>
        <!-- /wp:group -->
        <!-- wp:post-excerpt {"moreText":"<?php echo esc_attr__( 'Watch →', 'emindy' ); ?>","excerptLength":18} /-->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
    <!-- /wp:post-template -->
    <!-- Pagination -->
    <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"0.5rem"}}}} -->
    <div class="wp-block-query-pagination" style="margin-top:.5rem">
      <!-- wp:query-pagination-previous /-->
      <!-- wp:query-pagination-numbers /-->
      <!-- wp:query-pagination-next /-->
    </div>
    <!-- /wp:query-pagination -->
    <!-- No results -->
    <!-- wp:query-no-results -->
      <!-- wp:paragraph --><p><?php echo esc_html__( 'No videos found.', 'emindy' ); ?></p><!-- /wp:paragraph -->
    <!-- /wp:query-no-results -->
  </div>
  <!-- /wp:query -->

  <!-- Link to full archive -->
  <!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0.75rem"}}}} -->
  <p style="margin-top:.75rem"><a href="/video-library/" class="em-button" style="background:var(--em-gold);color:var(--em-bg);padding:.55rem .9rem;border-radius:999px;font-weight:600;text-decoration:none"><?php echo esc_html__( 'Browse all videos', 'emindy' ); ?> →</a></p>
  <!-- /wp:paragraph -->

</main>
<!-- /wp:group -->