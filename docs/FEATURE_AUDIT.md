# Capistra — Product Feature Audit

_Phase: dynamic-system architecture & personal-finance upgrade._
_Audit target: branch `main` @ `52228ca` (before this phase's changes)._
_Product positioning: self-hosted **personal** accounting, cash-flow and investment
management for a single owner/admin, reusable by anyone who clones the project._

Priorities: **P0** required foundation · **P1** high-value product capability ·
**P2** advanced enhancement · **P3** optional/future.

This document describes the application **as a product**, not as a file listing.
It records the current capability, the hard-coded assumptions that block a
reusable product, the missing CRUD/automation/analytics, data-model limits, UX
and navigation issues, technical debt, and the recommended upgrade.

---

## 1. Authentication & account management

| | |
|---|---|
| **Current capability** | Username/password login against `adminusers`, bcrypt hashes, failed-attempt lockout, 30-minute inactivity timeout, CSRF on state-changing forms, optional email OTP (`auth_otp`, hash-only), logout, signup/forgot-password pages. |
| **Hard-coded assumptions** | Default demo credentials were previously implied in code (bypass removed). OTP sender domains are fixed. |
| **Dynamic opportunity** | Session/cookie policy, timeout length and OTP requirement should be settings. |
| **Missing CRUD** | Profile self-service, password change, account lock management, role management UI. |
| **Missing automation** | No scheduled cleanup of expired OTP rows. |
| **Analytics** | Login/audit history view is not exposed. |
| **Data-model limit** | `role` enum exists but is not enforced in the UI. |
| **Navigation/UX** | Login is at the project root while the rest of the app is under `admin/`; two layouts coexist. |
| **Technical debt** | Root login page and `admin/` header/topbar have different visual systems. |
| **Recommended upgrade** | Keep as-is functionally; add Settings → General for timeout/OTP; surface recent audit log. |
| **Priority** | P2 |

## 2. Dashboard / Finance Command Center

| | |
|---|---|
| **Current capability** | KPI cards (cash position, YTD income/expense, net result, assets, liabilities, equity, net financial position), investment exposure by asset class, investor capital, approximate cash flow, income/expense bars, "books health" trial-balance check. |
| **Hard-coded assumptions** | Year window is calendar-year (`Y-01-01`); ignores the configured fiscal year start. Widgets are fixed and always rendered. |
| **Dynamic opportunity** | Widget enable/disable + order (Settings → Dashboard); fiscal-year range. |
| **Missing capability** | Net worth, budget status, upcoming recurring items, goals, portfolio value/latest prices, asset allocation chart, recent transactions. |
| **Analytics** | No trend/sparklines; no period comparison. |
| **Data-model limit** | Dashboard reads `investments` position rows, not transaction-level holdings. |
| **UX** | All KPIs equally weighted; no personal-finance framing. |
| **Technical debt** | Metric formulas live inline in `index.php`, duplicated from ledger helpers. |
| **Recommended upgrade** | Widget registry + settings; route all figures through canonical domain functions; add personal-finance widgets. |
| **Priority** | P1 |

## 3. Income

| | |
|---|---|
| **Current capability** | Add income (category/date/amount/bill/remarks), month-range filter, search, monthly grouping, PDF + Excel export, month/year comparison charts, recycle bin, ledger posting via sync. |
| **Hard-coded assumptions** | 20 income categories hard-coded in the `<select>` of `admin/Income/income.php`; year filter fixed to 2024–2025; currency label hard-coded "Rs"; PDF header shows a hard-coded address/PAN; a generic Cash account is always assumed. |
| **Dynamic opportunity** | Categories master (`transaction_categories`) with `ledger_account_id`; **Received Into** financial account; configurable currency/company header. |
| **Missing CRUD** | No inline edit UI in the main list (edit lives in `income_actions.php`); no soft-delete reason. |
| **Missing automation** | No recurring income (salary, rent received). |
| **Analytics** | No category breakdown, no budget-vs-actual. |
| **Data-model limit** | `category` is free text (no FK); no `financial_account_id`; no tags. |
| **Navigation/UX** | Bootstrap 5 page inside a jQuery/Bootstrap admin shell — inconsistent with the Swiss design system. |
| **Technical debt** | PDF block hard-codes company identity and a real-looking PAN; duplicated between list and export. |
| **Recommended upgrade** | Dynamic categories + financial account selector + tags; configurable document header. |
| **Priority** | **P0** |

## 4. Expenses

| | |
|---|---|
| **Current capability** | Same as income plus recycle-bin restore. |
| **Hard-coded assumptions** | ~18 expense categories hard-coded in `admin/Expense/expense.php`; single implicit cash account. |
| **Dynamic opportunity** | Category master + mapping; **Paid From** financial account. |
| **Missing capability** | Budgets, recurring bills, tags, reconciliation. |
| **Data-model limit** | Free-text category; no account linkage; guarded `bill_file` key was a latent bug (fixed this phase family). |
| **Recommended upgrade** | Mirror the income upgrade; share one transaction form component. |
| **Priority** | **P0** |

## 5. Accounting core (ledger)

| | |
|---|---|
| **Current capability** | Chart of accounts (CRUD-lite), double-entry journal, general ledger, trial balance, P&L, balance sheet, approximate cash flow, income/expense→ledger sync. Balanced-entry validation. |
| **Hard-coded assumptions** | `accounting/lib/integrate.php` maps category **names** to fixed account codes (4000/4100; 5000/5100/5200/5300/5400/5900); a single cash account is chosen by `is_cash_account=1 ORDER BY code`. |
| **Dynamic opportunity** | `transaction_categories.ledger_account_id` drives posting; financial accounts map to real ledger accounts. |
| **Missing CRUD** | Account edit/deactivate; journal reversal/void UI; manual journal entry UI is read-only listing. |
| **Missing automation** | Fiscal-period close does not block postings. |
| **Analytics** | No per-account drilldowns beyond general ledger; no comparative periods. |
| **Data-model limit** | No linkage from ledger entries to financial accounts. |
| **Recommended upgrade** | Data-driven mapping; financial-account posting; fiscal-period enforcement; journal void/reverse. |
| **Priority** | **P0** |

## 6. Fiscal periods

| | |
|---|---|
| **Current capability** | `fiscal_periods` table with demo rows; read-only listing. |
| **Hard-coded assumptions** | Fiscal start default `07-01`; no UI to create/close. |
| **Missing CRUD** | Create / open / close periods. |
| **Missing automation** | Postings are not blocked in closed periods. |
| **Recommended upgrade** | Fiscal Period management UI + posting guard + audit logging. |
| **Priority** | P1 |

## 7. Financial accounts / wallets

| | |
|---|---|
| **Current capability** | None as a first-class concept at the transaction layer (implied "Cash and Bank" account only). `account_vault_metadata` stores masked account metadata but is disconnected from transactions. |
| **Dynamic opportunity** | `financial_accounts` (cash, bank, e-wallet, broker, credit card, loan, other) each mapped to a ledger account. |
| **Missing capability** | Transfers, reconciliation, opening balances per wallet. |
| **Recommended upgrade** | Introduce `financial_accounts`, wire income/expense/investment/transfer to them. |
| **Priority** | **P0** |

## 8. Transfers & reconciliation

| | |
|---|---|
| **Current capability** | None. |
| **Missing capability** | Account-to-account transfers (bank→broker, bank→cash, cash→e-wallet) that are neither income nor expense, and basic statement reconciliation. |
| **Recommended upgrade** | `transfers` table posting balanced entries; `reconciliations` table recording statement balance vs system balance. |
| **Priority** | **P0** (transfers) / **P1** (reconciliation) |

## 9. Budgets, recurring transactions, goals, tags

| | |
|---|---|
| **Current capability** | None. |
| **Dynamic opportunity** | All four are core personal-finance capabilities and must be data-driven. |
| **Recommended upgrade** | `budgets`/`budget_items`, `recurring_transactions`, `goals`, `tags`/`transaction_tags`. |
| **Priority** | P1 |

## 10. Investments (unified)

| | |
|---|---|
| **Current capability** | Four asset classes (stock, business, loan, real estate) stored as a unified `investments` row plus a per-type detail row; add/edit/delete; `vw_all_investments` listing. |
| **Hard-coded assumptions** | Classifications hard-coded in HTML: stock horizon, business type, investment model, loan type, repayment frequency, collateral type, property type, ownership type. |
| **Missing capability** | Transaction-level accounting, corporate actions, dividends, fees, valuations over time, documents. |
| **Data-model limit** | Position-oriented: a single `invested_amount` + `current_value`; cannot represent buys/sells/bonus/splits. |
| **Recommended upgrade** | `stock_transactions` ledger + derived positions; `corporate_actions`; `classification_options`; conditional detail forms. |
| **Priority** | **P0** (stock ledger) / P1 (others) |

## 11. Stock / NEPSE investments

| | |
|---|---|
| **Current capability** | Select sector → company (from a 220-row hard-coded JS list) → symbol auto-fill; units/base price/horizon; a scraper writes `stock_prices`. |
| **Hard-coded assumptions** | Company list and sectors live in `admin/investment/assets/js/stock.js`; the form's sector list (11) disagrees with the list's sectors (14); `includes/company_data.php` is empty. |
| **Known data problems** | 12 duplicate symbols, mutual-fund unit symbols, no listing status, no verification. See `docs/NEPSE_MASTER_AUDIT.md`. |
| **Data-model limit** | `stock_prices` is a replace-style cache keyed only by symbol; no history guarantee, no FK to a security. |
| **Recommended upgrade** | `market_exchanges` / `market_sectors` / `securities` / `security_prices` masters, admin CRUD + CSV, manual price entry, provider abstraction. |
| **Priority** | **P0** |

## 12. Loans, business, real estate

| | |
|---|---|
| **Current capability** | Detail records with the fields listed above; amount/duration tracking only. |
| **Missing capability** | Loan repayment schedules, payments received, outstanding principal, interest received, amortisation (simple & reducing balance); business contributions/distributions/valuation; property cost basis, rental income, expenses, valuation history. |
| **Recommended upgrade** | Extend detail tables + optional schedule/payment tables; verified amortisation math only. |
| **Priority** | P1 |

## 13. Investors / fund management, customers, employees, billing

| | |
|---|---|
| **Current capability** | Investor CRUD with KYC-style metadata, equity/debt details, returns; customer CRUD; employee CRUD; billing pages. |
| **Hard-coded assumptions** | Business-oriented; always visible even in a personal install. |
| **Dynamic opportunity** | Feature toggles + `financial_profile_mode` (personal/business/hybrid) to hide these modules. |
| **Recommended upgrade** | Feature registry + nav filtering + direct-URL guards. |
| **Priority** | **P0** |

## 14. Reports, search, import/export

| | |
|---|---|
| **Current capability** | Per-page PDF/Excel export for income; ledger statements. |
| **Missing capability** | Central report filters (date range, fiscal period, financial account, category, tag, portfolio), global search, CSV import with preview/validation/duplicate detection, CSV export of core datasets. |
| **Recommended upgrade** | Shared filter component + canonical query helpers; `import_batches` log; CSV templates. |
| **Priority** | P1 |

## 15. Cross-cutting: configuration, currency, dates, navigation, encoding

| | |
|---|---|
| **Current capability** | Brand + DB bootstrap in `config/app.php`; `app_settings` table present but effectively unused. |
| **Hard-coded assumptions** | `APP_BASE_CURRENCY` (env) and `app_settings.base_currency` can disagree silently; fiscal-year start unused by reports; dates are AD-only. |
| **Defects found** | `accounting/lib/layout.php` links CSS at `./assets/...` from `accounting/`, so the design system stylesheet 404s on every accounting page. |
| **Legacy direct DB connections (active risk)** | `admin/dis/display_stocks.php`, `admin/customer/formhandle.php`, `admin/t.php`, `admin/config/table.php`, `admin/customer/tablecreate.php`, `admin/dis/insert_to_db.php`, `admin/dis/scrape_and_store.php` still open their own `mysqli`. |
| **Recommended upgrade** | One precedence model for currency; settings-driven fiscal/date/format; centralised connections; feature-aware navigation. |
| **Priority** | **P0** |

---

## Hard-coded inventory (summary)

1. Income categories — `admin/Income/income.php`
2. Expense categories — `admin/Expense/expense.php`
3. Category→account mapping — `accounting/lib/integrate.php`
4. NEPSE companies (220) — `admin/investment/assets/js/stock.js`
5. NEPSE sector dropdown (11) — `admin/investment/stock.php`
6. Investment classifications (8 enums) — investment forms
7. Default currency & fiscal start — `config/app.php` / `app_settings`
8. Company identity in PDF export — `admin/Income/income.php`
9. Direct DB credentials — seven legacy modules
10. Sidebar navigation — `accounting/lib/layout.php` (no feature awareness)
