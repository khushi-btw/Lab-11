# Lab 11 - Database-Based User Login Validation

A beginner-friendly PHP 8 and PostgreSQL login application. The Luma interface uses a charcoal, purple, and lavender palette, with a responsive sign-in card and matching authenticated welcome page.

## Project structure

- `index.php` - redirects to the sign-in page.
- `login.php` - form, server-side required-field validation, and database authentication.
- `dashboard.php` - session-protected personalized welcome page.
- `logout.php` - clears the session and returns to sign in.
- `config/database.php` - PostgreSQL PDO connection using environment settings.
- `includes/session.php` - secure session and CSRF helpers.
- `database/schema.sql` - non-destructive `users` table definition.
- `scripts/seed_demo_user.php` - creates the table if needed and inserts a demo user only if absent.
- `assets/css/style.css`, `assets/js/script.js` - application styling and small interface interactions.

## Local setup

Requirements: PHP 8 with `pdo` and `pdo_pgsql`, and PostgreSQL.

1. Create a database (if one does not already exist):

   ```sql
   CREATE DATABASE lab11_db;
   ```

2. Copy `.env.example` to `.env` and set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. Keep `.env` local; it is ignored by Git.
3. From this directory run the schema and safe demo seeder:

   ```sh
   psql -U postgres -d lab11_db -f database/schema.sql
   php scripts/seed_demo_user.php
   ```

   The seeder inserts the evaluation account `lab11student` with password `Demo@12345` only when that username is not already present. It does not overwrite existing records. For a new user, insert a username and a PHP `password_hash()` value into `users`.
4. Start the PHP development server from this project directory:

   ```sh
   php -S localhost:8000
   ```

5. Open <http://localhost:8000> and sign in. Use the seeded evaluation account, or an existing account in your database.

## Authentication flow

The server rejects missing fields before querying. It selects the matching user using a PDO prepared statement, then checks the submitted password with `password_verify()` against the stored `password_hash`. A match regenerates the PHP session ID and stores the user's ID and name in the session before redirecting to the protected dashboard. A mismatch produces the same generic error for unknown names and wrong passwords. User-entered values are escaped on output; the password is never printed in the page.

## Evaluation cases

- Correct account: personalized welcome page.
- Unknown username or incorrect password: generic sign-in error.
- Both fields empty, or either field empty: required-field validation errors.
- SQL-injection-style username: treated as a literal username by the prepared query; no match returns the generic error.
- Logout: the session is destroyed and sign-in is shown again.
