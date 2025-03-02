<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_is_enrolled_in_transport($student_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'transport_enrollments';
    return $wpdb->get_var($wpdb->prepare("SELECT active FROM $table WHERE student_id = %d AND active = 1", $student_id));
}

function my_education_erp_enroll_student_in_transport($student_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'transport_enrollments';
    $wpdb->insert($table, [
        'student_id' => $student_id,
        'institute_id' => 1,
        'enrollment_date' => current_time('Y-m-d'),
        'active' => 1,
    ]);
}