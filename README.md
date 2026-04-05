# Assignment

---

# Camila

## Overview

This part of the project focuses on the user authentication system and the core frontend structure of the NHS Appointment Booking System.

### The implementation includes:

- User registration  
- Secure login system  
- Session handling  
- Basic logout flow  
- Supporting pages (FAQ, Contact, About)  
- A structured and consistent user interface  

---

## BD User

The NHS Number is stored as a unique identifier and validated using a regular expression to ensure it contains exactly 10 digits.
Phone numbers are restricted to numeric input only.
The email field is used for authentication and must be unique.
A role field was introduced to distinguish between patient and admin users.
Email: admin@test.com
Password: admin123




## 🔹 Implemented Files

### 🔐 Authentication System

#### `register.php`
- Handles user registration  
- Collects:
  - NHS Number  
  - First and last name  
  - Email  
  - Password  
  - Phone number  
  - Date of birth  
- Validates input and prevents duplicate accounts  
- Uses `password_hash()` for secure password storage  

#### `login.php`
- Handles user authentication  
- Verifies credentials using:
  - Prepared statements  
  - `password_verify()`  
- Uses a generic error message for security:  
  - *"Invalid email or password"*  
- Stores user data in session variables  

#### `logout.php`
- Destroys the session securely  
- Displays a professional logout confirmation message  
- Provides navigation options:
  - Return to Home  
  - Log in again  

---

### 🌐 Core Pages

#### `index.php` (Home Page)
- Main entry point of the system  
- Session-aware interface:
  - Shows Register/Login or Dashboard/Logout dynamically  
- Includes:
  - Hero section  
  - Quick access services  
  - Project overview  
- Designed to simulate a real NHS-style interface  

#### `about.php`
- Provides project context and academic information  
- Includes placeholders for:
  - Module name and code  
  - Lecturer  
  - Institution  
  - Team members  
- Designed for easy editing before final submission  

#### `faq.php`
- Displays frequently asked questions  
- Explains:
  - Registration process  
  - Login behavior  
  - NHS Number usage  
  - System design decisions  

#### `contact.php`
- Provides a structured contact form  
- Currently implemented as a frontend demonstration  
- Ready for future backend/email integration  

---

### 🗄️ Database

#### `db_connection.php`
- Establishes connection to MySQL using `mysqli`  
- Used across all backend files  
- Centralized for maintainability  

#### `users.sql`
- Defines the structure of the `users` table  
- Includes:
  - Unique email field  
  - NHS Number as identifier  
  - Hashed password storage  
- Designed following normalization principles  

---

### 🎨 Styling

#### `style.css`
- Shared stylesheet for all pages  
- Provides:
  - Consistent layout  
  - Navigation styling  
  - Form design  
  - Buttons and cards  
- Ensures a clean and professional UI across the system  

---

## 🔹 Key Features

- Secure password handling (`password_hash`, `password_verify`)  
- Protection against SQL injection using prepared statements  
- Session-based authentication system  
- Role-ready structure for future expansion  
- NHS-inspired UI design  
- Consistent navigation across all pages  

---

## 🔹 Notes

- The `dashboard.php` file is currently a **basic placeholder** used for testing login functionality.  

- The system is designed to be extended with:
  - Appointment booking  
  - Notifications  
  - Medical data handling  

---

## 🔹 Contribution Summary

This contribution establishes the foundation of the system, covering:

- Authentication logic  
- Database integration  
- Frontend structure  
- Navigation and user flow  

It ensures that users can register, log in securely, and navigate the system, forming the base for all future features.

---
