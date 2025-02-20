<?php
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections_new';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $deleted = $wpdb->delete($table_name, array('id' => $id));
    error_log("Delete attempted for ID: $id, Result: " . ($deleted ? 'Success' : 'Failed'));
    wp_redirect(remove_query_arg(array('action', 'id')));
    exit;
}

// Handle Update Request
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
?>

<div class="wrap">
    <h1><?php esc_html_e('Class Sections Manager', 'textdomain'); ?></h1>
    
    <!-- Display Table -->
    <table class="wp-list-table widefat fixed striped">
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'class_sections_new';
        $results = $wpdb->get_results("SELECT * FROM $table_name");
        
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
                           onclick="return confirm('Are you sure you want to delete this record?');">
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

<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 9999;
    }
    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        width: 70%;
        max-width: 500px;
        border-radius: 5px;
    }
    .edit-btn { background-color: #007cba; color: white; }
    .delete-btn { background-color: #d63638; color: white; }
</style>

<script>
    function openEditModal(button) {
        const id = button.getAttribute('data-id');
        const className = button.getAttribute('data-classname');
        const sections = button.getAttribute('data-sections');

        console.log('Edit clicked - ID:', id, 'Class:', className, 'Sections:', sections);

        // Populate the form fields
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_class_name').value = className;
        document.getElementById('edit_sections').value = sections;

        // Show the modal
        document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Close the modal when clicking outside of it
    window.onclick = function(event) {
        let modal = document.getElementById('editModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>