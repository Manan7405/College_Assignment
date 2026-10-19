-- ---------------------------------------------------------------
-- students table (used by Questions 2, 3 and 6)
-- OPTIONAL: the PHP programs create this table automatically.
-- Import this file only if you want the sample rows without
-- running the Question 2 importer first.
-- ---------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS practical_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical_db;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    course     VARCHAR(100) NOT NULL,
    marks      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERT IGNORE skips rows whose student_id already exists
INSERT IGNORE INTO students (student_id, name, email, course, marks) VALUES
(101, 'Asha Patel',     'asha.patel@example.com',   'BCA',                       85.50),
(102, 'Ravi D''Souza',  'ravi.dsouza@example.com',  'MCA',                       72.00),
(103, 'Meera Desai',    'meera.desai@example.com',  'BCA',                       91.25),
(104, 'Karan Mehta',    'karan.mehta@example.com',  'B.Sc. Physics & Maths',     64.75),
(105, 'Priya Joshi',    'priya.joshi@example.com',  'MCA',                       78.00),
(106, 'Neha Shah',      'neha.shah@example.com',    'B.Com',                     88.40);
