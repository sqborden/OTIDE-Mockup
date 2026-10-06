<?php
function render_contact_card($attributes, $content) {
    $values = [
        'personTags' => '',
        'selectedPostType' => '',
        'selectedPost' => '',
        'imageToggle' => true,
        'tagsToggle' => true,
        'emailToggle' => true,
        'phoneToggle' => true,
        'locationToggle' => true,
        'websiteToggle' => true,
        'linkToggle' => true,
        'descriptionToggle' => true,
        'headingLevel' => 3,
        'title' => '',
        'subtitle' => '',
        'email' => '',
        'phone' => '',
        'location' => '',
        'website' => '',
        'url' => '',
        'opensInNewTab' => false,
        'img' => [
          'src' => '',
          'alt' => ''
        ],
        'showImage' => false,
        'textAlignment' => '',
        'className' => '',
        'isLandscape' => false,
        'mediaId' => ''
    ];

    foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
        $values[$key] = $attributes[$key];
    }
    }

    $landscapeClass = '';
    if($values['isLandscape']) {
      $landscapeClass = ' ua_card--landscape';
    }

    if( $values['selectedPost'] ){
        $selectedPost = $values['selectedPost'];
        $updatedPost = get_post($selectedPost['id']);
        $updatedPostMeta = get_post_meta($selectedPost['id']);
        $updatedTags = wp_get_post_terms($selectedPost['id'], 'directory_tag');
        $title = $updatedPost->post_title;
        $personSubtitle = $updatedPostMeta['_person_subtitle'][0];
        $personPhone = $updatedPostMeta['_person_phone'][0];
        $personEmail = $updatedPostMeta['_person_email'][0];
        $personWebsite = $updatedPostMeta['_person_website'][0];
        $personLocation = $updatedPostMeta['_person_location'][0];

        if( $values['tagsToggle'] && $updatedTags ) {
            $tagsHTML =
                '<div class="ua_component_wrapper">
                    <ul class="ua_tag-list ">';
                        foreach ($updatedTags as $updatedTag) {
                            $tagsHTML .= '<li><a href="' . get_term_link( $updatedTag->term_id, 'directory_tag' ) . '" rel="tag">'.$updatedTag->name.'</a></li>';
                        }
                        $tagsHTML .= '
                    </ul>
                </div>';
        } else { $tagsHTML =''; }

        if( $values['emailToggle'] &&  ( $personEmail != '' ) ){
            $email =
                '<li>
                    <a href="mailto:' . $personEmail . '" rel="email">
                        <span class="fa fa-envelope" aria-hidden="true"></span> ' . $personEmail . '
                    </a>
                </li>';
        } else { $email = ''; }

        if( $values['phoneToggle'] &&  ( $personPhone != '' ) ) {
            $phone =
                '<li>
                    <a href="tel:' . $personPhone . '" rel="phone">
                        <span class="fa fa-phone" aria-hidden="true"></span> ' . $personPhone .
                    '</a>
                </li>';
        } else { $phone = ''; }

        if( $values['locationToggle'] && ( $personLocation != '' ) ) {
            $location =
                '<li>
                    <span class="fa fa-location-dot" aria-hidden="true"></span>
                    ' .$personLocation . '
                </li>';
        } else { $location = ''; }

        if( $values['websiteToggle'] && ( $personWebsite != '' ) ) {
            $website =
                '<li>
                    <a href="'.$personWebsite.'" rel="website">
                        <span class="fa fa-globe" aria-hidden="true"></span> ' . $personWebsite .
                    '</a>
                </li>';
        } else { $website = ''; }

        $image = '';
        if( $values['imageToggle'] ) {
            if( has_post_thumbnail( $selectedPost['id'] ) ) {
                $image =
                    '<div class="ua_card_image-wrapper">'
                        . get_the_post_thumbnail( $selectedPost['id'], 'large' ) .
                    '</div>';
            }
        }

        $cardHTML =
            '<div class="ua_component_wrapper ua_contact-card ua_presence--subtle">
                <article class="ua_card '. $landscapeClass . '">
                    <div class="ua_card_content-wrapper">';
                        if( $values['linkToggle'] ) {
                            $cardHTML .=
                                '<h'. $values['headingLevel'] .' class=" ua_card_title">
                                    <a href="'.$selectedPost['link'].'">'.$title.'</a>
                                </h'. $values['headingLevel'] .'>';
                        } else {
                            $cardHTML .=
                                '<h'. $values['headingLevel'] .' class=" ua_card_title">
                                    '.$title.'
                                </h'. $values['headingLevel'] .'>';
                        }

                        if( $personSubtitle ) {
                            $cardHTML .= '<span class="ua_card_subtitle">' . $personSubtitle . '</span>';
                        }

                        $cardHTML .= $tagsHTML;

                        if( $values['descriptionToggle'] ) {
                            $excerpt = apply_filters( 'the_excerpt', get_the_excerpt( $updatedPost ) );
                            if ( $excerpt ) {
                                $cardHTML .=
                                    '<div class="ua_contact-card_content ua_layout--flow-half">'
                                        . $excerpt .
                                    '</div>';
                            }
                        }

                        if( $email OR $phone OR $location OR $website ) {
                            $cardHTML .=
                                '<ul class="ua_contact-card_info">
                                    '.$email.'
                                    '.$phone.'
                                    '.$location.'
                                    '.$website.'
                                </ul>';
                        }

                        $cardHTML .=
                        '</div>'
                    . $image .
                '</article>
            </div>';
    } else {
        $cardHTML = '';
        if($values['title'] != ''){
            if($values['url'] != ''){
                $title = '<h'. $values['headingLevel'] .' class=" ua_card_title">
                            <a href="'.$values['url'].'">'.$values['title'].'</a>
                        </h'. $values['headingLevel'] .'>'    ;
            }else {
                $title = '<h'. $values['headingLevel'] .'  class=" ua_card_title">
                '.$values['title'].'
                </h'. $values['headingLevel'] .' >'  ;
            }
        }else{
            $title = '';
        }

        if($values['email'] != ''){
            $email = '<li>
                    <a href="mailto:'.$values['email'].'" rel="email">
                    <span class="fa fa-envelope" aria-hidden="true"></span>
                    '.$values['email'].'
                    </a>
                    </li>';
        }else{  $email = ''; }
        if($values['phone'] != ''){
            $phone = '<li>
                    <a href="tel:'.$values['phone'].'" rel="phone">
                    <span class="fa fa-phone" aria-hidden="true"></span>
                    '.$values['phone'].'</a></li>';
        }else{  $phone = ''; }
        if($values['location'] != ''){
            $location =  '<li><span class="fa fa-location-dot" aria-hidden="true"></span> '.$values['location'].' </li>';
        }else{  $location = ''; }
        if($values['website'] != ''){
            $website =  '<li><a href="'.$values['website'].'" rel="website">
                    <span class="fa fa-globe" aria-hidden="true"></span>
                    '.$values['website'].'</a></li>';
        }else{  $website = ''; }

        if($values['showImage']) {
            $image = '
                    <div class="ua_card_image-wrapper">
                    <img src="' .wp_get_attachment_image_src( $values['mediaId'], 'large' )[0] . '" alt="'.$values['title'].'">
                    </div>';
        }else { $image = ''; }

        $cardHTML = '
        <div class="ua_component_wrapper undefined ua_contact-card ua_presence--subtle">
            <article class="ua_card '.$landscapeClass.'">
                <div class="ua_card_content-wrapper">
                   '.$title;

                   if($values['subtitle'] !=''){
                    $cardHTML .='<span class="ua_card_subtitle">'.$values['subtitle'].'</span>';
                   }

                   if((trim($content) !== "") && (trim($content) !=='<div class="wp-block-ua-blocks-contact-card"></div>')){
                    $cardHTML .= '<div class="ua_contact-card_content ua_layout--flow-half">'.$content.' </div>';
                   }

                   if( ($email != '') ||  ($phone!== '') || ($location != '') || ($website != '') ){
                    $cardHTML .= '
                    <ul class="ua_contact-card_info">
                    '.$email.'
                    '.$phone.'
                    '.$location.'
                    '.$website.'
                    </ul>';
                   }

                $cardHTML .= '
                </div>';

                $cardHTML .= $image;
                $cardHTML .= '
            </article>
        </div>';
    }
    return $cardHTML;
}
?>
