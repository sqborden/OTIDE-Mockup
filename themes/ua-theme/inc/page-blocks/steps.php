<?php 
function render_steps($attributes, $content) {

  $inlineStyles = '';
  if($attributes['maxColumns'] > 0){
    $inlineStyles =  'style="--grid-column-count: '.$attributes['maxColumns'].'"';
  }

  $customClass = '';
  if(isset($attributes['className'])){
    $customClass = $attributes['className'];
  }

  $align_class = '';
  if(isset($attributes['align'])){
    if($attributes['align'] === 'wide'){
      $align_class = 'alignwide';
    }else if($attributes['align'] === 'full'){
      $align_class = 'alignfull';
    }
  }

  $val = '<div class="ua_component_wrapper '. $customClass .' '.$align_class.'">';
  $val .= '<ol class="ua_steps ua_layout--grid" '.$inlineStyles.'>';
  $val .= $content;
  $val .= '</ol>';
  $val .= '</div>';
  return $val;
}