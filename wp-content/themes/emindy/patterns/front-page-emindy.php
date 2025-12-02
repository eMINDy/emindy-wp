<?php
/**
 * Title: eMINDy — Home (Final, Blocks-Only)
 * Slug: emindy/home-final
 * Categories: emindy
 * Block Types: core/post-content
 * Inserter: true
 */
?>

<!-- wp:group {"className":"is-style-em-card","style":{"spacing":{"padding":{"top":"32px","bottom":"28px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card">
	<!-- wp:heading {"level":1} -->
	<h1>Be your own therapist — gently 🌿</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p>Short, practical tools for calm, clarity, and self-kindness. Learn, practice, and grow — at your own pace.</p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-fill"} -->
		<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/videos/">Start with Videos</a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/exercises/">Try an Exercise</a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/assessments/">Take an Assessment</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"28px"}}}} -->
<h2>Find your path</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":"16px"}}} -->
<div class="wp-block-columns">
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
		<div class="wp-block-group is-style-em-card">
			<!-- wp:heading {"level":3} -->
			<h3>Stress Relief</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>8-session stress program.</p>
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
		<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
		<div class="wp-block-group is-style-em-card">
			<h3>Anxiety</h3>
			<p>Worry &amp; mind clarity tools.</p>
			<p><a href="/topics/anxiety/">Explore →</a></p>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
		<div class="wp-block-group is-style-em-card">
			<h3>Confidence</h3>
			<p>Self-kindness &amp; growth toolkit.</p>
			<p><a href="/topics/confidence/">Explore →</a></p>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
		<div class="wp-block-group is-style-em-card">
			<h3>Quick Reset</h3>
			<p>1-minute calm practices.</p>
			<p><a href="/topics/quick-reset/">Explore →</a></p>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"28px"}}}} -->
<h2>Start here — Essentials</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[lyte id="YOUTUBE_ID"]
<!-- /wp:shortcode -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"24px"},"blockGap":"24px"}}} -->
<div class="wp-block-columns" style="margin-top:24px">
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Latest Videos</h3>
		<!-- /wp:heading -->

		<!-- wp:query {"queryId":601,"query":{"perPage":6,"pages":0,"offset":0,"postType":"em_video","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
		<div class="wp-block-query">
			<!-- wp:post-template -->
			<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
			<div class="wp-block-group is-style-em-card">
				<!-- wp:post-title {"isLink":true} /-->
				<!-- wp:post-excerpt {"moreText":"View →"} /-->
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

	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Latest Exercises</h3>
		<!-- /wp:heading -->

		<!-- wp:query {"queryId":602,"query":{"perPage":6,"pages":0,"offset":0,"postType":"em_exercise","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
		<div class="wp-block-query">
			<!-- wp:post-template -->
			<!-- wp:group {"className":"is-style-em-card","layout":{"type":"constrained"}} -->
			<div class="wp-block-group is-style-em-card">
				<!-- wp:post-title {"isLink":true} /-->
				<!-- wp:post-excerpt {"moreText":"Try →"} /-->
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
</div>
<!-- /wp:columns -->

<!-- wp:group {"className":"is-style-em-card","style":{"spacing":{"padding":{"top":"22px","bottom":"22px","left":"20px","right":"20px"},"margin":{"top":"28px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-em-card" style="margin-top:28px">
	<!-- wp:heading {"level":3} -->
	<h3>Check-in with yourself</h3>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>Simple, non-diagnostic wellness checks.</p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-fill"} -->
		<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/phq-9/">PHQ-9</a></div>
		<!-- /wp:button -->
		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/gad-7/">GAD-7</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"24px"}}}} -->
<h3>Stay in practice</h3>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[em_newsletter]
<!-- /wp:shortcode -->

<!-- wp:details {"style":{"spacing":{"margin":{"top":"16px"}}}} -->
<details><summary>Credits &amp; Resources</summary>
<p>Voice: ElevenLabs (licensed). Video: Pexels/Pixabay/Freepik. Sound: Freesound.org (CC). Guidance: ChatGPT (OpenAI, GPT-5).</p>
</details>
<!-- /wp:details -->

<!-- wp:details {"style":{"spacing":{"margin":{"top":"12px"}}}} -->
<details><summary>Safety &amp; Disclaimer</summary>
<p>This platform is for education and self-help. It’s not medical advice. If you’re in crisis, please visit the <a href="/emergency/">Emergency</a> page.</p>
</details>
<!-- /wp:details -->
