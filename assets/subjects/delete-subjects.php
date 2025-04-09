<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX action for deletion
add_action('wp_ajax_delete_subject', 'ajax_delete_subject');
function ajax_delete_subject() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_subject_action')) {
        wp_send_json_error('Security check failed.');
        return;
    }

    // Get educational_center_id from the AJAX request instead of determining it anew
    $educational_center_id = sanitize_text_field($_POST['educational_center_id'] ?? '');
    if (!$educational_center_id) {
        wp_redirect(home_url('/login'));
            exit();
    }

    $subject_id = intval($_POST['subject_id'] ?? 0);
    if (!$subject_id) {
        wp_send_json_error('Invalid subject ID.');
        return;
    }

    // Debug: Log the attempt
    error_log('AJAX Delete: Subject ID: ' . $subject_id . ', Educational Center ID: ' . $educational_center_id);

    // Check if the subject exists before attempting deletion
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE subject_id = %d AND education_center_id = %s",
        $subject_id,
        $educational_center_id
    ));
    if ($exists == 0) {
        error_log('Subject ID: ' . $subject_id . ' not found for Educational Center ID: ' . $educational_center_id);
        wp_send_json_error('Subject not found or already deleted.');
        return;
    }

    $result = $wpdb->delete(
        $table_name,
        array(
            'subject_id' => $subject_id,
            'education_center_id' => $educational_center_id
        ),
        array('%d', '%s')
    );

    if ($result === false) {
        error_log('Delete Subject DB Error: ' . $wpdb->last_error);
        wp_send_json_error('Error deleting subject: ' . $wpdb->last_error);
    } elseif ($result === 0) {
        error_log('Unexpected: No rows deleted for subject ID: ' . $subject_id . ' despite existence check');
        wp_send_json_error('Subject not found or already deleted (unexpected).');
    } else {
        error_log('Subject ID: ' . $subject_id . ' deleted successfully');
        wp_send_json_success('Subject deleted successfully!');
    }
}

// Render the subjects list for deletion
function render_delete_subjects_list($educational_center_id, $current_teacher_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $subjects = $educational_center_id ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id)) : array();

    if (empty($subjects)) {
        echo '<p class="no-subjects">No subjects found for this educational center.</p>';
    } else {
        ?>
        <table class="wp-list-table widefat striped subjects-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Subject Name</th>
                    <th scope="col">Teacher ID</th>
                    <th scope="col">Educational Center ID</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <tr data-subject-id="<?php echo esc_attr($subject->subject_id); ?>">
                        <td><?php echo esc_html($subject->subject_id); ?></td>
                        <td><?php echo esc_html($subject->subject_name); ?></td>
                        <td><?php echo esc_html($subject->teacher_id ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($subject->education_center_id ?? 'N/A'); ?></td>
                        <td>
                            <button class="button button-secondary delete-subject-btn" 
                                    data-subject-id="<?php echo esc_attr($subject->subject_id); ?>" 
                                    data-edu-center-id="<?php echo esc_attr($educational_center_id); ?>" 
                                    data-nonce="<?php echo wp_create_nonce('delete_subject_action'); ?>">
                                Delete
                            </button>
                            <span class="delete-message" style="display: none; margin-left: 10px;"></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}

// Main page rendering
function delete_subjects_manager_page($atts) {
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
        wp_redirect(home_url('/login'));
            exit();
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          if (is_teacher($atts)) { 
        } else {
            echo render_admin_header(wp_get_current_user());
          if (!is_center_subscribed($educational_center_id)) {
              return render_subscription_expired_message($educational_center_id);
          }
        $active_section = 'delete-subject';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');}
        ?>
        <div class="wrap subjects-manager">
            <h1 class="wp-heading-inline">Manage Subjects</h1>
            <div id="delete-message" class="notice" style="display: none;"></div>

            <!-- Subjects List Section -->
            <div class="card subjects-card">
                <h2 class="title">Existing Subjects</h2>
                <?php render_delete_subjects_list($educational_center_id, $current_teacher_id); ?>
            </div>
        </div>
    </div>

    <style>
        .subjects-manager { padding: 20px; }
        .subjects-card { max-width: 900px; margin-bottom: 20px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .subjects-card .title { margin: 0 0 15px; font-size: 1.25em; color: #23282d; }
        .subjects-table { margin-top: 15px; }
        .subjects-table th { font-weight: 600; background: #f9f9f9; }
        .subjects-table td { vertical-align: middle; }
        .button-secondary { background: #d63638; border-color: #b32d2e; color: #fff; }
        .button-secondary:hover { background: #b32d2e; border-color: #921c1e; }
        .no-subjects { color: #666; font-style: italic; }
        .notice { margin: 20px 0; padding: 10px; border-radius: 4px; }
        .notice-success { background: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; }
        .notice-error { background: #f2dede; border: 1px solid #ebccd1; color: #a94442; }
        .delete-message.success { color: #3c763d; }
        .delete-message.error { color: #a94442; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        $('.delete-subject-btn').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var subjectId = $button.data('subject-id');
            var eduCenterId = $button.data('edu-center-id'); // Get educational_center_id from button
            var nonce = $button.data('nonce');
            var $row = $button.closest('tr');
            var $message = $row.find('.delete-message');

            if (!confirm('Are you sure you want to delete this subject?')) {
                return;
            }

            $button.prop('disabled', true);
            $message.hide().removeClass('success error').text('Deleting...').show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'delete_subject',
                    subject_id: subjectId,
                    educational_center_id: eduCenterId, // Pass educational_center_id to AJAX
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $message.text(response.data).addClass('success').show();
                        setTimeout(function() {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                if ($('.subjects-table tbody tr').length === 0) {
                                    $('.subjects-table').replaceWith('<p class="no-subjects">No subjects found for this educational center.</p>');
                                }
                            });
                        }, 1000);
                    } else {
                        $message.text(response.data).addClass('error').show();
                        $button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $message.text('Error occurred while deleting: ' + error).addClass('error').show();
                    $button.prop('disabled', false);
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Shortcode for frontend use
add_shortcode('delete_subjects_crud_frontend', 'delete_subjects_manager_page');