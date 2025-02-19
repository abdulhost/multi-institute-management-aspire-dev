<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $class_name = sanitize_text_field($_POST['class_name']);
    $sections = sanitize_text_field($_POST['sections']);

    $wpdb->insert(
        $table_name,
        array(
            'class_name' => $class_name,
            'sections' => $sections
        )
    );
    echo "<div class='updated'><p>Class and sections added successfully!</p></div>";
    
}
?>

<div class="wrap">
    <h1>Add New Class and Sections</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="class_name">Class Name</label></th>
                <td><input name="class_name" type="text" id="class_name" value="" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="sections">Sections</label></th>
                <td><input name="sections" type="text" id="sections" value="" class="regular-text" required></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" class="button-primary" value="Add Class"></p>
    </form>
</div>