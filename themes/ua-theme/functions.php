<?php

/* =================================================================
/* SECTION Imports
================================================================= */

require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'api.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'modify-block-markup.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'register-blocks.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'ua-helpers.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'ua-admin-customization.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-theme-updater.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-main-nav.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-sidebar-nav.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-dynamic-sidebar-nav.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'clone-page.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'customizer.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'roles-capabilities.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'get-browser.php';
require_once get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'directory-system.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'robots-control.php';

/* !SECTION Imports */
/* =================================================================
/* SECTION Setup
================================================================= */

if (!defined('UA_THEME_NAME')) {
  define('UA_THEME_NAME', 'ua-theme');
}

if (!defined('UA_THEME_VERSION')) {
  define('UA_THEME_VERSION', '3.7.3');
}

if (!defined('UA_THEME_DIR')) {
  define('UA_THEME_DIR', get_template_directory());
}

if (!defined('UA_THEME_URL')) {
  define('UA_THEME_URL', get_template_directory_uri());
}

if(!isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  new UA_Theme_Updater();
}

define( 'DISALLOW_FILE_EDIT', true );

/* !SECTION Setup */
/* =================================================================
/* SECTION Load Assets
================================================================= */
// Disable the third party "Available to Install" section in the block inserter
remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );

function ua_register_fe_assets() {
  wp_register_script_module(
    'minerva',
    UA_THEME_URL . '/assets/scripts/minerva-' . UA_THEME_VERSION . '.js',
    array(),
    null
  );

  $deps = [
    [
      'id' => 'minerva',
      'import' => 'dynamic',
    ]
  ];

  wp_enqueue_script_module(
    'ua-scripts',
    get_template_directory_uri() . '/assets/scripts/ua-theme-' . UA_THEME_VERSION . '.js',
    $deps,
    null
  );
  wp_enqueue_style('ua-styles', get_template_directory_uri() . '/assets/styles/ua-theme-' . UA_THEME_VERSION . '.css');
  wp_enqueue_style('fontawesome', 'https://assetfiles.ua.edu/fonts/fa_v6/fontawesome.css');
}

add_action('wp_enqueue_scripts', 'ua_register_fe_assets');

function ua_register_cookie_banner_assets() {
  wp_enqueue_style('cookie-banner-styles', get_template_directory_uri() . '/assets/styles/cookie-banner.css');
  wp_enqueue_script('cookie-banner-scripts', get_template_directory_uri() . '/assets/scripts/cookie-banner.js', array(), false, true);
}

add_action('wp_enqueue_scripts', 'ua_register_cookie_banner_assets');

function ua_register_style_tokens() {
  wp_enqueue_style('ua-tokens', get_template_directory_uri() . '/assets/styles/ua-tokens-' . UA_THEME_VERSION . '.css');
}

add_action('wp_enqueue_scripts', 'ua_register_style_tokens');

function ua_enqueue_block_assets() {
  wp_enqueue_script(
    'ua-theme-block-scripts',
    get_template_directory_uri() . '/build/index.js',
    array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-core-data', 'wp-data','wp-server-side-render')
  );

  // wp.domReady is finicky with dependencies
  // Source: https://github.com/WordPress/gutenberg/issues/27607#issuecomment-1022935579
  $dependencies = array( 'wp-blocks', 'wp-dom-ready' );
  if( is_object( get_current_screen() ) ) {
    if( get_current_screen()->id == 'site-editor' ) {
      $dependencies[] = 'wp-edit-site';
    } elseif( get_current_screen()->id == 'widgets' ) {
      $dependencies[] = 'wp-edit-widgets';
    } else {
      $dependencies[] = 'wp-edit-post';
    }
  } else {
    $dependencies[] = 'wp-edit-post';
  }

  wp_enqueue_script(
    'ua-theme-app',
    get_template_directory_uri() . '/assets/scripts/ua-theme-app.js',
    $dependencies,
    filemtime(get_template_directory() . '/assets/scripts/ua-theme-app.js')
  );

  // Localize the script to pass the site URL
  wp_localize_script('ua-theme-app', 'get_site_url', array(
      'siteUrl' => get_site_url(), // Use get_site_url() to get the site URL
  ));
}

/* !SECTION Load Assets */
/* =================================================================
/* SECTION Add Menu and Widget Areas
================================================================= */

function ua_widgets_init() {
	register_sidebar( array(
		'name'          => 'Site Footer',
		'id'            => 'site_footer',
		'before_widget' => '',
		'after_widget'  => '',
	) );

  register_sidebar( array(
		'name'          => 'Posts Index Sidebar',
		'id'            => 'index_sidebar',
		'before_widget' => '',
		'after_widget'  => '',
	) );

  register_sidebar( array(
		'name'          => 'Post Sidebar',
		'id'            => 'single_sidebar',
		'before_widget' => '',
		'after_widget'  => '',
	) );

  register_sidebar( array(
		'name'          => 'Post After',
		'id'            => 'single_after',
		'before_widget' => '',
		'after_widget'  => '',
	) );

  register_sidebar( array(
		'name'          => '404 Page Content',
		'id'            => '404_content',
		'before_widget' => '',
		'after_widget'  => '',
  ) );

}
add_action( 'widgets_init', 'ua_widgets_init' );

function ua_menus_init() {

  register_nav_menus(
    array(
      'main-nav' => esc_html__('Primary Navigation', 'ua-theme'),
    )
  );

}
add_action('init', 'ua_menus_init');

/* !SECTION Add Menu and Widget Areas */
/* =================================================================
/* SECTION Shortcodes
================================================================= */

add_shortcode('shy', 'ua_shy_shortcode');

function ua_shy_shortcode($atts) {
    return '&shy;';
}

add_shortcode('icon', 'ua_icon_shortcode');

function ua_icon_shortcode($atts) {
    if( array_key_exists( 'name', $atts ) ) {
      $prefix = 'fa';
      if( array_key_exists( 'prefix', $atts ) ) {
        $prefix = $atts['prefix'];
      }

      return
        '<span class="' . $prefix . ' fa-' . $atts['name'] . '" title="' . $atts['name'] . '" aria-hidden="true"></span>
        <span class="ua_visually-hidden">' . $atts['name'] . '</span>';
    }
}

/* !SECTION Shortcodes */

if (!defined('UA_BLOCKS_TO_DISABLE_IN_PAGE_EDITOR'))
  define('UA_BLOCKS_TO_DISABLE_IN_PAGE_EDITOR', array(
    'ua-theme/brandfooter',
    'ua-theme/brandbar',
    'ua-theme/title-bar',
    'ua-theme/site-footer',
    'core/site-tagline'
  ));

function ua_theme_init() {
  ua_add_rest_endpoints();

  register_ua_theme_blocks();

  register_block_pattern_category(
    'harper-patterns',
    array( 'label' => __( 'Harper Patterns', 'ua-theme' ) )
  );

  add_post_type_support('page', 'excerpt');

  remove_theme_support('core-block-patterns');

  add_theme_support('title-tag');

  add_theme_support( 'responsive-embeds' );

  remove_theme_support('block-templates');

  register_post_type('directory', array(
    'supports' => array(
      'title','editor','thumbnail','excerpt'
    ),
    'taxonomies' => array('directory_category'),
    'public' => true,
    'has_archive' => true,
    'show_in_rest' => true,
    'rest_base'  => 'directory',
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    'slug' => 'directory',
    'labels' => array(
      'name' => 'Directory',
      'add_new_item' => 'Add New Person',
      'edit_item' => 'Edit Person',
      'view_item' => 'View Person',
      'all_items' => 'All People',
      'singular_name' => 'Person',
      'add_new' => 'Add New Person'
    ),
    'menu_icon' => 'dashicons-businessperson'
  ));

  register_taxonomy( 'directory_category', array('directory'), array(
    'hierarchical' => true,
    'label' => 'Directory Categories',
    'singular_label' => 'Directory Category',
    'rewrite' => array( 'slug' => 'directory_category' ),
    'show_admin_column' => true,
    'show_in_rest' => true,
    'show_ui' => true,
    'public' => true,
    'publicly_queryable' => true,
    'show_in_nav_menus' => true,
    'slug' => 'directory_category',
    )
  );
  register_taxonomy_for_object_type( 'directory_category', 'directory' );

  register_taxonomy( 'directory_tag', array('directory'), array(
    'hierarchical' => false,
    'label' => 'Directory Tags',
    'singular_label' => 'Directory Tag',
    'rewrite' => array( 'slug' => 'directory_tag' ),
    'show_admin_column' => true,
    'show_in_rest' => true,
    'show_ui' => true,
    'public' => true,
    'publicly_queryable' => true,
    'show_in_nav_menus' => true,
    )
  );
  register_taxonomy_for_object_type( 'directory_tag', 'directory' );

  // Explicit auth_callback for the Page Settings meta. These keys are marked protected
  // which would otherwise flip their default REST auth_callback to __return_false and block all writes.
  // Setting it explicitly keeps REST writes working regardless of hook ordering — it only depends on the user's
  // ability to edit the specific post.
  $page_meta_auth = function ($allowed, $meta_key, $object_id) {
    return current_user_can('edit_post', $object_id);
  };

  register_post_meta('page', 'hero_blocks', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'string',
    'auth_callback' => $page_meta_auth
  ));

  register_post_meta('page', 'sidebar_menu', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'string',
    'auth_callback' => $page_meta_auth
  ));

  register_post_meta('page', 'sidebar', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'boolean',
    'auth_callback' => $page_meta_auth
  ));

  register_post_meta('page', 'sidebar_type', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'string',
    'default' => 'select',
    'auth_callback' => $page_meta_auth
  ));

  register_post_meta('page', 'hero', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'boolean',
    'auth_callback' => $page_meta_auth
  ));

  register_post_meta('page', 'title_alignment', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'string',
    'default' => 'left',
    'auth_callback' => $page_meta_auth
  ));

  register_post_meta('page', 'title_width', array(
    'show_in_rest' => true,
    'single' => true,
    'type' => 'string',
    'default' => 'standard',
    'auth_callback' => $page_meta_auth
  ));

  // Make the Page Settings meta fields protected so they don't get overridden by the custom fields UI
  add_filter('is_protected_meta', function ($protected, $meta_key, $meta_type) {
    static $keys = ['hero','sidebar','hero_blocks','sidebar_menu','sidebar_type','title_alignment','title_width'];
    if ($meta_type !== 'post') {
      return $protected;
    }
    return in_array($meta_key, $keys, true) ? true : $protected;
  }, 10, 3);

  register_block_style('core/button', [
    'name' => 'subtle',
    'label' => __('Subtle', 'ua-theme'),
  ]);

  register_block_style('core/group', [
    'name' => 'elevated',
    'label' => __('More Contrast', 'ua-theme'),
  ]);

  register_block_style('core/group', [
    'name' => 'prominent',
    'label' => __('Most Contrast', 'ua-theme'),
  ]);

  register_block_style('core/columns', [
    'name' => 'elevated',
    'label' => __('More Contrast', 'ua-theme'),
  ]);

  register_block_style('core/columns', [
    'name' => 'prominent',
    'label' => __('Most Contrast', 'ua-theme'),
  ]);

  register_block_style('core/media-text', [
    'name' => 'elevated',
    'label' => __('More Contrast', 'ua-theme'),
  ]);

  register_block_style('core/media-text', [
    'name' => 'prominent',
    'label' => __('Most Contrast', 'ua-theme'),
  ]);

  register_block_style('ua-blocks/card', [
    'name' => 'subtle',
    'label' => __('Subtle', 'ua-theme'),
  ]);

  register_block_style('ua-blocks/steps', [
    'name' => 'subtle',
    'label' => __('Subtle', 'ua-theme'),
  ]);

  register_block_style('ua-blocks/steps', [
    'name' => 'elevated',
    'label' => __('Elevated', 'ua-theme'),
  ]);

  register_block_style('ua-blocks/link-box', [
    'name' => 'prominent',
    'label' => __('Prominent', 'ua-theme'),
  ]);

  register_block_style('ua-blocks/event', [
    'name' => 'subtle',
    'label' => __('Subtle', 'ua-theme'),
  ]);

  register_block_style('ua-blocks/event-feed', [
    'name' => 'subtle',
    'label' => __('Subtle', 'ua-theme'),
  ]);

  register_block_style('core/separator', [
    'name' => 'elephant',
    'label' => __('Elephant', 'ua-theme'),
  ]);

  register_block_style('core/embed', [
    'name' => 'youtube-short',
    'label' => __('Youtube Short', 'ua-theme'),
  ]);
}

// On save, set hero and sidebar display to false if Page Settings block has been removed via deletion or revision
add_action( 'save_post', function($post_id, $post, $update) {
  $post_has_page_settings = false;
  $post_blocks = parse_blocks($post->post_content);

  foreach($post_blocks as $block) {
    if($block["blockName"] === 'ua-theme/data') {
      $post_has_page_settings = true;
    }
  }

  if(!$post_has_page_settings) {
    update_post_meta($post_id, 'hero', false);
    update_post_meta($post_id, 'sidebar', false);
    update_post_meta($post_id, 'title_alignment', 'left');
    update_post_meta($post_id, 'title_width', 'standard');
  }
}, 10, 3 );

add_action('init', 'ua_theme_init');

function ua_theme_after_setup(){
  add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'ua_theme_after_setup');

add_action('enqueue_block_editor_assets', 'ua_enqueue_block_assets');

add_filter('render_block', 'ua_modify_block_markup', 10, 2);

add_filter('allowed_block_types_all', 'ua_prevent_theme_blocks_in_editor', PHP_INT_MAX, 2);

add_action( 'admin_menu', 'ua_support_admin_menu' );

add_filter( 'document_title_parts', function( $title_parts_array ) {
  $title_parts_array['tagline'] = '';
  return $title_parts_array;
} );

add_filter( 'document_title_separator', function( $sep ) {
  $sep = "|";
  return $sep;
} );

add_action('wp_dashboard_setup', 'ua_customize_dashboard');

add_action('admin_notices', 'ua_admin_notice_warn');

add_action('wp_head', 'ua_theme_modify_head');

// set priority to 11 so the default priority value of 10 in the child theme will override
add_filter('ua_theme_favicon_markup', function($faviconMarkup) { echo $faviconMarkup; }, 11);

add_action('wp_footer', 'ua_theme_modify_footer');

add_filter('block_categories_all', 'ua_add_block_category', 10, 2);

add_filter('page_row_actions', 'ua_add_clone_page_link', 10, 2);

add_action('admin_action_ua_clone_page', 'ua_clone_page');

register_setting(
  'general',
  'siteurl',
  array(
      'show_in_rest' => array(
          'name'   => 'url',
          'schema' => array(
              'format' => 'uri',
          ),
      ),
      'type'         => 'string',
      'description'  => __( 'Site URL.' ),
  )
);

/* Directory Person Subtitle */
function person_subtitle() {
  add_meta_box(
    'person_subtitle',
    'Subtitle',
    'person_subtitle_callback',
    'directory'
  );
}

function person_subtitle_callback( $post ) {
  $subtitle = get_post_meta( $post->ID, '_person_subtitle', true );
  ?>
  <label for="person-subtitle">Subtitle:</label>
  <input type="text" id="person-subtitle" name="person-subtitle" value="<?php echo esc_attr( $subtitle ); ?>">
  <?php
}

add_action( 'add_meta_boxes', 'person_subtitle' );

function save_person_subtitle( $post_id ) {
  if ( isset( $_POST['person-subtitle'] ) ) {
    update_post_meta( $post_id, '_person_subtitle', sanitize_text_field( $_POST['person-subtitle'] ) );
  }
}

add_action( 'save_post', 'save_person_subtitle' );

/* Person Phone */
function person_phone() {
  add_meta_box(
    'person_phone',
    'Phone',
    'person_phone_callback',
    'directory'
  );
}

function person_phone_callback( $post ) {
  $phone = get_post_meta( $post->ID, '_person_phone', true );
  ?>
  <label for="person-phone">Phone:</label>
  <input type="text" id="person-phone" name="person-phone" value="<?php echo esc_attr( $phone); ?>">
  <?php
}

add_action( 'add_meta_boxes', 'person_phone' );

function save_person_phone( $post_id ) {
  if ( isset( $_POST['person-phone'] ) ) {
    update_post_meta( $post_id, '_person_phone', sanitize_text_field( $_POST['person-phone'] ) );
  }
}

add_action( 'save_post', 'save_person_phone' );

/* Person Email */
function person_email() {
  add_meta_box(
    'person_email',
    'Email',
    'person_email_callback',
    'directory'
  );
}

function person_email_callback( $post ) {
  $email = get_post_meta( $post->ID, '_person_email', true );
  ?>
  <label for="person-email">Email:</label>
  <input type="text" id="person-email" name="person-email" value="<?php echo esc_attr( $email); ?>">
  <?php
}

add_action( 'add_meta_boxes', 'person_email' );

function save_person_email( $post_id ) {
  if ( isset( $_POST['person-email'] ) ) {
    update_post_meta( $post_id, '_person_email', sanitize_text_field( $_POST['person-email'] ) );
  }
}

add_action( 'save_post', 'save_person_email' );

/* Directory Person Website */
function person_website() {
  add_meta_box(
    'person_website',
    'Website',
    'person_website_callback',
    'directory'
  );
}
function person_website_callback( $post ) {
  $website = get_post_meta( $post->ID, '_person_website', true );
  ?>
  <label for="person-website">Website:</label>
  <input type="text" id="person-website" name="person-website" value="<?php echo esc_attr( $website); ?>">
  <?php
}

add_action( 'add_meta_boxes', 'person_website' );

function save_person_website( $post_id ) {
  if ( isset( $_POST['person-website'] ) ) {
    update_post_meta( $post_id, '_person_website', sanitize_text_field( $_POST['person-website'] ) );
  }
}

add_action( 'save_post', 'save_person_website' );

/* Directory Person Location */
add_action( 'rest_api_init', 'register_custom_meta_fields' );

function person_location() {
  add_meta_box(
    'person_location',
    'Location',
    'person_location_callback',
    'directory'
  );
}

function person_location_callback( $post ) {
  $location = get_post_meta( $post->ID, '_person_location', true );
  ?>
  <label for="person-location">Location:</label>
  <input type="text" id="person-location" name="person-location" value="<?php echo esc_attr( $location); ?>">
  <?php
}

add_action( 'add_meta_boxes', 'person_location' );

function save_person_location( $post_id ) {
  if ( isset( $_POST['person-location'] ) ) {
    update_post_meta( $post_id, '_person_location', sanitize_text_field( $_POST['person-location'] ) );
  }
}

add_action( 'save_post', 'save_person_location' );

/* Directory Order Index */
function person_order_index() {
  add_meta_box(
    'person_order_index',
    'Order Index',
    'person_order_index_callback',
    'directory'
  );
}

function person_order_index_callback( $post ) {
  $order_index = get_post_meta( $post->ID, '_person_order_index', true );
  ?>
  <label for="person-order-index">Oder Index:</label>
  <input type="text" id="person-order-index" name="person-order-index" value="<?php echo esc_attr( $order_index); ?>">
  <?php
}
add_action( 'add_meta_boxes', 'person_order_index' );

function save_person_order_index( $post_id ) {
  if ( isset( $_POST['person-order-index'] ) ) {
    update_post_meta( $post_id, '_person_order_index', sanitize_text_field( $_POST['person-order-index'] ) );
  }
}

add_action( 'save_post', 'save_person_order_index' );

/** AND tax_query logic for directory REST API */
add_filter( 'rest_directory_collection_params', 'register_directory_tax_relation_param' );
function register_directory_tax_relation_param( $params ) {
    $params['tax_relation'] = array(
        'description' => 'Logical relationship between taxonomy filters.',
        'type'        => 'string',
        'enum'        => array( 'OR', 'AND', 'AND_ANY' ),
        'default'     => 'AND_ANY',
    );
    return $params;
}

add_filter( 'rest_directory_query', 'directory_rest_and_tax_query', 10, 2 );
function directory_rest_and_tax_query( $args, $request ) {
    $tax_relation   = $request->get_param( 'tax_relation' ) ?: 'AND_ANY';
    $tag_param      = $request->get_param( 'directory_tag' );
    $category_param = $request->get_param( 'directory_category' );

    if ( ! $tag_param && ! $category_param ) {
        return $args;
    }

    $both_present = $tag_param && $category_param;

    // Only intervene when needed:
    // - AND always needs per-term clauses
    // - AND_ANY + both taxonomies: needs explicit top-level AND (WP default is also AND,
    //   but we want to be explicit about the within-taxonomy OR)
    // - OR + both taxonomies: must override WP's default AND cross-taxonomy relation
    // - OR/AND_ANY with a single taxonomy: WP handles it correctly, return early
    if ( $tax_relation !== 'AND' && ! $both_present ) {
        return $args;
    }

    // Top-level relation between taxonomy clauses
    $top_relation = ( $tax_relation === 'OR' ) ? 'OR' : 'AND';
    $tax_query = array( 'relation' => $top_relation );

    if ( $tag_param ) {
        if ( $tax_relation === 'AND' ) {
            // One clause per term — post must have every selected tag
            foreach ( wp_parse_id_list( $tag_param ) as $tag_id ) {
                $tax_query[] = array(
                    'taxonomy' => 'directory_tag',
                    'field'    => 'term_id',
                    'terms'    => array( $tag_id ),
                );
            }
        } else {
            // One clause for all tags — post must have any selected tag
            $tax_query[] = array(
                'taxonomy' => 'directory_tag',
                'field'    => 'term_id',
                'terms'    => wp_parse_id_list( $tag_param ),
            );
        }
    }

    if ( $category_param ) {
        if ( $tax_relation === 'AND' ) {
            // One clause per term — post must have every selected category
            foreach ( wp_parse_id_list( $category_param ) as $cat_id ) {
                $tax_query[] = array(
                    'taxonomy' => 'directory_category',
                    'field'    => 'term_id',
                    'terms'    => array( $cat_id ),
                );
            }
        } else {
            // One clause for all categories — post must have any selected category
            $tax_query[] = array(
                'taxonomy' => 'directory_category',
                'field'    => 'term_id',
                'terms'    => wp_parse_id_list( $category_param ),
            );
        }
    }

    $args['tax_query'] = $tax_query;

    return $args;
}

/** Custom post query */
add_action( 'pre_get_posts', 'wpse176347_pre_get_posts' );
function wpse176347_pre_get_posts( $q )
{
    if(    !is_admin()
        && $q->is_main_query()
        && (   $q->is_post_type_archive( 'directory' )
            || $q->is_tax('directory_category')
            || $q->is_tax('directory_tag')
        )
    ) {
        $q->set( 'posts_per_page', -1 );
        $q->set( 'orderby', 'title' );
        $q->set( 'order', 'ASC' );
    }
}

/**
 * Plugin Name: Remove Archive Prefix
 * Plugin URI: https://www.binarymoon.co.uk/
 * Description: Hide the prefix displayed at the start of archive titles.
 * Author: Ben Gillbanks
 * Version: 1.0
 */

/**
 * Add a span around the title prefix so that the prefix can be hidden with CSS
 * if desired.
 * Note that this will only work with LTR languages.
 *
 * @param string $title Archive title.
 * @return string Archive title with inserted span around prefix.
 */
function ua_modify_archive_title_markup( $title ) {

	// Skip if the site isn't LTR, this is visual, not functional.
	// Should try to work out an elegant solution that works for both directions.
	if ( is_rtl() ) {
		return $title;
	}

	// Split the title into parts so we can wrap them with spans.
	$title_parts = explode( ': ', $title, 2 );

	// Glue it back together again.
	if ( ! empty( $title_parts[1] ) ) {
		$title = wp_kses(
			$title_parts[1],
			array(
				'span' => array(
					'class' => array(),
				),
			)
		);
		$title = '<span class="ua_archive-title_prefix">' . esc_html( $title_parts[0] ) . ' </span>' . $title;
	}

	return $title;
}

add_filter( 'get_the_archive_title', 'ua_modify_archive_title_markup' );



// To Fetch custom post type meta data
// Register custom meta feilds to appear in apiFetch
function register_custom_meta_fields() {
  register_rest_field( 'directory', 'location', array(
      'get_callback'    => 'get_location_value',
      'update_callback' => null, // Or your update callback
      'schema'          => null, // Or your schema
  ) );

  register_rest_field( 'directory', 'website', array(
    'get_callback'    => 'get_website_value',
    'update_callback' => null, // Or your update callback
    'schema'          => null, // Or your schema
  ) );

  register_rest_field( 'directory', 'email', array(
    'get_callback'    => 'get_email_value',
    'update_callback' => null, // Or your update callback
    'schema'          => null, // Or your schema
  ) );

  register_rest_field( 'directory', 'phone', array(
    'get_callback'    => 'get_phone_value',
    'update_callback' => null, // Or your update callback
    'schema'          => null, // Or your schema
  ) );

  register_rest_field( 'directory', 'subtitle', array(
    'get_callback'    => 'get_subtitle_value',
    'update_callback' => null, // Or your update callback
    'schema'          => null, // Or your schema
  ) );

  register_rest_field( 'directory', 'featured_image_url', array(
    'get_callback'    => 'get_featured_image_url',
    'update_callback' => null, // Or your update callback
    'schema'          => null, // Or your schema
  ) );

  register_rest_field( 'directory', 'order_index', array(
    'get_callback'    => 'get_order_index_value',
    'update_callback' => null, // Or your update callback
    'schema'          => null, // Or your schema
  ) );
}



function get_featured_image_url( $object, $field_name, $request ) {
  return  wp_get_attachment_image_src($object['featured_media'], 'full');;
}

function get_subtitle_value( $object, $field_name, $request ) {
  return get_post_meta( $object['id'], '_person_subtitle', true );
}

function get_location_value( $object, $field_name, $request ) {
  return get_post_meta( $object['id'], '_person_location', true );
}

function get_website_value( $object, $field_name, $request ) {
return get_post_meta( $object['id'], '_person_website', true );
}

function get_email_value( $object, $field_name, $request ) {
return get_post_meta( $object['id'], '_person_email', true );
}

function get_phone_value( $object, $field_name, $request ) {
return get_post_meta( $object['id'], '_person_phone', true );
}

function get_order_index_value( $object, $field_name, $request ) {
return get_post_meta( $object['id'], '_person_order_index', true );
}


add_action( 'rest_api_init', 'register_custom_meta_fields' );

function remove_fitText_variation( $metadata ) {
	$teh_array = ['core/paragraph', 'core/heading'];
    if ( in_array($metadata['name'], $teh_array, true )) {
        $metadata['supports']['typography']['fitText'] = false;
    }
    return $metadata;
}
add_filter( 'block_type_metadata', 'remove_fitText_variation' );

function ua_default_full_resolution( $metadata ) {
  if ( in_array( $metadata['name'], [ 'core/cover', 'core/image' ], true )
  && isset( $metadata['attributes'], $metadata['attributes']['sizeSlug'] )
  && is_array( $metadata['attributes']['sizeSlug'] )
  ) {
    $metadata['attributes']['sizeSlug']['default'] = 'full';
  }
  return $metadata;
}
add_filter( 'block_type_metadata', 'ua_default_full_resolution' );


// Custom Search for Directory
function custom_directory_search_template( $template ) {
    $post_type = get_query_var( 'post_type' );
    $post_type = is_array( $post_type ) ? $post_type : [ $post_type ];
    if ( is_search() && in_array( 'directory', $post_type, true ) ) {
        return locate_template( 'search-directory.php' );
    }
    return $template;
}
add_filter( 'template_include', 'custom_directory_search_template' );

function ua_block_editor_settings( $settings ) {
	$settings['fontLibraryEnabled'] = false;

	return $settings;
}
add_filter( 'block_editor_settings_all', 'ua_block_editor_settings' );
