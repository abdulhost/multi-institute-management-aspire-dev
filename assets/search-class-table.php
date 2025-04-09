<?php
function render_class_table($instance_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
     // Fetch the current user and their admin_id
     $current_user = wp_get_current_user();
     $admin_id = $current_user->user_login;
 
    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1, // Limit to 1 post
    ));

    // Check if there is an Educational Center for this admin
    if (empty($educational_center)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }
    $class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    $class_sections = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id),
    ARRAY_A
);
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