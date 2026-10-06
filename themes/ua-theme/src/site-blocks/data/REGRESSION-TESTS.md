# Page Settings block — regression tests

Covers the `ua-theme/data` (Page Settings) block after adding the `is_protected_meta`
filter that hides its meta keys from the classic "Custom Fields" metabox.

**Background:** the Custom Fields metabox round-trips public post meta with stale
page-load values on every save (`post.php?...meta-box-loader=1`), clobbering the block
editor's REST save. It only triggers for users with the Custom Fields panel enabled
(`enable_custom_fields = 1`), which is why the original report was account-specific.
The fix protects the seven Page Settings keys so the metabox can't touch them.

Meta keys in scope (registered in `functions.php`):
`hero`, `hero_blocks`, `sidebar`, `sidebar_type`, `sidebar_menu`, `title_alignment`, `title_width`.

WP-CLI note: `wp-env run cli` can break if the instance hash drifts; use
`CLI=$(docker ps --format '{{.Names}}' | grep -- '-cli-1$' | grep -v tests | head -1)`
then `docker exec "$CLI" wp ... --url=http://localhost:1000/socialwork/`.

---

## #1 risk: REST write permission (test first)

The seven keys are registered with an explicit `auth_callback`. WordPress otherwise derives
the default auth_callback from `is_protected_meta()` at registration time:
- not protected -> `__return_true`
- protected -> `__return_false`

If the filter is active when `register_post_meta` runs (both on `init`), the default can
flip to `__return_false` and deny ALL REST writes — i.e. no Page Settings field saves, for
everyone. If it works today it may be hook-order luck.

- [ ] Every field below still saves (not just `hero`).
- [ ] Direct REST `PUT` of each key returns 200 and persists.
- Keep the explicit `auth_callback` on each `register_post_meta` so display protection stays
  decoupled from write permission.

---

## Test axes

Run every persistence case across:

- **Axis A — Custom Fields panel:** OFF (baseline) **and** ON (`enable_custom_fields=1`,
  the previously-broken condition; the point of the fix).
- **Axis B — Save mode:** publish/Update, and draft -> autosave -> publish.

Toggle the panel per user:
```bash
docker exec "$CLI" wp user meta update <UID> enable_custom_fields '1' --url=http://localhost:1000/socialwork/   # ON
docker exec "$CLI" wp user meta update <UID> enable_custom_fields ''  --url=http://localhost:1000/socialwork/   # OFF
```

## Oracle per case

1. Make the change in the editor.
2. Save.
3. Hard reload the editor -> it reflects the change.
4. WP-CLI -> meta value matches.
5. Front end -> rendered output matches.

```bash
docker exec "$CLI" wp eval '
$id=24781;
foreach(["hero","sidebar","sidebar_type","sidebar_menu","title_alignment","title_width","hero_blocks"] as $k){
  $v=get_post_meta($id,$k,true);
  if($k==="hero_blocks") $v="(".strlen((string)$v)." chars)";
  printf("%-16s = %s\n",$k,var_export($v,true));
}' --url=http://localhost:1000/socialwork/
```

---

## Field matrix (run under CF-on AND CF-off)

| Field | Type | Change to exercise | Front-end assertion |
|---|---|---|---|
| `hero` | bool | on->off, off->on | hero `<header>` present / absent |
| `sidebar` | bool | on->off, off->on | sidebar nav present / absent |
| `sidebar_type` | string | select <-> dynamic | correct sidebar variant renders |
| `sidebar_menu` | string | switch between two menus | correct menu items render |
| `title_alignment` | string | left->center->right | alignment class on title |
| `title_width` | string | standard <-> wide | width class on title |
| `hero_blocks` | string | see content variations | hero content matches edit |

Critical pass: **CF-on persists after reload** for all seven.

## Inner-block content variations (`hero_blocks`)

Each: edit -> save -> reload -> editor matches -> front end matches -> no encoding drift.

- [ ] Default — Cover + H1
- [ ] Multi-block — Cover + H1 + paragraph + buttons
- [ ] No H1 in hero (verifies `hasHeroH1` branch shows Title Settings, and still saves)
- [ ] Has H1 (Title Settings hidden; still saves)
- [ ] Nested — Cover > Group > (Heading + Paragraph)
- [ ] Special characters — quotes, `&`, `< >`, em-dash, curly quotes, accents, emoji
      (highest-value test; same encoding-pitfall class flagged in event-feed CLAUDE.md)
- [ ] Nested theme block inside hero (e.g. `ua-blocks/link-list`, styled button)
- [ ] Media block — image/Cover with a real attachment (srcset, IDs survive)
- [ ] Shortcode in content (render path runs `do_shortcode`)
- [ ] Empty hero — checked, no inner content; graceful
- [ ] Large — 10+ blocks; integrity + debounced mirror keeps up

## Cross-cutting / integration

- [ ] Block removed entirely -> `save_post` hook resets `hero`/`sidebar` false, title defaults
- [ ] Hero off -> on in one session -> content restored; `hero_blocks` consistent
- [ ] Rapid edit + immediate Save (faster than 300 ms debounce) -> `hero_blocks` matches final content
- [ ] Orphaned-author page (author not a blog member, e.g. page 24781) -> settings still save
- [ ] Roles — admin and editor; editor with CF-on
- [ ] New page (`post-new.php`) -> defaults apply; first save writes meta
- [ ] Revisions — restore an older revision; meta/`hero_blocks` land sensibly

## Fix-specific assertions

- [ ] The seven keys no longer appear in the Custom Fields metabox (CF-on user); the
      `meta-box-loader` POST body no longer contains them
- [ ] A user-added arbitrary custom field (e.g. `qa_scratch`) still shows and saves
      (feature not globally disabled)
- [ ] `_ua_noindex` (Search Indexing panel) still saves/loads
- [ ] `_person_*` directory fields still save on the `directory` CPT (filter scoped to page meta)
- [ ] Direct REST: `GET` page -> all seven keys still present in `meta` (`show_in_rest` intact)
- [ ] Filter hygiene (unit): returns original `$protected` for non-matching keys; exact-key
      match, not substring; no error on empty/odd `$meta_key`
- [ ] Multisite smoke pass on a second subsite (theme/filter is network-wide)

---

## Automation tiers

- **Tier 1 (now):** manual checklist above, weighted to CF-on persistence, the encoding
  content case, and front-end render.
- **Tier 2 (recommended):** E2E (Playwright / `@wordpress/e2e-test-utils`) — per field under
  CF-on: toggle -> save -> reload -> assert, with WP-CLI `meta get` as the post-save oracle.
- **Tier 3 (nice):** PHPUnit for `render_markup()` output given meta, the `save_post` reset
  hook, and a pure-unit test of the `is_protected_meta` filter.
