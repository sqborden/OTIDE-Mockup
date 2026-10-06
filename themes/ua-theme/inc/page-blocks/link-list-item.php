<?php 

function render_link_list_item($attributes, $content) {
  $values = [
    'title' => '',
    'url' => '',
    'opensInNewTab' => false,
    'icon' => '',
    'iconAltText' => ''
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  $icon_detail = '';
  $val = '<li>';
  if($values['icon'] !== '') {
    $icon_detail .= '<span class="fa fa-' . $values['icon'] . '" title="" aria-hidden="true"></span>';
  }

  if($values['iconAltText'] !== '') {
    $icon_detail .= '<span class="ua_visually-hidden">' . $values['iconAltText'] . '</span>';
  }

  if($values['opensInNewTab']) {
    $val .= '<a href="' . $values['url'] . '" class="ua_link-list_item" target="_blank" rel="noreferrer noopener">' . $icon_detail .' '. $values['title'] .'</a>';
  } else {
    $val .= '<a href="' . $values['url'] . '" class="ua_link-list_item">' . $icon_detail .' '. $values['title'] .'</a>';
  }
  $val .= '</li>';
  return $val;
}

