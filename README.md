# Library of Alexandria Book Reservation System

## About

A PHP and MySQL library reservation website built for Web Development 2 CA1 at TU Dublin. Readers can search a small catalogue, reserve available books and manage their reservations. The design uses an Ancient Library of Alexandria theme, with shared navigation and a simple page-based PHP structure.

## Features

- Account registration with server-side validation
- Login and logout using session-based authentication
- Book search by title, author and category, including combined filters
- An option to hide reserved books
- Five books per results page, with filters retained between pages
- Available / Reserved status for each book
- Reservations for signed-in users, with one active reservation per ISBN
- A personal reservations page with removal of the user's own reservations
- Prepared SQL statements for user-supplied values and PHP password hashing

## Technologies

PHP, MySQL / MariaDB, mysqli, HTML and CSS. XAMPP provides a convenient local Apache, PHP and database environment. No framework, Composer packages or JavaScript build tools are required.

## Requirements

- XAMPP, or an equivalent Apache/PHP/MySQL environment
- PHP with `mysqli`, `mysqlnd` (for `get_result()`) and `mbstring` enabled
- MySQL or MariaDB with InnoDB and `utf8mb4` support
- A browser with cookies enabled

The application was runtime-tested with XAMPP's **PHP 8.2.12** and **MariaDB 10.4.32**, using PHP's built-in local server. Other versions and the Apache integration are **Not runtime-tested** in this review. The fonts use Google Fonts; local serif fonts are used if it is unavailable.

## Installation / Running Locally

1. Clone or download this repository.
2. Place its folder under your XAMPP `htdocs` directory. For a default Windows installation, use:

   ```text
   C:\xampp\htdocs\Library_of_Alexandria_Book_Reservation_Website\
   ```

   `index.php` and `library.sql` should be directly inside that folder.
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Open [phpMyAdmin](http://localhost/phpmyadmin/).
5. Use **Import** to import `library.sql`. It creates `librarydb`, its four tables, six categories and all 25 books.

   **This is a development reset script. Re-importing it deletes existing accounts, reservations and catalogue edits, then restores the sample catalogue. Back up any data you want to keep first.**
6. Check the database connection. The defaults are `127.0.0.1`, port `3306`, user `root`, an empty password and database `librarydb`, matching a standard local XAMPP setup.

   If your settings differ, copy `db_config.sample.php` to `db_config.local.php` in the same folder and edit the values in the returned PHP array. Leave the PHP syntax intact. The local file overrides the defaults and is ignored by Git; do not commit real credentials. No local file is needed for the default setup.
7. Open the explicit welcome page:

   ```text
   http://localhost/Library_of_Alexandria_Book_Reservation_Website/home.php
   ```

   The folder URL and `index.php` open **Search**. Home always links to `home.php`; Search and a successful login go to `index.php`. No special routing parameter is needed. If you rename the folder or change Apache's port, adjust the URL accordingly.
8. Register an account through the website. **There is no seeded login account.**

Do not open PHP files through a `file://` URL. If the site displays a generic service error, check that MySQL is running, the SQL import succeeded and the connection settings are correct. Technical details are written to the PHP/server error log configured by your local installation.

## Database

The database is named `librarydb`.

| Table | Purpose |
| --- | --- |
| `users` | Username primary key, full name, email, mobile number, password hash and creation time |
| `categories` | Category codes and descriptions used by the dropdown |
| `books` | ISBN primary key, title, author, category, publisher and publication year |
| `reserved_books` | Reservation ID, username, ISBN and reservation date |

Foreign keys connect books to categories and reservations to users/books. A unique constraint on `reserved_books.isbn` prevents two active reservations for the same book, including competing requests. Tables use InnoDB and `utf8mb4`.

Search joins books to reservations once to calculate availability. The count and results queries use the same filters; the results query sorts by title and ISBN and uses `LIMIT` / `OFFSET`.

## Authentication

Registration requires all fields, a valid email, a mobile number containing exactly ten digits, a unique username and matching passwords of at least six characters. Usernames are limited to 50 characters; full names and email addresses to 100. Passwords are limited to 72 bytes to avoid bcrypt truncation, and null characters are rejected. Non-ASCII characters may occupy several bytes.

Passwords are stored with `password_hash()` and checked with `password_verify()`. A successful login regenerates the session ID and stores the database username in the session. Logout uses a CSRF-protected POST and clears session data and the session cookie.

## Usage

1. **Register**, then **Login**. A successful login opens Search.
2. Search by title, author, category or any combination. Enable **Hide reserved books** if needed.
3. Use the numbered links to move through that search. Submitting the search form starts again at page 1.
4. Select **Reserve** beside an available book. The site returns to the same search context. If the results now have fewer pages, the page is clamped to the last available page.
5. Open **My Reservations** to see your books and reservation dates.
6. Select **Remove** and confirm to release a reservation. The result appears once after a redirect; refreshing does not repeat the removal.
7. Use **Logout** when finished.

Anonymous visitors can search and see availability, but reservation actions and My Reservations require login. Invalid or unknown category inputs are treated as All; missing category descriptions are displayed as Unknown.

## Project Structure

| File | Purpose |
| --- | --- |
| `home.php` | Welcome page and navigation buttons |
| `index.php` | Search, availability, filtering and pagination |
| `register.php` | Account validation and creation |
| `login.php` / `logout.php` | Authentication and session cleanup |
| `reserve.php` | Protected reservation POST handler |
| `my_reservations.php` | The user's reservations and protected removal handler |
| `header.php` / `footer.php` | Shared layout and authentication-aware navigation |
| `functions.php` | Session setup, scalar input/search helpers and CSRF tokens |
| `db_config.php` | Connection defaults and generic error handling |
| `db_config.sample.php` | Example for an optional, untracked local configuration |
| `library.sql` | Repeatable development database reset and seed data |
| `style.css` | Alexandria colours, typography, forms and tables |
| `C24344923_WebDCA.pdf` | Original design report and screenshots, preserved unchanged |
| `REVIEW.md` | Requirement checklist, report discrepancies and verification results |

## Security

- User-controlled SQL values use prepared statements.
- Dynamic HTML text and attributes are escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Protected actions require authentication and a session CSRF token. Login, registration and logout forms also use CSRF tokens.
- Reservation return URLs are built from known search fields and always target `index.php`.
- Removal checks both reservation ID and the authenticated username.
- Strict session mode, cookie-only session IDs, HttpOnly and SameSite=Lax cookies are enabled. The Secure cookie flag is enabled when PHP receives an HTTPS request.
- Database errors are logged while the browser receives a generic message.
- Database constraints enforce valid references and one active reservation per book.

The root/blank-password defaults are for local XAMPP development. For an Internet deployment, use HTTPS, a dedicated database account and server configuration appropriate for that environment. This project does not implement password recovery or login rate limiting.

## Notes and Verification

The repository contains the student's report, but no separate lecturer assignment brief, lab instructions or marking scheme. Complete compliance with those unavailable documents cannot be certified. The report's code screenshots predate the repairs; see [REVIEW.md](REVIEW.md) for specific corrections and manual checks.

The review executed 369 HTTP assertions against an isolated database, plus SQL import, constraint/concurrency and error-handling checks. Browser visual checks and Apache integration are **Not runtime-tested**. The test instance and its temporary configuration were removed from the project's running setup after testing; no test accounts or extra books were added to `library.sql`.
