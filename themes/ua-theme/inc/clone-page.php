<?php

function ua_add_clone_page_link($actions, $post) {
  $title = _draft_or_post_title($post);

  if (current_user_can('edit_posts')) {
    $url = wp_nonce_url(
      admin_url('admin.php?action=ua_clone_page&amp;post=' . $post->ID),
      'clone-post_' . $post->ID
    );

    $actions['clone'] =
      '<a
        href="' . $url . '"
        aria-label="' . esc_attr(sprintf(__('Clone &#8220;%s&#8221;', 'ua-theme'), $title)) . '"
      >' .
      esc_html_x('Clone', 'verb', 'ua-theme') .
      '</a>';
  }

  return $actions;
}

function ua_clone_page() {
  if (!current_user_can('edit_posts')) {
    wp_die(esc_html__('Current user is not allowed to clone posts.', 'ua-theme'));
  }

  if (!(isset($_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && 'ua_clone_page' === $_REQUEST['action']))) {
    wp_die(esc_html__('No post to duplicate has been supplied.', 'ua-theme'));
  }

  $id = $_GET['post'];
  $post = get_post($id);

  check_admin_referer('clone-post_' . $id);

  $new_post_author = wp_get_current_user();
  $new_post_author_id = $new_post_author->ID;

  if (isset($post) && $post != null) :
    $new_post = array(
      'menu_order' => $post->menu_order,
      'comment_status' => $post->comment_status,
      'ping_status' => $post->ping_status,
      'post_author' => $new_post_author_id,
      'post_content' => $post->post_content,
      'post_content_filtered' => $post->post_content_filtered,
      'post_excerpt' => $post->post_excerpt,
      'post_mime_type' => $post->post_mime_type,
      'post_parent' => $post->post_parent,
      'post_status' => 'draft',
      'post_title' => $post->post_title . ' Clone',
      'post_type' => $post->post_type,
      'post_name' => $post->post_name,
    );

    $new_post_id = wp_insert_post(wp_slash($new_post));

    wp_update_post(wp_slash(
      array(
        'ID' => $new_post_id,
        'post_name' => get_post_field('post_name', $new_post_id) . '-' . $new_post_id
      )
    ));

    wp_redirect(add_query_arg(array('cloned' => 1, 'ids' => $post->ID), admin_url('edit.php?post_type=' . $post->post_type)));
  else :
    wp_die(esc_html__('Copy creation failed. Could not find original:', 'ua-theme') . ' ' . htmlspecialchars($id));
  endif;
}
