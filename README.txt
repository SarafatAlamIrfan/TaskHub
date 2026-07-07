# TaskHub - Group Task Management System

TaskHub is a lightweight, web-based collaboration tool designed to help student groups and teams manage tasks efficiently. It features a secure authentication system, role-based access control, and dynamic task tracking across different development stages.

---

## 🚀 Features
* **Secure Authentication:** User registration and secure login system.
* **Role-Based Access Control (RBAC):** Distinct interfaces and permissions for **Admins** and **Members**.
* **Real-Time Task Tracking:** Monitor tasks seamlessly through status states: `Pending`, `In Progress`, and `Completed`.
* **Modern UI:** Responsive design built using Tailwind CSS and FontAwesome icons.

---

## 🛠️ Technologies Used
* **Frontend:** HTML5, Tailwind CSS (via CDN), FontAwesome Icons
* **Backend:** PHP (Vanilla/Native)
* **Database:** MySQL (Relational DB)
* **Server:** Apache (via XAMPP)

---

## 📂 Directory Structure
```text
/taskhub
  ├── /actions       # Logic files (Login, Register, Create Task)
  ├── /includes      # Database connection (db.php)
  ├── /css           # Stylesheets
  ├── /js            # JavaScript files
  ├── index.php      # Login Page
  └── dashboard.php  # Main Dashboard (Changes based on Role)

💻 Installation & Setup
1. Prerequisites
Ensure you have XAMPP (or a similar Apache/MySQL stack) installed on your system.

2. Project Setup
Clone or download this repository.

Move the taskhub folder into your local server's root directory (htdocs):

Windows: C:\xampp\htdocs\taskhub

Mac: /Applications/XAMPP/xamppfiles/htdocs/taskhub

Launch the XAMPP Control Panel and start both Apache and MySQL.

3. Database Configuration
Open your web browser and navigate to: http://localhost/phpmyadmin

Create a new database named taskhub_db.

Select the taskhub_db database, click on the Import tab.

Choose the database.sql file located in your project root directory.

Click Go (or Import) to execute the queries and build the tables.

🔑 Test Credentials
The database script initializes a default Admin account for testing purposes:

Admin Account (Full Access)
Email: admin@taskhub.com

Password: password

Permissions: Can create tasks, view all team tasks, and view global user statistics.

Member Account (Restricted Access)
To test a standard team member profile, please use the Register feature on the login page.

Permissions: Can view only their individually assigned tasks and update task completion status.

🛠️ Troubleshooting
"Connection Failed" Error: Open /includes/db.php and verify that your local database credentials match your server environment (Default XAMPP configuration is usually $user='root' and $pass='').

"404 Not Found" Error: Ensure that the repository folder name inside htdocs is written in lowercase exactly as taskhub.

Styles/CSS Layout Not Loading: This application utilizes Tailwind CSS via a Content Delivery Network (CDN). Make sure your development machine has an active internet connection to load the styles correctly.

📝 License
All Rights Reserved.
