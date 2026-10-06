<?php

function ua_customize_dashboard() {
  remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
  remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
  remove_meta_box('dashboard_activity', 'dashboard', 'normal');
  remove_meta_box('dashboard_primary', 'dashboard', 'side');
  remove_meta_box('dashboard_quick_press', 'dashboard', 'side');

  add_meta_box(
    'ua-theme-info-meta-box',
    __('Theme Info'),
    'ua_print_theme_info_metabox',
    'dashboard',
    'normal',
    'default'
  );
}

function ua_print_theme_info_metabox() {
  global $wp_version;
  echo
    '<p>Theme Version: ' . wp_get_theme()->get('Version') . '</p>
    <p>WordPress Version: ' . $wp_version . '</p>
    <p><a href="https://ua-public.policystat.com/policy/14671027/latest/">Web Policy</a></p>
    <p><a href="https://web.ua.edu/">Web Hub</a></p>';
}

function ua_support_admin_menu() {
  // encode fontawesome icon (https://fontawesome.com/icons/life-ring?f=classic&s=solid) for use in the admin sidebar
  $icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="white" d="M367.2 412.5C335.9 434.9 297.5 448 256 448s-79.9-13.1-111.2-35.5l58-58c15.8 8.6 34 13.5 53.3 13.5s37.4-4.9 53.3-13.5l58 58zm90.7 .8c33.8-43.4 54-98 54-157.3s-20.2-113.9-54-157.3c9-12.5 7.9-30.1-3.4-41.3S425.8 45 413.3 54C369.9 20.2 315.3 0 256 0S142.1 20.2 98.7 54c-12.5-9-30.1-7.9-41.3 3.4S45 86.2 54 98.7C20.2 142.1 0 196.7 0 256s20.2 113.9 54 157.3c-9 12.5-7.9 30.1 3.4 41.3S86.2 467 98.7 458c43.4 33.8 98 54 157.3 54s113.9-20.2 157.3-54c12.5 9 30.1 7.9 41.3-3.4s12.4-28.8 3.4-41.3zm-45.5-46.1l-58-58c8.6-15.8 13.5-34 13.5-53.3s-4.9-37.4-13.5-53.3l58-58C434.9 176.1 448 214.5 448 256s-13.1 79.9-35.5 111.2zM367.2 99.5l-58 58c-15.8-8.6-34-13.5-53.3-13.5s-37.4 4.9-53.3 13.5l-58-58C176.1 77.1 214.5 64 256 64s79.9 13.1 111.2 35.5zM157.5 309.3l-58 58C77.1 335.9 64 297.5 64 256s13.1-79.9 35.5-111.2l58 58c-8.6 15.8-13.5 34-13.5 53.3s4.9 37.4 13.5 53.3zM208 256a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z" /></svg>' );

  add_menu_page( 'Support', 'Support', 'edit_posts', 'support', 'ua_theme_render_support_page', $icon );
  add_submenu_page( 'support', 'General', 'General', 'edit_posts', 'support', 'ua_theme_render_support_page' );
  add_submenu_page( 'support', 'Bug Report', 'Bug Report', 'edit_posts', 'bug_report', 'ua_theme_render_bug_report_page' );
}

function ua_theme_render_support_page() { ?>
  <h1>General Support</h1>
  <div style="display:flex; flex-wrap: wrap;">
    <div style="margin-right:50px">
      <div class="card">
        <h2>The UA Web Forum</h2>
        <p>Available on Microsoft Teams to everyone working in any web related position at the University of Alabama. It is the best place to see announcements from the Division of Strategic Communications, see change logs for new product releases, and connect with other web professionals across campus.</p>
        <a class="button" target="_blank" href="https://teams.microsoft.com/l/team/19%3APe_W-tZXODzAlVgbr_64907G6XIM3mUUYSjkkS5oEIc1%40thread.tacv2/conversations?groupId=d0e7539c-359d-40c5-a091-ef59adb69200&tenantId=2a00728e-f0d0-40b4-a4e8-ce433f3fbca7">Join the Web Forum</a>
      </div>
      <div class="card">
        <h2>Need Help Getting Started?</h2>
        <p>WordPress offers many tutorials to get started and become familiar with the platform. Just keep in mind that the UA Theme has made several modifications and restrictions, so some things shown in the WordPress tutorials may not be available.</p>
        <p>The Division of Strategic Communications also provides both basic and advanced training on the latest version of the UA WordPress theme.</p>
        <a class="button" target="_blank" href="https://learn.wordpress.org/tutorial/how-to-create-a-post-or-page-with-the-wordpress-block-editor/">WordPress Tutorials</a>
        <a class="button" target="_blank" href="https://stratcomm.ua.edu/web-strategy/training/">Request a Training Session</a>
      </div>
      <div class="card">
        <h2>Accessibility</h2>
        <p>Accessibility is a critical pillar of UA's web presence. Please familiarize yourself with WCAG 2.2 and best practices. The Accessibility Office is also able to help.</p>
        <a class="button" target="_blank" href="https://accessibility.ua.edu/">Accessibility Office</a>
        <a class="button" target="_blank" href="https://www.w3.org/WAI/WCAG22/quickref/?versions=2.2">WCAG 2.2 Guidelines</a>
      </div>
    </div>
    <div>
      <div class="card">
        <h2>Tools & Resources</h2>
        <p>Please be sure that you're read the University of Alabama <a href="https://ua-public.policystat.com/policy/14671027/latest/">Web Policy</a>, and see our list for <a href="https://web.ua.edu/theme/#plugins">approved plugins</a>. The Division of Strategic Communications can also provide assistance regarding the <a href="https://stratcomm.ua.edu/campus-calendar/">campus calendar</a>.</p>
        <p>You may also want to upload and share files over <a href="https://alabama.app.box.com/">Box</a> instead of uploading them to WordPress, and <a href="https://uaphotos.photoshelter.com/">PhotoShelter</a> is a great place to find photos taken by the Division of Strategic Communications for university use.</p>
        <p>The <a href="https://web.ua.edu/">Web Hub</a> is a great place to find documentation, see product roadmaps, known issues, and guidelines.</p>
        <a class="button" target="_blank" href="https://ua-public.policystat.com/policy/14671027/latest/">Web Policy</a>
        <a class="button" target="_blank" href="https://web.ua.edu/theme/docs/">Documentation</a>
        <a class="button" target="_blank" href="https://web.ua.edu/theme/#plugins">Approved Plugins</a>
      </div>
      <div class="card">
        <h2>Security Tips</h2>
        <p>Keeping your site secure is important, and the consequences of a compromised site takes a lot of time and resources. To help protect your site against malicious actors, follow these recommendations:</p>
        <ol>
          <li>Do not have a user with the username "admin" or "webmaster"</li>
          <li>Before installing any 3rd party plugin, verify the plugin is on the approved plugins list located in the web hub, or verify the plugin's security by contacting the <a href="https://oit.ua.edu/">Office of Information Technology</a></li>
          <li>Always use a different password for every website you have an account on, and use a password manager such as Keeper to organize them</li>
          <li>Install the <a href="https://wordpress.org/plugins/duo-universal/">Duo plugin</a> on your site. You can get the credentials from OIT.</li>
        </ol>
        <p>Lastly, no one should be able to access the dashboard of a UA website outside the campus network unless they're on the VPN. If you notice this site can be accessed outside of these criteria, please contact OIT as soon as possible.</p>
        <a class="button" target="_blank" href="https://ua-public.policystat.com/policy/14809337/latest/">Data Security Policy</a>
        <a class="button" target="_blank" href="https://oit.ua.edu/software/cisco-anyconnect/">VPN Download</a>
        <a class="button" target="_blank" href="https://oit.ua.edu/software/keeper/">Keeper Password Manager</a>
      </div>
    </div>
  </div>
<?php }

function ua_theme_render_bug_report_page() {
  global $wp_version; ?>
  <div style="display:flex; flex-wrap:wrap">
    <div style="max-width:520px; margin-right:50px">
      <h1>Report an Issue</h1>
      <p>Please be aware that this form is for reporting bugs related to the UA Theme. Please do not use this form to submit bugs related to plugins, WordPress itself, or content. Server and hosting related issues should be directed to the Office of Information Technology.</p>
      <h2>Known Issues</h2>
      <p>A log of all known bugs and accessibility issues can be found on the Web Hub. These are pulled directly from our GitHub repositories. Be aware that the theme uses code from the Minerva Web Framework, so some issues may be reported on its repository. Please check both known issue logs before submitting a new bug report.</p>
      <a class="button" target="_blank" href="https://web.ua.edu/theme/#issues">Theme 3 Known Issues</a>
      <a class="button" target="_blank" href="https://web.ua.edu/framework/#issues">Minerva Known Issues</a>

      <h2>Submit a Bug Report</h2>
      <p><label for="email">Provide your preferred email address for contact *</label></p>
      <input name="email" id="email" class="large-text" type="text" placeholder="Limit input to 80 characters" required />

      <p><label for="description">Provide a SHORT description of the issue, think of this as an email subject line *</label></p>
      <input name="description" id="description" class="large-text" type="text" placeholder="Limit input to 80 characters" required />

      <p><label for="host">If you know where your site is hosted, please include the name of the host. This could be OIT or a third party like Pantheon</label></p>
      <input name="host" id="host" class="large-text" type="text" placeholder="Limit input to 80 characters" />

      <p><label for="problem">Indicate where this problem occurs *</label></p>
      <select name="problem" id="problem">
        <option value="wordpress-dashboard">WordPress Dashboard / Editor</option>
        <option value="front-end">Front End / Page</option>
        <option value="other">Other</option>
      </select>

      <p><label for="wcag">If this issue results in an explicit violation of WCAG 2.2 guidelines up to AA level, please indicate which criteria has been violated.</label></p>
      <textarea name="wcag" id="wcag" class="large-text" rows=4"></textarea>

      <p><label for="issue">Describe the issue and the expected behavior *</label></p>
      <textarea name="issue" id="issue" class="large-text" rows=4" required></textarea>

      <p><label for="reproduce">Provide clear steps to reproduce the issue, including any required assistive technology *</label></p>
      <textarea name="reproduce" id="reproduce" class="large-text" rows=4" required></textarea>

      <p><label for="screenshots">Upload any screenshots or video of the issue to any cloud storage service and share the links here. Make sure access to the shared link is public so we can see them.</label></p>
      <textarea name="screenshots" id="screenshots" class="large-text" rows=4"></textarea>

      <p><label for="additional">Please provide any additional information or context that may help us reproduce, fix, or understand the issue.</label></p>
      <textarea name="additional" id="additional" class="large-text" rows=4"></textarea>

      <p><strong>Please download and email this completed bug report to web@ua.edu</strong></p>
      <p><button id="download-bug-report" class="button">Download Bug Report</button></p>
    </div>
    <div class="card" id="diagnostic">
      <h2>Diagnostic Data</h2>
      <p>This information will be included in your bug report automatically, but can be downloaded independently if needed. If possible and applicable, please adjust your browser size so that you can see the bug you're reporting.</p>
      <button id="diagnostic-download" class="button">Download</button>

      <h3>Site Information</h3>
      <table class="widefat striped">
        <tbody>
          <tr>
            <td>Site URL:</td>
            <td><?php echo get_site_url(); ?></td>
          </tr>
          <tr>
            <td>Theme:</td>
            <td><?php echo wp_get_theme()->Name . ' ' . wp_get_theme()->Version; ?></td>
          </tr>
          <tr>
            <td>Parent Theme:</td>
            <td>
              <?php
                if( is_child_theme() ) {
                  echo wp_get_theme()->parent()->Name . ' ' . wp_get_theme()->parent()->Version;
                } else {
                  echo 'N/A';
                }
              ?>
            </td>
          </tr>
          <tr>
            <td>Wordpress Version:</td>
            <td><?php echo $wp_version; ?></td>
          </tr>
          <tr>
            <td>PHP Version:</td>
            <td><?php echo phpversion(); ?></td>
          </tr>
          <tr>
            <td>Multisite?</td>
            <td>
              <?php
                if( is_multisite() ) {
                  echo 'Yes';
                } else {
                  echo 'No';
                }
              ?>
            </td>
          </tr>
          <tr>
            <td>Custom CSS?</td>
            <td>
              <?php
                if(wp_get_custom_css()) {
                  echo 'Yes';
                } else {
                  echo 'No';
                }
              ?>
            </td>
          </tr>
        </tbody>
      </table>

      <h3>Browser Information</h3>
      <table class="widefat striped">
        <tbody>
          <tr>
            <td>Operating system:</td>
            <td>
              <?php
                if(getBrowser()['name'] == 'Firefox') {
                  preg_match( '/\;([^()]+)\;/i', $_SERVER['HTTP_USER_AGENT'], $match);
                } else {
                  preg_match( '/\;([^()]+)\)/i', $_SERVER['HTTP_USER_AGENT'], $match);
                }
                if($match) {
                  echo str_replace( '_', '.', str_replace( array('(', ')', '; ', ';', 'Intel '), '', $match[0]));
                }
              ?>
            </td>
          </tr>
          <tr>
            <td>Browser:</td>
            <td><?php echo getBrowser()['name'] . ' ' . getBrowser()['version']; ?></td>
          </tr>
          <tr>
            <td>Javascript enabled?</td>
            <td id="javascript-enabled">
              <script>document.getElementById('javascript-enabled').innerText = 'Yes'; </script>
              <noscript>No</noscript>
            </td>
          </tr>
          <tr>
            <td>Cookies enabled?</td>
            <td id="cookies-enabled">
              <script>
                if (navigator.cookieEnabled) {
                  document.getElementById('cookies-enabled').innerText = 'Yes';
                } else {
                  document.getElementById('cookies-enabled').innerText = 'No';
                }
              </script>
              <noscript>No</noscript>
            </td>
          </tr>
          <tr>
            <td>Browser Size:</td>
            <td id="browser-size">
              <script>document.getElementById('browser-size').innerText = window.innerWidth + ' x ' + window.innerHeight;</script>
              <noscript>Javascript disabled</noscript>
            </td>
          </tr>
        </tbody>
      </table>

      <p>
        <span><strong>Browser user agent string:</strong></span><br>
        <span id="user-agent"><?php echo $_SERVER['HTTP_USER_AGENT']; ?></span>
      </p>

      <h3>Plugin List</h3>
      <table class="widefat striped">
        <tbody>
          <thead>
            <td><strong>Name</strong></td>
            <td><strong>Version</strong></td>
            <td><strong>Status</strong></td>
          </thead>
          <?php
            $plugins = get_plugins();
            $active_plugins = get_option('active_plugins');
            $active_plugins_data = array();

            foreach($active_plugins as $active_plugin) {
              $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $active_plugin, false );
              $active_plugins_data[] = $plugin_data;
            }

            foreach($plugins as $plugin) {
              echo '
                <tr>
                  <td>
                    <a href="' . $plugin['PluginURI'] . '">' . $plugin['Name'] . '</a>
                  </td>
                  <td>' . $plugin['Version'] . '</td>
                  <td>' . ( in_array($plugin, $active_plugins_data) ? 'Active' : 'Inactive' ) . '</td>
                </tr>
              ';
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>


  <script>
    const diagnosticButton = document.querySelector("#diagnostic-download");
    const downloadButton = document.querySelector("#download-bug-report");
    const container = document.querySelector("#diagnostic");
    const userAgent = document.querySelector("#user-agent");
    const cells = document.querySelectorAll("td");
    const email = document.querySelector("#email");
    const description = document.querySelector("#description");
    const host = document.querySelector("#host");
    const problem = document.querySelector("#problem");
    const wcag = document.querySelector("#wcag");
    const issue = document.querySelector("#issue");
    const reproduce = document.querySelector("#reproduce");
    const screenshots = document.querySelector("#screenshots");
    const additional = document.querySelector("#additional");

    downloadButton.addEventListener("click", () => {
      let requiredFields = [];
      if(email.value == '') {
        requiredFields.push('email');
      }
      if(description.value == '') {
        requiredFields.push('short description');
      }
      if(issue.value == '') {
        requiredFields.push('issue and expected behavior');
      }
      if(reproduce.value == '') {
        requiredFields.push('steps to reproduce');
      }

      if(requiredFields.length) {
        alert("Before downloading your bug report, please fill out the following required fields: " + requiredFields.join(', ') + '.');
      } else {
        let value = 'Bug Report: ' + description.value + "\r\n" + '----------------------' + '\r\n';

        if(email.value !== '') {
          value += "**Reported by:** " + email.value + "\r\n\r\n";
        }

        if(issue.value !== '') {
          value += "## Describe the Issue and the Expected Behavior\r\n" + issue.value + "\r\n\r\n";
        }

        if(wcag.value !== '') {
          value += "## WCAG violation\r\n" + wcag.value + "\r\n\r\n";
        }

        if(reproduce.value !== '') {
          value += "## How to reproduce\r\n" + reproduce.value + "\r\n\r\n";
        }

        if(problem.value !== '') {
          value += "### Where the problem occurs\r\n" + problem.value + "\r\n\r\n";
        }

        if(host.value !== '') {
          value += "### Hosting Environment\r\n" + host.value + "\r\n\r\n";
        }

        if(screenshots.value !== '') {
          value += "### Screenshots\r\n" + screenshots.value + "\r\n\r\n";
        }

        if(additional.value !== '') {
          value += "## Additional Information\r\n" + additional.value + "\r\n\r\n";
        }

        value += getDiagnosticData(cells);

        let blobdtMIME = new Blob( [value], { type: "text/plain" } );
        let url = URL.createObjectURL(blobdtMIME);
        let a = document.createElement("a");
        a.setAttribute("download", "bug-report.txt");
        a.href = url;
        a.click();
      }
    })

    diagnosticButton.addEventListener("click", () => {
      let blobdtMIME = new Blob( [getDiagnosticData(cells)], { type: "text/plain" } );
      let url = URL.createObjectURL(blobdtMIME);
      let a = document.createElement("a");
      a.setAttribute("download", "diagnostic.txt");
      a.href = url;
      a.click();
    })

    function getDiagnosticData(cells) {
      let valueinput = "## Diagnostic Info\r\n\r\n";
      let count = 1;

      Array.from(cells).forEach((cell) => {
        if(cell.innerText !== 'Name' && cell.innerText !== 'Version' && cell.innerText !== 'Status') {
          if(count === 1) {
            valueinput += "| Property | Value |\r\n"
            valueinput += "| --- | --- |\r\n";
          }

          if(cell.nextElementSibling) {
            valueinput += "| " + cell.innerText;
          } else {
            valueinput += " | " + cell.innerText + " |\r\n";
          }

          count++;
        }

        if(cell.innerText === 'Version') {
          valueinput += "\r\n### User Agent String" + "\r\n\r\n" + userAgent.innerText + "\r\n";
          valueinput += "\r\n" + "### Plugin List" + "\r\n\r\n";
          valueinput += "| Name | Version | Status |\r\n";
          valueinput += "| --- | --- | --- |\r\n";
        }
      });

      return valueinput;
    }
  </script>
<?php }
