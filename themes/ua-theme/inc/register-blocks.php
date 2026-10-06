<?php

require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'brandbar.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'brand-footer.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'data.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'markup.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'page-search.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'site-footer.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'site-blocks' . DIRECTORY_SEPARATOR . 'titlebar.php';

require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'breadcrumbs.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'callout.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'card.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'contact-card.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'data-highlight.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'directory-feed.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'directory-search.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'event.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'event-feed.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'lead-text.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'link-box.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'link-list.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'link-list-item.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'minerva-grid.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'minerva-grid-item.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'steps.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'step.php';
require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'page-blocks' . DIRECTORY_SEPARATOR . 'tag-list.php';

function register_ua_theme_blocks() {
  $site_blocks = array(
    'brandbar',
    'title-bar',
    'brandfooter',
    'site-footer',
    'data',
    'markup',
    'page-search'
  );

  $page_blocks = [
    'breadcrumbs',
    'callout',
    'card',
    'contact-card',
    'data-highlight',
    'directory-feed',
    'directory-search',
    'event',
    'event-feed',
    'lead-text',
    'link-box',
    'link-list',
    'link-list-item',
    'minerva-grid',
    'minerva-grid-item',
    'steps',
    'step',
    'tag-list'
  ];

  $blocks = array_merge($site_blocks, $page_blocks);

  foreach ($blocks as $block) {
    $block_underscore_name = str_replace('-', '_', $block);

    if (in_array($block, $site_blocks)) {
      $block_json_file = get_template_directory() . '/src/site-blocks/' . $block;
      register_block_type($block_json_file, array(
        'render_callback' => 'render_' . $block_underscore_name,
      ));
    } else {
      $block_json_file = get_template_directory() . '/src/page-blocks/' . $block;
      register_block_type($block_json_file, array(
        'render_callback' => 'render_' . $block_underscore_name,
      ));
    }
  }
/*
  $block_json_file = get_template_directory() . '/src/page-blocks/breadcrumbs';
  register_block_type($block_json_file);  */
}
