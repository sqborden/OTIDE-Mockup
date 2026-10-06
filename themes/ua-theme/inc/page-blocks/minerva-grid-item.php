<?php 
function render_minerva_grid_item($attributes, $content) {
  $grid_type = $attributes['gridType'];

  $alignStyle = '';
  if($attributes['verticalAlignment']){
    if($attributes['verticalAlignment'] === 'Top'){
      $alignStyle = 'style = "align-content: start "';
    }else if($attributes['verticalAlignment'] === 'Center'){
      $alignStyle = 'style = "align-content: center "';
    }else if($attributes['verticalAlignment'] === 'Bottom'){
      $alignStyle = 'style = "align-content: end "';
    }
  }

  if( $attributes['gridType'] === 'div'){
    $grid_type = 'div';
  }else{
    $grid_type = 'li';
  }

  $val = '<'.$grid_type.'  '.$alignStyle .'>';
  $val .= $content;
  $val .= '</'. $grid_type .'>';
  return  $val;
}
