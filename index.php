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

// Fetch all employees
$stmt = $conn->query("SELECT * FROM employees ORDER BY id DESC");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Employee Management System</title>
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
            --warning: #f59e0b;
            --dark: #1f2937;
            --light: #f9fafb;
            --border: #e5e7eb;
            --text: #374151;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            padding: 20px;
            color: #ffffff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #1e293b;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header h1 {
            font-size: 32px;
            color: #ffffff;
            font-weight: 700;
            margin: 0;
        }

        .header-subtitle {
            color: #d1d5db;
            font-size: 14px;
            margin-top: 5px;
        }

        .btn-add {
            background: #6366f1;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-add:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }

        .table-wrapper {
            background: #1e293b;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #1e293b;
            color: white;
        }

        th {
            padding: 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        td:first-child {
            color: #ffffff;
            font-weight: 600;
        }

        td:nth-child(2), td:nth-child(4) {
            color: #94a3b8;
        }

        tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease;
        }

        .employee-name {
            font-weight: 600;
            color: var(--dark);
        }

        .employee-email {
            color: var(--primary);
            font-size: 14px;
        }

        .department {
            display: inline-block;
            background: rgba(59, 130, 246, 0.1);
            color: #1d4ed8;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .salary {
            font-weight: 600;
            color: #ffffff;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 8px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            color: white;
            width: 36px;
            height: 36px;
            text-align: center;
            line-height: 20px;
        }

        .btn-edit {
            background: #6366f1;
        }

        .btn-edit:hover {
            background: #4f46e5;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #f43f5e;
        }

        .btn-delete:hover {
            background: #e11d48;
            transform: translateY(-1px);
        }

        .success-message {
            background: #064e3b;
            color: #34d399;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
            }

            th, td {
                padding: 12px 8px;
                font-size: 13px;
            }

            .actions {
                flex-wrap: wrap;
            }

            .btn-small {
                flex: 1;
                min-width: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👥 Employee Management</h1>
                <p class="header-subtitle">Manage your company's workforce efficiently</p>
            </div>
            <a href="add.php" class="btn-add">+ Add Employee</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                ✓ Operation completed successfully!
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <?php if (count($employees) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Salary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td class="employee-name"><?php echo htmlspecialchars($emp['name']); ?></td>
                                <td class="employee-email"><?php echo htmlspecialchars($emp['email']); ?></td>
                                <td><span class="department"><?php echo htmlspecialchars($emp['department']); ?></span></td>
                                <td class="salary">₹<?php echo number_format($emp['salary'], 2); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn-small btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="delete.php?id=<?php echo $emp['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure?')" title="Delete"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3 style="color: var(--dark); margin-bottom: 10px;">No employees yet</h3>
                    <p>Start by adding your first employee to the system</p>
                    <br>
                    <a href="add.php" class="btn-add">+ Add Employee</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>