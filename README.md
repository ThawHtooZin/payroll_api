# Payroll API

A RESTful API for managing payroll, employees, and attendance tracking.

---

## 📋 Table of Contents

-   [Installation](#installation)
-   [Configuration](#configuration)
-   [Database Setup](#database-setup)
-   [Running the Server](#running-the-server)
-   [API Documentation](#api-documentation)
    -   [Base URL](#base-url)
    -   [Authentication](#authentication)
    -   [Endpoints](#endpoints)
        -   [Authentication Endpoints](#authentication-endpoints)
            -   [POST /api/auth/login](#post-apiauthlogin)
        -   [Admin Endpoints](#admin-endpoints)
            -   [GET /api/admin/users](#get-apiadminusers)
            -   [POST /api/admin/users](#post-apiadminusers)
            -   [GET /api/admin/users/{user}](#get-apiadminusersuser)
            -   [PUT /api/admin/users/{user}](#put-apiadminusersuser)
            -   [DELETE /api/admin/users/{user}](#delete-apiadminusersuser)
            -   [GET /api/admin/employees](#get-apiadminemployees)
            -   [GET /api/admin/employees/{employee}](#get-apiadminemployeesemployee)
            -   [GET /api/admin/calendar/month](#get-apiadmincalendarmonth)
            -   [GET /api/admin/calendar/year](#get-apiadmincalendaryear)
            -   [PUT /api/admin/calendar/day/status](#put-apiadmincalendardaystatus)
            -   [PUT /api/admin/calendar/days/bulk-update](#put-apiadmincalendardaysbulk-update)

---

## 🚀 Installation

### Prerequisites

-   PHP >= 8.2
-   Composer
-   MySQL/PostgreSQL/SQLite

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

-   `email` (string): User's email address
-   `password` (string): User's password

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
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

---

## 🔐 Admin Endpoints

All admin endpoints require authentication and admin role. All admin endpoints are prefixed with `/api/admin/`

---

### GET /api/admin/users

Get a paginated list of all users with optional filtering and search (Admin only).

#### Request

**URL:** `GET /api/admin/users`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**

-   `role` (optional, string): Filter by user role (admin, employee)
-   `search` (optional, string): Search by name, email, or phone
-   `per_page` (optional, integer): Number of results per page (default: 15)
-   `page` (optional, integer): Page number (default: 1)

**Example:**

```
GET /api/admin/users?role=employee&search=john&per_page=10&page=1
```

#### Response

**Success Response (200 OK):**

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "1234567890",
            "role": "employee",
            "email_verified_at": null,
            "created_at": "2025-01-08T08:30:00.000000Z",
            "updated_at": "2025-01-08T08:30:00.000000Z"
        },
        {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "phone": "0987654321",
            "role": "admin",
            "email_verified_at": null,
            "created_at": "2025-01-08T08:30:00.000000Z",
            "updated_at": "2025-01-08T08:30:00.000000Z"
        }
    ],
    "first_page_url": "http://localhost:8000/api/admin/users?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/admin/users?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost:8000/api/admin/users",
    "per_page": 15,
    "prev_page_url": null,
    "to": 2,
    "total": 2
}
```

---

### POST /api/admin/users

Create a new user (Admin only). If the role is "employee", this will automatically create an associated employee record.

#### Request

**URL:** `POST /api/admin/users`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (Admin User):**

```json
{
    "name": "Admin User",
    "email": "admin@example.com",
    "phone": "1234567890",
    "password": "password123",
    "role": "admin"
}
```

**Body (Employee User):**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "password": "password123",
    "role": "employee",
    "position": "Software Developer",
    "level": "Senior",
    "base_salary": 75000,
    "is_active": true
}
```

**Required Fields:**

-   `name` (string, max: 255): User's full name
-   `email` (string, max: 255): User's email address (must be unique)
-   `phone` (string, max: 255): User's phone number
-   `password` (string, min: 8): User's password
-   `role` (string): User role - must be either "admin" or "employee"

**Required Fields (if role is "employee"):**

-   `position` (string, max: 255): Employee's job position
-   `level` (string, max: 255): Employee's level (e.g., Junior, Senior, Lead)
-   `base_salary` (integer, min: 0): Employee's base salary

**Optional Fields (for employee role):**

-   `is_active` (boolean): Employee active status (defaults to true if not provided)

#### Response

**Success Response (201 Created):**

```json
{
    "message": "User created successfully",
    "user": {
        "id": 3,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "role": "employee",
        "employee_id": 1,
        "email_verified_at": null,
        "created_at": "2025-01-08T10:30:00.000000Z",
        "updated_at": "2025-01-08T10:30:00.000000Z",
        "employee": {
            "id": 1,
            "position": "Software Developer",
            "level": "Senior",
            "base_salary": 75000,
            "is_active": true,
            "created_at": "2025-01-08T10:30:00.000000Z",
            "updated_at": "2025-01-08T10:30:00.000000Z"
        }
    }
}
```

**Error Responses:**

**422 Unprocessable Entity - Validation Error:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password field must be at least 8 characters."],
        "role": ["The selected role is invalid."],
        "position": ["The position field is required when role is employee."]
    }
}
```

---

### GET /api/admin/users/{user}

Get a specific user by ID (Admin only).

#### Request

**URL:** `GET /api/admin/users/{user}`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**URL Parameters:**

-   `user` (required, integer): User ID

**Example:**

```
GET /api/admin/users/3
```

#### Response

**Success Response (200 OK):**

```json
{
    "id": 3,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "role": "employee",
    "email_verified_at": null,
    "created_at": "2025-01-08T10:30:00.000000Z",
    "updated_at": "2025-01-08T10:30:00.000000Z"
}
```

**Error Responses:**

**404 Not Found - User Not Found:**

```json
{
    "message": "No query results for model [App\\Models\\User] {user_id}"
}
```

---

### PUT /api/admin/users/{user}

Update a specific user (Admin only). If the user has an associated employee record, you can also update employee fields.

#### Request

**URL:** `PUT /api/admin/users/{user}`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**URL Parameters:**

-   `user` (required, integer): User ID

**Body (Update User Only):**

```json
{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "phone": "1112223333",
    "password": "newpassword123"
}
```

**Body (Update User and Employee Data):**

```json
{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "phone": "1112223333",
    "position": "Lead Developer",
    "level": "Lead",
    "base_salary": 95000,
    "is_active": false
}
```

**Optional Fields (all fields are optional):**

-   `name` (string, max: 255): User's full name
-   `email` (string, max: 255): User's email address (must be unique)
-   `phone` (string, max: 255): User's phone number
-   `password` (string, min: 8): User's new password
-   `role` (string): User role - must be either "admin" or "employee"

**Optional Employee Fields (if user has employee record):**

-   `position` (string, max: 255): Employee's job position
-   `level` (string, max: 255): Employee's level
-   `base_salary` (integer, min: 0): Employee's base salary
-   `is_active` (boolean): Employee active status

**Example:**

```
PUT /api/admin/users/3
```

#### Response

**Success Response (200 OK):**

```json
{
    "message": "User updated successfully",
    "user": {
        "id": 3,
        "name": "John Updated",
        "email": "john.updated@example.com",
        "phone": "1112223333",
        "role": "employee",
        "employee_id": 1,
        "email_verified_at": null,
        "created_at": "2025-01-08T10:30:00.000000Z",
        "updated_at": "2025-01-08T11:15:00.000000Z",
        "employee": {
            "id": 1,
            "position": "Lead Developer",
            "level": "Lead",
            "base_salary": 95000,
            "is_active": false,
            "created_at": "2025-01-08T10:30:00.000000Z",
            "updated_at": "2025-01-08T11:15:00.000000Z"
        }
    }
}
```

**Error Responses:**

**422 Unprocessable Entity - Validation Error:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

**404 Not Found - User Not Found:**

```json
{
    "message": "No query results for model [App\\Models\\User] {user_id}"
}
```

---

### DELETE /api/admin/users/{user}

Delete a specific user (Admin only).

#### Request

**URL:** `DELETE /api/admin/users/{user}`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**URL Parameters:**

-   `user` (required, integer): User ID

**Example:**

```
DELETE /api/admin/users/3
```

#### Response

**Success Response (200 OK):**

```json
{
    "message": "User deleted successfully"
}
```

**Error Responses:**

**403 Forbidden - Cannot Delete Own Account:**

```json
{
    "message": "Cannot delete your own account"
}
```

**404 Not Found - User Not Found:**

```json
{
    "message": "No query results for model [App\\Models\\User] {user_id}"
}
```

---

### GET /api/admin/employees

Get a paginated list of all employees with optional filtering and search (Admin only). This is a read-only endpoint - to create or update employees, use the User endpoints.

#### Request

**URL:** `GET /api/admin/employees`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**

-   `is_active` (optional, boolean): Filter by employee active status (true/false)
-   `position` (optional, string): Filter by employee position
-   `level` (optional, string): Filter by employee level
-   `search` (optional, string): Search by name, email, or phone (from related user)
-   `per_page` (optional, integer): Number of results per page (default: 15)
-   `page` (optional, integer): Page number (default: 1)

**Example:**

```
GET /api/admin/employees?is_active=true&position=Developer&search=john&per_page=10&page=1
```

#### Response

**Success Response (200 OK):**

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "position": "Software Developer",
            "level": "Senior",
            "base_salary": 75000,
            "is_active": true,
            "created_at": "2025-01-08T08:30:00.000000Z",
            "updated_at": "2025-01-08T08:30:00.000000Z",
            "user": {
                "id": 2,
                "name": "John Doe",
                "email": "john.doe@example.com",
                "phone": "1234567890",
                "role": "employee",
                "employee_id": 1,
                "created_at": "2025-01-08T08:30:00.000000Z",
                "updated_at": "2025-01-08T08:30:00.000000Z"
            }
        },
        {
            "id": 2,
            "position": "Project Manager",
            "level": "Lead",
            "base_salary": 85000,
            "is_active": true,
            "created_at": "2025-01-08T08:30:00.000000Z",
            "updated_at": "2025-01-08T08:30:00.000000Z",
            "user": {
                "id": 3,
                "name": "Jane Smith",
                "email": "jane.smith@example.com",
                "phone": "0987654321",
                "role": "employee",
                "employee_id": 2,
                "created_at": "2025-01-08T08:30:00.000000Z",
                "updated_at": "2025-01-08T08:30:00.000000Z"
            }
        }
    ],
    "first_page_url": "http://localhost:8000/api/admin/employees?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/admin/employees?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost:8000/api/admin/employees",
    "per_page": 15,
    "prev_page_url": null,
    "to": 2,
    "total": 2
}
```

**Note:** Employees are created and updated through the User endpoints. When creating a user with role "employee", an employee record is automatically created.

---

### GET /api/admin/employees/{employee}

Get a specific employee by ID (Admin only). This is a read-only endpoint.

#### Request

**URL:** `GET /api/admin/employees/{employee}`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**URL Parameters:**

-   `employee` (required, integer): Employee ID

**Example:**

```
GET /api/admin/employees/3
```

#### Response

**Success Response (200 OK):**

```json
{
    "id": 3,
    "position": "Software Developer",
    "level": "Senior",
    "base_salary": 75000,
    "is_active": true,
    "created_at": "2025-01-08T10:30:00.000000Z",
    "updated_at": "2025-01-08T10:30:00.000000Z",
    "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "phone": "1234567890",
        "role": "employee",
        "employee_id": 3,
        "created_at": "2025-01-08T10:30:00.000000Z",
        "updated_at": "2025-01-08T10:30:00.000000Z"
    }
}
```

**Error Responses:**

**404 Not Found - Employee Not Found:**

```json
{
    "message": "No query results for model [App\\Models\\Employee] {employee_id}"
}
```

---

### GET /api/admin/calendar/month

Get all calendar data for a specific month (Admin only).

#### Request

**URL:** `GET /api/admin/calendar/month`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**

-   `month` (required, integer): Month number (1-12)
-   `year` (required, integer): Year (2020-2030)

**Example:**

```
GET /api/admin/calendar/month?month=1&year=2025
```

#### Response

**Success Response (200 OK):**

```json
{
    "month": 1,
    "year": 2025,
    "month_name": "January",
    "total_days": 31,
    "work_days": 23,
    "weekend_days": 8,
    "calendar_data": [
        {
            "id": 1,
            "date": "2025-01-01",
            "day_name": "Wednesday",
            "is_work_day": true,
            "remark": "Normal workday",
            "created_at": "2025-01-08T08:30:00.000000Z",
            "updated_at": "2025-01-08T08:30:00.000000Z"
        }
    ]
}
```

---

### GET /api/admin/calendar/year

Get all calendar data for an entire year (Admin only).

#### Request

**URL:** `GET /api/admin/calendar/year`

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**

-   `year` (required, integer): Year (2020-2030)

**Example:**

```
GET /api/admin/calendar/year?year=2025
```

#### Response

**Success Response (200 OK):**

```json
{
    "year": 2025,
    "total_days": 365,
    "total_work_days": 261,
    "total_weekend_days": 104,
    "monthly_data": {
        "January": {
            "month": 1,
            "month_name": "January",
            "total_days": 31,
            "work_days": 23,
            "weekend_days": 8,
            "calendar_data": [...]
        },
        "February": {
            "month": 2,
            "month_name": "February",
            "total_days": 28,
            "work_days": 20,
            "weekend_days": 8,
            "calendar_data": [...]
        }
    }
}
```

**Error Responses:**

**403 Forbidden - Insufficient Permissions:**

```json
{
    "message": "Insufficient permissions. Required role: admin"
}
```

**422 Unprocessable Entity - Validation Error:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "month": ["The month field is required."],
        "year": ["The year must be between 2020 and 2030."]
    }
}
```

---

### PUT /api/admin/calendar/day/status

Update a specific calendar day status (Admin only).

#### Request

**URL:** `PUT /api/admin/calendar/day/status`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**

```json
{
    "date": "2025-12-25",
    "is_work_day": false,
    "remark": "Christmas Day - Company Holiday"
}
```

**Required Fields:**

-   `date` (string): Date in YYYY-MM-DD format
-   `is_work_day` (boolean): true for work day, false for non-work day

**Optional Fields:**

-   `remark` (string): Custom remark for the day (max 255 characters)

#### Response

**Success Response (200 OK):**

```json
{
    "message": "Calendar day status updated successfully",
    "calendar_day": {
        "id": 365,
        "date": "2025-12-25",
        "day_name": "Thursday",
        "is_work_day": false,
        "remark": "Christmas Day - Company Holiday",
        "created_at": "2025-01-08T08:30:00.000000Z",
        "updated_at": "2025-01-08T10:15:00.000000Z"
    }
}
```

**Error Responses:**

**404 Not Found - Calendar Entry Not Found:**

```json
{
    "message": "Calendar entry not found for the specified date"
}
```

**422 Unprocessable Entity - Validation Error:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "date": ["The date field is required."],
        "is_work_day": ["The is work day field is required."]
    }
}
```

---

### PUT /api/admin/calendar/days/bulk-update

Bulk update multiple calendar days status (Admin only).

#### Request

**URL:** `PUT /api/admin/calendar/days/bulk-update`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**

```json
{
    "dates": ["2025-12-25", "2025-12-26", "2025-01-01"],
    "is_work_day": false,
    "remark": "Holiday Season - Company Closed"
}
```

**Required Fields:**

-   `dates` (array): Array of dates in YYYY-MM-DD format
-   `is_work_day` (boolean): true for work day, false for non-work day

**Optional Fields:**

-   `remark` (string): Custom remark for all days (max 255 characters)

#### Response

**Success Response (200 OK):**

```json
{
    "message": "Bulk update completed. Updated 3 calendar days.",
    "updated_count": 3,
    "not_found_dates": []
}
```

**Partial Success Response (200 OK):**

```json
{
    "message": "Bulk update completed. Updated 2 calendar days.",
    "updated_count": 2,
    "not_found_dates": ["2025-12-31"]
}
```

**Error Responses:**

**422 Unprocessable Entity - Validation Error:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "dates": ["The dates field is required."],
        "is_work_day": ["The is work day field is required."]
    }
}
```

---

## 📝 Notes

-   All timestamps are in ISO 8601 format
-   All responses are in JSON format
-   Validation errors return a 422 status code
-   Authentication errors return a 401 status code
