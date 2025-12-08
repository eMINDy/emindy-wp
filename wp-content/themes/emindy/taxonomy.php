<?php
/** Generic taxonomy archive template for eMINDy */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$term          = get_queried_object();
$term_name     = single_term_title( '', false );
$term_link     = get_term_link( $term );
$term_link     = is_wp_error( $term_link ) ? '' : $term_link;
$desc          = term_description( $term ); // Optional: use as intro
$has_posts     = have_posts();

if ( $has_posts ) {
    rewind_posts();
}
?>

<main id="primary" class="site-main">
  <header class="archive-header">
    <h1 class="archive-title"><?php echo esc_html( $term_name ); ?></h1>
    <?php if ( $desc ) : ?>
      <div class="archive-description"><?php echo wp_kses_post( $desc ); ?></div>
    <?php endif; ?>
  </header>

  <?php if ( $has_posts ) : ?>
    <div class="cards-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <article <?php post_class( 'card' ); ?>>
          <a href="<?php the_permalink(); ?>" class="card-link" aria-label="<?php the_title_attribute(); ?>">
            <?php if ( has_post_thumbnail() ) : ?>
              <?php the_post_thumbnail( 'large', [ 'loading' => 'lazy' ] ); ?>
            <?php endif; ?>
            <h2 class="card-title"><?php the_title(); ?></h2>
            <p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
          </a>
        </article>
      <?php endwhile; ?>
    </div>

    <nav class="pagination" aria-label="<?php esc_attr_e( 'Posts navigation', 'emindy' ); ?>">
      <?php
      /*
       * Use the child theme text‑domain for the pagination arrows so they can
       * be translated.  Without specifying a text domain, WordPress will
       * attempt to load the string from the default domain which may not
       * include our translations.
       */
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
    <p><?php _e( 'No content found yet.', 'emindy' ); ?></p>
  <?php endif; ?>
</main>

<?php
// === SCHEMA: Breadcrumb + CollectionPage + ItemList ===
// Only add ItemList if loop has posts (>=1). Build positions by current page.
if ( $has_posts ) :
  // Rewind to build list safely without breaking main loop:
  rewind_posts();
  $position = 0;
  $items    = [];

  while ( have_posts() ) : the_post();
    $position++;
    $items[] = [
      '@type'    => 'ListItem',
      'position' => $position,
      'item'     => [
        '@type' => 'WebPage',
        'name'  => wp_strip_all_tags( get_the_title() ),
        'url'   => get_permalink(),
      ],
    ];
  endwhile;

  // Restore again for the template (optional):
  rewind_posts();

  $graph = [
    [
      '@context'        => 'https://schema.org',
      '@type'           => 'BreadcrumbList',
      'itemListElement' => [
        [
          '@type'    => 'ListItem',
          'position' => 1,
          'name'     => wp_strip_all_tags( __( 'Home', 'emindy' ) ),
          'item'     => home_url( '/' ),
        ],
        [
          '@type'    => 'ListItem',
          'position' => 2,
          'name'     => wp_strip_all_tags( __( 'Archive', 'emindy' ) ),
          'item'     => home_url( '/' ),
        ],
        [
          '@type'    => 'ListItem',
          'position' => 3,
          'name'     => wp_strip_all_tags( $term_name ),
          'item'     => $term_link,
        ],
      ],
    ],
    [
      '@context'    => 'https://schema.org',
      '@type'       => 'CollectionPage',
      'url'         => $term_link,
      'name'        => wp_strip_all_tags( $term_name ) . ' — eMINDy',
      'inLanguage'  => 'en',
      'description' => $desc ? wp_strip_all_tags( $desc ) : sprintf( __( 'Explore content related to %s on eMINDy.', 'emindy' ), wp_strip_all_tags( $term_name ) ),
      'hasPart'     => [
        '@type'           => 'ItemList',
        'itemListElement' => $items,
      ],
    ],
  ];

  echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
endif;

get_footer();
