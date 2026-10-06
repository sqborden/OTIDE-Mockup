<?php
/*
Template Name: Archive
*/

$ua_archive_categories = get_theme_mod('ua_archive_categories', true);
$ua_archive_tags = get_theme_mod('ua_archive_tags', true);
$ua_archive_post_featured_image = get_theme_mod('ua_archive_post_featured_image', false);
$ua_archive_post_pub_date = get_theme_mod('ua_archive_post_pub_date', true);
$ua_archive_post_tags = get_theme_mod('ua_archive_post_tags', true);
$ua_archive_post_categories = get_theme_mod('ua_archive_post_categories', false);

$categories = get_categories( array(
	'orderby' => 'name',
	'parent'  => 0
) );

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
      <header class="ua_page_header is-layout-flow ua_layout--wide">
        <h1 class="ua-util_my--flow-double">
          <?php the_archive_title() ?>
        </h1>
        <?php echo do_blocks('
            <!-- wp:columns -->
            <div class="wp-block-columns">
              <!-- wp:column -->
              <div class="wp-block-column">
                <!-- wp:search {"label":"Search this site","buttonText":"Search"} /-->
              </div>
              <!-- /wp:column -->

              <!-- wp:column -->
              <div class="wp-block-column">
                <!-- wp:archives {"displayAsDropdown":true} /-->
              </div>
              <!-- /wp:column -->
            </div>
            <!-- /wp:columns -->
          '); ?>
          <hr class="ua-util_mt--flow-double"/>
      </header>

      <div class="ua_page_content ua_layout--flow">
        <?php if ( have_posts() ) : ?>
          <?php while ( have_posts() ) : the_post();  ?>
            <article class="is-layout-constrained">
              <?php echo do_blocks('<!-- wp:post-title {"level":2,"isLink":true} /-->') ?>
              <?php if ($ua_archive_post_featured_image && has_post_thumbnail() ) {
                echo do_blocks('<!-- wp:post-featured-image /-->');
              } ?>
              <?php echo do_blocks('<!-- wp:post-excerpt /-->') ?>
              <?php if ($ua_archive_post_pub_date || $ua_archive_post_tags || $ua_archive_post_categories) : ?>
                <footer class="ua_post-metadata is-layout-constrained ua_layout--flow-half">
                  <?php if ($ua_archive_post_pub_date) : ?>
                    <p class="ua_post-metadata_pub-date">
                      <strong>Published:</strong>
                      <span><?php echo get_the_date(); ?></span>
                    </p>
                  <?php endif; ?>
                  <?php if ($ua_archive_post_tags && get_the_tag_list()) : ?>
                    <p class="ua_post-metadata_tags">
                      <strong>Tags:</strong>
                      <?php the_tags( '', ', ', '' ); ?>
                    </p>
                  <?php endif; ?>
                  <?php if ($ua_archive_post_categories && get_the_category_list()) : ?>
                    <p class="ua_post-metadata_categories">
                      <strong>Categories:</strong>
                      <?php the_category( ', ' ); ?>
                    </p>
                  <?php endif; ?>
                </footer>
              <?php endif; ?>
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

      <?php if ( $ua_archive_categories || $ua_archive_tags ) : ?>
        <div class="ua_page_sidebar ua_layout--flow ua_layout--standard">
          <?php if ($ua_archive_tags && get_the_tag_list()) : ?>
            <div class="ua_archive_tag-list ua_layout--flow-half">
              <p style="text-transform: uppercase;">Tags</p>
              <ul class="ua_tag-list">
                <?php
                  $tags = get_tags();
                  foreach ( $tags as $tag ) :
                  $tag_link = get_tag_link( $tag->term_id );
                ?>
                  <li>
                    <a href='<?php echo $tag_link; ?>' title='<?php echo $tag->name; ?>' rel="tag"><?php echo $tag->name ?></a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <?php if ($ua_archive_categories && $categories) : ?>
            <div class="ua_archive_tag-list ua_layout--flow-half">
              <p style="text-transform: uppercase;">Categories</p>
              <ul class="unstyled">
                <?php foreach ($categories as $category) : ?>
                  <li>
                    <a href="<?php echo get_category_link($category->term_id); ?>" rel="category"><?php echo $category->name; ?></a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
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
