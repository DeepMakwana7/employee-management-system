# Employee Management System (CRUD)

A complete employee management application built with PHP, MySQL, and modern CSS styling. This system allows you to create, read, update, and delete employee records with a clean, responsive interface.

## 📋 Project Structure

```
employee-system/
├── index.php          # Dashboard - List all employees
├── add.php           # Create - Add new employee form
├── edit.php          # Update - Edit employee details
├── delete.php        # Delete - Remove employee record
├── database.sql      # Database schema and sample data
└── README.md         # Documentation
```

## 🎯 Features

### 1. **Read (index.php)**
- Display all employees in a styled table
- Shows: Name, Email, Department, Salary
- Edit and Delete action buttons for each employee
- Empty state message when no employees exist
- Success notifications after operations
- Responsive design for mobile devices

### 2. **Create (add.php)**
- Form to add a new employee
- Fields: Name, Email, Department, Salary
- Client & server-side validation
- Error messaging for invalid inputs
- Redirects to dashboard after successful submission
- Department dropdown with 7 predefined options

### 3. **Update (edit.php)**
- Pre-filled form with current employee details
- Fetch employee by ID
- Update all fields
- Validation before update
- Redirects to dashboard on success
- Error handling for missing records

### 4. **Delete (delete.php)**
- Delete employee by ID
- Confirmation dialog before deletion
- Quick redirect back to dashboard
- Error handling

## 🗄️ Database Schema

### employees table
```sql
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(50) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Fields:
- **id**: Primary key (auto-increment)
- **name**: Employee full name (required, max 100 characters)
- **email**: Employee email (required, unique, max 100 characters)
- **department**: Department name (required, max 50 characters)
- **salary**: Annual salary (decimal with 2 decimal places)
- **created_at**: Record creation timestamp
- **updated_at**: Last update timestamp

## 🚀 Installation & Setup

### Prerequisites
- PHP 5.4+ (PDO extension)
- MySQL 5.5+
- Web server (Apache, Nginx, etc.)

### Step 1: Create Database
1. Open MySQL command line or phpMyAdmin
2. Execute the SQL from `database.sql`:
   ```bash
   mysql -u root -p < database.sql
   ```
   Or paste the SQL content into phpMyAdmin

### Step 2: Configure Database Connection
Edit the connection details in each PHP file (lines 2-8):
```php
$host = 'localhost';      // MySQL host
$db_name = 'employee_system';  // Database name
$db_user = 'root';        // MySQL username
$db_pass = '';            // MySQL password
```

### Step 3: Move Files to Web Root
- Copy all PHP files to your web server's document root
- Example: `/var/www/html/employee-system/`

### Step 4: Access the Application
- Open browser: `http://localhost/employee-system/index.php`

## 🎨 Design & UI Features

### Color Scheme
- **Primary Blue**: #2563eb (Links, Edit buttons)
- **Success Green**: #10b981 (Add button)
- **Danger Red**: #ef4444 (Delete button)
- **Dark Gray**: #1f2937 (Text, headers)
- **Light Gray**: #f9fafb (Hover states)

### Styling Highlights
- Modern gradient background (purple to blue)
- Responsive table with hover effects
- Smooth button transitions and shadows
- Mobile-friendly breakpoints (768px)
- Department badges with custom colors
- Clean form layouts with focus states
- Success/error message banners
- Empty state illustration

### Responsive Design
- Works on desktop (1200px+)
- Tablet friendly (768px - 1200px)
- Mobile optimized (<768px)
- Flexible layouts with flexbox
- Stacked buttons on small screens

## 🔐 Security Features

### Input Validation
- Email format validation
- Salary numeric validation
- Required field checking
- HTML special character escaping

### SQL Security
- Prepared statements (PDO) to prevent SQL injection
- Parameter binding for all queries
- Proper error handling

### User Confirmation
- Delete confirmation dialog prevents accidental deletion
- Validation error messages
- Success notifications

## 📱 Form Validation

### Add/Edit Form
```
Name:
- Required
- Max 100 characters
- Cannot be empty

Email:
- Required
- Must be valid email format
- Unique (cannot duplicate)
- Max 100 characters

Department:
- Required
- Predefined options only
- Options: HR, IT, Sales, Marketing, Finance, Operations, Development

Salary:
- Required
- Must be numeric
- Cannot be negative
- Supports decimal values (e.g., 50000.50)
```

## 🔄 Application Flow

```
Landing (index.php)
    ├─ View all employees
    ├─ Click "Add Employee" → add.php
    │   ├─ Fill form
    │   ├─ Click "Save" → Database insert → index.php
    │   └─ Click "Cancel" → Back to index.php
    │
    ├─ Click "Edit" → edit.php?id=X
    │   ├─ Pre-filled form
    │   ├─ Update details
    │   ├─ Click "Update" → Database update → index.php
    │   └─ Click "Cancel" → Back to index.php
    │
    └─ Click "Delete" → Confirmation
        ├─ Click "OK" → delete.php → Database delete → index.php
        └─ Click "Cancel" → Stay on index.php
```

## 📊 Sample Data

The database.sql includes 5 sample employees:
1. Rajesh Kumar - IT - ₹650,000
2. Priya Sharma - HR - ₹550,000
3. Amit Patel - Sales - ₹600,000
4. Neha Singh - Development - ₹700,000
5. Arjun Verma - Finance - ₹580,000

## 🛠️ Customization

### Add New Department
Edit the department dropdown in `add.php` and `edit.php`:
```html
<option value="Department_Name">Displayed Name</option>
```

### Change Color Scheme
Modify CSS variables in each file's `<style>` section:
```css
:root {
    --primary: #2563eb;      /* Change primary color */
    --success: #10b981;      /* Change success color */
    --danger: #ef4444;       /* Change danger color */
    /* ... */
}
```

### Modify Table Columns
Edit the table header and data in `index.php`

### Add More Fields
1. Add column to database: `ALTER TABLE employees ADD COLUMN field_name TYPE;`
2. Add form input in `add.php` and `edit.php`
3. Update INSERT/UPDATE queries
4. Add column to display table in `index.php`

## ⚠️ Common Issues & Solutions

### Issue: "Connection failed" error
**Solution**: Check MySQL credentials in database connection lines. Ensure MySQL server is running.

### Issue: "UNIQUE constraint failed: email"
**Solution**: Email already exists. Use a different email or delete the existing record first.

### Issue: Form not submitting
**Solution**: Check browser console for JavaScript errors. Ensure all required fields are filled.

### Issue: Changes not appearing
**Solution**: Clear browser cache (Ctrl+F5 or Cmd+Shift+R). Check database was actually updated.

## 📈 Future Enhancements

- Pagination for large employee lists
- Search and filter functionality
- Sorting by column
- Bulk delete operations
- Export to CSV/Excel
- Employee profile images
- Department analytics
- Salary range filters
- Login & authentication system
- Admin vs user roles
- Activity logs
- Email notifications

## 📞 Support

For issues or questions:
1. Check MySQL connection settings
2. Verify database and table structure
3. Check browser console for JavaScript errors
4. Ensure PHP errors are displayed in php.ini

## 📄 License

This project is free to use and modify for educational and commercial purposes.

---

**Created**: 2024
**Last Updated**: March 2026
**Version**: 1.0
