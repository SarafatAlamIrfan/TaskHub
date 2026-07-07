# 📋 TaskHub - Group Task Management System

TaskHub is a lightweight, web-based collaboration tool designed to help student groups and teams manage tasks efficiently. It features a secure authentication system, role-based access control, and dynamic task tracking across different development stages.

---

## 🚀 Features

* **Secure Authentication:** Built-in user registration and secure login framework.
* **Role-Based Access Control (RBAC):** Distinct interfaces and unique permissions tailored for both **Admins** and **Members**.
* **Real-Time Task Tracking:** Seamlessly monitor progress through dedicated workflow status states: `Pending`, `In Progress`, and `Completed`.
* **Modern UI/UX:** Fully responsive layout built using Tailwind CSS utility classes and clean FontAwesome iconography.

---

## 🛠️ Technologies Used

* **Frontend:** HTML5, Tailwind CSS (via CDN), FontAwesome Icons
* **Backend:** PHP (Vanilla / Native)
* **Database:** MySQL (Relational DB)
* **Server Stack:** Apache & MySQL (via XAMPP)

---

## 📂 Directory Structure

```text
/taskhub
  ├── /actions       # Logic processing files (Login, Register, Create Task)
  ├── /includes      # Database connectivity configurations (db.php)
  ├── /css           # Core stylesheets
  ├── /js            # JavaScript functionality scripts
  ├── index.php      # Application gateway / Login Page
  └── dashboard.php  # Main Dashboard (Dynamically changes view based on User Role)

```

## 💻 Installation & Setup

### 1. Prerequisites

Ensure you have **XAMPP** (or an equivalent Apache/MySQL distribution local server environment) installed on your system.

### 2. Project Setup

1. **Clone or download** this repository to your local machine.
2. **Move** the entire `taskhub` folder into your local web server's root directory (`htdocs`):
* **Windows:** `C:\xampp\htdocs\taskhub`
* **Mac:** `/Applications/XAMPP/xamppfiles/htdocs/taskhub`


3. Launch your **XAMPP Control Panel** and explicitly start both the **Apache** and **MySQL** modules.

### 3. Database Configuration

1. Open your web browser and navigate to the database management panel: `http://localhost/phpmyadmin`
2. Create a brand new, empty database named **`taskhub_db`**.
3. Select your newly created `taskhub_db` from the left sidebar, and click on the **Import** tab in the top navigation bar.
4. Click **Choose File** and select the `database.sql` file located directly in the root directory of this project.
5. Scroll down and click **Go** (or **Import**) to run the initialization scripts and build out the system tables.

---

## 🔑 Test Credentials

The database import file automatically initializes a default platform administrator account for testing:

### 🌟 Admin Account (Full Access)

* **Email:** `admin@taskhub.com`
* **Password:** `password`
* **Permissions:** Absolute control. Can generate new project tasks, oversee all assigned group tasks, and monitor global member statistics.

### 👥 Member Account (Restricted Access)

* **How to test:** Navigate to the login page and use the built-in **Register** link to create a fresh team member profile.
* **Permissions:** Scoped view. Members can only view tasks explicitly assigned to them and update their respective implementation status.

---

## 🛠️ Troubleshooting

> 💡 **"Connection Failed" Error**
> Open up `/includes/db.php` and verify that your local environment credentials accurately match. By default, standard local XAMPP environments utilize `$user = 'root'` and an empty password `$pass = ''`.

> 💡 **"404 Not Found" Error**
> Double-check your local server path. Ensure that the root repository folder nested inside your server's `htdocs` directory is written exactly in all-lowercase letters as `taskhub`.

> 💡 **Styles / Broken Layout UI**
> This application relies heavily on Tailwind CSS fetched via an online Content Delivery Network (CDN). Please guarantee your active development workstation maintains a stable internet connection to render the user interface elements properly.

---

## 📝 License

Distributed under the **All Rights Reserved** license framework. Feel free to clone and modify for educational or non-commercial development group assignments.

```

```
