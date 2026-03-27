🏢 Employee Management System
A clean, modern, and secure Full-Stack CRUD application built with PHP and MySQL.

🌟 Features
Full CRUD: Create, Read, Update, and Delete employee records seamlessly.

Functionalities: Paging , Move to x page , Sorting by attributes , Searching

Modern UI: Aesthetic design with responsive tables and action buttons.

Security: Powered by PDO Prepared Statements to prevent SQL Injection.

Validation: Built-in checks for empty fields, email formats, and numeric salaries.

🛠️ Tech Stack
Frontend: HTML5, CSS3 (Custom Gradients & Flexbox)

Backend: PHP 8.x (Procedural with PDO)

Database: MySQL (Relational Schema)

Server: XAMPP / Apache

🚀 Quick Setup
Database: Import database.sql into your local phpMyAdmin.

Connection: Open your PHP files and ensure the $db_name, $db_user, and $db_pass match your local XAMPP settings.

Deployment: Move the project folder to C:\xampp\htdocs\employee-system\.

Run: Access the app at http://localhost/employee-system/index.php.

Note: For a detailed step-by-step guide, see QUICK_START.md.

📊 Database Structure
The system uses a single employees table with the following logic:

ID: Primary Key (Auto-increment)

Name & Email: Basic identification (Email must be unique)

Department: Functional grouping (IT, HR, Sales, etc.)

Salary: Numeric decimal values for financial tracking.