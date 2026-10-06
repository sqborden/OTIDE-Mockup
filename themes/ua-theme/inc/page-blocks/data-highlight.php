<?php 

function render_data_highlight($attributes) {
  $values = [
    'title' => '',
    'description' => '',
    'lead' => '',
    'showLeadIn' => false,
    'textAlignment' => '',
    'className' => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  $textAlignmentValue = '';
  if($values['textAlignment']) {
    $textAlignmentValue = ' ua_align--' . $values['textAlignment'];
  }

  $val =  
    '<div class="ua_component_wrapper '. $values['className'] .'">
      <p class="ua_data-highlight' . $textAlignmentValue . '">';
        if($values['showLeadIn'] AND $values['lead'] !== '') {
          $val .= '<span class="ua_data-highlight_lead">' . $values['lead'] . '</span>';
        } $val .= 
        '<strong class="ua_data-highlight_stat">' . $values['title'] . '</strong>
        <span class="ua_data-highlight_description">' . $values['description'] . '</span>
      </p>
    </div>';

  return $val;
}