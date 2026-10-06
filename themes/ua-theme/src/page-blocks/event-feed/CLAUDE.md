# Event Feed Block

Pulls events from the [Localist API](https://calendar.ua.edu/api/2) and renders them as a filterable grid. The block is **fully server-rendered** — `save()` returns `null` and the PHP render callback always runs on page load.

## Directory structure

```
src/page-blocks/event-feed/
├── block.json      — Attribute schema and block metadata
├── edit.js         — Gutenberg editor component (React)
├── editor.css      — Editor-only styles
├── events.json     — Fixture data (sample Localist API response for development)
└── index.js        — Block registration; imports Edit, registers with null save

inc/page-blocks/
└── event-feed.php  — PHP render callback (render_event_feed); wired via inc/register-blocks.php
```

## Architecture

The editor and the frontend are completely independent code paths that share only the block attributes persisted to the database as a JSON comment in `post_content`:

```
<!-- wp:ua-blocks/event-feed {"singleDepartment":"123", ...} /-->
```

**Editor (`edit.js`):** On mount, fetches departments, student groups, audiences, event types, and topics from the Localist API directly via browser `fetch()`. Builds the preview URL in JavaScript and renders events via `setAttributes({ events: [...] })`. All filtering logic lives in `updateFeed()`.

**Frontend (`event-feed.php`):** Reads block attributes passed by WordPress, reconstructs the Localist API URL from scratch, and calls the API via cURL. The stored `events` attribute is ignored — the PHP always fetches fresh.

**Important:** The PHP render callback must **never** read `$attributes['groupQuery']` for URL construction. See "Known pitfall" below.

## Localist API endpoints

| Purpose | Endpoint |
|---|---|
| Events | `https://calendar.ua.edu/api/2/events` |
| Departments | `https://calendar.ua.edu/api/2/departments?pp=100` |
| Student groups | `https://calendar.ua.edu/api/2/groups?pp=100` |
| Audiences / event types / topics | `https://calendar.ua.edu/api/2/events/filters/` |

Departments require two pages (`page=1` and `page=2`, 100 per page). Groups require iterating `page.total` pages.

## Block attributes: configuration vs. cached data

The schema (`block.json`) includes two categories of attributes:

**Configuration attributes** — saved by the user, read by the PHP render callback:
`matchAll`, `departmentOrGroup`, `singleDepartment`, `singleGroup`, `multipleDepartments`, `multipleStudentGroups`, `audience`, `eventType`, `topic`, `dateRange`, `eventNum`, `eventsHeading`, `calendarURL`, `maxColumns`, `textAlignment`, `align`

**Cached editor data** — populated from the Localist API at editor load, used only for dropdowns and preview, **ignored by PHP**:
`audiences`, `departments`, `departmentNames`, `departmentURLs`, `departmentValues`, `eventTypes`, `events`, `studentGroupNames`, `studentGroups`, `studentGroupURLs`, `studentGroupValues`, `topics`

> **Known architectural debt:** The cached editor data attributes bloat `post_content` by up to ~400–500 KB per block (50 events × ~8–10 KB of API response JSON). The right fix is to move all of these to `useState` in `edit.js` so they are never persisted to the database. See the `groupQuery` pitfall below for why this matters.

## Known pitfall: `groupQuery` attribute and HTML entity encoding

### Root cause

When a **subsite administrator** (not a network super admin) saves a post via the REST API in a WordPress multisite install, WordPress applies `wp_kses_post()` to `post_content`. This HTML-encodes `&` characters to `&amp;` inside the block comment JSON.

`groupQuery` is a pre-built URL fragment containing literal `&` characters (e.g. `&group_id[]=123`). After `wp_kses_post()`, it is stored as `&amp;group_id[]=123` in the database. When the PHP render callback reads this string and appends it to the cURL URL, the Localist API receives `&amp;group_id[]=123` which it does not recognise, and returns all events unfiltered.

Single-site WordPress admins have the `unfiltered_html` capability and are not affected. This is why the bug was multisite-specific and not reproducible locally with a super admin account.

### How to confirm

```bash
# Replace 2 with the correct blog ID for the subsite
wp db query "SELECT post_content FROM wp_2_posts WHERE ID = <page_id>;"
# Look for: "groupQuery":"&amp;group_id[]=..."
```

Or via WP-CLI:
```bash
wp post get <id> --field=post_content --url=http://localhost:1000/subsite/ \
  | grep -o '"groupQuery":"[^"]*"'
```

### The fix

The PHP render callback **reconstructs** the group query from numeric ID attributes rather than reading `groupQuery` directly. Numeric IDs (e.g. `"40632032645349"`) contain no `&` characters and are unaffected by `wp_kses_post()`.

```php
// CORRECT — reconstruct from ID attributes
if ($matchAll) {
  if ($departmentOrGroup === 'department' && $attributes['singleDepartment'] !== '') {
    $groupQuery = '&group_id[]=' . $attributes['singleDepartment'];
  }
  // ...
} else {
  foreach ($attributes['multipleDepartments'] as $id) {
    $groupQuery .= '&group_id[]=' . $id;
  }
  // ...
}

// WRONG — reads encoded string
$groupQuery = $attributes['groupQuery']; // may contain &amp; on multisite
```

The same encoding affects the `match` attribute (`&require_all=true` / `&match=any`). The PHP derives this from the boolean `matchAll` attribute instead of reading `match` directly.

> **Backward compatibility note:** The `groupQuery` and `match` attributes are intentionally retained in `block.json` even though the PHP render callback no longer reads them. Removing them would cause WordPress to strip their values on the next save — harmless functionally, but a silent schema change for existing blocks. Leave them in place.

### Editor-side fix

Dropdown labels and token field suggestions also display `&amp;` instead of `&` (e.g. "Faculty &amp; Staff") because the cached option arrays stored as block attributes go through the same encoding. The fix is a `decodeEntities()` helper applied at render time:

```javascript
function decodeEntities(str) {
  return typeof str === 'string'
    ? str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"')
    : str;
}
// Applied to all SelectControl options and FormTokenField suggestions
```

The helper lives at **module scope in `edit.js`, before `export default`**. If you add a new SelectControl or FormTokenField, apply it to the `options` or `suggestions` prop the same way the existing ones do.

## Other PHP render callback notes

- `$events` is initialized to `[]` after `curl_close()` but before `count($events)`, so that a non-200 API response (e.g. 404 for an invalid group ID) shows the "no upcoming events" message instead of throwing a fatal `TypeError`.
- The `audience`, `eventType`, and `topic` type filters only append `&type[]=` to the URL when they are non-empty strings. Appending empty `type[]=` params can cause the Localist API to return unexpected results.
- `eventNum` is capped at 50 in the PHP (the API's `pp=50` limit).

## Local development

### Standard (single-site)

```bash
npm start        # starts wp-env + webpack watcher
npm run build    # production build
make build       # full build including submodule CSS (preferred)
```

Admin: http://localhost:8888 — `admin` / `password`

If `make build` fails with a missing submodule, run:
```bash
git submodule update --init --recursive
```

### Multisite (for testing the department filter bug fix)

The `.wp-env.override.json` at the project root configures wp-env for multisite. `DOMAIN_CURRENT_SITE` must match the domain stored in `wp_blogs` — on the external port (e.g. `localhost:1000`), not Docker's internal port 80.

**One-time setup after `npx wp-env start`:**

```bash
# 1. Write the Apache multisite rewrite rules (does not survive container restart)
npx wp-env run cli bash -c "cat > /var/www/html/.htaccess << 'EOF'
# BEGIN WordPress Multisite
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ \$1wp-admin/ [R=301,L]
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) \$2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ \$2 [L]
RewriteRule . index.php [L]
# END WordPress Multisite
EOF"

# 2. Create the subsite
npx wp-env run cli wp site create --slug=subsite --title="Subsite" --url=http://localhost:1000/subsite/

# 3. Create a subsite administrator (note: hyphens not allowed in username)
npx wp-env run cli wp user create subsiteadmin subsiteadmin@example.com \
  --role=administrator --user_pass=password --url=http://localhost:1000/subsite/
npx wp-env run cli wp user set-role subsiteadmin administrator --url=http://localhost:1000/subsite/

# 4. Activate the theme on the subsite (slug = directory name)
npx wp-env run cli wp theme activate ua-theme_v3 --url=http://localhost:1000/subsite/
```

**Sites:**
- Main: http://localhost:1000 — `admin` / `password`
- Subsite: http://localhost:1000/subsite — `subsiteadmin` / `password`

> **Important:** The `.htaccess` rewrite rules do not persist across `npx wp-env start` restarts. Re-run step 1 after each restart.

### Reproducing the encoding bug

To reproduce the original bug locally, log in as `subsiteadmin` (not `admin`) and save a page with the Event Feed block using a department filter. The subsite admin lacks `unfiltered_html`, so `wp_kses_post()` encodes the `&` in `groupQuery`.

To confirm encoding happened:
```bash
wp post get <id> --field=post_content --url=http://localhost:1000/subsite/ \
  | grep -o '"groupQuery":"[^"]*"'
# Bug present:  "groupQuery":"&amp;group_id[]=..."
# Bug fixed:    PHP ignores groupQuery, reconstructs from singleDepartment/multipleDepartments
```

## Test checklist

Run via WP-CLI `wp eval` or browser against a subsite admin account:

- [ ] Single department filter: frontend renders only events for that department
- [ ] Single student group filter: frontend renders only events for that group
- [ ] Multiple departments (Match All off): frontend renders combined results
- [ ] Audience filter: frontend applies `type[]=` param correctly
- [ ] Event type filter: same
- [ ] Topic filter: same
- [ ] No empty `&type[]=` params when audience/eventType/topic left unset
- [ ] `eventNum` respected (and capped at 50)
- [ ] Date range changes update event window
- [ ] `eventsHeading` text appears in `<h2>`
- [ ] Custom `calendarURL` applied to "Calendar" link
- [ ] `maxColumns` value appears as `--grid-column-count` inline style
- [ ] `textAlignment` applies `ua_align--{value}` class
- [ ] `align: wide` / `align: full` applies `alignwide` / `alignfull` class
- [ ] Non-200 API response (invalid group ID) shows "no upcoming events" message — no fatal error
- [ ] Dropdown labels with `&` (e.g. "Faculty & Staff") display correctly — not as `&amp;`
- [ ] Pages saved before this fix render correctly on the frontend without re-saving
