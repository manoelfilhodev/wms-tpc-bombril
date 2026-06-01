# Security Risk Acceptance

## firebase/php-jwt CVE-2025-45769

- Advisory: CVE-2025-45769 / GHSA-2x45-7fc3-mxwq
- Package: `firebase/php-jwt`
- Severity: low
- Status: temporarily accepted
- Date accepted: 2026-06-01
- Review cadence: monthly

## Reason for Temporary Acceptance

`firebase/php-jwt` is currently present as a transitive dependency. The project does not directly use this package for its own authentication flow; operational API authentication is handled with Laravel Sanctum and server-side session controls.

The advisory is low severity and the available remediation path depends on upstream package compatibility. The risk is accepted temporarily so the security pipeline can remain explicit about the finding while avoiding an unsafe dependency override.

## Mitigating Controls

- Sanctum protects internal API routes.
- Login responses avoid user enumeration.
- Rate limits protect login, API, critical and admin operations.
- Audit logs and security alerts record authentication and authorization anomalies.
- Composer audit runs in CI and weekly dependency monitoring.

## Monthly Review Plan

Review the advisory and dependency tree monthly:

```bash
composer audit
composer why firebase/php-jwt
composer outdated firebase/php-jwt laravel/socialite
```

Track whether upstream packages allow `firebase/php-jwt` `>=7.0.0` or remove the transitive exposure.

## Correction Trigger

Remediate immediately if any of these conditions occur:

- severity increases above low;
- the package becomes directly used by application authentication;
- an exploit becomes public and relevant to the deployed version;
- upstream releases a compatible fix;
- compliance or customer requirements forbid accepted dependency advisories.

