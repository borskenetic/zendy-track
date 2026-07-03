# Zendy Track

Laravel portal for launching [Zendy](https://zendy.io/) research access with usage tracking, admin reporting, and optional JWT SSO.

Built for library institutions that need a branded gateway, user management, and session analytics without exposing technical tracking details to patrons.

## Features

- **Zendy portal UI** — separate sidebar layout at `/zendy`
- **Launch tracking** — logs when users open Zendy (launch, direct link, SSO when enabled)
- **Session duration** — estimated time between launch and return to the portal
- **Tab close logging** — beacon-based logging when users leave the portal tab
- **My Activity** — patrons can review their own events
- **Admin tools** — activity logs, reports, user management, pending registrations
- **Roles** — student, faculty, staff, librarian, admin
- **SSO-ready** — JWT SSO hook when Zendy credentials are available

## Requirements

- PHP 8.2+
- Composer
- MySQL / MariaDB
- Node.js (optional, for front-end asset builds)

## Installation

```bash
git clone https://github.com/borskenetic/zendy-track.git
cd zendy-track
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

Default seeded admin (change after first login):

- Email: `admin@jib.edu.ph`
- Password: `password`

## Environment

| Variable | Description |
|----------|-------------|
| `ZENDY_SSO_ENABLED` | `true` when JWT SSO is configured |
| `ZENDY_REDIRECT_URL` | Where users are sent to use Zendy (default `https://zendy.io/`) |
| `ZENDY_SSO_URL` | Zendy SSO endpoint |
| `SSO_SECRET` | Shared secret for signing JWT SSO tokens |
| `ALLOWED_EMAIL_DOMAINS` | Comma-separated email domains for registration (e.g. `jib.edu.ph`). Leave empty to allow any email. |
| `BRAND_INSTITUTION_NAME` | School name shown in the portal sidebar |
| `BRAND_PORTAL_TITLE` | Portal title (default `Zendy Portal`) |
| `BRAND_LOGO_PATH` | Logo path under `public/` (default `images/d.png`) |
| `BRAND_PRIMARY` | Main accent color (buttons, links, avatar) |
| `BRAND_PRIMARY_HOVER` | Hover state for primary buttons |
| `BRAND_SIDEBAR_BG` | Sidebar background |
| `BRAND_SIDEBAR_BG_HOVER` | Sidebar hover / card backgrounds |
| `BRAND_BODY_BG` | Page background |

See `.env.example` for the full list of `BRAND_*` color variables.

## Branding

Set colors and institution name in `.env`, then run `php artisan config:clear`.

For advanced CSS (gradients, custom rules), copy `public/branding/portal.css` and set `BRANDING_CSS=branding/portal.css`.

| Path | Purpose |
|------|---------|
| `/zendy` | Portal dashboard |
| `/zendy/launch` | Launch Zendy (tracked redirect) |
| `/zendy/activity` | User activity history |
| `/zendy/logs` | Admin activity logs |
| `/zendy/reports` | Admin reports |
| `/login` | Main app / library login |

## Deploy notes

Upload these public assets when deploying:

- `public/css/zendy-app.css`
- `public/js/sidebar.js`
- `public/js/zendy-session.js`

Run migrations on the server after pulling updates:

```bash
php artisan migrate --force
```

**Existing database:** only the dated `2026_07_02_*` migrations run automatically. They add missing Zendy columns (including `zendy_logs.metadata`) without recreating tables.

**Brand-new database:** see `database/migrations/README.md` for the `_fresh_install` path.

## License

MIT
