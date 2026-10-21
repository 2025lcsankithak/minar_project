CREATE DATABASE IF NOT EXISTS mentorship_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mentorship_portal;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS meetings,request_courses,course_requests,mentor_assignments,courses,users;
SET FOREIGN_KEY_CHECKS=1;
CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, role ENUM('admin','mentor','student') NOT NULL, name VARCHAR(100) NOT NULL, username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(120) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, department VARCHAR(100), section VARCHAR(20), semester TINYINT, dob DATE NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE courses (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(20) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL, credits TINYINT NOT NULL, semester TINYINT NOT NULL, type ENUM('Core','Open Elective') NOT NULL DEFAULT 'Core');
CREATE TABLE mentor_assignments (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL UNIQUE, mentor_id INT NOT NULL, FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(mentor_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE course_requests (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, total_credits TINYINT NOT NULL, receipt_path VARCHAR(255), status ENUM('pending','approved','rejected') DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE request_courses (request_id INT NOT NULL, course_id INT NOT NULL, PRIMARY KEY(request_id,course_id), FOREIGN KEY(request_id) REFERENCES course_requests(id) ON DELETE CASCADE, FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE);
CREATE TABLE meetings (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, mentor_id INT NOT NULL, meeting_at DATETIME NOT NULL, mode ENUM('Online','In person') NOT NULL, notes TEXT, status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled', FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(mentor_id) REFERENCES users(id) ON DELETE CASCADE);
INSERT INTO users(role,name,username,email,password,department,section,semester) VALUES
('admin','System Administrator','admin','admin@minar.edu','$2y$10$tAztD8zO236L5w3vVph7QeB4V0VUJCpGDCXmO7pazyKnFbn9Nzpc6','Administration',NULL,NULL);
INSERT INTO courses(code,name,credits,semester,type) VALUES ('CS501','Advanced Algorithms',3,5,'Core'),('CS502','Database Management Systems',3,5,'Core'),('CS503','Operating Systems',3,5,'Core'),('CS504','Software Engineering',3,5,'Core'),('OE501','Introduction to AI',3,5,'Open Elective'),('OE502','Cloud Computing',3,5,'Open Elective');
