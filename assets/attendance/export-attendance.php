<?php

if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
add_shortcode('export_attendance', 'export_attendance_shortcode');
function export_attendance_shortcode($atts) {
    if (!is_user_logged_in()) {
        // wp_redirect(home_url('/login'));
        // exit();   
          }

    // $current_teacher_id = function_exists('get_current_teacher_id') ? get_current_teacher_id() : get_current_user_id();
    // if (!$current_teacher_id) {
    //     return '<p>Unable to identify teacher. Please log in again.</p>';
    // }
    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    // $educational_center_id = get_educational_center_data();
    if (!$educational_center_id) {
        wp_redirect(home_url('/login'));
        exit();     }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $output = '';
    $error = '';

    // Default message and type (if not set via GET)
    $message = '';
    $type = 'success';

    // Display any stored messages from submission with auto-hide
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'success';
        $output .= "<div class='attendance-message $type' data-auto-hide='3000'>$message</div>";
    }

    // Handle export request
    if (isset($_POST['export_attendance_action']) && wp_verify_nonce($_POST['export_attendance_nonce'], 'export_attendance_submit')) {
        $start_month = sanitize_text_field($_POST['start_month']);
        $end_month = sanitize_text_field($_POST['end_month']);
        $student_id = sanitize_text_field($_POST['student_id']);
        $class = sanitize_text_field($_POST['class']);
        $section = sanitize_text_field($_POST['section']);
        $format = sanitize_text_field($_POST['export_format']);

        // Validate date ranges
        $start_date = DateTime::createFromFormat('Y-m', $start_month);
        $end_date = DateTime::createFromFormat('Y-m', $end_month);
        if ($start_date === false || $end_date === false) {
            $error = '<div class="attendance-message error" data-auto-hide="3000">Invalid date format for month range.</div>';
        } elseif ($start_date > $end_date) {
            $error = '<div class="attendance-message error" data-auto-hide="3000">Start month cannot be after end month.</div>';
        } else {
            // Build query to get attendance records
            $query = "SELECT student_id, student_name, date, status 
                      FROM $table_name 
                      WHERE education_center_id = %s";
            $params = [$educational_center_id];

            if (!empty($student_id)) {
                $query .= " AND student_id = %s";
                $params[] = $student_id;
            }
            if (!empty($class)) {
                $query .= " AND class = %s";
                $params[] = $class;
            }
            if (!empty($section)) {
                $query .= " AND section = %s";
                $params[] = $section;
            }

            // Handle single month or range
            if ($start_month === $end_month) {
                $query .= " AND DATE_FORMAT(date, '%Y-%m') = %s";
                $params[] = $start_month;
            } else {
                $query .= " AND DATE_FORMAT(date, '%Y-%m') BETWEEN %s AND %s";
                $params[] = $start_month;
                $params[] = $end_month;
            }

            $query .= " ORDER BY student_id, date ASC";
            $results = $wpdb->get_results($wpdb->prepare($query, $params));

            if (empty($results)) {
                $error = '<div class="attendance-message error" data-auto-hide="3000">No attendance records found for the selected criteria.</div>';
            } else {
                // Prepare export data in bulk import format
                $start = clone $start_date; // Use clone to avoid modifying the original
                $start->setDate($start_date->format('Y'), $start_date->format('m'), 1); // Set to first day of the month
                $end = clone $end_date;
                $end->setDate($end_date->format('Y'), $end_date->format('m'), $end_date->format('t')); // Set to last day of the month

                $interval = new DateInterval('P1D');
                $date_range = new DatePeriod($start, $interval, $end->modify('+1 day')); // Include the last day

                $date_headers = [];
                foreach ($date_range as $date) {
                    // Use a consistent Excel-friendly date format (m/d/yyyy) to prevent #### issues
                    $date_headers[] = $date->format('m/d/yyyy');
                }

                // Group records by student and build attendance matrix
                $student_data = [];
                foreach ($results as $record) {
                    if (!isset($student_data[$record->student_id])) {
                        $student_data[$record->student_id] = [
                            'student_id' => $record->student_id,
                            'student_name' => $record->student_name,
                            'statuses' => array_fill(0, count($date_headers), '')
                        ];
                    }
                    // Convert date string to DateTime for formatting
                    $date_obj = DateTime::createFromFormat('Y-m-d', $record->date);
                    if ($date_obj !== false) {
                        $date_key = array_search($date_obj->format('m/d/yyyy'), $date_headers);
                        if ($date_key !== false) {
                            $student_data[$record->student_id]['statuses'][$date_key] = $record->status;
                        }
                    }
                }

                // Export based on format
                if ($format === 'csv') {
                    // Clear any existing output buffers to prevent theme interference
                    while (ob_get_level() > 0) {
                        ob_end_clean();
                    }

                    header('Content-Type: text/csv; charset=UTF-8'); // Explicitly set UTF-8 encoding
                    header('Content-Disposition: attachment; filename="attendance_export_' . date('Ymd') . '.csv"');
                    $output = fopen('php://output', 'w');

                    // Write header with un-padded, trimmed strings to ensure clean output
                    $header_row = ['Student ID', 'Student Name'];
                    $header_row = array_merge($header_row, array_map('trim', $date_headers));
                    fputcsv($output, $header_row, ',', '"', "\\"); // Use comma as delimiter, double quotes for strings, backslash as escape

                    // Write data with un-padded, trimmed strings to ensure clean output
                    foreach ($student_data as $student) {
                        $row = [
                            trim($student['student_id']),
                            trim($student['student_name'])
                        ];
                        $row = array_merge($row, array_map('trim', $student['statuses']));
                        fputcsv($output, $row, ',', '"', "\\"); // Use comma as delimiter, double quotes for strings, backslash as escape
                    }
                    fclose($output);
                    exit;
                } elseif ($format === 'pdf') {
                    require_once ABSPATH . 'wp-admin/includes/class-fpdf.php';
                    $pdf = new FPDF('L', 'mm', 'A4'); // Landscape for wider pages
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(0, 10, 'Attendance Export - ' . date('F Y'), 0, 1, 'C');
                    $pdf->SetFont('Arial', '', 8); // Reduced font size for better fit

                    // Headers
                    $headers = ['Student ID', 'Student Name'];
                    $headers = array_merge($headers, $date_headers);
                    $total_width = 280; // Total width for Landscape A4 (210mm height, 297mm width)
                    $num_columns = count($headers);
                    $cell_width = max(15, floor($total_width / $num_columns)); // Dynamic width, minimum 15mm to ensure dates fit

                    foreach ($headers as $header) {
                        // Truncate long headers, trim for safety, and ensure dates fit
                        $pdf->Cell($cell_width, 10, substr(trim($header), 0, 12), 1, 0, 'C'); // Increased truncate limit to 12 for longer dates
                    }
                    $pdf->Ln();

                    // Data
                    foreach ($student_data as $student) {
                        $row = [trim($student['student_id']), trim($student['student_name'])];
                        $row = array_merge($row, array_map('trim', $student['statuses']));
                        foreach ($row as $value) {
                            $pdf->Cell($cell_width, 6, trim($value) ?: '', 1, 0, 'C'); // Reduced height, trim for safety
                        }
                        $pdf->Ln();
                    }

                    $pdf->Output('attendance_export_' . date('Ymd') . '.pdf', 'D');
                    exit;
                }
            }
        }
    }

    // Get unique months with attendance records for dropdowns
    $months = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month 
         FROM $table_name 
         WHERE education_center_id = %s 
         ORDER BY month ASC",
        $educational_center_id
    ));

    // Get unique classes and sections for dropdowns
    $classes = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT class FROM $table_name WHERE education_center_id = %s",
        $educational_center_id
    ));
    $sections = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT section FROM $table_name WHERE education_center_id = %s",
        $educational_center_id
    ));

    ob_start();
    ?>
     <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
              if (is_teacher($atts)) { 
            } else {  echo render_admin_header(wp_get_current_user());
                if (!is_center_subscribed($educational_center_id)) {
                    return render_subscription_expired_message($educational_center_id);
                }
            $active_section = 'export-attendance';
            $main_section = 'student';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');}
            ?>
    <div class="attendance-frontend">
        <?php echo $error; ?>

        <!-- Export Form -->
        <h2>Export Attendance Records</h2>
        <form method="post" class="attendance-export-form" action="<?php echo esc_url(get_permalink()); ?>">
            <?php wp_nonce_field('export_attendance_submit', 'export_attendance_nonce'); ?>
            <div class="form-group">
                <label for="start_month">Start Month:</label>
                <select name="start_month" id="start_month" required>
                    <option value="">-- Select Start Month --</option>
                    <?php foreach ($months as $month) : ?>
                        <option value="<?php echo esc_attr($month); ?>">
                            <?php echo esc_html(date('F Y', strtotime($month . '-01'))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="end_month">End Month:</label>
                <select name="end_month" id="end_month" required>
                    <option value="">-- Select End Month --</option>
                    <?php foreach ($months as $month) : ?>
                        <option value="<?php echo esc_attr($month); ?>">
                            <?php echo esc_html(date('F Y', strtotime($month . '-01'))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="student_id">Student ID (Optional):</label>
                <input type="text" name="student_id" id="student_id" placeholder="e.g., 101">
            </div>
            <div class="form-group">
                <label for="class">Class (Optional):</label>
                <select name="class" id="class">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $cls) : ?>
                        <option value="<?php echo esc_attr($cls); ?>">
                            <?php echo esc_html($cls); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="section">Section (Optional):</label>
                <select name="section" id="section">
                    <option value="">-- All Sections --</option>
                    <?php foreach ($sections as $sec) : ?>
                        <option value="<?php echo esc_attr($sec); ?>">
                            <?php echo esc_html($sec); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="export_format">Export Format:</label>
                <select name="export_format" id="export_format" required>
                    <option value="csv">CSV</option>
                    <!-- <option value="pdf">PDF</option> -->
                </select>
            </div>
            <input type="submit" name="export_attendance_action" value="Export Attendance" class="button button-primary">
        </form>
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
    wp_enqueue_style('export-attendance-style', plugin_dir_url(__FILE__) . 'export-attendance.css', array(), '2.4');
    wp_add_inline_style('export-attendance-style', "
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
        .attendance-message {
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
    ");

    // Add JavaScript for auto-hide messages
    wp_enqueue_script('export-attendance-script', plugin_dir_url(__FILE__) . 'export-attendance.js', array('jquery'), '2.4', true);
    wp_localize_script('export-attendance-script', 'exportAttendanceData', $message_data);
    wp_add_inline_script('export-attendance-script', "
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
        });
    ");

    return $content;
}

