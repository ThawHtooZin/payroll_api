# Payroll API Documentation

## Authentication Endpoints

All authentication endpoints are prefixed with `/api/auth/`

---

## POST /api/auth/login

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

## Base URL

```
http://localhost:8000/api
```

## Default Admin Account

For testing purposes, use the following credentials:

- **Email:** admin@payroll.com
- **Password:** admin123
- **Role:** admin
