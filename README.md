# Lecturer Management System

A web application for managing lecturer records in an academic setting. Staff can register an account, log in, and view the lecturer list. Administrators can add, edit, and delete lecturers.

The project is built with **Laravel 10**, **MySQL/MariaDB**, and Blade views.

## What the system does

The system keeps a central list of lecturers (name, email, and department) and controls who can change that list.

1. A visitor opens the site and is sent to **Login**.
2. New staff **Register** with a name, email, password, and role (`user` or `admin`).
3. After registration, that person’s name and email are also added to the **lecturer list** (department starts as `Unassigned`).
4. After login, the home page shows the signed-in name and role.
5. **Lecturers** shows every lecturer currently stored in the database.
6. Only an **admin** can add a lecturer, edit details, or delete a record.
7. **Logout** ends the session and returns to the login page.

Authentication uses a session (`user` and `user_id`), not Laravel’s default `Auth` guard.

## Roles

| Role | Access |
| --- | --- |
| **User** | Home, lecturer list (view only), logout |
| **Admin** | Everything a user can do, plus Add / Edit / Delete lecturers |

If a non-admin tries to open an admin page, they are redirected back to the lecturer list.

## Main pages

| Page | Route | Description |
| --- | --- | --- |
| Login | `/login` | Sign in with email and password |
| Register | `/register` | Create an account and add the person to the lecturer list |
| Home | `/home` | Welcome screen after login |
| Lecturers | `/lecturers` | Full lecturer list |
| Add lecturer | `/lecturer/add` | Admin form to create a lecturer |
| Edit lecturer | `/lecturer/edit/{id}` | Admin form to update a lecturer |
| Documentation | `/docs` | Generated class documentation (optional) |

`/` redirects to Home if already logged in, otherwise to Login.

## Data stored

- **users** — login accounts: name, email, hashed password, role
- **lecturers** — lecturer records: name, email, department

These are separate tables. Registering creates a user and, if that email is not already a lecturer, a matching lecturer row.

## Optional documentation

`/docs` shows technical notes generated from controllers. To regenerate them (needs an OpenAI API key in `.env`):

```bash
php artisan generate:docs
```

## Requirements

- PHP 8.1+
- Composer
- MySQL or MariaDB (XAMPP is fine)

## Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
```

In `.env`, set the database to match your MySQL/XAMPP setup:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lectsystem
DB_USERNAME=root
DB_PASSWORD=
```

If XAMPP MySQL is on another port (for example `3307` because another MySQL is using `3306`), change `DB_PORT` to that port.

Create the database, then either import `lectsystem.sql` or run:

```bash
php artisan migrate
```

Start the app:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Built with Laravel

This application is based on the [Laravel](https://laravel.com) framework. Laravel documentation is available at [https://laravel.com/docs](https://laravel.com/docs).

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
