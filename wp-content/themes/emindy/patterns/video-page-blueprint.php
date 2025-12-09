<?php
/**
 * Title: Video Page Blueprint v1
 * Slug: emindy/video-page-blueprint
 * Categories: emindy
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div id="main-content" class="wp-block-group">
  <!-- wp:heading {"level":1} --><h1><?php echo esc_html__( 'Video Title', 'emindy' ); ?></h1><!-- /wp:heading -->
  <!-- wp:paragraph --><p><?php echo esc_html__( 'Short calming summary.', 'emindy' ); ?></p><!-- /wp:paragraph -->
  <!-- wp:shortcode -->[lyte id="YOUTUBE_ID"]<!-- /wp:shortcode -->
  <!-- wp:heading {"level":3} --><h3><?php echo esc_html__( 'Key Takeaways', 'emindy' ); ?></h3><!-- /wp:heading -->
  <!-- wp:list --><ul><li>Point 1</li><li>Point 2</li><li>Point 3</li></ul><!-- /wp:list -->
  <!-- wp:shortcode -->[em_video_chapters]<!-- /wp:shortcode -->
  <!-- wp:details --><details><summary><?php echo esc_html__( 'Transcript', 'emindy' ); ?></summary><p>…</p></details><!-- /wp:details -->
  <!-- wp:heading {"level":3} --><h3><?php echo esc_html__( 'Resources & Citations', 'emindy' ); ?></h3><!-- /wp:heading -->
  <!-- wp:paragraph --><p><?php echo esc_html__( 'Cite with Zotpress.', 'emindy' ); ?></p><!-- /wp:paragraph -->
  <!-- wp:shortcode -->[em_related]<!-- /wp:shortcode -->
  <!-- wp:shortcode -->[em_newsletter]<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
