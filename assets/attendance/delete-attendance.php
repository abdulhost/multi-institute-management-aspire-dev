<?php
// Handle deletion of attendance records
function handle_delete_attendance() {
    if (!wp_verify_nonce($_POST['nonce'], 'attendance_nonce')) {
        wp_send_json_error('Invalid nonce.');
    }

    $record_id = intval($_POST['record_id']);
    // Delete record from database
    // Example: $wpdb->delete('attendance_table', array('id' => $record_id));

    wp_send_json_success('Record deleted successfully.');
}
add_action('wp_ajax_delete_attendance', 'handle_delete_attendance');
?>