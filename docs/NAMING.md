# Product naming research

The original internal branding (`GMIC`, *Capistra*,
*Capistra*) is company-specific and must not appear in
the public product. This document records the rename research and the decision.

## Requirements

Short, professional, finance/accounting oriented, globally usable, easy to
pronounce, not tied to one company or country, clean GitHub slug, low obvious
conflict with existing accounting/finance products.

## Candidate research (web + GitHub)

| Candidate | Conflict found | Verdict |
|-----------|----------------|---------|
| LedgerFlow, FinLedger, LedgerCraft, Finora, BookPilot, TrackBooks, FinAxis, LedgerIQ, FinCanvas, KhataSphere, FinFusion, LedgerFusion | Explicitly excluded by the brief (known products) | Rejected |
| LedgerDesk | Bookkeeping products, Microsoft Store app, GitHub user | Rejected |
| Ledgerly | Multiple active accounting products (`ledgerlyclose.com`, G2, SourceForge) | Rejected |
| LedgerCrest | Active bookkeeping service + crypto research brand | Rejected |
| LedgerNest | Google Play accounting app + outsourcing firm | Rejected |
| Ledgera / Ledgera AI | Windows app, GitBook product, Google Play app | Rejected |
| Ledgerise | `ledgerise.dev` payment ops product | Rejected |
| Ledgerium | ICO / blockchain accounting project | Rejected |
| Finario | Enterprise CapEx software (`finario.com`) | Rejected |
| FinBook | Multiple products incl. an open-source accounting suite | Rejected |
| Fiscora | French + Indian accounting products (`fiscora.*`) | Rejected |
| Ledgerix / Ledgerio | Finance SaaS designs + `ledgerio.in` / `bizkhata.app` | Rejected |
| CapitalHarbor | Investment fund + course library | Rejected |
| FinVault / Capitalis | Digital-wallet and factoring products | Rejected |
| **Capistra** | **0 GitHub repositories named `capistra`; no finance/accounting product or trademark found** | **Selected** |

Verification method: Google web search for product/trademark use and the GitHub
search API (`GET /search/repositories?q=<name>+in:name`) for repository-slug
conflicts.

## Decision: **Capistra**

- Finance flavour ("cap" → capital) while remaining a distinctive coined word.
- Short (8 letters), easy to pronounce globally, not tied to a country or company.
- Clean GitHub slug: `capistra`; clean database name: `capistra`.

## Implementation

The public name is centralised so it can be changed in one place:

- `config/app.php` → `APP_NAME`, `APP_SLUG`, `APP_TAGLINE`
- `.env` → `APP_NAME`

Legacy internal identifiers (table names, historic comments) were intentionally
**not** renamed where doing so would break the application.
