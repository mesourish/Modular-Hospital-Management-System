# Security Policy 🛡️

Healthcare systems process sensitive Patient Health Information (PHI) and clinical records. At **OpenHMS**, we take security and data isolation extremely seriously.

---

## Supported Versions

We release security patches for the current and recent major versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

---

## Reporting a Vulnerability

If you discover a security vulnerability, cross-tenant data leak, privilege escalation issue, or authentication bypass, **please DO NOT open a public GitHub issue**.

Instead, report it privately to our security team:

1. **Email**: Send details to **sourish@openhms.org** or use GitHub's private vulnerability reporting feature (under the repository's **Security** tab &rarr; **Report a vulnerability**).
2. **Details to include**:
   - Step-by-step instructions to reproduce the vulnerability.
   - Affected controllers, routes, models, or database migrations.
   - Potential impact (e.g., cross-tenant visibility, unauthorized data deletion).
   - Any suggested patch or remediation.

---

## Security Architecture Principles

- **Multi-Tenant Isolation**: All tenant-bound Eloquent queries automatically include the global `TenantScope`.
- **Branch Bounding**: Branch administrative actions are scoped strictly by `$user->branch_id`.
- **Data Protection**: Patient passwords and medical record tokens are hashed using bcrypt.
- **Factory Reset Safeguards**: The "Clear All Data" action requires administrator authentication, password check, and explicit manual confirmation.
