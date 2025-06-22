# 🔐 Web Security Demo Application

A comprehensive web application demonstrating common web security vulnerabilities and their fixes, built with PHP, MySQL, and Docker.

## 🚀 Features

- **Authentication System**: Secure login/registration with password hashing
- **Session Management**: Session timeouts and security headers
- **CSRF Protection**: Token-based CSRF prevention
- **XSS Demonstration**: Shows both vulnerable and secure implementations
- **SQL Injection Protection**: Demonstrates prepared statements
- **Role-Based Access Control**: Admin and user roles
- **Modern UI**: Professional styling with security indicators

## 🛠 Setup Instructions

### Prerequisites

- Docker and Docker Compose
- Git

### Installation

1. **Clone the repository**

   ```bash
   git clone <your-repo-url>
   cd web-security
   ```

2. **Set up environment variables**
   ```bash
   cp .env.example .env
   ```
3. **Edit the `.env` file with your actual database credentials**

   ```
   DB_HOST=your-database-host
   DB_USER=your-username
   DB_PASS=your-password
   # ... other variables
   ```

4. **Start the application**

   ```bash
   docker-compose up -d
   ```

5. **Access the application**
   - Main app: http://localhost:3000
   - PHPMyAdmin: http://localhost:8080

## 🔒 Security Features Demonstrated

- ✅ **Secure**: Prepared statements, password hashing, CSRF tokens
- ⚠️ **Educational**: XSS vulnerability demonstration (for learning)
- 🔐 **Headers**: Security headers implementation
- 🕒 **Sessions**: Timeout and regeneration

## 📁 Project Structure

```
web-security/
├── app/                    # PHP application files
│   ├── index.php          # Main comments page
│   ├── login.php          # Authentication
│   ├── register.php       # User registration
│   ├── admin.php          # Admin panel
│   ├── protected.php      # Protected area
│   └── styles.css         # Modern CSS styling
├── db/                    # Database initialization
│   └── init.sql          # Database schema
├── docker-compose.yml     # Docker configuration
├── dockerfile            # PHP container setup
└── .env.example          # Environment template
```

## ⚠️ Important Security Notes

This application is for **educational purposes only**. Some features intentionally demonstrate vulnerabilities:

- The main comments section shows XSS vulnerability
- The admin panel shows the secure implementation
- Always use the secure patterns in production

## 🧪 Testing

1. **Register a new user** at `/register.php`
2. **Test XSS** by posting `<script>alert('XSS')</script>` as a comment
3. **Access admin features** by registering with role "admin"
4. **Try SQL injection** (it won't work due to prepared statements)

## 📝 License

Educational use only. Not for production deployment.

---

**⚠️ Disclaimer**: This application contains intentional security vulnerabilities for educational purposes. Do not deploy in production environments.
