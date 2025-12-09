<?php
/**
 * Title: Exercise Page Modern
 * Slug: emindy/exercise-modern
 * Description: A clean and accessible layout for exercise pages with clear instructions and player shortcode.
 * Categories: emindy
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!--
This pattern uses semantic HTML (header, main, section) to organise the content and
ensures a logical heading order【159373308617029†L193-L249】.  
Buttons and links are used appropriately【159373308617029†L256-L263】.
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main id="main-content" class="wp-block-group">
  <!-- Exercise header -->
  <!-- wp:group {"tagName":"header","className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}}}} -->
  <header class="wp-block-group is-style-em-card">
    <!-- wp:heading {"level":1} -->
    <h1><?php echo esc_html__( 'Exercise Title', 'emindy' ); ?></h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'A gentle introduction that sets the tone for the exercise.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
  </header>
  <!-- /wp:group -->

  <!-- Player embed -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:shortcode -->
    [em_player]
    <!-- /wp:shortcode -->
  </section>
  <!-- /wp:group -->

  <!-- Instructions -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'Instructions', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Provide step‑by‑step instructions for this exercise here. Keep the language friendly and concise.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
    <!-- wp:list {"ordered":true} -->
    <ol>
      <li><?php echo esc_html__( 'Step one of the exercise.', 'emindy' ); ?></li>
      <li><?php echo esc_html__( 'Step two of the exercise.', 'emindy' ); ?></li>
      <li><?php echo esc_html__( 'Step three of the exercise.', 'emindy' ); ?></li>
    </ol>
    <!-- /wp:list -->
  </section>
  <!-- /wp:group -->

  <!-- Additional resources -->
  <!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
  <section class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2><?php echo esc_html__( 'More to Explore', 'emindy' ); ?></h2>
    <!-- /wp:heading -->
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