#!/bin/bash

# Portfolio Website Setup Script
# This script helps set up the portfolio website project

echo "🎨 Portfolio Website Setup"
echo "=========================="
echo ""

# Check if we're in the correct directory
if [ ! -f "public/index.php" ]; then
    echo "❌ Error: Please run this script from the portfolio-web root directory"
    exit 1
fi

echo "📋 Setup Checklist:"
echo ""

# Step 1: Database Setup
echo "1. 📊 Database Setup"
echo "   Please ensure you have:"
echo "   - MySQL server running"
echo "   - Created a database (e.g., 'portfolio_db')"
echo "   - Database user with appropriate permissions"
echo ""
read -p "   Have you created the database? (y/n): " db_created
if [ "$db_created" != "y" ]; then
    echo "   Please create your database first and run this script again."
    exit 1
fi

# Step 2: Configuration
echo ""
echo "2. ⚙️  Configuration"
echo "   Current config file: config/config.php"
echo "   Please verify your database connection settings."
echo ""
read -p "   Have you updated the database configuration? (y/n): " config_updated
if [ "$config_updated" != "y" ]; then
    echo "   Please update config/config.php with your database details:"
    echo "   - DB_HOST (usually 'localhost')"
    echo "   - DB_NAME (your database name)"
    echo "   - DB_USER (your database username)"
    echo "   - DB_PASS (your database password)"
    exit 1
fi

# Step 3: Import Database Schema
echo ""
echo "3. 🗄️  Database Schema Import"
echo "   Importing database schema and sample data..."
echo ""

read -p "   Enter your MySQL username: " mysql_user
read -s -p "   Enter your MySQL password: " mysql_pass
echo ""
read -p "   Enter your database name: " db_name

# Try to import the schema
if mysql -u "$mysql_user" -p"$mysql_pass" "$db_name" < database/schema.sql 2>/dev/null; then
    echo "   ✅ Database schema imported successfully!"
else
    echo "   ❌ Failed to import database schema."
    echo "   Please manually import database/schema.sql"
    echo "   Command: mysql -u $mysql_user -p $db_name < database/schema.sql"
fi

# Step 4: File Permissions
echo ""
echo "4. 🔐 File Permissions"
echo "   Setting appropriate file permissions..."
echo ""

# Make sure public directory is readable
chmod -R 755 public/ 2>/dev/null || echo "   Note: Could not set permissions automatically"

# Step 5: Web Server Setup
echo ""
echo "5. 🌐 Web Server Setup"
echo "   Choose your web server option:"
echo ""
echo "   Option A: PHP Built-in Server (Development)"
echo "   Command: cd public && php -S localhost:8000"
echo ""
echo "   Option B: Apache/Nginx (Production)"
echo "   Point document root to: $(pwd)/public"
echo ""

read -p "   Start PHP built-in server now? (y/n): " start_server
if [ "$start_server" = "y" ]; then
    cd public
    echo "   🚀 Starting server at http://localhost:8000"
    echo "   Press Ctrl+C to stop the server"
    echo ""
    php -S localhost:8000
else
    echo "   Server setup complete! Configure your web server to serve from:"
    echo "   Document Root: $(pwd)/public"
fi

echo ""
echo "🎉 Setup Complete!"
echo ""
echo "📱 Access your portfolio:"
echo "   Website: http://localhost:8000 (or your configured domain)"
echo "   Admin: http://localhost:8000/admin/login"
echo ""
echo "🔑 Default Admin Credentials:"
echo "   Email: admin@portfolio.com"
echo "   Password: admin123"
echo ""
echo "⚠️  Important Security Notes:"
echo "   - Change the default admin password after first login"
echo "   - Update the admin email in the database"
echo "   - Set secure session settings for production"
echo ""
echo "📚 Documentation:"
echo "   See README.md for detailed setup and customization instructions"
echo ""
echo "Happy designing! 🎨"