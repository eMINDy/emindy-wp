<?php
/** Generic taxonomy archive template for eMINDy */
get_header();

$term = get_queried_object();
$term_name = single_term_title('', false);
$term_link = get_term_link($term);
$desc = term_description($term); // Optional: use as intro
$posts_per_page = 12; // paginate
$paged = max(1, get_query_var('paged'));
?>

<main id="primary" class="site-main">
  <header class="archive-header">
    <h1 class="archive-title"><?php echo esc_html( $term_name ); ?></h1>
    <?php if ($desc): ?>
      <div class="archive-description"><?php echo wp_kses_post($desc); ?></div>
    <?php endif; ?>
  </header>

  <?php if ( have_posts() ) : ?>
    <div class="cards-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <article <?php post_class('card'); ?>>
          <a href="<?php the_permalink(); ?>" class="card-link" aria-label="<?php the_title_attribute(); ?>">
            <?php if ( has_post_thumbnail() ) the_post_thumbnail('large', ['loading'=>'lazy']); ?>
            <h2 class="card-title"><?php the_title(); ?></h2>
            <p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
          </a>
        </article>
      <?php endwhile; ?>
    </div>

    <nav class="pagination">
      <?php
      /*
       * Use the child theme text‑domain for the pagination arrows so they can
       * be translated.  Without specifying a text domain, WordPress will
       * attempt to load the string from the default domain which may not
       * include our translations.  See https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/【870389742309372†L0-L10】
       */
      the_posts_pagination([
        'mid_size'  => 1,
        'prev_text' => __('←', 'emindy'),
        'next_text' => __('→', 'emindy'),
      ]);
      ?>
    </nav>
  <?php else: ?>
    <p><?php
      /*
       * Provide a text domain for the empty archive message so that it can be
       * translated.  Without the domain, this string falls back to the
       * default translation catalogue and may remain untranslated in
       * multilingual installs.
       */
      _e('No content found yet.', 'emindy');
    ?></p>
  <?php endif; ?>
</main>

<?php
// === SCHEMA: Breadcrumb + CollectionPage + ItemList ===
// Only add ItemList if loop has posts (>=1). Build positions by current page.
if ( have_posts() ) :
  // Rewind to build list safely without breaking main loop:
  rewind_posts();
  $position = 0;
  $items = [];

  while ( have_posts() ) : the_post();
    $position++;
    $items[] = [
      '@type'    => 'ListItem',
      'position' => $position,
      'item'     => [
        '@type' => 'WebPage',
        'name'  => wp_strip_all_tags(get_the_title()),
        'url'   => get_permalink(),
      ]
    ];
  endwhile;

  // Restore again for the template (optional):
  rewind_posts();

  $graph = [
    [
      '@context' => 'https://schema.org',
      '@type'    => 'BreadcrumbList',
      'itemListElement' => [
        [ '@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>home_url('/') ],
        [ '@type'=>'ListItem','position'=>2,'name'=> 'Archive', 'item'=> home_url('/') ],
        [ '@type'=>'ListItem','position'=>3,'name'=> $term_name, 'item'=> $term_link ],
      ]
    ],
    [
      '@context' => 'https://schema.org',
      '@type'    => 'CollectionPage',
      'url'      => esc_url( $term_link ),
      'name'     => $term_name . ' — eMINDy',
      'inLanguage'=> 'en',
      'description'=> $desc ? wp_strip_all_tags($desc) : 'Explore content related to ' . $term_name . ' on eMINDy.',
      'hasPart'  => [
        '@type' => 'ItemList',
        'itemListElement' => $items
      ]
    ]
  ];

  echo '<script type="application/ld+json">'. wp_json_encode($graph, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) .'</script>';
endif;

get_footer();
