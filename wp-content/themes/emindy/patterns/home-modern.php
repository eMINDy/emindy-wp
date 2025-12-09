<?php
/**
 * Title: eMINDy — Home Modern
 * Slug: emindy/home-modern
 * Description: A clean, accessible home page layout for eMINDy with clear sections, semantic HTML, and prominent calls to action.
 * Categories: emindy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$emindy_links = array(
    'videos'      => esc_url( home_url( '/videos/' ) ),
    'exercises'   => esc_url( home_url( '/exercises/' ) ),
    'assessments' => esc_url( home_url( '/assessments/' ) ),
    'stress'      => esc_url( home_url( '/topics/stress/' ) ),
    'anxiety'     => esc_url( home_url( '/topics/anxiety/' ) ),
    'confidence'  => esc_url( home_url( '/topics/confidence/' ) ),
    'reset'       => esc_url( home_url( '/topics/quick-reset/' ) ),
    'phq9'        => esc_url( home_url( '/phq-9/' ) ),
    'gad7'        => esc_url( home_url( '/gad-7/' ) ),
);
?>

<!--
This pattern focuses on accessible semantics and SEO‑friendly structure.
It uses header, main and section elements where appropriate and keeps
heading order logical as recommended by WordPress accessibility guidelines【159373308617029†L191-L249】.
Buttons are used only for actions and links are used for navigation【159373308617029†L256-L263】.
-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main id="main-content" class="wp-block-group">

  <!-- Hero section -->
  <!-- wp:group {"tagName":"section","className":"is-style-em-card","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.5rem","right":"1.5rem"}}}} -->
  <section class="wp-block-group is-style-em-card">
    <!-- wp:heading {"level":1} -->
    <h1><?php echo esc_html__( 'Find Your Calm and Clarity', 'emindy' ); ?></h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'eMINDy offers short, practical tools for mindfulness, resilience and self‑compassion. Choose your path below.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
    <!-- Call to action buttons -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
      <!-- wp:button {"className":"is-style-fill"} -->
      <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="<?php echo $emindy_links['videos']; ?>"><?php echo esc_html__( 'Watch Videos', 'emindy' ); ?></a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo $emindy_links['exercises']; ?>"><?php echo esc_html__( 'Try an Exercise', 'emindy' ); ?></a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo $emindy_links['assessments']; ?>"><?php echo esc_html__( 'Check In', 'emindy' ); ?></a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </section>
  <!-- /wp:group -->

  <!-- Topics grid -->
  <!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"2rem"}}}} -->
  <h2><?php echo esc_html__( 'Explore Popular Topics', 'emindy' ); ?></h2>
  <!-- /wp:heading -->
  <!-- wp:columns {"style":{"spacing":{"blockGap":"1rem"}}} -->
  <div class="wp-block-columns">
    <!-- Topic cards: each column is a topic. Feel free to duplicate and customize. -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group is-style-em-card">
        <!-- wp:heading {"level":3} -->
        <h3><?php echo esc_html__( 'Stress Relief', 'emindy' ); ?></h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p><?php echo esc_html__( 'Discover 8 sessions to unwind and centre yourself.', 'emindy' ); ?></p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph -->
        <p><a href="<?php echo $emindy_links['stress']; ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <div class="wp-block-group is-style-em-card">
        <h3><?php echo esc_html__( 'Anxiety & Worry', 'emindy' ); ?></h3>
        <p><?php echo esc_html__( 'Practical tools to calm anxious thoughts.', 'emindy' ); ?></p>
        <p><a href="<?php echo $emindy_links['anxiety']; ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
      </div>
    </div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <div class="wp-block-group is-style-em-card">
        <h3><?php echo esc_html__( 'Self‑Confidence', 'emindy' ); ?></h3>
        <p><?php echo esc_html__( 'Build kindness towards yourself and grow.', 'emindy' ); ?></p>
        <p><a href="<?php echo $emindy_links['confidence']; ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
      </div>
    </div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <div class="wp-block-group is-style-em-card">
        <h3><?php echo esc_html__( 'Quick Reset', 'emindy' ); ?></h3>
        <p><?php echo esc_html__( 'One‑minute practices for instant calm.', 'emindy' ); ?></p>
        <p><a href="<?php echo $emindy_links['reset']; ?>"><?php echo esc_html__( 'Explore →', 'emindy' ); ?></a></p>
      </div>
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->

  <!-- Latest content section: use query loops for dynamic lists -->
  <!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"2.5rem"}}}} -->
  <h2><?php echo esc_html__( 'Latest Resources', 'emindy' ); ?></h2>
  <!-- /wp:heading -->
  <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
  <div class="wp-block-columns">
    <!-- Videos column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:heading {"level":3} -->
      <h3><?php echo esc_html__( 'Recent Videos', 'emindy' ); ?></h3>
      <!-- /wp:heading -->
      <!-- wp:query {"query":{"perPage":4,"postType":"em_video","order":"desc","orderBy":"date"},"displayLayout":{"type":"list"}} -->
      <div class="wp-block-query">
        <!-- wp:post-template -->
        <!-- Each video preview uses a card -->
        <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
        <div class="wp-block-group is-style-em-card">
          <!-- wp:post-title {"isLink":true} /-->
          <!-- wp:post-excerpt {"moreText":"Watch →"} /-->
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
    <!-- Exercises column -->
    <div class="wp-block-column">
      <h3><?php echo esc_html__( 'Recent Exercises', 'emindy' ); ?></h3>
      <div class="wp-block-query">
        <!-- wp:query {"query":{"perPage":4,"postType":"em_exercise","order":"desc","orderBy":"date"},"displayLayout":{"type":"list"}} -->
        <!-- wp:post-template -->
        <div class="wp-block-group is-style-em-card">
          <!-- wp:post-title {"isLink":true} /-->
          <!-- wp:post-excerpt {"moreText":"Practice →"} /-->
        </div>
        <!-- /wp:post-template -->
        <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"left"}} -->
        <!-- wp:query-pagination-previous /-->
        <!-- wp:query-pagination-numbers /-->
        <!-- wp:query-pagination-next /-->
        <!-- /wp:query-pagination -->
        <!-- /wp:query -->
      </div>
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->

  <!-- Assessment call-to-action section -->
  <!-- wp:group {"className":"is-style-em-card","style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"},"margin":{"top":"2rem"}}},"layout":{"type":"constrained"}} -->
  <div class="wp-block-group is-style-em-card" style="margin-top:2rem">
    <!-- wp:heading {"level":3} -->
    <h3><?php echo esc_html__( 'Self‑Check Assessments', 'emindy' ); ?></h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Take a moment to check in with yourself. These brief assessments are anonymous and non‑diagnostic.', 'emindy' ); ?></p>
    <!-- /wp:paragraph -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
      <!-- wp:button {"className":"is-style-fill"} -->
      <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="<?php echo $emindy_links['phq9']; ?>"><?php echo esc_html__( 'PHQ‑9', 'emindy' ); ?></a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo $emindy_links['gad7']; ?>"><?php echo esc_html__( 'GAD‑7', 'emindy' ); ?></a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->

  <!-- Newsletter signup -->
  <!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"2rem"}}}} -->
  <h3><?php echo esc_html__( 'Stay connected', 'emindy' ); ?></h3>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p><?php echo esc_html__( 'Receive gentle reminders and new resources right in your inbox.', 'emindy' ); ?></p>
  <!-- /wp:paragraph -->
  <!-- wp:shortcode -->
  [em_newsletter]
  <!-- /wp:shortcode -->

  <!-- Credits & resources -->
  <!-- wp:details -->
  <details>
    <summary><?php echo esc_html__( 'Credits & Sources', 'emindy' ); ?></summary>
    <p><?php echo esc_html__( 'Voice: licensed voice actors. Video: Pexels/Pixabay/Freepik. Sound: Freesound.org (CC). Guidance: ChatGPT (OpenAI, GPT‑5).', 'emindy' ); ?></p>
  </details>
  <!-- /wp:details -->

</main>
<!-- /wp:group -->
