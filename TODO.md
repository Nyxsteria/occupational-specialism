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
- [x] Apply rolling-window login throttles to username/source-IP pairs and source IPs, without account-only lockouts; see `LOGIN_SECURITY.md`.

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
  - [ ] Return a generic error message instead of exception text or database details.

- [ ] **Fix the admin authentication-events include path.**
  - [ ] Replace the incorrect concatenated `__DIR__ . './db.php'` path in `admin-auth-events.php` with a valid absolute path consistent with the other PHP files.

- [ ] **Fix broken branch resources and links.**
  - [ ] Restore the missing `scripts/username-retrieve.js` referenced by `login.php`, or remove/update the reference.
  - [ ] Fix the admin-panel stylesheet link so it resolves to `css/admin-panel.css`.
  - [ ] Replace or implement missing administration, password-recovery, and navigation destinations.

## High-priority improvements

- [x] **Add login abuse controls.**
  - [x] Add rate limiting for failed login attempts.
  - [x] Temporarily throttle repeated attempts using rolling 15-minute windows and a `Retry-After` response.
  - [x] Track by username/source-IP pair and by source IP across accounts.
  - [x] Avoid account-only lockouts that enable denial-of-service attacks.
  - [ ] Consider MFA for administrator accounts; this requires a separate enrollment, verification, and recovery flow.

- [ ] **Harden session cookies and enforce HTTPS.**
  - [ ] Set the session cookie to `Secure` in production.
  - [ ] Set the session cookie to `HttpOnly`.
  - [ ] Set an appropriate `SameSite` value.
  - [ ] Enable strict session mode.
  - [ ] Configure reasonable session and idle-expiry lifetimes.
  - [ ] Continue regenerating session IDs after privilege changes and authentication transitions.

- [ ] **Complete secure password recovery.**
  - [ ] Use single-use, randomly generated recovery tokens.
  - [ ] Set a short expiry time for recovery tokens.
  - [ ] Return the same response regardless of whether an account exists.
  - [ ] Invalidate recovery tokens after use or password change.
  - [ ] Implement the missing password-recovery interface and workflow.

- [ ] **Strengthen server-side registration validation.**
  - [ ] Enforce username and password length limits.
  - [ ] Enforce a password-strength policy.
  - [ ] Normalize and validate usernames consistently.
  - [ ] Ensure usernames have a unique database constraint to handle concurrent registrations safely.

- [ ] **Protect state-changing requests with CSRF prevention.**
  - [ ] Change logout from a GET link to a POST request.
  - [ ] Add a CSRF token to logout and all future authenticated write operations.
  - [ ] Validate CSRF tokens on the server for every state change.

## Recommended improvements

- [ ] Improve authentication-log privacy and retention.
  - [ ] Restrict access to authentication logs.
  - [ ] Define retention and deletion policies for authentication events.
  - [ ] Avoid unnecessary IP address and user-agent data where it is not required.
  - [ ] Document and justify any personal data collected in login logs.

- [ ] Add security headers.
  - [ ] Add a Content Security Policy.
  - [ ] Configure appropriate `X-Content-Type-Options`, frame protection, referrer policy, and permissions policy headers.
  - [ ] Verify headers in the hosting environment and avoid policies that unnecessarily block required application behaviour.

- [ ] Complete the profile and dashboard implementation.
  - [ ] Replace static example statistics, deadlines, and activity in `profile.html` with real application data or clearly label prototype content.
  - [ ] Resolve inconsistencies between `main.php`, `profile.html`, and database-backed statistics.
  - [ ] Make dashboard controls perform their advertised actions.

- [ ] Complete unfinished user-facing features.
  - [ ] Implement or remove unfinished controls in `main.php`.
  - [ ] Implement `user-management.html` and `user-management.php` or clearly remove them from the navigation.
  - [ ] Implement the missing password-recovery interface.
  - [ ] Ensure every visible link, button, and form works or is clearly marked as outside the current scope.

- [ ] Improve testing and accessibility.
  - [ ] Test registration, login, logout, protected pages, profile loading, statistics, and admin access.
  - [ ] Test both administrator and regular-user authorization paths.
  - [ ] Check mobile layouts, keyboard navigation, focus states, labels, error messages, and colour contrast.
  - [ ] Remove placeholder imagery and replace generic copy with consistent, relevant content.

- [ ] Improve maintainability and repository hygiene.
  - [ ] Remove empty or unused files and dead code.
  - [ ] Standardise file paths and naming.
  - [ ] Remove debugging `console.log()` calls and other development output.
  - [ ] Document setup, database configuration, required PHP extensions, and deployment steps.

- [ ] Improve coursework evidence and documentation.
  - [ ] Explain the user need and requirements.
  - [ ] Document database structure and design decisions.
  - [ ] Record implementation decisions and security rationale.
  - [ ] Include test plans, expected results, actual results, and fixes.
  - [ ] Include feedback and a clear evaluation against the original goals.

## Verification after changes

- [ ] Rotate and remove all credentials before a new deployment.
- [ ] Search Git history for secret names and secret values.
- [ ] Test login, logout, registration, password recovery, and protected-page access over HTTPS.
- [ ] Test login throttling and administrator authorization (manual throttle cases are documented in `LOGIN_SECURITY.md`).
- [ ] Test for SQL injection, reflected and stored XSS, CSRF, session fixation, insecure direct object access, and information disclosure.
- [ ] Confirm error responses do not disclose stack traces, paths, SQL, or database information.
- [ ] Confirm authentication logs exclude or protect sensitive session and personal data.
