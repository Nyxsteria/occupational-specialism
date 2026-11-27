# Security TODO

This document summarizes the security features currently present in the application and the improvements still required.

## Implemented security features

- [x] Hash passwords with `password_hash()` and verify them with `password_verify()`.
- [x] Use prepared statements for database queries in authentication, profile, and admin-role checks.
- [x] Regenerate the session ID after successful login and account creation.
- [x] Protect pages and API endpoints with authentication checks.
- [x] Check the user's current role in the database for admin endpoints instead of relying only on session state.
- [x] Escape HTML output in the admin panel and use `textContent` in admin JavaScript activity views.
- [x] Record authentication events.
- [x] Use generic failure messages for failed login attempts.
- [x] Set `no-store` cache headers on the new admin and profile JSON endpoints.
- [x] Store a hash of the session identifier in authentication logs rather than the raw session ID.

## Critical improvements

- [ ] **Rotate and remove exposed credentials.** Database credentials are present in `db.php`, and SFTP credentials are present in `.vscode/sftp.json`.
  - [ ] Rotate the database password immediately.
  - [ ] Rotate the SFTP credentials immediately.
  - [ ] Move secrets to environment variables or a deployment secret store.
  - [ ] Remove secret-containing files from the branch and repository history.
  - [ ] Check whether any exposed credentials were reused elsewhere.

- [ ] **Disable displayed PHP errors in production.**
  - [ ] Configure PHP not to display errors to users.
  - [ ] Log detailed errors on the server instead.
  - [ ] Return a generic error message to clients instead of exception text or database details.

- [ ] **Fix the admin authentication-events include path.**
  - [ ] Replace the incorrect concatenated `__DIR__ . './db.php'` path in `admin-auth-events.php` with a valid absolute path, consistent with the other PHP files.

## High-priority improvements

- [ ] **Add login abuse controls.**
  - [ ] Add rate limiting for failed login attempts.
  - [ ] Consider progressive delays or temporary account throttling.
  - [ ] Track limits by account and source address where appropriate.
  - [ ] Avoid account lockout rules that enable denial-of-service attacks.
  - [ ] Consider MFA for administrator accounts.

- [ ] **Harden session cookies and enforce HTTPS.**
  - [ ] Set the session cookie to `Secure` in production.
  - [ ] Set the session cookie to `HttpOnly`.
  - [ ] Set an appropriate `SameSite` value.
  - [ ] Enable strict session mode.
  - [ ] Configure reasonable session and idle-expiry lifetimes.
  - [ ] Continue regenerating session IDs after privilege changes and authentication transitions.

## Recommended improvements

- [ ] **Add CSRF protection for state-changing requests.**
  - [ ] Change logout from a GET link to a POST request.
  - [ ] Add a CSRF token to logout and all future authenticated write operations.
  - [ ] Validate the CSRF token on the server for every state change.

- [ ] **Implement password recovery securely.**
  - [ ] Use single-use, randomly generated recovery tokens.
  - [ ] Set a short expiry time for recovery tokens.
  - [ ] Use the same response regardless of whether an account exists.
  - [ ] Invalidate recovery tokens after use or password change.

- [ ] **Strengthen server-side registration validation.**
  - [ ] Enforce username and password length limits.
  - [ ] Enforce a password-strength policy.
  - [ ] Normalize and validate usernames consistently.
  - [ ] Ensure usernames have a unique database constraint to handle concurrent registrations safely.

- [ ] **Improve authentication-log privacy and retention.**
  - [ ] Restrict access to authentication logs.
  - [ ] Define retention and deletion policies for authentication events.
  - [ ] Avoid storing unnecessary IP address or user-agent data when not required.
  - [ ] Document or justify any personal data collected in login logs.

- [ ] **Add security headers.**
  - [ ] Add a Content Security Policy.
  - [ ] Configure appropriate `X-Content-Type-Options`, frame protection, referrer policy, and permissions policy headers.
  - [ ] Verify headers are present in the hosting environment and remain strict enough to avoid known application behavior from being blocked.

## Verification after changes

- [ ] Rotate and remove all credentials before performing a new deployment.
- [ ] Search Git history for secret names and secret values.
- [ ] Test login, logout, registration, and password recovery on HTTPS.
- [ ] Test login throttling and admin authorization.
- [ ] Test for SQL injection, reflected or stored XSS, CSRF, session fixation, and insecure direct object access.
- [ ] Confirm error responses do not disclose stack traces, paths, SQL, or database information.
- [ ] Confirm authentication logs exclude or protect sensitive information.
