# Legacy route & market-data audit

This document records the public-release audit of Capistra's legacy routes: what
each one is, whether it belongs in the public open-source build, and what was
done about it. It complements [`PAGE_AUDIT.md`](PAGE_AUDIT.md) (structural map)
and [`SECURITY_AUDIT.md`](SECURITY_AUDIT.md) (private-data inventory).

Capistra grew out of an earlier PHP/MySQL project that had a very different
purpose — an investment/wealth-advisory firm with client onboarding, KYC
document collection and a third-party credential vault. Those areas are **not**
part of Capistra and must not be reachable from the public build.

## 1. Classification legend

| Status | Meaning |
|---|---|
| **ACTIVE PUBLIC** | A supported part of Capistra; intentionally reachable. |
| **MIGRATED** | Rewired to the canonical config/session; kept. |
| **DEPRECATED** | Superseded but retained for reference; guarded. |
| **PRIVATE / UNSAFE** | Collects or exposes private data, or is unsafe if reachable; removed or blocked. |
| **DEAD** | No longer does anything useful; removed. |

Method: every tracked PHP file was checked for an auth guard
(`protect/session_check.php`, `capistra_admin_guard()`, `capistra_require_login()`)
and every route was requested anonymously over HTTP to confirm its real
behaviour (`200` vs `302` vs `404`).

## 2. Excluded KYC / credential-vault modules

These directories exist only in the private off-repository backup. They contain
**zero tracked files** in the public repository — `.gitignore` excludes them.

| Area | Path | Status | Decision |
|---|---|---|---|
| KYC client records | `admin/clients/` | PRIVATE / UNSAFE | Excluded from the public tree (0 tracked files) |
| KYC + credential vault | `admin/clients_dis/` | PRIVATE / UNSAFE | Excluded (0 tracked files) |
| Registration flow | `admin/reg/` | PRIVATE / UNSAFE | Excluded (0 tracked files) |
| KYC uploads root | `clients-dis/` | PRIVATE / UNSAFE | Excluded |

**Nav links to them were still present in tracked files**, which would have
produced dead links (and pointed users at KYC concepts) in the public build.
Those links were removed from:

- `admin/includes/sidebar.php`
- `admin/head/header.php`
- `backup_ui.php`

## 3. Routes removed from the public codebase

| Route | What it was | Status | Decision |
|---|---|---|---|
| `admin/customer/custmerreg.php` | Legacy KYC registration form (citizenship, bank, DEMAT, document uploads) | PRIVATE / UNSAFE | **Removed** |
| `admin/customer/formhandle.php` | Insert handler for the above (`applicants`, `minors`, `married`, `children`, `singles`, `applicant_documents`) | PRIVATE / UNSAFE | **Removed** |
| `admin/customer/tablecreate.php` | Dev script creating `minordet`, `mariadult`, `nomarriadl` | DEAD | **Removed** |
| `admin/customer/demo.php` | Standalone Nepali date-picker demo | DEAD | **Removed** |
| `admin/customer/normalregis.php`, `dscustomer.php`, `disnormal.php` | Empty header/footer stubs | DEAD | **Removed** |
| `admin/customer/nepali-date-picker.*`, `LICENSE`, `README.md`, `_config.yml` | Vendored picker used only by the removed KYC forms | DEAD | **Removed** |
| `admin/t.php` | Dev table creator; schema contained citizenship / PAN / bank columns | DEAD | **Removed** |
| `admin/Expense/t.php` | Dead sidebar fragment ("Global tech" brand, links to `customer/*`) | DEAD | **Removed** |
| `admin/config/table.php` | Dev table creator for `profit_sharing_details` / `debt_details` / `equity_details`; referenced a non-existent `admin/errors/db.php` | DEAD | **Removed** (tables now live in `database/schema.sql`) |
| `admin/fund/process/edit_investor.php` | Unreferenced duplicate of `admin/fund/edit_investor.php` with a broken include path | DEAD | **Removed** |

Net effect: the removed paths now return **HTTP 404**, and no navigation entry
points at them.

## 4. Routes guarded in this pass

Previously reachable without a session. Each now requires an authenticated admin
session; anonymous requests receive **HTTP 302** to the login page.

| Route group | Files | Notes |
|---|---|---|
| `admin/fund/*.php` | `fundmanagement`, `investorprofile`, `view_investor`, `view_investor_details`, `investor_details`, `edit_investor`, `returnmanagement`, `save_investment_details` | `fundmanagement.php` previously returned **200 without login** |
| `admin/fund/process/*.php` | `delete_investor`, `get_investor`, `save_investor`, `update_investor`, `update_investment_details`, `export_pdf` | POST/GET handlers |
| `admin/dis/*.php` | `display_stocks`, `scrape_and_store`, `insert_to_db`, `scrape_merolagani` | Legacy market-data helpers (see §6) |
| `backup_ui.php`, `delete_backup.php` | — | `delete_backup.php` could previously delete backup files unauthenticated |
| `scheduler.php` | — | Now **CLI-only** (`HTTP 403`); it is an infinite loop and must never run under a web request |

### Guard redirect fixed

`protect/session_check.php` used a hard-coded relative redirect
(`../index.php`), which resolved to a non-existent page for deeper routes such
as `admin/fund/process/*`. It now resolves the login URL through the canonical
`APP_URL` (`capistra_legacy_login_url()`), so every legacy guard redirects to
the login page correctly regardless of nesting depth.

### Backup configuration decoupled from local-only files

`backup.php`, `backup_ui.php` and `scheduler.php` read a local, git-ignored
`includes/config.ini`, so the Backup screen died with *"Configuration file
missing"* on a fresh clone. They now load
[`includes/backup_config.php`](../includes/backup_config.php), which takes the
database connection from `.env` and treats `includes/config.ini` as an optional
override for backup tuning only.

`delete_backup.php` also `require_once`d `includes/config.ini` — an INI file —
which echoed its contents into the JSON response (an information-disclosure bug
that could print database credentials). That include was removed; the script
needs no configuration.

## 5. Routes reviewed and deliberately kept

| Route | Status | Rationale |
|---|---|---|
| `admin/page/{login,register,forgot-password,message,code}.php` | ACTIVE PUBLIC | Part of the authentication flow |
| `admin/logout.php` | ACTIVE PUBLIC | Must be reachable while signed in; it only destroys the session |
| `admin/index.php`, `admin/profile.php`, `admin/registered.php` | MIGRATED | Already guarded via `session_check.php` |
| `admin/investment/*.php` | MIGRATED | Guarded; `includes/process/*` handlers run under `process.php`, which guards |
| `admin/finance/*`, `admin/settings/*` | ACTIVE PUBLIC | Guarded via `capistra_admin_guard()` |
| `accounting/*.php` | ACTIVE PUBLIC | Guarded via `capistra_require_login()` |
| `iiiindex.php` | ACTIVE PUBLIC | Public project landing page; content is factual (it explicitly states the project publishes no testimonials) |
| `admin/Expense/fpdf.php` | DEPRECATED | Third-party library file; not a route |
| `scripts/nssm.exe`, `scripts/*.bat` | DEPRECATED | Windows backup-service helpers kept as-is; they contain no secrets |
| `protect/login_process.php` | DEAD (kept) | A hardened login handler that the login form does not call — `index.php` handles the POST itself. Harmless because it refuses any request without a valid CSRF token. **Follow-up:** either wire the form to it or delete it; do not leave two divergent login paths indefinitely. |

### Known duplicate/legacy areas still to consolidate

- `protect/login_process.php` vs the inline handler in `index.php` (above).
- `admin/investment/stock.php` still loads the legacy `assets/js/stock.js` company
  list for its picker; the newer `securities` master is authoritative (see §7).

## 6. NEPSE / scraper audit

**Status: Experimental / optional market-data helper.** Capistra does **not**
ship an official live NEPSE integration. The legacy scraper reads the public
Merolagani "Latest Market" HTML page; it is best-effort and may break whenever
that page changes.

| Concern | Finding |
|---|---|
| Is scraper output tracked? | **No.** `admin/dis/last_scraped.html`, `admin/dis/scrape.log`, `admin/dis/scrape_error.log`, `admin/investment/error.log` and `**/logs/` are all git-ignored, and `git ls-files` confirms none are tracked. |
| Does the app depend on the scraper? | **No.** Every page reads the cached `stock_prices` / `security_prices` tables. No page-load path triggers a scrape. The only trigger is the explicit "refresh" action in `admin/investment/dis_stock.php`, which `fetch`es `../dis/scrape_and_store.php`. |
| Do scraper failures break Capistra? | **No.** The refresh is an asynchronous `fetch`; a failure leaves the cached data in place. `get_last_update.php` and `check_data_freshness.php` return JSON and report `status: error` instead of fataling. |
| Manual price entry still possible? | **Yes.** `admin/investment/prices.php` imports CSV via `capistra_record_price(..., source: 'manual')`, and `admin/settings/nepse.php` manages the securities master. |
| Is price source / timestamp visible? | **Yes.** `security_prices.source` + `fetched_at` and `stock_prices.fetched_at` are shown by the prices/investment screens. |
| Is the scrape endpoint safe? | **Now guarded** (admin session required, see §4). Previously `insert_to_db.php` could be triggered anonymously and it runs `DELETE FROM stock_prices` first. |
| What is the documented label? | **Experimental / optional market-data helper** — presented as cached, unverified data, never as authoritative market data. |
| Remaining limitation | `admin/investment/cron_scrape.php` requires an authenticated session, so it is not a true headless cron entry point. Recommended: run a Windows scheduled task against the guarded endpoint while signed in, or import a CSV. |

## 7. Stock-master quality check

| Concern | Finding |
|---|---|
| Shipped master data | `database/demo_seed.sql` seeds **6 fictional securities** (`EXHYD`, `DEMOBNK`, `SMPMF`, `SAMPLINS`, `EXMFN`, `DEMOTEL`) with obviously fictional names and the note `Fictional demo security.` |
| Listing status | All 6 are `listing_status = 'unverified'`. Nothing is presented as a confirmed current listing. |
| Duplicate symbols | **None** in the shipped seed. `securities` enforces `UNIQUE (exchange_id, symbol)`. |
| Synthetic / placeholder companies | All of them are — deliberately, and labelled as such. |
| Real NEPSE names | Present only in the **legacy** `admin/investment/assets/js/stock.js` (~220 historic rows) and in [`NEPSE_MASTER_AUDIT.md`](NEPSE_MASTER_AUDIT.md), which documents the duplicate symbols, likely-obsolete listings and non-equity units. `scripts/migrate_nepse_seed.php` imports them only on explicit `--commit`, always as `unverified`, and never overwrites an existing symbol. |
| Sector consistency | `market_sectors` seeds a canonical 14-sector set; legacy sector text is preserved in `securities.notes`. |
| Obsolete listings | Not silently imported; they would arrive as `unverified` and require operator confirmation. |

**Conclusion:** the public build ships fictional, unverified demo data plus a
legacy reference list — it never claims unverified records are current NEPSE
listings.

## 8. Schema gap fixed in this pass

`admin/fund/save_investment_details.php`, `returnmanagement.php` and
`update_investment_details.php` read/write a `profit_sharing_details` table that
**was not present in `database/schema.sql`** (it was only ever created by the
now-removed dev script `admin/config/table.php`). The table is now part of
`database/schema.sql`, with a demo row in `database/demo_seed.sql` for the
profit-sharing demo investor. Fresh imports now create **49** tables.

## 9. Deferred / recommended follow-up

1. Consolidate `protect/login_process.php` with the inline login handler in `index.php`.
2. Replace the legacy `stock.js` picker in `admin/investment/stock.php` with the `securities` master.
3. Give the market-data refresh a proper headless entry point (CLI-safe) instead of requiring a browser session.
4. Move the remaining AdminLTE-era screens onto the shared Capistra layout.
