<?php

class UA_Theme_Walker_Main_Menu extends Walker_Nav_Menu {
  public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= "<ul>";
	}

  public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $class_names = $value = $attributes = '';
    $indent      = ($depth) ? str_repeat("\t", $depth) : '';

    if ($args->has_children)
      $class_names = 'ua_menu-item-parent';

    $class_names = $class_names ? sprintf(' class="%s"', esc_attr($class_names)) : '';
    $id = '';

    $output .= sprintf('%1$s<li%2$s%3$s%4$s>', $indent, $id, $value, $class_names);

    $atts = array();
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel']    = !empty($item->xfn) ? $item->xfn    : '';
    $atts['href']   = !empty($item->url) ? $item->url    : '';

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args);
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $value       = ('href' === $attr) ? esc_url($value) : esc_attr($value);
        $attributes .= sprintf(' %1$s="%2$s"', $attr, $value);
      }
    }

    $item_output  = $args->before;
    $item_output .= sprintf('<a%s>', $attributes);
    $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
    $item_output .= '</a>';

    if ($args->has_children) {
      $item_output .= '
        <button>
          <span class="ua_primary-navigation_inactive_content">
            <span class="fa fa-caret-down" title="Expand ' . $item->title .  ' menu" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Expand ' . $item->title . ' menu</span>
          </span>

          <span class="ua_primary-navigation_active_content">
            <span class="fa fa-caret-up" title="Close ' . $item->title . ' menu" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Close ' . $item->title . ' menu</span>
          </span>
        </button>';
    }

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
