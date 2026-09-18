# florianphp — discovery report

*Read-only survey, 18 September 2026. Nothing in this repo was modified; this file is the only thing written.*

## Summary

`florianphp` is a single Laravel 13 / Filament 5 application that serves one public marketing site
(a dark, terminal-themed one-page portfolio at `/`, plus per-project case studies at `/work/{slug}`)
and two Filament panels behind auth: `/website` — the portfolio CMS — and `/management`, an agency
back office. Almost all visible page copy is already database-driven through Filament, which is the
single most important thing about this codebase for the repositioning: the words on the homepage are
rows in MySQL, not strings in Blade. Structurally the app is in good shape — a clean controller, one
Blade component per homepage section, a compiled Vite/Tailwind 4 bundle, no CDN Tailwind, real SEO
tags and JSON-LD, 93 green tests covering the homepage, case studies, SEO and the contact anti-spam
path. Its weaknesses are all at the edges: no CI, no deploy script in the repo, no static analysis,
a stock Laravel README and a stock `.env.example` that documents none of the variables the app
actually needs, no authorization policies anywhere, and a seeded admin account with a hardcoded
password. Health overall: **good bones, unfinished edges, and currently mid-surgery** — a large
uncommitted port is in flight in this working tree right now and its test suite is red.

---

## How to read this report: (a) committed vs (b) in flight

Everything is labelled one of two ways.

**(a) The committed state.** `HEAD` is `6721606` on branch `management-panel`, four commits ahead of
`origin/main`. Note that HEAD itself is *not* the old portfolio: the four most recent commits are all
dated 2026-09-17 and are already part of the repositioning —

| commit | date | what it did |
|---|---|---|
| `6721606` | 2026-09-17 | Filament plugins, Management dashboard widgets, `CLAUDE.md`, `WindowsSafeFilesystem` |
| `b0e1305` | 2026-09-17 | services / testimonials / stats / case studies; moved CSS from the Tailwind Play CDN into Vite |
| `e171551` | 2026-09-17 | ported the command-center **domain models, enums, factories and migrations** |
| `f17cd3e` | 2026-09-17 | upgraded to Laravel 13 + Filament 5, split one panel into Website + Management |

The last commit that predates the repositioning is `1350c0d` (2026-08-04). Where the distinction
matters below I say "pre-repositioning" for that older state.

**(b) The in-flight, uncommitted port.** A separate session is actively moving the Agency Command
Center into this repo. It is **not reviewed and not committed**, and I treat it as such throughout.

**The tree moved while I was reading it.** At 18:14 `git status` listed one untracked test file; by
18:36 it listed fourteen more (`tests/Feature/Billing/`, `tests/Feature/Bugsnag/`,
`tests/Feature/Filament/`, `tests/Feature/Models/`, `tests/Feature/SiteBridge/`,
`tests/Unit/SiteBridge/`, `tests/Unit/RetainerIntervalTest.php`). Anything I say about (b) is a
snapshot of roughly 18:15–18:40 and may already be stale.

---

## 1. Stack and versions

*(a) committed.*

| | version | notes |
|---|---|---|
| PHP | `^8.5` required (`composer.json:10`) | Herd's global `php` is 8.4; see `CLAUDE.md` for the `php85.bat` invocation |
| Laravel | 13.32.0 | current |
| Filament | 5.8.2 | current; all sub-packages at 5.8.2 |
| Livewire | 4.4.5 | current (transitive, via Filament) |
| Tailwind CSS | 4.3.3, via `@tailwindcss/vite` 4.3.3 | current; compiled, no CDN |
| Vite | 7.2.2, `laravel-vite-plugin` 2.0.1 | current |
| Pest | 4.7.8 (+ `pest-plugin-laravel` 4.1.0) | current |
| PHPUnit | via Pest | `phpunit.xml` has no schema version pin issues |

Other direct Composer requirements (`composer.json:11-21`):

- `barryvdh/laravel-dompdf` 3.1.2 — **no code in this repo uses it.** No `PDF::`, no `Pdf` facade,
  no dompdf import anywhere in `app/`. Presumably staged for invoice PDFs by the in-flight port,
  but as committed it is an unused dependency.
- `bezhansalleh/filament-panel-switch` 3.1.0 — wired up in `app/Providers/AppServiceProvider.php:46-61`.
- `bugsnag/bugsnag-laravel` 2.31.0 — registered manually (`bootstrap/providers.php:4`) with
  auto-discovery disabled (`composer.json:"extra.laravel.dont-discover"`).
- `doctrine/dbal` 4.4.4 — needed for column changes in older migrations.
- `laravel/tinker` 3.0.2, `nunomaduro/essentials` 1.2.0, `spatie/laravel-activitylog` 5.1.1.

Dev: `pint` 1.32.1, `pail` 1.2.7, `sail` 1.67.0, `collision` 8.9.5, `mockery` 1.6.15, `faker` 1.24.1.

npm (`package.json`, all devDependencies): `@tailwindcss/vite` 4.3.3, `tailwindcss` 4.3.3, `vite`
7.2.2, `laravel-vite-plugin` 2.0.1, `axios` 1.13.2, `concurrently` 9.2.1. There is **no** Alpine,
no bundled app JS beyond `resources/js/app.js` (5 lines, just axios) — and the public site does not
even load that; `resources/views/components/layouts/portfolio.blade.php:79-82` explicitly says so
and pulls only `resources/css/app.css`.

**Nothing is behind current.** Laravel 13, Filament 5, Livewire 4, Tailwind 4, Vite 7, Pest 4 are all
at or near latest.

*(b) in flight:* the port **removed** two Filament plugins that commit `6721606` had added four hours
earlier — `johnrivera7/filament-custom-dashboard-widgets` and `pxlrbt/filament-environment-indicator`
(`git diff composer.json`). `vendor/` no longer contains them, so `composer install` has been run. See
§10 for the leftovers that removal left behind.

---

## 2. Structure, routing and domains

*(a) committed.*

`routes/web.php` is four lines and holds the entire public site:

```
routes/web.php:7   GET  /                      → PortfolioController@index      (home)
routes/web.php:8   GET  /work/{project:slug}   → PortfolioController@caseStudy  (case-study)
routes/web.php:9   GET  /sitemap.xml           → SitemapController              (sitemap)
routes/web.php:10  POST /contact               → PortfolioController@submitContact  (throttle:5,1)
```

`bootstrap/app.php` registers `web`, `console` and the `/up` health endpoint. Middleware and
exception hooks are both empty stubs (`bootstrap/app.php:21-24`).

`php artisan route:list --except-vendor` returns **58 routes**: the four above plus 54 Filament panel
routes (`/website/*`, `/management/*`) plus the two in-flight site-bridge endpoints.

**Subdomains: there are none.** No `->domain()` call exists anywhere in `routes/`, `app/` or
`config/`. The only `domain()` in the codebase is `App\Models\Website::domain()`
(`app/Models/Website.php:127`), a helper that parses the host out of a *client's* URL — unrelated to
routing.

**`gallery.florianphp.com` is not in this codebase.** A case-insensitive search for "gallery" across
`app/`, `resources/`, `routes/`, `config/`, `database/` and `public/` returns nothing. Whatever serves
that hostname is a separate application or a separate Forge site. No other demo sites are served here.

**No deploy or infrastructure config is in the repo at all** — no `.github/`, no `Envoy.blade.php`,
no nginx snippet, no `deploy.sh`, no `docker-compose.yml`. The Forge deploy script lives only in
Forge. `CLAUDE.md:86` records the one thing that matters about it: `public/build` is gitignored, so
the deploy script *must* run `npm ci && npm run build` or every page 500s on a missing Vite manifest.

`public/.htaccess` is the stock Laravel one. `public/robots.txt` disallows `/website` and `/management`
and points at `https://florianphp.com/sitemap.xml` — the only place the production hostname is
hardcoded in the repo.

*(b) in flight:* `bootstrap/app.php:16-19` gains a `then:` closure registering
`routes/site-bridge.php` under an `api/site-bridge` prefix, outside the `web` group (no session, no
CSRF). Two endpoints: `POST api/site-bridge/claim` (throttle 20/min) and
`POST api/site-bridge/heartbeat` (`AuthenticateSiteBridgeKey` + a named `site-bridge-heartbeat`
limiter defined in `app/Providers/AppServiceProvider.php:71-77`, 120/min keyed per bridge key rather
than per IP).

---

## 3. Data model — and what is CMS versus what is hardcoded

*(a) committed.* 45 migrations; `database/database.sqlite` (114 KB, dated Nov 2025) is a stale
artifact — the app runs on MySQL (`CLAUDE.md:41-54`, and a `migrate:fresh` accident on 2026-09-17 is
recorded there).

### Portfolio / CMS models (`app/Models/`)

| model | shape | rendered by |
|---|---|---|
| `Profile` | singleton row (`Profile::first()`), 24 fillable columns | hero, about, contact, footer, SEO |
| `Project` | many; `is_posted` gates visibility, `slug` + body gate the case study | projects section, `/work/{slug}` |
| `Experience` | many; `entry_type` splits work vs education | experience + education sections |
| `Skill` | many; `is_active`, `category`, `order` | skills marquee, hero badges |
| `NowItem` | many; `is_active`, `order` | "Now" section |
| `Service` | many; `title`, `description`, `icon`, `sort_order`, `is_active` | services section |
| `Testimonial` | many; optional `belongsTo(Project)` | testimonials section, case-study page |
| `Stat` | many; integer `value` + `suffix` + singular/plural labels + `auto_source` | about-section stat block |
| `Settings` | key/value with a JSON `value` column | theme colours, section order, navbar links, branding |
| `ContactMessage` | inbound leads, `is_spam`, `read_at` | Filament only |
| `PageView` | analytics rows written by `PortfolioController::trackPageView()` | Filament widgets only |

`Stat` deserves a note: `app/Models/Stat.php:45-52` resolves a value from `auto_source` —
`projects_count` recounts posted projects, `years_experience` derives from the earliest work
`Experience`, `manual` uses the typed integer. The column is `unsignedInteger`
(`database/migrations/2026_09_17_100400_create_stats_table.php:16`) precisely so a stat can no longer
be free text. `app/Models/Stat.php:59-64` picks singular vs plural label from the resolved number.

### Command-centre models (committed in `e171551`, not yet exposed in any committed UI)

`Client`, `Contact`, `Website`, `Invoice`, `InvoiceLine`, `Retainer`, `TimeEntry`, `SiteBridgeKey`,
`SiteBridgeClaimCode`, `SiteBridgeHeartbeat`, `SiteBridgeHeartbeatStat`, plus enums `ClientStatus`,
`InvoiceStatus`, `RetainerInterval`, `WebsiteEnvironment`, plus `spatie/laravel-activitylog`'s table.
Relationship spine: `Client hasMany Website|Contact|Invoice|Retainer|TimeEntry`;
`Website hasMany Invoice|Retainer|bridgeKeys|claimCodes|heartbeats`. **There is no `User` ↔ `Client`
or `User` ↔ `Website` relationship of any kind** — important for §"Fit".

### What is hardcoded in Blade rather than in the CMS

Almost all *body copy* is CMS-driven. What is **not** editable without a deploy:

| hardcoded string | file:line |
|---|---|
| `About me` / `/ me` | `resources/views/components/portfolio/about.blade.php:29-30` |
| `What I build` / `/ services` | `resources/views/components/portfolio/services.blade.php:8-9` |
| `Technologies` / `/ stack` | `resources/views/components/portfolio/skills-marquee.blade.php:10-11` |
| `Kind words` | `resources/views/components/portfolio/testimonials.blade.php:8` |
| `Now` / `/ current focus` | `resources/views/components/portfolio/now.blade.php:7-8` |
| `Projects` | `resources/views/components/portfolio/projects.blade.php:13` |
| `Experience` | `resources/views/components/portfolio/experience.blade.php:9` |
| `Education` | `resources/views/components/portfolio/education.blade.php:8` |
| `Get in touch` / `/ contact` | `resources/views/components/portfolio/contact.blade.php:10-11` |
| `Or email directly` | `resources/views/components/portfolio/contact.blade.php:21` |
| `Message sent! I'll get back to you soon.` | `resources/views/components/portfolio/contact.blade.php:55` |
| `Name` / `Email` / `Message` / `Send message` form labels | `contact.blade.php` (form block) |
| `Hi, I'm` greeting | `resources/views/components/portfolio/hero.blade.php:46` |
| terminal script: `whoami`, `cat info.json`, `ls projects/`, `git status`, `php artisan serve`, `"focus": "web applications"`, `nothing to commit, working tree clean`, `3 commits ahead of origin/main`, `Server running on http://localhost:8000`, `All systems go.` | `hero.blade.php:156-202` |
| `Built with` / `Other projects` / `Got something similar in mind?` | `resources/views/case-study.blade.php:90, 121, 145` |
| every Blade `?? 'fallback'` default (e.g. `'Software Developer'`, `'View my work'`, `'Built with Laravel & love.'`) | `hero.blade.php:23-25,40,57,65,68`, `footer.blade.php:36`, `contact.blade.php:15` |

There are **no lang files**; `resources/lang/` does not exist. The site is English-only with no
translation layer.

---

## 4. Filament

*(a) committed.* Two panels, both registered in `bootstrap/providers.php:6-7`.

### `website` panel — `app/Providers/Filament/WebsitePanelProvider.php`

- `->default()`, id `website`, path `/website`, brand name "Website", primary colour Blue.
- `->login()`, `->profile()`, `->spa()`, `->sidebarCollapsibleOnDesktop()`, `->unsavedChangesAlerts()`.
- Shared theme: `->viteTheme('resources/css/filament/website/theme.css')` (line 33) — **both** panels
  point at the same theme file.
- Navigation groups: `Content`, `Portfolio` (lines 60-67).
- Resources: Projects, Experiences, Skills, NowItems, Services, Testimonials, Stats, Messages,
  Settings. Pages: `OverviewDashboard`, `ProfilePage`, `SiteSettings`. Widgets:
  `DashboardStatsWidget`, `PageViewsChart`, `ContentOverviewWidget`, `RecentMessagesWidget`,
  `TopCountriesWidget`, `TopPagesWidget`.

### `management` panel — `app/Providers/Filament/ManagementPanelProvider.php`

- id `management`, path `/management`, brand name "Management", primary colour Emerald, same theme.
- Navigation groups: `CRM`, `Billing` (in flight; committed state said `Clients`).
- As committed it contains one page (`ManagementDashboard`) and three lead widgets. All the client /
  invoice / retainer / time-entry / website resources are **(b)**, uncommitted.

### Who can log into what — `app/Models/User.php:37-48`

```php
public function canAccessPanel(Panel $panel): bool
{
    if ($panel->getId() !== 'management') {
        return true;
    }
    return $this->hasAttribute('is_admin') && (bool) $this->is_admin;
}
```

So: **any authenticated user can access `/website` in full.** `/management` additionally requires
`users.is_admin` (column added by `2026_08_06_100002_add_is_admin_to_users_table.php`), read
defensively so a model hydrated without the column is treated as non-admin rather than 500ing.
`tests/Feature/PortedModelsTest.php:83-93` and `tests/Feature/ManagementPanelSmokeTest.php:44-56`
cover both directions.

### Multi-factor

`User` implements `HasAppAuthentication` and `HasAppAuthenticationRecovery`
(`app/Models/User.php:13`), with encrypted `app_authentication_secret` and
`app_authentication_recovery_codes` casts (lines 83-84) and a migration
(`2026_09_17_140000_add_multi_factor_columns_to_users_table.php`). **But neither panel provider calls
`->multiFactorAuthentication(...)`** — so the model side is wired and the panel side is not. TOTP is
effectively dormant.

### Authorization

**`app/Policies/` does not exist.** No policy, no `canAccess()` override on any resource, no
`Gate::` call anywhere in `app/Filament/`. Filament falls back to "allowed" — so panel access *is*
the only authorization boundary. Acceptable for a solo operator; not acceptable the moment a client
user or an employee gets an account.

### Plugins

*(b) in flight:* both `->plugins([...])` blocks were deleted. The panel switcher survives because it
is configured in `AppServiceProvider` rather than as a panel plugin.

---

## 5. Frontend

*(a) committed.*

- **Layout:** `resources/views/components/layouts/portfolio.blade.php` (185 lines). It emits the SEO
  block, the Bugsnag browser snippet, the favicon, Google Fonts (Syne + JetBrains Mono),
  `@vite('resources/css/app.css')`, and a per-request `<style>` block writing the seven palette CSS
  variables from `Settings::get('custom_colors')` (lines 85-97). That inline `<style>` is deliberate
  and documented at line 84 — it is the only per-request styling.
- **Section dispatch:** lines 118-158 loop `sections_order` from Settings and render one Blade
  component per key. Adding a section means component + `PortfolioController::defaultSectionsOrder()`
  (line 121) + the `SiteSettings` repeater options — exactly as `CLAUDE.md:71-74` says.
- **Components** (`resources/views/components/portfolio/`): `hero`, `services`, `now`, `projects`,
  `testimonials`, `experience`, `education`, `skills-marquee`, `about`, `contact`, `navigation`,
  `footer`, `seo`.
- **Hero / terminal:** `hero.blade.php`. Left column is CMS copy; the right column is a fake zsh
  window (lines 115-136) driven by an inline IIFE (lines 149-268) that types five commands. Content
  is injected via `window._heroProfile` (lines 19-30) from the `Profile` row and `SiteBranding`, so
  the *name, role, location, prompt and project names* are CMS-driven — but the **command list and
  their output are hardcoded** (lines 156-202). Stack badges fall back to a hardcoded array when no
  active `Skill` rows exist (line 107).
- **CSS:** `resources/css/app.css`, 2030 lines, `@import 'tailwindcss'` + `@theme` tokens that proxy
  the per-request CSS variables (lines 19-45) + hand-written BEM-ish component classes. The header
  comment (lines 13-16) records that this replaced an inline `tailwind.config` next to a Play-CDN
  script — that migration is done. `resources/css/filament/website/theme.css` is 18 lines of
  `@source` declarations for the panels.
- **SEO** — `resources/views/components/portfolio/seo.blade.php`, and it is genuinely complete:
  `<title>` (line 24), `meta description` (25), `canonical` (26), full Open Graph set (28-37),
  Twitter card that upgrades to `summary_large_image` when an OG image exists (39-44), and a
  JSON-LD `Person` block with `jobTitle`, `email`, `address` and `sameAs` from the profile's social
  links (46-68). Title and description resolve through `App\Support\SiteBranding::title()` /
  `metaDescription()` (`app/Support/SiteBranding.php:60-80`) with a profile fallback.
  `SitemapController` emits `/` plus every project that has a case-study body;
  `public/robots.txt` points at it. `tests/Feature/SeoTest.php` covers all of this.
- **Branding indirection:** `app/Support/SiteBranding.php` is the single source for the logo,
  terminal prompt and title, overridable per key from the `site_branding` Settings entry
  (lines 17-37). Its own docblock (lines 8-12) records that this replaced three disagreeing
  hardcoded copies.

---

## 6. Content audit

All findings below are in **(a) committed** files. Two caveats that matter:

1. **Seeders are not necessarily what the live site shows.** The production content lives in MySQL
   and has been editable through Filament for months. I did not query the database (out of scope for
   a read-only survey, and `.env` is permission-blocked). Every seeder line below is *the value the
   code would seed*, and is strong evidence for what the live row says, but must be confirmed in
   `/website`.
2. **Several of the briefed issues have already been fixed in code**, and the fix is recorded in the
   commits of 2026-09-17. Where that is so, I say so rather than reporting a stale defect.

| # | issue | file:line | current literal text | status |
|---|---|---|---|---|
| 1 | typo "Expierence" | — | **not present anywhere in the repo** | No match in `app/`, `resources/`, `database/`, `config/`, `routes/`, `public/`. Either already fixed or it lives only in a live DB row. **Check `/website` → Profile / Stats / Experiences.** |
| 2 | "1+ year" | `database/seeders/ProfileSeeder.php:34` | `'stat_2_value' => '1+ year',` (label at :35 = `'FilamentPHP experience'`) | Live defect in seed data. Should read "1+ year**s**" — or better, be replaced by a `Stat` row. |
| 2b | "2+ years" (the correct sibling) | `database/seeders/ProfileSeeder.php:36-37` | `'stat_3_value' => '2+ years', 'stat_3_label' => 'Development experience',` | Already correct — shows the "1+ year" above is an inconsistency, not a convention. |
| 3 | the "2.0" current-projects stat | root cause: `database/migrations/2026_09_17_100400_create_stats_table.php:14-15` | `// Integers only — the old profiles.stat_* columns were free text,` / `// which is how "2.0 Current Projects" happened.` | **Fixed in code.** `stats.value` is `unsignedInteger`. The legacy free-text source is `database/seeders/ProfileSeeder.php:32-33` (`'stat_1_value' => '2', 'stat_1_label' => 'Current projects'`), which `resources/views/components/portfolio/about.blade.php:10-18` still uses **as a fallback when the `stats` table is empty**. If no `Stat` rows have been created in production, the old path is still live. |
| 4 | brand: `florian.dev` | `app/Support/SiteBranding.php:22` | `'logo_text' => 'florian.dev',` | Default; overridable via the `site_branding` Settings key. |
| 4b | brand: `florian.dev` | `database/seeders/SettingsSeeder.php:14` | `'navbar_brand_text'  => 'florian.dev',` | Seeded navbar override — wins over `logo_text` (`navigation.blade.php:11`). |
| 4c | brand: `florian@dev` | `app/Support/SiteBranding.php:23-24` | `'terminal_user' => 'florian',` / `'terminal_host' => 'dev',` → composed to `florian@dev` at `SiteBranding.php:47` | Terminal prompt + window title (`hero.blade.php:124, 131`). |
| 4d | brand mismatch in comments | `app/Support/SiteBranding.php:10-11, 44, 50` | `the \`~/florian.dev\` logo … the \`florian@dev\` terminal prompt`; `/** "florian@dev" */`; `/** "~/florian.dev" */` | Docblocks only — will read as stale once the brand changes. |
| 4e | brand mismatch in a form hint | `app/Filament/Website/Pages/SiteSettings.php:104` | `->placeholder('florian.dev')` | Admin-facing placeholder. |
| 5 | the page `<title>` | `resources/views/components/portfolio/seo.blade.php:24` | `<title>{{ $title }}</title>` | Value from `app/Support/SiteBranding.php:60-69`: the `site_title` setting if set, otherwise `full_name . ' — ' . role`. With defaults (`SiteBranding.php:20` `'full_name' => 'Florian'`) that is **"Florian — Software Developer"**. |
| 5b | owner's full name | — | **"Florian Geense" appears nowhere as display copy.** | The surname exists only at `database/seeders/ProfileSeeder.php:21` (`'email' => 'florian.geense@gmail.com'`) and `:48` (`https://linkedin.com/in/floriangeense`). `SiteBranding` defaults, the hero headline, the footer copyright and the JSON-LD `Person.name` all render just "Florian". |
| 6 | BingoMC player numbers — claim A | `database/seeders/ProjectSeeder.php:15` | `…an economy system serving **thousands of concurrent players**.` | |
| 6b | BingoMC player numbers — claim B | `database/seeders/ExperienceSeeder.php:46` | `['responsibility' => 'Grew the platform to **18k+ monthly active players** and 2.2k+ Discord members'],` | **Inconsistent with 6.** "Thousands of concurrent" and "18k+ monthly active" are different orders of magnitude for the same product on the same page; concurrency claims are also the easiest thing for a prospect to doubt. Pick the monthly figure and drop the concurrency claim. The admin-facing hint at `app/Filament/Website/Pages/ProfilePage.php:143,149` already uses `18k+ monthly players` as its example. |
| 7 | timezone "UTC+1 / CET" | `app/Filament/Website/Pages/ProfilePage.php:83` | `->placeholder('UTC+1 / CET'),` | **Mostly fixed.** It survives only as a Filament placeholder. The fix is `app/Models/Profile.php:50-59`, which renders an IANA zone with a *live* abbreviation so it follows DST — the reason is documented at `Profile.php:42-48`. |
| 7b | the value that is actually seeded | `database/seeders/ProfileSeeder.php:23` | `'location_timezone' => 'Europe/Amsterdam (CET/CEST)',` | **The fix does not apply to this value.** `Profile.php:54-56` passes any non-IANA string through verbatim, and `"Europe/Amsterdam (CET/CEST)"` is not an IANA identifier. Set the field to plain `Europe/Amsterdam` and the live-abbreviation logic engages. |
| 8 | Vue.js claimed as a skill — the visible one | `database/seeders/ProfileSeeder.php:44` | `…My primary stack is <strong>PHP / Laravel</strong>, **paired with Vue.js on the frontend** and MySQL, Redis, and Docker in the backend…` | This is the about-section bio, rendered as raw HTML at `about.blade.php:35`. The only Vue claim that reaches a visitor by default. |
| 8b | Vue.js — hero fallback badges | `resources/views/components/portfolio/hero.blade.php:107` | `@foreach(['PHP','Laravel','MySQL','Redis','Docker','Git','Linux','Vue.js'] as $badge)` | Only renders when there are **no** active `Skill` rows (line 102). |
| 8c | Vue.js — marquee emoji map | `resources/views/components/portfolio/skills-marquee.blade.php:5` | `…'Docker'=>'🐳','Vue.js'=>'🔵','Vue'=>'🔵',…` | Lookup table only; harmless. |
| 8d | Vue.js — seeded skill row | `database/seeders/SkillSeeder.php:29` | `['name' => 'Vue.js', 'icon' => 'devicon-vuejs-plain', … 'category' => 'frontend', 'order' => 1],` | **Seeded inactive** — `Vue.js` is absent from the `$live` allow-list at `SkillSeeder.php:69-74`, so it does not appear in the marquee. |
| 8e | Vue.js — admin option lists | `ProfilePage.php:188`, `Experiences/Schemas/ExperienceForm.php:162`, `Projects/Schemas/ProjectForm.php:123, 142` | e.g. `->placeholder('+ Vue.js, MySQL, Docker')`, `'PHP', 'Laravel', 'Vue.js', 'React', …`, `'vue' => 'Vue',` | Admin-only; not visitor-facing. |
| 9 | Tuneroom "better than Spotify" | `database/seeders/NowItemSeeder.php:26` | `'description' => 'Working on Tuneroom — a shared music listening experience, **built better than Spotify\'s version**',` | Reads as an unsupported swipe at a named competitor; poor fit for a B2B company site. |

### Additional content findings (not in the brief, but same class)

| issue | file:line | current text |
|---|---|---|
| Filament version claim is a major behind | `database/seeders/NowItemSeeder.php:31` | `'Building this portfolio CMS with Laravel, **Filament v4**, and a fully custom Blade frontend.'` — the repo is on Filament 5.8.2 |
| "Now" item contradicts the repositioning framing | `database/seeders/NowItemSeeder.php:16` | `'Starting up my own Software Company'` — fine as a personal note, reads oddly on a company site that is already selling |
| subtitle still describes portfolio work | `database/seeders/ProfileSeeder.php:18` | `'subtitle' => 'Currently building portfolio tooling with Laravel & Filament.'` |
| tagline is developer-voiced, not company-voiced | `database/seeders/ProfileSeeder.php:17` | `'I build web applications with a focus on reliability, performance, and clear code.'` |
| stack card names a personal stack | `database/seeders/ProfileSeeder.php:39-40` | `'about_stack_primary' => 'PHP / Laravel'`, `'about_stack_secondary' => '+ FilamentPHP, MySQL, Tailwind'` |
| seeded admin has a hardcoded password | `database/seeders/DatabaseSeeder.php:30-37` | `['email' => 'florian@admin.dev']`, `'password' => Hash::make('password')` — and **no `is_admin => true`**, so the seeded admin cannot actually reach `/management` |
| Bugsnag browser key hardcoded in Blade | `resources/views/components/layouts/portfolio.blade.php:59-60` | `Bugsnag.start({ apiKey: '1d5f0d…' })` (truncated here) — a browser notifier key is public by design, but it belongs in config, not in a committed view |
| stock Laravel README | `README.md` (all 59 lines) | the unmodified `laravel/laravel` boilerplate, sponsors and all |
| dead import | `database/seeders/SettingsSeeder.php:5` | `use App\Models\Setting;` — no such class exists (the model is `Settings`) |

---

## 7. Integrations and infrastructure

*(a) committed.* **I could not read `.env` — it is permission-blocked, as `CLAUDE.md:39` warns.
Everything below is inferred from `config/`, and variable names only are listed. No values were read
or printed.**

- **Mail.** Stock `config/mail.php` — smtp / ses / postmark / resend / sendmail / log / array
  transports. **No Mailcoach integration in this repo.** The only Mailcoach reference is a string in
  seeded project copy (`database/seeders/ProjectSeeder.php:64, 66` — the Roadtrip-events client site
  uses it). Outbound mail is one `Mailable`: `App\Mail\ContactNotification`, sent to
  `Profile::first()->email` from `PortfolioController::submitContact()` (`PortfolioController:175-179`).
  Vars: `MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_URL`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`,
  `MAIL_PASSWORD`, `MAIL_EHLO_DOMAIN`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_LOG_CHANNEL`,
  `MAIL_SENDMAIL_PATH`, `POSTMARK_API_KEY`, `POSTMARK_MESSAGE_STREAM_ID`, `RESEND_API_KEY`.
- **Error tracking: Bugsnag, on both ends.** Server side: `bugsnag/bugsnag-laravel`, provider
  registered manually at `bootstrap/providers.php:4`, OOM bootstrapper at `bootstrap/app.php:9`,
  and a `bugsnag` log channel that is part of the default stack —
  `config/logging.php:57` `'channels' => explode(',', (string) env('LOG_STACK', 'single,bugsnag'))`,
  driver at `config/logging.php:61-63`. **There is no `config/bugsnag.php` in this repo**, so the
  notifier reads its key straight from the environment (`BUGSNAG_API_KEY`). Browser side: the
  CloudFront snippet at `layouts/portfolio.blade.php:56-61` with a literal key.
- **Analytics: first-party only.** `PortfolioController::trackPageView()` (lines 137-157) writes a
  `PageView` row per request, skipping private/reserved IPs and UA-matched bots. `App\Jobs\ResolveCountry`
  backfills a country. Surfaced by `PageViewsChart`, `TopPagesWidget`, `TopCountriesWidget`,
  `DashboardStatsWidget`. No Google Analytics, Plausible, Fathom or any third-party tag.
- **Storage.** Stock `config/filesystems.php`; default `env('FILESYSTEM_DISK', 'local')` → `local`
  rooted at `storage/app/private`. `public/storage` → `storage/app/public` symlink exists.
  **No `FileUpload` field anywhere sets `->visibility('public')`** (`SiteSettings.php:139, 163`,
  `ProjectForm.php:163`, `ExperienceForm.php:70`, `SkillForm.php:43`) and no `config/filament.php` is
  published. So uploads land on whatever `FILESYSTEM_DISK` says; if it is `local`, uploaded favicons,
  OG images, screenshots and logos are written outside the web root while `Storage::url()` emits a
  URL that will 404. **Unverified — depends on `.env`.** If production works today, `.env` must set
  `FILESYSTEM_DISK=public`. Vars: `FILESYSTEM_DISK`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`,
  `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`.
- **Queues.** Stock `config/queue.php`. One queued job exists (`ResolveCountry`). Vars:
  `QUEUE_CONNECTION`, `DB_QUEUE*`, `REDIS_QUEUE*`, `SQS_*`, `QUEUE_FAILED_DRIVER`.
- **Scheduler.** *(a)* `routes/console.php` contained only the `inspire` stub. *(b) in flight:* five
  scheduled commands (see §9).
- **Cache / session.** Stock. Vars: `CACHE_STORE`, `CACHE_PREFIX`, `DB_CACHE_*`, `MEMCACHED_*`,
  `REDIS_CACHE_*`, `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_PATH`,
  `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`, `SESSION_PARTITIONED_COOKIE`.
  Note `SESSION_DOMAIN` — relevant if subdomains are ever added.
- **Database.** MySQL. Vars: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
  `DB_PASSWORD`, `DB_SOCKET`, `DB_CHARSET`, `DB_COLLATION`, `DB_URL`, `MYSQL_ATTR_SSL_CA`.
- **`nunomaduro/essentials`** is installed with **no published `config/essentials.php`**, so its
  defaults apply: `ShouldBeStrict => true` (strict models — `preventLazyLoading`,
  `preventSilentlyDiscardingAttributes`, `preventAccessingMissingAttributes`),
  `Unguard => false`, plus aggressive prefetching, automatic eager loading and forced HTTPS in
  production. This is the direct cause of the in-flight test failures in §8.
- **Also referenced but unused in code:** `LOG_SLACK_WEBHOOK_URL`, `PAPERTRAIL_*`,
  `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL` (stock config only).

*(b) in flight* adds: `SITE_BRIDGE_CLAIM_TTL`, `SITE_BRIDGE_MAX_KEYS`, `SITE_BRIDGE_INTERVAL_UNLOCKED`,
`SITE_BRIDGE_INTERVAL_LOCKED`, `SITE_BRIDGE_STALE_AFTER`, `SITE_BRIDGE_HEARTBEAT_RETENTION_DAYS`,
`SITE_BRIDGE_ALERT_NTFY_URL`, `SITE_BRIDGE_SIGNING_KEYPAIR`, `SITE_BRIDGE_DIRECTIVE_MAX_AGE`
(`config/site-bridge.php`), and `BUGSNAG_AUTH_TOKEN`, `BUGSNAG_ORGANIZATION_ID`, `BUGSNAG_API_URL`,
`BUGSNAG_TIMEOUT`, `BUGSNAG_MAX_PROJECT_PAGES` (`config/bugsnag-hub.php`). Alerting is push-to-ntfy
(`app/SiteBridge/HealthAlerts.php`).

---

## 8. Quality and deploy

### Test DB pin — verified first

`phpunit.xml:26-27` sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. `tests/Pest.php:14-16`
applies `RefreshDatabase` to everything under `Feature`. Running the suite therefore cannot touch
MySQL. I verified this before running anything.

### Results

**(a) committed scope — green.** Running the ten pre-repositioning-through-HEAD test files:

```
Tests:  93 passed (189 assertions)   Duration: ~6s
```

Coverage areas (61 `it()` blocks, 93 tests after datasets):

| file | tests | covers |
|---|---|---|
| `tests/Feature/HomepageTest.php` | 12 | rendering, `is_posted` visibility, services/testimonials/education sections, work-vs-education split, stat pluralisation, auto-sourced stats, the live timezone abbreviation, footer link accessible names, mobile-nav `aria-hidden`, honeypot hidden from AT and keyboard, single `<h1>` |
| `tests/Feature/CaseStudyTest.php` | 11 | slug generation and de-duplication, 404s for missing body / unposted / unknown slug, per-page title+description+canonical, fallbacks, attached testimonials, card linking |
| `tests/Feature/ContactSpamTest.php` | 8 | genuine message emails the owner; honeypot, time-trap, missing timestamp, links, keywords and email-as-name all quarantine silently; validation still fires |
| `tests/Feature/SeoTest.php` | 5 | canonical/OG/Twitter tags, JSON-LD `Person` with `sameAs`, branded title, branded terminal prompt, robots.txt → sitemap |
| `tests/Feature/WebsitePanelSmokeTest.php` | 19 (dataset) | renders all 19 `/website` pages as an authenticated user |
| `tests/Feature/ManagementPanelSmokeTest.php` | 8 | panel registration, panel separation, login page, auth redirect, admin render, non-admin 403, lead widgets, no widget leakage |
| `tests/Feature/PortedModelsTest.php` | 7 (+9 dataset) | every ported model persists via factory, enum casts, client relationships, invoice total recalculation, `bridge_site_id` on create, health status, `is_admin` panel gate |
| `tests/Feature/DashboardChartsTest.php` *(b)* | 7 | weekly/daily bucketing, filter spans, theme-owned chart colours, polling disabled, bar type |
| `ExampleTest.php` ×2 | 2 | boilerplate, could be deleted |

**(b) in-flight scope — red.** The newly-added test directories fail hard. Running the whole suite
at the default 128 MB blows up with a fatal OOM inside Laravel's HTML error renderer — exactly the
Filament trap documented at `CLAUDE.md:108-116`. At `memory_limit=1024M` the real picture is:

```
Tests:  77 failed, 57 passed (110 assertions)
```

| failing file | failures |
|---|---|
| `tests/Feature/Filament/Resources/UserResourceTest.php` | 23 |
| `tests/Feature/Filament/Resources/CommandCenterResourcesTest.php` | 18 |
| `tests/Feature/SiteBridge/HeartbeatTest.php` | 8 |
| `tests/Feature/SiteBridge/EnrolmentTest.php` | 6 |
| `tests/Feature/Billing/BillingFeaturesTest.php` | 6 |
| `tests/Feature/SiteBridge/LockControlTest.php` | 4 |
| `tests/Feature/Bugsnag/SyncBugsnagActionTest.php` | 4 |
| `tests/Feature/SiteBridge/HeartbeatRetentionTest.php`, `Models/ClientTest.php`, `Filament/Widgets/ShouldInvoiceWidgetTest.php`, `Filament/Components/GlobalSearchTest.php` | 2 each |

**Root cause is identifiable and singular.** Every ported command-centre model —
`Client`, `Website`, `Invoice`, `InvoiceLine`, `Contact`, `Retainer`, `TimeEntry` — declares
**neither `$fillable` nor `$guarded`**. In the source repo that was safe because models were
unguarded; here `nunomaduro/essentials` ships `ShouldBeStrict => true` and `Unguard => false`
(`vendor/nunomaduro/essentials/config/essentials.php:154, 169`) and this repo has **not published
`config/essentials.php`**. So any direct `Client::create([...])` throws:

```
MassAssignmentException: Add [status_before_lock] to fillable property to allow mass assignment on [App\Models\Client].
```

The committed `PortedModelsTest` passes only because Laravel factories wrap creation in
`Model::unguarded()`. The fix is one of: publish `config/essentials.php` with `Unguard => true`, or
add `$fillable`/`$guarded = []` to the seven ported models. **This is an in-flight defect; it is not
a problem with the committed portfolio.**

### Tooling

- **Pint** installed (1.32.1). **No `pint.json`** — it runs the default `laravel` preset.
  `CLAUDE.md:92` requires `vendor/bin/pint --dirty` before finishing.
- **Larastan / PHPStan: not installed.** No `phpstan.neon`. (The `phpstan/phpdoc-parser` in vendor is
  a transitive dependency, not the analyser.)
- **Rector: not installed.** No `rector.php`.
- **CI: none.** No `.github/`, no pipeline of any kind. Nothing runs the suite except a human.
- **Deploy: nothing in the repo.** Forge-only; see §2.
- **`.env.example` is the untouched Laravel stock file** (66 lines). It documents `DB_CONNECTION=sqlite`
  while the app runs MySQL, and documents **none** of `BUGSNAG_API_KEY`, `LOG_STACK`,
  `FILESYSTEM_DISK=public`, or any `SITE_BRIDGE_*` / `BUGSNAG_*` hub variable. A fresh clone
  following `composer setup` would not produce a working install.
- **`packages/site-bridge/`** ships its own `phpunit.xml` and 23 tests across 4 files, but is **not
  in the root `composer.json`** (no path repository, no `require`) and its `tests/` are outside the
  root `phpunit.xml` testsuites. Those tests are not run by anything here.

---

## 9. History and state

### Last 30 commits

The repository has three clear eras.

- **Nov 2025 (`bd07932` … `beeee85`)** — settings/theming work on the original portfolio. Four
  near-identical commits titled "Fix namespace casing in SiteSettings.php for consistency"
  (`a7ed172`, `a16b109`, `d21ba4e`, `beeee85`) suggest a case-sensitivity fight with a Linux host.
- **Apr–Aug 2026** — contact form + mail (`9cec8b6`, `29c18b9`, three commits titled just "fix: bug"),
  page-view tracking and country resolution (`2343ba6`), the NowItem resource and Profile page
  (`a929ebb`), a run of UI/accessibility passes (`e475815`, `8de7b6e`, `1cee198`), dashboard widgets
  (`51d6de5`, `1fe8027`, `f463c25`), SPA + profile + panel switch (`05d8e9d`), contact anti-spam
  (`f37a70b`), Bugsnag (`b644c38`, `1350c0d`).
- **2026-09-17, four commits in one day** — the repositioning: Laravel 13 + Filament 5 and the panel
  split (`f17cd3e`), the command-centre domain port (`e171551`), services/testimonials/stats/case
  studies + the move off the Tailwind CDN (`b0e1305`), and the Management dashboard + `CLAUDE.md`
  (`6721606`).

Commit hygiene is uneven — three commits named "fix: bug", four identical namespace-casing commits —
but the recent four are large, coherent and well-described.

### Uncommitted changes

**(a)-adjacent modifications** — 16 tracked files, all part of (b)'s work:

| file | change |
|---|---|
| `composer.json` / `composer.lock` | removes `johnrivera7/filament-custom-dashboard-widgets` and `pxlrbt/filament-environment-indicator` |
| `app/Providers/Filament/{Website,Management}PanelProvider.php` | drops both `->plugins([...])` blocks; Management nav group `Clients` → `CRM` |
| `app/Filament/Website/Pages/OverviewDashboard.php`, `Management/Pages/ManagementDashboard.php` | drops `HasWidgetGrid`; the Management dashboard gains 5 new widgets |
| `app/Filament/{Website,Management}/Widgets/*` (5 files) | disables 5s polling on stats/chart widgets, converts both charts from tension-smoothed lines with hardcoded hex to theme-coloured bars, adds range filters |
| `app/Providers/AppServiceProvider.php` | adds the heartbeat rate limiter and a production signing-key guard; panel switch `slideOver()` → `simple()` |
| `bootstrap/app.php` | registers `routes/site-bridge.php` |
| `routes/console.php` | adds 5 scheduled commands: `invoices:mark-overdue` daily 06:00, `sites:check-health` every minute, `bugsnag:sync-errors` hourly, `site-bridge:rollup-heartbeats` daily 00:15, `model:prune` daily 00:30 |
| `tests/Feature/ManagementPanelSmokeTest.php` | reworked to assert widgets via `Livewire::test` now that they lazy-load |
| `CLAUDE.md` | conventions flipped from "don't add folders/docs without asking" to "create them as the work needs; still ask before a dependency" |

**(b) untracked** — roughly 120 new files:

| path | files | what |
|---|---|---|
| `app/Filament/Management/Resources/` | 45 | Clients (with Activities/Contacts/Invoices/Retainers/Websites sub-pages), Invoices, Retainers, TimeEntries, Users, Websites |
| `app/Filament/Management/Widgets/*.php` | 5 | Finance stats, revenue chart, should-invoice, uninvoiced work, site health |
| `app/SiteBridge/` | 7 | Ed25519 `LockDirectiveSigner`, `SiteBridgeKeys` (sha256-hashed `sb_live_` keys), `SiteBridgeEnrolment`, `HealthAlerts` (ntfy), 2 exceptions, `ClaimResult` |
| `app/Bugsnag/` | 4 | Data Access API client, sync, project DTO, exception |
| `app/Billing/` | 1 | `InvoiceFromWork` — bundles un-invoiced time entries into a draft invoice |
| `app/Console/Commands/` | 6 | `CheckSiteHealth`, `GenerateSigningKey`, `LinkBugsnagProjects`, `MarkOverdueInvoices`, `RollUpHeartbeats`, `SyncBugsnagErrors` |
| `app/Http/Controllers/SiteBridge/`, `app/Http/Middleware/` | 3 | claim + heartbeat endpoints, bearer-key middleware |
| `config/site-bridge.php`, `config/bugsnag-hub.php`, `routes/site-bridge.php` | 3 | |
| `packages/site-bridge/` | 29 | the **client-side** package (collectors, `EnforceLock` middleware, claim/heartbeat commands, directive verifier, its own tests) |
| `tests/` | 15 | the failing suites from §8 |

The code quality of (b) is visibly high — `declare(strict_types=1)` throughout, `final` classes,
generic-annotated relations, and substantial docblocks explaining *why* (e.g. why heartbeats are
rate-limited per key rather than per IP, why the signing key never auto-generates). It is simply not
finished or wired to the standard the rest of the repo holds (see §8 and §10).

### Dead code and unused artefacts

| item | why it's dead |
|---|---|
| `config/filament-widget-grid.php` | config for a plugin (b) uninstalled |
| `database/migrations/2026_09_17_143053_create_widget_grid_tables.php` | creates tables for the same uninstalled plugin |
| `resources/css/filament/website/theme.css:17-18` | `@source` two `vendor/` paths that **no longer exist** — `vendor/johnrivera7/...` and `vendor/pxlrbt/...` |
| `public/css/filament-environment-indicator/styles.css`, `public/css/johnrivera7/filament-custom-dashboard-widgets/filament-widget-grid-styles.css` | committed published assets of the removed plugins |
| `barryvdh/laravel-dompdf` | required, never imported anywhere in `app/` |
| `database/database.sqlite` | 114 KB stale artefact; the app is MySQL |
| `database/seeders/SettingsSeeder.php:5` | `use App\Models\Setting;` — class does not exist |
| `resources/js/app.js` + `bootstrap.js` | built by Vite but the public site does not load them (`layouts/portfolio.blade.php:79-82`) |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`, `tests/Pest.php:44-46` (`function something()`) | boilerplate |
| `packages/site-bridge/` | present as source, not required by the root `composer.json`, not in the root test suite |
| `Profile::$stat_1..3_*` columns | superseded by the `stats` table; kept only as the fallback at `about.blade.php:10-18` |
| `Project::scopeOngoing()` (`app/Models/Project.php:100`) | nothing calls it; visibility moved to `is_posted` |
| the `seasonal overlay` machinery | `layouts/portfolio.blade.php:166-183` still ships a snow-gif overlay engine; `SettingsSeeder.php:26` seeds `'overlay' => 'none'` and commit `bd07932` claims the setting was removed |
| **no unused routes** | all 58 resolve to real controllers/pages |

---

## 10. Risks

**High**

1. **No authorization beyond panel access.** No `app/Policies/`, no resource `canAccess()`, no gates.
   Any authenticated user gets **full CRUD** on every portfolio resource at `/website` — create,
   edit and delete projects, experiences, skills, services, testimonials, stats, and read every
   contact message. `/management` is gated only on `users.is_admin`, and once through, the same
   applies to clients, invoices and the site kill-switch. A single compromised or over-generously
   created account is total.
2. **Seeded admin with a known password.** `database/seeders/DatabaseSeeder.php:30-37` creates
   `florian@admin.dev` / `password`. If `db:seed` has ever been run on production, that account
   exists. (It does *not* set `is_admin`, so it cannot reach `/management` — but it can reach all of
   `/website`.) Verify against production and delete or rotate.
3. **MFA is half-wired.** The `User` model, the encrypted columns and the migration are all in place;
   neither panel provider enables `->multiFactorAuthentication(...)`. So the admin panels are
   password-only today while looking like they support TOTP.
4. **(b) The in-flight suite is red — 77 failures** from a single systemic cause (strict models with
   no `$fillable`). This must not be committed as-is. See §8.

**Medium**

5. **Tailwind build risk from the plugin removal.** `resources/css/filament/website/theme.css:17-18`
   `@source`s two vendor directories that no longer exist. I did not run `npm run build` (out of
   scope), so whether Tailwind 4 warns or errors on a missing `@source` path is **unverified** — but
   with `public/build` gitignored and no CDN fallback, a build failure on Forge means every page,
   public and panel, returns `Unable to locate file in Vite manifest`. Check this before the next
   deploy.
6. **Upload visibility.** No `FileUpload` sets `->visibility('public')` and no `config/filament.php`
   is published, so uploads follow `FILESYSTEM_DISK`. If that is `local`, files go to
   `storage/app/private` while `Storage::url()` emits a public URL. Unverified (`.env` blocked).
7. **Hardcoded Bugsnag browser key in a committed Blade file**
   (`layouts/portfolio.blade.php:59-60`). Public by design for a browser notifier, so not a secret
   leak — but it cannot be rotated or disabled per environment without a deploy, and it means local
   and staging both report into production's Bugsnag project.
8. **`.env.example` would not produce a working install** — stock file, `DB_CONNECTION=sqlite`, none
   of the app's real variables. Combined with `composer setup` (which runs `migrate --force`) this is
   a footgun for any second machine.
9. **No CI.** Nothing prevents a red suite reaching `main`; today's in-flight state is the proof.
10. **Analytics writes on every request.** `PortfolioController::trackPageView()` does a synchronous
    `PageView::create()` inside the homepage request. Cheap now; it becomes a write-per-pageview
    problem if traffic grows, and `page_views` has no pruning.

**Low**

11. **No debug routes and no obvious backdoors.** No Telescope, Horizon, Pulse, `dd()`, `dump()`,
    `ray()` or debug endpoints anywhere in `app/`, `routes/` or `config/`. `/up` is the stock health
    check. Admin paths (`/website`, `/management`) are predictable but authenticated and
    `robots.txt`-disallowed.
12. **No outdated packages with known issues** — everything is current (§1).
13. **Windows-only filesystem binding.** `AppServiceProvider::register()` swaps the `files` binding
    for `WindowsSafeFilesystem` on Windows only. Correct and documented, but it means local and
    production exercise different filesystem code.

---

## Turning this into a company site

The good news dominates: **the hard part is already built.** This is a CMS-backed marketing site with
a section-ordering system, an SEO layer, a lead-capture form with real anti-spam, case-study pages
and green tests. What has to change is mostly *content and framing*, not architecture.

### What stays as-is

- The whole rendering architecture: `PortfolioController` → `sections_order` → one Blade component
  per section. Adding a "Packages" or "Pricing" section is a component + one `defaultSectionsOrder()`
  entry + one `SiteSettings` repeater option.
- `Service` (`title`, `description`, `icon`, `sort_order`, `is_active`) — this model **is** the
  services section and needs no schema change to list "Platform websites / Platform webshops /
  Custom software".
- `Project` + `/work/{slug}` case studies — already the right shape for client proof. `project_type`
  already distinguishes `client` from `personal`.
- `Testimonial`, optionally attached to a `Project` and rendered on both the homepage and the case
  study — exactly what social proof needs.
- `Stat` with `auto_source` — safe, self-updating numbers.
- The SEO component, sitemap, robots.txt, JSON-LD scaffolding.
- `ContactMessage` + the anti-spam layer + the Management lead widgets — this is already a working
  lead pipeline.
- The dark terminal aesthetic *can* stay if the audience is technical buyers. Flag it as a
  positioning decision, not a technical one: a plumber or a dental practice buying a "platform
  website" will not read a zsh window as credibility. The terminal is one component
  (`hero.blade.php`) behind one `sections_order` key, so it is cheap to swap or make conditional.

### What must change

**Identity.** Every occurrence in the audit table row 4 — `florian.dev`, `florian@dev`,
`~/florian.dev`. `SiteBranding` already centralises them and they are overridable from
`/website → Site settings`, so this is a content edit plus a tidy-up of the defaults in
`app/Support/SiteBranding.php:20-29` and `database/seeders/SettingsSeeder.php:14`. Decide the legal
entity name and whether the site speaks as "Florian Geense" or as a company.

**Voice.** The site currently says "I". A company site selling packages says "we", names a price or a
range, and names an outcome. Concretely: `Profile.tagline`, `Profile.subtitle`, `Profile.about_text`,
`Profile.contact_intro`, `Profile.footer_tagline`, all three `stat_*` pairs, and the hardcoded
headings `About me` / `/ me`, `What I build`, `Kind words`, `Now` / `/ current focus`.

**The "Now" section is the wrong shape for a company site.** `NowItem` is a personal-blog convention.
Either drop it from `sections_order` or repurpose it as "What we're shipping". Its current seeded
contents (Minecraft server, "better than Spotify", "Filament v4") are all liabilities on a B2B page.

**Claims discipline.** Fix or remove: the Vue.js bio claim (8), the two incompatible BingoMC figures
(6/6b), "1+ year" (2), and the Spotify comparison (9). For an SMB buyer, "1+ year FilamentPHP
experience" actively undersells; consider replacing all three legacy `stat_*` values with `Stat`
rows using `auto_source` (`projects_count`, `years_experience`) so they cannot drift again.

**Portfolio → proof.** BingoMC (a Minecraft server) and Tuneroom read as hobby projects. Techno
Events and Roadtrip-events.nl are real client/product work and should lead. Mark hobby work
`is_posted = false` or move it behind a clearly-labelled "Side projects" section.

### Where the new pieces fit

| new thing | where it goes | effort |
|---|---|---|
| **Three package tiers** (platform website / platform webshop / custom software) | `Service` rows already render as cards. For pricing, either add `price_from` / `billing_interval` / `features` (JSON) columns to `services` and extend `services.blade.php`, or create a `Package` model + component + `sections_order` key alongside `Service`. | half a day either way |
| **Package detail pages** | Mirror `/work/{slug}`: a route, a controller method, a Blade view, a slug column. `CaseStudyTest` is the template for its tests. | a day |
| **Case studies as sales assets** | Already exist. Needs an `outcome`-forward layout and a client logo/testimonial per project — `Project.outcome` and `Testimonial.project_id` are both already there. | content only |
| **Pricing / FAQ / process sections** | New Blade components + `defaultSectionsOrder()` entries + `SiteSettings` options. The pattern is established and mechanical. | a day for all three |
| **Lead qualification** | `ContactMessage` has `name`/`email`/`message` only. Selling packages wants "which package", "budget", "timeline" — add columns + form fields, and extend the spam heuristics (`PortfolioController::looksLikeSpam`) accordingly. The Management lead widgets then segment by package. | half a day |
| **Legal pages** (terms, privacy, cookie, KvK/BTW footer) | **None exist.** A Dutch company selling to SMBs needs at minimum an imprint, terms and a privacy statement. No route, no model, no view. | a day, mostly text |
| **Editable section headings** | The hardcoded headings in §3 become a blocker the moment marketing copy needs iterating. A `heading`/`eyebrow` pair on a `Section` settings entry would close it. | half a day |

---

## Fit with the Site Platform and site-bridge

### Where the brief's framing no longer holds

The brief describes a *future separate* multi-tenant "Site Platform" and a *separate* "Agency
Command Center", and asks what it would cost to migrate florianphp into the former as its first
tenant. **That framing is superseded.** As of today the Command Center is being ported *into this
repo* — that is exactly what (b) is — and the target shape is three panels in one application:

- `/portfolio` — this site's own CMS content
- `/website` — **multi-tenant, with `Website` as the Filament tenant**
- `/management` — the cross-client back office

Assessed against *that* plan, here is the real state.

### Already done

- **The command-centre domain is here and committed.** `Client`, `Website`, `Invoice`, `InvoiceLine`,
  `Retainer`, `TimeEntry`, `Contact`, the four enums, the site-bridge tables, factories and 45
  migrations all landed in `e171551`. `PortedModelsTest` proves they persist and relate.
- **The site-bridge hub is written** (b): claim + heartbeat endpoints, Ed25519 directive signing,
  hashed `sb_live_` keys, per-key rate limiting, heartbeat rollups and pruning, ntfy alerting, the
  Bugsnag Data Access sync, and five scheduled commands. This is the bulk of the work and it is done.
- **`/management` exists** as a panel with an `is_admin` gate, a working dashboard, and (in flight)
  45 files of resources.

### The `/website` → `/portfolio` rename is a head-on collision

The existing portfolio panel **is** id `website` at path `/website`
(`WebsitePanelProvider.php:31-32`), with 45 classes under `app/Filament/Website/`. The plan wants
that name for the *tenant* panel. So the rename is not cosmetic:

- `app/Filament/Website/` → `app/Filament/Portfolio/` (~45 files, namespace + `discover*()` paths)
- panel id `website` → `portfolio`, path `/website` → `/portfolio`, and `->default()` moves
- `resources/css/filament/website/theme.css` path (referenced by **both** providers and by
  `vite.config.js:8`)
- `public/robots.txt` disallow lines
- `tests/Feature/WebsitePanelSmokeTest.php` (19 hardcoded paths) and
  `tests/Feature/ManagementPanelSmokeTest.php:26, 94`
- the panel-switch labels in `AppServiceProvider.php:51-58`
- `CLAUDE.md:59-76`

`CLAUDE.md:145-147` warns that Windows `sed`/`perl` mangle PHP namespaces — this rename must be done
with a PHP script or the Edit tool. **Estimate: half a day, mechanical but wide.**

### What multi-tenancy on `Website` actually requires

This is the genuinely large piece, and almost none of it exists yet.

1. **There is no `User` ↔ `Website` relationship at all.** Filament tenancy needs
   `User implements HasTenants` with `getTenants(Panel $panel)` and `canAccessTenant(Model $tenant)`,
   backed by a real relation. Today `Website belongsTo Client` and `User` relates to nothing. You
   need either a `website_user` pivot or `User belongsTo Client` + tenancy through the client.
   **New migration, new relations, new `User` contract.**
2. **No portfolio content model is tenant-scoped.** `Profile`, `Project`, `Experience`, `Skill`,
   `NowItem`, `Service`, `Testimonial`, `Stat`, `Settings`, `ContactMessage`, `PageView` — none has a
   `website_id`. Two honest options:
   - **(A) Keep them single-tenant.** `/portfolio` stays exactly as it is, managing florianphp.com's
     own content, and the multi-tenant `/website` panel manages a *different* set of per-client
     models built for the platform product. Much less churn; the existing 93 tests keep passing.
     The portfolio is then *not* "the first tenant" — it is a sibling.
   - **(B) Genuinely make florianphp.com tenant #1.** Every one of those 11 tables gets `website_id`,
     every query gets scoped, `Settings`' static per-request cache (`app/Models/Settings.php:15-24`)
     gets keyed by tenant, `SiteBranding` (which reads `Settings` globally) gets tenant-aware, and
     `PortfolioController::index()`'s `Profile::first()` (line 27) becomes a host-based resolve.
     Substantially more work, and it puts the one site that currently earns attention at risk.
3. **Routing is single-domain.** `routes/web.php` has no `domain()` and there is no
   host → `Website` resolver. Serving many tenant sites from this codebase needs domain routing,
   a host middleware, and per-tenant `SESSION_DOMAIN` thinking. Nothing exists.
4. **Authorization would stop being optional.** With no policies at all (§10), a tenant panel would
   have nothing but `canAccessTenant()` between one client and another's data.

**Estimate:** option (A) is roughly a week on top of the rename. Option (B) is materially more —
call it three to four weeks — and I would want a much more specific product definition before
committing to it.

### Installing site-bridge as-is on florianphp.com

This is the cheap win and I'd do it first. `packages/site-bridge/` is the **client** half and it is
already sitting in the repo — collectors, `EnforceLock` middleware, `ClaimCommand`,
`HeartbeatCommand`, `DirectiveVerifier`, its own migrations, 23 of its own tests. It is simply not
wired in: the root `composer.json` has no `repositories: [{ "type": "path", "url": "packages/site-bridge" }]`
entry and does not `require agency/site-bridge`.

To make florianphp.com the first *monitored* site:

1. Add the path repository + `require` to the root `composer.json`, `composer update agency/site-bridge`.
2. Publish/run the package's two migrations (`site_bridge_credentials`, `site_bridge_lock`).
3. Create a `Website` row for florianphp.com in `/management`, issue a claim code, run
   `php artisan site-bridge:claim` — and note the amusing consequence that the hub and the client
   would be **the same application talking to itself over HTTP**. Worth confirming that is intended
   rather than an accident of the merge; it is harmless but it makes the `EnforceLock` middleware a
   foot-gun (the hub could lock itself out of `/management`).
4. Generate the signing keypair: `php artisan site-bridge:generate-key`, pointed at a path Forge
   shares across releases (`config/site-bridge.php:43-53` documents exactly this) — and back it up.
   `AppServiceProvider::guardSigningKey()` logs `critical` in production if it is missing.
5. Set `SITE_BRIDGE_ALERT_NTFY_URL` to get red/recovered pushes.

**Estimate: one to two hours**, once (b) is committed and green.

### Order I'd suggest

1. Fix (b)'s 77 test failures (publish `config/essentials.php` with `Unguard => true`, or add
   `$fillable`) and the dead widget-grid/environment-indicator leftovers. Commit.
2. Wire site-bridge into florianphp.com itself. Small, proves the loop end to end.
3. Do the `/website` → `/portfolio` rename while the panel is still small.
4. Do the content work from "Turning this into a company site" — it is the only thing on this list
   that makes money, and it needs no tenancy.
5. Only then decide between tenancy option (A) and (B), with a written product definition in hand.

---

## Open questions for the owner

1. **`gallery.florianphp.com`** — not in this codebase at all. Separate repo, separate Forge site, or
   retired? If it is to be folded in, that is a fourth thing this repo would serve.
2. **What does the live database actually say?** The seeders are months old and everything has been
   editable through Filament since. Specifically: does the live `profiles` row still contain
   "1+ year", the Vue.js bio and `Europe/Amsterdam (CET/CEST)`? Do any `stats` rows exist, or is the
   legacy `stat_*` fallback still what renders? **And where is "Expierence"?** It is nowhere in the
   source, so if it is visible on the site it is a DB value.
3. **Has `db:seed` ever run on production?** If so, `florian@admin.dev` / `password` exists (§10).
4. **What is `FILESYSTEM_DISK` set to in production?** Determines whether uploaded OG images,
   favicons and screenshots are actually reachable (§7). I could not read `.env`.
5. **The terminal hero — keep or retire?** It is charming and it is well built, but it speaks to
   developers. Who is the buyer for "platform websites"?
6. **Is `barryvdh/laravel-dompdf` intended for invoice PDFs?** Nothing uses it today.
7. **Company identity:** legal entity name, KvK/BTW for the footer, and does the site speak as a
   person or a company? Everything in audit row 4 and the voice changes depend on this answer.
8. **Pricing on the page or not?** Determines whether packages need a `Package` model with price
   columns or just richer `Service` rows.
9. **Tenancy option (A) or (B)?** Sibling panels, or florianphp.com genuinely as tenant #1? This is
   the single biggest fork in the plan and everything downstream depends on it.
10. **Hub and client on the same host** — is florianphp.com meant to run the site-bridge client
    against its own hub, or is it exempt and monitored another way?
11. **Does anything besides Forge need to know about the new scheduled commands?**
    `sites:check-health` runs every minute; the Forge scheduler must be enabled for this site.
12. **Legal pages** — is there existing terms/privacy text to import, or does it need writing?
