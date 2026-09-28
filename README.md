# MediTrack

Web-based pharmacy inventory application using Laravel, Livewire, MySQL, and Tailwind CSS.

This repository is a phased import of an existing application. Import phase: 1/12. Commit dates record publication work.

## Local setup

Requirements: PHP matching composer.json (currently ^8.3), Composer, Node.js/npm compatible with the locked dependencies, and MySQL.

1. Run `composer install` and `npm ci`.
2. Copy `.env.example` to `.env`, configure a NEW local development database, and run `php artisan key:generate`.
3. From phase 2 onward, run `php artisan migrate`.
4. From phase 4 onward, demo seeders are available; use `php artisan db:seed` only on a disposable development database. Complete demo data arrives in phase 12.
5. Run `npm run build`, then `php artisan serve`.
6. Run `php artisan test` for tests included at the current phase.

Earlier phases contain only the features imported so far. Password reset delivery requires local mail configuration. Dashboard charts use an external CDN. Demo accounts are for local development only; inspect UserSeeder before using them.

## Features in the completed import

Pharmacy and user administration; medicine and batch records; stock-in, FEFO stock-out, adjustments, damaged and expired stock; reorder points using average daily demand, lead time, and safety stock; alerts; dashboards; PDF/CSV reports; audit logs and settings.

## Verification

The import package was checked for file coverage and phased routing dependencies. PHP tests and browser flows have not been run by the package creator. Verify functionality locally before deployment.
