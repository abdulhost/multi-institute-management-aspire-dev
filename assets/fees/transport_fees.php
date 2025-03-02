<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_record_transport_fee_payment($student_id, $template_id, $month_year, $payment_method, $cheque_number = '') {
    global $wpdb;
    $template = my_education_erp_get_template($template_id);
    $table = $wpdb->prefix . 'transport_fees';
    $status = $payment_method == 'cheque' ? 'pending' : 'paid';
    $wpdb->insert($table, [
        'student_id' => $student_id,
        'institute_id' => 1,
        'template_id' => $template_id,
        'amount' => $template->amount,
        'month_year' => $month_year,
        'status' => $status,
        'paid_date' => $status == 'paid' ? current_time('Y-m-d') : null,
        'payment_method' => $payment_method,
        'cheque_number' => $cheque_number,
    ]);
}

function my_education_erp_get_transport_fees($student_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'transport_fees';
    return $wpdb->get_results($wpdb->prepare(
        "SELECT tf.*, ft.name FROM $table tf 
         JOIN {$wpdb->prefix}fee_templates ft ON tf.template_id = ft.id 
         WHERE tf.student_id = %d", 
        $student_id
    ));
}