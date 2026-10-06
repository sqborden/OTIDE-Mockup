<?php 

function render_site_footer($attributes, $content) {
  return
    '<section class="ua_site-footer">
      <div class="ua_site-footer_container">
        <div class="ua_site-footer_content">
          ' . $content . '
        </div>
        <div class="ua_site-footer_logos">
          <img
            class="ua_site-footer_ua-systems"
            alt="Part of the University of Alabama System"
            src="https://assetfiles.ua.edu/brand/logos/UA_System.svg"
          />
          <img
            class="ua_site-footer_denny-chimes"
            alt="Illustration of Denny Chimes"
            src="https://assetfiles.ua.edu/brand/logos/Denny_Chimes-Crimson.svg"
          />
        </div>
      </div>
    </section>';
  }