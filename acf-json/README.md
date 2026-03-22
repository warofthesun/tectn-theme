# ACF local JSON

## Post Settings vs Site Settings vs Footer Information

- **Site Settings** (`site-settings`): Brand tab (logos) + **Google API** tab (Maps key) — `group_69a4e5ae0b03d.json`.
- **Footer Information** (`footer-information`): contact, social, CTAs, disclaimer, tagline — `group_tectn_footer_information.json` (sub-page under Site Settings).
- **Events Page Settings** (`theme-events-settings`): hero + intro fields — registered in `functions.php` as `tectn_register_acf_events_settings()` (sub-page under Site Settings; same slug/post_id as before for data continuity).
- **Post Settings** (`post-settings`): blog/events/pickup options (including blog sidebars migrated from the old Theme Settings screen) — `group_tectn_post_settings.json`.
- **Forms** (`tectn-forms`): repeater of plain-text embed snippets — `group_tectn_site_forms.json`. Use `tectn_get_embedded_forms()` in PHP.
- **Block: Forms** (`tectn/forms`): `blocks/forms/` + `group_tectn_block_forms.json` — pick a form by **stable Form ID** (`form_key`, UUID on each repeater row). Saving **Site Settings → Forms** backfills empty IDs; the block stores the key (not row index) so reordering forms does not break the selection. Legacy blocks that only stored a numeric index still resolve until re-saved.

There is **no** Theme Settings options page in this theme; options live under **Site Settings** and its sub-pages.

New blog/events/pickup options added in code belong in **`group_tectn_post_settings.json`**, not in the main Site Settings group.

## If “Sync available” or sync feels stuck

1. **Bump `modified`** in the edited JSON file to the current Unix time (seconds). ACF compares this to the saved field group in the database; if the file’s `modified` is older than the DB copy, sync may not appear or can look wrong after pulls.
2. Go to **WP Admin → Custom Fields → Field Groups**, open the **Sync available** tab, and use **Sync** on the row (avoid **Review changes** if that panel spins forever—that modal uses AJAX and can fail on some hosts).
3. **Hard refresh** the admin page or try another browser if the list doesn’t update after sync.
4. Last resort: **Trash** the “Post Settings” field group in the database (Field Groups list), then run **Sync** again so ACF imports cleanly from JSON (only if you’re sure the theme JSON is the source of truth).

## Saving from the admin UI

After editing a synced field group in WP Admin, ACF writes back to these files. Commit the updated JSON so environments stay aligned.
