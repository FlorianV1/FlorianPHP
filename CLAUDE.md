# florianphp

Personal portfolio **and** agency back office. Public Blade site at `/`, two Filament panels
behind auth. Repositioning from "developer portfolio" toward "developer + small software
company for SMBs".

## Stack

| | |
|---|---|
| PHP | requires **^8.4**; Forge runs **8.4.15**, local Herd is isolated to **8.5** |
| Laravel | 13.x |
| Filament | 5.x (two panels) |
| Livewire | 4.x |
| Tailwind | 4.x, compiled through Vite |
| Tests | Pest 4 |
| DB | **MySQL** (`florianphp` on 127.0.0.1:3306) |
| Hosting | Laravel Forge |

## Toolchain — read this before running anything

This site is isolated to PHP **8.5** in Herd, but the Forge server runs **8.4.15**, and
`composer.json` requires `^8.4` so both work. `config.platform.php` is pinned to `8.4.15`, so
Composer resolves against the production version — a dependency needing 8.5 can never be locked
here and then fail on deploy. Do not raise either without upgrading the server first.

Herd's global `php` is 8.4 while this site is 8.5, so keep using the explicit binary to match
what the tests and the served site run on:

```bash
# artisan
"$USERPROFILE/.config/herd/bin/php85.bat" artisan <cmd> --no-interaction

# composer
"$USERPROFILE/.config/herd/bin/php85.bat" "$USERPROFILE/.config/herd/bin/composer.phar" <cmd>

# pint / pest via vendor binaries
PATH="$USERPROFILE/.config/herd/bin/php85:$PATH" php vendor/bin/pint --dirty
```

The site is served by Herd at `http://florianphp.test` (`herd isolate 8.5`, persistent). Don't run
`artisan serve` for the user — the Herd site is always up.

`.env` is permission-blocked. Never infer the database from repo contents.

## The database is MySQL, not the SQLite file

`database/database.sqlite` exists in this repo and is a **stale empty artifact**. `.env` sets
`DB_CONNECTION=mysql`. Before `migrate:fresh`, `migrate:refresh`, `db:wipe` or anything
destructive:

```bash
php artisan config:show database.default
php artisan config:show database.connections.mysql
```

then back up **that** database with `mysqldump` — not by copying files. This is not hypothetical:
on 2026-09-17 a `migrate:fresh` here dropped and rebuilt every MySQL table while the "backup"
taken was of the unused SQLite file.

## Layout

```
app/Filament/Website/     → panel "website" at /website — portfolio content, messages, settings
app/Filament/Management/  → panel "management" at /management — agency back office (being ported)
app/Http/Controllers/     → PortfolioController (public site), SitemapController
app/Models/               → Profile (singleton), Project, Experience, Skill, NowItem,
                            Service, Testimonial, Stat, Settings (key/value JSON), ContactMessage
app/Support/SiteBranding  → the single source for name / logo / terminal prompt
resources/views/          → portfolio.blade.php, case-study.blade.php,
                            components/portfolio/* (one component per homepage section)
resources/css/app.css     → every public-site style; no inline `style=""` in Blade
resources/css/filament/website/theme.css → shared custom theme for BOTH panels
```

Homepage sections render from the `sections_order` Settings key, one Blade component each.
Adding a section means: component + `PortfolioController::defaultSectionsOrder()` +
the `SiteSettings` repeater options.

`Management` panel access is gated on `users.is_admin`; `Website` is open to any authenticated
user. See `User::canAccessPanel()`.

## Frontend bundling

The public site and both panels are served from the Vite build. There is **no CDN fallback** —
if the manifest is missing, every page 500s with `Unable to locate file in Vite manifest`.

- After changing CSS or Blade: `npm run build`.
- **`public/hot` is a trap.** If it exists and no dev server is running, `@vite` points every
  stylesheet at `localhost:5173` and the site renders unstyled. Delete it, or run `npm run dev`.
- `public/build` is gitignored, so **the Forge deploy script must run `npm ci && npm run build`**.

## Conventions

- Follow the conventions in sibling files. Check for an existing component before writing one.
- Create files with `php artisan make:*` (and Filament's own `make:filament-*`), `--no-interaction`.
- Run `vendor/bin/pint --dirty` after touching PHP, before finishing.
- Descriptive names: `isRegisteredForDiscounts`, not `discount()`.
- Curly braces always; constructor property promotion; explicit return types and param hints.
- TitleCase enum keys. PHPDoc over inline comments, with array shapes where useful.
- Create files, folders and documentation as the work needs them — no need to ask first.
- Still ask before adding a Composer or npm dependency.

## Testing

- Pest. `php artisan test --compact`, filter with `--filter`.
- `phpunit.xml` pins the test DB to sqlite `:memory:`, so tests never touch MySQL.
- `tests/Pest.php` applies `RefreshDatabase` to everything in `Feature`.
- Panel smoke tests (`WebsitePanelSmokeTest`, `ManagementPanelSmokeTest`) render every panel page
  — they are what catches a Filament upgrade breaking a resource. Add new resources to them.
- Don't delete tests without approval.

### Debugging a 500 in a test

Laravel's HTML error renderer OOMs on Filament pages before it prints the message. To see the
real exception, catch it instead:

```php
try { $this->withoutExceptionHandling()->get('/website/projects'); }
catch (\Throwable $e) { echo get_class($e) . ': ' . $e->getMessage(); }
```

## Filament 5 namespaces

- Form fields (`TextInput`, `Select`, `Repeater`): `Filament\Forms\Components\`
- Layout (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`): `Filament\Schemas\Components\Utilities\`
- Infolist entries: `Filament\Infolists\Components\`
- Table columns: `Filament\Tables\Columns\` · filters: `Filament\Tables\Filters\`
- **All** actions: `Filament\Actions\` — never `Filament\Tables\Actions\`
- Icons: `Filament\Support\Icons\Heroicon`

Property types when overriding, these are union-typed and must be preserved:

- `$navigationIcon`: `protected static string | BackedEnum | null`
- `$navigationGroup`: `protected static string | UnitEnum | null`
- `$view`: `protected string` (not static) on `Page` / `Widget`

Other traps: `Repeater` uses `->schema()`; uploads are private unless `->visibility('public')`;
`Grid`/`Section`/`Fieldset` don't span all columns by default; never `->dehydrated(false)` on a
field that must save.

## Windows

`app/Support/WindowsSafeFilesystem` is bound to `files` in `AppServiceProvider::register()` on
Windows only. Filament's SPA fires parallel requests that race compiling the same Blade view, and
Windows `rename()` fails when another process holds the destination open. Keep that binding when
touching the provider. `view:cache` does **not** help — CLI and web hash view paths with different
slash directions.

Windows `sed`/`perl` mangle backslashes in PHP namespaces and `$&` in replacements. Use the Edit
tool or a PHP script for namespace rewrites, never shell regex.
