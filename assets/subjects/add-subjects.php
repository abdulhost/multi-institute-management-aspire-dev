<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission directly in the display function for simplicity
function display_subjects_add($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $message = ''; // Store feedback here

    // Check if user is logged in
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

    // Process form submission
    if (isset($_POST['subject']) && isset($_POST['subject_nonce']) && wp_verify_nonce($_POST['subject_nonce'], 'add_subject_action')) {
        $subject_name = sanitize_text_field($_POST['subject']);
        $teacher_id = sanitize_text_field($_POST['teacher_id']);
        
        // Debug: Log the raw data
        error_log('Form submitted. Subject: ' . $subject_name);
        error_log('Educational Center ID from logic: ' . $educational_center_id);

        $data = array(
            'education_center_id' => $educational_center_id,
            'subject_name' => $subject_name,
            'teacher_id' => $teacher_id,
        );
        $format = array('%s', '%s');

        // Debug: Log the data before insertion
        error_log('Inserting into DB: ' . print_r($data, true));

        $insert_result = $wpdb->insert($table_name, $data, $format);

        if ($insert_result === false) {
            $message = '<div class="notice notice-error is-dismissible"><p>Error adding subject: ' . esc_html($wpdb->last_error) . '</p></div>';
            error_log('DB Insert Failed: ' . $wpdb->last_error);
        } elseif ($insert_result === 0) {
            $message = '<div class="notice notice-warning is-dismissible"><p>No rows affected. Subject might already exist.</p></div>';
        } else {
            $message = '<div class="notice notice-success is-dismissible"><p>Subject added successfully!</p></div>';
        }
// Redirect to the same page
wp_redirect($_SERVER['REQUEST_URI']);
exit;

    }

    // Output the form
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
        $active_section = 'add-subject';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');}
        ?>
        <div class="wrap subjects-wrapper">
            <h1>Add New Subject</h1>
            <?php echo $message; // Display feedback ?>
            <div class="subjects-content">
                <form method="post" action="" id="add-subject">
                    <?php wp_nonce_field('add_subject_action', 'subject_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="edu_center_id">Education Center ID</label></th>
                            <td><input name="edu_center_id" type="text" id="edu_center_id" value="<?php echo esc_attr($educational_center_id); ?>" class="regular-text" readonly></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="teacher_id">Teacher ID</label></th>
                            <td><input name="teacher_id" type="text" id="teacher_id" value="<?php echo esc_attr($current_teacher_id); ?>" class="regular-text" readonly></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="subject">Subject</label></th>
                            <td><input name="subject" type="text" id="subject" value="" class="regular-text" required></td>
                        </tr>
                    </table>
                    <?php submit_button('Add Subject', 'primary'); ?>
                </form>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

// Optional: Keep as shortcode if you want to use it on frontend
add_shortcode('subjects_add', 'display_subjects_add');