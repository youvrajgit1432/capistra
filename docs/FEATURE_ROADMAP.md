# Capistra — Feature Roadmap

_Positioning: self-hosted personal accounting, cash-flow and investment platform.
One admin/owner. Reusable by cloning and configuring._

This roadmap sequences the work so that **P0 data foundations exist before any
P2 analytics**. Items marked ✅ were implemented in this phase.

---

## P0 — Foundation (data is dynamic and correct)

| # | Capability | Status |
|---|---|---|
| 1 | Settings framework (`app_settings` + precedence + defaults) | ✅ |
| 2 | Settings Center (tabbed, not one giant form) | ✅ |
| 3 | Feature toggles + `financial_profile_mode` | ✅ |
| 4 | `transaction_categories` master with `ledger_account_id` | ✅ |
| 5 | Data-driven ledger posting (remove PHP name→code maps) | ✅ |
| 6 | `financial_accounts` (cash/bank/e-wallet/broker/card/loan) | ✅ |
| 7 | Account selection on income/expense (`Received Into` / `Paid From`) | ✅ |
| 8 | `transfers` (account→account, balanced, not income/expense) | ✅ |
| 9 | `market_exchanges` / `market_sectors` / `securities` masters | ✅ |
| 10 | Security/sector admin + CSV import/export + migration audit | ✅ |
| 11 | `security_prices` history (never truncate) + manual & CSV entry | ✅ |
| 12 | `stock_transactions` ledger + derived positions | ✅ |
| 13 | `classification_options` for configurable enums | ✅ |
| 14 | Centralise active hard-coded DB connections | ✅ |
| 15 | Fix encoding + design-system stylesheet path | ✅ |

## P1 — Personal finance & investment capability

| # | Capability | Status |
|---|---|---|
| 16 | Budgets (month × category) with actual/remaining/% used | ✅ |
| 17 | Recurring transactions (weekly→yearly, auto/reminder) | ✅ |
| 18 | Goals (target/current/date, progress, required contribution) | ✅ |
| 19 | Tags (multi-tag on transactions) | ✅ |
| 20 | Reconciliation (statement balance vs system, difference, status) | ✅ |
| 21 | CSV import (preview/validate/duplicates) + CSV export | ✅ |
| 22 | Net worth (assets − liabilities, by class) | ✅ |
| 23 | Corporate actions (dividend/bonus/rights/split/merger) | ✅ |
| 24 | Realized / unrealized P&L per security from transactions | ✅ |
| 25 | Portfolio analytics (sector/security/symbol allocation, gainers) | ✅ |
| 26 | Multiple portfolios/accounts + broker grouping | ✅ |
| 27 | Fee/tax rules (effective-dated, configurable) | ✅ |
| 28 | Loan repayment schedules (verified math, opt-in) | ⏳ P1 (scheduled) |
| 29 | Business & real-estate valuation history | ⏳ P1 (scheduled) |
| 30 | Fiscal-period create/close + posting guard | ✅ |
| 31 | Configurable dashboard widgets | ⏳ P1 (scheduled) |
| 32 | Deterministic financial-health insights | ⏳ P1 (scheduled) |

## P2 — Advanced

- Benchmarking (NEPSE index / user-defined) with CSV/manual import.
- XIRR / money-weighted and time-weighted returns (only with verified formulas + tests).
- Custom automation rules.
- Advanced comparative reports and cohort analysis.
- Pluggable market-data providers (live API behind the `MarketDataProvider` interface).
- BS/AD dual-date display with a verified conversion library.

## P3 — Optional / future

- Bank statement OFX/QIF import.
- Multi-currency revaluation.
- Document/attachment store hardening.
- Read-only shared portfolio links.

---

## Delivery principles

1. **No destructive migration.** Legacy columns and rows are preserved; new
   structure is additive. Unmigratable records produce a report, never a silent drop.
2. **Pure functions first.** Every financial calculation (average cost, realized
   P&L, split/bonus adjustment, budget usage, net worth, schedules) is a pure
   function covered by `tests/run-tests.php`.
3. **One canonical formula.** Dashboard and reports call the same domain helpers.
4. **Safe fallbacks.** When a category has no ledger mapping (legacy rows), a
   documented fallback account is used and flagged.
5. **Feature toggles are enforced, not cosmetic** — direct URLs are guarded too.
