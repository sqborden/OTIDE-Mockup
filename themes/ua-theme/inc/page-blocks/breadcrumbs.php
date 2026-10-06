<?php
    function render_breadcrumbs($attributes, $content) {
        global $post;
        $home = 'Home';
        $homeLink = home_url();
        $breadcrumbs_html = '';
        $breadcrumbs_html = '<nav class="ua_breadcrumbs" id="UA_BreadCrumbs" aria-label="breadcrumbs">';
        $breadcrumbs_html .= '<ol class="ua_breadcrumbs_list">';
        $breadcrumbs_html .= '<li><a href="' . $homeLink . '">' . $home . '</a></li>';

        if ( ! $post ) {
            return array();
        }
    
        $ancestors = array();
        $id          = $post->post_parent;

        if( ( !empty($id) ) ){
            $ancestors[] = $id;
        }

        while ( $ancestor = get_post( $id ) ) {
            // Loop detection: If the ancestor has been seen before, break.
            if ( empty( $ancestor->post_parent ) || ( $ancestor->post_parent == $post->ID ) || in_array( $ancestor->post_parent, $ancestors, true ) ) {
                break;
            }
            $id          = $ancestor->post_parent;
            $ancestors[] = $id;
        }
    
        foreach (array_reverse($ancestors) as $ancestor) {
            $breadcrumbs_html .= '<li><a href="'.get_permalink($ancestor).'">'.get_the_title($ancestor).'</a></li>';
        }
        $breadcrumbs_html .= '<li><span>'.get_the_title($post->id).'</span></li>';
        $breadcrumbs_html .= '</ol>';
        $breadcrumbs_html .= '</nav>';
        return $breadcrumbs_html;
    } 
