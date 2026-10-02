# Third-party notices

Capistra bundles or depends on the following third-party components. They are
distributed under their own licenses and are not relicensed by this project.
The full text of each license ships with the component (e.g. inside `vendor/`).

## Composer packages

| Package | Purpose | License |
|---------|---------|---------|
| `phpmailer/phpmailer` | Optional email (OTP/notifications) | LGPL-2.1-or-later |
| `setasign/fpdf` | PDF report generation | FPDF License (permissive, no warranty) |

## Front-end / bundled assets

| Component | Purpose | License |
|-----------|---------|---------|
| AdminLTE | Legacy admin theme | MIT |
| Bootstrap | CSS framework | MIT |
| Font Awesome Free | Icons | CC BY 4.0 (icons), MIT (code), SIL OFL (fonts) |
| Ionicons | Icons | MIT |
| IBM Plex Sans (Google Fonts) | Typography | SIL Open Font License 1.1 |

> When redistributing, keep the license notices shipped with each component.
> If a component's license is incompatible with your intended use, remove or
> replace that component.

## Not included

`vendor/` is git-ignored and must be installed with Composer. No third-party
source is committed to this repository.
