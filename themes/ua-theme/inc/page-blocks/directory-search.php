<?php

function render_directory_search( $attributes ) {

 $values = [
    'placeholder' => 'Search directory...',
    'buttonText'  => 'Search',
    'width'       => 100,
    'widthUnit'   => '%',
    'iconButton'  => false,
    'showLabel'   => true,
    'labelText'   => '',
    'className'   => '',
    'value'       => '',
    'align'       => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
        $values[$key] = $attributes[$key];
    }
  }
  $input_id    = wp_unique_id( 'ua-dir-search-' );
  $align       = $values['align'] !== '' ? 'align' . esc_attr( $values['align'] ) : '';

  $is_full = ( $values['widthUnit'] === '%' && $values['width'] >= 100 );
  $form_width = $is_full ? '' : ' style="width:' . $values['width'] . esc_attr( $values['widthUnit'] ) . '"';

  // Find the page assigned the "Directory Search" template.
  // Cache the permalink in a static variable so multiple blocks on the same page don't trigger multiple queries.
  // A null entry means "not found".
  static $search_action = false;
  if ( $search_action === false ) {
    $ids = get_posts( [
      'post_type'   => 'page',
      'post_status' => 'publish',
      'meta_key'    => '_wp_page_template',
      'meta_value'  => 'search-directory.php',
      'numberposts' => 1,
      'fields'      => 'ids',
    ] );
    $search_action = ! empty( $ids ) ? get_permalink( $ids[0] ) : home_url( '/' );
  }
  $action = esc_url( $search_action );

  $button_content = $values['iconButton']
    ? '<span class="fa fa-magnifying-glass" aria-hidden="true"></span><span class="ua_visually-hidden">' . esc_html( $values['buttonText'] ) . '</span>'
    : esc_html( $values['buttonText'] );  // escaped once here; $button_text is unescaped above

  return '
    <div class="ua_component_wrapper ' . $values['className'] . ' ' . $align . '">
      <form role="search" method="get" action="' . $action . '" class="wp-block-search__button-outside wp-block-search__text-button wp-block-search" '.$form_width.'>
        ' . ( $values['showLabel'] ? '<label class="wp-block-search__label" for="' . $input_id . '">' . $values['labelText'] . '</label>' : '<label class="ua_visually-hidden" for="' . $input_id . '">' . $values['labelText'] . '</label>' ) . '
        <div class="wp-block-search__inside-wrapper" style="display:flex">
          <input
            class="wp-block-search__input"
            id="' . $input_id . '"
            placeholder="' . $values['placeholder'] . '"
            value="' . $values['value'] . '"
            type="search"
            name="s"
          >
          <input type="hidden" name="post_type" value="directory" />
          <button class="wp-block-search__button wp-element-button" type="submit">' . $button_content . '</button>
        </div>
      </form>
    </div>';
}