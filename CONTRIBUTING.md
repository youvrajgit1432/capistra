# Contributing to Capistra

Thanks for your interest.

## The one rule that matters most: never commit real data

This is a public repository with **fictional demo data only**. Please do not
commit:

- real customer, employee, investor or KYC data;
- uploaded documents, citizenship images, agreements, receipts or photos;
- database dumps or backups;
- secrets: `.env`, `config.ini`, API keys, SMTP credentials, encryption keys;
- generated reports containing personal data.

`.gitignore` already excludes these paths — keep it that way.

## Development setup

1. Clone the repository under your web root.
2. `composer install` (optional — only for PDF/mail features).
3. `cp .env.example .env` and set your database details.
4. Create `capistra`, then import `database/schema.sql` and
   `database/demo_seed.sql`.

## Before you open a pull request

```bash
# PHP syntax check for every file
find . -name '*.php' -not -path './vendor/*' -exec php -l {} \;

# Financial-logic tests
php tests/run-tests.php
```

- Keep the design system as the visual source of truth
  (`design-system/capistra-public-accounting-platform/MASTER.md`).
- Add or update tests for financial logic (double-entry balancing, trial
  balance, statements).
- Write clear, atomic commits.

## Coding style

- PHP 8+, `declare(strict_types=1)` in new files.
- Prepared statements everywhere; escape output.
- Prefer the canonical bootstrap (`config/app.php`) over ad-hoc connections.
