# Gihanga Coffee - PHP MVC Framework with JWT Authentication

## 📋 Overview

A complete PHP MVC framework with JWT authentication, built for the Gihanga Coffee management system.

## 🚀 Quick Start

### 1. Start XAMPP

Make sure Apache and MySQL are running in XAMPP.

### 2. Setup Database

Visit: `http://localhost/gihangacoffee/setup.php`

This will:

- Create the `gihangacoffee` database
- Create all necessary tables
- Create a default admin user

### 3. Login

Visit: `http://localhost/gihangacoffee/login`

**Default Admin Credentials:**

- Email: `admin@gihangacoffee.com`
- Password: `Admin@123`

## 📁 Project Structure

```
gihangacoffee/
├── app/
│   ├── Controllers/         # Controller classes
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── UserController.php
│   ├── Core/                # Framework core classes
│   │   ├── Application.php
│   │   ├── Autoloader.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Model.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   └── View.php
│   ├── Middleware/          # Middleware classes
│   │   ├── AdminMiddleware.php
│   │   ├── ApiAuthMiddleware.php
│   │   ├── AuthMiddleware.php
│   │   └── GuestMiddleware.php
│   ├── Models/              # Model classes
│   │   └── User.php
│   ├── Services/            # Service classes
│   │   ├── AuthService.php
│   │   └── JWTService.php
│   └── Views/               # View templates
│       ├── auth/
│       ├── dashboard/
│       ├── errors/
│       ├── layouts/
│       ├── partials/
│       └── users/
├── assets/                  # Static assets (CSS, JS, images)
├── config/
│   ├── config.php           # Application configuration
│   └── database.sql         # Database schema
├── routes/
│   └── web.php              # Route definitions
├── template/                # Original templates
├── .htaccess                # Apache rewrite rules
├── index.php                # Application entry point
├── setup.php                # Database setup script
└── README.md                # This file
```

## 🔐 Authentication

### Session-based Authentication (Web)

- Login creates a session with user data and JWT tokens
- Refresh tokens stored in cookies for "remember me" functionality
- Middleware protects routes requiring authentication

### JWT Authentication (API)

- Access token expires in 1 hour
- Refresh token expires in 7 days
- Tokens stored in database for blacklisting capability

## 📡 API Endpoints

### Authentication

| Method | Endpoint             | Description           |
| ------ | -------------------- | --------------------- |
| POST   | `/api/auth/login`    | Login and get tokens  |
| POST   | `/api/auth/register` | Register new user     |
| POST   | `/api/auth/refresh`  | Refresh access token  |
| POST   | `/api/auth/logout`   | Logout (revoke token) |
| GET    | `/api/auth/me`       | Get current user      |

### Users

| Method | Endpoint           | Description         |
| ------ | ------------------ | ------------------- |
| GET    | `/api/users`       | List all users      |
| GET    | `/api/users/stats` | Get user statistics |
| GET    | `/api/users/{id}`  | Get single user     |
| PUT    | `/api/users/{id}`  | Update user         |
| DELETE | `/api/users/{id}`  | Delete user         |

## 🔧 API Usage Examples

### Login

```bash
curl -X POST http://localhost/gihangacoffee/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@gihangacoffee.com", "password": "Admin@123"}'
```

### Response

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "uuid": "...",
      "first_name": "Admin",
      "last_name": "User",
      "email": "admin@gihangacoffee.com",
      "role": "admin",
      "status": "active"
    },
    "tokens": {
      "access_token": "eyJ...",
      "refresh_token": "eyJ...",
      "token_type": "Bearer",
      "expires_in": 3600
    }
  }
}
```

### Get Users (Protected)

```bash
curl -X GET http://localhost/gihangacoffee/api/users \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 🗃️ Database Tables

### users

- User accounts with roles (admin, manager, staff, customer)
- Status tracking (active, inactive, suspended, pending)
- Email verification support

### jwt_tokens

- Token storage for refresh tokens
- Token blacklisting for logout
- Automatic cleanup of expired tokens

### password_resets

- Password reset token management

### user_activity_logs

- Audit trail for user actions

## ⚙️ Configuration

Edit `config/config.php` to customize:

```php
// Application settings
define('APP_NAME', 'Gihanga Coffee');
define('APP_URL', 'http://localhost/gihangacoffee');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gihangacoffee');
define('DB_USER', 'root');
define('DB_PASS', '');

// JWT Configuration
define('JWT_SECRET', 'your-secret-key');
define('JWT_EXPIRY', 3600);  // 1 hour
define('JWT_REFRESH_EXPIRY', 86400 * 7);  // 7 days
```

## 🛡️ Security Features

- Password hashing with bcrypt (cost factor 12)
- JWT token signing with HMAC-SHA256
- Token blacklisting for secure logout
- CSRF protection via session tokens
- Input sanitization and validation
- SQL injection prevention via PDO prepared statements
- XSS prevention via output escaping

## 📝 License

This project is for educational purposes.

---

Built with ❤️ for Gihanga Coffee
