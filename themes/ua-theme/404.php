<?php
/*
Template Name: 404
*/

$ua_404_title_alignment = get_theme_mod('ua_404_alignment', 'left');
$ua_404_content = get_theme_mod('ua_404_content', 'replace');

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
        <div class="ua_page_content is-layout-constrained">
          <h1 style="text-align: <?php echo $ua_404_title_alignment;?>">404</h1>
          
          <?php 
            if ( is_active_sidebar( '404_content' ) && $ua_404_content == 'above' ) {
              dynamic_sidebar( '404_content' ); 
            }
          ?>

          <?php if ( is_active_sidebar( '404_content' ) && $ua_404_content == 'replace' ) :
            dynamic_sidebar( '404_content' ); 
          else : ?>
            <h2>Page Not Found</h2>
            <p>We're sorry, but the page you requested cannot be found. This can happen if the URL is invalid or if the
              content has moved or no longer exists. Below are some navigational resources to help you get back on track.
            </p>
            <div class="ua_layout--grid" style="--grid-column-count: 2">
              <div>
                <?php echo do_blocks('<!-- wp:ua-blocks/link-box {"title":"Directory","url":"https://directory.ua.edu","description":"Find contact information for current faculty, staff and students."} /-->') ?>
              </div>
              <div>
                <?php echo do_blocks('<!-- wp:ua-blocks/link-box {"title":"Social Media Index","url":"https://ua.edu/social","description":"The Social Media Directory is a central listing of University of Alabama social media pages."} /-->') ?>
              </div>
            </div>
            <div>
              <?php echo do_blocks('<!-- wp:ua-blocks/link-box {"title":"Contact Us","url":"https://ua.edu/contact","description":"Find general and department contact information or report website issues."} /-->') ?>
            </div>
          <?php endif;

          if ( is_active_sidebar( '404_content' ) && $ua_404_content == 'below' ) {
            dynamic_sidebar( '404_content' ); 
          } ?>
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
