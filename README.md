# Portfolio Website

A modern, responsive portfolio website built with PHP MVC architecture, featuring a clean design with light/dark mode toggle and a complete admin dashboard for managing contact messages.

## 🎨 Features

### Frontend
- **Responsive Design**: Mobile-first approach with Bootstrap 5
- **Light/Dark Mode**: Seamless theme switching with localStorage persistence
- **Modern UI**: Clean design with purple accent color (#7B3FE4)
- **Interactive Elements**: Smooth scrolling, animations, and hover effects
- **Professional Typography**: Inter and Playfair Display fonts
- **Contact Form**: Ajax-powered contact form with validation

### Backend
- **PHP MVC Architecture**: Clean, organized code structure
- **Database Integration**: MySQL with PDO for secure operations
- **Admin Dashboard**: Complete management interface
- **Message Management**: View, reply, and organize contact messages
- **Authentication System**: Secure admin login with session management
- **RESTful Routes**: Clean URL structure

### Pages
- **Home**: Hero section with call-to-action
- **About**: Professional background and skills
- **Services**: Service offerings with detailed descriptions
- **Portfolio**: Project showcase with filtering capabilities
- **Contact**: Contact form with validation
- **Admin Dashboard**: Message management and statistics

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3
- **Icons**: Bootstrap Icons
- **Fonts**: Google Fonts (Inter, Playfair Display)
- **Animation**: AOS (Animate On Scroll)

## 📁 Project Structure

```
portfolio-web/
├── app/
│   ├── controllers/
│   │   ├── AdminController.php
│   │   ├── ContactController.php
│   │   ├── HomeController.php
│   │   └── PortfolioController.php
│   ├── core/
│   │   ├── BaseController.php
│   │   ├── BaseModel.php
│   │   └── Router.php
│   ├── models/
│   │   ├── Message.php
│   │   ├── Project.php
│   │   └── User.php
│   └── views/
│       ├── admin/
│       ├── contact/
│       ├── errors/
│       ├── home/
│       ├── layouts/
│       └── portfolio/
├── config/
│   └── config.php
├── database/
│   └── schema.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── images/
│   │   └── js/
│   └── index.php
└── README.md
```

## ⚡ Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server
- Composer (optional, for dependencies)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/portfolio-web.git
   cd portfolio-web
   ```

2. **Set up the database**
   ```bash
   # Create a new MySQL database
   mysql -u root -p
   CREATE DATABASE portfolio_db;
   exit
   
   # Import the schema
   mysql -u root -p portfolio_db < database/schema.sql
   ```

3. **Configure the database connection**
   ```php
   // Edit config/config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'portfolio_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set up web server**
   
   **Option A: PHP Built-in Server (Development)**
   ```bash
   cd public
   php -S localhost:8000
   ```
   
   **Option B: Apache/Nginx (Production)**
   - Point document root to `public/` directory
   - Ensure `.htaccess` is working (Apache) or configure URL rewriting (Nginx)

5. **Access the application**
   - Website: `http://localhost:8000`
   - Admin Login: `http://localhost:8000/admin/login`
   - Default admin credentials:
     - Username: `admin@portfolio.com`
     - Password: `admin123`

## 🔧 Configuration

### Environment Settings
Edit `config/config.php` to customize:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Site Configuration
define('SITE_NAME', 'Your Portfolio');
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_EMAIL', 'admin@yourportfolio.com');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'csrf_token');
```

### Customization

1. **Update Personal Information**
   - Edit content in `app/views/home/` files
   - Update contact information in `app/views/contact/index.php`
   - Modify about section in `app/views/home/about.php`

2. **Add Your Projects**
   - Insert project data into `projects` table
   - Add project images to `public/assets/images/projects/`
   - Update project showcase in portfolio section

3. **Customize Styling**
   - Main styles: `public/assets/css/style.css`
   - Admin styles: `public/assets/css/admin.css`
   - Modify CSS variables for color scheme

4. **Update Images**
   - Profile image: `public/assets/images/profile.jpg`
   - Project images: `public/assets/images/projects/`
   - Service icons: `public/assets/images/services/`

## 📊 Database Schema

### Tables

1. **admin_users**
   - `id` (Primary Key)
   - `username`
   - `email`
   - `password_hash`
   - `created_at`
   - `last_login`

2. **contact_messages**
   - `id` (Primary Key)
   - `name`
   - `email`
   - `subject`
   - `message`
   - `is_read`
   - `created_at`
   - `read_at`

3. **projects**
   - `id` (Primary Key)
   - `title`
   - `description`
   - `image_url`
   - `project_url`
   - `technologies`
   - `category`
   - `is_featured`
   - `created_at`

## 🎯 Admin Dashboard Features

### Message Management
- View all contact messages
- Filter by read/unread status
- Search messages by content
- Mark messages as read/unread
- Reply to messages directly
- Delete messages
- Export messages to PDF

### Dashboard Analytics
- Total messages count
- Unread messages count
- Recent message activity
- Quick action shortcuts

### Authentication
- Secure login system
- Session management
- Password hashing
- CSRF protection

## 🚀 Deployment

### Production Checklist

1. **Security**
   - [ ] Change default admin password
   - [ ] Update database credentials
   - [ ] Enable HTTPS
   - [ ] Set secure session settings
   - [ ] Remove development files

2. **Performance**
   - [ ] Enable PHP OPcache
   - [ ] Configure web server caching
   - [ ] Optimize images
   - [ ] Minify CSS/JS files
   - [ ] Enable GZIP compression

3. **Monitoring**
   - [ ] Set up error logging
   - [ ] Configure backup system
   - [ ] Monitor server resources
   - [ ] Set up uptime monitoring

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName yourportfolio.com
    DocumentRoot /path/to/portfolio-web/public
    
    <Directory /path/to/portfolio-web/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/portfolio_error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio_access.log combined
</VirtualHost>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourportfolio.com;
    root /path/to/portfolio-web/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔒 Security Features

- **SQL Injection Protection**: PDO prepared statements
- **CSRF Protection**: Token-based form protection
- **Password Security**: Bcrypt hashing
- **Session Security**: Secure session configuration
- **Input Validation**: Server-side validation for all inputs
- **XSS Protection**: HTML escaping for output

## 📱 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/yourusername/portfolio-web/issues) page
2. Create a new issue with detailed information
3. Contact the maintainer at your-email@example.com

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) for the responsive framework
- [Bootstrap Icons](https://icons.getbootstrap.com/) for the icon set
- [Google Fonts](https://fonts.google.com/) for typography
- [AOS](https://michalsnik.github.io/aos/) for scroll animations

---

**Built with ❤️ for showcasing amazing work**