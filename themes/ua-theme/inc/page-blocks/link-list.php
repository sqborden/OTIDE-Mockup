<?php 
function render_link_list($attributes, $content) {

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

  $value = '<div class="ua_component_wrapper '.$customClass.' '.$align_class.'">';
  $value .= '<ul class="ua_link-list ua_layout--grid" '. $inlineStyles.'>';
  $value .= $content;
  $value .= '</ul>';
  $value .= '</div>';
  return $value;
}

