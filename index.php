<?php

require_once 'config.php';

// Get search and filter parameters
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_email = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';
$filter_department = isset($_GET['filter_department']) ? trim($_GET['filter_department']) : '';
$filter_salary_min = isset($_GET['filter_salary_min']) && $_GET['filter_salary_min'] !== '' ? (int)$_GET['filter_salary_min'] : null;
$filter_salary_max = isset($_GET['filter_salary_max']) && $_GET['filter_salary_max'] !== '' ? (int)$_GET['filter_salary_max'] : null;
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'date_added';

// Pagination parameters
$employees_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $employees_per_page;

// Check if any filters are active
$has_active_filters = !empty($search_name) || !empty($search_email) || !empty($filter_department) || $filter_salary_min !== null || $filter_salary_max !== null;

// Build the SQL query with filters
$query = "SELECT * FROM employees WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM employees WHERE 1=1";
$params = [];

if (!empty($search_name)) {
    $query .= " AND name LIKE ?";
    $count_query .= " AND name LIKE ?";
    $params[] = "%" . $search_name . "%";
}

if (!empty($search_email)) {
    $query .= " AND email LIKE ?";
    $count_query .= " AND email LIKE ?";
    $params[] = "%" . $search_email . "%";
}

if (!empty($filter_department)) {
    $query .= " AND department = ?";
    $count_query .= " AND department = ?";
    $params[] = $filter_department;
}

if ($filter_salary_min !== null) {
    $query .= " AND salary >= ?";
    $count_query .= " AND salary >= ?";
    $params[] = $filter_salary_min;
}

if ($filter_salary_max !== null) {
    $query .= " AND salary <= ?";
    $count_query .= " AND salary <= ?";
    $params[] = $filter_salary_max;
}

// Apply sorting
switch($sort_by) {
    case 'name':
        $query .= " ORDER BY name ASC";
        break;
    case 'salary_high':
        $query .= " ORDER BY salary DESC";
        break;
    case 'department':
        $query .= " ORDER BY department ASC, name ASC";
        break;
    default:
        $query .= " ORDER BY id DESC";
}

// Add pagination to main query
$query .= " LIMIT " . (int)$employees_per_page . " OFFSET " . (int)$offset;

// Execute queries
$stmt = $conn->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute($params); // Use all params since LIMIT/OFFSET are now direct values
$total_employees = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_employees / $employees_per_page);

// Ensure current page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

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
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
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

        /* Hide spinner arrows on number inputs */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        .sort-section {
            background: #1e293b;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .sort-label {
            font-size: 14px;
            font-weight: 600;
            color: #d1d5db;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sort-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sort-btn {
            padding: 8px 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #d1d5db;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: none;
        }

        .sort-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        .sort-btn.active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .filter-icon svg,
        .sort-icon svg {
            width: 24px;
            height: 24px;
            stroke: #6366f1;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .sort-icon svg {
            stroke: #a5b4fc;
        }

        .no-user-found {
            background: #1e293b;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .no-user-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .no-user-title {
            font-size: 20px;
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .no-user-message {
            color: #9ca3af;
            font-size: 15px;
            margin-bottom: 24px;
        }

        .pagination-section {
            background: #1e293b;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .pagination-info {
            color: #d1d5db;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #d1d5db;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pagination-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        .pagination-btn.active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn:disabled:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: #d1d5db;
        }

        .page-input {
            width: 60px;
            padding: 8px 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            font-family: inherit;
        }

        .page-input:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.12);
        }

        .page-jump {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #d1d5db;
            font-size: 14px;
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
                <span class="section-icon filter-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <line x1="4" y1="6" x2="20" y2="6"/>
                        <line x1="6" y1="12" x2="18" y2="12"/>
                        <line x1="8" y1="18" x2="16" y2="18"/>
                    </svg>
                </span>
                Search & Filter Employees
            </div>

            <form method="GET" action="index.php" id="filterForm">
                <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when applying filters -->
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
                        <span style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            Apply Filters
                        </span>
                    </button>
                    <button type="button" class="btn-reset" onclick="resetFilters()">
                        <span style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2-8.83"></path>
                            </svg>
                            Reset Filters
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message" id="successMsg">
                ✓ Operation completed successfully!
            </div>
        <?php endif; ?>

        <!-- Sort Section -->
        <div class="sort-section">
            <span class="sort-label">
                <span class="section-icon sort-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="14" width="4" height="6"/>
                        <rect x="10" y="8" width="4" height="12"/>
                        <rect x="17" y="3" width="4" height="17"/>
                    </svg>
                </span>
                Sort By:
            </span>
            <div class="sort-buttons">
                <a href="?search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&filter_department=<?php echo urlencode($filter_department); ?>&filter_salary_min=<?php echo $filter_salary_min ?? ''; ?>&filter_salary_max=<?php echo $filter_salary_max ?? ''; ?>&sort_by=date_added&page=1" class="sort-btn <?php echo $sort_by === 'date_added' ? 'active' : ''; ?>">Date Added</a>
                <a href="?search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&filter_department=<?php echo urlencode($filter_department); ?>&filter_salary_min=<?php echo $filter_salary_min ?? ''; ?>&filter_salary_max=<?php echo $filter_salary_max ?? ''; ?>&sort_by=name&page=1" class="sort-btn <?php echo $sort_by === 'name' ? 'active' : ''; ?>">Name (A-Z)</a>
                <a href="?search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&filter_department=<?php echo urlencode($filter_department); ?>&filter_salary_min=<?php echo $filter_salary_min ?? ''; ?>&filter_salary_max=<?php echo $filter_salary_max ?? ''; ?>&sort_by=salary_high&page=1" class="sort-btn <?php echo $sort_by === 'salary_high' ? 'active' : ''; ?>">Salary (High-Low)</a>
                <a href="?search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&filter_department=<?php echo urlencode($filter_department); ?>&filter_salary_min=<?php echo $filter_salary_min ?? ''; ?>&filter_salary_max=<?php echo $filter_salary_max ?? ''; ?>&sort_by=department&page=1" class="sort-btn <?php echo $sort_by === 'department' ? 'active' : ''; ?>">Department</a>
            </div>
        </div>

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
                <?php if ($has_active_filters): ?>
                    <div class="no-user-found">
                        <div class="no-user-icon">🔍</div>
                        <div class="no-user-title">NO USER FOUND</div>
                        <div class="no-user-message">TRY REMOVING THE FILTER</div>
                        <a href="index.php" class="btn-reset" style="display: inline-block;">Clear All Filters</a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3 style="color: var(--dark); margin-bottom: 10px;">No employees yet</h3>
                        <p>Start by adding your first employee to the system</p>
                        <br>
                        <a href="add.php" class="btn-add">+ Add Employee</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_employees > 0): ?>
        <div class="pagination-section">
            <div class="pagination-info">
                <?php
                $start = $offset + 1;
                $end = min($offset + $employees_per_page, $total_employees);
                echo "Showing {$start}-{$end} of {$total_employees} results";
                ?>
            </div>

            <div class="pagination-controls">
                <?php
                // Build URL parameters for pagination links
                $base_url_params = [];
                if (!empty($search_name)) $base_url_params[] = "search_name=" . urlencode($search_name);
                if (!empty($search_email)) $base_url_params[] = "search_email=" . urlencode($search_email);
                if (!empty($filter_department)) $base_url_params[] = "filter_department=" . urlencode($filter_department);
                if ($filter_salary_min !== null) $base_url_params[] = "filter_salary_min=" . $filter_salary_min;
                if ($filter_salary_max !== null) $base_url_params[] = "filter_salary_max=" . $filter_salary_max;
                if ($sort_by !== 'date_added') $base_url_params[] = "sort_by=" . urlencode($sort_by);
                $url_params = !empty($base_url_params) ? "&" . implode("&", $base_url_params) : "";
                ?>

                <!-- Previous Button -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $url_params; ?>" class="pagination-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Previous
                    </a>
                <?php else: ?>
                    <span class="pagination-btn" style="cursor: not-allowed; opacity: 0.5;">
                        <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Previous
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                    echo '<a href="?page=1' . $url_params . '" class="pagination-btn">1</a>';
                    if ($start_page > 2) echo '<span class="pagination-btn" style="cursor: default; background: transparent;">...</span>';
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $page) ? 'active' : '';
                    echo '<a href="?page=' . $i . $url_params . '" class="pagination-btn ' . $active_class . '">' . $i . '</a>';
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo '<span class="pagination-btn" style="cursor: default; background: transparent;">...</span>';
                    echo '<a href="?page=' . $total_pages . $url_params . '" class="pagination-btn">' . $total_pages . '</a>';
                }
                ?>

                <!-- Next Button -->
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $url_params; ?>" class="pagination-btn">
                        Next
                        <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="pagination-btn" style="cursor: not-allowed; opacity: 0.5;">
                        Next
                        <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                <?php endif; ?>

                <!-- Jump to Page -->
                <div class="page-jump">
                    <span>Jump to:</span>
                    <form method="GET" action="index.php" style="display: flex; gap: 8px; align-items: center;">
                        <?php if (!empty($search_name)): ?><input type="hidden" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>"><?php endif; ?>
                        <?php if (!empty($search_email)): ?><input type="hidden" name="search_email" value="<?php echo htmlspecialchars($search_email); ?>"><?php endif; ?>
                        <?php if (!empty($filter_department)): ?><input type="hidden" name="filter_department" value="<?php echo htmlspecialchars($filter_department); ?>"><?php endif; ?>
                        <?php if ($filter_salary_min !== null): ?><input type="hidden" name="filter_salary_min" value="<?php echo $filter_salary_min; ?>"><?php endif; ?>
                        <?php if ($filter_salary_max !== null): ?><input type="hidden" name="filter_salary_max" value="<?php echo $filter_salary_max; ?>"><?php endif; ?>
                        <?php if ($sort_by !== 'date_added'): ?><input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>"><?php endif; ?>
                        <input type="number" name="page" class="page-input" min="1" max="<?php echo $total_pages; ?>" placeholder="Page" value="<?php echo $page; ?>">
                        <button type="submit" class="pagination-btn" style="padding: 6px 12px;">Go</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
        function resetFilters() {
            // Clear all filter inputs
            document.getElementById('search_name').value = '';
            document.getElementById('search_email').value = '';
            document.getElementById('filter_department').value = '';
            document.getElementById('filter_salary_min').value = '';
            document.getElementById('filter_salary_max').value = '';

            // Submit the form to refresh the page with no filters and reset to page 1
            window.location.href = 'index.php?page=1';
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