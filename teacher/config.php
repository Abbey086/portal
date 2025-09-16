<?php
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbPath = '../school_portal.db';

try {
    $conn = new SQLite3($dbPath);
    $conn->exec('PRAGMA foreign_keys = ON;');
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// -------------------- TABLE CREATION --------------------

// Schools Table
$conn->exec("CREATE TABLE IF NOT EXISTS schools (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL UNIQUE,
    school_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Classes Table
$conn->exec("CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (school_id) REFERENCES schools(school_id)
)");

// Teachers Table
$conn->exec("CREATE TABLE IF NOT EXISTS teachers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,

    FOREIGN KEY (school_id) REFERENCES schools(school_id)
)");

// Students Table
$conn->exec("CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    grade_level VARCHAR(50),
    FOREIGN KEY (school_id) REFERENCES schools(school_id)
)");

// Info Posts Table
$conn->exec("CREATE TABLE IF NOT EXISTS info_posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(school_id)
)");

// Info Replies Table
$conn->exec("CREATE TABLE IF NOT EXISTS info_replies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES info_posts(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
)");

// Info Likes Table
$conn->exec("CREATE TABLE IF NOT EXISTS info_likes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    school_id VARCHAR(50) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES info_posts(id),
    FOREIGN KEY (school_id) REFERENCES schools(school_id),
    UNIQUE(post_id, school_id)
)");

// Announcements Table
$conn->exec("CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
)");

// Announcement Recipients Table
$conn->exec("CREATE TABLE IF NOT EXISTS announcement_recipients (
    announcement_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    PRIMARY KEY (announcement_id, class_id),
    FOREIGN KEY (announcement_id) REFERENCES announcements(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
)");

// Assignments Table
$conn->exec("CREATE TABLE IF NOT EXISTS assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    instructions TEXT,
    due_date DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
)");

// Submissions Table
$conn->exec("CREATE TABLE IF NOT EXISTS submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    assignment_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    file_path TEXT NOT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
)");

// Notes Table
$conn->exec("CREATE TABLE IF NOT EXISTS notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    current_version INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
)");

// Note Versions Table
$conn->exec("CREATE TABLE IF NOT EXISTS note_versions (
    version_id INTEGER PRIMARY KEY AUTOINCREMENT,
    note_id INTEGER NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    version_number INTEGER NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id)
)");

// Office Hours Table
$conn->exec("CREATE TABLE IF NOT EXISTS office_hours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id INTEGER NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
)");

// Bookings Table
$conn->exec("CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    office_hour_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (office_hour_id) REFERENCES office_hours(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
)");

// Timetable Table
$conn->exec("CREATE TABLE IF NOT EXISTS timetable (id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL,
    class_id INTEGER NOT NULL,
    day INTEGER NOT NULL,             -- 1=Monday, 2=Tuesday, etc.
    time_slot INTEGER NOT NULL,       -- 1=Period 1, etc.
    subject TEXT NOT NULL,
    teacher TEXT,
    FOREIGN KEY (school_id) REFERENCES schools(school_id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
)");
?>