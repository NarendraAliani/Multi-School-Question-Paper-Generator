# Project Description: Multi-School Question Paper Generator SaaS

## Overview
The **Multi-School Question Paper Generator** is a SaaS (Software as a Service) application designed to automate the creation of question papers for schools. It supports a multi-tenant architecture where a Super Admin manages multiple schools, and each school has its own admin and teachers. The system facilitates the management of question banks, blueprints, and the intelligent generation of print-ready question papers.

## Technology Stack
- **Backend**: PHP 8+ (Procedural/OOP Hybrid approach)
- **Database**: MySQL (PDO with prepared statements)
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Scripting**: jQuery, AJAX
- **Server**: Apache (XAMPP recommended for dev)

## Core Features

### 1. User Roles & Permissions
- **Super Admin**: 
  - Full system control.
  - Manages Schools, School Admins, and Master Data (Boards, Standards, Subjects).
- **School Admin**: 
  - Manages School Users (Teachers).
  - Views reports and statistics for their school.
- **Teacher**: 
  - Manages Question Bank (Add/Edit Questions).
  - Creates Paper Blueprints.
  - Generates and Prints Question Papers.

### 2. Question Bank Management
- **Hierarchy**: Board -> Standard -> Subject -> Chapter.
- **Metadata**: Difficulty Level, Question Type, Marks.
- **Media**: Support for image uploads in questions.

### 3. Smart Paper Generation
- **Blueprints**: Define paper structure (sections, question types per section, marks).
- **Randomization**: Algorithm selects unique questions matching the blueprint criteria.
- **Output**: Generates A4 print-ready HTML views.

### 4. SaaS Capabilities
- Single instance serves multiple schools.
- Data isolation and role-based access control.

## Project Structure
The project follows a modular structure:
- **`admin/`**: Super Admin functionality.
- **`school_admin/`**: School Admin functionality.
- **`teacher/`**: Teacher functionality.
- **`auth/`**: Login/Logout and authentication logic.
- **`config/`**: Database connection and global constants.
- **`includes/`**: Shared functions, header/footer templates.
- **`modules/`**: Core logic (Paper Generator, AJAX handlers, Models).
- **`uploads/`**: Stores question images and school logos.

## Database Schema
The system maps to a normalized relational database (`paper_generator_saas`) with 13 tables, including:
- `schools`, `users` (Authentication & Authorization)
- `boards`, `standards`, `subjects`, `chapters` (Academic Master Data)
- `questions` (The core content)
- `paper_blueprints`, `generated_papers` (The operational data)

## Installation & Setup
The project is set up for a standard LAMP/WAMP/XAMPP environment.
- **Entry Point**: `index.php` (handles routing based on login status).
- **Config**: `config/constants.php` for DB credentials and Base URL.
- **Database**: Import `schema.sql` and `dummy_data.sql` to initialize.
