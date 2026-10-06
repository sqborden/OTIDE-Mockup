<?php 
function render_minerva_grid($attributes, $content) {
  
  $inlineStyles = '';
  $blockGapStyles = '';

  if(isset($attributes['style']['spacing']['blockGap']))
  {
    $dimension_array = $attributes['style']['spacing']['blockGap'];
    $inlineStyles = 'style=';
    foreach ($dimension_array as $key => $value) {
      $cssValue = explode("|", $value);
      if($key == 'top'){
        $blockGapStyles .= 'row-gap:var(--ua_space--'.end($cssValue).');';
      }else if($key == 'left'){
        $blockGapStyles .= 'column-gap:var(--ua_space--'.end($cssValue).');';
      }
    }
  }
  $inlineStyles .= $blockGapStyles;

  if($attributes['maxColumns'] > 0){
    if( $inlineStyles == ''){
      $inlineStyles = 'style=';
    }
    $inlineStyles .='--grid-column-count:'.$attributes['maxColumns'];
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

  $grid_type = $attributes['gridType'] ;
  $val = '<div class="ua_component_wrapper '. $customClass .' '.$align_class.'">';
  $val .= '<'.$grid_type.' class="ua_layout--grid" '.$inlineStyles.'>';
  $val .= $content;
  $val .= '</'. $grid_type .'>';
  $val .= '</div>';
  return  $val;
}
