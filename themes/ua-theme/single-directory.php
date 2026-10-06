<?php
/*
Template Name: Person
*/

the_post();

$subtitle = get_post_meta( $post->ID, '_person_subtitle', true );
$phone = get_post_meta( $post->ID, '_person_phone', true );
$email = get_post_meta( $post->ID, '_person_email', true );
$location = get_post_meta( $post->ID, '_person_location', true );
$website = get_post_meta( $post->ID, '_person_website', true );
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

      <main id="wp--skip-link--target" class="ua_page single-directory">
        <header class="ua_page_header">
          <div class="ua_layout--standard">
            <div class="ua_component_wrapper ua_presence--subtle" >
              <article class="ua_card ua_card--landscape ua_contact-card ">
                <div class="ua_card_content-wrapper">
                  <h1 class="ua_card_title"><?php the_title(); ?></h1>
                  <span class="ua_card_subtitle"> <?php echo $subtitle;?></span>
                  <?php if ( has_term( '', 'directory_tag', $post->ID ) ) : ?>
                  <div class="ua_component_wrapper">
                    <ul class="ua_tag-list">
                      <?php echo get_the_term_list( $post->ID, 'directory_tag', '<li>', '</li><li>' ); ?>
                    </ul>
                  </div>
                  <?php endif; ?>
                  <ul class="ua_contact-card_info">
                    <?php if($email != '') {?>
                    <li><a href="mailto:<?php echo $email;?>" rel="email">
                      <span class="fa fa-envelope" aria-hidden="true"></span>
                      <?php echo $email;?></a>
                    </li>
                    <?php }?>
                    <?php if($phone != '') {?>
                    <li><a href="tel:<?php echo esc_attr( $phone ); ?>" rel="phone"><span class="fa fa-phone" aria-hidden="true"></span> <?php echo esc_html( $phone ); ?></a>
                    </li>
                    <?php }?>
                    <?php if($location != '') {?>
                    <li><span class="fa fa-location-dot" aria-hidden="true"></span> <?php echo esc_html( $location ); ?></li>
                    <?php }?>
                    <?php if($website != '') {?>
                    <li><a href="<?php echo esc_url( $website ); ?>" rel="website"><span class="fa fa-globe" aria-hidden="true"></span> <?php echo esc_html( $website ); ?></a></li>
                    <?php }?>
                  </ul>
                </div>
              
                <?php
                  if (has_post_thumbnail() ) { ?>
                    <div class="ua_card_image-wrapper">
                      <img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                    </div>
                <?php } ?>
              </article>
            </div>
           </div>
           <hr/>
        </header>
      
        <article class="ua_page_content is-layout-constrained">
          <?php echo do_blocks('
            <!-- wp:post-content {"className": "is-layout-flow", "layout":{"type":"constrained"}} /-->
          '); ?>
        </article>
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
