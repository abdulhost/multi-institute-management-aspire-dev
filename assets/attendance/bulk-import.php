<?php


if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
add_shortcode('bulk_import_attendance', 'bulk_import_attendance_shortcode');
function bulk_import_attendance_shortcode($atts) {
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
    // $current_teacher_id = function_exists('get_current_teacher_id') ? get_current_teacher_id() : get_current_user_id();
    // if (!$current_teacher_id) {
    //     return '<p>Unable to identify teacher. Please log in again.</p>';
    // }

    // $educational_center_id = get_educational_center_data();


    if (!$educational_center_id) {
        return 'Please Login Again';   }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $output = '';

    // Default message and type (if not set via GET)
    $message = '';
    $type = 'success';

    // Display any stored messages from submission with auto-hide
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'success';
        $output .= "<div class='attendance-message $type' data-auto-hide='3000'>$message</div>";
    }

    // Handle bulk upload
    if (isset($_POST['bulk_attendance_action']) && wp_verify_nonce($_POST['bulk_attendance_nonce'], 'bulk_attendance_submit')) {
        if (!empty($_FILES['attendance_file']['name']) && !empty($_POST['bulk_class']) && !empty($_POST['bulk_section']) && !empty($_POST['bulk_subject'])) {
            $file = $_FILES['attendance_file']['tmp_name'];
            $bulk_class = sanitize_text_field($_POST['bulk_class']);
            $bulk_section = sanitize_text_field($_POST['bulk_section']);
            $bulk_subject = sanitize_text_field($_POST['bulk_subject']);

            // Process CSV file
            if (($handle = fopen($file, 'r')) !== false) {
                $row = 0;
                $success_count = 0;
                $update_count = 0;
                $error_count = 0;
                $error_details = []; // Store detailed errors for display

                // Read header row to get date columns
                $header = fgetcsv($handle, 1000, ',');
                if (count($header) < 3) {
                    $output .= '<div class="attendance-message error" data-auto-hide="3000">Invalid CSV format: Missing headers.</div>';
                    fclose($handle);
                    ob_start();
                    ?>
                    <div class="attendance-frontend">
                        <?php echo $output; ?>
                    </div>
                    <?php
                    return ob_get_clean();
                }

                $date_columns = array_slice($header, 2); // Skip student_id and student_name

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $row++;
                    if (count($data) < 3) {
                        $error_count++;
                        $error_details[] = "Row $row: Invalid format (insufficient columns).";
                        continue;
                    }

                    $student_id = sanitize_text_field(trim($data[0]));
                    $student_name = sanitize_text_field(trim($data[1]));

                    if (empty($student_id) || empty($student_name)) {
                        $error_count++;
                        $error_details[] = "Row $row: Missing student_id or student_name.";
                        continue;
                    }

                    // Process each date and status pair
                    for ($i = 0; $i < count($date_columns); $i++) {
                        if (isset($data[$i + 2])) {
                            $date_str = trim($date_columns[$i]); // Date from header
                            $status = sanitize_text_field(trim($data[$i + 2]));

                            if (empty($date_str) || empty($status)) {
                                $error_count++;
                                $error_details[] = "Row $row, Date $date_str: Missing date or status.";
                                continue;
                            }

                            // Convert date to Y-m-d format (e.g., 2/1/2025 -> 2025-02-01)
                            $date = DateTime::createFromFormat('n/j/Y', $date_str);
                            if ($date === false) {
                                $error_count++;
                                $error_details[] = "Row $row, Date $date_str: Invalid date format (use MM/DD/YYYY).";
                                continue;
                            }
                            $date = $date->format('Y-m-d');

                            // Validate status
                            $valid_statuses = ['Present', 'Late', 'Absent', 'Full Day', 'Holiday'];
                            if (!in_array($status, $valid_statuses)) {
                                $status_short = strtoupper(substr($status, 0, 1));
                                $status_map = ['P' => 'Present', 'L' => 'Late', 'A' => 'Absent', 'F' => 'Full Day', 'H' => 'Holiday'];
                                $status = $status_map[$status_short] ?? 'Absent'; // Default to Absent if invalid
                            }

                            // Check if record exists
                            $existing_record = $wpdb->get_row($wpdb->prepare(
                                "SELECT sa_id FROM $table_name 
                                 WHERE education_center_id = %s 
                                 AND student_id = %s 
                                 AND date = %s 
                                 AND subject = %s",
                                $educational_center_id,
                                $student_id,
                                $date,
                                $bulk_subject
                            ));

                            $record_data = [
                                'education_center_id' => $educational_center_id,
                                'student_id' => $student_id,
                                'student_name' => $student_name,
                                'class' => $bulk_class,
                                'section' => $bulk_section,
                                'date' => $date,
                                'status' => $status,
                                'teacher_id' => $current_teacher_id,
                                'subject' => $bulk_subject
                            ];

                            if ($existing_record) {
                                // Update existing record
                                $result = $wpdb->update(
                                    $table_name,
                                    ['status' => $status],
                                    ['sa_id' => $existing_record->sa_id],
                                    ['%s'],
                                    ['%d']
                                );
                                if ($result !== false) {
                                    $update_count++;
                                } else {
                                    $error_count++;
                                    $error_details[] = "Row $row, Date $date: Failed to update - " . $wpdb->last_error;
                                }
                            } else {
                                // Insert new record
                                $result = $wpdb->insert($table_name, $record_data);
                                if ($result !== false) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                    $error_details[] = "Row $row, Date $date: Failed to insert - " . $wpdb->last_error;
                                }
                            }
                        }
                    }
                }
                fclose($handle);

                $message = "Bulk upload complete: $success_count inserted, $update_count updated, $error_count errors.";
                $type = $error_count > 0 ? 'error' : 'success';
                
                // Display detailed errors below the form if any
                if ($error_count > 0) {
                    $output .= '<h3>Import Errors:</h3>';
                    $output .= '<ul class="error-details">';
                    foreach ($error_details as $error) {
                        $output .= '<li>' . esc_html($error) . '</li>';
                    }
                    $output .= '</ul>';
                }

                // Redirect to the same page for a fresh start
                $redirect_url = $_SERVER['REQUEST_URI'];
                wp_redirect($redirect_url);
                exit;
            } else {
                $output .= '<div class="attendance-message error" data-auto-hide="3000">Failed to open the uploaded file.</div>';
            }
        } else {
            $output .= '<div class="attendance-message error" data-auto-hide="3000">Please upload a file and fill all fields.</div>';
        }
    }

    ob_start();
    ?>
     <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
              if (is_teacher($atts)) { 
            } else {
                echo render_admin_header(wp_get_current_user());
                if (!is_center_subscribed($educational_center_id)) {
                    return render_subscription_expired_message($educational_center_id);
                }
            $active_section = 'bulk-upload-attendance';
            $main_section = 'student';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');  }
            ?>
    <div class="attendance-frontend">
        <?php echo $output; ?>

        <!-- Bulk Upload Form -->
        <h2>Bulk Import Attendance</h2>
        <form method="post" class="attendance-bulk-form" action="<?php echo esc_url(get_permalink()); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('bulk_attendance_submit', 'bulk_attendance_nonce'); ?>
            <div class="form-group">
                <label for="bulk_class">Class:</label>
                <input type="text" name="bulk_class" id="bulk_class" required placeholder="e.g., 10A">
            </div>
            <div class="form-group">
                <label for="bulk_section">Section:</label>
                <input type="text" name="bulk_section" id="bulk_section" required placeholder="e.g., A">
            </div>
            <div class="form-group">
                <label for="bulk_subject">Subject:</label>
                <select name="bulk_subject" id="bulk_subject" required>
                    <option value="">-- Select Subject --</option>
                    <?php
                    $subjects = $wpdb->get_results($wpdb->prepare(
                        "SELECT subject_id, subject_name 
                         FROM {$wpdb->prefix}subjects 
                         WHERE education_center_id = %s 
                         ORDER BY subject_id ASC",
                        $educational_center_id
                    ));
                    foreach ($subjects as $subject) : ?>
                        <option value="<?php echo esc_attr($subject->subject_name); ?>">
                            <?php echo esc_html($subject->subject_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="attendance_file">Upload CSV File:</label>
                <input type="file" name="attendance_file" id="attendance_file" accept=".csv" required>
                <p><small>Format: student_id, student_name, followed by dates (e.g., 2/1/2025, 3/2/2025) and status (P/A/L/F/H or full names like Present/Absent)</small></p>
                <div class="upload-progress" style="display: none;">Uploading... <span class="progress-text"></span></div>
            </div>
            <input type="submit" name="bulk_attendance_action" value="Import Attendance" class="button button-primary">
        </form>

        <!-- Detailed Results (if errors or after upload) -->
        <?php if (isset($_GET['type']) && isset($_GET['message'])): ?>
            <div class="import-results">
                <h3>Import Summary:</h3>
                <p><?php echo esc_html(sanitize_text_field($_GET['message'])); ?></p>
                <?php if ($_GET['type'] === 'error' && !empty($error_details)): ?>
                    <h4>Errors Details:</h4>
                    <ul class="error-details">
                        <?php foreach ($error_details as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>
    <?php
    $content = ob_get_clean();

    // Localize message and type for JavaScript
    $message_data = array(
        'message' => $message,
        'type' => $type,
        'ajax_url' => admin_url('admin-ajax.php')
    );
    wp_enqueue_style('bulk-attendance-style', plugin_dir_url(__FILE__) . 'bulk-attendance.css', array(), '1.3');
    wp_add_inline_style('bulk-attendance-style', "
        .attendance-frontend {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #0073aa;
            outline: none;
            box-shadow: 0 0 5px rgba(0,115,170,0.3);
        }
        .form-group input::placeholder {
            color: #999;
        }
        .button {
            padding: 12px 24px;
            background: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #005177;
        }
        .attendance-message, .import-results {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .attendance-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .attendance-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .import-results h3, .import-results h4 {
            margin-top: 0;
            color: #333;
        }
        .error-details {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .error-details li {
            padding: 8px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-bottom: 5px;
            color: #721c24;
        }
        .upload-progress {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            display: none;
        }
        .upload-progress .progress-text {
            font-weight: bold;
        }
    ");

    // Add JavaScript for progress indicator and auto-hide messages
    wp_enqueue_script('bulk-attendance-script', plugin_dir_url(__FILE__) . 'bulk-attendance.js', array('jquery'), '1.3', true);
    wp_localize_script('bulk-attendance-script', 'bulkAttendanceData', $message_data);
    wp_add_inline_script('bulk-attendance-script', "
        jQuery(document).ready(function($) {
            // Auto-hide messages after 3 seconds
            $('.attendance-message[data-auto-hide]').each(function() {
                var $message = $(this);
                setTimeout(function() {
                    $message.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, $(this).data('auto-hide'));
            });

            $('.attendance-bulk-form').on('submit', function(e) {
                var form = $(this);
                var progress = $('.upload-progress');
                progress.show();
                progress.find('.progress-text').text('Processing... 0%');

                // Simulate progress (replace with actual progress tracking if needed)
                var progressInterval = setInterval(function() {
                    var currentProgress = parseInt(progress.find('.progress-text').text().replace('Processing... ', '').replace('%', ''));
                    if (currentProgress < 100) {
                        progress.find('.progress-text').text('Processing... ' + (currentProgress + 10) + '%');
                    } else {
                        clearInterval(progressInterval);
                    }
                }, 500);

                // Optional: Add actual file upload progress via AJAX if needed
                // For now, rely on the redirect for final feedback
            });
        });
    ");

    return $content;
}