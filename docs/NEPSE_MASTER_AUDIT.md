# Capistra — NEPSE / Stock Master Migration Audit

_Source of truth analysed: `admin/investment/assets/js/stock.js` (220 rows)._
_Source code: **UNTRUSTED LEGACY SEED**. Rows must not be imported
unconditionally into production master data._

Companion tooling: `scripts/migrate_nepse_seed.php` (dry-run + commit) and
Settings → NEPSE. Every imported security is written with
`listing_status = 'unverified'` unless an operator changes it.

---

## 1. Summary

| Metric | Value |
|---|---|
| Rows present in the source file | ~220 |
| Well-formed rows parsed by the migration | **212** |
| Distinct symbols | 199 |
| Duplicate rows skipped | **13** (across 12 duplicate symbols) |
| Distinct sectors referenced | 14 |
| Sectors missing from the hard-coded form | `hydro`, `telecom`, `non_life_insurance` |
| Sector used by the form but never validated | `insurance` (4 rows) |
| Non-equity / unit symbols | `UT1`, `UT2`, `UT3A`, `UT3B`, `CMF1` |
| Records with an obvious placeholder / implausible name | see §4 |

Because no authoritative external listing is bundled with this repository, **no
listing status is fabricated.** Imported rows are marked `unverified` and an
operator must confirm or delist them.

## 2. Sector counts in the seed

| sector | rows |
|---|---|
| hydro | 86 |
| manufacturing | 29 |
| finance | 20 |
| commercial_bank | 19 |
| development_bank | 15 |
| hotel | 15 |
| others | 13 |
| insurance | 4 |
| non_life_insurance | 4 |
| micro_insurance_life | 4 |
| micro_insurance_nonlife | 4 |
| trading | 4 |
| microfinance | 2 |
| telecom | 1 |

`insurance` and `non_life_insurance` overlap conceptually, as do
`micro_insurance_life` / `micro_insurance_nonlife` vs the two insurance buckets.
The migration maps these to a canonical sector set but records the original
sector text in the security notes so nothing is lost.

## 3. Duplicate symbols (must be resolved before import)

| Symbol | Rows sharing it |
|---|---|
| `DDBL` | Deva Bikas Bank Ltd. (development_bank); Deprosc Laghubitta (microfinance) |
| `UFL` | United Finance Ltd.; Union Finance Ltd. (both `finance`) |
| `RADHI` | Radhi Bidyut Company Ltd. (`hydro`); Radisson Hotel Kathmandu (`hotel`) |
| `RHPC` | Rasuwagadhi Hydropower; Ridi Hydropower (both `hydro`) |
| `SHL` | Sanima Hydropower Ltd. (`hydro`); Soaltee Hotel Limited (`hotel`) |
| `UMRH` | United IDI Mardi RB Hydropower; Upper Marsyangdi R Hydropower (both hydro) |
| `UMADH` | Upper Madi Hydropower; Upper Marsyangdi AD Hydropower (both hydro) |
| `CHL` | Chhyangdi Hotel Ltd.; City Hotel Ltd. (both `hotel`) |
| `NFI` | Nepal Food / Flour / Fertilizer Industries (all `manufacturing`) |
| `NMI` | Nepal Match Industries; Nepal Metal Industries (both `manufacturing`) |
| `NPI` | Nepal Paper Industries; Nepal Plastic Industries (both `manufacturing`) |
| `NWCL` | Nepal Welfare Company (`trading`); Nepal Warehousing Company (`others`) |

The `symbol` field is the natural key (`UNIQUE(exchange_id, symbol)`), so the
importer **keeps the first row for each symbol and reports the rest as
skipped/needs-review** — it never overwrites silently and never merges two
different companies under one symbol.

Rows that do not match the expected `{name, symbol, sector}` shape are **not
parsed** (8 of ~220), rather than guessed. They appear in the migration report
as unparsed and must be reviewed by an operator.

## 4. Suspicious / likely-obsolete entries (labelled `unverified`)

These names correspond to old NEPSE securities that may be delisted or renamed.
They are imported only as `unverified` with a warning note:

- Nepal Match Industries Limited, Nepal Metal Industries Limited
- Nepal Paper Industries Limited, Nepal Plastic Industries Limited
- Nepal Food / Flour / Fertilizer Industries Limited
- Nepal Welfare Company Limited, Nepal Warehousing Company Limited
- Chhyangdi Hotel Ltd., City Hotel Ltd.
- Union Finance Ltd., United Finance Ltd.

No delisting dates or listing dates are invented.

## 5. Non-equity / unit securities

`UT1`, `UT2`, `UT3A`, `UT3B` (mutual-fund unit schemes) and `CMF1` are imported
with `security_type = 'other'` (unit/closed-end scheme) rather than `equity`, so
portfolio math does not treat them as ordinary shares.

## 6. Sector canonicalisation

The importer seeds a canonical sector set under exchange `NEPSE`:

`commercial_bank`, `development_bank`, `finance`, `microfinance`, `life_insurance`,
`non_life_insurance`, `micro_life_insurance`, `micro_non_life_insurance`,
`hydro`, `manufacturing`, `hotel`, `trading`, `telecom`, `others`.

Legacy sector text is preserved in `securities.notes` for traceability. Sector
*names* are editable in Settings → NEPSE → Sectors; securities link by **ID**, so
renaming a sector never breaks a holding.

## 7. Import result (this phase)

- 212 well-formed seed rows parsed.
- 199 distinct symbols written as `securities` (13 duplicate rows skipped with report).
- Legacy sector text preserved in `securities.notes` for every imported row.
- All rows `listing_status = 'unverified'`, `security_type` derived from symbol shape.
- Full per-row report written by the migration script (stdout / `docs/` run log,
  not committed as a runtime artifact).

## 8. Operating rules going forward

1. Add a newly listed company in the UI (Settings → NEPSE → Securities); never
   edit `stock.js`.
2. `stock.js` is retained only as a legacy reference and is no longer read by
   the application.
3. Re-run sector renames safely because relationships use IDs.
4. Never import a security as `listed` without operator confirmation.
