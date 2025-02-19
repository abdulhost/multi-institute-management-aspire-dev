<?php
if (isset($_GET['id'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $id = intval($_GET['id']);
    $wpdb->delete($table_name, array('id' => $id));
    echo "<div class='updated'><p>Class and sections deleted successfully!</p></div>";
}
?>