export function ua_handlePrimaryNav() {
  const navElement = document.getElementById('UA_PrimaryNav');
  const buttons = document.querySelectorAll('#UA_PrimaryNav button');
  const submenus = document.querySelectorAll('#UA_PrimaryNav button + ul');
  const parentItems = document.querySelectorAll('#UA_PrimaryNav li.ua_menu-item-parent');

  // Create method to collapse all sub menus
  const resetItems = () => {
    navElement.style.marginBottom = 0;
    parentItems.forEach((menu) => {
      menu.setAttribute('aria-expanded', 'false');
    });
    submenus.forEach((submenu) => {
      submenu.setAttribute('aria-hidden', 'true');
    });
  };

  if (navElement) {
    parentItems.forEach((menu) => {
      menu.setAttribute('aria-expanded', false);
      menu.setAttribute('aria-haspopup', true);
    });
    buttons.forEach((button) => {
      // Enable the buttons
      button.removeAttribute('hidden');
      // Add event listener for each button
      button.addEventListener('click', (event) => {
        let submenu = event.target.nextElementSibling;
        let parent = event.target.parentElement;

        // Handle opening submenu
        if (parent.getAttribute('aria-expanded') === 'true') {
          resetItems();
        } else {
          resetItems();
          submenu.setAttribute('aria-hidden', 'false');
          parent.setAttribute('aria-expanded', 'true');
        }
      });
    });

    // Handle closing the menus on focus out
    parentItems.forEach((parent) => {
      parent.addEventListener('focusout', (event) => {
        if (parent.getAttribute('aria-expanded') === 'true') {
          // Fix for :focus-within behavior
          if (parent.contains(event.relatedTarget)) {
            return;
          }
          resetItems();
        }
      });
    });

    // Handle closing the menu on 'esc'
    document.addEventListener('keyup', (event) => {
      if (event.key === 'Escape') {
        resetItems();
      }
    });
  }
}
export function ua_handleTitleBar() {
  const titleBarElement = document.querySelector('#UA_TitleBar:not([data-minerva-initialized])');

  // Quit if title bar doesn't exist
  if (!titleBarElement) {
    return;
  }

  const searchElement = titleBarElement.querySelector('#UA_TitleBar_Search');
  const navElement = titleBarElement.querySelector('#UA_PrimaryNav');

  const expanderElement = {
    button: titleBarElement.querySelector('#UA_TitleBarExpander'),
    open: titleBarElement.querySelector('#UA_TitleBarExpander .ua_title-bar_expander_open'),
    closed: titleBarElement.querySelector('#UA_TitleBarExpander .ua_title-bar_expander_closed'),
  };

  // Create method to close title bar menu
  const closeMenu = () => {
    expanderElement.button?.setAttribute('aria-expanded', 'false');
    expanderElement.open?.setAttribute('aria-hidden', 'true');
    expanderElement.closed?.setAttribute('aria-hidden', 'false');

    navElement?.setAttribute('aria-hidden', 'true');
    searchElement?.setAttribute('aria-hidden', 'true');
  };

  // Create method to open title bar menu
  const openMenu = () => {
    expanderElement.button?.setAttribute('aria-expanded', 'true');
    expanderElement.open?.setAttribute('aria-hidden', 'false');
    expanderElement.closed?.setAttribute('aria-hidden', 'true');
    navElement?.setAttribute('aria-hidden', 'false');
    searchElement?.setAttribute('aria-hidden', 'false');
  };

  // Set initial state (for small viewports)
  searchElement?.setAttribute('aria-hidden', 'true');
  navElement?.setAttribute('aria-hidden', 'true');
  // Enable expander button
  expanderElement.button?.removeAttribute('hidden');

  // Set as expanded on larger viewports and collapsed on smaller viewports
  const mediaQuery = window.matchMedia('(min-width: 58rem)');
  const handleViewportChange = (mq) => {
    if (mq.matches) {
      openMenu();
    } else {
      closeMenu();
    }
  };
  // Initial check
  handleViewportChange(mediaQuery);
  // Listen for viewport changes
  mediaQuery.addEventListener('change', handleViewportChange);

  // Handle opening the menu
  const expanderClickHandler = () => {
    if (expanderElement.button.getAttribute('aria-expanded') === 'true') {
      closeMenu();
    } else {
      openMenu();
    }
  };
  expanderElement.button.addEventListener('click', expanderClickHandler);

  // Handle closing the sub menu when parent loses :focus-within
  // Only on smaller viewports
  const focusoutHandler = (event) => {
    if (!mediaQuery.matches && expanderElement.button?.getAttribute('aria-expanded') === 'true') {
      if (titleBarElement.contains(event.relatedTarget)) {
        return;
      }
      closeMenu();
    }
  };
  titleBarElement.addEventListener('focusout', focusoutHandler);

  // Handle closing the menu on 'esc'
  // Only on smaller viewports
  const keyupHandler = (event) => {
    if (!mediaQuery.matches && event.key === 'Escape') {
      closeMenu();
    }
  };
  document.addEventListener('keyup', keyupHandler);

  titleBarElement.setAttribute('data-minerva-initialized', 'true');

  // Define cleanup function
  ua_handleTitleBar.cleanup = () => {
    mediaQuery.removeEventListener('change', handleViewportChange);
    expanderElement.button?.removeEventListener('click', expanderClickHandler);
    titleBarElement?.removeEventListener('focusout', focusoutHandler);
    document.removeEventListener('keyup', keyupHandler);

    // Remove initialized state
    titleBarElement?.removeAttribute('data-minerva-initialized');
  };
}
export function ua_handlePageSearch({ qualifier, selector = 'p, h2, li, td, th' }) {
  const container = document.querySelector(qualifier);
  const searchInput = document.querySelector('.ua_page-search_input');
  const resultsList = document.querySelector('.ua_page-search_results');

  if (!container || !searchInput || !resultsList) {
    return;
  }

  const elements = Array.from(container.querySelectorAll(selector));
  let currentIndex = -1;

  function clearHighlights() {
    elements.forEach((el) => {
      el.innerHTML = el.innerHTML.replace(/<mark>|<\/mark>/g, '');
    });
  }

  function highlightMatches(term) {
    clearHighlights();
    if (!term) {
      return;
    }
    const regex = new RegExp(`(${term})`, 'gi');
    elements.forEach((el) => {
      if (el.textContent.match(regex)) {
        el.innerHTML = el.textContent.replace(regex, '<mark>$1</mark>');
      }
    });
  }

  function buildResults(query) {
    resultsList.innerHTML = '';

    if (!query) {
      resultsList.setAttribute('aria-expanded', 'false');
      clearHighlights();
      return;
    }

    const matches = elements.filter((el) => el.textContent.toLowerCase().includes(query.toLowerCase()));

    if (matches.length === 0) {
      resultsList.innerHTML = `<li class="no-results" role="alert">No results found</li>`;
    } else {
      matches.forEach((el) => {
        const li = document.createElement('li');
        li.textContent = el.textContent.trim().slice(0, 60) + '...';
        li.setAttribute('tabindex', '-1');
        li.addEventListener('click', () => {
          el.scrollIntoView({ behavior: 'smooth', block: 'start' });
          resultsList.setAttribute('aria-expanded', 'false');
        });
        resultsList.appendChild(li);
      });
    }

    resultsList.setAttribute('aria-expanded', 'true');
  }

  // Show results when focusing the input
  searchInput.addEventListener('focus', () => {
    const query = searchInput.value.trim();
    if (query !== '') {
      currentIndex = -1;
      highlightMatches(query);
      buildResults(query);
    }
  });

  // Optional: mobile/touch support
  searchInput.addEventListener('mousedown', () => {
    setTimeout(() => {
      const query = searchInput.value.trim();
      if (query !== '') {
        currentIndex = -1;
        highlightMatches(query);
        buildResults(query);
      }
    }, 0);
  });

  // Input changes trigger search
  searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim();
    highlightMatches(query);
    buildResults(query);
  });

  // Hide results when clicking outside
  document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
      resultsList.setAttribute('aria-expanded', 'false');
    }
  });

  // Hide results when tabbing away
  searchInput.addEventListener('blur', () => {
    setTimeout(() => {
      if (!resultsList.contains(document.activeElement)) {
        resultsList.setAttribute('aria-expanded', 'false');
      }
    }, 0);
  });

  // Keyboard navigation
  searchInput.addEventListener('keydown', (e) => {
    const items = Array.from(resultsList.querySelectorAll('li:not(.no-results)'));

    if (e.key === 'Escape') {
      resultsList.setAttribute('aria-expanded', 'false');
    }

    if (e.key === 'Tab') {
      resultsList.setAttribute('aria-expanded', 'false');
      return;
    }

    if (!items.length) {
      return;
    }

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      currentIndex = (currentIndex + 1) % items.length;
      items.forEach((el, idx) => el.classList.toggle('active', idx === currentIndex));
      items[currentIndex].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      currentIndex = (currentIndex - 1 + items.length) % items.length;
      items.forEach((el, idx) => el.classList.toggle('active', idx === currentIndex));
      items[currentIndex].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (resultsList.getAttribute('aria-expanded') === 'true' && currentIndex >= 0) {
        items[currentIndex].click();
      } else {
        const query = searchInput.value.trim();
        if (query !== '') {
          currentIndex = -1;
          highlightMatches(query);
          buildResults(query);
          container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    }
  });
}
export class UA_TagRibbon {
  constructor(element) {
    this.element = element;
    this.tagList = this.element.querySelector('.ua_tag-list');
    this.forwardButton = this.element.querySelector('.ua_tag-list_scroll-forward');
    this.backButton = this.element.querySelector('.ua_tag-list_scroll-back');
    this.expandButton = this.element.querySelector('.ua_tag-list_expand');

    this.init();
  }

  isInitialized() {
    return this.element.getAttribute('data-minerva-initialized') === 'true';
  }

  toggleExpanded() {
    const isExpanded = this.tagList.getAttribute('data-expanded') === 'true';
    this.tagList.setAttribute('data-expanded', isExpanded ? 'false' : 'true');
  }

  // Helper method to get scroll position relative to visual left/right
  getVisualScrollPosition() {
    if (!this.tagList) {
      return { position: 0, maxScroll: 0 };
    }

    const { scrollLeft, scrollWidth, clientWidth } = this.tagList;
    const isRTL = getComputedStyle(this.tagList).direction === 'rtl';
    const maxScroll = scrollWidth - clientWidth;

    let visualPosition;

    if (!isRTL) {
      // LTR: scrollLeft 0 = visual left, scrollLeft max = visual right
      visualPosition = scrollLeft;
    } else {
      // RTL: Need to normalize based on browser behavior
      if (scrollLeft <= 0) {
        // Chrome/Safari: scrollLeft 0 = visual right, negative = visual left
        visualPosition = maxScroll + scrollLeft;
      } else if (scrollLeft > maxScroll) {
        // Firefox: scrollLeft starts high, decreases as you go right
        visualPosition = scrollLeft - maxScroll;
      } else {
        // Edge/IE: scrollLeft behaves like LTR
        visualPosition = scrollLeft;
      }
    }

    return { position: visualPosition, maxScroll };
  }

  updateScrollPosition() {
    if (!this.tagList) {
      return;
    }

    const { scrollWidth, clientWidth } = this.tagList;
    const hasOverflow = scrollWidth > clientWidth;

    if (!hasOverflow) {
      // Remove the attribute if there's no overflow
      this.tagList.removeAttribute('data-scroll-position');
      return;
    }

    const { position, maxScroll } = this.getVisualScrollPosition();
    const tolerance = 1; // Small tolerance for floating point precision

    let scrollState;

    if (position <= tolerance) {
      scrollState = 'start'; // Visually at the left
    } else if (position >= maxScroll - tolerance) {
      scrollState = 'end'; // Visually at the right
    } else {
      scrollState = 'middle'; // Somewhere in between
    }

    this.tagList.setAttribute('data-scroll-position', scrollState);
  }

  // Scroll the tag list in the specified visual direction
  scrollInDirection(direction) {
    if (!this.tagList) {
      return;
    }

    const { clientWidth } = this.tagList;
    const scrollAmount = clientWidth * 0.75; // Scroll 75% of visible width
    const isRTL = getComputedStyle(this.tagList).direction === 'rtl';

    let scrollDelta;

    if (direction === 'right') {
      // Always scroll visually to the right
      scrollDelta = isRTL ? -scrollAmount : scrollAmount;
    } else {
      // Always scroll visually to the left
      scrollDelta = isRTL ? scrollAmount : -scrollAmount;
    }

    // Use smooth scrolling
    this.tagList.scrollBy({
      left: scrollDelta,
      behavior: 'smooth',
    });
  }

  scrollLeft() {
    this.scrollInDirection('left');
  }

  scrollRight() {
    this.scrollInDirection('right');
  }

  init() {
    if (this.isInitialized() || !this.element) {
      return;
    }

    if (this.expandButton) {
      console.log('Adding expand button listener');
      this.expandButton.addEventListener('click', () => this.toggleExpanded());
    }

    if (this.tagList) {
      // Add scroll event listener
      this.tagList.addEventListener('scroll', () => this.updateScrollPosition(), { passive: true });

      // Add resize observer to handle container size changes
      this.resizeObserver = new ResizeObserver(() => this.updateScrollPosition());
      this.resizeObserver.observe(this.tagList);

      // Initial check
      this.updateScrollPosition();
    }

    if (this.backButton) {
      this.backButton.addEventListener('click', () => this.scrollLeft());
    }

    if (this.forwardButton) {
      this.forwardButton.addEventListener('click', () => this.scrollRight());
    }

    // Set the component as initialized
    this.element.setAttribute('data-minerva-initialized', 'true');
  }

  cleanup() {
    if (!this.isInitialized()) {
      return;
    }
    if (this.expandButton) {
      this.expandButton.removeEventListener('click', () => this.toggleExpanded());
    }
    if (this.tagList) {
      this.tagList.removeEventListener('scroll', () => this.updateScrollPosition());
    }
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
    if (this.backButton) {
      this.backButton.removeEventListener('click', () => this.scrollLeft());
    }
    if (this.forwardButton) {
      this.forwardButton.removeEventListener('click', () => this.scrollRight());
    }
  }
}

export function ua_handleTagRibbon() {
  const tagRibbons = document.querySelectorAll('.ua_tag-list_ribbon');
  for (const ribbon of tagRibbons) {
    new UA_TagRibbon(ribbon);
  }
}
