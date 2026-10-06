# Directory Feed Block

## Overview

Displays a filterable, sortable list or grid of `directory` custom post type entries. The block has **two parallel implementations that must stay in sync**:

- **Editor (`edit.js`)** — fetches via REST API, sorts client-side, renders a live preview
- **Frontend (`inc/page-blocks/directory-feed.php`)** — fetches via WP_Query, sorts server-side, renders final HTML

Any change to sort or filter logic must be applied to **both**.

---

## Key Files

| File | Purpose |
|------|---------|
| `src/page-blocks/directory-feed/block.json` | Attribute definitions |
| `src/page-blocks/directory-feed/edit.js` | Editor React component |
| `src/page-blocks/directory-feed/index.js` | Block registration |
| `inc/page-blocks/directory-feed.php` | PHP render function + sort utilities |
| `functions.php` | Post type/taxonomy registration, REST param + query filters, REST field registration |

---

## Attributes

| Attribute | Type | Default | Notes |
|-----------|------|---------|-------|
| `headingLevel` | integer | 3 | h2–h5 |
| `textAlignment` | string | `""` | Text alignment for entry content |
| `layout` | string | `"list"` | `"list"` or `"grid"` |
| `maxColumns` | number | 4 | Grid only |
| `orderBy` | string | `"last_name"` | `"first_name"` or `"last_name"`. Legacy values `""` (was date-desc) and `"order_index"` (was a Sort By choice) are auto-migrated to `"last_name"` — see Sort Logic. |
| `orderIndexOverride` | boolean | true | Pin posts with a positive `_person_order_index` to the top of whatever Sort By produces |
| `category` | string | `""` | Comma-separated `directory_category` IDs |
| `tag` | string | `""` | Comma-separated `directory_tag` IDs |
| `taxRelation` | string | `"AND_ANY"` | Filter logic — see Filter Logic section |
| `postPerPage` | number | 100 | Items shown after offset |
| `offset` | number | 0 | Items skipped before display |
| `maxPages` | number | 0 | 0 = unlimited |
| `imageToggle` | boolean | true | Show profile image |
| `categoriesToggle` | boolean | true | Show `directory_category` term labels |
| `tagsToggle` | boolean | true | Show `directory_tag` term labels |
| `excerptToggle` | boolean | true | Show excerpt. Source is `get_the_excerpt()` — honors a manual `post_excerpt` at full length; otherwise renders WP's auto-generated 55-word fallback with `[…]`. The `directory` CPT registers `excerpt` support in `functions.php` so authors can write a manual excerpt. |
| `emailToggle` | boolean | true | Show email |
| `phoneToggle` | boolean | true | Show phone |
| `locationToggle` | boolean | true | Show location |
| `websiteToggle` | boolean | true | Show website |
| `linkToggle` | boolean | true | Make title a link to the single post |

---

## REST API

**Endpoint:** `GET /wp/v2/directory/`

**Built-in params:** `per_page`, `page`, `_embed`, `directory_category`, `directory_tag`

**`_embed=true` is always sent** in editor REST requests. This pulls taxonomy terms and the featured image URL into the single response, avoiding extra roundtrips. The `featured_image_url` REST field depends on embedded media data being present.

**Custom param — `tax_relation`:**
- Registered via `rest_directory_collection_params` filter → `register_directory_tax_relation_param()` in `functions.php`
- Enum: `["AND", "AND_ANY", "OR"]`, default `"AND_ANY"`
- Applied via `rest_directory_query` filter → `directory_rest_and_tax_query()` in `functions.php`

**`tax_relation` must always be sent explicitly in REST requests.** Do not omit it and rely on the default — the registered default may change, and omitting causes silent mismatch (see the "Always send `tax_relation` explicitly in REST requests" pitfall below).

**Custom REST fields** (all in `functions.php`):
- `location` → `_person_location` meta
- `email` → `_person_email` meta
- `phone` → `_person_phone` meta
- `website` → `_person_website` meta
- `subtitle` → `_person_subtitle` meta
- `order_index` → `_person_order_index` meta
- `featured_image_url` → featured media attachment URL

---

## Filter Logic (taxRelation)

The `taxRelation` attribute controls how multiple taxonomy filters combine. The same logic runs in PHP (`render_directory_feed` and `directory_rest_and_tax_query`).

| Value | Label | Behavior |
|-------|-------|----------|
| `AND_ANY` | Any tag AND any category (default) | Post must match ≥1 selected tag AND ≥1 selected category. One `tax_query` clause per taxonomy. |
| `AND` | All tags AND all categories | Post must have every selected tag and every selected category. One clause per individual term. |
| `OR` | Any tag OR any category | Post matches if it has ≥1 selected tag OR ≥1 selected category. One clause per taxonomy, top-level `relation = OR`. |

**Implementation detail:** `AND` and `AND_ANY` both use `top_relation = 'AND'`; the difference is whether each term gets its own clause. `OR` uses `top_relation = 'OR'`.

---

## Sort Logic

Sorting is **client-side in the editor** and **server-side in PHP**. Both implementations must produce the same order.

### Pin by Order Index (`orderIndexOverride` toggle)

- Independent of Sort By. When on, the renderer/editor applies the Sort By cascade first, then pins indexed posts to the top.
- Source: `_person_order_index` post meta (PHP) / `order_index` REST field (JS)
- Posts with a **positive integer** index → sorted numerically ascending and prepended
- Posts with 0, negative, empty, or null index → kept in their already-sorted order from the Sort By pass (last name asc or first name asc)
- PHP helper: `pin_posts_by_order_index()`. JS helper: `pinPostsByOrderIndex()`.
- **Legacy `orderBy` migration:** two legacy values get rewritten on the fly so already-published blocks keep working without a re-save. `"order_index"` (when Order Index was a Sort By choice) → `orderBy = "last_name"` + `orderIndexOverride = true`. `""` (when empty meant date-desc) → `orderBy = "last_name"`. Both the editor mount effect (`edit.js`) and the PHP renderer apply the rewrite; the editor variant persists on the next save.

### Name token normalization (shared by `first_name` and `last_name`)

Both sort modes run titles through a shared normalizer before extracting their sort key:

1. Drop everything after the first comma (handles "Jane Doe, PhD" forms).
2. Split on whitespace.
3. Peel leading tokens that match an honorific **prefix** (e.g. `Dr.`, `Prof.`, `Rev.`).
4. Peel trailing tokens that match a degree/generational **suffix** (e.g. `Jr.`, `III`, `PhD`, `M.D.`).
5. Always keep at least one token, so a single-word title like "Plato" survives.

Both lists are maintained in two places that **must stay in sync**:
- PHP: `DIRECTORY_NAME_PREFIXES` and `DIRECTORY_NAME_SUFFIXES` in `inc/page-blocks/directory-feed.php`
- JS: `NAME_PREFIXES` and `NAME_SUFFIXES` in `src/page-blocks/directory-feed/edit.js`

Comparison normalizes input by stripping `.` and `,` and lowercasing, so the lists hold canonical lowercase forms with no punctuation (`'phd'` matches `PhD`, `Ph.D.`, `PHD`, etc.).

The `Sr.` token is in both lists — leading it's the "Sister" prefix, trailing it's the "Senior" suffix. Position-based parsing handles the distinction correctly.

### last_name

- Sort key: last token of the normalized name (so "Dr. Patrick Allen Jr." sorts under "Allen").
- Tie-breaker: full normalized name, lowercased ("Patrick Allen" < "Sara Allen").
- PHP: `sort_posts_by_last_name()`. JS: `sortPostsByLastName()`.

### first_name

- Sort key: first token of the normalized name (so "Dr. John Smith" sorts under "John", not "Dr.").
- Tie-breaker: full normalized name, lowercased ("Ahmed Bailey" < "Ahmed Gray").
- PHP: `sort_posts_by_first_name()`. JS: `sortPostsByFirstName()`.

### Sort + Pagination Interaction

Every sort goes through a PHP-side pass (so prefix/suffix stripping can apply). That means the PHP render always fetches **all matching posts** with no DB-level `posts_per_page`, reorders them in PHP, then `array_slice`s using `offset` and `postPerPage`. There is no DB-pagination fast path. The editor mirrors this by fetching all REST pages before sorting client-side.

---

## Editor Pagination

`recordsApiCall()` in `edit.js` is `async` because it paginates:

1. Fetch page 1 with `parse: false` to read the `X-WP-TotalPages` response header
2. Fire parallel `wp.apiFetch` calls for all remaining pages via `Promise.all`
3. Merge all pages, sort client-side, slice by `offset`/`postPerPage`

Without `async/await`, you cannot read the header before deciding how many additional requests to make.

**`per_page=100` vs `postPerPage`:** The REST requests are hardcoded to fetch 100 posts per API page (batch size for pagination). This is independent of the `postPerPage` attribute, which controls how many entries the block actually displays after sorting and slicing. A directory with 250 posts triggers 3 API requests of 100 each; `postPerPage` then limits the visible output to whatever the editor is configured to show.

---

## Editor Lookup Maps

`tagById` and `categoryById` (built with `useMemo`) map term IDs to full term objects for O(1) lookup during render. They recompute only when `tags` or `categories` change — not on every render.

---

## Manual Test Checklist

Run through these whenever a bug is fixed or an enhancement is made. The goal is to catch regressions across filtering, sorting, display toggles, and editor/frontend parity.

### Running Tests via wp-cli

Most of the PHP-side tests can be run without a browser using `wp eval`.

**Always pass `--url=localhost:1000`** to wp-cli commands. Without it, wp-cli fails with "Site 'localhost/' not found".

```bash
# From the worktree directory:
npx wp-env start
npx wp-env run cli wp eval '...' --url=localhost:1000
```

**What can be tested via wp-cli:** filter logic (via WP_Query), sort functions, post_status exclusion, offset/slice pagination, and the PHP render function output.

**What requires the browser:** case-insensitive token field matching (editor JS/UI only), display toggles other than the category wrapper (need to inspect rendered HTML visually), editor/frontend parity (need both views open), and REST API multi-page pagination behavior in the editor.

### Seeding Test Data

The tests rely on 200 published directory posts with specific taxonomy assignments. Save the script below somewhere inside the project tree (the theme dir is mounted at `/var/www/html/wp-content/themes/ua-theme_v3` inside the container — paths under `.claude/` are gitignored and a good place for ad-hoc test scripts):

```bash
# After saving to .claude/seed.php in the project root:
npx wp-env run cli wp eval-file /var/www/html/wp-content/themes/ua-theme_v3/.claude/seed.php --url=localhost:1000
```

The script is **idempotent** — if 200 posts already exist it prints a summary and exits. To start fresh, delete all directory posts first:

```bash
npx wp-env run cli wp post delete \
  $(npx wp-env run cli wp post list --post_type=directory --format=ids --url=localhost:1000 2>/dev/null | tail -1) \
  --force --url=localhost:1000
```

**What the seed script produces** (counts from `mt_srand(42)` — deterministic but driven by random selection from name/category/tag pools, so the per-category counts don't match the planned ratios exactly when title collisions cause inserts to be skipped):

| Condition | Count |
|-----------|-------|
| Total published posts | 200 (guaranteed) |
| Fixed entries always present | Destiny Peterson, Kenneth Anderson (both Emeritus + Biology); Ahmed Bailey, Gregory Bailey, Ahmed Gray; Order Index Alpha/Beta/Gamma |
| Emeritus Faculty category | 10 posts |
| Biology tag | 16 posts (2 fixed + up to 4 from `bio_assigned` cap + any from per-post random tag picker) |
| Both Emeritus Faculty AND Biology | 3 posts (Destiny Peterson, Kenneth Anderson from the fixed set + ~1 random) |
| OR union (Emeritus + Biology) | 23 posts |
| Posts with `_person_order_index` set (1, 2, 3) | 3 posts: Order Index Alpha/Beta/Gamma |
| Tie-break test data | "Ahmed Bailey" + "Gregory Bailey" (last-name); "Ahmed Bailey" + "Ahmed Gray" (first-name) |

**Seed script:**

```php
<?php
// Directory Feed seed script — see "Seeding Test Data" above for the wp eval-file invocation.

$categories = [
    'Faculty', 'Tenure-Track Faculty', 'Non-Tenure-Track Faculty', 'Emeritus Faculty',
    'Staff', 'Administrative Staff', 'Research Staff', 'Graduate Students', 'Postdoctoral Researchers',
];
$tags = [
    'Biology', 'Chemistry', 'Physics', 'Mathematics', 'Computer Science', 'Engineering',
    'Psychology', 'Economics', 'History', 'English & Literature', 'Law', 'Education',
    'Nursing', 'Business & Commerce', 'Social Work', 'Sociology', 'Political Science',
    'Natural Sciences', 'Medicine & Health', 'Humanities', 'Data Science', 'Artificial Intelligence',
    'Environmental Studies', 'Research', 'Teaching', 'STEM', 'Arts & Design',
    'Communication Studies', 'Public Health', 'Graduate Education',
];

echo "Creating taxonomy terms...\n";
$cat_ids = [];
foreach ( $categories as $name ) {
    $t = get_term_by( 'name', $name, 'directory_category' );
    $cat_ids[ $name ] = $t ? $t->term_id : wp_insert_term( $name, 'directory_category' )['term_id'];
}
$tag_ids = [];
foreach ( $tags as $name ) {
    $t = get_term_by( 'name', $name, 'directory_tag' );
    $tag_ids[ $name ] = $t ? $t->term_id : wp_insert_term( $name, 'directory_tag' )['term_id'];
}

if ( (int) wp_count_posts( 'directory' )->publish >= 200 ) {
    echo "Already have 200 posts — skipping. Delete first to re-seed:\n";
    echo "  wp post delete \$(wp post list --post_type=directory --format=ids) --force\n";
} else {
    $first_names = ['Ahmed','Aisha','Amanda','Angela','Barbara','Brandon','Carol','Carlos',
        'Charlotte','Chioma','Christopher','Daniel','David','Destiny','Edward','Elena',
        'Elizabeth','Emeka','Fatima','Frank','Gabriela','Gregory','Heather','Hiroshi',
        'Ingrid','James','Janet','Jason','Jennifer','Jessica','John','Jonathan','Joseph',
        'Joshua','Justin','Kenneth','Kevin','Kimberly','Kofi','Lauren','Linda','Lisa',
        'Marcus','Maria','Megan','Melissa','Michael','Monique','Natasha','Nicholas',
        'Nicole','Omar','Patricia','Patrick','Paul','Priya','Rachel','Raymond','Richard',
        'Robert','Ronald','Samuel','Santiago','Sarah','Scott','Sofia','Soren','Stephen',
        'Stephanie','Susan','Tameka','Thomas','Timothy','Wei','William','Yuki','Zanele'];
    $last_names  = ['Adams','Allen','Anderson','Bailey','Baker','Barnes','Bell','Bennett',
        'Boyd','Brooks','Brown','Bryant','Carter','Clark','Collins','Cooper','Cox',
        'Crawford','Davis','Diaz','Ellis','Flores','Foster','Garcia','Gonzalez','Gordon',
        'Gray','Green','Gutierrez','Hall','Harris','Henderson','Hernandez','Hill','Howard',
        'Hughes','Jackson','Jefferson','Jenkins','Johnson','Jones','Kelly','Kim','Lawrence',
        'Lee','Lewis','Long','Lopez','Martin','Martinez','Miller','Mitchell','Moore',
        'Morales','Morgan','Murphy','Nguyen','Owens','Patterson','Patel','Perez','Peterson',
        'Phillips','Porter','Powell','Price','Ramirez','Reed','Reyes','Rivera','Roberts',
        'Robinson','Rodriguez','Ross','Russell','Sanders','Scott','Simmons','Smith',
        'Stewart','Taylor','Thomas','Thompson','Torres','Walker','Ward','Watson','White',
        'Williams','Wilson','Wood','Young','Zhang'];
    $locations   = ['Nott Hall 101','Farrah Hall 203','Bevill Building 305','Bidgood Hall 410','Rose Administration 112'];
    $departments = ['Department of Biology','Department of Chemistry','School of Engineering',
        'Department of Psychology','College of Business','School of Law',
        'Department of History','Department of Computer Science','College of Education',
        'School of Nursing','Department of Mathematics','Department of Physics'];

    // Fixed entries that test assertions depend on
    $fixed = [
        ['title'=>'Destiny Peterson',  'cat'=>'Emeritus Faculty',      'tags'=>['Biology','Research'],       'index'=>null],
        ['title'=>'Kenneth Anderson',  'cat'=>'Emeritus Faculty',      'tags'=>['Biology','Teaching'],       'index'=>null],
        ['title'=>'Ahmed Bailey',      'cat'=>'Faculty',               'tags'=>['Mathematics','STEM'],       'index'=>null],
        ['title'=>'Gregory Bailey',    'cat'=>'Faculty',               'tags'=>['Computer Science'],         'index'=>null],
        ['title'=>'Ahmed Gray',        'cat'=>'Tenure-Track Faculty',  'tags'=>['Physics'],                  'index'=>null],
        ['title'=>'Order Index Alpha', 'cat'=>'Staff',                 'tags'=>[],                           'index'=>1],
        ['title'=>'Order Index Beta',  'cat'=>'Staff',                 'tags'=>[],                           'index'=>2],
        ['title'=>'Order Index Gamma', 'cat'=>'Staff',                 'tags'=>[],                           'index'=>3],
    ];

    $cat_pool = array_merge(
        array_fill(0,90,'Faculty'), array_fill(0,55,'Tenure-Track Faculty'),
        array_fill(0,20,'Non-Tenure-Track Faculty'), array_fill(0,15,'Emeritus Faculty'),
        array_fill(0,60,'Staff'), array_fill(0,40,'Administrative Staff'),
        array_fill(0,20,'Research Staff'), array_fill(0,35,'Graduate Students'),
        array_fill(0,15,'Postdoctoral Researchers')
    );

    mt_srand(42);
    shuffle($cat_pool);
    $created = 0;
    $cat_index = 0;
    $bio_assigned = 0;

    $insert = function($title, $cat, $post_tags, $index) use ($cat_ids, $tag_ids, $locations, $departments) {
        if (get_page_by_title($title, OBJECT, 'directory')) return;
        $id = wp_insert_post(['post_type'=>'directory','post_title'=>$title,
            'post_status'=>'publish','post_content'=>'Bio for '.$title.'.']);
        if (!$id || is_wp_error($id)) return;
        if ($cat && isset($cat_ids[$cat])) wp_set_object_terms($id, [(int)$cat_ids[$cat]], 'directory_category');
        $tids = array_values(array_filter(array_map(fn($t)=>isset($tag_ids[$t])?(int)$tag_ids[$t]:null, $post_tags)));
        if ($tids) wp_set_object_terms($id, array_unique($tids), 'directory_tag');
        $parts = explode(' ', $title);
        update_post_meta($id, '_person_email', strtolower($parts[0].'.'.$parts[1]).'@ua.edu');
        update_post_meta($id, '_person_phone', '(205) 555-'.str_pad(mt_rand(1000,9999),4,'0'));
        update_post_meta($id, '_person_location', $locations[mt_rand(0,count($locations)-1)]);
        update_post_meta($id, '_person_subtitle', $departments[mt_rand(0,count($departments)-1)]);
        if ($index !== null) update_post_meta($id, '_person_order_index', $index);
    };

    foreach ($fixed as $e) { $insert($e['title'], $e['cat'], $e['tags'], $e['index']); $created++; }

    $tag_list = array_values($tag_ids);
    while ($created < 200) {
        $first = $first_names[mt_rand(0,count($first_names)-1)];
        $last  = $last_names[mt_rand(0,count($last_names)-1)];
        $title = $first.' '.$last;
        $cat   = $cat_pool[$cat_index % count($cat_pool)]; $cat_index++;
        $post_tags = [];
        if ($bio_assigned < 4 && mt_rand(0,10) < 2) { $post_tags[] = 'Biology'; $bio_assigned++; }
        shuffle($tag_list);
        foreach (array_slice($tag_list, 0, mt_rand(1,3)) as $tid) {
            $k = array_search($tid, $tag_ids); if ($k) $post_tags[] = $k;
        }
        $insert($title, $cat, array_unique($post_tags), null);
        $created++;
    }
    echo "Done. Created {$created} posts.\n";
}

// Summary
foreach ($cat_ids as $name => $id) {
    $n = count(get_posts(['post_type'=>'directory','numberposts'=>-1,
        'tax_query'=>[['taxonomy'=>'directory_category','field'=>'term_id','terms'=>[$id]]]]));
    echo "  {$name}: {$n}\n";
}
$bio = count(get_posts(['post_type'=>'directory','numberposts'=>-1,
    'tax_query'=>[['taxonomy'=>'directory_tag','field'=>'term_id','terms'=>[$tag_ids['Biology']]]]]));
echo "Biology tag: {$bio} posts\n";
echo "Total: ".(int)wp_count_posts('directory')->publish."\n";
```

### Filter Logic

- [ ] Select only a category (no tag) — correct entries appear in both editor and frontend
- [ ] Select only a tag (no category) — correct entries appear in both editor and frontend
- [ ] Select both a category and a tag — entries appear only if they match both (AND_ANY default)
- [ ] Select multiple categories and multiple tags — verify AND_ANY behavior (any cat AND any tag)
- [ ] Switch Filter Logic to **All tags AND all categories** — verify only entries with every selected term appear. Note: selecting two mutually exclusive categories (e.g. Faculty + Tenure-Track, where no post can have both) will correctly return 0 results — this is expected AND behavior, not a bug
- [ ] Switch Filter Logic to **Any tag OR any category** — verify entries matching either taxonomy appear
- [ ] Type a term name with wrong casing (e.g. `"teaching"` when stored as `"Teaching"`) — verify the filter resolves correctly and results update

### Sort Order

- [ ] Sort by **First Name**, Pin off — verify alphabetical by first token of the normalized name; matches frontend
- [ ] Sort by **Last Name** (default), Pin off — verify alphabetical by last token of the normalized name; matches frontend
- [ ] **Prefix/suffix stripping:** create a few entries with prefixes (`Dr. John Smith`) and suffixes (`Patrick Allen Jr.`, `Sarah Davis PhD`) and confirm they sort by the core name (John/Smith, Patrick/Allen, Sarah/Davis), not by the prefix/suffix token. Repeat for both First Name and Last Name sort. Run in editor and frontend.
- [ ] **Pin by Order Index on** with each Sort By choice — verify indexed entries appear first in ascending numeric order, and the remainder follows the chosen sort method (alphabetical-by-first/last-name), not WP-Query order
- [ ] **Pin + filter:** apply a category/tag filter that *excludes* the pinned posts and confirm they do not leak through (filtering happens before pinning). Apply a filter that *includes* them and confirm they stay pinned to the top.
- [ ] **Pin + offset:** set offset higher than the number of pinned posts and confirm the pinned posts are correctly skipped (the slice happens after pinning).
- [ ] **Legacy migration (order_index):** open a previously-saved block where serialized attrs include `"orderBy":"order_index"`. Toggle should show ON and Sort By should be Last Name. Save and confirm the saved source contains `"orderBy":"last_name"` and no longer contains `"order_index"`.
- [ ] **Legacy migration (empty orderBy):** open a previously-saved block where serialized attrs include `"orderBy":""`. Sort By should show Last Name. Save and confirm the saved source contains `"orderBy":"last_name"`.
- [ ] **Frontend back-compat:** visit pages containing legacy blocks (either form) *without* re-saving — confirm they render alphabetical-by-last-name via the PHP shim.
- [ ] Confirm editor preview and frontend render produce the same order for each matrix cell

### Pagination & Display Controls

- [ ] **Posts Per Page** — reduce to a small number (e.g. 3) and confirm only that many entries show
- [ ] **Offset** — set offset to 2 and confirm the first 2 entries are skipped
- [ ] **Offset > result count** — set offset higher than matching entries and confirm nothing displays (expected behavior, not a bug)
- [ ] **Max Pages** — set to 1 and confirm results are capped to the first page only
- [ ] **Zero matching posts** — apply a filter combination that returns no results and confirm the block renders gracefully (no PHP errors, no broken HTML)

### Display Toggles

- [ ] Toggle off **Image**, **Categories**, **Tags**, **Excerpt**, **Email**, **Phone**, **Location**, **Website**, **Link** one at a time — confirm each hides only its own element on both editor and frontend
- [ ] Confirm category labels appear by default and toggling **Categories** off removes them cleanly with no empty wrapper remaining

### Editor/Frontend Parity

**Filter parity (testable via wp-cli):** For all filter modes, `directory_rest_and_tax_query()` (REST) and the `tax_query` built in `render_directory_feed()` (PHP) have been confirmed to return identical post ID sets. Run via `wp eval` using `WP_REST_Request` to dispatch REST calls internally and compare against `get_posts()` with the equivalent `tax_query`. Confirmed for: category only, tag only, AND_ANY, OR, AND (per-term), and no filters.

**Sort parity (testable via wp-cli for working set; order comparison is JS-side only):** Confirmed that the REST API and PHP sort functions operate on the same post set. The specific ordering produced by the JS sort functions in `edit.js` cannot be verified via wp-cli — it requires the browser.

- [ ] Apply a filter combination and visually confirm the editor preview and the published frontend show the same entries
- [ ] Confirm no draft or private posts appear on the frontend when logged in as an editor or admin

---

## Pitfalls

Things that have burned us or are easy to get wrong. Add new entries here as they're discovered.

### Always send `tax_relation` explicitly in REST requests
Never omit it and rely on the registered default. The default was changed from `OR` to `AND_ANY` during development, and omitting the param caused OR to silently apply AND_ANY in the editor. (`348a239`)

### Always set `post_status => 'publish'` in WP_Query
Without it, WP_Query includes draft and private posts for logged-in users. The REST API only returns published posts, so editors would see entries on the frontend that never appeared in the editor preview. Easy to lose if the WP_Query args array is ever restructured. (`f754734`)

### Sort before slicing, never after
Every directory render fetches all matching posts (no DB-level `posts_per_page`), reorders in PHP, then `array_slice`s by `offset`/`postPerPage`. Applying DB-level pagination before reordering produces wrong results. The editor mirrors this by fetching all REST pages before sorting client-side. There is no DB-pagination fast path — all sorts go through PHP so that the prefix/suffix stripping in `directory_normalize_name_tokens()` can apply. (`890f812`)

### Editor and PHP sort logic must stay in sync
Any change to a sort function needs to be applied to both `edit.js` and `directory-feed.php`. This now includes the `NAME_PREFIXES` / `NAME_SUFFIXES` allowlists — both files hold their own copy and they must match. Past mismatches: PHP `sort_posts_by_last_name` used the third word (index 2) instead of the last word, diverging from JS `extractLastName` for names with more than three words. (`890f812`)

### Guard the `pre_get_posts` hook carefully
The hook in `functions.php` that sets `posts_per_page = -1` for taxonomy archive pages must be guarded by both `is_admin()` and `is_main_query()` with correct parentheses. A missing parenthesis caused `is_tax()` to fire on any query touching directory taxonomies — including REST API requests — forcing `posts_per_page = -1` and making WordPress reject `page=1` with `rest_post_invalid_page_number`. (`fdf6879`)

### Offset hides entries silently
`offset` skips N entries before applying `postPerPage`. If offset exceeds the number of matching entries the block displays nothing, with no error. Check `offset` first when debugging apparent "filter not working" or "no results" issues.

### Category wrapper must be conditional
The `<ul>` wrapper for `directory_category` terms must only render when terms are present. An unconditional wrapper outputs empty markup when no categories are assigned. (`1c9f904`)

### AND mode returns 0 for mutually exclusive categories
Selecting two categories that no single post can simultaneously hold (e.g. Faculty + Tenure-Track if they're modeled as exclusive) correctly returns 0 results in AND mode. This is expected behavior — not a bug — but easy to mistake for a broken filter when testing.

### Editor preview hardcodes `<h3>` regardless of `headingLevel`
`edit.js` destructures `headingLevel` and exposes the SelectControl for it, but the JSX that renders entry titles writes `<h3 className="ua_card_title">` unconditionally. The PHP renderer honors the attribute correctly, so a curator who picks H2/H4/H5 sees H3 in the editor preview and the chosen tag on the published page. Pre-existing; surfaces as the only real editor↔frontend parity mismatch during testing. Fix by interpolating the tag (`const Tag = \`h${headingLevel}\``) in the JSX.

### `textAlignment` is a dead control
The attribute exists in `block.json` and is exposed via the `AlignmentToolbar` BlockControl in `edit.js`, but nothing in either `edit.js` or `directory-feed.php` reads it back out to apply a class, style, or align attribute. Toggling the alignment control changes nothing in either view. Either wire it up or remove the control + attribute.

### Editor filter attributes don't react to programmatic `setAttributes`
`category`, `tag`, and `taxRelation` are mirrored into local `useState` (`categoryParam`, `tagParam`, `taxRelationParam`) that's initialized at mount and only updated through the Inspector form handlers. The `useEffect` that triggers the REST refetch keys off the state copies, not the attribute values. Real users are unaffected because the Inspector handlers always call `setAttributes` and the state setter together. But automated tests / harnesses that update attributes via `wp.data.dispatch('core/block-editor').updateBlockAttributes(...)` won't see the editor preview re-filter until the editor is reloaded — easy to mistake for a parity bug.