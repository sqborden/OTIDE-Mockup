<?php

require get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'simplehtmldom' . DIRECTORY_SEPARATOR . 'HtmlDocument.php';
use simplehtmldom\HTMLDocument;
use simplehtmldom\HTMLNode;

function ua_modify_block_markup($block_content = '', $block = []) {
  $client = new HtmlDocument();
  $html = $client->load(do_shortcode($block_content));

  // check if it's a cover block
  if (isset($block['blockName']) && 'core/cover' === $block['blockName']) {
    // convert string to dom and look for the cover block container div
    $container = $html->find('.wp-block-cover', 0);

    if ($container !== null && strpos( $container, 'has-background' ) !== true) {
      $container->class .= ' has-background';
    }

    return $html->__toString();
  }

  // check if it's a media-text block
  if (isset($block['blockName']) && 'core/media-text' === $block['blockName']) {
    // convert string to dom and look for the cover block container div
    $container = $html->find('.wp-block-media-text__content', 0);

    if ($container !== null && strpos( $container, 'is-layout-flow' ) !== true) {
      $container->class .= ' is-layout-flow';
      return $html->__toString();
    } else {
      return $html->__toString();
    }
  }

  // check if it's a details block
  if (isset($block['blockName']) && 'core/details' === $block['blockName']) {
    $details = $html->find('.wp-block-details', 0);

    if ($details) {
      $node = new HtmlNode($client);
      $container = $html->createElement('div');
      $detailsContainer = $html->createElement('div');
      $newDetails = $html->createElement('details');
      $container->class = 'ua_component_wrapper ua_layout--flow';
      $detailsContainer->class = 'details-content';
      $newDetails->class = 'wp-block-details';
      $children = $details->children;

      if ($details->id) {
        $newDetails->id = $details->id;
      }

      if ($details->name) {
        $newDetails->name = $details->name;
      }

      if (array_key_exists('className', $block['attrs']) && !empty($block['attrs']['className']) ) {
        $newDetails->class .= ' ' . $block['attrs']['className'];
      }

      if (str_contains($details->class, 'alignfull')) {
        $container->class .= ' alignfull';
      } elseif (str_contains($details->class, 'alignwide')) {
        $container->class .= ' alignwide';
      }

      if (array_key_exists('showContent', $block['attrs']) && $block['attrs']['showContent']) {
        $newDetails->open = '';
      }

      foreach ($children as $child) {
        if (property_exists($child, 'tag') && $child->tag !== 'summary') {
          $detailsContainer->appendChild($child);
        } else {
          $newDetails->appendChild($child);
        }
      }

      $newDetails->appendChild($detailsContainer);
      $container->appendChild($newDetails);
      $node->appendChild($container);

      return $node->__toString();
    } else {
      return $html->__toString();
    }
  }

  return $block_content;
}
