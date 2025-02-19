<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $id = intval($_POST['id']);
    $class_name = sanitize_text_field($_POST['class_name']);
    $sections = sanitize_text_field($_POST['sections']);

    $wpdb->update(
        $table_name,
        array(
            'class_name' => $class_name,
            'sections' => $sections
        ),
        array('id' => $id)
    );
    echo "<div class='updated'><p>Class and sections updated successfully!</p></div>";
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$class_section = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
?>

<div class="wrap">
    <h1>Edit Class and Sections</h1>
    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo esc_attr($class_section['id']); ?>">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="class_name">Class Name</label></th>
                <td><input name="class_name" type="text" id="class_name" value="<?php echo esc_attr($class_section['class_name']); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="sections">Sections</label></th>
                <td><input name="sections" type="text" id="sections" value="<?php echo esc_attr($class_section['sections']); ?>" class="regular-text" required></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" class="button-primary" value="Update Class"></p>
    </form>
</div>