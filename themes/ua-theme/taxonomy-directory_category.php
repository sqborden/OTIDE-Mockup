<?php
/*
Template Name: Directory Category
*/
$directory_page_title = get_the_archive_title();

// Customizer settings
$directory_settings = get_directory_settings('ua_directory_archive_');

// Grid styles
$grid_style = $directory_settings['grid_style'];
$landscape_css = $directory_settings['landscape_css'];

// Get taxonomy terms
$person_categories = get_directory_terms('directory_category');
$person_tags = get_directory_terms('directory_tag');

// Pagination
$paged = max(1, get_query_var('paged'));
$posts_per_page = $directory_settings['records_per_page'];
$current_category = get_queried_object();
$args = array(
    'post_type'      => 'directory',
    'posts_per_page' => -1,
    'paged'          => $paged,
    'tax_query'      => array(
        array(
            'taxonomy' => 'directory_category',
            'field'    => 'term_id',
            'terms'    => $current_category->term_id,
        ),
    ),
);
$wp_query = new WP_Query($args);

// Sort posts by last name
$sorted_posts = sort_by_last_name($wp_query->posts);

// Pagination
$total_posts = count($sorted_posts);
//$posts_per_page = get_option('posts_per_page');
$paged = max(1, get_query_var('paged'));
$offset = ($paged - 1) * $posts_per_page;
$paginated_posts = array_slice($sorted_posts, $offset, $posts_per_page);
$wp_query->posts = $paginated_posts;
$wp_query->post_count = count($paginated_posts);
$wp_query->found_posts = $total_posts;
$wp_query->max_num_pages = ceil($total_posts / $posts_per_page);

// Build position-aware widget HTML
$widgets = build_directory_widget_html( $directory_settings, $person_tags, $person_categories );
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
        <h1 class="ua-util_my--flow-double">   <?php echo $directory_page_title; ?></h1>
        <hr class="ua-util_mt--flow-double"/>
        <?php $top_widgets = render_directory_widgets('top', $widgets);
        if ( $top_widgets ): ?>
          <div class="ua_component_wrapper ua_layout--flow-half ua_layout--standard alignwide">
            <?php echo $top_widgets; ?>
          </div>
        <?php endif; ?>
      </header>

      <div class="ua_page_content is-layout-constrained ">
        <div class="ua_component_wrapper alignwide">
          <div class="ua_layout--grid" style="<?php echo $grid_style?>">
          <?php if ( $wp_query->have_posts() ) : ?>
            <?php while ( $wp_query->have_posts() ) :  $wp_query->the_post();
                $person_data = array(
                  'subtitle' => get_post_meta($post->ID, '_person_subtitle', true),
                  'phone' => get_post_meta($post->ID, '_person_phone', true),
                  'email' => get_post_meta($post->ID, '_person_email', true),
                  'location' => get_post_meta($post->ID, '_person_location', true),
                  'website' => get_post_meta($post->ID, '_person_website', true),
                  'view_link' => get_permalink($post->ID)
                );
            ?>
          <div class="ua_component_wrapper ua_contact-card ua_presence--subtle">
            <article class="ua_card <?php echo $landscape_css;?>">
            <?php if ($directory_settings['profile_image'] && has_post_thumbnail($post->ID)): ?>
                <div class="ua_card_image-wrapper">
                    <img src="<?php echo get_the_post_thumbnail_url($post->ID, 'large'); ?>" alt="<?php echo esc_attr($post->post_title); ?>">
                </div>
            <?php endif; ?>

              <div class="ua_card_content-wrapper">
                 <h3 class="ua_card_title">
                    <?php if ($directory_settings['profile_button']): ?>
                        <a href="<?php echo $person_data['view_link']; ?>"><?php echo $post->post_title; ?></a>
                    <?php else: ?>
                        <?php echo $post->post_title; ?>
                    <?php endif; ?>
                </h3>

                <?php if ($person_data['subtitle']): ?>
                    <span class="ua_card_subtitle"><?php echo $person_data['subtitle']; ?></span>
                <?php endif; ?>

                <ul class="ua_contact-card_info" style="margin-block-start:var(--ua_space--flow-half, calc(var(--ua_space--flow, 2rem) / 2))">
                    <?php foreach (['email', 'phone', 'location', 'website'] as $field): ?>
                        <?php if ($directory_settings[$field] && !empty($person_data[$field])): ?>
                            <li><?php echo person_contact_field($field, $person_data[$field]); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <?php endwhile;
           wp_reset_postdata();?>
          </div>
        </div>
          <div class="ua_component_wrapper alignwide">
            <nav aria-label="Pagination" class="ua_pagination">
              <div>
                <?php echo paginate_links( array(
                  'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                  'total'        => $wp_query->max_num_pages,
                  'current'      => max( 1, get_query_var( 'paged' ) ),
                  'format'       => '?paged=%#%',
                  'show_all'     => false,
                  'type'         => 'plain',
                  'end_size'     => 2,
                  'mid_size'     => 1,
                  'prev_next'    => true,
                  'prev_text'    => sprintf( '<span class="fa fa-arrow-left" aria-hidden="true"></span>  %1$s', __( 'Previous', 'ua-theme' ) ),
                  'next_text'    => sprintf( '%1$s  <span class="fa fa-arrow-right" aria-hidden="true"></span>', __( 'Next', 'ua-theme' ) ),
                  'add_args'     => false,
                  'add_fragment' => '',
                ) ); ?>
              </div>
            </nav>
          </div>
        <?php else : ?>
          <p>No directory entries are available</p>
        <?php endif; ?>

        <?php $bottom_widgets = render_directory_widgets('bottom', $widgets);
        if ( $bottom_widgets ): ?>
          <div class="ua_component_wrapper ua_layout--flow-half ua_layout--standard alignwide" style="padding-block-start:var(--ua_space--flow-double);">
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
