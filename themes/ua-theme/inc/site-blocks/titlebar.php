<?php

function render_title_bar($attributes) {
  $siteTitle = get_bloginfo('name');
  if (array_key_exists('siteTitle', $attributes)) {
    $siteTitle = $attributes['siteTitle'];
  }

  $subtitle = '';
  if (array_key_exists('subtitle', $attributes)) {
    $subtitle = $attributes['subtitle'];
  }

  $subtitleURL = '';
  if (array_key_exists('subtitleURL', $attributes)) {
    $subtitleURL = $attributes['subtitleURL'];
  }

  $val =
    '<section id="UA_TitleBar" class="ua_title-bar">
      <div class="ua_title-bar_content" id="UA_TitleBar_Content">
        <div class="ua_title-bar_expander-row">
        <div class="ua_title-bar_title-group">';

          if ($subtitle != '') {
            if ($subtitleURL != '') {
              $val .=
              '<a href="' . $subtitleURL . '" class="ua_title-bar_subtitle">
                  ' . $subtitle . '
              </a>';
            }else {
              $val .= '<span class="ua_title-bar_subtitle">' . $subtitle . '</span>';
            }
          }

          $val .=
        '</div>

        <button
          type="button"
          id="UA_TitleBarExpander"
          class="ua_title-bar_expander"
          aria-expanded="false"
          aria-haspopup="true"
          aria-controls="UA_TitleBar_Search UA_PrimaryNav"
        >
          <span class="ua_title-bar_expander_closed" aria-hidden="false">
            <span class="fa fa-bars" title="Expand title bar menu" aria-hidden="false"></span>
            <span class="ua_visually-hidden">Expand title bar menu</span>
          </span>
          <span class="ua_title-bar_expander_open" aria-hidden="true">
            <span class="fa fa-xmark" title="Close title bar menu" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Close title bar menu</span>
          </span>
        </button><form id="UA_TitleSearch" action="' . get_site_url() . '" method="GET" class="ua_input-group ua_title-bar_search" role="search" aria-label="Site wide">
          <label class="ua_visually-hidden" for="UA_TitleSearch_Input">
            Search This Site
          </label>
          <input type="search" role="searchbox" name="s" id="UA_TitleSearch_Input" />
          <button type="submit">
            <span class="fa fa-magnifying-glass" title="Submit" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Submit</span>
          </button>
          </div>
        </form>
      </div>

      <nav aria-label="Primary Navigation" class="ua_primary-navigation" id="UA_PrimaryNav" aria-hidden="true">' .
        wp_nav_menu( array(
          'theme_location'  => 'main-nav',
          'walker'          => new UA_Theme_Walker_Main_Menu(),
          'fallback_cb'     => 'UA_Theme_Walker_Main_Menu::fallback',
          'echo'            => false,
          'menu_class'      => 'ua_primary-navigation_list',
          'container'       => false
        ) ) .
      '</nav>
    </section>';

  return $val;
}
