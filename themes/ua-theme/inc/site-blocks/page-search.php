<?php

function render_page_search($attributes, $content) {
  $values = [
    'qualifier' => '.ua_page_content',
    'selector' => 'p, h2, h3, h4, h5, h6, li, td, th, blockquote, figcaption',
    'className' => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  $val =
    '<div class="ua_component_wrapper ' . $values['className'] . '">
      <div class="ua_page-search">
        <label
          class="ua_page-search_label"
          for="ua_page-search_input"
        >
          Search this page
        </label>
        <input
          id="ua_page-search_input"
          class="ua_page-search_input"
          type="search"
          role="combobox"
          auto-complete="off"
          aria-expanded="false"
          aria-label="Search this page"
        />
        <ul class="ua_page-search_results" role="listbox" aria-label="Search Results" aria-live="polite"></ul>
      </div>
    </div>
    <script type="module">
      import { ua_handlePageSearch } from "' . get_theme_file_uri( 'assets/scripts/ua-theme-' . UA_THEME_VERSION . '.js' ) . '";

      ua_handlePageSearch({
        qualifier: "' . $values['qualifier'] . '",
        selector: "' . $values['selector'] . '"
      });
    </script>';

  return $val;
}
