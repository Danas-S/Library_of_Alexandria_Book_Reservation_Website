# CA1 review and verification

## Sources and scope

Reviewed every tracked project file, including the 40-page `C24344923_WebDCA.pdf`: design text on pages 1-9, website screenshots on pages 10-13 and source-code screenshots on pages 14-40. The PDF was not edited. The repository started clean on `main` at `c5a1f0f`.

No lecturer brief, lab sheet, marking scheme, separate supplied dataset or editable report source was present. The checklist below covers the report and the requested repairs. It is **not** a claim to know the missing lecturer requirements. The report links to a separate SharePoint code folder; that external copy was not part of the repository review.

## Checklist

PASS means the implementation meets the available description. PARTIAL means it worked only in some cases or had a relevant defect. FAIL means the requested behaviour was absent or broken. NOT VERIFIABLE identifies evidence that was unavailable.

| Requirement / claim | Before | After / evidence |
| --- | --- | --- |
| Simple PHP/MySQL/mysqli project, no framework | PASS | PASS; retained |
| Required page set and shared header/footer | PASS | PASS on rendered pages; action handlers redirect |
| Alexandria theme, welcome page and consistent navigation | PARTIAL | PASS by source/HTTP review; visual appearance not runtime-tested |
| Anonymous search; authentication-aware navigation | PASS | PASS; also corrected the valid username `0` edge case |
| Registration: every field required | PASS | PASS; malformed array input also handled |
| Valid email; numeric mobile of exactly ten digits | PASS | PASS |
| Password minimum six characters and matching confirmation | PARTIAL | PASS; counts UTF-8 characters, checks bcrypt byte limit and null characters |
| Unique username, successful registration leads to login | PARTIAL | PASS; duplicate insert races also handled |
| Field lengths fit database columns | FAIL | PASS; username/full name/email limits checked |
| Store hashes, verify passwords, reject incorrect login | PASS | PASS; hashes inspected and login exercised |
| Successful login reaches Search | FAIL | PASS; removed the `from_home` guard |
| Session checks on protected pages/actions | PASS | PASS; canonical username retained |
| Session ID renewal after login and full logout cleanup | PARTIAL | PASS; new ID, old session invalidation and cookie removal verified |
| Title and author search, category dropdown, combined filters | PASS | PASS; all eight title/author/category combinations checked |
| Hide-reserved option enabled and disabled | PASS | PASS; also exercised after reserving a book |
| Maximum five books per page | PASS | PASS |
| Pagination retains all search filters | FAIL | PASS; actual page 2 tested with matching fixtures |
| Invalid/out-of-range pages, new search starts at page 1 | PARTIAL | PASS; clamp before calculating offset; search form omits page |
| Unknown category input, missing category description | PARTIAL | PASS; validated dropdown IDs and Unknown fallback |
| Empty results are understandable | PARTIAL | PASS; explicit no-match message |
| Available/Reserved status without per-row queries | PARTIAL | PASS; shared LEFT JOIN in count and result queries |
| Logged-in users reserve existing available books | PASS | PASS; invalid ISBNs and anonymous actions rejected |
| One reservation per ISBN, including competing requests | PASS | PASS; original unique constraint retained and exercised concurrently |
| Reservation date stored and reservation survives navigation | PASS | PASS |
| Safe return to the same search after reserving | FAIL | PASS; fixed local route plus encoded, allow-listed search fields |
| View only own reservations; delete using ID and username | PASS | PASS; cross-user removal attempt leaves data intact |
| Removal uses POST/Redirect/GET and accurate messages | FAIL | PASS; 303 on success/failure, one-time flash, affected-row check |
| Parameterised user-controlled SQL | PASS | PASS; retained throughout |
| Consistent escaping of dynamic HTML/attributes | PARTIAL | PASS; stored/reflected special-character tests |
| CSRF protection for state-changing requests | FAIL | PASS; shared session token on all POST forms |
| Four original tables, foreign keys, six categories, 25 books | PASS | PASS; original seed statements unchanged |
| utf8mb4 and foreign-key-capable table engine | PARTIAL | PASS; explicitly set InnoDB/utf8mb4 on every table |
| Repeatable SQL import | FAIL | PASS; tested against an empty database and existing copy |
| Configurable XAMPP connection, no public driver details | PARTIAL | PASS; ignored optional config, generic errors and server logs |
| Valid home links, associated labels and styled text inputs | PARTIAL | PASS by source/HTTP review; browser rendering not runtime-tested |
| Useful setup, database, authentication and usage documentation | FAIL | PASS; expanded README and this review |
| All lecturer requirements, exact supplied data, marking/report rules | NOT VERIFIABLE | NOT VERIFIABLE; source documents absent |

## Report statements needing clarification

- Page 9 says the system completes every requirement in the brief. This remains **not verifiable** without that brief and marking scheme.
- Pages 4 and 8 say header/footer are included on all pages. They are shared by rendered user-facing pages. `reserve.php` and `logout.php` are action handlers and intentionally redirect without rendering a layout.
- Page 6 says `reserve.php` is called only from `index.php`. The normal form originates there, but a client can request the endpoint directly; login, POST, CSRF, ISBN and database checks enforce access.
- Page 7 says users can remove "any reservation". This means any reservation **belonging to the signed-in user**, not another user's reservation.
- The Users field list on page 2 omits the existing `fullname` column. The screenshot list on page 9 uses `my_reservation.php`; the actual filename is `my_reservations.php`.
- Pages 7-8 could distinguish validation from hashing more precisely: registration validates the password rules, `password_hash()` stores a hash, and `password_verify()` checks login credentials.
- The original code/screenshots are now historical. They still show the old routing, raw return URL, per-book queries and earlier form handling. The implementation additionally uses CSRF tokens, session renewal, password/field upper limits and PRG.
- Page 9's historical testing claims cannot establish what was tested at submission time. Current verification is recorded below. Login-to-search, five results per page, combined filters, hiding reserved books, availability, uniqueness and ownership now match the report's intended behaviour.

## Verification performed

Used the installed XAMPP PHP **8.2.12** with mysqli/mysqlnd/mbstring and MariaDB **10.4.32**. Started a disposable MariaDB data directory under the system temporary directory on `127.0.0.1:33317`, with PHP's local HTTP server on `127.0.0.1:8137`. The user's normal MySQL data directory was not used. Temporary accounts and catalogue fixtures existed only in this isolated database.

- PHP syntax checks for every application and sample PHP file; Git diff/whitespace review.
- SQL import into an empty database and a second import over the existing schema. Verified 25 books, six categories, all three foreign keys, the unique ISBN index and four InnoDB/utf8mb4 tables. Compared the seed statements with the original commit.
- **369 passing HTTP assertions** using actual requests, cookies and the database: registration failures/success, duplicates, password hashes, valid/invalid login, regenerated session ID, cookie flags, old-session rejection, anonymous access, all filter combinations, pagination, reservations, CSRF, ownership, redirects, flashes and logout.
- Added twelve temporary Java titles to exercise a real second page with title + author + category + hide-reserved. Verified every parameter survives the page link and reservation return. Also checked the last page shrinking after reservations.
- Checked malformed arrays, invalid/negative/huge page numbers, unknown categories, empty results, SQL-injection strings, invalid ISBNs and invalid removal IDs.
- Checked stored/reflected `<`, `>`, `&`, double quotes and apostrophes in book data, category options, usernames, hidden inputs and reservations. Included a missing category, a null author and username `0`.
- Used two independent database connections to attempt the same reservation concurrently. Exactly one insert succeeded; the other failed with the duplicate-key constraint. This verifies the database safeguard, not a load test of concurrent HTTP traffic.
- Forced connection/schema errors and mutation failures. Verified generic browser errors, 303 redirects for failed mutations, and technical diagnostics in the server log. No PHP warnings, deprecations or fatal errors appeared during the successful full suite.

**Not runtime-tested:** browser rendering, keyboard/mobile layout, browser confirmation dialogs and Apache/subdirectory integration. The browser tool exposed no available browser. Automatic approval review rejected starting the temporary Apache server as "blocked by policy" without a more specific reason. Other PHP/MySQL versions, HTTPS/proxy deployment and production load were not tested.

## Manual XAMPP checks

1. Follow the README installation instructions on a disposable development copy. Import twice only if losing the copy's accounts/reservations is acceptable. Confirm the 25-book catalogue after each import.
2. Register two accounts. Try empty fields, invalid email/mobile, a five-character password, mismatched confirmation, an overlong field and a duplicate username. Log in with incorrect and correct credentials.
3. Check Home, Search, Register and Login while signed out, then Home, Search, My Reservations and Logout while signed in. Confirm successful login opens Search under your `htdocs` subfolder URL.
4. On the fresh catalogue, search for title `a`, category **Fiction**, with **Hide reserved books** enabled. There are enough matching books for a second page. Check page 2 retains the filters. Test author-only and other filter combinations, page 0, a very large page and a no-match search.
5. Reserve a book and inspect its status with hide-reserved on and off. In another browser/profile, log in as the second account and confirm that the same book cannot be reserved and is absent from that account's reservations.
6. Remove your reservation, then refresh. Confirm the message appears once and the browser does not ask to resend a POST. Verify the other account's reservations remain untouched.
7. Inspect desktop and narrow/mobile layouts, keyboard focus, form labels, table scrolling, home links, fonts and the Remove confirmation dialog. These visual/browser checks remain outstanding.
8. If exposing the site beyond local XAMPP, separately verify HTTPS cookies, database account permissions and server configuration. Do not use the local root/blank-password defaults for a public deployment.
