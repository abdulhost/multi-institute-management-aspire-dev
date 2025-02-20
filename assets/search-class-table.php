<?php
function render_class_table($instance_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    ?>
    <div class="wrap">
        <h2>Class Sections</h2>
        <!-- Search Form -->
        <div class="form-group search-form">
            <div class="input-group">
                <span class="input-group-addon">Search</span>
                <input type="text" id="search_text_class_<?php echo $instance_id; ?>" placeholder="Search by Student Details" class="form-control" />
            </div>
        </div>
        <table id="classlist_<?php echo $instance_id; ?>" class="wp-list-table widefat fixed striped">
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

    <script>
    $(document).ready(function() {
        // Bind the keyup event to the search input
        $('#search_text_class_<?php echo $instance_id; ?>').keyup(function() {
            var searchText = $(this).val().toLowerCase(); // Convert input to lowercase

            // Loop through all table rows
            $('#classlist_<?php echo $instance_id; ?> tbody tr').each(function() {
                // var classID = $(this).find('td').eq(0).text().toLowerCase(); // Class ID column
                var className = $(this).find('td').eq(1).text().toLowerCase(); // Class Name column
                var sections = $(this).find('td').eq(2).text().toLowerCase(); // Sections column

                // If any of the fields match the search text, show the row; otherwise, hide it
                if ( className.indexOf(searchText) > -1 || sections.indexOf(searchText) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
    <?php
}
?>