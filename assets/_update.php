<?php
// Assuming you're using WordPress environment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $class_name = isset($_POST['class_name']) ? sanitize_text_field($_POST['class_name']) : '';
    $sections = isset($_POST['sections']) ? sanitize_text_field($_POST['sections']) : '';

    // Prepare the update query
    $updated = $wpdb->update(
        $table_name,
        [
            'class_name' => $class_name,
            'sections' => $sections
        ],
        ['id' => $id],
        ['%s', '%s'],
        ['%d']
    );

    if ($updated !== false) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    exit;
}
?>
