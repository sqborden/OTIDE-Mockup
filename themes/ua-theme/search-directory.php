<?php
/*
Template Name: Directory Search
*/

require_once get_template_directory() . '/inc/directory-system.php';
$directory_settings = get_directory_settings('ua_directory_archive_');
$grid_style         = $directory_settings['grid_style'];
$landscape_css      = $directory_settings['landscape_css'];

// Get taxonomy terms
$person_categories = get_directory_terms('directory_category');
$person_tags       = get_directory_terms('directory_tag');

// Sanitize the search query and pagination
$search_query = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$paged        = max(1, get_query_var('paged', isset($_GET['paged']) ? absint($_GET['paged']) : 1));
$per_page     = $directory_settings['records_per_page'] ?? 20;

// Query using SQL-level filters so each search word is matched against title OR
// any meta field (subtitle, email, phone, location, website). All words must
// match (AND), but each word can appear in any field (OR within the word).
// This avoids WP_Query combining `s` AND `meta_query` with AND logic.
$query = null;
if ($search_query !== '') {
  $words     = array_values( array_filter( preg_split( '/\s+/', $search_query ) ) );
  $meta_keys = ['_person_subtitle', '_person_email', '_person_phone', '_person_location', '_person_website'];

  if ( ! empty( $words ) ) {
    $join_filter = function( $join ) use ( &$join_filter, $meta_keys ) {
      global $wpdb;
      remove_filter( 'posts_join', $join_filter );
      $keys  = implode( ',', array_map( fn( $k ) => $wpdb->prepare( '%s', $k ), $meta_keys ) );
      $join .= " LEFT JOIN {$wpdb->postmeta} AS dir_pm ON ({$wpdb->posts}.ID = dir_pm.post_id AND dir_pm.meta_key IN ($keys))";
      return $join;
    };

    $where_filter = function( $where ) use ( &$where_filter, $words ) {
      global $wpdb;
      remove_filter( 'posts_where', $where_filter );
      foreach ( $words as $word ) {
        $like   = '%' . $wpdb->esc_like( $word ) . '%';
        $where .= $wpdb->prepare(
          " AND ( {$wpdb->posts}.post_title LIKE %s OR dir_pm.meta_value LIKE %s )",
          $like,
          $like
        );
      }
      return $where;
    };

    $distinct_filter = function( $d ) use ( &$distinct_filter ) {
      remove_filter( 'posts_distinct', $distinct_filter );
      return 'DISTINCT';
    };

    add_filter( 'posts_join',     $join_filter );
    add_filter( 'posts_where',    $where_filter );
    add_filter( 'posts_distinct', $distinct_filter );

    $query = new WP_Query([
      'post_type'        => 'directory',
      'post_status'      => 'publish',
      'posts_per_page'   => $per_page,
      'paged'            => $paged,
      'orderby'          => 'title',
      'order'            => 'ASC',
      'no_found_rows'    => false,
      'suppress_filters' => false,
    ]);
  }
}

$result_count = $query ? $query->found_posts : 0;
$total_pages  = $query ? $query->max_num_pages : 0;

// Build position-aware widget HTML
$result_html = '';
if ( $search_query !== '' ) {
    $result_html = '<p class="ua-util_mt--flow-half alignwide">' . $result_count . ' result' . ( $result_count !== 1 ? 's' : '' ) . ' for <strong>' . esc_html( $search_query ) . '</strong></p>';
}
$widgets = build_directory_widget_html(
    $directory_settings,
    $person_tags,
    $person_categories,
    [ 'value' => $search_query ],
    $result_html
);
?>

<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php wp_head(); ?>
  </head>

  <body <?php body_class(); ?> >
    <?php wp_body_open(); ?>
    <div class="ua_minerva" id="ua_app">

      <header>
        <?php get_template_part( 'parts/brand-bar' ); ?>
        <?php get_template_part( 'parts/title-bar' ); ?>
        <?php get_header(); ?>
      </header>

      <main id="wp--skip-link--target" class="ua_page">
        <header class="ua_page_header is-layout-flow ua_layout--wide">
          <h1 class="ua-util_my--flow-double">Directory Search Results</h1>
          <hr class="ua-util_mt--flow-double"/>

          <?php $top_widgets = render_directory_widgets('top', $widgets);
          if ( $top_widgets ): ?>
            <div class="ua_component_wrapper ua_layout--flow ua_layout--standard alignwide">
              <?php echo $top_widgets; ?>
            </div>
          <?php endif; ?>
        </header>

        <div class="ua_page_content is-layout-constrained">
          <div class="ua_component_wrapper alignwide">
            <?php if ($search_query === '') : ?>
              <p>Enter a search term above to find directory entries.</p>
            <?php elseif (! $query->have_posts()) : ?>
              <p>No directory entries found matching <strong><?php echo esc_html($search_query); ?></strong>.</p>
            <?php else : ?>
              <div class="ua_layout--grid" style="<?php echo esc_attr($grid_style); ?>">
                <?php while ($query->have_posts()) : $query->the_post(); $post = get_post();
                  $person_data = [
                    'subtitle'  => get_post_meta($post->ID, '_person_subtitle', true),
                    'phone'     => get_post_meta($post->ID, '_person_phone', true),
                    'email'     => get_post_meta($post->ID, '_person_email', true),
                    'location'  => get_post_meta($post->ID, '_person_location', true),
                    'website'   => get_post_meta($post->ID, '_person_website', true),
                    'view_link' => get_permalink($post->ID),
                  ];
                ?>
                <div class="ua_component_wrapper ua_contact-card ua_presence--subtle">
                  <article class="ua_card <?php echo esc_attr($landscape_css); ?>">

                    <?php if ($directory_settings['profile_image'] && has_post_thumbnail($post->ID)) : ?>
                      <div class="ua_card_image-wrapper">
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'large')); ?>" alt="<?php echo esc_attr($post->post_title); ?>">
                      </div>
                    <?php endif; ?>

                    <div class="ua_card_content-wrapper">
                      <h3 class="ua_card_title">
                        <?php if ($directory_settings['profile_button']) : ?>
                          <a href="<?php echo esc_url($person_data['view_link']); ?>"><?php echo esc_html($post->post_title); ?></a>
                        <?php else : ?>
                          <?php echo esc_html($post->post_title); ?>
                        <?php endif; ?>
                      </h3>

                      <?php if ($person_data['subtitle']) : ?>
                        <span class="ua_card_subtitle"><?php echo esc_html($person_data['subtitle']); ?></span>
                      <?php endif; ?>

                      <ul class="ua_contact-card_info" style="margin-block-start:var(--ua_space--flow-half, calc(var(--ua_space--flow, 2rem) / 2))">
                        <?php foreach (['email', 'phone', 'location', 'website'] as $field) : ?>
                          <?php if ($directory_settings[$field] && !empty($person_data[$field])) : ?>
                            <li><?php echo person_contact_field($field, $person_data[$field]); ?></li>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </article>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
              </div>

              <?php if ($total_pages > 1) : ?>
              <div class="ua_component_wrapper"  style="padding-block:var(--ua_space--flow);">
                <nav aria-label="Pagination" class="ua_pagination">
                  <div>
                    <?php
                    echo paginate_links([
                      'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                      'format'    => '?paged=%#%',
                      'total'     => $total_pages,
                      'current'   => $paged,
                      'add_args'  => [
                        's'         => rawurlencode( $search_query ),
                        'post_type' => 'directory',
                      ],
                      'prev_text' => '<span class="fa fa-arrow-left" aria-hidden="true"></span> Previous',
                      'next_text' => 'Next <span class="fa fa-arrow-right" aria-hidden="true"></span>',
                    ]);
                    ?>
                  </div>
                </nav>
              </div>
              <?php endif; ?>
            <?php endif; ?>

          </div>
            <?php $bottom_widgets = render_directory_widgets('bottom', $widgets);
        if ( $bottom_widgets ): ?>
          <div class="ua_component_wrapper ua_layout--flow ua_layout--standard alignwide" style="padding-block-start:var(--ua_space--flow-double);">
            <?php echo $bottom_widgets; ?>
          </div>
        <?php endif; ?>
        </div>

        <?php $sidebar_widgets = render_directory_widgets('sidebar', $widgets);
        if ( $sidebar_widgets ): ?>
          <div class="ua_page_sidebar ua_layout--flow-half ua_layout--standard">
            <?php echo $sidebar_widgets; ?>
          </div>
        <?php endif; ?>
      </main>

      <footer>
        <?php get_footer() ?>
        <?php get_template_part( 'parts/site-footer' ); ?>
        <?php get_template_part( 'parts/brand-footer' ); ?>
      </footer>

    </div>
    <?php wp_footer(); ?>
  </body>
</html>
