<?php
/**
 * Title: Article Page Modern
 * Slug: emindy/article-modern
 * Description: A starter layout for articles with clear headings, content area, citations, and related links.
 * Categories: emindy
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!--
This pattern provides an accessible article layout using semantic elements and proper heading hierarchy【159373308617029†L193-L249】.  
The content is inserted via the Post Content block, letting WordPress manage your article body.  
It includes sections for citations and related content to encourage good SEO practices like adding structured data【596865987361330†L420-L497】.
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main id="main-content" class="wp-block-group">
  <!-- Article header with title and excerpt -->
  <!-- wp:group {"tagName":"header","className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}}}} -->
  <header class="wp-block-group is-style-em-card">
    <!-- wp:post-title {"level":1} /-->
    <!-- wp:post-excerpt /-->
  </header>
  <!-- /wp:group -->

  <!-- Article content -->
  <!-- wp:group {"tagName":"article","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <article class="wp-block-group">
    <!-- wp:post-content {"layout":{"type":"constrained"}} /-->
  </article>
  <!-- /wp:group -->

  <!-- Citations section -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Citations &amp; Further Reading', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'List your sources here. Consider using a citation plugin such as Zotpress for automated lists.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </section>
  <!-- /wp:group -->

  <!-- Related posts and newsletter -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:shortcode -->
    [em_related]
    <!-- /wp:shortcode -->
    <!-- wp:shortcode -->
    [em_newsletter]
    <!-- /wp:shortcode -->
  </section>
  <!-- /wp:group -->

</main>
<!-- /wp:group -->