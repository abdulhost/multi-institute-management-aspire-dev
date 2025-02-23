<?php
include plugin_dir_path(__FILE__) . 'fetch_details_func.php';

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

    // Update
    if (isset($_POST['update']) && isset($_POST['class_section_nonce']) && wp_verify_nonce($_POST['class_section_nonce'], 'update_class_section')) {
        $id = intval($_POST['id']);
        $data = array(
            'class_name' => sanitize_text_field($_POST['class_name']),
            'sections' => sanitize_text_field($_POST['sections'])
        );
        $where = array('id' => $id);

        error_log("Update attempted - ID: $id, Data: " . print_r($data, true));
        $updated = $wpdb->update($table_name, $data, $where);
        error_log("Update result: " . ($updated !== false ? "Success (Rows affected: $updated)" : 'Failed') . " SQL: " . $wpdb->last_query);

        if ($updated !== false) {
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            wp_die("Update failed. Last query: " . $wpdb->last_query . "<br>Last error: " . $wpdb->last_error);
        }
    }
}
add_action('wp', 'handle_class_section_requests');

// Display the front-end content
function display_class_section_manager() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $educational_center_id = get_educational_center_data();

    if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
        return $educational_center_id;
    }

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id));
    
    // Clean any prior output and start buffering
    ob_clean();
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
            <table  id="students-table_1" class="wp-list-table widefat fixed striped">
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
                                <button class="button edit-btn" 
                                    data-id="<?php echo esc_attr($row->id); ?>" 
                                    data-classname="<?php echo esc_attr($row->class_name); ?>" 
                                    data-sections="<?php echo esc_attr($row->sections); ?>" 
                                    onclick="openEditModal(this)">
                                    <?php esc_html_e('Edit', 'textdomain'); ?>
                                </button>
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
}

// Shortcode to display the class section manager
add_shortcode('class_sections_manager', 'display_class_section_manager');
?>
