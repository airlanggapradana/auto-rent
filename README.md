# 🚗 Auto Rent

Auto Rent is a modern car rental web application designed to simplify the vehicle booking process for users and rental management for administrators. This project provides a responsive interface, vehicle catalog management, booking workflows, and a scalable foundation for future development.

## ✨ Features

* 🔐 User authentication and authorization
* 🚘 Browse available rental cars
* 📅 Car booking and reservation system
* 💳 Rental price calculation
* 📱 Responsive UI for desktop and mobile
* 🛠️ Admin dashboard for managing cars and bookings
* 🔎 Search and filtering functionality
* ⚡ Fast and modern frontend experience

---

## 🛠️ Tech Stack

### Frontend

* HTML5
* CSS3
* JavaScript

### Backend

* PHP Native

### Database

* Postgresql

### Development Tools

* Laragon
* Git & GitHub

---

## 📂 Project Structure

```bash
rental-mobil/
├── database.sql      # Database schema
├── index.php         # Main page / dashboard
├── login.php         # Login page
├── logout.php        # Logout process
├── tambah.php        # Add rental data
├── edit.php          # Edit rental data
├── hapus.php         # Delete rental data
├── detail.php        # Rental detail page
├── koneksi.php       # Database connection configuration
├── script.js         # JavaScript functionality
├── style.css         # Main styling file
└── README.md
```

---

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/airlanggapradana/auto-rent.git
cd auto-rent
```

### 2. Setup Database

1. Open phpMyAdmin
2. Create a new database
3. Import the `database.sql` file

---

## 🗄️ Database Configuration

Edit the `koneksi.php` file according to your local database configuration.

```php
<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "rental_mobil";

$conn = mysqli_connect($host, $user, $password, $database);
?>
```

---

## ▶️ Running the Project

1. Move the project folder into:

```bash
htdocs/        # XAMPP
```

or

```bash
www/           # Laragon
```

2. Start Apache and MySQL
3. Open browser and access:

```bash
http://localhost/rental-mobil
```

---

## 📸 Preview

<img width="1896" height="985" alt="image" src="https://github.com/user-attachments/assets/d257caa2-8f03-4ecc-8ea4-ca2d18804b3d" />
<img width="1882" height="979" alt="image" src="https://github.com/user-attachments/assets/1aeb61cb-5019-4529-9f79-2c9c988530b5" />

---

## 📌 Main Functionalities

### User Side

* Register and login
* Browse available cars
* Book vehicles
* View booking history
* Manage profile

### Admin Side

* Manage vehicle data
* Manage bookings
* Update rental status
* Monitor users

---

## 🚀 Future Improvements

* Online payment gateway integration
* Real-time vehicle availability
* Email notifications
* Google Maps integration
* AI-powered vehicle recommendation
* Multi-language support

---

## 🧪 CRUD Features

* ✅ Add rental data
* ✅ View rental data
* ✅ Edit rental data
* ✅ Delete rental data
* ✅ Authentication login/logout
* ✅ Responsive interface

---

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository
2. Create a new branch
3. Commit your changes
4. Push to your branch
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Author

Created by [Airlangga Pradana GitHub](https://github.com/airlanggapradana?utm_source=chatgpt.com)
