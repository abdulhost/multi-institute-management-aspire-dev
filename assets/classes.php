<?php
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>

<div class="wrap">
    <h1>Class Sections</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Class Name</th>
                <th>Sections</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($class_sections as $class_section): ?>
                <tr>
                    <td><?php echo esc_html($class_section['id']); ?></td>
                    <td><?php echo esc_html($class_section['class_name']); ?></td>
                    <td><?php echo esc_html($class_section['sections']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>