import {
  ua_handlePrimaryNav,
  ua_handleTitleBar,
  ua_handleTagRibbon,
} from './minerva-3.7.3.js';

// REVIEW: What is this export used for?
export {
  ua_handlePageSearch
} from './minerva-3.7.3.js';

function ua_handleVideoCovers() {
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    return;
  }
  const containers = document.querySelectorAll(
    ".wp-block-cover:has(.wp-block-cover__video-background)"
  );
  if (containers.length === 0) {
    return;
  }

  // Add play/pause buttons to each video cover
  containers.forEach((container) => {
    const button = document.createElement("button");
    button.className = "ua_video-cover_play-pause-btn";
    const icon = document.createElement("span");
    icon.className = "fa-solid fa-pause";
    button.appendChild(icon);
    container.appendChild(button);
    const video = container.querySelector(".wp-block-cover__video-background");
    video.setAttribute("data-ua-initialized", "true");
  });

  function ua_toggleVideo(video, icon) {
    if (video.paused) {
      video.play().catch((error) => {
        console.warn("Play prevented:", error);
        icon.className = "fa-solid fa-play";
      });
      icon.className = "fa-solid fa-pause";
    } else {
      video.pause();
      icon.className = "fa-solid fa-play";
    }
  }

  document.addEventListener("click", function (e) {
    const button = e.target.closest(".ua_video-cover_play-pause-btn");
    if (button) {
      const icon = button.querySelector("span");
      const video = button
        .closest(".wp-block-cover")
        .querySelector(".wp-block-cover__video-background");
      if (video) {
        ua_toggleVideo(video, icon);
      }
    }
  });
}

// REVIEW: Why is this needed?
function ua_handleAutoPlayVideoControls() {
  const videos = document.querySelectorAll('.wp-block-video video');

  videos.forEach(video => {
    video.addEventListener('click', () => {
      video.paused ? video.play() : video.pause();
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  ua_handleAutoPlayVideoControls();
  ua_handleVideoCovers();
  ua_handlePrimaryNav();
  ua_handleSecondaryNav();
  ua_handleTitleBar();
  ua_handleTagRibbon();
});

function ua_handleSecondaryNav() {
  const navElement = document.getElementById('UA_SecondaryNav');
  const navList = navElement ? navElement.querySelector('#UA_SecondaryNav_List, .ua_secondary-navigation_list') : null;
  const buttons = navList ? navList.querySelectorAll('button') : [];
  const parentItems = navList ? navList.querySelectorAll('li[aria-haspopup]') : [];
  const navExpander = navElement ? navElement.querySelector('.ua_secondary-navigation_expander') : null;

  const mediaQuery = window.matchMedia('(min-width: 77rem)');
  function handleMediaQueryChange(e) {
    if (e.matches) {
      navExpander.setAttribute('aria-expanded', 'true');
    } else {
      navExpander.setAttribute('aria-expanded', 'false');
    }
  }

  if (navElement) {
    if (parentItems) {
      parentItems.forEach((parent) => {
        // Get the first active child of the parent
        const firstActiveChild = parent.querySelector('a[aria-current="true"]');
        // Collapse parent if it doesn't have an active child
        if (!firstActiveChild) {
          parent.setAttribute('aria-expanded', 'false');
        }
        // Collapse parent if the parent itself is active
        if (firstActiveChild && firstActiveChild.parentElement.parentElement === parent) {
          parent.setAttribute('aria-expanded', 'false');
        }

        // Get list of all nested menus
        const children = parent.querySelectorAll('ul[aria-hidden]');
        children.forEach((child) => {
          // Get first active child of the nested menu
          const isActive = child.querySelector('a[aria-current="true"]');
          // Hide the menu if it doesn't include an active item
          if (!isActive) {
            child.setAttribute('aria-hidden', true);
          }
        });
      });
    }

    if (navExpander) {
      // Add event listener for nav expander
      navExpander.addEventListener('click', () => {
        const isExpanded = navExpander.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
          navExpander.setAttribute('aria-expanded', 'false');
        } else {
          navExpander.setAttribute('aria-expanded', 'true');
        }
      });

      // Auto expand nav on desktop
      mediaQuery.addEventListener('change', handleMediaQueryChange);
      handleMediaQueryChange(mediaQuery);
    }

    buttons.forEach((button) => {
      // Enable the buttons
      button.removeAttribute('hidden');
      // Add event listener for each button
      button.addEventListener('click', (event) => {
        const parentSpan = event.currentTarget.parentElement;
        const submenu = parentSpan ? parentSpan.nextElementSibling : null;
        const parent = parentSpan ? parentSpan.parentElement : null;

        if (!submenu || !parent) {
          return;
        }

        // Handle opening submenu
        if (parent.getAttribute('aria-expanded') === 'true') {
          submenu.setAttribute('aria-hidden', 'true');
          parent.setAttribute('aria-expanded', 'false');
        } else {
          submenu.setAttribute('aria-hidden', 'false');
          parent.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }
}
