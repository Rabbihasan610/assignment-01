<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# Laravel Project Setup Guide

## Prerequisites

Before starting, make sure the following tools are installed:

- PHP
- Composer
- MySQL or MariaDB
- Git
- Node.js and npm (if the project uses Vite)

## Step 1: Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
cd YOUR_REPOSITORY
```

Replace the repository URL with your actual GitHub repository URL.

## Step 2: Install Dependencies

```bash
composer update
```

> **Note:** For a fresh setup with an existing `composer.lock` file, `composer install` is generally recommended to install the locked dependency versions.

## Step 3: Create the Environment File

**Windows:**

```cmd
copy .env.example .env
```

**Linux / macOS:**

```bash
cp .env.example .env
```

## Step 4: Generate the Application Key

```bash
php artisan key:generate
```

## Step 5: Create and Configure the Database

Create a database in MySQL or phpMyAdmin.

Example:

```sql
CREATE DATABASE laravel_project;
```

Update the following values in your `.env` file:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_project
DB_USERNAME=root
DB_PASSWORD=
```

Use your actual database credentials.

## Step 6: Configure Google Maps API

1. Open the [Google Cloud Console](https://console.cloud.google.com/).
2. Select or create a Google Cloud project.
3. Enable billing if required.
4. Open **APIs & Services → Library**.
5. Enable the Google Maps APIs required by this project, such as Maps JavaScript API, Places API, or Geocoding API.
6. Open **APIs & Services → Credentials** and create an API key.
7. Configure the API key restrictions according to your application.

Add the API key to `.env`:

```dotenv
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

Make sure the variable name matches the one used in your application code.

For browser-based maps, configure the allowed website referrers and restrict the key to the required APIs. Keep server-side credentials private.

## Step 7: Run Migrations and Seeders

```bash
php artisan migrate:fresh --seed
```

**Warning:** This command deletes existing tables and their data before recreating them. Use it only with a development database.

## Step 8: Install Frontend Dependencies

If the project includes `package.json`, run:

```bash
npm install
npm run build
```

For frontend development:

```bash
npm run dev
```

## Step 9: Clear Configuration Cache

```bash
php artisan optimize:clear
```

## Step 10: Run the Project

```bash
php artisan serve
```

Open the URL displayed in your terminal, usually:

http://127.0.0.1:8000

## Troubleshooting

### Database Connection Error
- Verify the database name and credentials.
- Make sure MySQL is running.
- Ensure the database user has the required permissions.

### Google Maps Not Working
- Verify the API key in `.env`.
- Enable the required Google Maps APIs.
- Check billing, API restrictions, and website referrer settings.
- Check your browser console for errors.

### Composer Errors
Check your installed PHP and Composer versions:

```bash
php -v
composer --version
```

Ensure that your PHP version and extensions satisfy the project's requirements.

## Security Notes

- Never commit your `.env` file or real API keys to GitHub.
- Keep `.env.example` free of private credentials.
- Do not run `migrate:fresh` on a production database.
- Back up important data before destructive database operations.

## Setup Complete

Your Laravel project is now ready for local development.
