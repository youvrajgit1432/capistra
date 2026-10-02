# Capistra

**Self-hosted Accounting, Cash-Flow & Investment Management Platform**

Capistra is an open, self-hosted platform that combines a real double-entry
accounting core with multi-asset investment tracking and investor/fund
management — in a single, privacy-first application you run on your own server.

![Capistra trust-navy finance dashboard](docs/images/02-financial-dashboard.png)

> **This public repository contains FICTIONAL demonstration data only.**
> Every name, company, email, phone number, account number and financial figure
> in `database/demo_seed.sql` is invented. See [Privacy](#privacy).

---

## Why this project exists

Most self-hosted bookkeeping tools stop at income and expenses. Most investment
trackers ignore double-entry accounting. Capistra was built to close that gap:
accounting and capital allocation in **one** system, without sending financial
data to a third party.

## Key differentiators

- **Accounting + investments together.** A proper chart of accounts, journal,
  ledger and financial statements, next to a portfolio of stocks, businesses,
  real estate and loans.
- **Finance & Capital Command Center.** Cash position, operating result, assets,
  liabilities, equity, portfolio cost vs value, unrealized gain/loss and
  investor capital in one view.
- **Investor / fund management.** Investor profiles, equity/debt/profit-sharing
  terms, allocations and recorded returns.
- **Scenario / liquidity planner.** Project cash, runway, surplus and deficit
  against a reserve target. Clearly labelled a *calculator*, not advice.
- **Privacy-first account metadata.** Store *masked* account identifiers and
  notes — never third-party logins, passwords, security answers or OTP secrets.
- **Configurable localisation.** Base currency (NPR by default) is configurable;
  the UI works with any currency code.

## Features

**Accounting** — Chart of accounts (assets, liabilities, equity, income,
expenses), double-entry journal enforced to balance, general ledger, trial
balance, profit & loss, balance sheet, cash-flow summary, period filtering and
an audit trail.

**Income & Expense** — Categorised income and expenses with attachments, search,
filters, reports and recycle-bin restore; designed to post to the journal.

**Investments** — Unified investment records with per-asset detail for stocks,
businesses, loans and real estate, cost basis, latest valuation and status.
Market data is optional and cached locally, so the app works offline.

**Investor / Fund** — Investor profiles, equity and debt terms, profit-sharing,
and recorded returns.

**Finance Command Center & Scenario Planner** — See the differentiators above.

**Billing, Employees, Customers** — Invoice/bill records, employee directory
(sensitive fields optional) and a simplified, privacy-first customer model.

**Reports** — Print-friendly financial statements; CSV where practical.

## Tech stack

- PHP 8 (procedural + PDO/mysqli), MySQL / MariaDB
- Apache (XAMPP-friendly)
- Bootstrap/AdminLTE legacy admin + a new design-system-based accounting module
- Optional Composer packages: `phpmailer/phpmailer`, `setasign/fpdf`, `google/apiclient`

## Architecture

```
config/            Canonical bootstrap (.env loader, DB, session, branding)
accounting/        New accounting & capital module (design-system UI)
  lib/             Ledger engine, auth/CSRF/audit, layout
database/          schema.sql + demo_seed.sql (public, safe)
admin/             Legacy admin modules (income, expense, investments, ...)
design-system/     UI UX Pro Max MASTER.md (visual source of truth)
assets/css/        capistra.css (design tokens + components)
docs/              Naming research, page audit, security audit
```

## Installation

### Prerequisites

- XAMPP (or Apache + PHP 8 + MySQL/MariaDB)
- Composer (optional; only for PDF/mail features)

### Steps

1. **Place the project** under your web root (XAMPP: `C:\xampp\htdocs\capistra`).
2. **Install dependencies** (optional):
   ```bash
   composer install
   ```
3. **Configure the environment:**
   ```bash
   cp .env.example .env
   ```
   Set `DB_NAME=capistra`, `DB_HOST`, `DB_USER`, `DB_PASSWORD`.
4. **Create the database and import the schema + demo data:**
   ```bash
   mysql -u root -p -e "CREATE DATABASE capistra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p capistra < database/schema.sql
   mysql -u root -p capistra < database/demo_seed.sql
   ```
5. **Start Apache and MySQL**, then open the app:
   `http://localhost/capistra/`

Full database instructions: [`database/README.md`](database/README.md).

## Demo credentials (LOCAL DEMO ONLY)

| Field | Value |
|-------|-------|
| URL | `http://localhost/capistra/` |
| Username | `demo_admin` |
| Email | `demo@example.test` |
| Password | `Demo@12345` |

These credentials exist solely for local demonstration with fictional data.

## Screenshots

Screenshots live in `docs/images/` and use fictional demo data only.

| | |
|---|---|
| ![Financial dashboard](docs/images/02-financial-dashboard.png) | ![Trial balance](docs/images/07-trial-balance.png) |
| ![Capital command center](docs/images/15-capital-command-center.png) | ![Scenario planner](docs/images/16-scenario-planner.png) |

<sub>Screenshots are generated from the fictional demo database. If an image is
missing in your checkout, see `docs/images/README.md`.</sub>

## Security model

- `password_hash()` / `password_verify()` for credentials; never plaintext.
- Prepared statements for all queries.
- CSRF tokens on state-changing forms.
- Secure sessions (`HttpOnly`, `SameSite`, id regeneration, inactivity timeout).
- Login rate limiting with generic error messages.
- Output escaping; input validation.
- Audit trail for accounting mutations.
- **No credential vault:** the application does not store email/bank passwords,
  security answers or OTP secrets.

See [`SECURITY.md`](SECURITY.md) for how to report vulnerabilities.

## Privacy

> The public repository contains **fictional demonstration data only**.
> The original private system's customer, KYC and credential data was backed up
> privately and excluded from this repository — it was never committed.
>
> If you deploy Capistra with real data, **you** are responsible for securing it:
> change the demo account, set a strong `APP_KEY`, keep the database and any
> uploaded documents off version control, and serve the app over HTTPS.

Capistra is **not** certified accounting, tax or investment-advice software.

## Dynamic configuration

Capistra is configurable without editing source code:

- **Settings Center** (`admin/settings/`) — general preferences, money/locale,
  feature modules, categories, accounts & wallets, accounting mappings, fiscal
  periods, NEPSE securities & sectors, fee rules, classifications, import/export
  and backup.
- **Feature modules** — `financial_profile_mode` (personal/business/hybrid) sets
  defaults; individual modules can be toggled. Disabled modules are hidden from
  navigation *and* blocked by direct URL.
- **Transaction categories** — income/expense categories are rows mapped to a
  ledger account; posting is data-driven.
- **Financial accounts** — cash, bank, e-wallet, broker, credit card and loan
  wallets, each linked to a ledger account, with transfers and reconciliation.
- **NEPSE master data** — exchanges, sectors and securities live in the database;
  add a newly listed company from the UI. Historical prices are append-only.
- **Stock ledger** — positions, average cost, realized/unrealized P&L and
  corporate actions are derived from `stock_transactions`.

One documented precedence model governs the reporting currency
(`app_settings.base_currency` overrides the deployment default).

## Production checklist

- [ ] Change or remove the `demo_admin` account.
- [ ] Set a strong `APP_KEY` (`php -r "echo bin2hex(random_bytes(32));"`).
- [ ] Create your own administrator with a strong password.
- [ ] Serve over HTTPS and set `SESSION_SECURE_COOKIE=true`.
- [ ] Set `APP_DEBUG=false` and `APP_ENV=production`.
- [ ] Restrict database user privileges to the `capistra` schema only.
- [ ] Disable directory listing; block execution in upload directories.
- [ ] Take and test regular encrypted backups (outside the web root).

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md). Please never commit real customer
data, secrets, database dumps or uploaded documents.

## License

Released under the [MIT License](LICENSE). Third-party components retain their
own licenses — see [`THIRD_PARTY_NOTICES.md`](THIRD_PARTY_NOTICES.md).
