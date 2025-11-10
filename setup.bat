@echo off
REM Portfolio Website Setup Script for Windows
REM This script helps set up the portfolio website project

echo.
echo 🎨 Portfolio Website Setup
echo ==========================
echo.

REM Check if we're in the correct directory
if not exist "public\index.php" (
    echo ❌ Error: Please run this script from the portfolio-web root directory
    pause
    exit /b 1
)

echo 📋 Setup Checklist:
echo.

REM Step 1: Database Setup
echo 1. 📊 Database Setup
echo    Please ensure you have:
echo    - MySQL server running
echo    - Created a database (e.g., 'portfolio_db')
echo    - Database user with appropriate permissions
echo.
set /p db_created="   Have you created the database? (y/n): "
if /i not "%db_created%"=="y" (
    echo    Please create your database first and run this script again.
    pause
    exit /b 1
)

REM Step 2: Configuration
echo.
echo 2. ⚙️  Configuration
echo    Current config file: config\config.php
echo    Please verify your database connection settings.
echo.
set /p config_updated="   Have you updated the database configuration? (y/n): "
if /i not "%config_updated%"=="y" (
    echo    Please update config\config.php with your database details:
    echo    - DB_HOST (usually 'localhost')
    echo    - DB_NAME (your database name)
    echo    - DB_USER (your database username)
    echo    - DB_PASS (your database password)
    pause
    exit /b 1
)

REM Step 3: Import Database Schema
echo.
echo 3. 🗄️  Database Schema Import
echo    Importing database schema and sample data...
echo.

set /p mysql_user="   Enter your MySQL username: "
set /p mysql_pass="   Enter your MySQL password: "
set /p db_name="   Enter your database name: "

REM Try to import the schema
mysql -u %mysql_user% -p%mysql_pass% %db_name% < database\schema.sql >nul 2>&1
if %errorlevel% equ 0 (
    echo    ✅ Database schema imported successfully!
) else (
    echo    ❌ Failed to import database schema.
    echo    Please manually import database\schema.sql
    echo    Command: mysql -u %mysql_user% -p %db_name% ^< database\schema.sql
)

REM Step 4: Web Server Setup
echo.
echo 4. 🌐 Web Server Setup
echo    Choose your web server option:
echo.
echo    Option A: PHP Built-in Server (Development)
echo    Command: cd public ^&^& php -S localhost:8000
echo.
echo    Option B: Apache/Nginx/XAMPP (Production)
echo    Point document root to: %cd%\public
echo.

set /p start_server="   Start PHP built-in server now? (y/n): "
if /i "%start_server%"=="y" (
    cd public
    echo    🚀 Starting server at http://localhost:8000
    echo    Press Ctrl+C to stop the server
    echo.
    php -S localhost:8000
) else (
    echo    Server setup complete! Configure your web server to serve from:
    echo    Document Root: %cd%\public
)

echo.
echo 🎉 Setup Complete!
echo.
echo 📱 Access your portfolio:
echo    Website: http://localhost:8000 (or your configured domain)
echo    Admin: http://localhost:8000/admin/login
echo.
echo 🔑 Default Admin Credentials:
echo    Email: admin@portfolio.com
echo    Password: admin123
echo.
echo ⚠️  Important Security Notes:
echo    - Change the default admin password after first login
echo    - Update the admin email in the database
echo    - Set secure session settings for production
echo.
echo 📚 Documentation:
echo    See README.md for detailed setup and customization instructions
echo.
echo Happy designing! 🎨
echo.
pause