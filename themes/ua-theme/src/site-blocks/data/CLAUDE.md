# Page Settings block (`ua-theme/data`)

Editor: [`edit.js`](edit.js) · Registration + meta + save hook: `functions.php` (`ua_theme_init`) · Front-end render: `inc/site-blocks/markup.php` · Test plan: [`REGRESSION-TESTS.md`](REGRESSION-TESTS.md)

This block stores per-page settings as **post meta** (`hero`, `sidebar`, `sidebar_type`,
`sidebar_menu`, `title_alignment`, `title_width`) plus the hero content. It has three
non-obvious behaviors. Read these before editing the block, its meta registration, or
`markup.php`.

## 1. The meta keys MUST stay protected (`is_protected_meta`)

`functions.php` registers these seven keys and adds an `is_protected_meta` filter marking
them protected. **Do not remove that filter or rename the keys to be non-protected.**

Why: they are public (non-`_`-prefixed) post meta with `show_in_rest`. If they are *not*
protected, the classic **"Custom Fields" metabox** lists them, and on every save Gutenberg
fires a second request (`post.php?...&meta-box-loader=1`) that submits those fields with the
values present **at page load** — clobbering the block editor's REST save with stale data.

- It only triggers for users who have the **Custom Fields panel enabled**
  (`enable_custom_fields` user meta = 1), so it presents as a baffling *per-user* "can't save
  Page Settings" bug that you can't reproduce on your own account.
- Symptom split by storage: pure-meta fields (checkboxes, title settings) visibly revert;
  the hero content silently diverges (see #3) because its canonical copy lives in
  `post_content`, which the metabox doesn't touch.

## 2. The meta needs an explicit `auth_callback`

Each `register_post_meta` call passes an explicit `auth_callback`
(`current_user_can('edit_post', $object_id)`). **Keep it.**

Why: when meta is protected and has *no* explicit `auth_callback`, WordPress sets the default
REST `auth_callback` to `__return_false` at registration time — which blocks **all** REST
writes (no Page Settings would save, for anyone). The default is computed from
`is_protected_meta()` during `register_meta`, so without the explicit callback the behavior
depends on whether the filter is registered before or after the `register_post_meta` calls.
The explicit callback removes that ordering trap.

## 3. `hero_blocks` is a denormalized mirror (can diverge)

The hero content is authored as **inner blocks** of `ua-theme/data` (canonical, in
`post_content`). `edit.js` *also* mirrors a serialized copy into the `hero_blocks` meta via a
debounced effect, and **`markup.php` renders the front-end hero from `hero_blocks`, not from
the inner blocks.**

Consequences:
- The editor displays the inner blocks; the front end displays `hero_blocks`. If the mirror
  falls out of sync (the #1 clobber, a debounce race, a half-completed save), the editor and
  the live page disagree with no error.
- Long-term, the robust fix is to render the hero from the block's inner blocks server-side
  (e.g. `parse_blocks()` + `render_block()` on the queried post) and drop the meta mirror.

## Reproducing / verifying

Toggle the Custom Fields panel for a test user, then edit Page Settings and save:

```bash
# enable the panel (reproduces the historical bug if protection is removed)
wp user meta update <USER_ID> enable_custom_fields '1' --url=<subsite-url>
```

Full matrix in [`REGRESSION-TESTS.md`](REGRESSION-TESTS.md).
