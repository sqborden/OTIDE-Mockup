<?php

class UA_Theme_Walker_Sidebar_Menu extends Walker_Nav_Menu {
  // https://wordpress.stackexchange.com/questions/268571/output-the-aria-labelledby-parameter-for-a-nav-menu-child
  private $el;

  public function start_lvl( &$output, $depth = 0, $args = null ) {
    $output .= "<ul aria-hidden='false' aria-label='" . $this->el . " submenu'>";
	}

  public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $this->el = $item->title;
    global $post;

    $class_names = $value = $attributes = '';
    $indent      = ($depth) ? str_repeat("\t", $depth) : '';

    if ($args->has_children)
      $value = ' aria-haspopup=true aria-expanded=true';

    $class_names = $class_names ? sprintf(' class="%s"', esc_attr($class_names)) : '';
    $id = '';

    // Check if this nav item is the current page
    if($post->ID == $item->object_id) {
      if($class_names == ''){
        $class_names = 'class="ua_secondary-navigation_link--active"';
      }else {
        $class_names .= 'ua_secondary-navigation_link--active';
      }
      $attributes .= sprintf(' %1$s="%2$s"', 'aria-current', 'true');
    }
 
    $output .= sprintf('%1$s<li%2$s%3$s%4$s>', $indent, $id, $value, $class_names);

    $atts = array();
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel']    = !empty($item->xfn) ? $item->xfn    : '';
    $atts['href']   = !empty($item->url) ? $item->url    : '';
    //$atts['data-text'] = $item->title;

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args);
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $value       = ('href' === $attr) ? esc_url($value) : esc_attr($value);
        $attributes .= sprintf(' %1$s="%2$s"', $attr, $value);
      }
    }

    $item_output  = $args->before;
    $item_output .= '<span>';
    $item_output .= sprintf('<a%s>', $attributes);
    $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
    $item_output .= '</a>';

    if ($args->has_children) {
      $item_output .= '
        <button>
          <span class="ua_secondary-navigation_inactive_content">
            <span class="fa fa-caret-down" title="Expand ' . $item->title . ' menu" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Expand ' . $item->title . ' menu</span>
          </span>

          <span class="ua_secondary-navigation_active_content">
            <span class="fa fa-caret-up" title="Close ' . $item->title . ' menu" aria-hidden="false"></span>
            <span class="ua_visually-hidden">Close ' . $item->title . ' menu</span>
          </span>
        </button>';
    }

    $item_output .= '</span>';
    $item_output .= $args->after;

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }

  public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
    if (!$element)
      return;

    $id_field = $this->db_fields['id'];

    if (is_object($args[0]))
      $args[0]->has_children = !empty($children_elements[$element->$id_field]);

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }

  static function fallback() {
    return '';
  }
}
