<?php
if (!defined('ABSPATH')) { exit; }

const UA_NOINDEX_META_KEY = '_ua_noindex';

function ua_noindex_register_meta() {
    register_post_meta('', UA_NOINDEX_META_KEY, array(
        'single' => true,
        'type' => 'boolean',
        'show_in_rest' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        },
    ));
}

add_action('init', 'ua_noindex_register_meta');

function ua_noindex_metabox() {
    $post_types = apply_filters('ua_noindex_post_types', array(
        'post', 'page')
    );
    foreach ($post_types as $pt) {
        add_meta_box(
            'ua_noindex_metabox',
            __('Search Indexing', 'ua-per-page-noindex'),
            'ua_noindex_metabox_callback',
            $pt,
            'side',
            'default'
        );
    }
}

function ua_noindex_metabox_callback($post) {
    $value = get_post_meta(
        $post->ID,
        UA_NOINDEX_META_KEY,
        true
    );
    wp_nonce_field('ua_noindex_save', 'ua_noindex_nonce');
    ?>
    <p><?php echo esc_html__('Optionally discourage search engines from indexing this content.', 'ua-per-page-noindex') ?></p>
    <label style="display:block; margin-top:8px;">
        <input type="checkbox" name="ua_noindex" value="1" <?php checked( $value, true ); ?> />
        <?php echo esc_html__('Discourage indexing (noindex)', 'ua-per-page-noindex'); ?>
    </label>
    <?php
}

add_action( 'add_meta_boxes', 'ua_noindex_metabox' );

function ua_noindex_save($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (wp_is_post_revision($post_id)) { return; }
    if (!isset($_POST['ua_noindex_nonce']) || !wp_verify_nonce($_POST['ua_noindex_nonce'], 'ua_noindex_save')) { return; }

    $post_type = get_post_type($post_id);
    if ($post_type && !current_user_can('edit_' . $post_type, $post_id)) {
        return;
    }

    $new_value = isset($_POST['ua_noindex']) ? true : false;
    if ($new_value) {
        update_post_meta($post_id, UA_NOINDEX_META_KEY, true);
    } else {
        delete_post_meta($post_id, UA_NOINDEX_META_KEY);
    }
}

add_action('save_post', 'ua_noindex_save', 10, 2);

function ua_noindex_robots($robots) {
    if (is_admin()) { return $robots; }
    if (!is_singular()) { return $robots; }

    $post_id = get_queried_object_id();
    if (!$post_id) { return $robots; }

    if (get_post_meta($post_id, UA_NOINDEX_META_KEY, true)) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
    }
    return $robots;
}

add_filter('wp_robots', 'ua_noindex_robots');

function ua_noindex_headers() {
    if (is_admin()) { return; }
    if (!is_singular()) { return; }
    $post_id = get_queried_object_id();
    if (!$post_id) { return; }
    if (get_post_meta($post_id, UA_NOINDEX_META_KEY, true)) {
        if (!headers_sent()) {
            header('X-Robots-Tag: noindex,nofollow', true);
        }
    }
}

add_action('send_headers', 'ua_noindex_headers');

function ua_noindex_rest_dispatch($result, $server, $request) {
    $params = $request->get_params();
    $post_id = !empty($params['id']) ? absint($params['id']) : 0;
    if ($post_id && get_post_meta($post_id, UA_NOINDEX_META_KEY, true)) {
        if (is_array($result) && isset($result['headers'])) {
            $result['headers']['X-Robots-Tag'] = 'noindex,nofollow';
        }
    }
    return $result;
}

add_filter('rest_post_dispatch', 'ua_noindex_rest_dispatch', 10, 3);
