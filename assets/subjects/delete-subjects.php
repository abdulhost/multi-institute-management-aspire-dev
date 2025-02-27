<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}


// Handle subject actions (add, edit, delete)
function handle_delete_subject_actions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $message = '';
    $educational_center_id = get_educational_center_data();

    // Add Subject
    // if (isset($_POST['add_subject']) && isset($_POST['add_nonce']) && wp_verify_nonce($_POST['add_nonce'], 'add_subject_action')) {
    //     $subject_name = sanitize_text_field($_POST['subject_name']);
    //     if ($educational_center_id === false) {
    //         $message = '<div class="notice notice-error is-dismissible"><p>No valid Educational Center ID found.</p></div>';
    //     } else {
    //         $result = $wpdb->insert(
    //             $table_name,
    //             array('education_center_id' => $educational_center_id, 'subject_name' => $subject_name),
    //             array('%s', '%s')
    //         );
    //         $message = $result === false 
    //             ? '<div class="notice notice-error is-dismissible"><p>Error adding subject: ' . esc_html($wpdb->last_error) . '</p></div>'
    //             : '<div class="notice notice-success is-dismissible"><p>Subject added successfully!</p></div>';
    //         if ($result === false) error_log('Add Subject DB Error: ' . $wpdb->last_error);
    //     }
    // }

    // Edit Subject
    // if (isset($_POST['edit_subject']) && isset($_POST['edit_nonce']) && wp_verify_nonce($_POST['edit_nonce'], 'edit_subject_action')) {
    //     $subject_id = intval($_POST['subject_id']);
    //     $subject_name = sanitize_text_field($_POST['subject_name']);
    //     if ($educational_center_id === false) {
    //         $message = '<div class="notice notice-error is-dismissible"><p>No valid Educational Center ID found.</p></div>';
    //     } else {
    //         $result = $wpdb->update(
    //             $table_name,
    //             array('subject_name' => $subject_name),
    //             array('subject_id' => $subject_id, 'education_center_id' => $educational_center_id),
    //             array('%s'),
    //             array('%d', '%s')
    //         );
    //         $message = $result === false 
    //             ? '<div class="notice notice-error is-dismissible"><p>Error updating subject: ' . esc_html($wpdb->last_error) . '</p></div>'
    //             : ($result === 0 
    //                 ? '<div class="notice notice-warning is-dismissible"><p>No changes made to subject.</p></div>'
    //                 : '<div class="notice notice-success is-dismissible"><p>Subject updated successfully!</p></div>');
    //         if ($result === false) error_log('Edit Subject DB Error: ' . $wpdb->last_error);
    //     }
    // }

    // Delete Subject
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['subject_id']) && isset($_GET['delete_nonce']) && wp_verify_nonce($_GET['delete_nonce'], 'delete_subject_action')) {
        $subject_id = intval($_GET['subject_id']);
        if ($educational_center_id === false) {
            $message = '<div class="notice notice-error is-dismissible"><p>No valid Educational Center ID found.</p></div>';
        } else {
            $result = $wpdb->delete(
                $table_name,
                array('subject_id' => $subject_id, 'education_center_id' => $educational_center_id),
                array('%d', '%s')
            );
            $message = $result === false 
                ? '<div class="notice notice-error is-dismissible"><p>Error deleting subject: ' . esc_html($wpdb->last_error) . '</p></div>'
                : ($result === 0 
                    ? '<div class="notice notice-warning is-dismissible"><p>Subject not found or already deleted.</p></div>'
                    : '<div class="notice notice-success is-dismissible"><p>Subject deleted successfully!</p></div>');
            if ($result === false) error_log('Delete Subject DB Error: ' . $wpdb->last_error);
        }
    }

    return $message;
}

// Render the subjects list
function render_delete_subjects_list($educational_center_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $subjects = $educational_center_id ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id)) : array();

    if (empty($subjects)) {
        echo '<p class="no-subjects">No subjects found for this educational center.</p>';
    } else {
        ?>
        <table class="wp-list-table widefat  striped subjects-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Subject Name</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <tr>
                        <td><?php echo esc_html($subject->subject_id); ?></td>
                        <td><?php echo esc_html($subject->subject_name); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete', 'subject_id' => $subject->subject_id, 'delete_nonce' => wp_create_nonce('delete_subject_action')))); ?>" class="button button-secondary delete-btn" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}

// Main page rendering
function delete_subjects_manager_page() {
    $message = handle_delete_subject_actions();
    $educational_center_id = get_educational_center_data();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
            $active_section = 'delete-subject';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');
            ?>
    <div class="wrap subjects-manager">
        <h1 class="wp-heading-inline">Manage Subjects</h1>
        <?php echo $message; ?>

        <!-- Add Subject Section -->
        <!-- <div class="card subjects-card">
            <h2 class="title">Add New Subject</h2>
            <form method="post" action="" class="subjects-form">
                <?php 
                // wp_nonce_field('add_subject_action', 'add_nonce'); 
                ?>
                <div class="form-field">
                    <label for="subject_name">Subject Name</label>
                    <input type="text" name="subject_name" id="subject_name" class="regular-text" required>
                </div>
                <?php submit_button('Add Subject', 'primary large', 'add_subject'); ?>
            </form>
        </div> -->

        <!-- Subjects List Section -->
        <div class="card subjects-card">
            <h2 class="title">Existing Subjects</h2>
            <?php render_delete_subjects_list($educational_center_id); ?>
        </div>
    </div>
    </div>

    <style>
        .subjects-manager { padding: 20px; }
        .subjects-card { max-width: 900px; margin-bottom: 20px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .subjects-card .title { margin: 0 0 15px; font-size: 1.25em; color: #23282d; }
        .subjects-form .form-field { margin-bottom: 20px; }
        .subjects-form label { display: block; font-weight: 600; margin-bottom: 5px; }
        .subjects-form .regular-text { width: 100%; max-width: 400px; padding: 8px; border-radius: 4px; border: 1px solid #8c8f94; }
        .subjects-table { margin-top: 15px; }
        .subjects-table th { font-weight: 600; background: #f9f9f9; }
        .subjects-table td { vertical-align: middle; }
        .inline-form { display: inline-block; margin-right: 10px; }
        .inline-input { width: 200px; padding: 6px; border-radius: 4px; border: 1px solid #8c8f94; }
        .button.large { padding: 10px 20px; font-size: 14px; }
        .button-primary { background: #007cba; border-color: #006ba1; }
        .button-primary:hover { background: #006ba1; border-color: #005177; }
        .button-secondary { background: #d63638; border-color: #b32d2e; color: #fff; }
        .button-secondary:hover { background: #b32d2e; border-color: #921c1e; }
        .no-subjects { color: #666; font-style: italic; }
    </style>
    <?php
}

// Shortcode for frontend use (optional)
add_shortcode('delete_subjects_crud_frontend', 'delete_subjects_manager_page');