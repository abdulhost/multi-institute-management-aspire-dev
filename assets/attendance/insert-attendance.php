<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Create subjects table
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        subject_id mediumint(9) NOT NULL AUTO_INCREMENT,
        education_center_id varchar(255) NOT NULL,
        subject_name VARCHAR(100) NOT NULL,
        PRIMARY KEY (subject_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Insert some default subjects if table is empty
    if ($wpdb->get_var("SELECT COUNT(*) FROM $table_name") == 0) {
        $default_subjects = ['Mathematics', 'Science', 'English', 'History', 'Physical Education'];
        foreach ($default_subjects as $subject) {
            $wpdb->insert($table_name, ['subject_name' => $subject], ['%s']);
        }
    }

// Enqueue scripts and styles
function enqueue_attendance_entry_scripts() {
    // wp_enqueue_style('attendance-entry-css', plugins_url('css/attendance-entry.css', __FILE__));
    wp_enqueue_script('attendance-entry-js', plugins_url('js/attendance-entry.js', __FILE__), array('jquery'), '1.3', true);
    wp_localize_script('attendance-entry-js', 'attendance_entry_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('attendance_entry_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_attendance_entry_scripts');

// AJAX handler for fetching students based on class and section
add_action('wp_ajax_fetch_students_for_attendance', 'fetch_students_for_attendance');
function fetch_students_for_attendance() {
    check_ajax_referer('attendance_entry_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $class = sanitize_text_field($_POST['class'] ?? '');
    $section = sanitize_text_field($_POST['section'] ?? '');
    $educational_center_id = get_educational_center_data();

    if (!$educational_center_id || empty($class) || empty($section)) {
        wp_send_json_error('Missing required fields.');
    }

    $query = $wpdb->prepare(
        "SELECT DISTINCT student_id, student_name 
         FROM $table_name 
         WHERE education_center_id = %s AND class = %s AND section = %s",
        $educational_center_id, $class, $section
    );
    $students = $wpdb->get_results($query);

    wp_send_json_success($students ?: array());
}

// Handle form submission via AJAX
add_action('wp_ajax_submit_attendance', 'submit_attendance');
function submit_attendance() {
    check_ajax_referer('attendance_entry_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';

    $educational_center_id = get_educational_center_data();
    $teacher_id = get_current_teacher_id();
    $class = sanitize_text_field($_POST['class'] ?? '');
    $section = sanitize_text_field($_POST['section'] ?? '');
    $date = sanitize_text_field($_POST['date'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $attendance_data = isset($_POST['attendance']) ? (array) $_POST['attendance'] : [];
    $selected_students = isset($_POST['selected_students']) ? (array) $_POST['selected_students'] : [];

    if (!$educational_center_id || !$teacher_id || empty($class) || empty($section) || empty($date) || empty($subject) || empty($selected_students)) {
        wp_send_json_error('Missing required fields or no students selected.');
    }

    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        wp_send_json_error('Invalid date format.');
    }

    foreach ($selected_students as $student_id) {
        $student_id = sanitize_text_field($student_id);
        $status = sanitize_text_field($attendance_data[$student_id] ?? 'Present'); // Default only if explicitly selected
        $student_name = sanitize_text_field($_POST['student_names'][$student_id] ?? 'Unknown');

        if (!in_array($status, ['Present', 'Late', 'Absent', 'Full Day', 'Holiday'])) {
            continue;
        }

        $wpdb->insert(
            $table_name,
            array(
                'education_center_id' => $educational_center_id,
                'student_id' => $student_id,
                'student_name' => $student_name,
                'class' => $class,
                'section' => $section,
                'date' => $date,
                'status' => $status,
                'teacher_id' => $teacher_id,
                'subject' => $subject
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    wp_send_json_success('Attendance recorded successfully for selected students.');
}




// Frontend form shortcode
function display_attendance_entry_form() {
    if (!is_user_logged_in()) {
        return '<p class="form-message form-error">You must be logged in to record attendance.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $subjects_table = $wpdb->prefix . 'subjects';
    $educational_center_id = get_educational_center_data();

    if (!$educational_center_id) {
        return '<p class="form-message form-error">Educational center ID not found.</p>';
    }

    $classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_name WHERE education_center_id = '$educational_center_id'");
    $sections = $wpdb->get_col("SELECT DISTINCT section FROM $table_name WHERE education_center_id = '$educational_center_id'");
    $subjects = $wpdb->get_col("SELECT subject_name FROM $subjects_table");

    ob_start();
    ?>
       <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
            $active_section = 'record-attendance';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');
            ?>
      
    <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
        <h3 class="form-title">Record Attendance</h3>
        <form id="attendance-entry-form" class="form">
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="attendance-class" class="form-label">Class</label>
                    <select id="attendance-class" name="class" class="form-select" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class) : ?>
                            <option value="<?php echo esc_attr($class); ?>"><?php echo esc_html($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group-half">
                    <label for="attendance-section" class="form-label">Section</label>
                    <select id="attendance-section" name="section" class="form-select" required>
                        <option value="">Select Section</option>
                        <?php foreach ($sections as $section) : ?>
                            <option value="<?php echo esc_attr($section); ?>"><?php echo esc_html($section); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="attendance-date" class="form-label">Date</label>
                    <input type="date" id="attendance-date" name="date" class="form-input" required>
                </div>
                <div class="form-group form-group-half">
                    <label for="attendance-subject" class="form-label">Subject</label>
                    <select id="attendance-subject" name="subject" class="form-select" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject) : ?>
                            <option value="<?php echo esc_attr($subject); ?>"><?php echo esc_html($subject); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="students-list" class="form-group form-group-full">
                <p class="form-message">Select class and section to load students.</p>
            </div>
            <div class="form-row">
                <button type="submit" id="submit-attendance" class="form-button form-button-primary">Save Attendance</button>
            </div>
            <div id="attendance-message" class="form-message"></div>
        </form>
    </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('attendance_entry_form', 'display_attendance_entry_form');