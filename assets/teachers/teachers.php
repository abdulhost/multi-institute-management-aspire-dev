<?php
// teachers.php
function teachers_institute_dashboard_shortcode() {
    global $wpdb;

    $educational_center_id = get_educational_center_data();
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    
    }

    // Handle teacher deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['teacher_id'])) {
        $teacher_id = intval($_GET['teacher_id']);
        if (wp_delete_post($teacher_id, true)) {
            wp_redirect(home_url('/institute-dashboard/#teachers'));
            exit;
        } else {
            echo '<p class="error-message">Error deleting teacher.</p>';
        }
    }

    // Start output buffering
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          echo render_admin_header(wp_get_current_user());
          if (!is_center_subscribed($educational_center_id)) {
              return render_subscription_expired_message($educational_center_id);
          }
        $active_section = 'view-teachers';
        include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?>
    <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
        <a href="/institute-dashboard/add-teacher">+ Add Teacher</a>

         <!-- Search Form -->

<div class="form-group search-form">
    <div class="input-group">
        <span class="input-group-addon">Search</span>
        <input type="text" id="search_text" placeholder="Search by Student Details" class="form-control" />
    </div>
</div>
<!-- teacher List Table -->
<div id="result">
    <h3>Teachers List</h3>
    <table id="teachers-table">
        <thead>
            <tr>
                <th>Teacher ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to get teacher associated with the current Educational Center
            $teachers = get_posts(array(
                'post_type' => 'teacher',
                'meta_key' => 'educational_center_id',
                'meta_value' => $educational_center_id,
                'posts_per_page' => -1, // Get all posts
            ));

            // Display the list of teacher in the table
            if (!empty($teachers)) {
                foreach ($teachers as $teacher) {
                    $teacher_id = get_post_meta($teacher->ID, 'teacher_id', true);
                    $teacher_name = $teacher->post_title;
                    $teacher_email = get_post_meta($teacher->ID, 'teacher_email', true);
                    $teacher_phone_number= get_post_meta($teacher->ID, 'teacher_phone_number', true);

                    echo '<tr class="teacher-row">
                            <td>' . esc_html($teacher_id) . '</td>
                            <td>' . esc_html($teacher_name) . '</td>
                            <td>' . esc_html($teacher_email) . '</td>
                            <td>' . esc_html($teacher_phone_number) . '</td>
                            <td>
                                <a href="#teachers" class="edit-btn" data-teacher-id="' . esc_attr($teacher->ID) . '" data-teacher-name="' . esc_attr($teacher_name) . '" data-teacher-email="' . esc_attr($teacher_email) . '" data-teacher-class="' . esc_attr($teacher_phone_number) . '">Edit</a> |
                                <a href="?action=delete&teacher_id=' . $teacher->ID . '" onclick="return confirm(\'Are you sure you want to delete this teacher?\')">Delete</a>
                            </td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="5">No teachers found for this Educational Center.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

    </div>
    </div>
   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <!-- JavaScript for toggling form and search -->
    <script>

    $(document).ready(function() {
        $('#search_text').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#teachers-table tbody tr').each(function() {
                var teacherID = $(this).find('td').eq(0).text().toLowerCase();
                var teacherName = $(this).find('td').eq(1).text().toLowerCase();
                var teacherEmail = $(this).find('td').eq(2).text().toLowerCase();
                if (teacherID.includes(searchText) || teacherName.includes(searchText) || teacherEmail.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('teachers_institute_dashboard', 'teachers_institute_dashboard_shortcode');
?>