# Luma

A PHP and PostgreSQL sign-in app with a responsive blush, orchid, and plum interface. New users can create an account and sign in immediately.

## Run locally

Requires PHP with `pdo_pgsql` and a PostgreSQL database.

1. Create the database configured in `.env` (the example uses `lab11_db`).
2. Copy `.env.example` to `.env` and enter your database connection details. The app accepts either the existing `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `DB_SSLMODE` settings or a PostgreSQL `DATABASE_URL`.
3. Create the `users` table:

   ```sh
   psql -U postgres -d lab11_db -f database/schema.sql
   ```

   To add the optional evaluation account, run `php scripts/seed_demo_user.php`. It does not replace existing users.
4. Start the app from this folder:

   ```sh
   php -S localhost:8000
   ```

5. Visit <http://localhost:8000>.

Select **Create one** below the sign-in form to register. Usernames must be 3–32 characters (letters, numbers, dots, underscores, or hyphens); passwords must be at least 8 characters. Passwords are stored as hashes. Registration and login use the same `users` table, and successful sign-in opens the protected welcome page until logout.
