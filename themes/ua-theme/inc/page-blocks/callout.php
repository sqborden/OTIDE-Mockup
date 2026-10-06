<?php

function render_callout($attributes, $content) {
  $values = [
    'title' => '',
    'headingLevel' => 2,
    'children' => '',
    'context' => '',
    'className' => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  if($values['context'] !== '') {
    $values['context'] = 'ua_context--' . $values['context'];
  }

  $val =  
    '<div class="ua_component_wrapper '.$values['className'].'">
      <div class="ua_callout ' . $values['context'] . ' ua_layout--flow">
        <h' . $values['headingLevel'] . ' class="ua_callout_title">'; 
          switch($values['context']) {
            case 'ua_context--positive':
              $val .= 
                '<span class="fa fa-circle-check" title="check mark" aria-hidden="true"></span>
                <span class="ua_visually-hidden">check mark</span>';
                break;
            case 'ua_context--negative':
              $val .=
                '<span class="fa fa-circle-exclamation" title="exclamation" aria-hidden="true"></span>
                <span class="ua_visually-hidden">exclamation</span>';
                break;
            case 'ua_context--info':
              $val .= 
                '<span class="fa fa-circle-info" title="Info" aria-hidden="true"></span>
                <span class="ua_visually-hidden">Info</span>';
          }

          $val .= $values['title'] . 
        '</h' . $values['headingLevel'] . '>
        
        ' . $content . '
      </div>
    </div>';

  return $val;
}