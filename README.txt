============================================================
  BURGE AL SALHIYA - POS SYSTEM
  Installation & Setup Guide
  Compatible: Windows 7 (32-bit & 64-bit) + XAMPP
============================================================

STEP 1 — INSTALL XAMPP
-----------------------
1. Download XAMPP for Windows from: https://www.apachefriends.org
   - For Windows 7 32-bit: Download "XAMPP for Windows" version 7.4.x (32-bit)
   - For Windows 7 64-bit: Download "XAMPP for Windows" version 7.4.x (64-bit)
2. Install XAMPP to default folder: C:\xampp
3. Open "XAMPP Control Panel"
4. Start "Apache" and "MySQL" — both should show green "Running"


STEP 2 — COPY POS FILES
------------------------
1. Copy the entire "pos" folder to:
   C:\xampp\htdocs\pos\

   Final structure:
   C:\xampp\htdocs\pos\
   ├── index.php
   ├── receipt.php
   ├── invoices.php
   ├── settings.php
   ├── api\
   │   ├── save_order.php
   │   ├── get_invoices.php
   │   └── menu.php
   ├── db\
   │   └── connect.php
   └── sql\
       └── setup.sql


STEP 3 — CREATE DATABASE
-------------------------
1. Open your web browser
2. Go to: http://localhost/phpmyadmin
3. Click "SQL" tab at the top
4. Open the file: pos\sql\setup.sql in Notepad
5. Copy ALL the text from setup.sql
6. Paste it into the phpMyAdmin SQL box
7. Click "Go" button
8. You should see "Your SQL query has been executed successfully"


STEP 4 — OPEN THE POS
----------------------
1. Open your web browser (Internet Explorer, Firefox, or Chrome)
2. Go to: http://localhost/pos/
3. The POS system will load with the full menu!


============================================================
  SCREENS & FEATURES
============================================================

POS SCREEN (index.php)
  - Category tabs at the top: Fatayir, Sandwiches, Meal Dishes, 
    Juices, Drinks, Pizza
  - Click any item to add to order
  - Pizza items show size selection (Small/Medium/Large)
  - Right panel shows current order
  - +/- buttons to change quantity
  - X button to remove an item
  - Enter cash paid — change is calculated automatically
  - "Save & Print Receipt" button saves order AND opens receipt
  - Keyboard: F2 = focus cash input, Ctrl+Enter = checkout

RECEIPT (receipt.php)
  - Thermal receipt style, ready to print
  - Click "Print Receipt" or use Ctrl+P
  - Includes invoice number, date, all items, total, cash, change

INVOICES (invoices.php)
  - Browse all saved invoices
  - Click "View" to see invoice details
  - Click "Print" to reprint any invoice
  - Today's count and revenue shown at top

SETTINGS (settings.php)
  - Change any item price
  - For pizza: change Small, Medium, Large prices separately
  - Changes save instantly to database


============================================================
  TROUBLESHOOTING
============================================================

Problem: "Database connection failed"
Solution: Make sure MySQL is running in XAMPP Control Panel

Problem: "Menu not loading"
Solution: 
  1. Check Apache is running in XAMPP
  2. Confirm setup.sql was imported correctly in phpMyAdmin
  3. Visit http://localhost/pos/api/menu.php — should show JSON data

Problem: Receipt not printing correctly
Solution: 
  1. In print dialog, set paper size to A4 or 80mm thermal
  2. Uncheck "Headers and Footers" in print settings
  3. Set margins to minimum/none

Problem: Page shows blank/error
Solution:
  1. Check that PHP files are in C:\xampp\htdocs\pos\
  2. Make sure Apache is running
  3. URL should be: http://localhost/pos/ (not file:///...)

To change database password:
  Edit: pos\db\connect.php
  Change DB_PASS to your MySQL password


============================================================
  DATABASE INFO
============================================================

Database Name: pos_salhiya
Tables:
  - categories   (6 categories)
  - items        (all menu items with prices)
  - invoices     (saved invoice headers)
  - invoice_items (saved invoice line items)

Default login (XAMPP default):
  Host: localhost
  User: root
  Password: (empty)


============================================================
  Tel: 9670 6364 | Burge Al Salhiya
============================================================
