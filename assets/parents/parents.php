<?php
// parents.php
function parents_institute_dashboard_shortcode() {
    global $wpdb;

    $educational_center_id = get_educational_center_data();
    if (empty($educational_center_id)) {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    // Handle parent deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['parent_id'])) {
        $parent_id = intval($_GET['parent_id']);
        if (wp_delete_post($parent_id, true)) {
            wp_redirect(home_url('/institute-dashboard/#parents'));
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
        $active_section = 'view-parents';
        include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?>
    <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
        <a href="/institute-dashboard/add-parent">+ Add Parent</a>

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
                        <th>Actions</th>
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
                                    <td>
                                        <a href="#parents" class="edit-btn" 
                                           data-parent-id="' . esc_attr($parent->ID) . '" 
                                           data-parent-name="' . esc_attr($parent_name) . '" 
                                           data-parent-email="' . esc_attr($parent_email) . '" 
                                           data-parent-phone="' . esc_attr($parent_phone_number) . '">Edit</a> |
                                        <a href="?action=delete&parent_id=' . $parent->ID . '" 
                                           onclick="return confirm(\'Are you sure you want to delete this parent?\')">Delete</a>
                                    </td>
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
?>