# Login abuse controls

`login.php` applies temporary limits using the existing `authentication_events` table:

- Five failed attempts for the same submitted username and source IP in a rolling 15-minute window result in HTTP 429 until the oldest failure ages out.
- Thirty failed attempts from one source IP across usernames in the same window also result in HTTP 429. The response includes `Retry-After` and `retry_after`.
- Failed attempts for unknown usernames count toward the same limits, and throttled requests are recorded for audit but do not extend the failure window.
- Usernames/passwords receive generic failure responses. Password values are not trimmed.

There is intentionally no username-only lockout. A remote party therefore cannot permanently or independently lock a known account by submitting its username; these limits target a source address and the source-address/account pair. The source address comes from `REMOTE_ADDR`. If the application is behind a reverse proxy, configure trusted-proxy handling at the server before changing this; never trust arbitrary `X-Forwarded-For` values.

The limits use the current database event history, so keep `authentication_events.event_timestamp`, `event_type`, `username_attempted`, and `ip_address` available. For larger event tables, add database indexes suited to these lookups after checking the actual schema and query plan. Deploy over HTTPS and monitor false positives, especially for users behind shared NAT addresses. These controls do not implement MFA; require MFA for administrator accounts through a separately designed enrollment and recovery flow.

## Manual verification

1. Submit an incorrect password five times for the same username from one source IP; the next attempt should return HTTP 429 and a `Retry-After` header.
2. Confirm another username from the same IP is still allowed until the IP-wide limit is reached.
3. Confirm the same username from a different source IP is not locked solely by the first source's failures.
4. Confirm requests become available after the rolling window expires and throttled attempts do not extend it.
5. Confirm unknown usernames and known usernames have the same credential-failure message, successful login still regenerates the session ID, and password whitespace is preserved.
