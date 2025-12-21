# One Piece Explorer (Anime API)

Simple Laravel app to browse One Piece episodes, track viewing progress, and render embedded players — all with a lightweight pirate-themed UI.

## Features

- Episodes list with compact pagination (default 50 per page)
- Episode details page with embedded player (when provider allows)
- Progress tracking via session-backed API (`GET/POST /api/progress`)
- Simple name-based login that personalizes the session header
- Themed homepage with quick actions and current progress card

## Quick Start

1. Install dependencies:
   - `composer install`
   - `npm install` (optional; not required for basic functionality)

2. Environment:
   - Copy `.env.example` to `.env`
   - Set a valid app key: `php artisan key:generate`
   - Ensure `DB_CONNECTION=sqlite` and `SESSION_DRIVER=database`
   - Create the database file if missing: `touch database/database.sqlite`
   - Optionally set `APP_URL` to the host you’ll use (e.g., `http://127.0.0.1:8000` or `http://localhost:8000`)

3. Database:
   - Run migrations: `php artisan migrate`
   - Populate episodes:
     - `php artisan episodes:populate-onepiece` — creates/updates One Piece episodes and their `video_url` when available.

4. Serve:
   - `php artisan serve` — dev server at `http://127.0.0.1:8000`
   - Use a single host consistently (either `127.0.0.1` or `localhost`) to keep session cookies consistent across pages and API calls.

## Routes

- Web
  - `/` — Home (banner, quick actions, current progress)
  - `/episodes` — Episodes list (50 per page by default)
  - `/episodes/{id}` — Episode details with optional embed player
  - `/login` (`GET`) — Enter a viewer name
  - `/login` (`POST`) — Persist `viewer_name` to session and redirect
  - `/logout` (`POST`) — Clear session and return home

- API (session-enabled)
  - `GET /api/episodes` — Paginated episodes (default 25; use `?per_page=50` to match UI)
  - `GET /api/episodes/{id}` — Episode by id
  - `GET /api/progress` — Current episode for the session
  - `POST /api/progress` — Set current episode `{ episode_id: number }`

## Usage Notes

- Current episode
  - The Episodes list auto-redirects to the page that contains the last marked episode when the `page` query param is not set.
  - The “Current episode” card is clickable and navigates to the last marked episode.

- Pagination
  - The list renders controls: First/Prev and Next/Last, plus a compact summary (e.g., `Page 3 of 31 • 50 per page`).
  - Change page size with `?per_page=50` or any value (controller uses the query param).

- Login & Session
  - The header shows “Sailing as <name>” when logged in and provides a Logout button.
  - Progress uses the server-side session ID; keep the same host throughout your browsing session.

## Scripts

- `scripts/scrape-onepiece.cjs` — helper script to source embed/watch URLs (used by the populate command).

## Troubleshooting

- Login seems to disappear after refresh
  - Ensure all pages and API calls use the same host: only `http://127.0.0.1:8000` or only `http://localhost:8000`.
  - The API routes include cookie encryption and session middleware so the session cookie is read correctly.

- Progress doesn’t show on Home or Episodes list
  - Check the browser console for network errors; the pages fetch `/api/progress` with `credentials: 'same-origin'`.
  - Confirm the episode exists: `curl http://127.0.0.1:8000/api/episodes/1`
  - Confirm the Progress API: `curl -X POST -H 'Content-Type: application/json' -d '{"episode_id":1}' http://127.0.0.1:8000/api/progress`

## Stack

- Laravel (PHP) + SQLite
- Session driver: `database` (uses `sessions` table)
- Minimal frontend using Blade templates and vanilla JavaScript

## Development

- Run tests: `phpunit`
- Lint/format PHP: `vendor/bin/pint`

## Roadmap Ideas

- Jump to episode/page input on the list
- Page size selector (e.g., 25/50/100)
- Persist progress by `viewer_name` for cross-session continuity