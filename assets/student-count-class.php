<?php
// Fetch the current user and their admin_id
$current_user = wp_get_current_user();
$admin_id = $current_user->user_login;

// Query the Educational Center based on the admin_id
$educational_center = get_posts(array(
    'post_type' => 'educational-center',
    'meta_key' => 'admin_id',
    'meta_value' => $admin_id,
    'posts_per_page' => 1, // Limit to 1 post
));

// Check if there is an Educational Center for this admin
if (empty($educational_center)) {
    echo '<p>No Educational Center found for this Admin ID.</p>';
    return;
}

// Get the educational_center_id
$educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);

// Get all class sections from the custom table
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

// Get the total number of students for the educational center
$total_students = get_total_students($educational_center_id);
?>

<div class="wrap">
    <h2>Student Count per Class and Section</h2>
     <!-- Search Form -->
     <div class="form-group search-form">
            <div class="input-group">
                <span class="input-group-addon">Search</span>
                <input type="text" id="search_text_class_count" placeholder="Search by Student Details" class="form-control" />
            </div>
        </div>
    <p>Total Students: <?php echo esc_html($total_students); ?></p>
    <table id="class_count" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Section</th>
                <th>Student Count</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($class_sections as $class_section) {
                $class_name = esc_html($class_section['class_name']);
                $sections = explode(',', $class_section['sections']); // Split sections by comma

                // Loop through each section
                foreach ($sections as $section) {
                    $section = trim($section); // Remove any extra spaces
                    $student_count = esc_html(get_student_count($class_name, $section, $educational_center_id));
                    ?>
                    <tr>
                        <td><?php echo $class_name; ?></td>
                        <td><?php echo $section; ?></td>
                        <td><?php echo $student_count; ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php
function get_student_count($class_name, $section, $educational_center_id) {
    // Query students custom post type
    $args = array(
        'post_type' => 'students', // Custom post type
        'posts_per_page' => -1, // Get all posts
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'class', // ACF field for class
                'value' => $class_name,
                'compare' => '='
            ),
            array(
                'key' => 'section', // ACF field for section
                'value' => $section,
                'compare' => '='
            ),
            array(
                'key' => 'educational_center_id', // ACF field for educational center ID
                'value' => $educational_center_id,
                'compare' => '='
            )
        )
    );

    $students_query = new WP_Query($args);
    return $students_query->found_posts; // Return the count of students
}

function get_total_students($educational_center_id) {
    // Query students custom post type
    $args = array(
        'post_type' => 'students', // Custom post type
        'posts_per_page' => -1, // Get all posts
        'meta_query' => array(
            array(
                'key' => 'educational_center_id', // ACF field for educational center ID
                'value' => $educational_center_id,
                'compare' => '='
            )
        )
    );

    $students_query = new WP_Query($args);
    return $students_query->found_posts; // Return the total count of students
}
?>
     <script>
    $(document).ready(function() {
        // Bind the keyup event to the search input
        $('#search_text_class_count').keyup(function() {
            var searchText = $(this).val().toLowerCase(); // Convert input to lowercase

            // Loop through all table rows
            $('#class_count tbody tr').each(function() {
                // var classID = $(this).find('td').eq(0).text().toLowerCase(); // Class ID column
                var className = $(this).find('td').eq(0).text().toLowerCase(); // Class Name column
                var sections = $(this).find('td').eq(1).text().toLowerCase(); // Sections column

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
