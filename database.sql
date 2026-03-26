-- Create the employee_system database
CREATE DATABASE IF NOT EXISTS employee_system;
USE employee_system;

-- Create the employees table
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(50) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data (optional)
INSERT INTO employees (name, email, department, salary) VALUES
('Rajesh Kumar', 'rajesh.kumar@company.com', 'IT', 650000),
('Priya Sharma', 'priya.sharma@company.com', 'HR', 550000),
('Amit Patel', 'amit.patel@company.com', 'Sales', 600000),
('Neha Singh', 'neha.singh@company.com', 'Development', 700000),
('Arjun Verma', 'arjun.verma@company.com', 'Finance', 580000);
