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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $custom_department = trim($_POST['custom_department'] ?? '');
    $salary = trim($_POST['salary']);

    // Handle custom department
    if ($department === 'OTHERS' && !empty($custom_department)) {
        $department = $custom_department;
    }

    // Validation
    if (empty($name) || empty($email) || empty($department) || empty($salary)) {
        $error = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif (!is_numeric($salary) || $salary < 0) {
        $error = 'Salary must be a valid positive number!';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO employees (name, email, department, salary) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $department, $salary]);
            header("Location: index.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = 'Error adding employee: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Add Employee</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1f2937;
            --light: #f9fafb;
            --border: #e5e7eb;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .form-card {
            background: #1e293b;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            transition: gap 0.2s ease;
        }

        .back-link:hover {
            gap: 10px;
        }

        h1 {
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitle {
            color: #d1d5db;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        input::placeholder, select {
            color: #9ca3af;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }

        select option {
            background: #1e293b;
            color: #ffffff;
            padding: 8px;
        }

        select option:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Hide spinner arrows on number inputs */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        .error-message {
            background: #451a1a;
            color: #f87171;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f43f5e;
            font-size: 14px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit {
            background: #6366f1;
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-submit:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }

        .btn-cancel {
            background: var(--border);
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cancel:hover {
            background: #d1d5db;
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 24px;
            }

            h1 {
                font-size: 24px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <a href="index.php" class="back-link">← Back to Employees</a>

            <h1>➕ Add Employee</h1>
            <p class="subtitle">Fill in the details to add a new employee</p>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="add.php">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" placeholder="e.g., John Doe" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="e.g., john@company.com" required>
                </div>

                <div class="form-group">
                    <label for="department">Department *</label>
                    <select id="department" name="department" required onchange="toggleCustomDepartment()">
                        <option value="">Select Department</option>
                        <option value="Full Stack Developer">Full Stack Developer</option>
                        <option value="Data Analyst">Data Analyst</option>
                        <option value="Game Developer">Game Developer</option>
                        <option value="Blockchain Developer">Blockchain Developer</option>
                        <option value="AI/ML Engineer">AI/ML Engineer</option>
                        <option value="DevOps Engineer">DevOps Engineer</option>
                        <option value="OTHERS">OTHERS</option>
                    </select>
                </div>

                <div class="form-group" id="custom-department-group" style="display: none;">
                    <label for="custom_department">Specify Department *</label>
                    <input type="text" id="custom_department" name="custom_department" placeholder="Enter department name">
                </div>

                <div class="form-group">
                    <label for="salary">Annual Salary (₹) *</label>
                    <input type="number" id="salary" name="salary" placeholder="e.g., 500000" step="0.01" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-submit">Save Employee</button>
                    <a href="index.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCustomDepartment() {
            const departmentSelect = document.getElementById('department');
            const customDepartmentGroup = document.getElementById('custom-department-group');
            const customDepartmentInput = document.getElementById('custom_department');

            if (departmentSelect.value === 'OTHERS') {
                customDepartmentGroup.style.display = 'block';
                customDepartmentInput.required = true;
            } else {
                customDepartmentGroup.style.display = 'none';
                customDepartmentInput.required = false;
                customDepartmentInput.value = '';
            }
        }
    </script>
</body>
</html>