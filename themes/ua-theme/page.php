<?php
/*
Template Name: Page
*/

$ua_experimental_margins = get_theme_mod('ua_experimental_margins', false);

$class = 'ua_minerva';

if ($ua_experimental_margins) {
  $class .= ' ua_experimental-margins';
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php wp_head(); ?>
  </head>

  <body <?php body_class(); ?> >
    <?php wp_body_open(); ?>
    <div class="<?php echo $class ?>" id="ua_app">

      <header>
        <?php get_template_part( 'parts/brand-bar' ); ?>
        <?php get_template_part( 'parts/title-bar' ); ?>
        <?php get_header(); ?>
      </header>

      <main id="wp--skip-link--target" class="ua_page">
        <?php echo do_blocks('
          <!-- wp:ua-theme/markup {"type":"hero"} /-->
          <!-- wp:ua-theme/markup {"type":"sidebar"} /-->
        '); ?>
        <div class="ua_page_content is-layout-flow">
          <?php
            $title_alignment = get_post_meta(get_the_ID(), 'title_alignment', true);
            $title_width = get_post_meta(get_the_ID(), 'title_width', true);
            echo '<div class="ua_layout--' . $title_width . '">';
            echo do_blocks('
            <!-- wp:post-title {"level": "1", "className": "ua-util_align--' . $title_alignment . ' ua_page_title"} /-->');
            echo '</div>';
            echo do_blocks('
            <!-- wp:post-content {"className": "is-layout-flow", "layout":{"type":"constrained"}} /-->
          '); ?>
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
