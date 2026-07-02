# Zendy Track migrations

## Production / existing databases

Run only the dated migrations in this folder:

```bash
php artisan migrate
```

These migrations **alter** existing tables safely. They check for missing columns before adding them and will not recreate tables that already exist.

| Migration | Purpose |
|-----------|---------|
| `2026_07_02_120000_add_zendy_profile_fields_to_users_table` | Adds `course`, `department`, `campus` to `users` if missing |
| `2026_07_02_120100_setup_pending_users_table` | Creates `pending_users` or adds missing columns |
| `2026_07_02_120200_upgrade_zendy_logs_table_for_tracking` | Adds `metadata` and other Zendy tracking columns |

## Fresh install only

The `0001_01_01_*` files in `_fresh_install/` are for **new empty databases** only.

```bash
php artisan migrate --path=database/migrations/_fresh_install
php artisan migrate
php artisan db:seed
```

Do **not** run `_fresh_install` on a live database that already has library or Zendy tables.
