## About This Project

This project is a Laravel-based web application that integrates Google Maps API for route planning and delivery management. It provides features for customers to place orders, for delivery personnel to manage their assigned deliveries, and for administrators to oversee the entire process.


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
