<?php 
    get_header();

    if ( have_posts() ) {

        $i = 0;

        while ( have_posts() ) {
            $i++;
            if ( $i > 1 ) {
                echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
            }
            the_post();

            the_content();
        }
    }

    get_footer(); 
?>