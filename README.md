# Student Course Hub - Web Development Project

A comprehensive university course management system with admin, staff, and student interfaces. Built with PHP, MySQL, HTML, CSS, and JavaScript.

## 📋 Table of Contents
- [Student Course Hub - Web Development Project](#student-course-hub---web-development-project)
  - [📋 Table of Contents](#-table-of-contents)
  - [🎓 Project Overview](#-project-overview)
    - [Key Features](#key-features)
  - [✨ Features](#-features)
    - [Student Interface (Complete)](#student-interface-complete)
    - [Admin Interface (Complete)](#admin-interface-complete)
    - [Staff Interface (Complete)](#staff-interface-complete)
  - [💻 Requirements](#-requirements)
    - [Server Requirements](#server-requirements)
    - [Required PHP Extensions](#required-php-extensions)
    - [Browser Requirements](#browser-requirements)
  - [🚀 Installation Guide](#-installation-guide)
    - [Option 1: XAMPP (Windows/Mac/Linux)](#option-1-xampp-windowsmaclinux)
    - [Option 2: WAMP (Windows only)](#option-2-wamp-windows-only)
    - [Option 3: MAMP (Mac only)](#option-3-mamp-mac-only)

---

## 🎓 Project Overview

Student Course Hub is a web-based application designed for a UK university to manage and market undergraduate and postgraduate degree programmes. The system allows:

- **Students**: Browse programmes, view modules, and register interest
- **Staff**: View assigned modules and programmes, see interested students
- **Admin**: Full CRUD operations for programmes, modules, staff, and user accounts

### Key Features
- 📚 Programme and module management with images
- 👥 Staff profiles with photos, titles, and bios
- 📧 Mailing list generation with CSV export
- 🔐 Secure authentication with role-based access control
- 📱 Fully responsive design (mobile, tablet, desktop)
- ♿ WCAG2 compliant accessibility features

---

## ✨ Features

### Student Interface (Complete)
- Browse published programmes
- Filter by undergraduate/postgraduate level
- Search programmes by keywords
- View programme details with modules by year
- View staff profiles (module leaders and programme leaders)
- Register interest in programmes
- View all modules with module leaders

### Admin Interface (Complete)
- Dashboard with statistics
- Programme CRUD (Create, Read, Update, Delete)
- Module CRUD with image upload
- Staff management with profile photos
- User account management (admin/staff)
- Programme-module assignment with year
- Mailing list view and CSV export
- Publish/unpublish programmes
- Academic qualification tracking for student registrations

### Staff Interface (Complete)
- Personal dashboard with stats
- View assigned modules with details
- View assigned programmes
- View interested students
- Export student lists to CSV
- Profile management
- Change password functionality

---

## 💻 Requirements

### Server Requirements
- **PHP**: Version 7.4 or higher (8.x recommended)
- **MySQL**: Version 5.7 or higher (8.x recommended)
- **Web Server**: Apache (XAMPP/WAMP/LAMP recommended)

### Required PHP Extensions
- `mysqli` - MySQL database connection
- `gd` - Image processing (for uploads)
- `fileinfo` - File type detection
- `session` - Session management
- `openssl` - Security features

### Browser Requirements
- Modern browsers: Chrome, Firefox, Safari, Edge
- JavaScript enabled
- Cookies enabled
- Minimum screen width: 320px (fully responsive)

---

## 🚀 Installation Guide

### Option 1: XAMPP (Windows/Mac/Linux)

1. **Download and install XAMPP** from [https://www.apachefriends.org/](https://www.apachefriends.org/)

2. **Clone or download the project**
   ```bash
   git clone https://github.com/yourusername/Web_Dev_Project.git

3. Copy Project to htdocs
  cp -r Web_Dev_Project /opt/lampp/htdocs/students_course_hub
4. Start XAPP services
  Start Apache
  Start MySQL
5. Import the database (see Database Setup)
6. Create upload folders (see Configuration)
7. Access the application
      - Admin/Staff Login: http://localhost/students_course_hub/auth/login.php
      - Student Interface: http://localhost/students_course_hub/home/index.php

### Option 2: WAMP (Windows only)
1. Download and install WAMP from https://www.wampserver.com/

2. Clone or download the project to        C:\wamp64\www\students_course_hub         

3. Start WAMP services (click the icon in system tray)

4. Import the database (see Database Setup)

5. Create upload folders (see Configuration)

6. Access the application

    - Admin/Staff Login: http://localhost/students_course_hub/auth/login.php

    - Student Interface: http://localhost/students_course_hub/home/index.php
  
### Option 3: MAMP (Mac only)
1. Download and install MAMP from https://www.mamp.info/

2. Clone or download the project to /Applications/MAMP/htdocs/students_course_hub

3. Start MAMP services

4. Import the database (see Database Setup)

5. Create upload folders (see Configuration)

6. Access the application

   - Admin/Staff Login: http://localhost:8888/students_course_hub/auth/login.php

  - Student Interface: http://localhost:8888/students_course_hub/home/index.php
  
🗄️ Database Setup
Step 1: Import the Database
- Open phpMyAdmin (http://localhost/phpmyadmin)

- Click on New to create a database

- Database name: student_course_hub

- Collation: utf8mb4_unicode_ci

- Click Import tab

- Choose the database.sql file from the project root

- Click Go to import

Step 2: Run Additional SQL (Required for Student Fields)
After importing, run this SQL to add student registration fields:
-- Add student registration fields to InterestedStudents
ALTER TABLE InterestedStudents 
ADD COLUMN AcademicQualification VARCHAR(50) DEFAULT NULL AFTER StudentName,
ADD COLUMN ReceiveUpdates ENUM('yes','no') DEFAULT 'no' AFTER Email;

-- Update existing records
UPDATE InterestedStudents SET ReceiveUpdates = 'no' WHERE ReceiveUpdates IS NULL;

Step 3: Create Upload Folders
Create these folders in your project root (they must be writable):
  mkdir uploads
  mkdir uploads/staff
  mkdir uploads/programmes
  mkdir uploads/modules
  mkdir logs

On Windows: Right-click folder → Properties → Security → Users → Full Control

On Mac/Linux:
   chmod -R 777 uploads/
   chmod -R 777 logs/

⚙️ Configuration
1. Update Database Configuration
Open includes/config.php and verify these settings:
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'student_course_hub');

2. Update Base URL
In includes/config.php, update BASE_URL to match your setup:
    // If your project is in htdocs/students_course_hub/
    define('BASE_URL', 'http://localhost/students_course_hub/');
    define('BASE_PATH', '/students_course_hub/');

    // If your project is directly in htdocs/
    define('BASE_URL', 'http://localhost/');
    define('BASE_PATH', '/');

    // If using MAMP on port 8888
    define('BASE_URL', 'http://localhost:8888/students_course_hub/');

🔑 Default Login Credentials
Admin Access
Username	Password	Role	Description
admin	admin123	Administrator	Full system access
Staff Access
Username	Password	Role	Linked Staff
ajohnson	staff123	Staff	Dr. Alice Johnson
blee	staff123	Staff	Dr. Brian Lee

🚨Important Notes
First Login: Staff/Admin should change their password immediately

Password Reset: Admin can reset passwords via Users Management page

Default Password: All new users get password123 (must change on first login)


📁 Project Structure
students_course_hub/
│
├── auth/                           # Authentication files
│   ├── login.php                   # Login page
│   ├── logout.php                  # Logout script
│   └── change-password.php         # Password change page
│
├── admin/                          # Admin interface (Complete)
|   |__ css/admin-style.css         # Admin css styles
│   ├── dashboard.php               # Admin dashboard
│   ├── programmes.php              # Programme management
│   ├── edit-programme.php          # Add/edit programme
│   ├── process-programme.php       # Programme CRUD processing
│   ├── modules.php                 # Module management
│   ├── edit-module.php             # Add/edit module
│   ├── process-module.php          # Module CRUD processing
│   ├── staff.php                   # Staff management
│   ├── edit-staff.php              # Add/edit staff
│   ├── process-staff.php           # Staff CRUD processing
│   ├── users.php                   # User account management
│   ├── add-user.php                # Create user accounts
│   ├── edit-user.php               # Edit user accounts
│   ├── mailing-list.php            # View interested students
│   ├── export-mailing.php          # Export mailing list CSV
│   ├── programme-modules.php       # Manage programme modules
│   ├── process-programme-module.php # Module assignment processing
│   ├── assign-module.php           # Assign module to programme
│   └── process-assign-module.php   # Assignment processing
│
├── staff/                          # Staff interface (Complete)
|   |__ css/staff-styles.css        # staff css stylesheet
│   ├── dashboard.php               # Staff dashboard
│   ├── my-modules.php              # View assigned modules
│   ├── my-programmes.php           # View assigned programmes
│   ├── profile.php                 # Staff profile
│   ├── programme-students.php      # View interested students
│   ├── export-programme-students.php # Export CSV
│   ├── module-students.php         # View module students
│   └── export-module-students.php  # Export module students CSV
│
├── home/                           # Student interface (Complete)
|   |__ Register/debug.php          # debug file for students daata
|   |           /main.php           # register interested course
|   |     /process_registration.php # registeration form
|   |     /register.css             # register css file
|   |     /test_connection.php      #test db for student interface
|   |     /thankyou.php             #success message for form submission
│   ├── index.php                   # Student landing page
│   ├── programmes.php              # Browse programmes
│   ├── programme_details.php       # Programme details
│   ├── modules.php                 # Browse modules
│   ├── staff.php                   # Staff directory
│   ├── search.php                  # Search functionality
│   ├── header.php                  # Student header
│   ├── footer.php                  # Student footer
│   └── style.css                   # Student stylesheet
│
├── includes/                       # Core files
│   ├── config.php                  # Database and security config
│   ├── header.php                  # Site header (admin/staff)
│   └── footer.php                  # Site footer (admin/staff)
│
├── uploads/                        # Uploaded images
│   ├── staff/                      # Staff profile photos
│   ├── programmes/                 # Programme images
│   └── modules/                    # Module images
│
├── logs/                           # Security logs
│   └── security.log                # Login attempts and events
|   |__ .htacess                    # Apache security rules
│
├── css/                            # Stylesheets (admin/staff)
│   ├── common.css                  # Shared styles
│   ├── login.css                   # Login page styles
│   ├── admin/                      # Admin specific styles
│   └── staff/                      # Staff specific styles
│
├── js/                             # JavaScript
│   └── script.js                   # Main JavaScript file
│
├── assets/                         # Static assets
│   └── fontawesome/                # Font Awesome icons
│
├── database.sql                    # Database schema and sample data
└── README.md                       # Readme file for complete setups and configurations


🔧 Troubleshooting
Common Issues and Solutions
1. 404 Not Found Error
Check that your folder name matches BASE_PATH in config.php

Verify you're accessing the correct URL

Ensure all files are in the correct locations

2. Database Connection Error
Verify MySQL is running (XAMPP/WAMP/MAMP)

Check database credentials in includes/config.php

Ensure database name is correct: student_course_hub

3. Login Failed
Check if user is active in AdminUsers table (IsActive = 1)

Try resetting password via Users Management

Clear browser cookies and cache

4. "Too many failed attempts" Message
Wait 15 minutes or clear browser cookies

Or run: DELETE FROM LoginAttempts WHERE IPAddress = 'your_ip';

5. Image Upload Not Working
Check upload folders exist and are writable

Ensure file size is less than 2MB

Use allowed file types: JPG, PNG, GIF, WebP

6. Session Timeout Too Fast
In includes/config.php, increase timeout value:
  $timeout = 3600; // 1 hour instead of 30 minutes

7. CSRF Token Error
Clear browser cookies and refresh the page

Ensure session is working properly

8. Blank Page or White Screen
Enable error reporting in includes/config.php:
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   
   Check Apache/PHP error logs

9. File Permissions (Linux/Mac)
     run commands on cmd prompt or terminal
     chmod -R 755 includes/
     chmod -R 777 uploads/
     chmod -R 777 logs/
10. .htaccess Issues
If you get Internal Server Error, rename .htaccess to htaccess.backup
Or comment out the rewrite rules

WAMP-Specific Issues
Apache won't start: Check if port 80 is in use (Skype, IIS)

MySQL won't start: Check if port 3306 is in use

mod_rewrite not working: Enable via WAMP menu → Apache → Apache Modules → rewrite_module

XAMPP-Specific Issues
Port conflicts: Change ports in Apache config if needed

Permissions: Run XAMPP as administrator

🛡️ Security Features
✅ CSRF tokens on all forms

✅ Prepared statements for SQL injection prevention

✅ Password hashing with bcrypt

✅ Session timeout (30 minutes)

✅ Session validation (IP/User Agent)

✅ Login attempt limiting (5 attempts / 15 minutes)

✅ XSS prevention with htmlspecialchars()

✅ Security logging

✅ .htaccess protection

✅ No browser password saving (autocomplete="off")

✅ Session cookie expires on browser close

📊 Database Schema
Tables
Table	Description
Levels	Undergraduate/Postgraduate levels
Staff	Staff profiles with photos, titles, bios, departments
Modules	Module details with status, images, alt text
Programmes	Programme details with publish status, images, alt text
ProgrammeModules	Links modules to programmes with year
InterestedStudents	Student interest tracking with qualifications
AdminUsers	Admin and staff login accounts

Sample Data
The database comes pre-loaded with:

2 levels (Undergraduate, Postgraduate)

20 staff members

10 modules

10 programmes

Programme-module relationships

Sample student registrations with academic qualifications

3 user accounts (1 admin, 2 staff)


🚀 Deployment Notes
For Production Deployment
1. Change Database Password
   change in db config file:
   define('DB_PASS', 'your_secure_password');
2. Enable HTTPS
   - Update BASE_URL to use HTTPS
   - Configure SSL certificate

3. Disable Error Display
   ini_set('display_errors', 0);

4. Update Security Settings

  - Change default passwords

  - Configure proper file permissions

- - Enable password strength requirements

5. Set up Backups

 - Regular database backups

 - File backups for uploads folder

👥 Contributors
Contributor	Role	Responsibilities
Sherab Namgyal : 	Admin Interface	Admin dashboard, CRUD operations, security features
Ashma Dahal	: Staff Interface	Staff dashboard, view modules, student lists
Tek Bahadur Dangi : 	Student Interface	Student browsing, registration, search

📝 License
This project is for educational purposes only. All rights reserved.

📧 Support
If you encounter any issues:

Check the Troubleshooting section

Review PHP error logs

Check Apache error logs

Contact the project maintainer

🎯 Quick Start Checklist
Install XAMPP/WAMP/MAMP

Copy project to htdocs/www folder

Start Apache and MySQL

Import database.sql in phpMyAdmin

Run additional SQL for student fields

Create upload folders (staff, programmes, modules)

Create logs folder with security.log

Update config.php with correct paths

Access: http://localhost/students_course_hub/auth/login.php

Login with admin/admin123

Test all functionality

Change default passwords

Happy Coding! 🚀