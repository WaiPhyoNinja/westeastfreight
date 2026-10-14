# Quote form security audit — 2026-10-12

Scope: local application source, isolated SQLite feature tests, local browser with mocked account/quote responses. No live customer payloads, SMTP messages, production scanning, or load testing. Application behavior has not been patched by this audit; the added tests characterize current behavior.

## Findings

### 1. Confirmed stored-data XSS sink in customer quote details — medium, fix first

`resources/frontend/components/MyAccount.vue:386` constructs HTML by interpolating raw quote values, including description at line 632 and special instructions at line 637, and passes it to `Swal.fire({html: details})` at line 647. Sender/receiver fields have the same pattern. `RequestQuote.vue:165` only trims text; this is not HTML escaping.

Evidence: the API accepts and stores an image tag with an event handler as a description in an isolated test. A browser rendering the same payload through a mocked quote API executed a harmless `window.__quoteAuditXss` marker when opening View Details. No data exfiltration or persistent local payload was used. The email receipt escapes the same input correctly.

Impact boundary: the demonstrated popup is owner-only. Cross-account exploitation and administrator execution were not demonstrated. Do not claim an admin takeover from this reproduction.

Fix: render customer values using Vue text interpolation or DOM textContent, or consistently escape every interpolated value at the HTML output boundary. Do not rely on client-side input escaping. Keep stored data as plain text. Add a browser regression assertion that the marker never executes.

### 2. Unverified accounts can enqueue mail to supplied third-party addresses — medium

`CustomerAuthController::register` immediately authenticates accounts without email verification. The quote route requires auth, but not verified email. `QuoteRequestController::store` queues receipts using `QuoteMailDispatcher::recipients`, which trusts validated sender/receiver email strings rather than proving ownership or consent. Quotes allow 20 requests/minute per authenticated identity; two distinct recipients can mean up to 40 queued messages/minute/account. These are code-derived bounds, not a load test. Contact and welcome mail also need abuse controls.

Fix: verify account email before quote-triggered delivery, add per-account/IP and per-recipient cooldowns and daily quotas, and explicitly decide how receiver notifications are authorized. Preserve legitimate sender/receiver business workflows.

### 3. Freight form and API validation differ — low / data integrity

`QuoteRequestController.php:55-63` makes transport mode optional and conditions related fields on its presence. A direct request using the older payload can omit the whole freight group. This may support legacy callers but does not enforce the new UI's required fields.

The API accepts arrival_date `2000-01-01` and sender_phone `not a phone`. Phone is only a string with a 255-character limit; date validation checks format, not a minimum date. Decide whether old pending requests may retain historical arrival dates before tightening update rules. Dimensions can be partially supplied. These are validation issues, not evidence of server code execution.

### 4. Local response hardening / production checks — deployment follow-up

The local preview response has no Content-Security-Policy, X-Frame-Options, or X-Content-Type-Options header. Local runtime reports APP_ENV=local, APP_DEBUG=true, SESSION_HTTP_ONLY=true, SESSION_SECURE=null, SameSite=lax. Debug=true is normal locally; production configuration was not inspected and is not alleged to be vulnerable. The web server may apply different headers in deployment.

Before production: verify debug disabled, HTTPS and secure session cookies, public-only document root, secrets and logs inaccessible, appropriate anti-framing and MIME-sniffing headers, and a tested CSP that accommodates the site's existing scripts. API quote listing loads all owned quotes without server pagination; pagination and a read throttle would bound work as history grows.

## Controls verified

- Guests cannot list/create quotes (401).
- Cross-account listing/edit attempts do not expose or modify another account's quotes (404 for edit).
- Posted ownership/status values cannot override server assignment.
- Reviewed/locked quotes reject edits (409).
- Negative numbers, fractional quantity, oversized description, nested name input, invalid enum/boolean values, CRLF email, and a SQL-like country string are rejected (422).
- Receipt HTML escapes the tested HTML payload.
- A tokenless local HTTP POST was rejected with 419. Standard feature tests alone do not prove CSRF because Laravel bypasses it in testing.
- The 21st quote request within a minute was rejected (429).

## Validation

`php artisan test --compact --filter='QuoteSecurityAuditTest|FrontendIntegrationTest|RolePermissionManagementTest|CustomerEmailWorkflowTest'`

29 tests passed, 298 assertions. Two audit tests intentionally document accepted validation gaps; a green suite does NOT mean all findings are fixed.

Local browser reproduction: XSS marker executed; CSRF 419; headers inspected. No SMTP delivery or real customer mutation.

## Limits and next action

Prioritize fixing the quote-details HTML sink, then mail abuse controls and agreed validation rules. Infrastructure, dependency advisory scanning, upload handling, every CMS HTML sink, and production Gmail/Outlook execution were not exhaustively audited. Existing CMS v-html/raw HTML rendering needs a separate trust-boundary and sanitization review. No claim of comprehensive protection against server attacks, SQL injection, RCE, or DDoS is made.

Reference: https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html (context-specific output encoding and safe rendering sinks).
