# occupational-specialism

A backup of my Occupational Specialism revision project.

## Changelog

This changelog summarizes the project’s development across its branches. The `main` branch is the repository’s default branch; the feature snapshots below are on their own branches.

### 25 November 2026 — `master-v4`

- Fixed the account-creation flow to validate JSON input, preserve password whitespace, create a session after a successful insert, and redirect newly registered users to the PHP dashboard.
- Replaced the static dashboard entry point with a session-protected PHP page that displays the signed-in username.
- Expanded login and logout to regenerate and clear sessions and record successful logins, failed attempts, and logouts in the authentication-events table.
- Added a profile API and client script that fetch the signed-in user’s username and send unauthenticated visitors back to the login page.
- Added an admin login-activity page with database role checks, escaped output, and pagination, plus an API/UI for recent authentication events.
- Added account and login feedback popups, a profile dashboard mock-up, and styling for the admin and profile screens. Organized more stylesheets under `css/`.
- Added empty user-management placeholders; no user-management functionality is implemented yet.

### 11 November 2026 — `master-v3`

- Completed the database-backed login and registration flow using PDO and password-hash verification.
- Added logout/session cleanup, success and error popups, and a redirect into the PHP dashboard after login.
- Corrected stylesheet paths and added a profile-page mock-up and supporting profile endpoint/script.

### 10 November 2026 — `master-v2`

- Began replacing the single-user login with a multi-user account system backed by MySQL/PDO.
- Added account-creation form assets and server-side registration with hashed passwords, plus account-creation and forgot-password links on the login screen.
- Started moving project stylesheets into the `css/` directory.

### Earlier snapshot — `master`

- Established the portal’s initial HTML/CSS/JavaScript pages, including the login screen, dashboard, cookie policy, and shared page styling.

## Branches

- [`main`](https://github.com/Nyxsteria/occupational-specialism/tree/main) — default branch.
- [`master`](https://github.com/Nyxsteria/occupational-specialism/tree/master) — earlier portal snapshot.
- [`master-v2`](https://github.com/Nyxsteria/occupational-specialism/tree/master-v2) — account creation and multi-user login work.
- [`master-v3`](https://github.com/Nyxsteria/occupational-specialism/tree/master-v3) — login/registration improvements and profile-page work.
- [`master-v4`](https://github.com/Nyxsteria/occupational-specialism/tree/master-v4) — latest feature snapshot, including session-protected dashboard and authentication activity pages.

## Security note

Before deploying, move database connection details out of source code and into server-side environment configuration, and rotate any credentials that have ever been committed. Review the authentication-event endpoint and other admin routes in a live environment before relying on them.
