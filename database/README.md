# Database setup

This directory contains **only sanitized, publishable SQL**:

| File | Contents | Safe to publish |
|------|----------|-----------------|
| `schema.sql` | Structure only — tables, keys, indexes, constraints and the `vw_all_investments` view. No data. | Yes |
| `demo_seed.sql` | Fictional demonstration data only. | Yes |

> The original private dump (`gmic.sql`) and the dated backups are **not** part of
> the public project. They contained real customer / KYC / credential material and
> live only in the private off-repository backup.

## 1. Create the database

```sql
CREATE DATABASE capistra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 2. Import the schema

```bash
mysql -u root -p capistra < database/schema.sql
```

## 3. Import the fictional demo data

```bash
mysql -u root -p capistra < database/demo_seed.sql
```

Or, in phpMyAdmin, select the `capistra` database and import each file in order.

## 4. Configure the application

Copy `.env.example` to `.env` and set at least:

```
DB_NAME=capistra
DB_HOST=127.0.0.1
DB_USER=root
DB_PASSWORD=
```

## Demo account (LOCAL DEMO ONLY)

| Field | Value |
|-------|-------|
| Username | `demo_admin` |
| Email | `demo@example.test` |
| Password | `Demo@12345` |

The password is stored as a `password_hash()` value in the seed — never plaintext.

## Migrations

Existing installations apply additive migrations from `database/migrations/`.
They are idempotent and never drop data:

```bash
mysql -uroot capistra < database/migrations/001_dynamic_foundation.sql
```

Legacy NEPSE company data (`admin/investment/assets/js/stock.js`) can be
migrated into the `securities` master with a validated, dry-run-first script:

```bash
php scripts/migrate_nepse_seed.php            # report only
php scripts/migrate_nepse_seed.php --commit   # import (all rows 'unverified')
```

## Schema overview

- **Authentication:** `adminusers`, `auth_otp`
- **Configuration:** `app_settings` (scalar preferences only)
- **Accounting:** `accounts`, `journal_entries`, `journal_lines`, `fiscal_periods`, `audit_log`
- **Money in/out:** `income`, `expenses`, `recycle_binexpense`
- **Dynamic masters:** `transaction_categories`, `financial_accounts`, `transfers`, `reconciliations`, `classification_options`, `fee_rules`
- **Market data:** `market_exchanges`, `market_sectors`, `securities`, `security_prices`
- **Portfolio:** `portfolios`, `stock_transactions`, `corporate_actions`
- **Personal finance:** `budgets`, `budget_items`, `recurring_transactions`, `goals`, `tags`, `transaction_tags`
- **Advanced:** `benchmarks`, `benchmark_prices`, `import_batches`
- **Investments (legacy detail):** `investments`, `stock_investments`, `business_investments`, `loan_investments`, `real_estate_investments`, `property_images`, `stock_prices`
- **Investors / funds:** `investors`, `equity_details`, `debt_details`, `investor_returns`
- **Parties:** `customers`, `employees`
- **Safe account metadata (no secrets):** `account_vault_metadata`
- **View:** `vw_all_investments`

## Privacy guarantees baked into the schema

- No KYC / citizenship registration tables.
- No credential-vault columns (email/bank passwords, security answers,
  Meroshare/TMS passwords, OTP secrets).
- Only *masked* account identifiers are stored, in `account_vault_metadata`.
