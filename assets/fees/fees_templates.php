<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_get_templates($fee_type = null) {
    global $wpdb;
    $table = $wpdb->prefix . 'fee_templates';
    $query = "SELECT * FROM $table WHERE institute_id = 1"; // Hardcoded institute for now
    if ($fee_type) {
        $query .= $wpdb->prepare(" AND fee_type = %s", $fee_type);
    }
    return $wpdb->get_results($query);
}

function my_education_erp_get_template($template_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'fee_templates';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id));
}