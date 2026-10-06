<?php

class UA_Theme_Walker_Dynamic_Sidebar_Menu extends Walker_Page {
  private $el;

  public function start_lvl( &$output, $depth = 0, $args = null ) {
    $output .= "<ul aria-hidden='false' aria-label='" . $this->el . " submenu'>";
	}

  public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
    global $post;
    $class_names = $value = $attributes = '';
    $indent = '';

    // Check so there aren't more than 3 levels of nesting in the sidebar
    if ($args["has_children"] && ( $depth != ( $args["depth"] - 1 ) ) ) {
      $value = ' aria-haspopup=true aria-expanded=true';
    }

    // Check if this nav item is the current page
    if($post->ID == $item->ID) {
      $class_names .= 'ua_secondary-navigation_link--active';
      $attributes .= sprintf(' %1$s="%2$s"', 'aria-current', 'true');
    }
    
    $class_names = $class_names ? sprintf(' class="%s"', esc_attr($class_names)) : '';
    $id = '';

    $output .= sprintf('%1$s<li%2$s%3$s%4$s>', $indent, $id, $value, $class_names);

    $atts = array();
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel']    = !empty($item->xfn) ? $item->xfn    : '';
    $atts['href']   = !empty(get_permalink($item->ID)) ? get_permalink($item->ID) : '';
   //$atts['data-text'] = $item->title;


    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args);
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $value       = ('href' === $attr) ? esc_url($value) : esc_attr($value);
        $attributes .= sprintf(' %1$s="%2$s"', $attr, $value);
      }
    }

    $item_output = '';
    if(array_key_exists('before', $args)) {
      $item_output = $args["before"]; 
    }

    $item_output .= '<span>';
    $item_output .= sprintf('<a%s>', $attributes);
    $item_output .= $args["link_before"] . apply_filters('the_title', $item->post_title, $item->ID) . $args["link_after"];
    $item_output .= '</a>';

    if ( $args["has_children"] && ( $depth != ( $args["depth"] - 1 ) ) ) {
      $item_output .= '
        <button>
          <span class="ua_secondary-navigation_inactive_content">
            <span class="fa fa-caret-down" title="Expand ' . $item->post_title . ' menu" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Expand ' . $item->post_title . ' menu</span>
          </span>

          <span class="ua_secondary-navigation_active_content">
            <span class="fa fa-caret-up" title="Close ' . $item->post_title . ' menu" aria-hidden="false"></span>
            <span class="ua_visually-hidden">Close ' . $item->post_title . ' menu</span>
          </span>
        </button>';
    }

    $item_output .= '</span>';
    
    if(array_key_exists('after', $args) ) {
      $item_output .= $args["after"];
    }

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }

  public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
    if (!$element)
      return;

    $args[0]["has_children"] = !empty($children_elements[$element->ID]);
    
    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }

  static function fallback() {
    return '';
  }
}
