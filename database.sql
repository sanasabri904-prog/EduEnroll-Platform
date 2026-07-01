-- Create database

CREATE DATABASE IF NOT EXISTS enrollment_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE enrollment_db;

--  TABLE 1: users

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED         NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(120)         NOT NULL,
  email         VARCHAR(255)         NOT NULL UNIQUE,
  username      VARCHAR(80)          NOT NULL UNIQUE,
  password_hash VARCHAR(255)         NOT NULL,
  role          ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at    TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--  TABLE 2: courses

CREATE TABLE IF NOT EXISTS courses (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  course_code   VARCHAR(20)      NOT NULL UNIQUE,
  title         VARCHAR(200)     NOT NULL,
  instructor    VARCHAR(120)     NOT NULL,
  schedule      VARCHAR(120)     NOT NULL,
  credits       TINYINT UNSIGNED NOT NULL DEFAULT 3,
  capacity      INT UNSIGNED     NOT NULL DEFAULT 30,
  description   TEXT,
  created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--  TABLE 3: enrollments

CREATE TABLE IF NOT EXISTS enrollments (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  student_name    VARCHAR(120)  NOT NULL,
  student_email   VARCHAR(255)  NOT NULL,
  course_id       INT UNSIGNED  NOT NULL,
  enrollment_date DATE          NOT NULL DEFAULT (CURRENT_DATE),
  notes           TEXT,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  UNIQUE KEY no_duplicate (student_email, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account
INSERT INTO users (full_name, email, username, password_hash, role) VALUES (
  'Administrator',
  'admin@eduentroll.com',
  'admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);

--  SAMPLE COURSES
INSERT INTO courses (course_code, title, instructor, schedule, credits, capacity, description) VALUES
('CS101',  'Introduction to Programming',   'Dr. Ahmad Khalil', 'Mon/Wed 09:00-10:30',     3, 35, 'Fundamentals of programming using Python.'),
('CS201',  'Data Structures & Algorithms',  'Dr. Lara Nassar',  'Tue/Thu 11:00-12:30',     3, 30, 'Arrays, linked lists, stacks, queues and sorting algorithms.'),
('WD301',  'Web Development',               'Dr. Sami Haddad',  'Mon/Wed/Fri 13:00-14:00', 3, 25, 'HTML, CSS, JavaScript, PHP and MySQL.'),
('DB401',  'Database Management Systems',   'Dr. Rania Khoury', 'Tue/Thu 14:00-15:30',     3, 28, 'SQL, normalization, transactions and indexing.'),
('NET202', 'Computer Networks',             'Dr. Jad Moussa',   'Mon/Wed 15:00-16:30',     2, 30, 'OSI model, TCP/IP and routing protocols.');
