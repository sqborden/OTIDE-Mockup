<?php
// https://developer.wordpress.org/reference/classes/WP_Customize_Control/__construct/
// https://developer.wordpress.org/reference/classes/wp_customize_manager/add_control/
// https://developer.wordpress.org/reference/classes/wp_customize_setting/__construct/
// https://developer.wordpress.org/themes/customize-api/customizer-objects/
// https://developer.wordpress.org/reference/functions/get_option/
// https://developer.wordpress.org/reference/functions/update_option/
// https://developer.wordpress.org/reference/functions/update_blog_public/
// https://www.tech-prastish.com/blog/create-custom-controls-for-theme-customizer-wordpress/

require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-custom-controls.php';

add_action( 'customize_register', '__return_true' );

function ua_customizer_styles() {
  wp_enqueue_style('customizer-styles', get_template_directory_uri() . '/assets/css/customizer.css', array(), null);
}
add_action( 'customize_register', 'ua_customizer_styles' );

/* =================================================================
/* SECTION Customizer Init
================================================================= */

function ua_sanitize_checkbox( $checked ) {
  return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

function ua_sanitize_number_range ( $number ) {
  $number = absint( $number );
  if($number < 1) {
    $number = 1;
  } elseif($number > 100) {
    $number = 100;
  }
  return $number;
}
/*
Remove Notification: Meet the New Theme Builder…
*/
// function dw_admin_theme_style() {
//   if (current_user_can( 'manage_options' )) {
//     echo '<!-- Remove FSE Notification -->';
//     echo '<style>#customize-notifications-area { display: none; }</style>';
//   }
// }

// add_filter('admin_head', 'dw_admin_theme_style');

/* ==============================
/* SECTION Remove Customizer Options
============================== */

function remove_customizer_sections( $wp_customize ){
  $wp_customize->remove_control('site_icon');
  $wp_customize->remove_panel( 'themes' );
  $wp_customize->remove_panel( 'notifications_area' );
}

add_action( 'customize_register', 'remove_customizer_sections', 30 );

/* !SECTION Remove Customizer Options */
/* !SECTION Customizer Init */
/* =================================================================
/* SECTION Site Identity
================================================================= */

function ua_add_site_id_settings( $wp_customize ) {

  // Change the labels for Site Title and Tagline
  $wp_customize->get_control('blogname')->label = __( 'Site Name', 'ua-theme' );
  $wp_customize->get_control('blogdescription')->label = __( 'Description', 'ua-theme' );

  $wp_customize->add_setting( 'ua_site_subtitle',
    array(
      'default'           => '',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_setting( 'ua_site_subtitle_url',
    array(
      'default'           => '',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new WP_Customize_Control(
    $wp_customize,
    'ua_site_subtitle',
    array(
        'label'      => __( 'Subtitle', 'ua-theme' ),
        'priority'   => 10,
        'section'    => 'title_tagline',
        'settings'   => 'ua_site_subtitle',
        'type'       => 'text',
    )
  ));

  $wp_customize->add_control( new WP_Customize_Control(
    $wp_customize,
    'ua_site_subtitle_url',
    array(
        'label'      => __( 'Subtitle Link', 'ua-theme' ),
        'priority'   => 10,
        'section'    => 'title_tagline',
        'settings'   => 'ua_site_subtitle_url',
        'type'       => 'text',
    )
  ));
}

add_action( 'customize_register', 'ua_add_site_id_settings' );

/* !SECTION Site Identity  */
/* =================================================================
/* SECTION Site Settings Panel
================================================================= */

function ua_add_site_settings_panel( $wp_customize ) {

  $wp_customize->add_section( 'ua_site_settings_section',
    array(
      'title'         => __( 'Site Settings', 'ua-theme' ),
      'priority'      => 1,
    )
  );

  $wp_customize->add_setting( 'posts_per_page', array(
    'default'           => get_option('posts_per_page'),
    'type'              => 'option'
  ) );

  $wp_customize->add_control( 'posts_per_page',
    array(
      'label'      => __( 'Posts Per Page', 'ua-theme' ),
      'type'       => 'number',
      'section'    => 'ua_site_settings_section'
    )
  );

  $wp_customize->add_setting( 'analytics_type',
    array(
      'type'              => 'option',
      'default'           => 'google_analytics',
    )
  );

  $wp_customize->add_control( 'analytics_type',
    array(
      'label'      => __( 'Analytics Type', 'ua-theme' ),
      'section'    => 'ua_site_settings_section',
      'type'       => 'select',
      'choices'    => array(
        'google_analytics' => 'Google Analytics',
        'google_tag_manager' => 'Google Tag Manager',
      ),
    )
  );

  $wp_customize->add_setting( 'google_property_id',
    array(
      'type'              => 'option',
      'default'           => '',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( 'google_property_id',
    array(
      'label'      => __( 'Google Property ID', 'ua-theme' ),
      'section'    => 'ua_site_settings_section',
      'type'       => 'text',
    )
  );

  $wp_customize->add_setting( 'ua_seo_notice_control',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_seo_notice_control',
   array(
      'label' => __( 'Search Engine Optimization' ),
      'section' => 'ua_site_settings_section'
   )
  ));

  $wp_customize->add_setting( 'ua_seo_disabled',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_seo_disabled',
    array(
      'label'      => __( 'Disable default meta tags', 'ua-theme' ),
      'description' => 'Only check if using a plugin to control meta information.',
      'section'    => 'ua_site_settings_section',
      'type'       => 'checkbox',
    )
  );
}

add_action( 'customize_register', 'ua_add_site_settings_panel' );

/* !SECTION Site Settings Panel */
/* =================================================================
/* SECTION Template Panel
================================================================= */

function ua_template_settings_panel( $wp_customize ) {
  $wp_customize->add_panel( 'ua_templates_panel',
    array(
      'title'            => __( 'Page Templates', 'ua-theme' ),
      'description'      => __( 'Theme modifications to page templates', 'ua-theme' ),
    )
  );
}

add_action( 'customize_register', 'ua_template_settings_panel' );

/* ==============================
/* SECTION Single Section
============================== */

function ua_single_settings_section( $wp_customize ) {
  $wp_customize->add_section( 'ua_single_settings',
    array(
      'title'         => __( 'Single Post', 'ua-theme' ),
      'priority'      => 2,
      'panel'         => 'ua_templates_panel'
    )
  );

  $wp_customize->add_setting( 'ua_single_meta_location',
    array(
      'default'           => 'below',
    )
  );

  $wp_customize->add_control( 'ua_single_meta_location',
    array(
      'label'      => __( 'Metadata location', 'ua-theme' ),
      'type'       => 'select',
      'section'    => 'ua_single_settings',
      'choices'    => array(
        'below'    => 'Below Content',
        'sidebar'  => 'Sidebar',
      ),
    )
  );

  $wp_customize->add_setting( 'ua_single_featured_image',
    array(
      'default'           => 'below_title',
    )
  );

  $wp_customize->add_control( 'ua_single_featured_image',
    array(
      'label'      => __( 'Show Featured Image', 'ua-theme' ),
      'type'       => 'select',
      'section'    => 'ua_single_settings',
      'choices'    => array(
        'above_title' => 'Above Title',
        'below_title' => 'Below Title',
        'hidden'      => 'Hidden',
      ),
    )
  );

  $wp_customize->add_setting( 'ua_single_author',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_single_author',
    array(
      'label'      => __( 'Post Author', 'ua-theme' ),
      'description' => __('Show the author of the post in the post area', 'ua-theme'),
      'section'    => 'ua_single_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_single_pub_date',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_single_pub_date',
    array(
      'label'      => __( 'Published Date', 'ua-theme' ),
      'description' => __('Show the published date of the post in the meta area', 'ua-theme'),
      'section'    => 'ua_single_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_single_mod_date',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_single_mod_date',
    array(
      'label'      => __( 'Modified Date', 'ua-theme' ),
      'description' => __('Show the modified date of the post in the meta area', 'ua-theme'),
      'section'    => 'ua_single_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_single_tags',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_single_tags',
    array(
      'label'      => __( 'Tags', 'ua-theme' ),
      'description' => __('Show the post tags in the meta area', 'ua-theme'),
      'section'    => 'ua_single_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_single_categories',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_single_categories',
    array(
      'label'      => __( 'Categories', 'ua-theme' ),
      'description' => __('Show the post categories in the meta area', 'ua-theme'),
      'section'    => 'ua_single_settings',
      'type'       => 'checkbox',
    )
  );
}

add_action( 'customize_register', 'ua_single_settings_section' );

/* !SECTION Single Section */
/* ==============================
/* SECTION Index Section
============================== */

function ua_index_settings_section( $wp_customize ) {
  $wp_customize->add_section( 'ua_index_settings',
    array(
      'title'         => __( 'Posts Index', 'ua-theme' ),
      'priority'      => 1,
      'panel'         => 'ua_templates_panel'
    )
  );

  $wp_customize->add_setting( 'ua_index_content',
    array(
      'default'           => 'above_feed',
    )
  );

  $wp_customize->add_control( 'ua_index_content',
    array(
      'label'      => __( 'Show page content', 'ua-theme' ),
      'description' => 'This is the content of the page that the posts index is set to. To edit this content, edit this page in the dashboard under "Pages".',
      'type'       => 'select',
      'section'    => 'ua_index_settings',
      'choices'    => array(
        'above_feed' => 'Above Feed',
        'below_feed' => 'Below Feed',
        'hidden'      => 'Hidden',
      ),
    )
  );

  $wp_customize->add_setting( 'ua_index_tags',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_index_tags',
    array(
      'label'      => __( 'Tags', 'ua-theme' ),
      'description' => __('Show all tags in the sidebar', 'ua-theme'),
      'section'    => 'ua_index_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_index_categories',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_index_categories',
    array(
      'label'      => __( 'Categories', 'ua-theme' ),
      'description' => __('Show all categories in the sidebar', 'ua-theme'),
      'section'    => 'ua_index_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_posts_metadata_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_posts_metadata_notice',
   array(
      'label' => __( 'Post Metadata' ),
      'section' => 'ua_index_settings'
   )
  ));

  $wp_customize->add_setting( 'ua_index_post_featured_image',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_index_post_featured_image',
    array(
      'label'      => __( 'Post Featured Image', 'ua-theme' ),
      'description'      => __( 'Shows the featured image for each post in the feed', 'ua-theme' ),
      'type'       => 'checkbox',
      'section'    => 'ua_index_settings',
    )
  );

  $wp_customize->add_setting( 'ua_index_post_pub_date',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_index_post_pub_date',
    array(
      'label'      => __( 'Post Published Date', 'ua-theme' ),
      'description' => __('Show the published date for each post in the feed', 'ua-theme'),
      'section'    => 'ua_index_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_index_post_tags',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_index_post_tags',
    array(
      'label'      => __( 'Post Tags', 'ua-theme' ),
      'description' => __('Show the tags for each post in the feed', 'ua-theme'),
      'section'    => 'ua_index_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_index_post_categories',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_index_post_categories',
    array(
      'label'      => __( 'Post Categories', 'ua-theme' ),
      'description' => __('Show the categories for each post in the feed', 'ua-theme'),
      'section'    => 'ua_index_settings',
      'type'       => 'checkbox',
    )
  );
}

add_action( 'customize_register', 'ua_index_settings_section' );

/* !SECTION Index Section */
/* ==============================
/* SECTION Archive Section
============================== */

function ua_archive_settings_section( $wp_customize ) {
  $wp_customize->add_section( 'ua_archive_settings',
    array(
      'title'         => __( 'Posts archive', 'ua-theme' ),
      'priority'      => 1,
      'panel'         => 'ua_templates_panel'
    )
  );

  $wp_customize->add_setting( 'ua_archive_tags',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_archive_tags',
    array(
      'label'      => __( 'Tags', 'ua-theme' ),
      'description' => __('Show all tags in the sidebar', 'ua-theme'),
      'section'    => 'ua_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_archive_categories',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_archive_categories',
    array(
      'label'      => __( 'Categories', 'ua-theme' ),
      'description' => __('Show all categories in the sidebar', 'ua-theme'),
      'section'    => 'ua_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_archive_posts_metadata_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_archive_posts_metadata_notice',
   array(
      'label' => __( 'Post Metadata' ),
      'section' => 'ua_archive_settings'
   )
  ));

  $wp_customize->add_setting( 'ua_archive_post_featured_image',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_archive_post_featured_image',
    array(
      'label'      => __( 'Post Featured Image', 'ua-theme' ),
      'description'      => __( 'Shows the featured image for each post in the feed', 'ua-theme' ),
      'type'       => 'checkbox',
      'section'    => 'ua_archive_settings',
    )
  );

  $wp_customize->add_setting( 'ua_archive_post_pub_date',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_archive_post_pub_date',
    array(
      'label'      => __( 'Post Published Date', 'ua-theme' ),
      'description' => __('Show the published date for each post in the feed', 'ua-theme'),
      'section'    => 'ua_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_archive_post_tags',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_archive_post_tags',
    array(
      'label'      => __( 'Post Tags', 'ua-theme' ),
      'description' => __('Show the tags for each post in the feed', 'ua-theme'),
      'section'    => 'ua_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_archive_post_categories',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_archive_post_categories',
    array(
      'label'      => __( 'Post Categories', 'ua-theme' ),
      'description' => __('Show the categories for each post in the feed', 'ua-theme'),
      'section'    => 'ua_archive_settings',
      'type'       => 'checkbox',
    )
  );
}

add_action( 'customize_register', 'ua_archive_settings_section' );

/* !SECTION Archive Section */
/* ==============================
/* SECTION 404 Section
============================== */

function ua_404_settings_section( $wp_customize ) {
  $wp_customize->add_section( 'ua_404_settings',
    array(
      'title'         => __( '404 Page', 'ua-theme' ),
      'priority'      => 2,
      'panel'         => 'ua_templates_panel'
    )
  );

  $wp_customize->add_setting( 'ua_404_alignment',
    array(
      'default'           => 'left',
    )
  );

  $wp_customize->add_control( 'ua_404_alignment',
    array(
      'label'      => __( 'Title Alignment', 'ua-theme' ),
      'type'       => 'select',
      'section'    => 'ua_404_settings',
      'choices'    => array(
        'left'    => 'Left',
        'center'  => 'Center',
        'right'   => 'Right',
      ),
    )
  );

  $wp_customize->add_setting( 'ua_404_content',
    array(
      'default' => 'replace',
    )
  );

  $wp_customize->add_control( 'ua_404_content',
    array(
      'label'      => __( '404 Content', 'ua-theme' ),
      'type'       => 'select',
      'section'    => 'ua_404_settings',
      'choices'    => array(
        'replace'    => 'Replace',
        'above'  => 'Above',
        'below'   => 'Below',
      ),
    )
  );
}

add_action( 'customize_register', 'ua_404_settings_section' );

/* !SECTION 404 Section */
/* =================================================================
/* !SECTION Template Panel */
/* =================================================================
/* SECTION Experimental Panel
================================================================= */


function ua_add_experimental_settings_panel( $wp_customize ) {

  $wp_customize->add_section( 'ua_experimental_settings_section',
    array(
      'title'         => __( 'Experimental Settings', 'ua-theme' ),
      'priority'      => 999,
    )
  );

  $wp_customize->add_setting( 'ua_experimental_margins',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_experimental_margins',
    array(
      'label'      => __( 'Dynamic page content margins', 'ua-theme' ),
      'description' => 'Toggles the page content top and/or bottom margins based on several conditions. The page must not have a sidebar, and the first or last child must be full width with a background.',
      'section'    => 'ua_experimental_settings_section',
      'type'       => 'checkbox',
    )
  );
}

add_action( 'customize_register', 'ua_add_experimental_settings_panel' );


/* !SECTION Experimental Panel */
/* =================================================================
/* SECTION Adjust order of panels
================================================================= */

function adjust_panel_order( $wp_customize ) {
  $wp_customize->get_section('title_tagline')->priority = 1;
  $wp_customize->get_section('static_front_page')->priority = 3;
  $wp_customize->get_section('ua_site_settings_section')->priority = 2;
  $wp_customize->get_panel('ua_templates_panel')->priority = 4;
}

add_action( 'customize_register', 'adjust_panel_order' );

/* !SECTION Adjust order of panels */


/* =================================================================
/* SECTION Directory Index Panel
================================================================= */

function ua_directory_settings_section( $wp_customize ) {
  $wp_customize->add_section( 'ua_directory_settings',
    array(
      'title'            => __( 'Directory Index', 'ua-theme' ),
      'panel'         => 'ua_templates_panel',
      'priority'      => 5,
    )
  );

  $wp_customize->add_setting( 'ua_directory_tags',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_tags',
    array(
      'label'      => __( 'Tags', 'ua-theme' ),
      'description' => 'Show all tags on the page',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_tags_position',
    array(
      'default'           => 'top',
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_tags_position',
    array(
      'label'           => __( 'Tag position', 'ua-theme' ),
      'section'         => 'ua_directory_settings',
      'type'            => 'select',
      'choices'         => array(
        'top'     => 'Top',
        'sidebar' => 'Sidebar',
        'bottom'  => 'Bottom',
      ),
      'active_callback' => function() use ( $wp_customize ) {
         return (bool) $wp_customize->get_setting( 'ua_directory_tags' )->value();
      },
    )
  );

  $wp_customize->add_setting( 'ua_directory_categories',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_categories',
    array(
      'label'      => __( 'Categories', 'ua-theme' ),
      'description' => 'Show all categories on the page',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_categories_position',
    array(
      'default'           => 'bottom',
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_categories_position',
    array(
      'label'           => __( 'Category position', 'ua-theme' ),
      'section'         => 'ua_directory_settings',
      'type'            => 'select',
      'choices'         => array(
        'top'     => 'Top',
        'sidebar' => 'Sidebar',
        'bottom'  => 'Bottom',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return (bool) $wp_customize->get_setting( 'ua_directory_categories' )->value();
      },
    )
  );

 $wp_customize->add_setting( 'ua_directory_search',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_search',
    array(
      'label'      => __( 'Search', 'ua-theme' ),
      'description' => 'Show search on the page',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_search_position',
    array(
      'default'           => 'top',
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_search_position',
    array(
      'label'           => __( 'Search position', 'ua-theme' ),
      'section'         => 'ua_directory_settings',
      'type'            => 'select',
      'choices'         => array(
        'top'     => 'Top',
        'sidebar' => 'Sidebar',
        'bottom'  => 'Bottom',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return (bool) $wp_customize->get_setting( 'ua_directory_search' )->value();
      },
    )
  );

  $wp_customize->add_setting( 'ua_directory_records_per_page',
    array(
      'default' => 10,
      'sanitize_callback' => 'ua_sanitize_number_range',
    )
  );

  $wp_customize->add_control( 'ua_directory_records_per_page',
    array(
      'label'      => __( 'Records Per Page', 'ua-theme' ),
      'description' => 'Number of records to show per page in the directory page, minimum 1 and maximum 100.',
      'type'     => 'number',
      'section'    => 'ua_directory_settings',
      'settings' => 'ua_directory_records_per_page',
      'input_attrs' => array(
                'min' => 1,
                'max' => 100,
                'step' => 1,
              ),
    )
  );

  $wp_customize->add_setting( 'ua_directory_view_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_directory_view_notice',
    array(
      'label' => __( 'Page View' ),
      'section' => 'ua_directory_settings'
    )
  ));

  $wp_customize->add_setting( 'ua_directory_page_view',
    array(
      'default'           => 'grid',
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_page_view',
    array(
      'section'    => 'ua_directory_settings',
      'type'       => 'radio',
      'choices' => array(
                    'grid' => 'Grid',
                    'list' => 'List'
                  )
    )
  );

  $wp_customize->add_setting( 'ua_directory_max_columns',
    array(
      'default'           => '2',
    )
  );

  $wp_customize->add_control( 'ua_directory_max_columns',
    array(
      'label'      => __( 'Max Columns', 'ua-theme' ),
      'description' => 'Shows max columns to the grid view',
      'type'       => 'select',
      'section'    => 'ua_directory_settings',
      'choices'    => array(
        '2' => '2',
        '3' => '3',
        '4' => '4',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return 'grid' === $wp_customize->get_setting( 'ua_directory_page_view' )->value();
    },
    )
  );

  $wp_customize->add_setting( 'ua_directory_pinned_category_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_directory_pinned_category_notice',
    array(
      'label' => __( 'Directory Pinned Category', 'ua-theme' ),
      'section' => 'ua_directory_settings'
    )
  ));

  $wp_customize->add_setting( 'ua_directory_is_pin_category',
    array(
      'default'           => false,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_is_pin_category',
    array(
      'label'      => __( 'Pin a Category', 'ua-theme' ),
      'description' => 'Shows pinned category top of the directory list',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $args = array(
    'taxonomy'   => 'directory_category', 
    'hide_empty' => false,
  );

  $categories = get_terms( $args );

  // Add a default "Select Category" option
  $default_category = (object) array(
      'term_id' => 0, // Or any other identifier for your default
      'name'    => 'Select Category',
  );
  array_unshift( $categories, $default_category );
  
  $wp_customize->add_setting( 'ua_directory_pinned_category',
    array(
      'default'           => 0,
    )
  );

  $wp_customize->add_control( 'ua_directory_pinned_category',
    array(
      'label'      => __( '', 'ua-theme' ),
      'description' => '',
      'type'       => 'select',
      'section'    => 'ua_directory_settings',
      'choices'  => wp_list_pluck( $categories, 'name', 'term_id' ),
      'active_callback' => function() use ( $wp_customize ) {
         return get_theme_mod( 'ua_directory_is_pin_category', false );
    },
    )
  );

  $wp_customize->add_setting( 'ua_directory_sorting_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_directory_sorting_notice',
    array(
      'label' => __( 'Directory Sorting By' ),
      'section' => 'ua_directory_settings'
    )
  ));

  $wp_customize->add_setting( 'ua_directory_sorting',
    array(
      'default'           => 'order_index',
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_sorting',
    array(
      'section'    => 'ua_directory_settings',
      'type'       => 'radio',
      'choices' => array(
                    'order_index' => 'Order Index',
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                  )
    )
  );

  $wp_customize->add_setting( 'ua_directory_contact_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_directory_contact_notice',
    array(
      'label' => __( 'Person Metadata' ),
      'section' => 'ua_directory_settings'
    )
  ));

  $wp_customize->add_setting( 'ua_directory_profile_image',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_profile_image',
    array(
      'label'      => __( 'Profile Image', 'ua-theme' ),
      'description'      => __( 'Shows the profile image for each person in the directory feed', 'ua-theme' ),
      'type'       => 'checkbox',
      'section'    => 'ua_directory_settings',
    )
  );

  $wp_customize->add_setting( 'ua_directory_location',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_location',
    array(
      'label'      => __( 'Location', 'ua-theme' ),
      'description' => 'Shows the location for each person in the directory feed',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_website',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_website',
    array(
      'label'      => __( 'Website', 'ua-theme' ),
      'description' => 'Shows the website for each person in the directory feed',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_phone',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_phone',
    array(
      'label'      => __( 'Phone', 'ua-theme' ),
      'description' => 'Shows the phone for each person in the directory feed',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_email',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_email',
    array(
      'label'      => __( 'Email', 'ua-theme' ),
      'description' => 'Shows the email for each person in the directory feed',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_profile_button',
    array(
      'default'           => true,
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_profile_button',
    array(
      'label'      => __( 'Link to Profile', 'ua-theme' ),
      'description' => 'Links the name of each person in the directory to their profile page',
      'section'    => 'ua_directory_settings',
      'type'       => 'checkbox',
    )
  );
}

add_action( 'customize_register', 'ua_directory_settings_section' );

/* !SECTION Directory Index Panel */

/* =================================================================
/* SECTION Directory Archive Panel
================================================================= */

function ua_directory_archive_settings_section( $wp_customize ) {
  $wp_customize->add_section( 'ua_directory_archive_settings',
    array(
      'title'            => __( 'Directory Archive', 'ua-theme' ),
      'panel'         => 'ua_templates_panel',
      'priority'      => 6,
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_tags',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_tags' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_tags',
    array(
      'label'      => __( 'Tags', 'ua-theme' ),
      'description' => 'Show all tags on the archive pages',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox'
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_tags_position',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_tags_position' )->value(),
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_tags_position',
    array(
      'label'           => __( 'Tag position', 'ua-theme' ),
      'section'         => 'ua_directory_archive_settings',
      'type'            => 'select',
      'choices'         => array(
        'top'     => 'Top',
        'sidebar' => 'Sidebar',
        'bottom'  => 'Bottom',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return (bool) $wp_customize->get_setting( 'ua_directory_archive_tags' )->value();
      },
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_categories',
  array(
    'default'           => $wp_customize->get_setting( 'ua_directory_categories' )->value(),
    'sanitize_callback' => 'ua_sanitize_checkbox',
  )
  );

  $wp_customize->add_control( 'ua_directory_archive_categories',
  array(
    'label'      => __( 'Categories', 'ua-theme' ),
    'description' => 'Show all categories on the archive pages',
    'section'    => 'ua_directory_archive_settings',
    'type'       => 'checkbox',
  )
  );

  $wp_customize->add_setting( 'ua_directory_archive_categories_position',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_categories_position' )->value(),
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_categories_position',
    array(
      'label'           => __( 'Category position', 'ua-theme' ),
      'section'         => 'ua_directory_archive_settings',
      'type'            => 'select',
      'choices'         => array(
        'top'     => 'Top',
        'sidebar' => 'Sidebar',
        'bottom'  => 'Bottom',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return (bool) $wp_customize->get_setting( 'ua_directory_archive_categories' )->value();
      },
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_search',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_search' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_search',
    array(
      'label'      => __( 'Search', 'ua-theme' ),
      'description' => 'Show search on the archive pages',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_search_position',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_search_position' )->value(),
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_search_position',
    array(
      'label'           => __( 'Search position', 'ua-theme' ),
      'section'         => 'ua_directory_archive_settings',
      'type'            => 'select',
      'choices'         => array(
        'top'     => 'Top',
        'sidebar' => 'Sidebar',
        'bottom'  => 'Bottom',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return (bool) $wp_customize->get_setting( 'ua_directory_archive_search' )->value();
      },
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_records_per_page',
    array(
      'default' => 10,
      'sanitize_callback' => 'ua_sanitize_number_range',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_records_per_page',
    array(
      'label'      => __( 'Records Per Page', 'ua-theme' ),
      'description' => 'Number of records to show per page in the directory archive, minimum 1 and maximum 100.',
      'type'     => 'number',
      'section'    => 'ua_directory_archive_settings',
      'settings' => 'ua_directory_archive_records_per_page',
      'input_attrs' => array(
                'min' => 1,
                'max' => 100,
                'step' => 1,
              ),
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_view_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $page_view_archive_default = $wp_customize->get_setting( 'ua_directory_page_view' )->value().'_archive';
  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_directory_archive_view_notice',
    array(
      'label' => __( 'Page View' ),
      'section' => 'ua_directory_archive_settings'
    )
  ));

  $wp_customize->add_setting( 'ua_directory_archive_page_view',
    array(
      'default'           => $page_view_archive_default,
      'sanitize_callback' => 'sanitize_key',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_page_view',
    array(
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'radio',
      'choices' => array(
                    'grid_archive' => 'Grid',
                    'list_archive' => 'List'
                  )
    )
  );

  $max_columns_archive_default = '0'.$wp_customize->get_setting( 'ua_directory_max_columns' )->value();
  $wp_customize->add_setting( 'ua_directory_archive_max_columns',
    array(
      'default'           => $max_columns_archive_default,
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_max_columns',
    array(
      'label'      => __( 'Max Columns', 'ua-theme' ),
      'description' => 'Shows max columns to the grid view',
      'type'       => 'select',
      'section'    => 'ua_directory_archive_settings',
      'choices'    => array(
        '02' => '2',
        '03' => '3',
        '04' => '4',
      ),
      'active_callback' => function() use ( $wp_customize ) {
        return 'grid_archive' === $wp_customize->get_setting( 'ua_directory_archive_page_view' )->value();
    },
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_contact_notice',
    array(
      'default'           => '',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'sanitize_text_field',
    )
  );

  $wp_customize->add_control( new UA_Notice_Custom_control( $wp_customize, 'ua_directory_archive_contact_notice',
    array(
      'label' => __( 'Person Metadata' ),
      'section' => 'ua_directory_archive_settings'
    )
  ));

  $wp_customize->add_setting( 'ua_directory_archive_profile_image',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_profile_image' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_profile_image',
    array(
      'label'      => __( 'Profile Image', 'ua-theme' ),
      'description'      => __( 'Shows the profile image for each person in the directory archive', 'ua-theme' ),
      'type'       => 'checkbox',
      'section'    => 'ua_directory_archive_settings',
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_location',
    array(
      'default'           =>  $wp_customize->get_setting( 'ua_directory_location' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_location',
    array(
      'label'      => __( 'Location', 'ua-theme' ),
      'description' => 'Shows the location for each person in the directory archive',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_website',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_website' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_website',
    array(
      'label'      => __( 'Website', 'ua-theme' ),
      'description' => 'Shows the website for each person in the directory archive',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_phone',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_phone' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_phone',
    array(
      'label'      => __( 'Phone', 'ua-theme' ),
      'description' => 'Shows the phone for each person in the directory archive',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_email',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_email' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_email',
    array(
      'label'      => __( 'Email', 'ua-theme' ),
      'description' => 'Shows the email for each person in the directory archive',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox',
    )
  );

  $wp_customize->add_setting( 'ua_directory_archive_profile_button',
    array(
      'default'           => $wp_customize->get_setting( 'ua_directory_profile_button' )->value(),
      'sanitize_callback' => 'ua_sanitize_checkbox',
    )
  );

  $wp_customize->add_control( 'ua_directory_archive_profile_button',
    array(
      'label'      => __( 'Link to Profile', 'ua-theme' ),
      'description' => 'Links the name of each person in the directory to their profile page',
      'section'    => 'ua_directory_archive_settings',
      'type'       => 'checkbox',
    )
  );

}

add_action( 'customize_register', 'ua_directory_archive_settings_section' );

/* !SECTION Directory Archive Panel */
