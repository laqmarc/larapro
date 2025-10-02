# LaraPro

LaraPro is a full-stack Laravel 12 application for discovering, authoring, and saving cooking recipes. It delivers a filterable public catalog, rich authoring tools for home cooks, and personal cookbooks powered by authentication from Laravel Breeze with a Tailwind CSS and Alpine.js front end.

## Features
- Browse a public recipe catalog with full-text search, difficulty and dish-type filters, dietary tags, ingredient targeting, saved-only, and mine-only toggles.
- Author detailed recipes with structured ingredients, cooking times, servings, optional nutrition facts, and photo galleries.
- Toggle recipe visibility between private drafts and public posts with automatic publish timestamps and slug management.
- Save favorite public recipes to a personal collection and quickly revisit them from the "Saved" view.
- Review recipe pages enriched with media, tags, nutrition, related suggestions, and published community comments.
- Email-verified authentication, profile management, and policy-based access control for editing or deleting authored recipes.

## Tech Stack
- PHP 8.2 with Laravel 12
- SQLite by default (swap to MySQL/PostgreSQL via `.env`)
- Tailwind CSS, Alpine.js, Vite, and Laravel Breeze
- Laravel Pail for real-time log streaming and queue worker during development

## Getting Started

### Prerequisites
- PHP 8.2 with the `pdo_sqlite`, `openssl`, `mbstring`, `fileinfo`, and `intl` extensions
- Composer
- Node.js 18+ and npm
- SQLite (bundled with PHP) or another database supported by Laravel

### Installation
1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Install front-end dependencies:
   ```bash
   npm install
   ```
3. Copy the environment file and adjust any settings you need (app name, mail driver, etc.):
   ```bash
   cp .env.example .env
   ```
4. If you are sticking with SQLite, make sure the database file exists (Laravel's default `.env` already points to it):
   ```bash
   php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
   ```
5. Generate the application key:
   ```bash
   php artisan key:generate
   ```
6. Run the database migrations and seeders to create demo content, dietary tags, and a test user:
   ```bash
   php artisan migrate --seed
   ```
7. Expose the recipe media stored on the `public` disk:
   ```bash
   php artisan storage:link
   ```

### Running the app
- Start the HTTP server and Vite separately:
  ```bash
  php artisan serve
  npm run dev
  ```
- Or use the bundled concurrent runner (serves Laravel, queue worker, log stream, and Vite in one command):
  ```bash
  composer run dev
  ```

### Default accounts
The database seeder creates a ready-to-use login:
- Email: `test@example.com`
- Password: `password`

### Testing
- Execute the automated suite:
  ```bash
  php artisan test
  ```
  (Alias: `composer test`.)

### Production build
- Compile and version assets with Vite:
  ```bash
  npm run build
  ```
- Run migrations in production with
  ```bash
  php artisan migrate --force
  ```

## Notable directories
- `app/Http/Controllers` - recipe CRUD, saved recipe collection, and profile management controllers
- `app/Models` - Eloquent models for recipes, ingredients, dietary tags, media, saves, and comments
- `database/migrations` - schema for recipes, tags, ingredients, saved recipes, comments, and media
- `database/seeders` - demo fixtures with recipes, media galleries, comments, and a test account
- `resources/views` - Blade templates for catalog browsing, recipe authoring, saved collection, and layouts

## Localization
Most interface copy is written in Catalan. Adjust wording in the Blade templates under `resources/views` if you need another language.
In the next version I will make more languages ​​and we will have to implement it differently.

## License
This project ships with the standard Laravel MIT license.
