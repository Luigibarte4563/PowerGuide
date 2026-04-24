# ⚠️ If You Have Problems with Packages (Composer Setup Fix Guide)

This project uses PHP dependencies managed by **Composer**. If you encounter errors when installing or running packages, follow the troubleshooting guide below.

---

## 📦 Requirements

Make sure you have:

* PHP installed (XAMPP recommended) XAMPP
* Composer installed Composer
* Git installed (optional but recommended) Git

---

## 🚨 Common Problems & Fixes

### 1. ❌ Missing `vendor` folder or `autoload.php`

**Error:**

```
Failed opening required vendor/autoload.php
```

**Fix:**
Run inside your project folder:

```bash
composer install
```

---

### 2. ❌ ZIP extension missing

**Error:**

```
The zip extension and unzip/7z commands are both missing
```

**Fix:**

1. Open:

```
C:\xampp\php\php.ini
```

2. Enable ZIP:

```ini
extension=zip
```

3. Restart terminal and run:

```bash
php -m
```

Make sure `zip` appears.

---

### 3. ❌ Git “dubious ownership” error

**Error:**

```
detected dubious ownership in repository
```

**Fix:**

Run:

```bash
git config --global --add safe.directory C:/
```

---

### 4. ❌ Composer install stuck or incomplete

**Fix:**

Delete and reinstall dependencies:

```bash
rm -rf vendor composer.lock
composer install
```

---

### 5. ❌ Package not found in `vendor`

If a package like `vlucas/phpdotenv` is missing:

```bash
composer require vlucas/phpdotenv
```

---

## 📁 Correct Project Structure

```
PowerGuide/
│
├── vendor/
├── composer.json
├── composer.lock
├── public/
├── src/
└── .env
```

---

## 🔁 Recommended Fix Flow

If everything breaks:

```bash
composer clear-cache
rm -rf vendor composer.lock
composer install
```

---

## 💡 Tip

Always run Composer commands inside your project folder:

```
C:\xampp\htdocs\PowerGuide
```

NOT inside PHP system folders.

---

## 👨‍💻 Author Note

If you cloned this project, always run:

```bash
composer install
```

before starting the project.
