# Minar Mentorship Portal

This is a small PHP and MySQL portal for managing student mentorship, course selection, and meetings.

## Run the project

1. Start Apache and MySQL in XAMPP.
2. Import `database/mentorship_portal.sql` into phpMyAdmin or MySQL.
3. Confirm the database settings in `config.php`. The default setup uses MySQL user `root` with no password.
4. Open `http://localhost/minar_project/` in the browser.

The SQL file creates sample users, courses, requests, assignments, and a meeting. The sample account usernames are in that SQL file.

## How the portal works

1. `index.php` is the home page. It shows Admin, Mentor, and Student sign-in links.
2. Each role link opens its own folder's `index.php` sign-in page.
3. The sign-in form posts to `login.php`.
4. `login.php` verifies the account and stores the logged-in user in the PHP session.
5. The dashboard checks that session before showing private information.
6. `logout.php` clears the session and returns to the Admin sign-in page.

## Main files

| File | What it does |
| --- | --- |
| `index.php` | Public landing page and links to the three portals. |
| `config.php` | Database connection settings, base URL, HTML escaping helper, and count helper. |
| `auth.php` | Starts the session, checks the user role, and performs login verification. |
| `login.php` | Receives sign-in form data and redirects the user after login. |
| `logout.php` | Deletes session data and redirects to `admin/index.php`. |
| `login-page.php` | Shared sign-in page template used by every role. |
| `layout.php` | Shared dashboard sidebar, header, profile display, and logout link. |
| `admin.php` | Admin dashboard and focused People, Courses, Requests, and Assignments screens. |
| `mentor.php` | Mentor dashboard, assigned students, request list, and meeting scheduler. |
| `student.php` | Student dashboard, course-request form, mentor details, and meetings. |

## Sign-in page files

| File | What it does |
| --- | --- |
| `admin/index.php` | Sets the Admin sign-in text, then loads `login-page.php`. |
| `mentor/index.php` | Sets the Mentor sign-in text, then loads `login-page.php`. |
| `student/index.php` | Sets the Student sign-in text, then loads `login-page.php`. |

## Assets and database

| File | What it does |
| --- | --- |
| `assets/style.css` | Shared colours, sidebar, forms, cards, tables, buttons, and responsive layout. |
| `assets/login.css` | Extra styling only for the sign-in screen. |
| `database/mentorship_portal.sql` | Creates all database tables and inserts demo data. |

## Admin navigation

The Admin dashboard is intentionally small. Use the sidebar to open one area at a time:

- **People**: view and edit users.
- **Courses**: add, edit, or upload course data using a CSV file.
- **Requests**: approve, reject, or return a course request to pending.
- **Assignments**: assign mentors to students who are still pending.

## Course CSV format

The Courses page accepts a CSV file with these columns:

```text
code,name,credits,semester,type
CS101,Introduction to Programming,3,1,Core
OE201,Design Thinking,2,2,Open Elective
```

Semester values must be from **1 through 8**.
