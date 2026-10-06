<?php
/**
 * Function to Sort directory posts by last name
 */
function sort_by_last_name($posts) {
    usort($posts, function($a, $b) {
        // Remove text after comma and trim
        $title_a = trim(explode(',', $a->post_title)[0]);
        $title_b = trim(explode(',', $b->post_title)[0]);
        
        $words_a = array_values(array_filter(explode(' ', $title_a)));
        $words_b = array_values(array_filter(explode(' ', $title_b)));
        
        // Get last name based on number of words
        $last_name_a = '';
        $last_name_b = '';
        
        $count_a = count($words_a);
        $count_b = count($words_b);
        
        // Use third word if available, otherwise second, otherwise first
        $last_name_a = strtolower($words_a[min(2, $count_a - 1)] ?? $words_a[0] ?? '');
        $last_name_b = strtolower($words_b[min(2, $count_b - 1)] ?? $words_b[0] ?? '');
        
        // Primary sort by last name
        $result = strcmp($last_name_a, $last_name_b);
        
        // Secondary sort by first name if last names match
        if ($result === 0) {
            $first_name_a = strtolower($words_a[0] ?? '');
            $first_name_b = strtolower($words_b[0] ?? '');
            return strcmp($first_name_a, $first_name_b);
        }
        return $result;
    });
    return $posts;
}


/**
 * Customizer settings for directory pages
 */
function get_directory_settings($prefix = 'ua_directory_') {
    $keys = array(
        'categories',
        'categories_position',
        'tags',
        'tags_position',
        'search',
        'search_position',
        'profile_image',
        'email',
        'phone',
        'location',
        'website',
        'profile_button',
        'page_view',
        'max_columns',
        'sorting',
        'is_pin_category',
        'pinned_category',
        'records_per_page'
    );

    $defaults = array(
        'categories'          => true,
        'categories_position' => 'bottom',
        'tags'                => true,
        'tags_position'       => 'top',
        'search'              => true,
        'search_position'     => 'top',
        'profile_image'       => true,
        'email' => true,
        'phone' => true,
        'location' => true,
        'website' => true,
        'profile_button' => true,
        'page_view' => 'grid',
        'max_columns' => '2',
        'sorting' => 'order_index',
        'is_pin_category' => false,
        'pinned_category' => '',
        'records_per_page' => 10
    );

    $settings = array();
    foreach ($keys as $key) {
        $settings[$key] = get_theme_mod($prefix . $key, $defaults[$key]);
    }

    if ($settings['page_view'] == 'list' || $settings['page_view'] == 'list_archive') {
        $settings['grid_style'] = '--grid-column-count:1';
        $settings['landscape_css'] = 'ua_card--landscape';
    } else {
        $settings['grid_style'] = '--grid-column-count:' . $settings['max_columns'];
        $settings['landscape_css'] = '';
    }

    if ($settings['records_per_page'] < 1) {
        $settings['records_per_page'] = 1;
    } elseif ($settings['records_per_page'] > 100) {
        $settings['records_per_page'] = 100;
    }

    return $settings;
}

/**
  * Person contact fields
  */
function person_contact_field($type, $value) {
    $icons = array(
        'email' => 'fa-envelope',
        'phone' => 'fa-phone',
        'location' => 'fa-location-dot',
        'website' => 'fa-globe'
    );
    
    $icon = '<span class="fa ' . $icons[$type] . '" aria-hidden="true"></span>';
    
    switch ($type) {
        case 'email':
            return '<a href="mailto:' . $value . '" rel="email">' . $icon . ' ' . $value . '</a>';
        case 'phone':
            return '<a href="tel:' . $value . '" rel="phone">' . $icon . ' ' . $value . '</a>';
        case 'website':
            return '<a href="' . $value . '" rel="website">' . $icon . ' ' . $value . '</a>';
        case 'location':
            return $icon . ' ' . $value;
        default:
            return $value;
    }
}


/**
  * Get directory taxonomy terms
  */
function get_directory_terms($taxonomy, $args = array()) {
    $defaults = array(
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    );
    $args = wp_parse_args($args, $defaults);
    $args['taxonomy'] = $taxonomy;
    return get_terms($args);
}

/**
 * Build the tags, categories, and search HTML fragments for position-aware rendering.
 *
 * @param array  $settings          Result of get_directory_settings().
 * @param array  $person_tags       Terms from get_directory_terms('directory_tag').
 * @param array  $person_categories Terms from get_directory_terms('directory_category').
 * @param array  $search_overrides  Optional overrides for the search render attrs (e.g. 'value').
 * @param string $result_html       Optional HTML appended after the search widget (e.g. result count).
 * @return array {
 *   tags_position, categories_position, search_position,
 *   tags_html, categories_html, search_html
 * }
 */
function build_directory_widget_html( $settings, $person_tags, $person_categories, $search_overrides = array(), $result_html = '' ) {
    $tags_position       = $settings['tags']       ? $settings['tags_position']       : null;
    $categories_position = $settings['categories'] ? $settings['categories_position'] : null;
    $search_position     = $settings['search']     ? $settings['search_position']     : null;

    // Tags HTML
    $tags_html = '';
    if ( $tags_position && $person_tags ) {
        $tags_attrs = [
            'tagListType' => 'directory_tag',
            'isRibbon'    => $tags_position !== 'sidebar',
            'align'       => $tags_position !== 'sidebar' ? 'wide' : '',
        ];
        $tags_html  = '<div class="alignwide">';
        $tags_html .= '<p style="text-transform: uppercase;" class="' . ( $tags_position !== 'sidebar' ? 'alignwide' : '' ) . '">Tags</p>';
        $tags_html .= render_tag_list( $tags_attrs );
        $tags_html .= '</div>';
    }

    // Categories HTML
    $categories_html = '';
    if ( $categories_position && $person_categories ) {
        $categories_html = '<div class="alignwide">';
        if ( $categories_position === 'sidebar' ) {
            $categories_html .= '<p style="text-transform: uppercase;">Categories</p>';
            $categories_html .= '<ul class="wp-block-categories unstyled">';
        } else {
            $categories_html .= '<ul style="display:flex; flex-wrap:wrap; list-style:none;">';
            $categories_html .= '<li style="margin-inline-start:0; text-transform: uppercase;">Categories</li>';
        }
        $categories_html .= '<li><a href="' . esc_url( get_post_type_archive_link( 'directory' ) ) . '">All</a></li>';
        $categories_html .= implode( '', array_map( fn( $c ) => '<li><a href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( $c->name ) . '</a></li>', $person_categories ) );
        $categories_html .= '</ul></div>';
    }

    // Search HTML
    $search_html = '';
    if ( $search_position ) {
        $search_attrs = array_merge( [
            'align'       => $search_position !== 'sidebar' ? 'wide' : '',
            'placeholder' => '',
            'showLabel'   => true,
            'labelText'   => 'Search Directory',
            'iconButton'  => $search_position === 'sidebar',
        ], $search_overrides );
        $search_html = render_directory_search( $search_attrs );
        if ( $result_html ) {
            $search_html .= $result_html;
        }
    }

    return [
        'tags_position'       => $tags_position,
        'categories_position' => $categories_position,
        'search_position'     => $search_position,
        'tags_html'           => $tags_html,
        'categories_html'     => $categories_html,
        'search_html'         => $search_html,
    ];
}

/**
 * Output widgets (tags, categories, search) for a given position slot.
 *
 * @param string $position    'top', 'sidebar', or 'bottom'.
 * @param array  $widget_data Return value of build_directory_widget_html().
 * @return string
 */
function render_directory_widgets( $position, $widget_data ) {
    $output = '';
    if ( $widget_data['search_position'] === $position ) $output .= $widget_data['search_html'];
    if ( $widget_data['tags_position']   === $position ) $output .= $widget_data['tags_html'];
    if ( $widget_data['categories_position'] === $position ) $output .= $widget_data['categories_html'];
    return $output;
}