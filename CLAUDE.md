# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

InsightSW is an internal operations/analytics dashboard for Shoe Workshop (shoeworkshop.id). It pulls data from Meta Ads, Sleekflow CRM, the company's own "pusat" API (`info.shoeworkshop.id`) and a Google Sheet, and presents it per division: Marketing, CS, CX/QC, Finance, Gudang (warehouse), Workshop.

Stack: PHP 8.2+, Laravel 12, Livewire 3 (class-based components) + Volt (auth/profile pages only), Tailwind 3, Alpine, Vite. PDF via `barryvdh/laravel-dompdf`, Excel via `maatwebsite/excel`.

UI copy, code comments, commit messages and the docs (`README.md`, `DOKUMENTASI.md`) are in Indonesian; domain terms such as SPK, piutang, sortir, gudang, omset are kept untranslated in identifiers and routes. Match that.

## Commands

```bash
composer run setup        # install, .env, key, migrate, npm install + build
composer run dev          # serve + queue:listen + pail + vite, concurrently
npm run dev               # Vite only (README/DOKUMENTASI wrongly say this starts everything)
npm run build

composer run test         # config:clear + php artisan test
php artisan test --filter=ProfileTest
php artisan test tests/Feature/Auth/AuthenticationTest.php
vendor/bin/pint           # code style (no custom pint.json)

php artisan migrate --seed   # seeds admin@algoritma.com / password (role Admin)
php artisan schedule:work    # run the scheduler locally
```

The site is also served by Laravel Herd from this directory. The local `.env` uses MySQL; `.env.example` and the test suite use SQLite (`:memory:` in `phpunit.xml`).

Tests are only the Breeze scaffolding tests. Screen-rendering tests need `public/build/manifest.json`, so run `npm run build` first; a couple still fail because the app diverged from Breeze defaults (e.g. `/` redirects to `/dashboard`). There are no tests for services or Livewire components.

### Sync commands

```bash
php artisan sync:meta-ads [--preset=last_3d | --from=Y-m-d --to=Y-m-d] [--account=act_...]
php artisan sync:sleekflow-analytics [--days=7 | --from= --to=] [--include-today]
php artisan sync:finance [--start= --end=]      # no options = rolling 60 days
php artisan qc:capture-snapshot                 # morning QC baseline from the Google Sheet
```

The schedule lives in `bootstrap/app.php` (`withSchedule`), not `routes/console.php`: QC snapshot at 08:00, Meta Ads `last_3d` at 01:00 plus hourly `today` 07:00–23:59, Sleekflow analytics 7-day reconcile at 01:30 plus hourly. Output is appended to `storage/logs/scheduler-sync.log`. `sync:finance` is not scheduled.

## Architecture

### Request flow

`routes/web.php` maps each URL directly to a full-page Livewire component in `app/Livewire/` (view in `resources/views/livewire/<kebab-name>.blade.php`, layout `resources/views/layouts/app.blade.php`). Components stay thin and delegate to `app/Services/`. The only controllers are the `*ExportController` classes, each with a `printPdf` action rendering a `resources/views/exports/*-pdf.blade.php` view through dompdf; Excel exports are in `app/Exports/`.

### Two kinds of data access

Services fall into two groups, and a page's behaviour depends on which it uses:

- **Sync-to-DB** (`*SyncService`, `MetaAdsService`, `SleekflowService`): fetch from the external API and bulk `upsert` into local tables in chunks of 500, keyed on a unique business column (`spk_number`, `sleekflow_id`, `item_id`, ad id + date, ...). Pages then query Eloquent models.
- **Live passthrough** (`*ApiService`, `DashboardService`): call the pusat API on each render and cache the JSON briefly; nothing is stored locally. The "Live" dashboards, piutang, shoerack, manifest and workshop data pages work this way, so they have no model or migration.

`MarketingSyncService::syncRange` is the piece that ties sources together: it builds the `daily_reports` rows that the marketing dashboard reads, taking spend from Meta (multiplied by `META_TAX_RATE`), revenue from `payment_syncs.paid_at`, and chat counts from Sleekflow data, while preserving the manually entered `budgeting` column.

### Sync triggering from the UI

Besides the scheduler, pages trigger syncs themselves: the Blade root has `wire:poll.<n>s="checkSync"`, and `checkSync` calls `SyncService::syncIfAllowed($module, $closure, $throttle)`. That helper uses two cache keys per module, `sync_last_time_{module}` (throttle) and `sync_lock_{module}` (atomic lock). Console commands deliberately reuse the same key names (e.g. `sync_lock_meta_ads_sync`) so cron and page-triggered syncs don't overlap and the page's "last sync" indicator stays correct. Keep the names aligned when adding a sync. Locks depend on the cache store (`database` by default).

### External integrations

| Source | Service(s) | Config |
| --- | --- | --- |
| Meta Graph API v19.0 | `MetaAdsService` | `services.meta.*` |
| Sleekflow | `SleekflowService` | `services.sleekflow.*` (daily analytics: `GET {base_url}/analytic?startDate=&endDate=`, Y-m-d, inclusive) |
| Pusat API (`X-API-KEY` header) | Finance/Payment/Warehouse/Workshop services, `DashboardApiService` | `services.dashboard.*` |
| Google Sheets CSV export (public link, no auth) | `GoogleSheetService` | sheet URL + gid hardcoded in callers |

Things that are easy to get wrong:

- Meta returns several rows per ad per day (placement breakdowns). `MetaAdsService::syncItem` sums them within a run using an in-memory tracker rather than overwriting; a naive upsert undercounts.
- Meta attribution changes for a few days after the fact, hence the `last_3d` re-pull. Don't shrink it to "today only".
- The Sleekflow API key only works on the `azurewebsites.net` host; `api.sleekflow.io` returns 401.
- Sleekflow timestamps are UTC and are converted with `addHours(7)` in `SleekflowService::parseToWib`. `config/app.php` hardcodes `Asia/Jakarta`.
- Sleekflow contact sync over the API is slow and rate-limited; `MarketingSyncService` skips it and relies on already-imported contacts (`sleekflow_contacts.sql` dump).
- Config access is inconsistent: several services call `env()` directly with hardcoded fallbacks (`FinanceSyncService`, `PaymentSyncService`, `MarketingSyncService`, `WorkshopCxApiService`) instead of `config('services.*')`. These return null under `config:cache`. `PAYMENT_SYNC_API_KEY` and `META_ADS_ACCESS_TOKEN` are used but missing from `.env.example`.

### Roles and access

`users.role` is a plain string: `Admin`, `Editor`, `Viewer`, `CS`, `Leader CS`, `CX`, `Finance`, `Gudang`. Access is defined in three places that must be changed together:

1. `role:A,B,...` middleware groups in `routes/web.php` (`RoleMiddleware`, which redirects an unauthorised user to their own division's landing page rather than returning 403).
2. Gates in `AppServiceProvider::boot` (`access-marketing`, `access-cs`, `access-finance`, ...).
3. `@can(...)` blocks in `resources/views/layouts/app.blade.php`, which build the sidebar.

Only `UserManager` calls `$this->authorize()` inside actions; other components rely on the route middleware alone. `Viewer` can open most pages, and write actions are not separately guarded.

### Shared page UI

The Gudang pages and the Sleekflow analytics page share one page chrome; new or migrated pages should use it instead of hand-rolled headers:

- `<x-ui.page-header title subtitle badge>`: title on the left, actions in the slot on the right. Do not use the layout's `header` slot for controls: it renders in the navbar, outside the Livewire component, so `wire:click` does nothing there and it never re-renders.
- `<x-ui.range-toolbar :start :end :max :presets refresh="method">`: Flatpickr range picker, "Perbarui Data" button and preset chips. The component must `use App\Livewire\Concerns\WithDateRange` (provides `setDateRange`, `datePresets`, and the `dateRangeChanged()` / `maxSelectableDate()` hooks).
- `<x-ui.range-info>` for the "Menampilkan data ... / Data per ..." line, `<x-ui.btn variant icon target>` for buttons.
- Charts are drawn client-side inside `wire:ignore`; send new data with a Livewire event from the component rather than relying on `@json` in the view, which is only evaluated on first render.
- Numbers use Indonesian formatting (`number_format($n, 0, ',', '.')`) and dates use `translatedFormat`.

### Repo clutter

Root-level `debug_*.php`, `verify_*.php`, `import_*.php`, `force_sync_v2.php`, `test_api.php`, `scratch/`, `tmp/`, `api_*.json`, `tmp_error.txt` are one-off scripts and dumps, not part of the app. `sleekflow_contacts.sql` (16 MB) is a contacts seed dump. `formula_sheet_investigasi.md` and `laporan_perbandingan_juni_juli_agustus.md` are analysis notes. `DOKUMENTASI.md` is the detailed technical reference (endpoints, route table, per-service summary).
