<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue scripts and styles
function enqueue_attendance_entry_scripts2() {
    wp_enqueue_script('attendance-entry2-js', plugins_url('attendance.js', __FILE__), array('jquery'), '1.3', true);
    
    global $wpdb;
    $educational_center_id = educational_center_teacher_id(); 
    $class_sections_table = $wpdb->prefix . 'class_sections';
    $class_sections = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $class_sections_table WHERE education_center_id = %s",
            $educational_center_id
        ),
        ARRAY_A
    );
    
    $sections_data = [];
    foreach ($class_sections as $row) {
        $sections_data[$row['class_name']] = explode(',', $row['sections']);
    }

    wp_localize_script('attendance-entry2-js', 'attendance_entry2_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('attendance_entry_nonce'),
        'sections_data' => $sections_data,
        'debug' => WP_DEBUG // Enable debug in development
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_attendance_entry_scripts2');

// AJAX handler for fetching students
add_action('wp_ajax_fetch_students_for_attendance2', 'fetch_students_for_attendance2');
function fetch_students_for_attendance2() {
    check_ajax_referer('attendance_entry_nonce', 'nonce');

    $class = sanitize_text_field($_POST['class'] ?? '');
    $section = sanitize_text_field($_POST['section'] ?? '');
    $educational_center_id = educational_center_teacher_id();

    if (empty($class) || empty($section) || !$educational_center_id) {
        wp_send_json_error('Missing required fields.');
        return;
    }

    $args = array(
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array('key' => 'class', 'value' => $class, 'compare' => '='),
            array('key' => 'section', 'value' => $section, 'compare' => '='),
            array('key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=')
        )
    );

    $students_query = new WP_Query($args);
    $students = array();

    if ($students_query->have_posts()) {
        while ($students_query->have_posts()) {
            $students_query->the_post();
            $students[] = array(
                'student_id' => get_field('student_id'),
                'student_name' => get_the_title()
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($students ?: []);
}

// Handle form submission via AJAX
add_action('wp_ajax_submit_attendance2', 'submit_attendance2');
function submit_attendance2() {
    check_ajax_referer('attendance_entry_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance'; 

    $educational_center_id = educational_center_teacher_id();
    $teacher_id = aspire_get_current_teacher_id();
    $class = sanitize_text_field($_POST['class'] ?? '');
    $section = sanitize_text_field($_POST['section'] ?? '');
    $date = sanitize_text_field($_POST['date'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $attendance_data = isset($_POST['attendance']) ? (array) $_POST['attendance'] : [];
    $selected_students = isset($_POST['selected_students']) ? (array) $_POST['selected_students'] : [];

    if (!$educational_center_id || !$teacher_id || empty($class) || empty($section) || empty($date) || empty($subject) || empty($selected_students)) {
        wp_send_json_error('Missing required fields or no students selected.');
        return;
    }

    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        wp_send_json_error('Invalid date format.');
        return;
    }

    $valid_statuses = ['Present', 'Late', 'Absent', 'Full Day', 'Holiday'];
    
    foreach ($selected_students as $student_id) {
        $student_id = sanitize_text_field($student_id);
        $status = sanitize_text_field($attendance_data[$student_id] ?? 'Present');
        $student_name = sanitize_text_field($_POST['student_names'][$student_id] ?? 'Unknown');

        if (!in_array($status, $valid_statuses)) {
            continue;
        }

        $result = $wpdb->insert(
            $table_name,
            [
                'education_center_id' => $educational_center_id,
                'student_id' => $student_id,
                'student_name' => $student_name,
                'class' => $class,
                'section' => $section,
                'date' => $date,
                'status' => $status,
                'teacher_id' => $teacher_id,
                'subject' => $subject
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error('Database error occurred: ' . $wpdb->last_error);
            return;
        }
    }

    wp_send_json_success('Attendance recorded successfully for selected students.');
}

// Frontend form
function render_teacher_attendance_add($user_id, $teacher) {
    if (!is_user_logged_in()) {
        return '<p class="form-message form-error">You must be logged in to record attendance.</p>';
    }

    global $wpdb;
    $subjects_table = $wpdb->prefix . 'subjects';
    $class_sections_table = $wpdb->prefix . 'class_sections';
    $educational_center_id = educational_center_teacher_id();
    
    if (!$educational_center_id) {
        return '<p class="form-message form-error">Educational center ID not found.</p>';
    }

    $class_sections = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $class_sections_table WHERE education_center_id = %s",
            $educational_center_id
        ),
        ARRAY_A
    );
    
    $subjects = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT subject_name FROM $subjects_table WHERE education_center_id = %s",
            $educational_center_id
        )
    );

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <h3 class="form-title">Record Attendance</h3>
            <form id="attendance-entry-form" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="class_name">Class:</label>
                        <select name="class" id="class_nameadd" required>
                            <option value="">Select Class</option>
                            <?php foreach ($class_sections as $row) : ?>
                                <option value="<?php echo esc_attr($row['class_name']); ?>">
                                    <?php echo esc_html($row['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="section">Section:</label>
                        <select name="section" id="sectionadd" required disabled>
                            <option value="">Select Class First</option>
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
                                <option value="<?php echo esc_attr($subject); ?>">
                                    <?php echo esc_html($subject); ?>
                                </option>
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

//edit

