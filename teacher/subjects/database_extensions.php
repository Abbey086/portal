<?php
require_once 'config.php';

// Subjects Table - Predefined and custom subjects
$conn->exec("CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id VARCHAR(50) NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20),
    description TEXT,
    color_theme VARCHAR(7) DEFAULT '#667eea',
    icon_class VARCHAR(50) DEFAULT 'fas fa-book',
    is_default BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(school_id)
)");

// Teacher Subjects - Many-to-many relationship
$conn->exec("CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    UNIQUE(teacher_id, subject_id)
)");

// Student Subjects - Many-to-many relationship
$conn->exec("CREATE TABLE IF NOT EXISTS student_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    UNIQUE(student_id, subject_id)
)");

// Insert default subjects for all schools
function insertDefaultSubjects($conn, $school_id) {
    $defaultSubjects = [
        ['Mathematics', 'MATH', 'Mathematical concepts and problem solving', '#3b82f6', 'fas fa-calculator'],
        ['Physics', 'PHYS', 'Study of matter, energy, and their interactions', '#f59e0b', 'fas fa-atom'],
        ['Chemistry', 'CHEM', 'Study of substances and their properties', '#ef4444', 'fas fa-flask'],
        ['Biology', 'BIO', 'Study of living organisms', '#10b981', 'fas fa-dna'],
        ['English', 'ENG', 'Language arts and literature', '#8b5cf6', 'fas fa-book-open'],
        ['History', 'HIST', 'Study of past events', '#f97316', 'fas fa-landmark'],
        ['Geography', 'GEO', 'Study of Earth and its features', '#06b6d4', 'fas fa-globe'],
        ['Computer Science', 'CS', 'Programming and computational thinking', '#6366f1', 'fas fa-laptop-code'],
        ['Art', 'ART', 'Creative expression and visual arts', '#ec4899', 'fas fa-palette'],
        ['Music', 'MUS', 'Musical theory and performance', '#84cc16', 'fas fa-music'],
        ['Physical Education', 'PE', 'Physical fitness and sports', '#f59e0b', 'fas fa-running'],
        ['Economics', 'ECON', 'Study of economic systems', '#059669', 'fas fa-chart-line']
    ];

    $stmt = $conn->prepare("INSERT OR IGNORE INTO subjects (school_id, subject_name, subject_code, description, color_theme, icon_class, is_default) VALUES (?, ?, ?, ?, ?, ?, 1)");
    
    foreach ($defaultSubjects as $subject) {
        $stmt->bindValue(1, $school_id);
        $stmt->bindValue(2, $subject[0]);
        $stmt->bindValue(3, $subject[1]);
        $stmt->bindValue(4, $subject[2]);
        $stmt->bindValue(5, $subject[3]);
        $stmt->bindValue(6, $subject[4]);
        $stmt->execute();
    }
}

// Create default subjects for existing schools
$schools = $conn->query("SELECT school_id FROM schools");
while ($school = $schools->fetchArray(SQLITE3_ASSOC)) {
    insertDefaultSubjects($conn, $school['school_id']);
}

echo "Database extensions created successfully!";
?>
