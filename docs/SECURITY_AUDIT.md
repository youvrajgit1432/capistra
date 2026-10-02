# Security & privacy audit

Scope: the transition of the private GMIC application into the public Capistra
product. This file records what private material existed, what was done about
it, and which credentials must be treated as compromised.

> No secret values are reproduced here on purpose — only paths and types.

## 1. Credential / secret inventory & required actions

| # | Location | Credential type | Action |
|---|----------|-----------------|--------|
| 1 | `includes/config.ini` | MySQL user + (empty) password, DB name | **REPLACE** — file is git-ignored; configuration now comes from `.env` via `config/app.php`. |
| 2 | `protect/db_connection.php` | Hardcoded MySQL host/user/password/DB | **REPLACE** — rewritten to use the canonical bootstrap. |
| 3 | `admin/config/dbcon.php`, `admin/config/table.php` | Hardcoded MySQL credentials | **REPLACE/REMOVE** — `dbcon.php` rewritten; `table.php` is a dev script (removed from the public tree). |
| 4 | `admin/clients/config/config.php`, `admin/clients_dis/config/config.php` | Hardcoded MySQL credentials | **REPLACE** — rewritten to the canonical bootstrap (tree excluded from public repo). |
| 5 | `admin/reg/config/database.php`, `admin/admin/admin_data.php` | Hardcoded MySQL credentials (PDO) | **REPLACE** — rewritten. |
| 6 | `admin/investment/config.php` | Hardcoded MySQL credentials | **REPLACE** — rewritten to the canonical bootstrap. |
| 7 | `admin/t.php`, `admin/customer/formhandle.php`, `admin/customer/tablecreate.php`, `admin/dis/*.php` | Hardcoded MySQL credentials | **REPLACE** — migrate to `config/app.php` (see remaining work below). |
| 8 | `admin/clients_dis/config/encryption.php` | Application encryption key | **DELETE + ROTATE** — the key protected encrypted third-party credentials. Removed from the public tree. Any data encrypted with it must be considered compromised. |
| 9 | Original dump: `gmail_accounts.encrypted_password` | Encrypted Gmail passwords | **DELETE + ROTATION REQUIRED** — third-party email passwords were stored. Rotate all such passwords; do not restore. |
| 10 | Original dump: `internet_banking.encrypted_password`, `..._security_answer1/2` | Encrypted bank logins + security answers | **DELETE + ROTATION REQUIRED** — rotate internet-banking credentials and security answers. |
| 11 | Original dump: `adult_registrations.meroshare_password`, `minor_registrations.meroshare_password`, `tp_mero` | Broker / Meroshare / TMS passwords | **DELETE + ROTATION REQUIRED** — rotate Meroshare/TMS credentials. |
| 12 | Original dump: `adminusers.password` (bcrypt) | Real administrator password hashes | **ROTATE** — treat as compromised; the public build ships only the fictional `demo_admin`. |
| 13 | `scheduler.php`, `scripts/*.bat`, `backup.php`, `backup_ui.php` | Path / configuration (no secret literal found) | **REVIEW** — ensure no credentials are read from removed config. |
| 14 | Mail / SMTP | No credential literal found in the tracked source | **N/A** — placeholders live in `.env.example`. |

### Scan results

- Hardcoded API keys / client secrets: **none found** in tracked PHP/INI.
- `openssl_encrypt` custom encryption: only in the removed credential-vault config.
- Password literals in source: **none found** (the previous `DEFAULT_PASSWORD`
  reset constant was removed and replaced with random temporary passwords).

## 2. Credential-vault removal (feature redesign)

The original schema stored third-party logins. In the public design these are
**removed** and replaced by `account_vault_metadata`, which stores only:

`provider`, `account_nickname`, **masked** identifier, `reference_id`, `notes`,
`last_verified_at`, `category`.

**Never stored:** bank login password, email password, recovery password,
security question answers, OTP secrets, TMS/Meroshare password.

## 3. KYC / registration data

The citizenship/KYC registration areas (`admin/clients/`, `admin/clients_dis/`,
`clients-dis/`, `admin/reg/`) are excluded from the public repository via
`.gitignore` and are preserved only in the private off-repository backup. The
public customer model is the simplified `customers` table.

## 4. Private upload areas (excluded, `.gitkeep` only)

```
admin/uploads/                       admin/Income/uploads/
admin/Expense/uploads/               admin/assets/uploads/
admin/clients_dis/sections/uploads/  admin/agreements/
**/profile_images/                   **/employee_photos/
```

## 5. What must never be committed

Database dumps, `backups/`, `logs/`, uploaded documents, citizenship images,
agreements, generated PDFs, `.env`, `config.ini`, encryption keys, session files.

Enforced by `.gitignore`.

## 6. Rotation checklist

- [ ] Rotate every third-party credential that was ever stored in the vault
      (email, internet-banking, Meroshare/TMS).
- [ ] Rotate the MySQL password used by the original private deployment.
- [ ] Rotate the moved application encryption key's dependent data (or discard).
- [ ] Rotate any administrator account that existed in the private system.
