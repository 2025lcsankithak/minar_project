# Minar Mentorship Portal

A role-based PHP and MySQL web application for coordinating student course selections and mentoring. It provides separate dashboards for administrators, mentors, and students, with a shared login/session system.

## Features

- **Admin:** manage student and mentor accounts, edit profiles, assign mentors by section and semester, manage courses, review course requests, and import courses from CSV.
- **Mentor:** view assigned students and their academic information, review course selections and supporting document links, publish announcements, and manage meeting availability and requests.
- **Student:** update profile details and password, view academic information and mentor details, select semester courses and submit a supporting document link, read mentor announcements, and request/confirm meetings.
- **Meeting workflow:** students request an available time; mentors approve or decline; students confirm an approved meeting.
- Responsive shared styling for the sign-in pages and dashboards.

## Requirements

- XAMPP (or another local Apache, PHP, and MySQL/MariaDB setup)
- PHP 8.0 or later with PDO MySQL enabled
- MySQL or MariaDB

## Setup with XAMPP

1. Place this project in the XAMPP web root, for example `C:\xampp\htdocs\minar_project`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Import [`database/mentorship_portal.sql`](database/mentorship_portal.sql) using phpMyAdmin or the MySQL command line. The dump creates and populates the `mentorship_portal` database.
4. Check the database constants and `BASE_URL` in [`config.php`](config.php). The defaults are database `mentorship_portal`, host `localhost`, user `root`, an empty password, and base URL `/minar_project`.
5. Visit `http://localhost/minar_project/` and choose a role to sign in.

> **Database import warning:** the SQL dump drops and recreates the application's tables before inserting its snapshot data. Do not import it over data you need to keep.

The SQL dump contains seeded sample accounts with hashed passwords. It does not document their plaintext passwords; use credentials configured for your own environment or create/reset accounts through the application/database as appropriate.

## Application flow

The home page links to role-specific sign-in pages under `admin/`, `mentor/`, and `student/`. Each page uses the shared `login-page.php` template. Successful sign-in is handled by `login.php`, which stores the user and role in a PHP session. Dashboard pages enforce their required role, and `logout.php` ends the session.

Students can submit one course request per semester, including a valid shareable `http` or `https` document URL. Their assigned mentor can review the request; administrators can also manage its status. Mentors can post open meeting times for assigned students. A meeting request holds that slot while pending or approved; approved requests become confirmed after the student confirms them.

## Course CSV import

On the Admin **Courses** screen, upload a CSV with these columns:

```csv
code,name,credits,semester,type
CS501,Advanced Algorithms,3,5,Core
OE501,Introduction to AI,3,5,Open Elective
```

The header row is optional. Semester must be between 1 and 8. Course type is `Core` or `Open Elective`. A course with an existing code is updated by the import.

## Project structure

| Path | Purpose |
| --- | --- |
| `index.php` | Public landing page |
| `admin/`, `mentor/`, `student/` | Role-specific sign-in entry points |
| `admin.php` | Admin dashboard and account, assignment, course, and request actions |
| `mentor.php` | Mentor dashboard, course review, announcements, and meeting scheduling |
| `student.php` | Student dashboard, course selection, profile, and meetings |
| `auth.php`, `login.php`, `logout.php` | Authentication and session handling |
| `config.php` | Database connection, base URL, and shared helpers |
| `layout.php`, `login-page.php` | Shared dashboard and sign-in templates |
| `assets/style.css`, `assets/login.css` | Application and sign-in styles |
| `database/mentorship_portal.sql` | Database schema and seeded sample data |

## Configuration and deployment

`config.php` currently contains local development database settings. Change these values for your environment, protect database credentials, and use HTTPS and appropriate PHP/database security settings before exposing the application beyond a trusted local development setup.
