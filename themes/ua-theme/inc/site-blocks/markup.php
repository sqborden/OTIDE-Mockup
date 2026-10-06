<?php

function render_markup($attributes) {
  $type = '';
  if( array_key_exists( 'type', $attributes ) ) {
    $type = $attributes['type'];
  }

  $hero = get_post_meta(get_the_ID(), 'hero', true);
  $sidebar = get_post_meta(get_the_ID(), 'sidebar', true);
  $sidebar_type = get_post_meta(get_the_ID(), 'sidebar_type', true);
  $value = '';

  if( $type === 'hero' && $hero ) {
    $blocks = get_post_meta(get_the_ID(), 'hero_blocks', true);

    if( $blocks ) {
      return '<header class="ua_page_header">' . apply_filters( 'the_content', do_shortcode( $blocks ) ) . '</header>';
    }
  }

  if( $type === 'sidebar' && $sidebar ) {
    $value = '<div class="ua_page_sub-nav ua_layout--standard">';
    if( $sidebar_type === 'select' && $sidebar ) {
      $sidebarMenu = get_post_meta(get_the_ID(), 'sidebar_menu', true);

      $value .=
        '<nav aria-label="Supplementary Navigation" class="ua_page_sub-nav ua_secondary-navigation" id="UA_SecondaryNav">
        <button
          aria-expanded="true"
          aria-haspopup="true"
          aria-controls="UA_SecondaryNav_List"
          class="ua_secondary-navigation_expander"
        >
        <span>Sidebar Menu</span>
        <span class="fa fa-caret-down">
          <span class="ua_visually-hidden">Open Sidebar Menu</span>
        </span>
        <span class="fa fa-xmark">
          <span class="ua_visually-hidden">Close Sidebar Menu</span>
        </span>
      </button>' .
          wp_nav_menu( array(
            'menu'            => $sidebarMenu,
            'echo'            => false,
            'container'       => false,
            'menu_class'      => 'ua_secondary-navigation_list',
            'menu_id'         => 'UA_SecondaryNav_List',
            'walker'          => new UA_Theme_Walker_Sidebar_Menu(),
            'fallback_cb'     => 'UA_Theme_Walker_Sidebar_Menu::fallback',
          ) ) .
        '</nav>';
      }

      if( $sidebar_type === 'dynamic' && $sidebar ) {
        global $post;
        $markup = '';

        if ( is_page() ) {
          $top_ancestor_id = $post->ID;
          $ancestors = get_post_ancestors( $post->ID );

          if ( ! empty( $ancestors ) ) {
            $top_ancestor_id = (int) end( $ancestors );
          }

          $descendants = wp_list_pages(
            array(
              'sort_column' => 'menu_order',
              'title_li' => '',
              'echo' => 0,
              'walker' => new UA_Theme_Walker_Dynamic_Sidebar_Menu(),
              'child_of' => $top_ancestor_id,
              'depth' => 3
            )
          );

          if ( $descendants ) {
            $top_ancestor_url = esc_url( get_permalink( $top_ancestor_id ) );
            $top_ancestor_title = esc_html( get_the_title( $top_ancestor_id ) );
            $top_ancestor_title_attr = esc_attr( get_the_title( $top_ancestor_id ) );

            $markup .=
              '<li aria-haspopup=true aria-expanded=true>
                <span>
                  <a href="' . $top_ancestor_url . '">' . $top_ancestor_title . '</a>
                  <button>
                    <span class="ua_secondary-navigation_inactive_content">
                      <span class="fa fa-caret-down" title="Expand ' . $top_ancestor_title_attr . ' menu" aria-hidden="true"></span>
                      <span class="ua_visually-hidden">Expand ' . $top_ancestor_title . ' menu</span>
                    </span>

                    <span class="ua_secondary-navigation_active_content">
                      <span class="fa fa-caret-up" title="Close ' . $top_ancestor_title_attr . ' menu" aria-hidden="false"></span>
                      <span class="ua_visually-hidden">Close ' . $top_ancestor_title . ' menu</span>
                    </span>
                  </button>
                </span>
                <ul aria-hidden="false" aria-label="submenu">' . $descendants . '</ul>
              </li>';
          } else {
            $top_ancestor_url = esc_url( get_permalink( $top_ancestor_id ) );
            $top_ancestor_title = esc_html( get_the_title( $top_ancestor_id ) );

            $markup .=
              '<li>
                <span>
                  <a href="' . $top_ancestor_url . '">' . $top_ancestor_title . '</a>
                </span>
              </li>';
          }
        }

        if ( $markup ) {
           $value .=
            '<nav aria-label="Supplementary Navigation" class="ua_page_sub-nav ua_secondary-navigation" id="UA_SecondaryNav">
              <ul class="ua_secondary-navigation_list" id="UA_SecondaryNav_List">' . $markup . '</ul>
            </nav>';
        }
      }
      $value .= '</div>';
    return $value;
  }
}
