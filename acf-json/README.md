# ACF local JSON

## Site Settings menu (parent + sub-pages)

- **Site Settings** top-level menu (`site-settings-parent`) is a **container only** (not a field screen). Sync `ui_options_page_69a4e43144435.json` so the parent slug updates in ACF.
- **Brand** (`site-settings`): logos only — `group_69a4e5ae0b03d.json`. Same `post_id` as before (`site-settings`) so `get_field( 'site_settings', 'site-settings' )` is unchanged.
- **Integrations** (`site-settings-integrations`): Google Maps API key — registered in `inc/acf-options.php` (`tectn_register_acf_integrations_field_group`). Read order: `tectn_google_maps_api_key()` in `inc/theme-setup.php`.
- **Footer Information** (`footer-information`): **Contact Information** section (message + address, phone, email inside the `footer_information` group), then Social / CTA / Disclaimer tabs — `group_tectn_footer_information.json`. (Tab fields are not used for contact inside the group: ACF’s tab UI often fails when nested in a Group, which hid those fields.)
- **Events Page Settings** (`theme-events-settings`): hero + intro fields — registered in `inc/acf-options.php` as `tectn_register_acf_events_settings()`.
- **Post Settings** (`post-settings`): blog/events/pickup options — `group_tectn_post_settings.json`.
- **Forms** (`tectn-forms`): repeater of plain-text embed snippets — `group_tectn_site_forms.json`. Use `tectn_get_embedded_forms()` in PHP.
- **Information tables** (`tectn-info-tables`): reusable four-column tables — `group_tectn_site_info_tables.json`. Use `tectn_get_embedded_info_tables()` in PHP.

### Block-related options

- **Block: Forms** (`tectn/forms`): `blocks/forms/` + `group_tectn_block_forms.json` — pick a form by **stable Form ID** (`form_key`, UUID on each repeater row). Saving **Site Settings → Forms** backfills empty IDs; the block stores the key (not row index) so reordering forms does not break the selection. Legacy blocks that only stored a numeric index still resolve until re-saved.
- **Block: Information table** (`tectn/info-table`): `blocks/info-table/` + `group_tectn_block_info_table.json` — pick a table by **stable table ID** (`info_table_key`, UUID). Saving **Site Settings → Information tables** backfills empty keys; legacy numeric selection still resolves until re-saved.
- **Block: Iframe Embed** (`tectn/iframe-embed`): `blocks/iframe-embed/` + `group_tectn_iframe_embed_block.json` — optional section headline+textarea for pasted **iframe** markup; output is passed through `wp_kses()` with an iframe allowlist.

There is **no** Theme Settings options page in this theme; options live under **Site Settings** and its sub-pages.

New blog/events/pickup options added in code belong in **`group_tectn_post_settings.json`**, not in the Brand field group.

## If “Sync available” or sync feels stuck

1. **Understand `modified`:** ACF shows **Sync available** when the JSON file’s `modified` Unix timestamp is **greater than** the WordPress post’s `post_modified_gmt` for that ACF item. Placeholder values in JSON that are *too large* (e.g. `1777600000` while the server clock is lower) make JSON always look “newer” than the DB **after** you sync, so the notice **never clears**. Fix: set `modified` in JSON to a real time **before** the DB post’s modified time (e.g. run `date +%s` on the server and use a value below that), **or** open the field group / options page in WP Admin and **Save** once so ACF rewrites JSON using the post’s actual modified time.
2. To **force** a sync **from JSON** to the database, **bump `modified`** in the JSON file to **now** (`date +%s`) so it is greater than the DB copy; then sync.
3. Go to **WP Admin → Custom Fields → Field Groups** (or **Options Pages** for UI pages), open the **Sync available** tab, and use **Sync** on the row (avoid **Review changes** if that panel spins forever—that modal uses AJAX and can fail on some hosts).
4. **Hard refresh** the admin page or try another browser if the list doesn’t update after sync.
5. Last resort: **Trash** the field group or options page in the database (ACF list), then run **Sync** again so ACF imports cleanly from JSON (only if you’re sure the theme JSON is the source of truth).

## Saving from the admin UI

After editing a synced field group in WP Admin, ACF writes back to these files. Commit the updated JSON so environments stay aligned.
