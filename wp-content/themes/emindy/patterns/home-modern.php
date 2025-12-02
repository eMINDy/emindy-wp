<?php
/**
 * Title: eMINDy — Home Modern
 * Slug: emindy/home-modern
 * Description: A clean, accessible home page layout for eMINDy with clear sections, semantic HTML, and prominent calls to action.
 * Categories: emindy
 */
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
    <h1>Find Your Calm and Clarity</h1>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>eMINDy offers short, practical tools for mindfulness, resilience and self‑compassion. Choose your path below.</p>
    <!-- /wp:paragraph -->
    <!-- Call to action buttons -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
      <!-- wp:button {"className":"is-style-fill"} -->
      <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/videos/">Watch Videos</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/exercises/">Try an Exercise</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/assessments/">Check In</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </section>
  <!-- /wp:group -->

  <!-- Topics grid -->
  <!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"2rem"}}}} -->
  <h2>Explore Popular Topics</h2>
  <!-- /wp:heading -->
  <!-- wp:columns {"style":{"spacing":{"blockGap":"1rem"}}} -->
  <div class="wp-block-columns">
    <!-- Topic cards: each column is a topic. Feel free to duplicate and customize. -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group is-style-em-card">
        <!-- wp:heading {"level":3} -->
        <h3>Stress Relief</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Discover 8 sessions to unwind and centre yourself.</p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph -->
        <p><a href="/topics/stress/">Explore →</a></p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <div class="wp-block-group is-style-em-card">
        <h3>Anxiety &amp; Worry</h3>
        <p>Practical tools to calm anxious thoughts.</p>
        <p><a href="/topics/anxiety/">Explore →</a></p>
      </div>
    </div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <div class="wp-block-group is-style-em-card">
        <h3>Self‑Confidence</h3>
        <p>Build kindness towards yourself and grow.</p>
        <p><a href="/topics/confidence/">Explore →</a></p>
      </div>
    </div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <div class="wp-block-group is-style-em-card">
        <h3>Quick Reset</h3>
        <p>One‑minute practices for instant calm.</p>
        <p><a href="/topics/quick-reset/">Explore →</a></p>
      </div>
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->

  <!-- Latest content section: use query loops for dynamic lists -->
  <!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"2.5rem"}}}} -->
  <h2>Latest Resources</h2>
  <!-- /wp:heading -->
  <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
  <div class="wp-block-columns">
    <!-- Videos column -->
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:heading {"level":3} -->
      <h3>Recent Videos</h3>
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
      <h3>Recent Exercises</h3>
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
    <h3>Self‑Check Assessments</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>Take a moment to check in with yourself. These brief assessments are anonymous and non‑diagnostic.</p>
    <!-- /wp:paragraph -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
      <!-- wp:button {"className":"is-style-fill"} -->
      <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/phq-9/">PHQ‑9</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/gad-7/">GAD‑7</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->

  <!-- Newsletter signup -->
  <!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"2rem"}}}} -->
  <h3>Stay connected</h3>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>Receive gentle reminders and new resources right in your inbox.</p>
  <!-- /wp:paragraph -->
  <!-- wp:shortcode -->
  [em_newsletter]
  <!-- /wp:shortcode -->

  <!-- Credits & resources -->
  <!-- wp:details -->
  <details>
    <summary>Credits &amp; Sources</summary>
    <p>Voice: licensed voice actors. Video: Pexels/Pixabay/Freepik. Sound: Freesound.org (CC). Guidance: ChatGPT (OpenAI, GPT‑5).</p>
  </details>
  <!-- /wp:details -->

</main>
<!-- /wp:group -->