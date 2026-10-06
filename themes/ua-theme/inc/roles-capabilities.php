<?php

/* =================================================================
/* SECTION Update Editor Role
================================================================= */

function ua_editor_capabilities() {
  $user = wp_get_current_user();
  if (in_array('editor', (array) $user->roles)) {
    // They're an editor, so grant the edit_theme_options capability if they don't have it
    if (!current_user_can('edit_theme_options')) {
      $role_object = get_role('editor');
      $role_object->add_cap('edit_theme_options');
    }
  }
}

add_action('admin_menu', 'ua_editor_capabilities');

// Map 'edit_css' capability for administrators in multisite
function ua_enable_multisite_admin_css($caps, $cap, $user_id) {
  // Do not run on single sites, it causes problems.
  if (!is_multisite()) {
    return $caps;
  }

  if ($cap === 'edit_css') {
    $user = get_user_by('ID', $user_id);
    if ($user && in_array('administrator', (array) $user->roles)) {
      $caps = array('manage_options');
    }
  }
  return $caps;
}

add_filter('map_meta_cap', 'ua_enable_multisite_admin_css', 10, 3);

function ua_hide_editor_fse() {
  global $wp_admin_bar;
  $user = wp_get_current_user();

  if (in_array('editor', (array) $user->roles)) {
    $wp_admin_bar->remove_menu('site-editor');
  }
}

add_action('wp_before_admin_bar_render', 'ua_hide_editor_fse');

function ua_hide_editor_fse_admin() {
  $user = wp_get_current_user();
  if (in_array('editor', (array) $user->roles)) {
    remove_submenu_page( 'themes.php', 'site-editor.php' );
    remove_submenu_page( 'themes.php', 'themes.php' );
  }
}

add_action('admin_init', 'ua_hide_editor_fse_admin');

/* !SECTION Update Editor Role */
/* =================================================================
/* SECTION Disable Comments
================================================================= */

function ua_disable_comments() {
  // Redirect any user trying to access comments page
  global $pagenow;
  if ($pagenow === 'edit-comments.php') {
    wp_redirect(admin_url());
    exit;
  }

  // Remove comments metabox from dashboard
  remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

  // Disable support for comments and trackbacks in post types
  foreach (get_post_types() as $post_type) {
    if (post_type_supports($post_type, 'comments')) {
      remove_post_type_support($post_type, 'comments');
      remove_post_type_support($post_type, 'trackbacks');
    }
  }
}

add_action('admin_init', 'ua_disable_comments');

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
function ua_remove_comments_menu() {
  remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'ua_remove_comments_menu');

// Remove comments link from admin bar in wp-admin
function ua_remove_comments_admin_bar() {
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('comments');
}
add_action('wp_before_admin_bar_render', 'ua_remove_comments_admin_bar');

/* !SECTION Disable Comments */
?>
