<?php
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>

<div class="wrap">
    <h1>Student Count per Class and Section</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Sections</th>
                <th>Student Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($class_sections as $class_section): ?>
                <tr>
                    <td><?php echo esc_html($class_section['class_name']); ?></td>
                    <td><?php echo esc_html($class_section['sections']); ?></td>
                    <td><?php echo esc_html(get_student_count($class_section['id'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
function get_student_count($class_section_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students'; // Assuming you have a students table
    return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE class_section_id = %d", $class_section_id));
}
?>