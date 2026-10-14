# BloodBank+

BloodBank-final is a blood donation and request management application.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `blood_requests`, `blood_stock`, `donations`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php), [`admin/admin_manage_donations.php`](admin/admin_manage_donations.php), [`admin/admin_navbar.php`](admin/admin_navbar.php), [`admin/approve_user.php`](admin/approve_user.php) |
| Donor | [`donor/donations.php`](donor/donations.php), [`donor/donor_dashboard.php`](donor/donor_dashboard.php), [`donor/update_profile.php`](donor/update_profile.php) |
| Hospital | [`hospital/hospital_dashboard.php`](hospital/hospital_dashboard.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php) |
| Recipient | [`recipient/recipient_dashboard.php`](recipient/recipient_dashboard.php), [`recipient/update_profile.php`](recipient/update_profile.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/BloodBank-final.git
cd BloodBank-final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/bloodbank_db.sql`](Database/bloodbank_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`donor/config.php`](donor/config.php), [`hospital/config.php`](hospital/config.php), [`recipient/config.php`](recipient/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
