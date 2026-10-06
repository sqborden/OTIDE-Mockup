<section id="UA_TitleBar" class="ua_title-bar">
  <div class="ua_title-bar_content" id="UA_TitleBar_Content">
    <div class="ua_title-bar_expander-row">
      <div class="ua_title-bar_title-group">
        <a href="<?php echo get_site_url() ?>" class="ua_title-bar_name">
          <?php echo get_bloginfo('name') ?>
        </a>
        <?php if (get_theme_mod('ua_site_subtitle') != '') : ?>
          <?php if (get_theme_mod('ua_site_subtitle_url') != '') : ?>
            <a href="<?php echo get_theme_mod('ua_site_subtitle_url') ?>" class="ua_title-bar_subtitle">
              <?php echo get_theme_mod('ua_site_subtitle') ?>
            </a>
          <?php else : ?>
            <span class="ua_title-bar_subtitle">
              <?php echo get_theme_mod('ua_site_subtitle') ?>
            </span>
          <?php endif; ?>
        <?php endif; ?>
      </div>
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
      </button>
    </div>
      <form
        id="UA_TitleBar_Search"
        action="<?php echo get_site_url() ?>"
        method="GET"
        class="ua_input-group ua_title-bar_search"
        role="search"
        aria-label="Sitewide"
        aria-hidden="true"
      >
        <label class="ua_visually-hidden" for="UA_TitleBar_Search_Input">
          Search This Site
        </label>
        <input type="search" role="searchbox" name="s" id="UA_TitleBar_Search_Input" />
        <button type="submit">
          <span class="fa fa-magnifying-glass" title="Submit" aria-hidden="true"></span>
          <span class="ua_visually-hidden">Submit</span>
        </button>
      </form>
    </div>
  <?php if (has_nav_menu('main-nav')) : ?>
    <nav aria-label="Primary Navigation" class="ua_primary-navigation" id="UA_PrimaryNav" aria-hidden="true">
      <?php wp_nav_menu( array(
        'theme_location'  => 'main-nav',
        'walker'          => new UA_Theme_Walker_Main_Menu(),
        'fallback_cb'     => 'UA_Theme_Walker_Main_Menu::fallback',
        'menu_class'      => 'ua_primary-navigation_list',
        'container'       => false
      ) ); ?>
    </nav>
    <?php endif; ?>
</section>
