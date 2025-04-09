<?php
// include plugin_dir_path(__FILE__) . 'fetch_details_func.php';

    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    // Delete
    // if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    //     $id = intval($_GET['id']);
    //     $deleted = $wpdb->delete($table_name, array('id' => $id));
    //     error_log("Delete attempted for ID: $id, Result: " . ($deleted ? 'Success' : 'Failed'));
    //     wp_redirect(remove_query_arg(array('action', 'id')));
    //     exit;
    // }

   

// Display the front-end content
function display_delete_class_section_manager() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $educational_center_id = get_educational_center_data();

    if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
        return $educational_center_id;
    }
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id));
    
    // Clean any prior output and start buffering
    ob_clean();
    ob_start();
    ?>
    <!-- Main Wrapper -->
    <div style="display: flex;">

        <!-- Sidebar (Left Side) -->
        <!-- <div class="institute-dashboard-wrapper"> -->
        <?php
        echo render_admin_header(wp_get_current_user());
        if (!is_center_subscribed($educational_center_id)) {
            return render_subscription_expired_message($educational_center_id);
        }
// Set the active section for the sidebar
$active_section = 'delete-class';

// Include the sidebar
include plugin_dir_path(__FILE__) . 'sidebar.php';
?>
        <!-- </div> -->

        <!-- Content (Right Side) -->
        <div class="wrap" style="width: 70%; padding: 20px;">
            <h2><?php esc_html_e('Class Sections Manager', 'textdomain'); ?></h2>
            <?php
// Include the search bar for the first table
$args = array(
    'table_id' => 'students-table_1',
    'search_input_id' => 'search_text_1',
);
include plugin_dir_path(__FILE__) . 'searchbar.php';
?>
            <!-- Display Table -->
            <table  id="students-table_1" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'textdomain'); ?></th>
                        <th><?php esc_html_e('Class Name', 'textdomain'); ?></th>
                        <th><?php esc_html_e('Sections', 'textdomain'); ?></th>
                        <th><?php esc_html_e('Delete', 'textdomain'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($results) {
                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html($row->class_name); ?></td>
                            <td><?php echo esc_html($row->sections); ?></td>
                            <td>
                              
                                <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete', 'id' => $row->id))); ?>" 
                                   class="button delete-btn" 
                                   onclick="return deleteRecord(<?php echo esc_js($row->id); ?>)">
                                   <?php esc_html_e('Delete', 'textdomain'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="4">No records found</td></tr>';
                }
                ?>
                </tbody>
            </table>

        </div>
    </div>

    <script>
        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                return true;
            }
            return false;
        }
    </script>

    <?php
}

// Shortcode to display the class section manager
add_shortcode('delete_class_sections_manager', 'display_delete_class_section_manager');
?>
