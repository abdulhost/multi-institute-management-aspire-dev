<?php
// parents.php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

function parents_institute_dashboard_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

    if (empty($educational_center_id)) {
        return '<p>No Educational Center found for this user.</p>';
    }

    // Handle parent deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['parent_id'])) {
        $parent_id = intval($_GET['parent_id']);
        if (wp_delete_post($parent_id, true)) {
            // Redirect to the same page
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<p class="error-message">Error deleting parent.</p>';
        }
    }

    // Start output buffering
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          if (is_teacher($atts)) { 
        } else {
        $active_section = 'view-parents';
        include plugin_dir_path(__FILE__) . '../sidebar.php';} // Adjust path as needed
        ?>
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <!-- <a href="/institute-dashboard/add-parent">+ Add Parent</a> -->

            <!-- Search Form -->
            <?php
            // Include the search bar for the first table
            $args = array(
                'table_id' => 'parents-table',
                'search_input_id' => 'search_text',
            );
            include plugin_dir_path(__FILE__) . '../searchbar.php';
            ?>

            <!-- Parent List Table -->
            <div id="result">
                <h3>Parents List</h3>
                <table id="parents-table">
                    <thead>
                        <tr>
                            <th>Parent ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query to get parents associated with the current Educational Center
                        $parents = get_posts(array(
                            'post_type' => 'parent', // Changed to parent CPT
                            'meta_key' => 'educational_center_id',
                            'meta_value' => $educational_center_id,
                            'posts_per_page' => -1, // Get all posts
                        ));

                        // Display the list of parents in the table
                        if (!empty($parents)) {
                            foreach ($parents as $parent) {
                                $parent_id = get_post_meta($parent->ID, 'parent_id', true);
                                $parent_name = get_post_meta($parent->ID, 'parent_name', true); // Changed from post_title
                                $parent_email = get_post_meta($parent->ID, 'parent_email', true);
                                $parent_phone_number = get_post_meta($parent->ID, 'parent_phone_number', true);

                                echo '<tr class="parent-row">
                                        <td>' . esc_html($parent_id) . '</td>
                                        <td>' . esc_html($parent_name) . '</td>
                                        <td>' . esc_html($parent_email) . '</td>
                                        <td>' . esc_html($parent_phone_number) . '</td>
                                     
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5">No parents found for this Educational Center.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('parents_institute_dashboard', 'parents_institute_dashboard_shortcode');