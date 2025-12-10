<?php
/**
 * Title: Archive – Unified Library
 * Slug: emindy/archive-cpt
 * Description: Shared archive layout for video, exercise and article libraries with consistent search, topic filter and grid.
 * Categories: emindy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- wp:group {"metadata":{"name":"Archive Header, Filters and Grid"},"style":{"spacing":{"blockGap":"1.25rem"}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group">
  <!-- Header / Breadcrumbs -->
  <!-- wp:group {"style":{"spacing":{"margin":{"bottom":"0"}}},"layout":{"type":"constrained","contentSize":"900px"}} -->
  <div class="wp-block-group">
    <!-- wp:shortcode -->[rank_math_breadcrumb]<!-- /wp:shortcode -->
    <!-- wp:archive-title {"showPrefix":false,"style":{"typography":{"fontSize":"clamp(1.9rem,3vw,2.6rem)","lineHeight":"1.15"},"spacing":{"margin":{"top":"0.5rem","bottom":"0.25rem"}}}} /-->
    <!-- wp:term-description {"style":{"typography":{"fontSize":"1.05rem"}}} /-->
  </div>
  <!-- /wp:group -->

  <!-- Toolbar: search + topic filter -->
  <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained","contentSize":"1200px"},"style":{"spacing":{"margin":{"bottom":"0"},"padding":{"top":"0.75rem","bottom":"0.75rem","left":"0.75rem","right":"0.75rem"}}}} -->
  <div class="wp-block-group is-style-em-card" style="padding:.75rem">
    <!-- wp:shortcode -->[em_archive_toolbar]<!-- /wp:shortcode -->
  </div>
  <!-- /wp:group -->

  <!-- Grid -->
  <!-- wp:query {"queryId":501,"query":{"perPage":9,"pages":0,"offset":0,"postType":"any","order":"desc","orderBy":"date","inherit":true},"displayLayout":{"type":"grid","columns":3},"layout":{"type":"constrained","contentSize":"1200px"}} -->
  <div class="wp-block-query">
    <!-- wp:post-template -->
    <!-- wp:group {"style":{"border":{"radius":"var(--wp--custom--brand--radius--xl)"},"shadow":"var(--wp--custom--brand--shadow--card)"},"layout":{"type":"constrained"}} -->
    <div class="wp-block-group" style="border-radius:var(--wp--custom--brand--radius--xl);box-shadow:var(--wp--custom--brand--shadow--card);overflow:hidden">
      <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
      <div class="wp-block-group" style="padding:.8rem">
        <!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"1.1rem","lineHeight":"1.3"}}} /-->
        <!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.5rem"},"typography":{"fontSize":"0.9rem"}}} -->
        <div class="wp-block-group" style="font-size:.9rem">
          <!-- wp:post-date {"format":"M j, Y"} /-->
          <!-- wp:paragraph --><p aria-hidden="true">·</p><!-- /wp:paragraph -->
          <!-- wp:terms {"term":"topic"} /-->
        </div>
        <!-- /wp:group -->
        <!-- wp:post-excerpt {"moreText":"View →","excerptLength":16} /-->
      </div>
    </div>
    <!-- /wp:group -->
    <!-- /wp:post-template -->

    <!-- Pagination -->
    <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"0.5rem"}}}} -->
    <div class="wp-block-query-pagination" style="margin-top:.5rem" aria-label="Archive navigation">
      <!-- wp:query-pagination-previous /-->
      <!-- wp:query-pagination-numbers /-->
      <!-- wp:query-pagination-next /-->
    </div>
    <!-- /wp:query-pagination -->

    <!-- No results -->
    <!-- wp:query-no-results -->
      <!-- wp:paragraph --><p><?php echo esc_html__( 'No posts found.', 'emindy' ); ?></p><!-- /wp:paragraph -->
    <!-- /wp:query-no-results -->
  </div>
  <!-- /wp:query -->
</div>
<!-- /wp:group -->
