# Capistra — Feature Matrix

Legend — **Works?**: does the capability function today · **Dynamic?**: is it
configurable without code edits · **Implemented?**: as of this phase ·
**Tested?**: covered by `tests/run-tests.php` or an HTTP/lint check.

| Module | Current feature | Works? | Dynamic? | Hard-coded data | Missing capability | Recommended data model | Recommended UI | Priority | Implemented? | Tested? |
|---|---|---|---|---|---|---|---|---|---|---|
| Auth | Login/logout, lockout, CSRF, OTP | Yes | Partial | Timeout/OTP policy | Profile self-service | `adminusers`, `auth_otp` | Login + guard | P2 | Yes | Yes (HTTP) |
| Settings | Key/value store | Table only | No | Defaults in code | Full settings center | `app_settings` + typed getters | Tabbed Settings | **P0** | Yes | Yes |
| Feature toggles | — | No | — | — | Hide modules + guard URLs | `app_settings.enable_*` | Settings → Modules | **P0** | Yes | Yes |
| Categories | — | No | — | Strings in income/expense forms | Add/edit/reorder/deactivate | `transaction_categories` | Settings → Categories | **P0** | Yes | Yes |
| Ledger mapping | Name→code in PHP | Yes | No | 4000/4100/5000.. | Data-driven mapping | `transaction_categories.ledger_account_id` | Settings → Accounting | **P0** | Yes | Yes |
| Financial accounts | Implicit cash | Partial | No | `is_cash_account` | Wallets/bank/broker/card | `financial_accounts` | Settings → Accounts | **P0** | Yes | Yes |
| Income | CRUD, filter, export | Yes | No | 20 categories | Account, tags, budget | `income` + `financial_account_id` | Income | **P0** | Yes | Yes |
| Expenses | CRUD, recycle | Yes | No | 18 categories | Account, tags, budget | `expenses` + `financial_account_id` | Expenses | **P0** | Yes | Yes |
| Transfers | — | No | — | — | Account↔account | `transfers` + journal | Finance → Transfers | **P0** | Yes | Yes |
| Reconciliation | — | No | — | — | Statement vs system | `reconciliations` | Finance → Reconcile | P1 | Yes | Yes |
| Budgets | — | No | — | — | Monthly limits | `budgets`/`budget_items` | Finance → Budgets | P1 | Yes | Yes |
| Recurring | — | No | — | — | Salary/rent/SIP | `recurring_transactions` | Finance → Recurring | P1 | Yes | Yes |
| Goals | — | No | — | — | Savings goals | `goals` | Finance → Goals | P1 | Yes | Yes |
| Tags | — | No | — | — | Cross-cutting labels | `tags`/`transaction_tags` | Finance → Tags | P1 | Yes | Yes |
| Net worth | Approx. via ledger | Partial | No | — | Assets − liabilities view | derived from accounts+investments | Finance → Net Worth | P1 | Yes | Yes |
| CSV import/export | PDF/Excel per page | Partial | No | — | Preview/validate/dupes | `import_batches` | Settings → Data | P1 | Yes | Yes |
| Accounting | Journal, TB, P&L, BS, CF | Yes | No | mappings | Void/reverse, guard | `journal_*` | Accounting | **P0** | Yes | Yes |
| Fiscal periods | Table | Partial | No | `07-01` | Create/close + guard | `fiscal_periods` | Settings → Fiscal | P1 | Yes | Yes |
| NEPSE sectors | 11 in form / 14 in JS | No | No | hard-coded | Manage sectors | `market_sectors` | Settings → Sectors | **P0** | Yes | Yes |
| NEPSE securities | 220 in JS, 12 dups | No | No | hard-coded | CRUD + CSV | `securities` | Settings → Securities | **P0** | Yes | Yes |
| Market prices | Scraper overwrites | Partial | No | symbol only | History + manual/CSV | `security_prices` | Settings → Market Data | **P0** | Yes | Yes |
| Stock ledger | Position only | Partial | No | — | Buys/sells/bonus | `stock_transactions` | Investment → Transactions | **P0** | Yes | Yes |
| Positions/P&L | Invested vs value | Partial | No | — | Avg cost, realized | derived | Investment → Portfolio | P1 | Yes | Yes |
| Corporate actions | — | No | — | — | Dividend/bonus/split | `corporate_actions` | Investment → Actions | P1 | Yes | Yes |
| Fees/taxes | — | No | — | — | Effective-dated rules | `fee_rules` | Settings → Fees | P1 | Yes | Yes |
| Classifications | HTML enums | Yes | No | 8 enums | Configurable values | `classification_options` | Settings → Classifications | P1 | Yes | Yes |
| Customers | CRUD | Yes | No | — | Toggle | `customers` | Admin | **P0** | Yes | Yes |
| Employees | CRUD | Yes | No | — | Toggle | `employees` | Admin | **P0** | Yes | Yes |
| Billing | Pages | Yes | No | — | Toggle | existing | Admin | **P0** | Yes | Yes |
| Investors/Funds | CRUD + returns | Yes | No | — | Toggle | `investors` etc. | Fund | **P0** | Yes | Yes |
| Loans | Amount/rate | Partial | No | enums | Schedule, payments | `loan_payments` | Investment → Loans | P1 | No | No |
| Business | Amount/equity | Partial | No | enums | Valuation history | extended detail | Investment → Business | P1 | No | No |
| Real estate | Amount/location | Partial | No | enums | Cost basis, rent | extended detail | Investment → Property | P1 | No | No |
| Dashboard | Fixed KPIs | Yes | No | layout | Widgets, net worth | widget registry | Dashboard | P1 | Partial | Yes |
| Search | — | No | — | — | Global search | unified queries | Topbar | P2 | No | No |
| Reports | Per-page | Partial | No | — | Central filters | canonical helpers | Reports | P1 | Partial | Yes |
| Benchmarking | — | No | — | — | Portfolio vs index | `benchmarks` | Investment | P2 | Schema only | No |
| XIRR/TWR | — | No | — | — | Advanced returns | derived | Portfolio | P2 | No | No |
| Net worth trend | — | No | — | — | Monthly history | snapshots | Finance | P2 | No | No |
