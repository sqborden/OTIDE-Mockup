<?php
function render_tag_list($attributes, $content = '') {
  // Default values
  $values = [
    'isRibbon' => false,
    'tagListType' => null,
    'textAlignment' => null,
    'className' => null,
  ];
  // Assign values from attributes
  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }
  // Fetch terms based on tagListType
  $values['tags'] = [];
  if ($values['tagListType']) {
    $terms = get_terms([
    'taxonomy' => $values['tagListType'],
    'hide_empty' => false,
    ]);
  }
  // Prepare items for rendering
  $values['tags'] = [];
  if (!is_wp_error($terms)) {
    foreach ($terms as $term) {
        $values['tags'][] = [
            'name' => $term->name,
            'link' => get_term_link($term),
        ];
    }
  }
  // Build the wrapper attributes string
  $wrapper_class = 'ua_component_wrapper';
  if ($values['className']) {
    $wrapper_class .= ' ' . $values['className'];
  }
  $align_class = '';
  if(isset($attributes['align'])){
    if($attributes['align'] === 'wide'){
      $wrapper_class .= ' ' . 'alignwide';
    }else if($attributes['align'] === 'full'){
      $wrapper_class .= ' ' . 'alignfull';
    }
  }

  $justify_class = '';
  if ($values['textAlignment'] && !$values['isRibbon']) {
    $justify_class = ' ua_justify--' . $values['textAlignment'];
    if( $values['textAlignment'] === 'left' ) {
      $justify_class = ' ua_justify--start';
    } elseif ( $values['textAlignment'] === 'right' ) {
      $justify_class = ' ua_justify--end';
    } 
  }

  $tag_list_markup = '<ul class="ua_tag-list' . $justify_class . '">';
  foreach ($values['tags'] as $tag) {
    $tag_list_markup .= '<li>';
    $tag_list_markup .= ua_tag($tag);
    $tag_list_markup .= '</li>';
  }
  $tag_list_markup .= '</ul>';

  $markup = '';

  if ($values['isRibbon']) {
    $markup = '<div class="' . $wrapper_class . '">
      <div class="ua_tag-list_ribbon">
        <button class="ua_tag-list_scroll-back">';
          $markup .= '<span class="fa fa-chevron-left" aria-hidden="true"></span>';
          $markup .= '<span class="ua_visually-hidden">Scroll left</span>
        </button>';
        $markup .= $tag_list_markup;
        $markup .= '<button class="ua_tag-list_scroll-forward">';
          $markup .= '<span class="fa fa-chevron-right" aria-hidden="true"></span>';
          $markup .= '<span class="ua_visually-hidden">Scroll right</span>
        </button>
        <button class="ua_tag-list_expand">';
          $markup .= '<span class="fa fa-circle-chevron-down" aria-hidden="true"></span>';
          $markup .= '<span class="fa fa-circle-chevron-up" aria-hidden="true"></span>';
          $markup .= '<span class="ua_visually-hidden">Toggle expanded tag list</span>
        </button>
      </div>
    </div>';
  } else {
    $markup = '<div class="' . $wrapper_class . '">';
    $markup .= $tag_list_markup;
    $markup .= '</div>';
  }
  return $markup;
}

// Function to render individual tag
function ua_tag($tag) {
  if ($tag['link']) {
    return '<a href="' . esc_url( $tag['link'] ) . '" class="ua_tag">' . esc_html( $tag['name'] ) . '</a>';
  } else {
    return '<span class="ua_tag">' . esc_html( $tag['name'] ) . '</span>';
  }
}