Employee Management System
Follow these 3 steps to get your system running in 5 minutes.

🛠️ Step 1: Database Setup
Open XAMPP Control Panel and start Apache and MySQL.

Go to http://localhost/phpmyadmin.

Click New on the left and create a database named employee_system.

Click the Import tab at the top.

Choose your database.sql file and click Go.

⚙️ Step 2: Update Connection Info
Ensure the top of each PHP file (index.php, add.php, edit.php, delete.php) looks like this:

PHP
<?php

$host = 'localhost';           // MySQL host
$db_name = 'employee_system';  // Database name
$db_user = 'root';             // MySQL username
$db_pass = '';                 // MySQL password


try {
    $conn = new PDO("mysql:host=$host;port=3307;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

📂 Step 3: Run the App
Move your project folder to: C:\xampp\htdocs\employee-system\

Open your browser and type:

http://localhost/employee-system/index.php

📖 How to Use
Add Employee: Click the + Add button and fill out the form.

Edit: Click the Blue button to update details.

Delete: Click the Red button. (A confirmation box will appear).

❓ Troubleshooting
Database Error: Make sure the $db_name in your code matches the name you typed in phpMyAdmin exactly.

SQL Error: Ensure you imported database.sql before trying to add an employee.

XAMPP Error: If MySQL won't start, check if another program (like Skype or MySQL Workbench) is using the port.