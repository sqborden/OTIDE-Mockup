<?php
/*
Template Name: Search
*/

global $wp_query;

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
        <header class="ua_page_header is-layout-constrained">
          <div class="ua_layout--standard">
            <h1 class="ua-util_my--flow-double">
              Search Results
            </h1>
          </div>
          <?php echo do_blocks('<!-- wp:search {"label":"Search","buttonText":"Search"} /-->'); ?>
          <hr class="ua-util_mt--flow-double"/>
        </header>

        <div class="ua_page_content ua_layout--flow">
          <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post();  ?>
              <article class="is-layout-constrained">
                <?php echo do_blocks('<!-- wp:post-title {"level":2,"isLink":true} /-->') ?>
                <?php echo do_blocks('<!-- wp:post-excerpt /-->') ?>
                <hr />
              </article>
            <?php endwhile; ?>

            <div class="ua_component_wrapper is-layout-constrained">
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
            <p>No posts are available</p>
          <?php endif; ?>
        </div>
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
