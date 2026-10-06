<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Add custom api endpoints
 *
 * @return void
 */

function ua_add_rest_endpoints() {

  add_action('rest_api_init', function () {

    //Make the menus available via api
    //use by org-footer block's NavMenu component
    register_rest_route('ua-theme/v1', '/menu', array(
      'methods' => 'GET',
      'callback' => 'ua_get_menu',
      'permission_callback' => '__return_true', //public
    ));

    register_rest_route('ua-theme/v1', '/menus', array(
      'methods' => 'GET',
      'callback' => 'ua_get_menus',
      'permission_callback' => '__return_true', //public
    ));
  });
}

/**
 * Get list of pages contained in custom menu
 *
 * @return array
 */
function ua_get_menu(\WP_REST_Request $request) {
  $slug = $request->get_param('slug');
  $menu_array = wp_get_nav_menu_items($slug, [
    'container'     => '',
    'items_wrap'    => '%3$s',
    'depth'         => 1,
    'fallback_cb'   => false,
  ]);

  $menu = array();

  function populate_children($menu_array, $menu_item) {
    $children = array();
    if (!empty($menu_array)) {
      foreach ($menu_array as $k => $m) {
        if ($m->menu_item_parent == $menu_item->ID) {
          $children[$m->ID] = array();
          $children[$m->ID]['ID'] = $m->ID;
          $children[$m->ID]['title'] = $m->title;
          $children[$m->ID]['url'] = $m->url;
          unset($menu_array[$k]);
          $children[$m->ID]['children'] = populate_children($menu_array, $m);
        }
      }
    };
    return $children;
  }

  foreach ($menu_array as $m) {
    if (empty($m->menu_item_parent)) {
      $menu[$m->ID] = array();
      $menu[$m->ID]['ID'] = $m->ID;
      $menu[$m->ID]['title'] = $m->title;
      $menu[$m->ID]['url'] = $m->url;
      $menu[$m->ID]['children'] = populate_children($menu_array, $m);
    }
  }

  return $menu;
}

function ua_get_menus() {
  return wp_get_nav_menus();
}