<?php

function render_card($attributes, $content) {
  $values = [
    'headingLevel' => 2,
    'title' => '',
    'subTitle' => '',
    'url' => '',
    'opensInNewTab' => false,
    'img' => [
      'src' => '',
      'alt' => ''
    ],
    'showImage' => false,
    'aspectRatio' => '',
    'textAlignment' => '',
    'className' => '',
    'isLandscape' => false
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

  $landscapeClass = '';
  if($values['isLandscape']) {
    $landscapeClass = ' ua_card--landscape';
  }

  $val =
    '<div class="ua_component_wrapper '. $values['className'] .'">
      <article class="ua_card' . $landscapeClass . $textAlignmentValue . '">
        <div class="ua_card_content-wrapper">';

          if($values['title']) {
            $val .= '
            <h' . $values['headingLevel'] . ' class="ua_card_title">';
              if($values['opensInNewTab'] AND $values['url'] !== '') {
                $val .= '<a href="' . $values['url'] . '" target="_blank" rel="noreferrer noopener">' . $values['title'] .'</a>';
              } elseif($values['url'] !== '') {
                $val .= '<a href="' . $values['url'] . '">' . $values['title'] .'</a>';
              } else {
                $val .= $values['title'];
              } $val .=
            '</h' . $values['headingLevel'] .  '>';
          }
          
          if($values['subTitle']) {
              $val .= '<span class="ua_card_subtitle">'.$values['subTitle'].'</span>';
          }

          $val .= $content .
        '</div>';

        if($values['showImage']) {
          $aspectRatioValue = '';
          if(!$values['isLandscape']) {
            if($values['aspectRatio']){
              $aspectRatioValue = 'style = "aspect-ratio: '.$values['aspectRatio'].'"';
            }
          }

          $val .= 
            '<div class="ua_card_image-wrapper">
              <img src="' . $values['img']['src'] . '" alt="' . $values['img']['alt'] . '"  '.$aspectRatioValue.'>
            </div>';
        }

        $val .=
      '</article>
    </div>';

  return $val;
}
