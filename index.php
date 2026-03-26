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

// Get search and filter parameters
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_email = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';
$filter_department = isset($_GET['filter_department']) ? trim($_GET['filter_department']) : '';
$filter_salary_min = isset($_GET['filter_salary_min']) && $_GET['filter_salary_min'] !== '' ? (int)$_GET['filter_salary_min'] : null;
$filter_salary_max = isset($_GET['filter_salary_max']) && $_GET['filter_salary_max'] !== '' ? (int)$_GET['filter_salary_max'] : null;

// Build the SQL query with filters
$query = "SELECT * FROM employees WHERE 1=1";
$params = [];

if (!empty($search_name)) {
    $query .= " AND name LIKE ?";
    $params[] = "%" . $search_name . "%";
}

if (!empty($search_email)) {
    $query .= " AND email LIKE ?";
    $params[] = "%" . $search_email . "%";
}

if (!empty($filter_department)) {
    $query .= " AND department = ?";
    $params[] = $filter_department;
}

if ($filter_salary_min !== null) {
    $query .= " AND salary >= ?";
    $params[] = $filter_salary_min;
}

if ($filter_salary_max !== null) {
    $query .= " AND salary <= ?";
    $params[] = $filter_salary_max;
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all unique departments for the filter dropdown
$dept_stmt = $conn->query("SELECT DISTINCT department FROM employees ORDER BY department ASC");
$departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
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
            animation: slideInDown 0.4s ease-out, slideOutUp 0.4s ease-out 3.6s forwards;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideOutUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-30px);
            }
        }

        .filter-section {
            background: #1e293b;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .filter-title {
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #d1d5db;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .filter-group input::placeholder {
            color: #6b7280;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .filter-group select option {
            background: #1e293b;
            color: #ffffff;
        }

        .filter-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-filter,
        .btn-reset {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-filter {
            background: #6366f1;
            color: white;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .btn-filter:hover {
            background: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-reset {
            background: rgba(255, 255, 255, 0.1);
            color: #d1d5db;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-reset:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.3);
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

        <div class="filter-section">
            <div class="filter-title">
                <i class="fas fa-filter"></i> Search & Filter Employees
            </div>

            <form method="GET" action="index.php" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="search_name">Search by Name</label>
                        <input type="text" id="search_name" name="search_name" placeholder="e.g., John Doe" value="<?php echo htmlspecialchars($search_name); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="search_email">Search by Email</label>
                        <input type="email" id="search_email" name="search_email" placeholder="e.g., john@company.com" value="<?php echo htmlspecialchars($search_email); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="filter_department">Filter by Department</label>
                        <select id="filter_department" name="filter_department">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $filter_department === $dept ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter_salary_min">Minimum Salary (₹)</label>
                        <input type="number" id="filter_salary_min" name="filter_salary_min" placeholder="e.g., 30000" value="<?php echo $filter_salary_min !== null ? $filter_salary_min : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="filter_salary_max">Maximum Salary (₹)</label>
                        <input type="number" id="filter_salary_max" name="filter_salary_max" placeholder="e.g., 100000" value="<?php echo $filter_salary_max !== null ? $filter_salary_max : ''; ?>">
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <button type="button" class="btn-reset" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset Filters
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message" id="successMsg">
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

    <script>
        function resetFilters() {
            // Clear all filter inputs
            document.getElementById('search_name').value = '';
            document.getElementById('search_email').value = '';
            document.getElementById('filter_department').value = '';
            document.getElementById('filter_salary_min').value = '';
            document.getElementById('filter_salary_max').value = '';

            // Submit the form to refresh the page with no filters
            window.location.href = 'index.php';
        }

        // Auto-dismiss success message after animation completes
        const successMsg = document.getElementById('successMsg');
        if (successMsg) {
            // Remove the element after 4 seconds (animation takes 4s total)
            setTimeout(() => {
                successMsg.remove();
            }, 4000);
        }
    </script>
</body>
</html>