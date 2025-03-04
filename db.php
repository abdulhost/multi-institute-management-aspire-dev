<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Fee Templates Table
    $templates_table = $wpdb->prefix . 'fee_templates';
    $sql = "CREATE TABLE $templates_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        frequency ENUM('monthly', 'quarterly', 'annual') DEFAULT 'monthly',
        class_id varchar(255) DEFAULT NULL,
        education_center_id varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Student Fees Table (Tuition/Combo)
    $student_fees_table = $wpdb->prefix . 'student_fees';
    $sql = "CREATE TABLE $student_fees_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id varchar(255) NOT NULL,
        education_center_id varchar(255) NOT NULL,
        template_id varchar(255) NOT NULL,
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
    $enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $sql = "CREATE TABLE $enrollments_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id varchar(255) NOT NULL,
        education_center_id varchar(255) NOT NULL,
        enrollment_date DATE NOT NULL,
        active TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Transport Fees Table
    $transport_fees_table = $wpdb->prefix . 'transport_fees';
    $sql = "CREATE TABLE $transport_fees_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id varchar(255) NOT NULL,
        education_center_id varchar(255) NOT NULL,
        template_id varchar(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        status ENUM('pending', 'paid') DEFAULT 'paid',
        paid_date DATE DEFAULT NULL,
        payment_method ENUM('cod', 'cheque', 'cash') DEFAULT NULL,
        cheque_number VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);
}

// function my_education_erp_seed_templates() {
//     global $wpdb;
//     $table = $wpdb->prefix . 'fee_templates';
//     $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table");
//     if ($existing == 0) {
//         $wpdb->insert($table, ['name' => 'Tuition Class 11', 'amount' => 500.00, 'frequency' => 'monthly', 'education_center_id' => 1, 'fee_type' => 'tuition']);
//         $wpdb->insert($table, ['name' => 'Transport', 'amount' => 200.00, 'frequency' => 'monthly', 'education_center_id' => 1, 'fee_type' => 'transport']);
//         $wpdb->insert($table, ['name' => 'Tuition + Transport', 'amount' => 700.00, 'frequency' => 'monthly', 'education_center_id' => 1, 'fee_type' => 'combo']);
//     }
// }
my_education_erp_create_tables();
// my_education_erp_seed_templates();