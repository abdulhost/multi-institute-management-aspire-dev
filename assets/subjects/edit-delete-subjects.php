<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Handle subject actions (add, edit, delete)
function handle_subject_actions($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $message = '';

    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

    if (!$educational_center_id) {
        return '<div class="notice notice-error is-dismissible"><p>Unable to retrieve educational center information.</p></div>';
    }

    // Edit Subject
    if (isset($_POST['edit_subject']) && isset($_POST['edit_nonce']) && wp_verify_nonce($_POST['edit_nonce'], 'edit_subject_action')) {
        $subject_id = intval($_POST['subject_id']);
        $subject_name = sanitize_text_field($_POST['subject_name']);
        $teacher_id = sanitize_text_field($_POST['teacher_id']); // New field

        $result = $wpdb->update(
            $table_name,
            array(
                'subject_name' => $subject_name,
                'teacher_id' => $teacher_id // Include teacher_id in update
            ),
            array(
                'subject_id' => $subject_id,
                'education_center_id' => $educational_center_id
            ),
            array('%s', '%s'), // Format for subject_name and teacher_id
            array('%d', '%s')  // Format for subject_id and education_center_id
        );
        $message = $result === false 
            ? '<div class="notice notice-error is-dismissible"><p>Error updating subject: ' . esc_html($wpdb->last_error) . '</p></div>'
            : ($result === 0 
                ? '<div class="notice notice-warning is-dismissible"><p>No changes made to subject.</p></div>'
                : '<div class="notice notice-success is-dismissible"><p>Subject updated successfully!</p></div>');
        if ($result === false) error_log('Edit Subject DB Error: ' . $wpdb->last_error);
    }

    // Delete Subject
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['subject_id']) && isset($_GET['delete_nonce']) && wp_verify_nonce($_GET['delete_nonce'], 'delete_subject_action')) {
        $subject_id = intval($_GET['subject_id']);
        $result = $wpdb->delete(
            $table_name,
            array(
                'subject_id' => $subject_id,
                'education_center_id' => $educational_center_id
            ),
            array('%d', '%s')
        );
        $message = $result === false 
            ? '<div class="notice notice-error is-dismissible"><p>Error deleting subject: ' . esc_html($wpdb->last_error) . '</p></div>'
            : ($result === 0 
                ? '<div class="notice notice-warning is-dismissible"><p>Subject not found or already deleted.</p></div>'
                : '<div class="notice notice-success is-dismissible"><p>Subject deleted successfully!</p></div>');
        if ($result === false) error_log('Delete Subject DB Error: ' . $wpdb->last_error);
    }

    return $message;
}

// Render the subjects list
function render_subjects_list($educational_center_id, $current_teacher_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $subjects = $educational_center_id ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id)) : array();

    if (empty($subjects)) {
        echo '<p class="no-subjects">No subjects found for this educational center.</p>';
    } else {
        ?>
        <table class="wp-list-table widefat fixed striped subjects-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Subject Name</th>
                    <th scope="col">Teacher ID</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <tr>
                        <td><?php echo esc_html($subject->subject_id); ?></td>
                        <td><?php echo esc_html($subject->subject_name); ?></td>
                        <td><?php echo esc_html($subject->teacher_id ?? 'N/A'); ?></td>
                        <td>
                            <form method="post" action="" class="inline-form">
                                <?php wp_nonce_field('edit_subject_action', 'edit_nonce'); ?>
                                <input type="hidden" name="subject_id" value="<?php echo esc_attr($subject->subject_id); ?>">
                                <input type="text" name="subject_name" value="<?php echo esc_attr($subject->subject_name); ?>" class="inline-input" required>
                                <input type="text" name="teacher_id" value="<?php echo esc_attr($subject->teacher_id ?? $current_teacher_id); ?>" class="inline-input" required placeholder="Teacher ID">
                                <button type="submit" name="edit_subject" class="button button-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}

// Main page rendering
function subjects_manager_page($atts) {
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

    if (!$educational_center_id) {
        return '<p>Unable to retrieve educational center information.</p>';
    }

    $message = handle_subject_actions($atts);

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          if (is_teacher($atts)) { 
        } else {
        $active_section = 'update-subject';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');}
        ?>
        <div class="wrap subjects-manager">
            <h1 class="wp-heading-inline">Manage Subjects</h1>
            <?php echo $message; ?>

            <!-- Subjects List Section -->
            <div class="card subjects-card">
                <h2 class="title">Existing Subjects</h2>
                <?php render_subjects_list($educational_center_id, $current_teacher_id); ?>
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
        .inline-input { width: 200px; padding: 6px; border-radius: 4px; border: 1px solid #8c8f94; margin-right: 5px; }
        .button.large { padding: 10px 20px; font-size: 14px; }
        .button-primary { background: #007cba; border-color: #006ba1; }
        .button-primary:hover { background: #006ba1; border-color: #005177; }
        .button-secondary { background: #d63638; border-color: #b32d2e; color: #fff; }
        .button-secondary:hover { background: #b32d2e; border-color: #921c1e; }
        .no-subjects { color: #666; font-style: italic; }
    </style>
    <?php
    return ob_get_clean();
}

// Shortcode for frontend use (optional)
add_shortcode('subjects_crud_frontend', 'subjects_manager_page');