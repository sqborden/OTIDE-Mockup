<?php

function ua_prevent_theme_blocks_in_editor($allowed_block_types, $editor_context) {
  $all_block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

  if (!empty($editor_context->post)) {
    $filtered_block_types = [];
    foreach ($all_block_types as $name => $settings) {
      if (!in_array($name, UA_BLOCKS_TO_DISABLE_IN_PAGE_EDITOR)) {
        $filtered_block_types[] = $name;
      }
    }
    return $filtered_block_types;
  }
  return $allowed_block_types;
}

function ua_admin_notice_warn() {
  $themeUpdates = get_theme_updates();

  if (!empty($themeUpdates) && array_key_exists('ua-theme', $themeUpdates)) : ?>
    <div class="notice notice-warning is-dismissible">
      <p>
        There is a
        <a href="<?php echo admin_url('update-core.php'); ?>">newer version</a>
        of the UA Theme available.
      </p>
    </div>
  <?php endif;
}

function ua_theme_modify_head() {
  $analyticsType = get_option('analytics_type') ? get_option('analytics_type') : 'google_analytics';
  $googlePropertyID = get_option('google_property_id') ? get_option('google_property_id') : '';

  if (!get_theme_mod('seo_disabled')) {
    if ( has_excerpt() ) {
      echo '<meta name="description" content="' . get_the_excerpt() . '" />';
      echo '<meta property="og:description" content="' . get_the_excerpt() . '" />';
    } elseif ( get_bloginfo('description') ) {
      echo '<meta name="description" content="' . get_bloginfo('description') . '" />';
      echo '<meta property="og:description" content="' . get_bloginfo('description') . '" />';
    } else {
      echo '<meta name="description" content="" />';
      echo '<meta property="og:description" content="" />';
    }

    if ( is_single() ) {
      echo '<meta property="og:type" content="article" />';
    } else {
      echo '<meta property="og:type" content="website" />';
    }

     if ( is_home() ) {
      echo '<meta property="og:title" content="' . get_bloginfo('name') . ' | The University of Alabama" />';
    } else {
      echo '<meta property="og:title" content="' . get_the_title() . ' - ' . get_bloginfo('name') . ' | The University of Alabama" />';
    }

    if ( has_post_thumbnail() ) {
      $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium');
      if($large_image_url){
        $featuredImage = $large_image_url[0];
        echo '<meta property="og:image" content="' . $featuredImage . '" />';
        echo '<meta name="twitter:card" content="summary_large_image">';
      }else{
        echo '<meta name="twitter:card" content="summary" />';
      }
    } else {
      echo '<meta name="twitter:card" content="summary" />';
    }
  }

  echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

  // add a new filter to allow child themes to override the favicon markup
  // https://developer.wordpress.org/reference/functions/apply_filters/
  apply_filters( 'ua_theme_favicon_markup',
    '<link rel="icon" href="' . get_template_directory_uri() . '/assets/images/favicon.ico" sizes="32x32">
    <link rel="icon" href="' . get_template_directory_uri() . '/assets/images/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="' . get_template_directory_uri() . '/assets/images/apple-touch-icon.png">'
  );

  if($analyticsType === 'google_tag_manager' AND $googlePropertyID !== '') {
    echo
      "<!-- Google Tag Manager -->
      <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','" . $googlePropertyID . "');
      </script>
      <!-- End Google Tag Manager -->";
  }

  if($analyticsType === 'google_analytics' AND $googlePropertyID !== '') {
    echo
      '<!-- Google tag (gtag.js) -->
      <script async src="https://www.googletagmanager.com/gtag/js?id=' . $googlePropertyID . '"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag("js", new Date());
        gtag("config", "' . $googlePropertyID . '");
      </script>';
  }
}

function ua_theme_modify_footer() {
  $analyticsType = get_option('analytics_type') ? get_option('analytics_type') : '';
  $googlePropertyID = get_option('google_property_id') ? get_option('google_property_id') : '';

  if($analyticsType === 'google_tag_manager' AND $googlePropertyID !== '') {
    echo
      '<!-- Google Tag Manager (noscript) -->
      <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=' . $googlePropertyID . '" height="0" width="0" style="display:none;visibility:hidden"></iframe>
      </noscript>
      <!-- End Google Tag Manager (noscript) -->';
  }

  echo
    '<div class="ua_cookie-banner__container" hidden id="ua_cookie-banner__container">
      <div class="ua_cookie-banner__content">
        <p>This website uses cookies to collect information to improve your browsing experience. Please review our <a href="https://www.ua.edu/privacy">Privacy Statement</a> for more information.</p>
        <button>I understand</button>
      </div>
    </div>';
}

function ua_add_block_category($categories, $post) {
  return array_merge(
    array(
      array(
        'slug' => 'ua_page_blocks',
        'title' => __('UA Page Blocks', 'ua-theme'),
      ),
      array(
        'slug' => 'ua_site_blocks',
        'title' => __('UA Site Blocks', 'ua-theme'),
      ),
    ),
    $categories
  );
}
