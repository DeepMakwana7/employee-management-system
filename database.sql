-- Create the employee_system database
CREATE DATABASE IF NOT EXISTS employee_system;
USE employee_system;

-- Create the employees table
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15),
    department VARCHAR(50) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data (optional)
INSERT INTO employees (name, email, phone, department, salary) VALUES
('Rajesh Kumar', 'rajesh.kumar@company.com', '+919876543210', 'IT', 650000),
('Priya Sharma', 'priya.sharma@company.com', '+919876543211', 'HR', 550000),
('Amit Patel', 'amit.patel@company.com', '+919876543212', 'Sales', 600000),
('Neha Singh', 'neha.singh@company.com', '+919876543213', 'Development', 700000),
('Arjun Verma', 'arjun.verma@company.com', '+919876543214', 'Finance', 580000);
