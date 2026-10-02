# Security Policy

## Supported versions

The `main` branch receives security fixes.

## Reporting a vulnerability

**Please do not open a public issue for security vulnerabilities.**

Report privately using GitHub's **"Report a vulnerability"** feature on the
Security tab of the repository (Private Vulnerability Reporting), or contact the
maintainer through the repository profile.

Include:

- A description of the issue and its impact.
- Steps to reproduce (proof of concept if possible).
- Affected files/versions.
- Any suggested remediation.

You can expect an acknowledgement within a few days. Please allow a reasonable
disclosure window before any public discussion.

## Scope

In scope: authentication, authorisation, CSRF, session handling, SQL injection,
file upload handling, unsafe file download, and exposure of secrets or personal
data.

Out of scope: issues that require an already-compromised host, and social
engineering.

## Baseline guarantees

- Credentials hashed with `password_hash()`; verified with `password_verify()`.
- Prepared statements for database access.
- CSRF tokens on state-changing requests.
- Secure session cookies, id regeneration and inactivity timeout.
- No third-party credential storage (no email/bank passwords, security answers
  or OTP secrets).
