<?php
/**
 * Title: Exercise Page Blueprint v1
 * Slug: emindy/exercise-page-blueprint
 * Categories: emindy
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div id="main-content" class="wp-block-group">
  <!-- wp:heading {"level":1} --><h1><?php echo esc_html__( 'Exercise Title', 'emindy' ); ?></h1><!-- /wp:heading -->
  <!-- wp:paragraph --><p><?php echo esc_html__( 'Gentle introduction.', 'emindy' ); ?></p><!-- /wp:paragraph -->
  <!-- wp:shortcode -->[em_player]<!-- /wp:shortcode -->
  <!-- wp:heading {"level":3} --><h3><?php echo esc_html__( 'Instructions', 'emindy' ); ?></h3><!-- /wp:heading -->
  <!-- wp:paragraph --><p>…</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
