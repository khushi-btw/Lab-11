# Luma

A minimal PHP and PostgreSQL sign-in app with a responsive blush, orchid, and plum interface.

## Run locally

Requires PHP with `pdo_pgsql` and a PostgreSQL database.

1. Create the database configured in `.env` (the example uses `lab11_db`).
2. Copy `.env.example` to `.env` and enter your database connection details.
3. Create the `users` table and add the optional demo account:

   ```sh
   psql -U postgres -d lab11_db -f database/schema.sql
   php scripts/seed_demo_user.php
   ```

   The demo account is `lab11student` / `Demo@12345`. The seeder does not replace existing users.
4. Start the app from this folder:

   ```sh
   php -S localhost:8000
   ```

5. Visit <http://localhost:8000>.

The login form checks the database on the server. User passwords are stored as hashes; sessions keep authenticated users signed in until logout.
