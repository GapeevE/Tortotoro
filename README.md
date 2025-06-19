# Tortotoro - Pastry Shop Management System

## Overview

Tortotoro is a full-stack web application for managing pastry shop operations. The system includes four specialized interfaces for different roles: login, administrator, waiter, and chef. The solution streamlines employee management, shift scheduling, and order processing in a bakery environment.

## Key Features

- **Responsive Design:** Fully adaptive interface for all device sizes
- **Dynamic Content Handling:** Real-time AJAX fetching and rendering with search functionality
- **Secure Database Connection:** PDO-based database interactions with prepared statements
- **Error Monitoring:** Cookie-based error logging with visual badge notifications
- **Role-Based Access Control:** Granular permissions for different staff roles
- **Real-Time Updates:** Instant status changes without page reloads

## Functional Modules

### 1. Login Page

![Login Page](assets/login.png)

- Secure authentication system
- Role-based access control
- Session management

### 2. Admin Panel (admin.php)

![Admin Panel](assets/admin.png)

- Employee Management: Create, delete, and search employees
- Shift Management:
        - Create new shifts
        - Edit shift details (staff assignment, time adjustments)
        - Update shift status (active/closed)
        - Track shift performance

### 3. Waiter Panel (waiter.php)

![Waiter Panel](assets/waiter.png)

- Create new orders with multiple items
- Modify existing orders
- Update order status (pending, in-progress, completed)
- Table management system

### 4. Chief Panel (chief.php)

![Chief Panel](assets/chief.png)

- View pending orders
- Update cooking status (preparing, ready to serve)
- Priority order management
- Kitchen display system

## Tech Stack

The following technologies were used to build this project:

- <img src="./assets/icons/HTML5.png" height="24"> HTML5

- <img src="./assets/icons/CSS3.png" height="24"> CSS3

- <img src="./assets/icons/JS.png" height="24"> JavaScript

- <img src="./assets/icons/Bootstrap.png" height="24"> Bootstrap

- <img src="./assets/icons/MySQL.png" height="24"> MySQL

- <img src="./assets/icons/PHP.png" height="24"> PHP

## Installation Guide

Follow these steps to set up Tortotoro on your local server:

### Prerequisites

- Web server (Apache recommended)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- [OpenServer](https://ospanel.io) (recommended for Windows) or similar stack

### Step-by-Step Installation

1. **Clone the repository** 

```

git clone https://github.com/GapeevE/Tortotoro.git

```

2. **Set up the database** 
    - Create a new MySQL database named `tortotoro`
    - Import the database structure from `tortotoro.sql`:

    ```

        mysql -u [username] -p tortotoro < tortotoro.sql

    ```

3. **Configure database connection:** 

Edit `db_connect.php` with your credentials:

```php

<?php

$host = 'localhost'; // your host

$dbname = 'tortotoro'; // your database name

$username = 'root'; // your database username

$password = ''; // your database password


```
4. **Start your web server** (OpenServer, XAMPP, WAMP, etc.)

5. **Access the application:** 

Open your browser and navigate to:

```

http://localhost/tortotoro/

```

### Default User Credentials

Use these credentials for initial access:

|Role|Username|Password|
|---|---|---|
|Admin|cheban|Admin123!|
|Waiter|shakilonil|Offick123!|
|Chief|olga|Povar123!|

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.