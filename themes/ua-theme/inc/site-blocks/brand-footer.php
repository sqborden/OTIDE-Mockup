<?php 

function render_brandfooter() {
  return
    '<section class="ua_brand-footer">
      <div class="ua_brand-footer_content">
        <div class="ua_brand-footer_logo-copyright">
          <img src="https://assetfiles.ua.edu/brand/logos/Capstone_A-White.svg" alt="The University of Alabama Logo" />
          <p>
            <span>
              <a href="https://www.ua.edu/copyright">Copyright &copy; ' . date('Y') . '</a>
              <a href="https://www.ua.edu"> The University of Alabama</a>
            </span>
            <span>
              <a href="tel:+12053486010">(205) 348-6010</a>
            </span>
            <span>
              <a href="https://www.ua.edu/contact">Contact UA</a>
            </span>
          </p>
        </div>
        <nav aria-label="Legal Links">
          <ul class="ua_brand-footer_link-list">
            <li>
              <a href="https://accessibility.ua.edu/statement/">Accessibility</a>
            </li>
            <li>
              <a href="https://oie.ua.edu/accreditation">SACSCOC</a>
            </li>
            <li>
              <a href="https://login.ua.edu/cas/login?service=https%3a%2f%2fw.taskstream.com%2fCas%2fLogin%3fpartnerId%3dksh0hphohk">
                Taskstream
              </a>
            </li>
            <li>
              <a href="http://eop.ua.edu/">Equal Opportunity</a>
            </li>
            <li>
              <a href="http://compliance.ua.edu/privacy/dsar/">Data Access Request</a>
            </li>
            <li>
              <a href="https://www.ua.edu/disclaimer">Disclaimer</a>
            </li>
            <li>
              <a href="https://www.ua.edu/privacy">Privacy</a>
            </li>
          </ul>
        </nav>
      </div>
    </section>';
}
