=====================================================================
PROJET NAME:  TaskHub - Group Task Management System
TYPE:         Web Application (PHP / MySQL)
DATE:         December 2025
=====================================================================

[1] PROJECT OVERVIEW
---------------------------------------------------------------------
TaskHub is a web-based collaboration tool designed to help student 
groups and teams manage tasks efficiently. It features a secure 
login system, role-based access control (Admin vs. Member), and 
real-time task tracking (Pending, In Progress, Completed).

[2] TECHNOLOGIES USED
---------------------------------------------------------------------
- Frontend: HTML5, Tailwind CSS (via CDN), FontAwesome Icons
- Backend:  PHP (Vanilla/Native)
- Database: MySQL (Relational DB)
- Server:   Apache (via XAMPP)

[3] DIRECTORY STRUCTURE
---------------------------------------------------------------------
/taskhub
  ├── /actions       # Logic files (Login, Register, Create Task)
  ├── /includes      # Database connection (db.php)
  ├── /css           # Stylesheets
  ├── /js            # JavaScript files
  ├── index.php      # Login Page
  ├── dashboard.php  # Main Dashboard (Changes based on Role)
  └── database.sql   # SQL file to setup database

[4] INSTALLATION STEPS
---------------------------------------------------------------------
1. Install XAMPP (Apache & MySQL).
2. Copy the 'taskhub' folder to your 'htdocs' directory.
   (Windows: C:\xampp\htdocs\taskhub)
   (Mac: /Applications/XAMPP/xamppfiles/htdocs/taskhub)
3. Open XAMPP Control Panel and Start 'Apache' and 'MySQL'.

[5] DATABASE SETUP
---------------------------------------------------------------------
1. Open your browser and go to: http://localhost/phpmyadmin
2. Click "New" and create a database named: taskhub_db
3. Click on the "Import" tab.
4. Choose the file 'database.sql' from the project folder.
5. Click "Go" to create the tables.

[6] LOGIN CREDENTIALS (TEST ACCOUNTS)
---------------------------------------------------------------------
The system comes with a pre-configured Admin account.

(A) ADMIN ACCOUNT (Full Access)
    Email:    admin@taskhub.com
    Password: password
    *Role: Can create tasks, view all tasks, see user stats.*

(B) MEMBER ACCOUNT (Restricted Access)
    *Please Register a new user on the Login page to test this.*
    *Role: Can only view assigned tasks and update status.*

[7] TROUBLESHOOTING
---------------------------------------------------------------------
- Issue: "Connection Failed"
  Fix: Open /includes/db.php and check if $user='root' and $pass=''.

- Issue: "404 Not Found"
  Fix: Ensure the folder name in htdocs is exactly 'taskhub'.

- Issue: CSS not loading
  Fix: Ensure you have an active internet connection (Tailwind loads 
  from the web).

=====================================================================
Developed by: 
=====================================================================