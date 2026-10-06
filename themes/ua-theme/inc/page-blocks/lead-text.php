<?php 
function render_lead_text($attributes) {
  $className = '';
  if (array_key_exists('className', $attributes)) {
    $className = ' ' . $attributes['className'];
  }

  $text = '';
  if(array_key_exists('text', $attributes)) {
    $text = $attributes['text'];
  }

  $val = 
    '<div class="ua_component-wrapper' . $className . '">
      <p class="ua_lead-in">
        <strong>' . $text . '</strong>
      </p>
    </div>';
  
  return $val;
}
