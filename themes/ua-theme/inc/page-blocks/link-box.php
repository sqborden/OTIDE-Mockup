<?php

function render_link_box($attributes) {
  $values = [
    'title' => '',
    'url' => '',
    'opensInNewTab' => false,
    'description' => '',
    'icon' => '',
    'iconAltText' => '',
    'className' => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  $val =
    '<div class="ua_component_wrapper '. $values['className'].'">
      <div class="ua_link-box">';
        if($values['icon'] !== '') {
          $duotone = '';
          if($values['className'] == 'is-style-prominent'){
            $duotone = '-duotone';
          }
          $val .= '<span class="fa'.$duotone.' fa-' . $values['icon'] . '" title="" aria-hidden="true"></span>';
        }

        if($values['iconAltText'] !== '') {
          $val .= '<span class="ua_visually-hidden">' . $values['iconAltText'] . '</span>';
        }

        if($values['opensInNewTab']) {
          $val .= '<a href="' . $values['url'] . '" class="ua_link-box_title" target="_blank" rel="noreferrer noopener">' . $values['title'] .'</a>';
        } else {
          $val .= '<a href="' . $values['url'] . '" class="ua_link-box_title">' . $values['title'] .'</a>';
        }

        if($values['description']) {
          $val .= '<p>' . $values['description'] . '</p>';
        }
        
        $val .='
      </div>
    </div>';

  return $val;
}
