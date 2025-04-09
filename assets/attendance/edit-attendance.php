<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
add_shortcode('edit_attendance', 'edit_attendance_frontend_shortcode');
function edit_attendance_frontend_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    
    if (!$educational_center_id) {
        return 'Please Login Again';
       }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $output = '';

    // Display any stored messages from submission
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'success';
        $output .= "<div class='attendance-message $type'>$message</div>";
    }

    // Get students from 'students' post type
    $args = array(
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'educational_center_id',
                'value' => $educational_center_id,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $students_query = new WP_Query($args);
    $students = array();

    if ($students_query->have_posts()) {
        while ($students_query->have_posts()) {
            $students_query->the_post();
            $students[] = (object) array(
                'student_id' => get_field('student_id'),
                'student_name' => get_the_title(),
                'class' => get_field('class'),
                'section' => get_field('section')
            );
        }
        wp_reset_postdata();
    }

    $selected_student = isset($_POST['select_student_id']) ? sanitize_text_field($_POST['select_student_id']) : '';
    $selected_month = isset($_POST['select_month']) ? sanitize_text_field($_POST['select_month']) : '';
    $selected_date = isset($_POST['select_date']) ? sanitize_text_field($_POST['select_date']) : '';

    // Get edit record only when "Load Record" is clicked
    $edit_record = null;
    if (isset($_POST['select_attendance_record']) && $selected_student && $selected_date) {
        $edit_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE student_id = %s 
             AND date = %s 
             AND education_center_id = %s",
            $selected_student,
            $selected_date,
            $educational_center_id
        ));
        if (!$edit_record) {
            $output .= '<div class="attendance-message error">No record found for student ' . esc_html($selected_student) . ' on ' . esc_html($selected_date) . '</div>';
        }
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
            $active_section = 'update-attendance';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');
        }
        ?>
        <div class="attendance-frontend">
            <?php echo $output; ?>

            <!-- Selection Form -->
            <form method="post" class="attendance-select-form" action="">
                <h2>Select Student, Month, and Date</h2>
                <div class="form-group">
                    <label for="select_student_id">Select Student:</label>
                    <select name="select_student_id" id="select_student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $student) : ?>
                            <option value="<?php echo esc_attr($student->student_id); ?>" 
                                    <?php selected($selected_student, $student->student_id); ?>>
                                <?php echo esc_html("{$student->student_name} ({$student->student_id} - {$student->class} {$student->section})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="month_select_container" style="<?php echo $selected_student ? '' : 'display: none;'; ?>">
                    <label for="select_month">Filter by Month (Optional):</label>
                    <select name="select_month" id="select_month">
                        <option value="">-- All Months --</option>
                        <?php if ($selected_student) : ?>
                            <?php
                            $months = $wpdb->get_results($wpdb->prepare(
                                "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month 
                                 FROM $table_name 
                                 WHERE student_id = %s 
                                 AND education_center_id = %s 
                                 ORDER BY month DESC",
                                $selected_student,
                                $educational_center_id
                            ));
                            foreach ($months as $month) : ?>
                                <option value="<?php echo esc_attr($month->month); ?>" 
                                        <?php selected($selected_month, $month->month); ?>>
                                    <?php echo esc_html(date('F Y', strtotime($month->month . '-01'))); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group" id="date_select_container" style="<?php echo $selected_student ? '' : 'display: none;'; ?>">
                    <label for="select_date">Select Date:</label>
                    <select name="select_date" id="select_date" required>
                        <option value="">-- Select Date --</option>
                        <?php if ($selected_student) : ?>
                            <?php
                            $query = "SELECT date, status 
                                      FROM $table_name 
                                      WHERE student_id = %s 
                                      AND education_center_id = %s";
                            $params = [$selected_student, $educational_center_id];
                            if ($selected_month) {
                                $query .= " AND DATE_FORMAT(date, '%Y-%m') = %s";
                                $params[] = $selected_month;
                            }
                            $query .= " ORDER BY date DESC";
                            $dates = $wpdb->get_results($wpdb->prepare($query, $params));
                            foreach ($dates as $date) : ?>
                                <option value="<?php echo esc_attr($date->date); ?>" 
                                        <?php selected($selected_date, $date->date); ?>>
                                    <?php echo esc_html("{$date->date} ({$date->status})"); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <input type="submit" name="select_attendance_record" value="Load Record" class="button">
            </form>

            <!-- Edit Form -->
            <form method="post" class="attendance-update-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="edit_attendance_submit">
                <?php wp_nonce_field('edit_attendance_submit', 'edit_attendance_nonce'); ?>
                <h2><?php echo $edit_record ? 'Edit Attendance' : 'Add New Attendance'; ?></h2>
                <?php if ($edit_record) : ?>
                    <input type="hidden" name="attendance_id" value="<?php echo esc_attr($edit_record->sa_id); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="edu_center_id">Education Center ID:</label>
                    <input type="text" name="edu_center_id" id="edu_center_id" 
                           value="<?php echo esc_attr($edit_record->education_center_id ?? $educational_center_id); ?>" 
                           readonly required>
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID:</label>
                    <input type="text" name="student_id" id="student_id" 
                           value="<?php echo esc_attr($edit_record->student_id ?? ($selected_student ?? '')); ?>" 
                           <?php echo $edit_record ? 'readonly' : ''; ?> required>
                </div>

                <div class="form-group">
                    <label for="student_name">Student Name:</label>
                    <input type="text" name="student_name" id="student_name" 
                           value="<?php echo esc_attr($edit_record->student_name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="class_name">Class:</label>
                    <input type="text" name="class_name" id="class_name" 
                           value="<?php echo esc_attr($edit_record->class ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="section_name">Section:</label>
                    <input type="text" name="section_name" id="section_name" 
                           value="<?php echo esc_attr($edit_record->section ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="attendance_date">Date:</label>
                    <input type="date" name="attendance_date" id="attendance_date" 
                           value="<?php echo esc_attr($edit_record->date ?? ($selected_date ?? '')); ?>" 
                           <?php echo $edit_record ? 'readonly' : ''; ?> required>
                </div>

                <div class="form-group">
                    <label for="attendance_status">Status:</label>
                    <select name="attendance_status" id="attendance_status" required>
                        <option value="Present" <?php selected($edit_record->status ?? '', 'Present'); ?>>Present</option>
                        <option value="Late" <?php selected($edit_record->status ?? '', 'Late'); ?>>Late</option>
                        <option value="Absent" <?php selected($edit_record->status ?? '', 'Absent'); ?>>Absent</option>
                        <option value="Full Day" <?php selected($edit_record->status ?? '', 'Full Day'); ?>>Full Day</option>
                        <option value="Holiday" <?php selected($edit_record->status ?? '', 'Holiday'); ?>>Holiday</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_name">Subject:</label>
                    <input type="text" name="subject_name" id="subject_name" 
                           value="<?php echo esc_attr($edit_record->subject ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="teacher_id">Teacher ID:</label>
                    <input type="text" name="teacher_id" id="teacher_id" 
                           value="<?php echo esc_attr($current_teacher_id); ?>" readonly required>
                </div>

                <input type="submit" name="edit_attendance_action" value="<?php echo $edit_record ? 'Update Attendance' : 'Add Attendance'; ?>" class="button button-primary">
            </form>
        </div>
    </div>
    <?php
    $content = ob_get_clean();

    wp_enqueue_script('attendance-frontend-script');
    wp_localize_script('attendance-frontend-script', 'attendanceData', array(
        'current_teacher_id' => $current_teacher_id,
        'educational_center_id' => $educational_center_id,
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    return $content;
}

// Handle admin-post submission
add_action('admin_post_edit_attendance_submit', 'handle_edit_attendance_submission');
function handle_edit_attendance_submission() {
    if (!isset($_POST['edit_attendance_nonce']) || !wp_verify_nonce($_POST['edit_attendance_nonce'], 'edit_attendance_submit')) {
        wp_die('Security check failed.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $message = '';
    $type = 'success';

    $data = [
        'education_center_id' => sanitize_text_field($_POST['edu_center_id']),
        'student_id' => sanitize_text_field($_POST['student_id']),
        'student_name' => sanitize_text_field($_POST['student_name']),
        'class' => sanitize_text_field($_POST['class_name']),
        'section' => sanitize_text_field($_POST['section_name']),
        'date' => sanitize_text_field($_POST['attendance_date']),
        'status' => sanitize_text_field($_POST['attendance_status']),
        'teacher_id' => sanitize_text_field($_POST['teacher_id']),
        'subject' => sanitize_text_field($_POST['subject_name'])
    ];

    // Get these values from POST since they're now consistently available
    $educational_center_id = sanitize_text_field($_POST['edu_center_id']);
    $current_teacher_id = sanitize_text_field($_POST['teacher_id']);

    if ($data['education_center_id'] !== $educational_center_id) {
        $message = 'Invalid educational center ID.';
        $type = 'error';
    } else {
        if (!empty($_POST['attendance_id'])) {
            $sa_id = intval($_POST['attendance_id']);
            $result = $wpdb->update(
                $table_name,
                $data,
                ['sa_id' => $sa_id],
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );
            if ($result === false) {
                $message = 'Update failed: ' . $wpdb->last_error;
                $type = 'error';
            } elseif ($result === 0) {
                $message = 'No changes made to attendance record.';
                $type = 'error';
            } else {
                $message = 'Attendance updated successfully!';
            }
        } else {
            $result = $wpdb->insert($table_name, $data);
            if ($result === false) {
                $message = 'Insert failed: ' . $wpdb->last_error;
                $type = 'error';
            } else {
                $message = 'Attendance added successfully!';
            }
        }
    }

    // Redirect back with message
    // $redirect_url = add_query_arg(
    //     array('message' => urlencode($message), 'type' => $type),
    //     // wp_get_referer() ?: home_url('/institute-dashboard/edit-attendance/')
    //     // Redirect to the same page

    // );
    wp_redirect($_SERVER['REQUEST_URI']);

    // wp_safe_redirect($redirect_url);
    exit;
}

// Enqueue frontend styles and scripts
add_action('wp_enqueue_scripts', 'attendance_frontend_scripts');
function attendance_frontend_scripts() {
    if (!is_admin()) {
        wp_enqueue_style(
            'attendance-frontend-style',
            plugin_dir_url(__FILE__) . 'frontend-attendance.css',
            array(),
            '1.4'
        );

        $custom_css = "
            .attendance-frontend {
                margin: 20px auto;
                padding: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
            }
            .form-group input, .form-group select {
                width: 100%;
                padding: 8px;
                box-sizing: border-box;
            }
            .form-group input[readonly] {
                background: #f5f5f5;
                cursor: not-allowed;
            }
            .button {
                padding: 10px 20px;
                background: #0073aa;
                color: #fff;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            }
            .button:hover {
                background: #005177;
            }
            .attendance-message {
                padding: 10px;
                margin-bottom: 15px;
                border-radius: 3px;
            }
            .attendance-message.success {
                background: #dff0d8;
                color: #3c763d;
                border: 1px solid #d6e9c6;
            }
            .attendance-message.error {
                background: #f2dede;
                color: #a94442;
                border: 1px solid #ebccd1;
            }
        ";
        wp_add_inline_style('attendance-frontend-style', $custom_css);

        wp_enqueue_script(
            'attendance-frontend-script',
            plugin_dir_url(__FILE__) . 'frontend-attendance.js',
            array('jquery'),
            '1.4',
            true
        );

        wp_add_inline_script('attendance-frontend-script', "
            jQuery(document).ready(function($) {
                function loadDates(studentId, month) {
                    $.ajax({
                        url: attendanceData.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'get_attendance_dates',
                            student_id: studentId,
                            month: month,
                            teacher_id: attendanceData.current_teacher_id,
                            education_center_id: attendanceData.educational_center_id
                        },
                        success: function(response) {
                            if (response.success) {
                                var dates = response.data;
                                var \$dateSelect = $('#select_date');
                                \$dateSelect.empty().append('<option value=\"\">-- Select Date --</option>');
                                $.each(dates, function(index, date) {
                                    \$dateSelect.append('<option value=\"' + date.date + '\">' + date.date + ' (' + date.status + ')</option>');
                                });
                                $('#date_select_container').show();
                            }
                        }
                    });
                }

                $('#select_student_id').on('change', function() {
                    var studentId = $(this).val();
                    if (studentId) {
                        $.ajax({
                            url: attendanceData.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'get_attendance_months',
                                student_id: studentId,
                                teacher_id: attendanceData.current_teacher_id,
                                education_center_id: attendanceData.educational_center_id
                            },
                            success: function(response) {
                                if (response.success) {
                                    var months = response.data;
                                    var \$monthSelect = $('#select_month');
                                    \$monthSelect.empty().append('<option value=\"\">-- All Months --</option>');
                                    $.each(months, function(index, month) {
                                        \$monthSelect.append('<option value=\"' + month.month + '\">' + month.display + '</option>');
                                    });
                                    $('#month_select_container').show();
                                    loadDates(studentId, '');
                                }
                            }
                        });
                    } else {
                        $('#month_select_container, #date_select_container').hide();
                    }
                });

                $('#select_month').on('change', function() {
                    var studentId = $('#select_student_id').val();
                    var month = $(this).val();
                    if (studentId) {
                        loadDates(studentId, month);
                    }
                });
            });
        ");
    }
}

// AJAX handler for getting months
add_action('wp_ajax_get_attendance_months', 'attendance_get_months_ajax');
function attendance_get_months_ajax() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    
    $student_id = sanitize_text_field($_POST['student_id']);
    $teacher_id = intval($_POST['teacher_id']);
    $education_center_id = sanitize_text_field($_POST['education_center_id']);

    $months = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month 
         FROM $table_name 
         WHERE student_id = %s 
         AND education_center_id = %s 
         ORDER BY month DESC",
        $student_id,
        $education_center_id
    ), ARRAY_A);

    foreach ($months as &$month) {
        $month['display'] = date('F Y', strtotime($month['month'] . '-01'));
    }

    wp_send_json_success($months);
}

// AJAX handler for getting dates
add_action('wp_ajax_get_attendance_dates', 'attendance_get_dates_ajax');
function attendance_get_dates_ajax() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    
    $student_id = sanitize_text_field($_POST['student_id']);
    $month = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : '';
    $teacher_id = intval($_POST['teacher_id']);
    $education_center_id = sanitize_text_field($_POST['education_center_id']);

    $query = "SELECT date, status 
              FROM $table_name 
              WHERE student_id = %s 
              AND education_center_id = %s";
    $params = [$student_id, $education_center_id];
    
    if ($month) {
        $query .= " AND DATE_FORMAT(date, '%Y-%m') = %s";
        $params[] = $month;
    }
    
    $query .= " ORDER BY date DESC";
    $dates = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

    wp_send_json_success($dates);
}