<?php
/**
 * Title: Video Page Modern
 * Slug: emindy/video-modern
 * Description: A structured, accessible layout for single video pages with clear sections and SEO‑friendly markup.
 * Categories: emindy
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

<!--
This pattern follows best practices by using semantic elements (header, main, section) and logical heading order【159373308617029†L193-L249】.  
Structured data should be added separately via your SEO plugin; avoid duplicate schema as per guidelines【596865987361330†L420-L497】.
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main id="main-content" class="wp-block-group">
  <!-- Video header -->
  <!-- wp:group {"tagName":"header","className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}}}} -->
  <header class="wp-block-group is-style-em-card">
    <!-- wp:heading {"level":1} -->
    <h1><?php echo esc_html__( 'Video Title', 'emindy' ); ?></h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Brief, inviting summary of the video.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </header>
  <!-- /wp:group -->

  <!-- Video embed -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:shortcode -->
    [lyte id="YOUTUBE_ID"]
    <!-- /wp:shortcode -->
  </section>
  <!-- /wp:group -->

  <!-- Key takeaways section -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Key Takeaways', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:list {"ordered":false} -->
    <ul>
      <li><?php echo esc_html__( 'Point 1', 'emindy' ); ?></li>
      <li><?php echo esc_html__( 'Point 2', 'emindy' ); ?></li>
      <li><?php echo esc_html__( 'Point 3', 'emindy' ); ?></li>
    </ul>
    <!-- /wp:list -->
  </section>
  <!-- /wp:group -->

  <!-- Chapters (use shortcode) -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Chapters', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:shortcode -->
    [em_video_chapters]
    <!-- /wp:shortcode -->
  </section>
  <!-- /wp:group -->

  <!-- Transcript -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Transcript', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:details -->
    <details>
      <summary><?php echo esc_html__( 'Read full transcript', 'emindy' ); ?></summary>
      <p>…</p>
    </details>
    <!-- /wp:details -->
  </section>
  <!-- /wp:group -->

  <!-- Resources & citations -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Resources &amp; Citations', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Use a citation plugin such as Zotpress to list your references.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </section>
  <!-- /wp:group -->

  <!-- Related content and newsletter -->
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