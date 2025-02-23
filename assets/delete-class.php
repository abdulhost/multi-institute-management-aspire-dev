<?php
// Handle requests - this will now be moved into an action hook that will be executed when the page is loaded
function handle_class_section_requests() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    // Delete
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $deleted = $wpdb->delete($table_name, array('id' => $id));
        error_log("Delete attempted for ID: $id, Result: " . ($deleted ? 'Success' : 'Failed'));
        wp_redirect(remove_query_arg(array('action', 'id')));
        exit;
    }
}
add_action('wp', 'handle_class_section_requests');

function display_class_sections() {
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
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id));

    // Start output buffering
    ob_start();
    ?>
    <!-- Main Wrapper -->
    <div style="display: flex;">

        <!-- Sidebar (Left Side) -->
        <div class="institute-dashboard-wrapper">
            <?php
            // Set the active section for the sidebar
            $active_section = 'edit-class';

            // Include the sidebar
            include plugin_dir_path(__FILE__) . 'sidebar.php';
            ?>
        </div>

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
            <table id="students-table_1" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'textdomain'); ?></th>
                        <th><?php esc_html_e('Class Name', 'textdomain'); ?></th>
                        <th><?php esc_html_e('Sections', 'textdomain'); ?></th>
                        <th><?php esc_html_e('Actions', 'textdomain'); ?></th>
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

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <h3><?php esc_html_e('Edit Class Section', 'textdomain'); ?></h3>
                    <form id="editForm" method="POST" action="">
                        <?php wp_nonce_field('update_class_section', 'class_section_nonce'); ?>
                        <input type="hidden" name="id" id="edit_id">
                        <div style="margin-bottom: 15px;">
                            <label><?php esc_html_e('Class Name:', 'textdomain'); ?></label><br>
                            <input type="text" name="class_name" id="edit_class_name" required style="width: 100%;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label><?php esc_html_e('Sections (comma separated):', 'textdomain'); ?></label><br>
                            <input type="text" name="sections" id="edit_sections" required style="width: 100%;">
                        </div>
                        <div>
                            <button type="submit" name="update" class="button button-primary"><?php esc_html_e('Save', 'textdomain'); ?></button>
                            <button type="button" class="button" onclick="closeEditModal()"><?php esc_html_e('Cancel', 'textdomain'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(button) {
            const id = button.getAttribute('data-id');
            const className = button.getAttribute('data-classname');
            const sections = button.getAttribute('data-sections');

            const idField = document.getElementById('edit_id');
            const classField = document.getElementById('edit_class_name');
            const sectionsField = document.getElementById('edit_sections');

            idField.value = id;
            classField.value = className;
            sectionsField.value = sections;
            console.log('Form submitted - ID:', id, 'Class:', className, 'Sections:', sections);

            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                return true;
            }
            return false;
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            const id = document.getElementById('edit_id').value;
            const className = document.getElementById('edit_class_name').value;
            const sections = document.getElementById('edit_sections').value;
            console.log('Form submitted - ID:', id, 'Class:', className, 'Sections:', sections);
        });

        window.onclick = function(event) {
            let modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <?php
    // Return the buffered content
    return ob_get_clean();
}

// Display the class sections
echo display_class_sections();
?>