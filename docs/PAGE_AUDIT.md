# Page audit

Status legend: **OK** (present, lint-clean, wired to schema), **NEW** (added in
this upgrade), **MIGRATED** (rewired to the canonical config/session),
**EXCLUDED** (kept out of the public repository — private/KYC/credential),
**REVIEW** (still needs work; see notes).

> Routes were re-audited for the public release in [`LEGACY_AUDIT.md`](LEGACY_AUDIT.md),
> which records what was removed or guarded and why.

The project contains ~165 tracked PHP files. This audit covers every top-level
area plus the new accounting module; it is a structural audit, not a claim that
each legacy page was individually browser-tested (see "Remaining work").

## Authentication & entry points

| Area | Page | URL | Purpose | Auth | DB deps | Status | Notes |
|------|------|-----|---------|------|---------|--------|-------|
| Auth | Login page | `/index.php` | Login form | No | `adminusers` | MIGRATED | Uses canonical config/session after rewrite |
| Auth | Login handler | `/protect/login_process.php` | Verify credentials | No | `adminusers` | NEW | CSRF, rate limiting, generic errors, session regeneration |
| Auth | Session guard | `/protect/session_check.php` | Guard admin pages | Yes | `adminusers` | NEW | Canonical session + timeout + role |
| Auth | Signup | `/protect/signup.php` | Self-registration | No | `adminusers` | REVIEW | OTP flow still references duplicated senders |
| Auth | Forgot/reset | `/protect/forgot-password.php`, `reset-password.php` | Password reset | No | `adminusers`, `auth_otp` | REVIEW | Consolidate OTP senders |

## Accounting (new module)

| Area | Page | URL | Purpose | Auth | DB deps | Status |
|------|------|-----|---------|------|---------|--------|
| Accounting | Command Center | `/accounting/index.php` | Finance & capital overview | Yes | accounts, journal_*, investments, investors | NEW |
| Accounting | Chart of accounts | `/accounting/chart-of-accounts.php` | COA list + create | Yes | accounts, journal_lines | NEW |
| Accounting | Journal | `/accounting/journal.php` | Create/list balanced entries | Yes | journal_entries, journal_lines, accounts | NEW |
| Accounting | General ledger | `/accounting/general-ledger.php` | Account detail | Yes | journal_* | NEW |
| Accounting | Trial balance | `/accounting/trial-balance.php` | Debits = credits | Yes | journal_* | NEW |
| Accounting | Profit & loss | `/accounting/profit-loss.php` | Period income/expense | Yes | journal_*, accounts | NEW |
| Accounting | Balance sheet | `/accounting/balance-sheet.php` | Assets = L + E | Yes | journal_*, accounts | NEW |
| Accounting | Cash flow | `/accounting/cash-flow.php` | Approx. cash movement | Yes | journal_*, accounts | NEW |
| Planning | Scenario planner | `/accounting/planner.php` | Liquidity projection | Yes | journal_*, accounts | NEW |

## Legacy admin modules

| Area | Page(s) | Auth | DB deps | Status | Notes |
|------|---------|------|---------|--------|-------|
| Dashboard | `/admin/index.php` | Yes | income, expenses, investments | MIGRATED | Uses `admin/config/dbcon.php` (now canonical) |
| Income | `/admin/Income/income.php`, `income_actions.php` | Yes | `income` | MIGRATED | Journal integration pending |
| Expense | `/admin/Expense/expense.php`, `expense_actions.php`, `process_expense.php` | Yes | `expenses`, `recycle_binexpense` | MIGRATED | Recycle-bin restore present |
| Investments | `/admin/investment/*` | Yes | `investments`, `stock_*`, `business_*`, `loan_*`, `real_estate_*`, `stock_prices` | MIGRATED | Market scraping optional |
| Investor/Fund | `/admin/fund/*` | Yes | `investors`, `equity_details`, `debt_details`, `investor_returns` | MIGRATED | Investor create uses simplified fields |
| Billing | `/admin/billing/*` | Yes | (billing tables) | REVIEW | Company info now configurable via `app_settings` |
| Employees | `/admin/employee/*` | Yes | `employees` | MIGRATED | Sensitive fields optional |
| Customers | — | — | `customers` | REMOVED | Legacy KYC onboarding removed from the public build; the `customers` table remains for billing/accounting |
| Reports | `/admin/*` PDF/CSV exports | Yes | many | REVIEW | Some templates still contain old branding |
| Backup | `/backup.php`, `/backup_ui.php` | Yes | `.env` via `includes/backup_config.php` | MIGRATED | `scheduler.php` is **CLI-only**; `includes/config.ini` is an optional override |

## Excluded from the public repository

| Area | Path | Reason |
|------|------|--------|
| KYC clients | `admin/clients/` | Citizenship/KYC data |
| KYC registration | `admin/clients_dis/` | KYC + credential vault + uploads |
| KYC root | `clients-dis/` | KYC uploads |
| Legacy customer onboarding | `admin/customer/` | Removed entirely (KYC form, handlers, dev scripts) |
| Registration area | `admin/reg/` | Registration/KYC flow |
| Agreements | `admin/agreements/` | Private agreements |
| All uploads | `**/uploads/`, `**/profile_images/`, `**/employee_photos/` | Private documents/photos |
| Private SQL & backups | `database/gmic.sql`, `backups/`, `logs/` | Real data / runtime |

## Remaining work (explicit)

These items are **not yet complete** and are tracked so the audit is honest:

1. Consolidate the duplicated OTP senders to a single hardened implementation.
2. Point Income/Expense create/edit at `ledger_post_entry()` for automatic
   journal posting (the engine and accounts are ready).
3. Replace remaining legacy branding strings in PDF/report templates with
   `APP_NAME`.
4. Consolidate the two login paths (`index.php` inline handler vs
   `protect/login_process.php`).

Done in this pass (formerly listed here): the inline connections in
`admin/customer/*`, `admin/dis/*`, `admin/Expense/t.php` and `admin/t.php` were
resolved by guarding or removing those routes — see
[`LEGACY_AUDIT.md`](LEGACY_AUDIT.md). `iiiindex.php` is retained: it is a factual
public landing page.
