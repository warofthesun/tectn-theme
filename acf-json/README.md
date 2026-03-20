# ACF local JSON

## Post Settings vs Site Settings

- **Site Settings** (top-level options page) field groups use location `options_page == site-settings`.
- **Post Settings** (submenu under Site Settings) fields live in `group_tectn_post_settings.json` and use `options_page == post-settings`.

New blog/events/pickup options added in code belong in **`group_tectn_post_settings.json`**, not in the main Site Settings group.

## If “Sync available” or sync feels stuck

1. **Bump `modified`** in the edited JSON file to the current Unix time (seconds). ACF compares this to the saved field group in the database; if the file’s `modified` is older than the DB copy, sync may not appear or can look wrong after pulls.
2. Go to **WP Admin → Custom Fields → Field Groups**, open the **Sync available** tab, and use **Sync** on the row (avoid **Review changes** if that panel spins forever—that modal uses AJAX and can fail on some hosts).
3. **Hard refresh** the admin page or try another browser if the list doesn’t update after sync.
4. Last resort: **Trash** the “Post Settings” field group in the database (Field Groups list), then run **Sync** again so ACF imports cleanly from JSON (only if you’re sure the theme JSON is the source of truth).

## Saving from the admin UI

After editing a synced field group in WP Admin, ACF writes back to these files. Commit the updated JSON so environments stay aligned.
