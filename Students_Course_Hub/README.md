# Student Course Hub - Web Development Project

A comprehensive university course management system with admin, staff, and student interfaces. Built with PHP, MySQL, HTML, CSS, and JavaScript.

## 📋 Table of Contents
- [Student Course Hub - Web Development Project](#student-course-hub---web-development-project)
  - [📋 Table of Contents](#-table-of-contents)
  - [🎓 Project Overview](#-project-overview)
    - [Key Features](#key-features)
  - [✨ Features](#-features)
    - [Student Interface (Coming Soon)](#student-interface-coming-soon)
    - [Admin Interface (Complete)](#admin-interface-complete)
    - [Staff Interface (Complete)](#staff-interface-complete)
  - [💻 Requirements](#-requirements)
    - [Server Requirements](#server-requirements)
    - [Required PHP Extensions](#required-php-extensions)
    - [Browser Requirements](#browser-requirements)
  - [🚀 Installation Guide](#-installation-guide)
    - [Option 1: XAMPP (Windows/Mac/Linux)](#option-1-xampp-windowsmaclinux)

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

### Student Interface (Coming Soon)
- Browse published programmes
- Filter by undergraduate/postgraduate level
- Search programmes by keywords
- View programme details with modules by year
- See staff profiles (module leaders and programme leaders)
- Register and withdraw interest in programmes

### Admin Interface (Complete)
- Dashboard with statistics
- Programme CRUD (Create, Read, Update, Delete)
- Module CRUD with image upload
- Staff management with profile photos
- User account management (admin/staff)
- Programme-module assignment with year
- Mailing list view and CSV export
- Publish/unpublish programmes

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

3. Start XAMPP services

- Start Apache

- Start MySQL

4. Import the database (see Database Setup)

5. Create upload folders (see Configuration)

6. Access the application

- Admin/Staff Login: http://localhost/students_course_hub/ auth/login.php

Option 2: WAMP (Windows Only)
Download and install WAMP from https://www.wampserver.com/

Clone or download the project to C:\wamp64\www\

Start WAMP services (click the icon in system tray)

Import the database (see Database Setup)

Create upload folders (see Configuration)

Access the application

Admin/Staff Login: http://localhost/students_course_hub/auth/login.php

Option 3: MAMP (Mac Only)
Download and install MAMP from https://www.mamp.info/

Clone or download the project to /Applications/MAMP/htdocs/

Start MAMP services

Import the database (see Database Setup)

Create upload folders (see Configuration)

Access the application

Admin/Staff Login: http://localhost:8888/students_course_hub/auth/login.php