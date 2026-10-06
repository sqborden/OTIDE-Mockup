<?php 

function render_step($attributes, $content) {
  $values = [
    'title' => '',
    'headingLevel' => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  $val =  '<li>';
  $val .= '<h'.$values['headingLevel'].' class="ua_steps-title">'. $values['title'] .'</h'.$values['headingLevel'].'>';
  if($content){
    $val .= $content;
  }
  $val .= '</li>';
  return $val;
}
