<?php
function render_directory_feed($attributes) {
    global $post;
    $values = [
        'imageToggle' => true,
        'categoriesToggle' => true,
        'tagsToggle' => true,
        'excerptToggle' => true,
        'emailToggle' => true,
        'phoneToggle' => true,
        'locationToggle' => true,
        'websiteToggle' => true,
        'linkToggle' => true,
        'headingLevel' => 3,
        'layout' => 'list',
        'maxColumns' => 4,
        'category' => '',
        'tag' => '',
        'postPerPage' => 100,
        'offset' => 0,
        'maxPages' => 0,
        'orderBy' => 'last_name',
        'orderIndexOverride' => true,
        'taxRelation' => 'AND_ANY',
    ];

    foreach( $values as $key => $value ) {
        if (array_key_exists($key, $attributes)) {
            $values[$key] = $attributes[$key];
        }
    }

    // Back-compat: "order_index" was once a Sort By choice (now a separate
    // toggle). "" once meant date-desc (now removed; last_name is the default).
    // Migrates already-published posts that haven't been re-saved in the editor.
    if ($values['orderBy'] === 'order_index') {
        $values['orderBy'] = 'last_name';
        $values['orderIndexOverride'] = true;
    } elseif ($values['orderBy'] === '') {
        $values['orderBy'] = 'last_name';
    }

    $inlineStyles =   '--grid-column-count:1' ;
    $landscape_css = 'ua_card--landscape';
    if($values['layout'] == "grid") {
        $inlineStyles =   '--grid-column-count:'.$values['maxColumns'] ;
        $landscape_css  = '';
    }
    $directoryHTML = '';
    $html = "";

    $align_class = '';
    if(isset($attributes['align'])){
      if($attributes['align'] === 'wide'){
        $align_class = 'alignwide';
      }else if($attributes['align'] === 'full'){
        $align_class = 'alignfull';
      }
    }

    $customClass = '';
    if(isset($attributes['className'])){
      $customClass = $attributes['className'];
    }

    $tax_relation = $values['taxRelation'];
    $top_relation = ( $tax_relation === 'OR' ) ? 'OR' : 'AND';
    $tax_query    = array( 'relation' => $top_relation );

    if (($values['category']) != '') {
        $categoryArray = explode(',', $values['category']);
        if ($tax_relation === 'AND') {
            // One clause per term — post must have every selected category
            foreach ($categoryArray as $cat_id) {
                $tax_query[] = array(
                    'taxonomy' => 'directory_category',
                    'field'    => 'id',
                    'terms'    => array( intval($cat_id) ),
                );
            }
        } else {
            // One clause for all categories — post must have any selected category
            $tax_query[] = array(
                'taxonomy' => 'directory_category',
                'field'    => 'id',
                'terms'    => $categoryArray,
            );
        }
    }

    if (($values['tag']) != '') {
        $tagArray = explode(',', $values['tag']);
        if ($tax_relation === 'AND') {
            // One clause per term — post must have every selected tag
            foreach ($tagArray as $tag_id) {
                $tax_query[] = array(
                    'taxonomy' => 'directory_tag',
                    'field'    => 'id',
                    'terms'    => array( intval($tag_id) ),
                );
            }
        } else {
            // One clause for all tags — post must have any selected tag
            $tax_query[] = array(
                'taxonomy' => 'directory_tag',
                'field'    => 'id',
                'terms'    => $tagArray,
            );
        }
    }

    $current_page = get_query_var('paged');
    $current_page = max( 1, $current_page );
    $per_page = $values['postPerPage'];
    $offset_start = $values['offset'];
    $offset = ( $current_page - 1 ) * $per_page + $offset_start;

    $args = array(
        'post_type'      => 'directory',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'offset'         => $offset,
        'tax_query'      => $tax_query,
        'paged'          => $current_page,
    );

    // first_name and last_name both go through a PHP-side sort that strips
    // honorific prefixes and degree suffixes before extracting the sort key.
    // That means every render fetches the full filtered set, reorders in PHP,
    // and slices — there's no longer a DB-pagination fast path. The initial
    // WP_Query orderby/order doesn't matter (PHP overwrites the ordering)
    // but title/ASC keeps the un-pinned tail stable if both sort passes are
    // somehow skipped.
    $args['orderby'] = 'title';
    $args['order']   = 'ASC';

    $full_args                   = $args;
    $full_args['posts_per_page'] = -1;
    $full_args['offset']         = 0;
    unset($full_args['paged']);

    $wp_query  = new WP_Query($full_args);
    $all_posts = $wp_query->have_posts() ? $wp_query->posts : [];

    if ($values['orderBy'] === 'first_name') {
        $all_posts = sort_posts_by_first_name($all_posts);
    } else {
        $all_posts = sort_posts_by_last_name($all_posts);
    }
    if ($values['orderIndexOverride']) {
        $all_posts = pin_posts_by_order_index($all_posts);
    }

    $wp_query->posts      = array_slice($all_posts, $offset, $per_page ?: PHP_INT_MAX);
    $wp_query->post_count = count($wp_query->posts);

    $total_rows = max( 0, $wp_query->found_posts - $offset_start );
    $total_pages = ceil( $total_rows / $per_page );

    if(($values['maxPages'] > 0) && ($values['maxPages'] <= $total_pages)){
        $total_pages = $values['maxPages'] ;
    }

    $directoryHTML .=  '<div class=" is-layout-flow ua_component_wrapper '.$align_class.' '.$customClass.'">
            <div class="ua_layout--grid" style='.$inlineStyles.'>';

    if ( $wp_query->have_posts() ) :
        while ( $wp_query->have_posts() ) :  $wp_query->the_post();
            $subtitle = get_post_meta( $post->ID, '_person_subtitle', true );
            $phone = get_post_meta( $post->ID, '_person_phone', true );
            $email = get_post_meta( $post->ID, '_person_email', true );
            $location = get_post_meta( $post->ID, '_person_location', true );
            $website = get_post_meta( $post->ID, '_person_website', true );
            $title = $post->post_title;
            $view_link= get_permalink();

            $directoryHTML .= '<div class="ua_component_wrapper ua_contact-card ua_presence--subtle">';
            $directoryHTML .= '<article class="ua_card '.$landscape_css.'"> ';
            if($values['imageToggle'] && has_post_thumbnail()){
                $directoryHTML .= ' <div class="ua_card_image-wrapper">
                <img src="'.get_the_post_thumbnail_url( $post->ID, 'large').'" alt="'.$title.'">
                </div>';
            }
            $directoryHTML .= '<div class="ua_card_content-wrapper">
                    <h' . $values['headingLevel'] . ' class="ua_card_title">';
                    if(($values['linkToggle'])) {
                        $directoryHTML .= '<a href="' . $view_link . '">' . $title . '</a>';
                    } else {
                        $directoryHTML .= $title;
                    }
                    $directoryHTML .= '</h' . $values['headingLevel'] .  '>
                    <span class="ua_card_subtitle"> '.$subtitle.'</span>';

                    if(($values['categoriesToggle'] == true)){
                        $category_list = get_the_term_list( $post->ID, 'directory_category', '<li>', '</li><li>', '</li>' );

                        if ( ! empty( $category_list ) ) {
                            $directoryHTML .= '<div class="ua_component_wrapper">
                                                <ul class="ua_tag-list">';
                            $directoryHTML .= $category_list;
                            $directoryHTML .= '</ul>
                                            </div>';
                        }
                    }

                    if(($values['tagsToggle'] == true)){
                         $directoryHTML .= ' <div class="ua_component_wrapper">

                                                <ul class="ua_tag-list ">';
                                                $directoryHTML .=  get_the_term_list( $post->ID,'directory_tag','<li>','</li><li>','</li>');
                                                $directoryHTML .= '</ul>
                                            </div>';
                    }

                    if ( $values['excerptToggle'] == true ) {
                        $excerpt = apply_filters( 'the_excerpt', get_the_excerpt( $post ) );
                        if ( $excerpt ) {
                            $directoryHTML .= '<div class="ua_contact-card_content ua_layout--flow-half">
                            ' . $excerpt . '
                            </div>';
                        }
                    }
                    $directoryHTML .= '
                    <ul class="ua_contact-card_info">';
                if(($values['emailToggle']) && $email!=''){
                $directoryHTML .= '<li>
                    <a href="mailto:'.$email.'" rel="email">
                        <span class="fa fa-envelope" aria-hidden="true"></span>
                        '.$email.'</a>
                    </li>';
                }
                if(($values['phoneToggle']) && $phone!=''){
                    $directoryHTML .= ' <li>
                                <a href="tel:'.$phone.'" rel="phone">
                                <span class="fa fa-phone" aria-hidden="true"></span>
                                '.$phone.'
                                </a>
                            </li>';
                }
                if(($values['locationToggle']) && $location!=''){
                $directoryHTML .= '<li>
                                <span class="fa fa-location-dot" aria-hidden="true"></span> '.$location.'
                            </li>';
                }
                if(($values['websiteToggle']) && $website != ''){
                $directoryHTML .= '<li >
                                <a href="'.$website.'" rel="website">
                                <span class="fa fa-globe" aria-hidden="true"></span> '.$website.'
                                </a>
                            </li>';
                }
                $directoryHTML .= '</ul> ';

            $directoryHTML .= '</div>';
            $directoryHTML .= '</article>';
            $directoryHTML .= '</div>';

        endwhile;
        wp_reset_postdata();
        $directoryHTML .= '</div><div class="ua_component_wrapper ">
            <nav aria-label="Pagination" class="ua_pagination">
              <div>';
            $directoryHTML .=  paginate_links( array(
                'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'total'        => $total_pages,
                'current'      => $current_page,
                'format'       => '?paged=%#%',
                'show_all'     => false,
                'type'         => 'plain',
                'end_size'     => 2,
                'mid_size'     => 1,
                'prev_next'    => true,
                'prev_text'    => sprintf( '<span class="fa fa-arrow-left" aria-hidden="true"></span>  %1$s', __( 'Previous', 'ua-theme' ) ),
                'next_text'    => sprintf( '%1$s  <span class="fa fa-arrow-right" aria-hidden="true"></span>', __( 'Next', 'ua-theme' ) ),
                'add_args'     => false,
                'add_fragment' => '',
            ) );
            $directoryHTML .= '</div>
              </nav>
            </div>
           </div>';
            else : '<p>No directory entries are available</p>';
        endif;

    return  $directoryHTML;
}

/**
 * Honorific prefixes that appear before a name. Compared case-insensitively
 * after stripping punctuation, so this list holds the canonical lowercase form
 * with no periods. Mirrored in edit.js NAME_PREFIXES — keep in sync.
 */
const DIRECTORY_NAME_PREFIXES = [
    'dr', 'mr', 'mrs', 'ms', 'mx', 'prof', 'professor',
    'rev', 'reverend', 'fr', 'father', 'sr', 'hon', 'honorable',
];

/**
 * Degree and generational suffixes that appear after a name. Compared
 * case-insensitively after stripping punctuation. Mirrored in edit.js
 * NAME_SUFFIXES — keep in sync.
 */
const DIRECTORY_NAME_SUFFIXES = [
    'jr', 'sr', 'ii', 'iii', 'iv', 'v',
    'phd', 'md', 'jd', 'mba',
    'dds', 'dvm', 'edd', 'psyd',
    'ms', 'ma', 'bs', 'ba',
    'mph', 'mfa', 'llm',
    'esq', 'cpa', 'pe', 'rn',
];

/**
 * Strip a title's comma-tail (degree suffixes like ", PhD"), then peel
 * leading prefix tokens and trailing suffix tokens. Returns the cleaned
 * token list, guaranteeing at least one token if the input had any.
 */
function directory_normalize_name_tokens($title) {
    $without_comma_tail = trim(explode(',', $title)[0]);
    $tokens = array_values(array_filter(preg_split('/\s+/', $without_comma_tail)));

    $is_prefix = function ($word) {
        $key = strtolower(str_replace(['.', ','], '', $word));
        return in_array($key, DIRECTORY_NAME_PREFIXES, true);
    };
    $is_suffix = function ($word) {
        $key = strtolower(str_replace(['.', ','], '', $word));
        return in_array($key, DIRECTORY_NAME_SUFFIXES, true);
    };

    // Strip leading prefixes, but always keep at least 1 token.
    while (count($tokens) > 1 && $is_prefix($tokens[0])) {
        array_shift($tokens);
    }
    // Strip trailing suffixes, but always keep at least 1 token.
    while (count($tokens) > 1 && $is_suffix($tokens[count($tokens) - 1])) {
        array_pop($tokens);
    }

    return $tokens;
}

/**
 * Sort by last name (last word of the normalized title). Tie-breaker is the
 * full normalized title so e.g. "Patrick Allen" sorts before "Sara Allen".
 */
function sort_posts_by_last_name($posts) {
    usort($posts, function ($a, $b) {
        $tokens_a = directory_normalize_name_tokens($a->post_title);
        $tokens_b = directory_normalize_name_tokens($b->post_title);

        $last_a = strtolower(end($tokens_a) ?: '');
        $last_b = strtolower(end($tokens_b) ?: '');
        $result = strcmp($last_a, $last_b);
        if ($result !== 0) {
            return $result;
        }

        $full_a = strtolower(implode(' ', $tokens_a));
        $full_b = strtolower(implode(' ', $tokens_b));
        return strcmp($full_a, $full_b);
    });
    return $posts;
}

/**
 * Sort by first name (first word of the normalized title). Tie-breaker is the
 * full normalized title so "Ahmed Bailey" sorts before "Ahmed Gray".
 */
function sort_posts_by_first_name($posts) {
    usort($posts, function ($a, $b) {
        $tokens_a = directory_normalize_name_tokens($a->post_title);
        $tokens_b = directory_normalize_name_tokens($b->post_title);

        $first_a = strtolower($tokens_a[0] ?? '');
        $first_b = strtolower($tokens_b[0] ?? '');
        $result = strcmp($first_a, $first_b);
        if ($result !== 0) {
            return $result;
        }

        $full_a = strtolower(implode(' ', $tokens_a));
        $full_b = strtolower(implode(' ', $tokens_b));
        return strcmp($full_a, $full_b);
    });
    return $posts;
}


/**
 * Pin posts with a positive integer _person_order_index to the top, in
 * ascending order. The non-indexed bucket preserves its incoming order so
 * the caller's Sort By choice (first name or last name) is respected for
 * the rest.
 */
function pin_posts_by_order_index($posts) {
    $posts_with_index = array();
    $posts_without_index = array();
    
    foreach ($posts as $post) {
        $order_index = get_post_meta($post->ID, '_person_order_index', true);
        if (!empty($order_index) && is_numeric($order_index) && intval($order_index) > 0) {
            $posts_with_index[] = $post;
        } else {
            $posts_without_index[] = $post;
        }
    }
    
    usort($posts_with_index, function($a, $b) {
        $index_a = (int)get_post_meta($a->ID, '_person_order_index', true);
        $index_b = (int)get_post_meta($b->ID, '_person_order_index', true);
        return $index_a - $index_b;
    });

    return array_merge($posts_with_index, $posts_without_index);
}
?>
