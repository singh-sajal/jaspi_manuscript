# 📚 JASPI Manuscript Management System

A Laravel-based web platform designed for doctoral candidates to submit research manuscripts and receive structured staff ratings based on predefined academic standards. Built for the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI).
---

🌐 Live Preview [click here](https://manuscript.ilikasofttech.com/)

🚀 [Visit the website (Local)](http://127.0.0.1:8000)

---

## 🔧 Tech Stack

- **Backend:** Laravel 9, PHP 8+
- **Frontend:** Blade, Bootstrap 5, jQuery
- **Database:** MySQL
- **Version Control:** Git, GitHub

---

## 🧩 Features

- 📝 Manuscript submission with metadata & attachments  
- ✅ Role-based login for Authors, Reviewers, and Admin  
- ⭐ Reviewer ratings based on specific JASPI criteria  
- 📊 Dashboard with submission and review tracking  
- 🔒 Authentication, validation, and access control  
- 📥 Downloadable reports and review summaries  
- 🖼️ File upload with preview (PDF, DOCX, etc.)

---

## 🚀 Getting Started

### 1. Clone the repository
```bash
git clone https://github.com/YOUR_USERNAME/jaspi-manuscript-system.git
cd jaspi-manuscript-system
```

###  2. Install dependencies
```bash
composer install
```

### 3. Set up environment
#### Copy the example environment file and generate an application key:
```bash
cp .env.example .env
php artisan key:generate
```

#### Update your .env file with database credentials:

```env
DB_DATABASE=jaspi_db
DB_USERNAME=root
DB_PASSWORD=
```

### Migrate & Seed

```bash
php artisan migrate --seed
```

### Serve the Web Application

```bash
php artisan serve
```

Visit the application 👉 [http://127.0.0.1:8000](http://127.0.0.1:8000) 


