# PHP Practical Assignment (6 Questions)

Core PHP 8 + MySQL (PDO, prepared statements) + JavaScript (fetch/AJAX) + Bootstrap 5 + Node.js/Express.
No frameworks. Everything runs locally under XAMPP on Windows.

```
PHP_Practical_Assignment/
├── README.md                      <- this file
├── common/                        <- shared DB settings (db_config.php) and helper (db_connect.php)
├── database/
│   ├── shopping_cart.sql          <- Question 1 tables + sample data
│   ├── students.sql               <- optional sample rows for the students table (Q2, Q3, Q6)
│   └── generate_hash.php          <- makes a password hash (for the admin password)
├── question1_shopping_cart/       <- config, includes, admin, ajax, assets, uploads, index/login/register/cart ...
├── question2_xml_to_mysql/        <- students.xml + import_students.php
├── question3_mysql_to_xml/        <- export_students.php
├── question4_directory_listing/   <- index.php + files/ (sample files)
├── question5_express_api_to_php/  <- express_api/ (Node) + php_client/ (PHP)
└── question6_php_api_to_express/  <- php_api/ (PHP) + express_client/ (Node)
```

> Keep the folder structure unchanged. Questions 1, 2, 3 and 6 read the database settings from `common/`.

---

## 0. One-time setup

1. Install **XAMPP** (PHP 8+) and **Node.js 18 or newer** (needed for `fetch` in Question 6).
2. Copy the whole `PHP_Practical_Assignment` folder to `C:\xampp\htdocs\`.
   Result: `C:\xampp\htdocs\PHP_Practical_Assignment\question1_shopping_cart\...`
3. Open the **XAMPP Control Panel** and **Start Apache** and **Start MySQL**.
4. Database settings are in `common/db_config.php` (defaults: host `localhost`, port `3306`, user `root`, empty password, database `practical_db`). Change them only if yours differ.
5. Import the Question 1 database: open `http://localhost/phpmyadmin` -> **Import** tab -> choose `database/shopping_cart.sql` -> **Go**.
   (This creates the database `practical_db`. Re-importing resets the shop tables.)
6. Enable the **GD** extension (for image resizing in Q1) and **cURL** (Q5): XAMPP Control Panel -> Apache **Config** -> `PHP (php.ini)`. Make sure these lines have no `;` in front, then restart Apache:
   ```
   extension=gd
   extension=curl
   extension=mbstring
   extension=fileinfo
   ```
   (They are usually enabled by default in XAMPP.)

Base URL used below (if Apache runs on port 8080, write `localhost:8080`):
`http://localhost/PHP_Practical_Assignment/`

---

## Question 1 - Shopping Cart

| Part | URL |
|---|---|
| User site | `http://localhost/PHP_Practical_Assignment/question1_shopping_cart/index.php` |
| Admin login | `http://localhost/PHP_Practical_Assignment/question1_shopping_cart/admin/login.php` |

**Admin account:** username `admin`, password `admin123`.
**Users:** register your own on the Register page (there is no default user).

**How the admin password is stored.** The `admins` table keeps only a *bcrypt hash* made by PHP's `password_hash()`. The hash for `admin123` is already in `database/shopping_cart.sql`. To create a different one run
`php database/generate_hash.php MyNewPassword` (or open `database/generate_hash.php?pw=MyNewPassword` in the browser), then in phpMyAdmin run
`UPDATE admins SET password_hash = '<paste hash>' WHERE username = 'admin';`

**What to try (expected results)**
1. User site: click *Garments / Mobiles / Laptops* -> 3 products appear without a page reload. *All Products* (9 products, 6 per page) shows pagination `Previous 1 2 Next`; switching category resets to page 1.
2. Register (solve the arithmetic CAPTCHA) -> redirected to Login -> log in with username *or* email.
3. *Add to cart* (needs login; if not logged in you are sent to the login page) -> cart badge increases. Cart page: `+`/`-`, Remove, Clear, grand total.
4. Admin: login -> Dashboard -> Categories (add / edit / delete; a category that still has products cannot be deleted) -> Products (add / edit / delete with picture upload). Upload a JPEG/PNG/WebP up to 2 MB; it is resized to max 800 px and gets a unique name in `uploads/products/`. A `.txt` file renamed to `.jpg`, or a file over 2 MB, is rejected with a message.
5. Logout (admin and user).

**Short explanation for the exam**
- `includes/functions.php`: sessions, `e()` (escape output), flash messages, CSRF token, login checks, cart helpers.
- Login: look the user up with a prepared statement, check with `password_verify()`, call `session_regenerate_id()`, store the id in `$_SESSION`. Admin pages call `require_admin()` first.
- Passwords are stored with `password_hash()`, never plain text.
- AJAX + pagination: `assets/js/products.js` calls `ajax/get_products.php?category_id=..&page=..` using `fetch()`. The PHP script counts the rows, calculates total pages and returns one page using `LIMIT/OFFSET` as JSON; JavaScript draws the cards and the Previous / numbers / Next buttons.
- CAPTCHA: the server creates e.g. `7 + 4`, keeps `11` in `$_SESSION['captcha_answer']`, and compares it with the submitted answer.
- Image upload: check error code, size, real image type (`getimagesize`), then GD resize keeping aspect ratio, unique file name, saved in `uploads/products/` (script execution is blocked there by `.htaccess`).
- Cart: `$_SESSION['cart'] = [product_id => quantity]`; prices are always read from the database.

---

## Question 2 - Read `students.xml` and store it in MySQL

Open `http://localhost/PHP_Practical_Assignment/question2_xml_to_mysql/import_students.php` and click **Import now**.

- Expected: "**6** record(s) imported successfully out of 6". Click again: 0 imported, 6 duplicates skipped.
- The `students` table (and the database) is created automatically if missing.
- **Duplicate IDs:** `student_id` is the PRIMARY KEY, so an existing id is **skipped** (old row kept) and reported. This makes re-running safe.
- Handles: missing file, malformed XML (shows line numbers), wrong root element, invalid email/marks (record skipped and listed), database errors.
- The drop-down also lets you import `students_export.xml` created by Question 3.

**Exam explanation:** `simplexml_load_file()` turns the XML into objects; `foreach ($xml->student as $student)` loops over the records; one prepared `INSERT` is executed for each record; a MySQL error code 1062 means duplicate key.

## Question 3 - Export the `students` table to `students_export.xml`

Open `http://localhost/PHP_Practical_Assignment/question3_mysql_to_xml/export_students.php` and click **Export now** (run Question 2 first so the table has data).

- Creates `question3_mysql_to_xml/students_export.xml` with the root `<students>` and one `<student>` per row, using the **same element names** as Question 2's input, so it can be imported again (choose it in Question 2's drop-down).
- Special characters (`&`, `<`, `>`, quotes) are escaped automatically because values are added with `createTextNode()`.

**Exam explanation:** `DOMDocument` builds the tree (`createElement`, `appendChild`), `formatOutput` indents it, `save()` writes the file.

## Question 4 - List files of a directory

Open `http://localhost/PHP_Practical_Assignment/question4_directory_listing/index.php`.

- Lists the files in `question4_directory_listing/files/` (7 sample files) with name, extension, size (B/KB/MB) and last modified date.
- Empty or missing folder -> a friendly message. The path is fixed in the code, so visitors cannot browse other folders; hidden files and sub-folders are skipped.
- Try it: add or delete files in `files/` and reload.

**Exam explanation:** `scandir()` reads names, `is_file()` filters files, `pathinfo()` gives the extension, `filesize()` and `filemtime()` give size and date.

## Question 5 - PHP calls an Express REST API

> **The Express server must be running before the PHP client can show data.** If it is not running, the PHP page shows a "Cannot reach the Express API" message (this is the expected error handling).

1. Open a Command Prompt:
   ```
   cd C:\xampp\htdocs\PHP_Practical_Assignment\question5_express_api_to_php\express_api
   npm install
   npm start
   ```
   Leave this window open. You should see `Students API is running on http://localhost:3000`.
2. Check the API in a browser: `http://localhost:3000/api/students` and `http://localhost:3000/api/students/102` (a missing id such as `/api/students/999` gives a JSON 404, `/api/students/abc` gives a JSON 400).
3. Open the PHP client: `http://localhost/PHP_Practical_Assignment/question5_express_api_to_php/php_client/index.php`
   -> a table with 6 students. Use the search box to fetch one student (`?id=102`).
4. Different port? Change `PORT` (environment variable) or `API_BASE_URL` at the top of `php_client/index.php`.

**Exam explanation:** Express routes return JSON with proper status codes (200/400/404/500). PHP uses `curl_init`, `curl_setopt_array`, `curl_exec`; checks connection errors, the HTTP status, then `json_decode(..., JSON_THROW_ON_ERROR)` and prints a table.

## Question 6 - Express calls a PHP REST API

1. Make sure Apache and MySQL are running and the `students` table has data (run Question 2, or import `database/students.sql`).
2. Test the PHP API in a browser:
   `http://localhost/PHP_Practical_Assignment/question6_php_api_to_express/php_api/students.php`
   (one student: add `?id=102`). Other methods such as POST return **405** with an `Allow: GET` header.
3. Start the Express client:
   ```
   cd C:\xampp\htdocs\PHP_Practical_Assignment\question6_php_api_to_express\express_client
   npm install
   npm start
   ```
4. Open `http://localhost:3001/` (HTML table) or `http://localhost:3001/json` (raw JSON).

**PHP endpoint URL configured in the client** (`express_client/server.js`):
`http://localhost/PHP_Practical_Assignment/question6_php_api_to_express/php_api/students.php`

**If your XAMPP Apache port is not 80** (for example 8080), set the URL before starting:
```
set PHP_API_URL=http://localhost:8080/PHP_Practical_Assignment/question6_php_api_to_express/php_api/students.php
npm start
```
or edit the default value of `PHP_API_URL` in `server.js`.

**Exam explanation:** the PHP script sets `Content-Type: application/json`, uses prepared statements, and returns status codes 200/400/404/405/500. The Express app uses `fetch` with `async/await` and `try/catch`, shows a clear message when Apache is down (502) or the PHP API answers with an error.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| "Database connection failed" (Q1) | Start MySQL in XAMPP, import `database/shopping_cart.sql`, check `common/db_config.php`. |
| Q2/Q3/Q6 "Database error" | MySQL not running, or wrong user/password/port in `common/db_config.php`. |
| MySQL uses another port (e.g. 3307) | Set `DB_PORT` in `common/db_config.php`. |
| Admin login fails | Use `admin` / `admin123` after importing the SQL file; or create a new hash with `database/generate_hash.php` and update the `admins` table. |
| "GD extension is not enabled" warning | Enable `extension=gd` in php.ini, restart Apache. Uploads still work but are not resized. |
| WebP upload rejected | Your GD build lacks WebP support; use JPEG/PNG. |
| Upload fails for big files | Raise `upload_max_filesize` / `post_max_size` in php.ini (the app's own limit is 2 MB). |
| Q5 "Cannot reach the Express API" | Start `npm start` in `express_api` first; check the port (3000). |
| `npm install` errors | Check `node -v` (18+) and your internet connection (npm downloads Express). |
| `'npm' is not recognized` | Install Node.js and reopen the Command Prompt. |
| Q6 "Cannot connect to the PHP API" / "did not return valid JSON" | Apache not running, wrong port, or wrong folder name in `PHP_API_URL`. Open the URL in a browser to confirm it shows JSON. |
| Port 3000/3001 already in use | `set PORT=4000` then `npm start` (and update `API_BASE_URL` in the Q5 PHP client). |
| Cart / login lost | Browser blocks cookies for `localhost`; allow cookies. |

---

## What was tested (honest summary)

The project was assembled in an environment that has **no PHP interpreter, no MySQL server, and no internet access for `npm install`**. Therefore:

**Checked**
- Folder structure and that every file listed above exists; all `require` paths resolve to existing files.
- JavaScript syntax: `node --check` passed for both Express `server.js` files.
- A script-based structural check of all PHP files (balanced brackets and matching `if/endif`, `foreach/endforeach`) passed. This is **not** a real PHP syntax check.
- `students.xml` is well-formed; its 6 records are identical to `database/students.sql` and to the Express sample data.
- The bcrypt hash of `admin123` in `shopping_cart.sql` verifies correctly against the password.
- Table/column names used in PHP match the SQL files; the AJAX URLs and the element ids used by `products.js` match the files and `index.php`.
- All 9 sample product pictures referenced by the SQL exist.

**Not tested (please run once before your practical)**
- Running the PHP pages in Apache, the MySQL queries, the file upload / GD resizing, and the browser behaviour (AJAX, pagination).
- `npm install` and starting the two Express servers.

If something does not work on your machine, follow the Troubleshooting table; each page shows a readable error message instead of a blank screen.

## Notes
- Bootstrap 5.3.2 (a Bootswatch "Cerulean" build) is included locally in `question1_shopping_cart/assets/bootstrap/`, so no internet or CDN is needed. `node_modules` is not included; `npm install` creates it.
- Security basics used: prepared statements, `password_hash/verify`, output escaping, CSRF tokens on forms, session id regeneration, upload validation, no script execution in `uploads/`.
