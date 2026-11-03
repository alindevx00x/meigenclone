# MeiGen Prompt Directory

A Laravel prompt directory inspired by MeiGen’s airy AI inspiration gallery. It includes public browsing, creator accounts, a review queue, and an admin studio.

## Stack

- Laravel 12 / PHP 8.2+
- MySQL 8+
- Tailwind CSS 4 through Vite
- Blade views with responsive custom styling

## Local setup

1. Install PHP dependencies and copy the environment file:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Create a MySQL database named `meigen_directory`, then set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

3. Run migrations, add the public cover-image link, and seed the sample directory:

   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

4. Build the front-end assets and start Laravel:

   ```bash
   npm install
   npm run build
   php artisan serve
   ```

The gallery is available at `http://127.0.0.1:8000`.

## Demo accounts

The seeder creates two ready-to-use accounts. Both use `password` as the password.

- Admin: `admin@meigen.test`
- Creator: `creator@meigen.test`

## Prompt workflow

- Visitors can browse approved prompts by model, category, popularity, and keyword.
- Members can save prompts, submit their own prompts, and track review status from the creator dashboard.
- New and edited member prompts enter the pending queue and stay hidden from the public gallery until approved.
- Admins can edit, approve, decline, feature, and remove prompts; create and manage categories; and grant or remove administrator access.
- Uploaded prompt covers are stored on Laravel’s public disk. Seeded example prompts use remote image URLs.
- Every uploaded cover is converted to WebP at quality 82, stripped of metadata, and capped at 2,400 pixels on its longest edge. Existing covers can be converted with `php artisan prompt-covers:convert-to-webp`; the server needs Imagick or GD with WebP support.
- Admin directory settings include SMTP delivery options and search/social metadata controls.
