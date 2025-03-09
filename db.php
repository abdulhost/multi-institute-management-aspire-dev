<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_create_db() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Define table names
    $fee_templates_table = $wpdb->prefix . 'fee_templates';
    $student_fees_table = $wpdb->prefix . 'student_fees';
    $transport_enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $transport_fees_table = $wpdb->prefix . 'transport_fees';
    $exams_table = $wpdb->prefix . 'exams';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $exam_results_table = $wpdb->prefix . 'exam_results';
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';
    // Fee Templates Table
    $sql = "CREATE TABLE $fee_templates_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        frequency ENUM('monthly', 'quarterly', 'annual') DEFAULT 'monthly',
        class_id VARCHAR(255) DEFAULT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Student Fees Table (Tuition/Combo)
    $sql = "CREATE TABLE $student_fees_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(255) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        template_id VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        status ENUM('pending', 'paid') DEFAULT 'paid',
        paid_date DATE DEFAULT NULL,
        payment_method ENUM('cod', 'cheque', 'cash') DEFAULT NULL,
        cheque_number VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Transport Enrollments Table
    $sql = "CREATE TABLE $transport_enrollments_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(255) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        enrollment_date DATE NOT NULL,
        active TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Transport Fees Table
    $sql = "CREATE TABLE $transport_fees_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(255) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        template_id VARCHAR(255) NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        status ENUM('pending', 'paid') DEFAULT 'paid',
        paid_date DATE DEFAULT NULL,
        payment_method ENUM('cod', 'cheque', 'cash') DEFAULT NULL,
        cheque_number VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Exams Table
    $sql = "CREATE TABLE $exams_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        exam_date DATE NOT NULL,
        class_id VARCHAR(255) DEFAULT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Exam Subjects Table (No foreign key for now)
    $sql = "CREATE TABLE $exam_subjects_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        exam_id VARCHAR(255) NOT NULL,
        subject_name VARCHAR(100) NOT NULL,
        max_marks DECIMAL(10,2) NOT NULL DEFAULT 100.00,
        education_center_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Exam Results Table (No foreign keys for now)
    $sql = "CREATE TABLE $exam_results_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        exam_id VARCHAR(255) NOT NULL,
        subject_id VARCHAR(255) NOT NULL,
        student_id VARCHAR(255) NOT NULL,
        marks DECIMAL(10,2) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    ta_id INT(11) NOT NULL AUTO_INCREMENT,
    education_center_id VARCHAR(255) NOT NULL,
    teacher_id VARCHAR(50) NOT NULL,
    teacher_name VARCHAR(255) NOT NULL,
    department_id INT(11) NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    PRIMARY KEY (ta_id),
    INDEX (teacher_id, date)
) $charset_collate;";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Create departments table
$sql = "CREATE TABLE IF NOT EXISTS $departments_table (
    department_id INT(11) NOT NULL AUTO_INCREMENT,
    department_name VARCHAR(255) NOT NULL,
    education_center_id VARCHAR(255) NOT NULL,
    PRIMARY KEY (department_id),
    INDEX (education_center_id)
) $charset_collate;";
dbDelta($sql);

$table_class_name = $wpdb->prefix . 'class_sections';

    // Uncomment and adjust table creation if needed
        $sql = "CREATE TABLE $table_class_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            class_name varchar(255) NOT NULL,
            sections text NOT NULL,
            education_center_id varchar(255), -- Added to match query
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    
}

function my_education_erp_seed_templates() {
    global $wpdb;

    $fee_templates_table = $wpdb->prefix . 'fee_templates';
    $exams_table = $wpdb->prefix . 'exams';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $exam_results_table = $wpdb->prefix . 'exam_results';

    // Seed Fee Templates
    if ($wpdb->get_var("SELECT COUNT(*) FROM $fee_templates_table") == 0) {
        $wpdb->insert($fee_templates_table, ['name' => 'Tuition Class 11', 'amount' => 500.00, 'frequency' => 'monthly', 'education_center_id' => 1]);
        $wpdb->insert($fee_templates_table, ['name' => 'Transport', 'amount' => 200.00, 'frequency' => 'monthly', 'education_center_id' => 1]);
        $wpdb->insert($fee_templates_table, ['name' => 'Tuition + Transport', 'amount' => 700.00, 'frequency' => 'monthly', 'education_center_id' => 1]);
    }

    // Seed Exams
    if ($wpdb->get_var("SELECT COUNT(*) FROM $exams_table") == 0) {
        $wpdb->insert($exams_table, [
            'name' => 'Final Exam Class 12 2025',
            'exam_date' => '2025-12-15',
            'class_id' => 12,
            'education_center_id' => 1,
        ]);
    }

    // Seed Exam Subjects
    if ($wpdb->get_var("SELECT COUNT(*) FROM $exam_subjects_table") == 0) {
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'Mathematics', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'Science', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'English', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'History', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'Geography', 'max_marks' => 100, 'education_center_id' => 1]);
    }

    // Seed Exam Results
    if ($wpdb->get_var("SELECT COUNT(*) FROM $exam_results_table") == 0) {
        $wpdb->insert($exam_results_table, ['exam_id' => 1, 'subject_id' => 1, 'student_id' => 1, 'marks' => 85.50, 'education_center_id' => 1]);
        $wpdb->insert($exam_results_table, ['exam_id' => 1, 'subject_id' => 2, 'student_id' => 1, 'marks' => 78.00, 'education_center_id' => 1]);
    }
}
// / Only run on activation, not on every include
// Remove direct calls outside functions
my_education_erp_create_db();
my_education_erp_seed_templates();