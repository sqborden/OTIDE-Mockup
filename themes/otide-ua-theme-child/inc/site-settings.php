<?php

function ua_site_settings() {
  $user = wp_get_current_user();

  // Check if the current user is an Editor
  if (in_array('editor', (array) $user->roles)) {
    // They're an editor, so grant the edit_theme_options capability if they don't have it
    if (!current_user_can('edit_theme_options')) {
      $role_object = get_role('editor');
      $role_object->add_cap('edit_theme_options');
    }
  }

  // Disable the Posts and Comments admin pages
  remove_menu_page('edit.php');
  remove_menu_page('edit-comments.php');

  add_menu_page(
    'Site Settings',
    'Site Settings',
    'edit_posts',
    'site_settings',
    'print_site_settings_admin',
    'dashicons-welcome-learn-more',
    61
  );

  add_menu_page(
    'webhub_url', 
    'Web Hub', 
    'read', 
    'https://web.ua.edu/', 
    '', 
    'dashicons-text', 
    62
  );

  function print_site_settings_admin() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $siteTitle = get_option('blogname') ? stripslashes(get_option('blogname')) : '';
    $homepage = get_option('page_on_front') ? get_option('page_on_front') : "0";
    $analyticsType = get_option('analytics_type') ? get_option('analytics_type') : '';
    $googlePropertyID = get_option('google_property_id') ? get_option('google_property_id') : '';

    if (isset($_GET['status']) && $_GET['status'] === 'success') : ?>
      <div id="message" class="updated notice is-dismissible">
        <p>You have updated your site config.</p>
      </div>
    <?php endif; ?>

    <div class="wrap">
      <h1>Site Config</h1>

      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="update_site_settings" />

        <table class="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row">
                <label for="site_title">Site Title</label>
              </th>
              <td>
                <input name="site_title" type="text" class="regular-text ltr" value="<?php echo esc_attr($siteTitle); ?>" maxlength="50">
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="homepage">Site Homepage</label>
              </th>
              <td>
                <select name="homepage" class="regular-text ltr">
                  <option <?php selected($homepage, "0"); ?> value="0">None</option>
                  <?php foreach (get_pages() as $page) {
                    echo '<option ' . selected($homepage, $page->ID) . 'value="' . $page->ID . '">' . $page->post_title . '</option>';
                  } ?>
                </select>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="analytics_type">Analytics Type</label>
              </th>
              <td>
                <select name="analytics_type" class="regular-text ltr">
                  <option <?php selected($analyticsType, 'google_analytics'); ?> value="google_analytics">Google Analytics</option>
                  <option <?php selected($analyticsType, 'google_tag_manager'); ?> value="google_tag_manager">Google Tag Manager</option>
                </select>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="google_property_id">Google Property ID</label>
              </th>
              <td>
                <input name="google_property_id" type="text" class="regular-text ltr" value="<?php echo esc_attr($googlePropertyID); ?>" maxlength="40">
              </td>
            </tr>
          </tbody>
        </table>

        <p class="submit">
          <input type="submit" class="button button-primary" value="Save Changes">
        </p>
      </form>
    </div>
<?php }
}

function ua_handle_site_settings() {
  // if our current user can't edit posts, bail
  if (!current_user_can('edit_posts')) return;

  if (isset($_POST['site_title'])) {
    update_option('blogname', stripslashes(sanitize_text_field($_POST['site_title'])), TRUE);
  }

  if (isset($_POST['homepage'])) {
    update_option('page_on_front', (int) $_POST['homepage'], TRUE);

    if( $_POST['homepage'] == '0') {
      update_option('show_on_front', 'posts', TRUE);
    } else {
      update_option('show_on_front', 'page', TRUE);
    }
    
  }

  if (isset($_POST['analytics_type'])) {
    update_option('analytics_type', sanitize_text_field($_POST['analytics_type']), TRUE);
  }

  if (isset($_POST['google_property_id'])) {
    update_option('google_property_id', sanitize_text_field($_POST['google_property_id']), TRUE);
  }

  // Redirect back to settings page
  $redirect_url = get_bloginfo('url') . '/wp-admin/admin.php?page=site_settings&status=success';
  header('Location: ' . $redirect_url);
  exit;
}
