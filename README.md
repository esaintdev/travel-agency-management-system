# 🌍 M25 Travel & Tours Agency Management System

A comprehensive web-based management system for travel and tour agencies, featuring client management, document handling, visa processing, and administrative tools.

## ✨ Features

### 🎯 **Client Management**
- **Client Registration** - Complete application forms with document upload
- **Profile Management** - Edit personal information and preferences
- **Application Tracking** - Real-time status updates and timeline
- **Document Management** - Secure upload, view, and download system
- **Currency Preferences** - Multi-currency support with 40+ currencies

### 👨‍💼 **Admin Dashboard**
- **Client Overview** - Comprehensive client management interface
- **Document Review** - Approve/reject client documents
- **Search & Filter** - Advanced client search capabilities
- **Export Data** - Generate reports and export client information
- **User Management** - Admin user accounts and permissions
- **Email Templates** - Customizable email communication

### 🔐 **Security Features**
- **Secure Authentication** - Session management with timeout
- **File Security** - Protected document access with ownership verification
- **Input Validation** - SQL injection and XSS protection
- **Access Control** - Role-based permissions (Client/Admin)

### 🎨 **Modern UI/UX**
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Professional Theme** - Clean, modern interface
- **Custom Modals** - Beautiful confirmation dialogs
- **Glass Effects** - Modern glassmorphism design elements
- **Interactive Elements** - Smooth animations and transitions

## 🛠️ **Technology Stack**

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5
- **Icons**: Font Awesome 5
- **Server**: Apache/Nginx

## 📋 **Requirements**

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- GD extension for image processing
- PDO MySQL extension

## 🚀 **Installation**

### 1. **Clone Repository**
```bash
git clone https://github.com/yourusername/m25-travel-agency.git
cd m25-travel-agency
```

### 2. **Database Setup**
```sql
-- Create database
CREATE DATABASE m25_travel_agency;

-- Import base structure
mysql -u username -p m25_travel_agency < sql/database-structure.sql

-- Update client fields (if needed)
mysql -u username -p m25_travel_agency < sql/update-client-fields.sql
```

### 3. **Configuration**
```php
// Copy and configure database settings
cp config-sample.php config.php

// Edit config.php with your database credentials
$host = 'localhost';
$dbname = 'm25_travel_agency';
$username = 'your_username';
$password = 'your_password';
```

### 4. **File Permissions**
```bash
# Set proper permissions for upload directories
chmod 755 uploads/
chmod 755 client_documents/
chmod 644 *.php
```

### 5. **Web Server Configuration**
- Point document root to project directory
- Ensure mod_rewrite is enabled
- Configure virtual host (optional)

## 📁 **Project Structure**

```
m25-travel-agency/
├── 📄 index.php                 # Main landing page
├── 🔐 admin-login.php           # Admin authentication
├── 🔐 client-login.php          # Client authentication
├── 📊 admin-dashboard.php       # Admin control panel
├── 👤 client-dashboard.php      # Client portal
├── 📋 client-status.php         # Application status page
├── 📁 client-documents.php      # Document management
├── ⚙️ config.php               # Database configuration
├── 🛡️ includes/               # Authentication & utilities
├── 🎨 css/                     # Stylesheets
├── 📱 js/                      # JavaScript files
├── 🖼️ img/                     # Images & assets
├── 📄 sql/                     # Database scripts
└── 📋 README.md                # This file
```

## 🎯 **Key Features Breakdown**

### **Client Portal**
- ✅ Application submission with document upload
- ✅ Real-time application status tracking
- ✅ Secure document management
- ✅ Profile editing and preferences
- ✅ Multi-currency support
- ✅ Mobile-responsive design

### **Admin Panel**
- ✅ Complete client management
- ✅ Document review and approval
- ✅ Advanced search and filtering
- ✅ Data export capabilities
- ✅ User account management
- ✅ Email template customization

### **Security & Performance**
- ✅ Secure file handling
- ✅ Session management
- ✅ Input sanitization
- ✅ Optimized database queries
- ✅ Responsive caching

## 🔧 **Configuration Options**

### **Currency Settings**
The system supports 40+ currencies with automatic formatting:
- USD, EUR, GBP, CAD, AUD
- GHS, NGN, KES, UGX, TZS
- JPY, CNY, INR, BRL, MXN
- And many more...

### **File Upload Settings**
```php
// Maximum file size (default: 10MB)
$max_file_size = 10 * 1024 * 1024;

// Allowed file types
$allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
```

### **Email Configuration**
```php
// SMTP settings for email notifications
$smtp_host = 'your-smtp-server.com';
$smtp_port = 587;
$smtp_username = 'your-email@domain.com';
$smtp_password = 'your-password';
```

## 📱 **Mobile Support**

The system is fully responsive and optimized for:
- 📱 **Mobile phones** (320px+)
- 📱 **Tablets** (768px+)
- 💻 **Desktops** (1024px+)
- 🖥️ **Large screens** (1200px+)

## 🔒 **Security Features**

- **Authentication**: Secure login with session management
- **Authorization**: Role-based access control
- **File Security**: Protected document access
- **Input Validation**: XSS and SQL injection protection
- **Session Security**: Automatic timeout and regeneration

## 🎨 **Customization**

### **Branding**
- Update company name in `config.php`
- Replace logo in `img/` directory
- Modify color scheme in CSS files

### **Email Templates**
- Customize templates in admin panel
- Support for HTML and plain text
- Dynamic variable replacement

## 📊 **Database Schema**

### **Main Tables**
- `clients` - Client information and applications
- `client_documents` - Document uploads and status
- `admin_users` - Administrative user accounts
- `email_templates` - Customizable email templates

## 🚀 **Deployment**

### **Production Checklist**
- [ ] Update database credentials
- [ ] Set proper file permissions
- [ ] Configure SSL certificate
- [ ] Enable error logging
- [ ] Set up automated backups
- [ ] Configure email settings
- [ ] Test all functionality

### **Performance Optimization**
- Enable gzip compression
- Configure browser caching
- Optimize database queries
- Minify CSS/JS files
- Use CDN for static assets

## 🤝 **Contributing**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 **Support**

For support and questions:
- 📧 **Email**: info@m25travelagency.com
- 📱 **Phone**: +233 59 260 5752
- 🌐 **Website**: [M25 Travel & Tours Agency](https://m25travelagency.com)

## 🎉 **Acknowledgments**

- Bootstrap team for the excellent CSS framework
- Font Awesome for the beautiful icons
- PHP community for continuous improvements
- All contributors and testers

---

**Built with ❤️ for M25 Travel & Tours Agency**
