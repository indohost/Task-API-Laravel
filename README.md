# Task API - Laravel 10 Project

## Overview

This project is a Laravel 10 application that includes authentication using JWT (JSON Web Token) implemented with `PHPOpenSourceSaver/JWTAuth` and uses UUIDs as primary keys for the database models.

## Prerequisites

-   PHP >= 8.1
-   Composer
-   Laravel 10
-   MariaDB or any other compatible database

## Installation

### 1. Clone the repository

```bash
git https://github.com/indohost/Task-API-Laravel.git
cd task-api-laravel
```

### 2. Install dependencies

```bash
composer install
```

### 3. Environment setup

```bash
cp .env.example .env

php artisan key:generate
```

### 4. Set up the database

```bash
php artisan migrate
```

### 5. Generate JWT secret

```bash
php artisan jwt:secret
```

### Penjelasan Tambahan

-   **UUID**: UUID (Universally Unique Identifier) digunakan sebagai primary key untuk model. Ini berguna untuk memastikan keunikan global dan menghindari konflik ID di lingkungan multi-server atau distribusi.
-   **JWT**: JSON Web Token digunakan untuk autentikasi stateless. Ini memungkinkan aplikasi untuk mengidentifikasi pengguna tanpa perlu menyimpan informasi sesi di server.

## API Documentation

Dokumentasi API untuk proyek ini dibuat menggunakan Postman. Anda dapat mengakses dokumentasi API di link berikut:


```bash
https://documenter.getpostman.com/view/4839164/2sA3kbgyAo
```
