## Creations JY

Upcycling products showcase website for Yasemin Jemmely, built with PHP 8, MySQL 8, Tailwind CSS, and vanilla JavaScript.

### Local setup (overview)

1. Create a MySQL database named `creationsjy_db`.
2. Import `database/schema.sql` into that database.
3. Configure database credentials in `includes/config.php` (will be created in a later step).
4. Run the site via a local PHP server or a stack like MAMP/XAMPP.

See `DEVELOPMENT_ROADMAP.md` for the detailed functional and technical roadmap.

### Admin onboarding

1. (Optional but recommended) Set `ADMIN_SIGNUP_TOKEN` in `includes/config.php` or via env vars. When empty, only the very first admin can be created (fresh install).
2. Visit `/admin/signup.php` to create the first Super Admin. If a token is configured, enter it in the form.
3. Subsequent accounts can be created from the admin area or again via `/admin/signup.php` using the token if you are locked out.
4. Once logged in, change your password from **Settings → Users**.

