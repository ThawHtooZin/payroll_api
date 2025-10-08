# Payroll API

A RESTful API for managing payroll, employees, and attendance tracking.

---

## 📋 Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Server](#running-the-server)
- [API Documentation](#api-documentation)
  - [Base URL](#base-url)
  - [Authentication](#authentication)
  - [Endpoints](#endpoints)
    - [POST /api/auth/login](#post-apiauthlogin)

---

## 🚀 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL/PostgreSQL/SQLite

### Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd payroll_api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

---

## ⚙️ Configuration

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payroll
DB_USERNAME=root
DB_PASSWORD=
```

For SQLite (simpler for development):
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

---

## 🗄️ Database Setup

1. **Run migrations**
   ```bash
   php artisan migrate
   ```

2. **Seed the database with admin account**
   ```bash
   php artisan db:seed --class=AdminSeeder
   ```

   Or seed everything:
   ```bash
   php artisan db:seed
   ```

   This will create an admin account with the following credentials:
   - **Email:** `admin@payroll.com`
   - **Password:** `admin123`
   - **Role:** `admin`

---

## 🏃 Running the Server

Start the development server:

```bash
php artisan serve
```

The API will be available at: `http://localhost:8000`

---

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_access_token}
```

---

## 🔐 Endpoints

### Authentication Endpoints

All authentication endpoints are prefixed with `/api/auth/`

---

### POST /api/auth/login

Authenticate a user and return an access token.

### Request

**URL:** `POST /api/auth/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "email": "admin@payroll.com",
    "password": "admin123"
}
```

**Required Fields:**
- `email` (string): User's email address
- `password` (string): User's password

### Response

**Success Response (200 OK):**
```json
{
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@payroll.com",
        "phone": "1234567890",
        "role": "admin",
        "email_verified_at": "2025-01-08T08:30:00.000000Z",
        "created_at": "2025-01-08T08:30:00.000000Z",
        "updated_at": "2025-01-08T08:30:00.000000Z"
    },
    "token": "1|abcdef1234567890abcdef1234567890abcdef12"
}
```

**Error Responses:**

**401 Unauthorized - Invalid Credentials:**
```json
{
    "message": "Invalid credentials"
}
```

**422 Unprocessable Entity - Validation Error:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password field is required."
        ]
    }
}
```

---

## 📝 Notes

- All timestamps are in ISO 8601 format
- All responses are in JSON format
- Validation errors return a 422 status code
- Authentication errors return a 401 status code
