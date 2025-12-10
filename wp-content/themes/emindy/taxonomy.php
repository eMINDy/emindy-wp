<?php
/**
 * Generic taxonomy archive template for eMINDy.
 *
 * @package emindy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term          = get_queried_object();
$term_name_raw = single_term_title( '', false );
$term_name     = wp_strip_all_tags( $term_name_raw );

if ( '' === $term_name && $term instanceof WP_Term ) {
	$term_name = wp_strip_all_tags( $term->name );
}

$term_link = '';
if ( $term instanceof WP_Term ) {
	$link = get_term_link( $term );
	if ( ! is_wp_error( $link ) ) {
		$term_link = esc_url( $link );
	}
}

$desc      = term_description(); // Uses queried term by default.
$has_posts = have_posts();

if ( $has_posts ) {
	rewind_posts();
}
?>

<main id="primary" class="site-main">
	<header class="archive-header">
		<h1 class="archive-title"><?php echo esc_html( $term_name ); ?></h1>
		<?php if ( ! empty( $desc ) ) : ?>
			<div class="archive-description"><?php echo wp_kses_post( $desc ); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( $has_posts ) : ?>
		<div class="cards-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$card_title   = get_the_title();
				$card_excerpt = wp_trim_words( get_the_excerpt(), 20 );
				?>
				<article <?php post_class( 'card' ); ?>>
					<a href="<?php the_permalink(); ?>" class="card-link">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'large', [ 'loading' => 'lazy' ] ); ?>
						<?php endif; ?>
						<h2 class="card-title"><?php echo esc_html( $card_title ); ?></h2>
						<?php if ( ! empty( $card_excerpt ) ) : ?>
							<p class="card-excerpt"><?php echo esc_html( $card_excerpt ); ?></p>
						<?php endif; ?>
					</a>
				</article>
			<?php endwhile; ?>
		</div>

		<nav class="pagination" aria-label="<?php echo esc_attr__( 'Posts navigation', 'emindy' ); ?>">
			<?php
			the_posts_pagination(
				[
					'mid_size'  => 1,
					'prev_text' => __( '←', 'emindy' ),
					'next_text' => __( '→', 'emindy' ),
				]
			);
			?>
		</nav>
	<?php else : ?>
		<p class="no-results"><?php esc_html_e( 'No content found yet.', 'emindy' ); ?></p>
	<?php endif; ?>
</main>

<?php
// === SCHEMA: Breadcrumb + CollectionPage + ItemList ===.
if ( $has_posts && $term instanceof WP_Term && $term_link ) {

	// Rewind to build list safely without breaking the main loop.
	rewind_posts();

	$paged = max( 1, (int) get_query_var( 'paged', 1 ) );
	$ppp   = (int) get_query_var( 'posts_per_page', get_option( 'posts_per_page' ) );

	if ( $ppp <= 0 ) {
		$ppp = (int) get_option( 'posts_per_page', 10 );
	}

	$position = ( $paged - 1 ) * $ppp;
	$items    = [];

	while ( have_posts() ) :
		the_post();
		$position++;
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $position,
			'item'     => [
				'@type' => 'WebPage',
				'name'  => wp_strip_all_tags( get_the_title() ),
				'url'   => esc_url_raw( get_permalink() ),
			],
		];
	endwhile;

	// Breadcrumb taxonomy label.
	$taxonomy_obj   = get_taxonomy( $term->taxonomy );
	$taxonomy_label = $taxonomy_obj && isset( $taxonomy_obj->labels->name )
		? wp_strip_all_tags( $taxonomy_obj->labels->name )
		: wp_strip_all_tags( __( 'Archive', 'emindy' ) );

	$home_url = esc_url_raw( home_url( '/' ) );

	// Determine current language / locale.
	$in_language = get_bloginfo( 'language' );
	if ( function_exists( 'pll_current_language' ) ) {
		$pll_locale = pll_current_language( 'locale' );
		if ( $pll_locale ) {
			$in_language = $pll_locale;
		}
	}

	// Description text for CollectionPage.
	if ( ! empty( $desc ) ) {
		$description_text = wp_strip_all_tags( wp_kses_post( $desc ) );
	} else {
		/* translators: %s: Taxonomy term name. */
		$description_text = sprintf( __( 'Explore content related to %s on eMINDy.', 'emindy' ), $term_name );
	}

	$graph = [
		[
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => [
				[
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => wp_strip_all_tags( __( 'Home', 'emindy' ) ),
					'item'     => $home_url,
				],
				[
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $taxonomy_label,
					'item'     => $term_link,
				],
				[
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => $term_name,
					'item'     => $term_link,
				],
			],
		],
		[
			'@context'    => 'https://schema.org',
			'@type'       => 'CollectionPage',
			'url'         => $term_link,
			'name'        => sprintf( '%s — eMINDy', $term_name ),
			'inLanguage'  => $in_language,
			'description' => $description_text,
			'hasPart'     => [
				'@type'           => 'ItemList',
				'itemListElement' => $items,
			],
		],
	];

	$graph_output = wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( $graph_output ) {
		// Safe JSON-LD output.
		echo '<script type="application/ld+json">' . $graph_output . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

get_footer();
