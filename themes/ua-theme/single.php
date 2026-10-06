<?php
/*
Template Name: Post
*/

the_post();
$metadata = false;
$ua_single_featured_image = get_theme_mod('ua_single_featured_image', 'below_title');
$ua_single_author = get_theme_mod('ua_single_author', false);
$ua_single_mod_date = get_theme_mod('ua_single_mod_date', true);
$ua_single_pub_date = get_theme_mod('ua_single_pub_date', true);
$ua_single_tags = get_theme_mod('ua_single_tags', true);
$ua_single_categories = get_theme_mod('ua_single_categories', true);
$ua_single_meta_location = get_theme_mod('ua_single_meta_location', 'below');

if ($ua_single_author || $ua_single_pub_date || $ua_single_mod_date || $ua_single_tags || $ua_single_categories) {
  $metadata = true;
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
    <div class="ua_minerva" id="ua_app">

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

        <article class="ua_page_content is-layout-constrained">
          <?php
            $title_alignment = get_post_meta(get_the_ID(), 'title_alignment', true);
            $title_width = get_post_meta(get_the_ID(), 'title_width', true);
          
            if ($ua_single_featured_image !== 'hidden') {
              if ($ua_single_featured_image === 'below_title' && has_post_thumbnail() ) {
                echo '<div class="ua_layout--' . $title_width . '">';
                echo do_blocks('<!-- wp:post-title {"level":1, "className": "ua-util_align--' . $title_alignment . ' ua_page_title"} /-->');
                echo '</div>';
                echo do_blocks('<!-- wp:post-featured-image {"align": "wide"} /-->');
              } else {
                echo do_blocks('<!-- wp:post-featured-image {"align": "wide"} /-->');
                echo '<div class="ua_layout--' . $title_width . '">';
                echo do_blocks('<!-- wp:post-title {"level":1, "className": "ua-util_align--' . $title_alignment . ' ua_page_title"} /-->');
                echo '</div>';
              }
            } else { 
            ?>
            <?php echo '<div class="ua_layout--' . $title_width . '">';
              echo do_blocks('<!-- wp:post-title {"level":1, "className": "ua-util_align--' . $title_alignment . ' ua_page_title"} /-->');
              echo '</div>';
            }
          ?>
          <?php echo do_blocks('<!-- wp:post-content {"layout":{"type":"constrained"}} /-->') ?>
          <?php if (($metadata && $ua_single_meta_location === 'below') || is_active_sidebar( 'single_after' )) : ?>
            <hr/>
            <footer class="ua_post-metadata is-layout-constrained ua_layout--flow-half">
              <?php if ($metadata && $ua_single_meta_location === 'below') : ?>
                <?php if ($ua_single_author) : ?>
                  <p class="ua_post-metadata_author">
                    <strong>Author:</strong>
                    <span><?php the_author(); ?></span>
                  </p>
                <?php endif; ?>
                <?php if ($ua_single_pub_date) : ?>
                  <p class="ua_post-metadata_date">
                    <strong>Published:</strong>
                    <span><?php the_date(); ?></span>
                  </p>
                <?php endif; ?>
                <?php if ($ua_single_mod_date && (get_the_modified_date() !== get_the_date())) : ?>
                  <p class="ua_post-metadata_modified">
                    <strong>Modified:</strong>
                    <span><?php the_modified_date(); ?></span>
                  </p>
                <?php endif; ?>
                <?php if (($ua_single_tags && get_the_tag_list()) || $ua_single_categories) : ?>
                  <div class="is-layout-flex">
                    <?php if ($ua_single_tags && get_the_tag_list()) : ?>
                      <div class="ua_post-metadata_tags">
                        <p style="text-transform: uppercase;"><strong>Tags<br></strong></p>
                        <ul class="ua_tag-list"><li>
                        <?php echo get_the_tag_list('', '</li><li>', ''); ?>
                        </li></ul>
                      </div>
                    <?php endif; ?>
                    <?php if ($ua_single_categories) : ?>
                      <div class="ua_post-metadata_categories">
                        <p style="text-transform: uppercase;"><strong>Categories<br></strong></p>
                        <ul class="ua_tag-list"><li>
                        <?php echo get_the_category_list('</li><li>'); ?>
                        </li></ul>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ( is_active_sidebar( 'single_after' ) ) {
                echo '<div class="is-layout-constrained">';
                dynamic_sidebar( 'single_after' );
                echo '</div>';
              } ?>
            </footer>
          <?php endif; ?>
        </article>

        <?php if (($metadata && $ua_single_meta_location === 'sidebar') || (is_active_sidebar( 'single_sidebar' )) ) : ?>
          <div class="ua_page_sidebar is-layout-flow ua_layout--standard">
            <?php if ($metadata && $ua_single_meta_location === 'sidebar') : ?>
              <footer class="ua_post-metadata ua_layout--flow-half">
                <?php if ($ua_single_author) : ?>
                  <p class="ua_post-metadata_author">
                    <strong>Author:</strong>
                    <span><?php the_author(); ?></span>
                  </p>
                <?php endif; ?>
                <?php if ($ua_single_pub_date) : ?>
                  <p class="ua_post-metadata_date">
                    <strong>Published:</strong>
                    <span><?php the_date(); ?></span>
                  </p>
                <?php endif; ?>
                <?php if ($ua_single_mod_date && (get_the_modified_date() !== get_the_date())) : ?>
                  <p class="ua_post-metadata_modified">
                    <strong>Modified:</strong>
                    <span><?php the_modified_date(); ?></span>
                  </p>
                <?php endif; ?>
                <div class="is-layout-flex">
                  <?php if ($ua_single_tags && get_the_tag_list()) : ?>
                    <div class="ua_post-metadata_tags">
                      <p style="text-transform: uppercase;"><strong>Tags<br></strong></p>
                      <ul class="ua_tag-list"><li>
                      <?php echo get_the_tag_list('', '</li><li>', ''); ?>
                      </li></ul>
                    </div>
                  <?php endif; ?>
                  <?php if ($ua_single_categories) : ?>
                    <div class="ua_post-metadata_categories">
                      <p style="text-transform: uppercase;"><strong>Categories<br></strong></p>
                      <ul class="ua_tag-list"><li>
                      <?php echo get_the_category_list('</li><li>'); ?>
                      </li></ul>
                    </div>
                  <?php endif; ?>
                </div>
              </footer>
            <?php endif; ?>
            <?php if ( is_active_sidebar( 'single_sidebar' ) ) {
              dynamic_sidebar( 'single_sidebar' );
            } ?>
          </div>
        <?php endif; ?>
      </main>

      <footer>
        <?php get_footer(); ?>
        <?php get_template_part( 'parts/site-footer' ); ?>
        <?php get_template_part( 'parts/brand-footer' ); ?>
      </footer>

    </div>
    <?php wp_footer(); ?>
  </body>
</html>
