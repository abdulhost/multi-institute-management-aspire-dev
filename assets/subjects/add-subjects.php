<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission directly in the display function for simplicity
function display_subjects_add() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $message = ''; // Store feedback here

    // Process form submission
    if (isset($_POST['subject']) && isset($_POST['subject_nonce']) && wp_verify_nonce($_POST['subject_nonce'], 'add_subject_action')) {
        $subject_name = sanitize_text_field($_POST['subject']);
            $current_user = wp_get_current_user();
            $admin_id = $current_user->user_login;
        
            $educational_center = get_posts(array(
                'post_type' => 'educational-center',
                'meta_key' => 'admin_id',
                'meta_value' => $admin_id,
                'posts_per_page' => 1,
            ));
        
            if (empty($educational_center)) {
                return "<p>No Educational Center found for this Admin ID.</p>";
            }
        
        // Debug: Log the raw data
        error_log('Form submitted. Subject: ' . $subject_name);
        error_log('Educational Center Data: ' . print_r($educational_center, true));

        if (empty($educational_center) || !isset($educational_center[0]->ID)) {
            $message = '<div class="notice notice-error is-dismissible"><p>No Educational Center found for this Admin ID.</p></div>';
            error_log('No educational center found for user: ' . get_current_user_id());
        } else {
            $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);

            // Debug: Log the ID
            error_log('Educational Center ID: ' . ($educational_center_id ?: 'Not found'));

            if (empty($educational_center_id)) {
                $message = '<div class="notice notice-error is-dismissible"><p>Invalid or missing Educational Center ID.</p></div>';
            } else {
                $data = array(
                    'education_center_id' => $educational_center_id,
                    'subject_name' => $subject_name,
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
            }
        }
          // Redirect to the same page
    wp_redirect(home_url('/institute-dashboard/add-subjects'));
    exit;
    }

    // Output the form
    ?>

<div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
            $active_section = 'add-subject';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');
            ?>
    <div class="wrap subjects-wrapper">
        <h1>Add New Subject</h1>
        <?php echo $message; // Display feedback ?>
        <div class="subjects-content">
            <form method="post" action="" id="add-subject">
                <?php wp_nonce_field('add_subject_action', 'subject_nonce'); ?>
                <table class="form-table">
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
    <style>
        .subjects-wrapper { padding: 20px; }
        .subjects-content { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 20px; }
        .form-table { margin: 20px 0; }
        .regular-text { width: 25em; }
    </style>
    <?php
}

// Optional: Keep as shortcode if you want to use it on frontend
add_shortcode('subjects_add', 'display_subjects_add');