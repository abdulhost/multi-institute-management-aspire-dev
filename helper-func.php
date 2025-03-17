<?php
use Dompdf\Dompdf;
use Dompdf\Options;

function generate_teacher_student_marksheet($exam_id, $education_center_id, $institute_name, $student_id = null, $logo_url = '', $is_single_student = false) {
    global $wpdb;

    $dompdf_path = plugin_dir_path(__FILE__) . '/assets/exam/dompdf/autoload.inc.php';
    if (!file_exists($dompdf_path)) {
        wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
    }
    require_once $dompdf_path;

    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));
    if (!$exam) {
        wp_die('Exam not found.');
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
        $exam_id
    ));
    if (empty($subjects)) {
        wp_die('No subjects found.');
    }

    $query = $wpdb->prepare(
        "SELECT er.student_id, er.subject_id, er.marks, es.subject_name, p.post_title 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.meta_key = 'student_id' AND pm.meta_value = er.student_id 
         LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id AND p.post_type = 'student' AND p.post_status = 'publish' 
         WHERE er.exam_id = %d AND er.education_center_id = %d",
        $exam_id, $education_center_id
    );
    if ($is_single_student && $student_id) {
        $query .= $wpdb->prepare(" AND er.student_id = %s", $student_id);
    }
    $results = $wpdb->get_results($query, ARRAY_A);
    if (empty($results)) {
        wp_die('No results found.');
    }

    $student_marks = [];
    foreach ($results as $result) {
        $sid = $result['student_id'];
        $student_marks[$sid]['id'] = $sid;
        $student_marks[$sid]['name'] = $result['post_title'] ?: 'Unknown (' . esc_html($sid) . ')';
        $student_marks[$sid]['marks'][$result['subject_id']] = $result['marks'];
    }

    if ($is_single_student && $student_id && !isset($student_marks[$student_id])) {
        wp_die('Student not found.');
    }

    $logo_path = $logo_url ? (file_exists(str_replace(home_url(), ABSPATH, $logo_url)) ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $logo_url))) : $logo_url) : '';

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('chroot', ABSPATH);
    $options->set('tempDir', sys_get_temp_dir());
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { margin: 15mm; border: 2px solid #1a2b5f; padding: 10mm; }
        body { font-family: Helvetica, sans-serif; font-size: 12pt; color: #333; line-height: 1.4; }
        .container { width: 100%; padding: 15px; background-color: #fff; }
        .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #1a2b5f; margin-bottom: 20px; }
        .header img { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; object-fit: cover; }
        .header h1 { font-size: 24pt; color: #1a2b5f; margin: 0; text-transform: uppercase; }
        .header .subtitle { font-size: 14pt; color: #666; margin: 5px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table td { padding: 8px; border: 1px solid #ddd; }
        .details-table .label { font-weight: bold; color: #1a2b5f; width: 150px; background-color: #f9f9f9; }
        .marks-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .marks-table th, .marks-table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .marks-table th { background-color: #1a2b5f; color: white; font-weight: bold; }
        .marks-table tr:nth-child(even) { background-color: #f9f9f9; }
        .marks-table .total-row td { font-weight: bold; background-color: #e6f0fa; }
        .footer { text-align: center; margin-top: 30px; font-size: 10pt; color: #666; }
        .footer .generated-by { font-size: 8pt; margin-top: 10px; color: #999; }
        .signature { margin-top: 20px; }
        .page-break { page-break-before: always; }
    </style></head><body>';

    if ($is_single_student && $student_id) {
        $html .= '<div class="container">';
        $html .= generate_teacher_marksheet_content($exam, $subjects, $student_marks[$student_id], $logo_path, $institute_name);
        $html .= '</div>';
        $filename = "marksheet_{$exam_id}_{$student_id}.pdf";
    } else {
        $first = true;
        foreach ($student_marks as $sid => $data) {
            if (!$first) $html .= '<div class="page-break"></div>';
            $html .= '<div class="container">';
            $html .= generate_teacher_marksheet_content($exam, $subjects, $data, $logo_path, $institute_name);
            $html .= '</div>';
            $first = false;
        }
        $filename = "marksheet_{$exam_id}_all.pdf";
    }

    $html .= '</body></html>';

    try {
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: no-cache');

        echo $pdf_content;
        flush();
        exit;
    } catch (Exception $e) {
        error_log('Dompdf Error: ' . $e->getMessage());
        wp_die('Error generating PDF: ' . $e->getMessage());
    }
}

function generate_teacher_marksheet_content($exam, $subjects, $student_data, $logo_path, $institute_name) {
    global $wpdb;

    // Debug the structure of $student_data
    error_log("Student Data in generate_teacher_marksheet_content: " . print_r($student_data, true));

    // Handle $student_data as either object or array
    $student_id = is_object($student_data) ? ($student_data->id ?? null) : ($student_data['id'] ?? null);
    if (!$student_id) {
        error_log("No student ID found in student_data");
        $student_id = 'Unknown';
    }

    $student = get_posts([
        'post_type' => 'student',
        'meta_query' => array(
            array(
                'key' => 'student_id',
                'value' => $student_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ]);

    if (empty($student)) {
        $student = get_posts([
            'post_type' => 'student',
            'name' => $student_id,
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ]);
    }

    if (empty($student)) {
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT p.* 
             FROM {$wpdb->prefix}posts p 
             JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
             WHERE pm.meta_key = 'student_id' 
             AND pm.meta_value = %s 
             AND p.post_status = 'publish'",
            $student_id
        ));

        if (!$student) {
            $student = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}posts 
                 WHERE post_name = %s 
                 OR post_title = %s 
                 AND post_status = 'publish'",
                $student_id, $student_id
            ));
        }
    }

    if (!empty($student)) {
        // $student_name = $student[0]->post_title ?: 'N/A';
        $student_name = get_field('student_name', $student->ID) ?: 'N/A';
        $student_roll = get_field('roll_number', $student->ID) ?: 'N/A';
        $student_dob = get_field('date_of_birth', $student->ID) ?: 'N/A';
        $student_gender = get_field('gender', $student->ID) ?: 'N/A';
        error_log("Student found for ID: $student_id - Name: $student_name, Post Type: {$student->post_type}, ID: {$student->ID}");
    } else {
        $student_name = 'Unknown (' . esc_html($student_id) . ')';
        $student_roll = 'N/A';
        $student_dob = 'N/A';
        $student_gender = 'N/A';
        error_log("Student not found for ID: $student_id. Searched 'student' post type and all posts/meta. Check database for '$student_id'.");
    }

    $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

    $html = '
        <div class="header">
            ' . ($logo_path ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">' . esc_html($exam->name) . ' Marksheet</div>
        </div>
        <table class="details-table">
            <tr>
                <td class="label">Student Name</td>
                <td>' . esc_html($student_name) . '</td>
            </tr>
            <tr>
                <td class="label">Student ID</td>
                <td>' . esc_html($student_id) . '</td>
            </tr>
            <tr>
                <td class="label">Roll Number</td>
                <td>' . esc_html($student_roll) . '</td>
            </tr>
            <tr>
                <td class="label">Date of Birth</td>
                <td>' . esc_html($student_dob) . '</td>
            </tr>
            <tr>
                <td class="label">Gender</td>
                <td>' . esc_html($student_gender) . '</td>
            </tr>
            <tr>
                <td class="label">Examination</td>
                <td>' . esc_html($exam->name) . '</td>
            </tr>
            <tr>
                <td class="label">Class/Course</td>
                <td>' . esc_html($exam->class_id ?: 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Academic Year</td>
                <td>' . $exam_year . '</td>
            </tr>
            <tr>
                <td class="label">Exam Date</td>
                <td>' . esc_html($exam->exam_date ?: 'N/A') . '</td>
            </tr>
        </table>
        <table class="marks-table">
            <tr>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Maximum Marks</th>
            </tr>';

    $total = 0;
    $max_total = 0;
    $marks = is_object($student_data) ? (isset($student_data->marks) ? $student_data->marks : []) : (isset($student_data['marks']) ? $student_data['marks'] : []);
    foreach ($subjects as $subject) {
        $mark = isset($marks[$subject->id]) ? $marks[$subject->id] : '-';
        $total += is_numeric($mark) ? floatval($mark) : 0;
        $max_total += floatval($subject->max_marks);
        $html .= '
            <tr>
                <td>' . esc_html($subject->subject_name) . '</td>
                <td>' . (is_numeric($mark) ? $mark : '-') . '</td>
                <td>' . $subject->max_marks . '</td>
            </tr>';
    }

    $percentage = $max_total > 0 ? round(($total / $max_total) * 100, 2) : 0;

    $html .= '
            <tr class="total-row">
                <td>Total</td>
                <td>' . $total . '</td>
                <td>' . $max_total . '</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Percentage</td>
                <td>' . $percentage . '%</td>
            </tr>
        </table>
        <div class="footer">
            <p>This is an official marksheet issued by ' . esc_html($institute_name) . '</p>
            <p>Generated on ' . date('Y-m-d') . '</p>
            <div class="signature">
                <p>___________________________</p>
                <p>Registrar / Authorized Signatory</p>
            </div>
            <div class="generated-by">Managed by Instituto Educational Center Management System</div>
        </div>';

    return $html;
}

// Data Fetching Helper (Namespaced for Teachers)
function fetch_teacher_attendance_data_helper2($request, $educational_center_id, $table_name, $wpdb = null) {
    $wpdb = $wpdb ?? $GLOBALS['wpdb'];
    $full_table_name = $wpdb->prefix . $table_name;

    // Validate inputs
    if (!$request instanceof WP_REST_Request) {
        return ['success' => false, 'message' => 'Invalid request object'];
    }
    if (!$educational_center_id) {
        return ['success' => false, 'message' => 'Educational center ID not found'];
    }
    if (!$table_name) {
        return ['success' => false, 'message' => 'Table name not provided'];
    }

    $params = $request->get_params();
    $class = sanitize_text_field($params['class'] ?? '');
    $section = sanitize_text_field($params['section'] ?? '');
    $month = !empty($params['month']) ? max(1, min(12, intval($params['month']))) : 0;
    $year = !empty($params['year']) ? max(1900, min(9999, intval($params['year']))) : 0;
    $student_id = sanitize_text_field($params['student_id'] ?? '');
    $student_name = sanitize_text_field($params['student_name'] ?? '');

    // Default to current month/year if no filters
    $has_filters = !empty($class) || !empty($section) || ($month > 0 && $year > 0) || !empty($student_id) || !empty($student_name);
    if (!$has_filters) {
        $month = date('n');
        $year = date('Y');
    }

    $days_in_month = ($month > 0 && $year > 0) ? cal_days_in_month(CAL_GREGORIAN, $month, $year) : 31;

    // Build dynamic query
    $query = "SELECT student_id, student_name, class, section, date, status 
              FROM $full_table_name 
              WHERE education_center_id = %s";
    $query_args = [$educational_center_id];

    if (!empty($class)) {
        $query .= " AND class = %s";
        $query_args[] = $class;
    }
    if (!empty($section)) {
        $query .= " AND section = %s";
        $query_args[] = $section;
    }
    if ($month > 0 && $year > 0) {
        $query .= " AND YEAR(date) = %d AND MONTH(date) = %d";
        $query_args[] = $year;
        $query_args[] = $month;
    }
    if (!empty($student_id)) {
        $query .= " AND student_id = %s";
        $query_args[] = $student_id;
    }
    if (!empty($student_name)) {
        $query .= " AND student_name LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($student_name) . '%';
    }

    $results = $wpdb->get_results($wpdb->prepare($query, $query_args));
    $student_data = [];

    if ($month > 0 && $year > 0) {
        foreach ($results as $row) {
            $student_key = $row->student_id;
            if (!isset($student_data[$student_key])) {
                $student_data[$student_key] = [
                    'name' => $row->student_name,
                    'class' => $row->class,
                    'section' => $row->section,
                    'attendance' => array_fill(1, $days_in_month, ''),
                    'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0]
                ];
            }
            $day = (int) date('j', strtotime($row->date));
            $status_short = '';
            switch ($row->status) {
                case 'Present': $status_short = 'P'; $student_data[$student_key]['counts']['P']++; break;
                case 'Late': $status_short = 'L'; $student_data[$student_key]['counts']['L']++; break;
                case 'Absent': $status_short = 'A'; $student_data[$student_key]['counts']['A']++; break;
                case 'Full Day': $status_short = 'F'; $student_data[$student_key]['counts']['F']++; break;
                case 'Holiday': $status_short = 'H'; $student_data[$student_key]['counts']['H']++; break;
            }
            $student_data[$student_key]['attendance'][$day] = $status_short;
        }
    } else {
        foreach ($results as $row) {
            $student_key = $row->student_id;
            if (!isset($student_data[$student_key])) {
                $student_data[$student_key] = [
                    'name' => $row->student_name,
                    'class' => $row->class,
                    'section' => $row->section,
                    'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0]
                ];
            }
            switch ($row->status) {
                case 'Present': $student_data[$student_key]['counts']['P']++; break;
                case 'Late': $student_data[$student_key]['counts']['L']++; break;
                case 'Absent': $student_data[$student_key]['counts']['A']++; break;
                case 'Full Day': $student_data[$student_key]['counts']['F']++; break;
                case 'Holiday': $student_data[$student_key]['counts']['H']++; break;
            }
        }
    }

    return ['success' => true, 'data' => $student_data, 'month' => $month, 'year' => $year];
}

// HTML Rendering Helper (Reusable)
function render_teacher_attendance_table($student_data, $month = 0, $year = 0, $table_id = 'teacher-attendance-table') {
    $days_in_month = ($month > 0 && $year > 0) ? cal_days_in_month(CAL_GREGORIAN, $month, $year) : 0;

    ob_start();
    ?>
    <table id="<?php echo esc_attr($table_id); ?>" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Admission No</th>
                <th>Class</th>
                <th>P</th>
                <th>L</th>
                <th>A</th>
                <th>F</th>
                <th>H</th>
                <th>%</th>
                <?php if ($month > 0 && $year > 0) : ?>
                    <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                        <?php
                        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $day_name = date('D', strtotime($date));
                        $is_weekend = in_array($day_name, ['Sat', 'Sun']) ? ' class="weekend"' : '';
                        ?>
                        <th<?php echo $is_weekend; ?>><?php echo esc_html($day) . '<br>' . esc_html($day_name); ?></th>                    <?php endfor; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($student_data)) : ?>
                <?php foreach ($student_data as $student_id => $data) : ?>
                    <?php
                    $total_days = ($month > 0 && $year > 0) ? $days_in_month - $data['counts']['H'] : array_sum($data['counts']);
                    $attended_days = $data['counts']['P'] + $data['counts']['L']; // Include 'Late' as attended
                    $percent = $total_days > 0 ? round(($attended_days / $total_days) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo esc_html($data['name']); ?></td>
                        <td><?php echo esc_html($student_id); ?></td>
                        <td><?php echo esc_html($data['class'] . ' - ' . $data['section']); ?></td>
                        <td><?php echo esc_html($data['counts']['P']); ?></td>
                        <td><?php echo esc_html($data['counts']['L']); ?></td>
                        <td><?php echo esc_html($data['counts']['A']); ?></td>
                        <td><?php echo esc_html($data['counts']['F']); ?></td>
                        <td><?php echo esc_html($data['counts']['H']); ?></td>
                        <td><?php echo esc_html($percent . '%'); ?></td>
                        <?php if ($month > 0 && $year > 0) : ?>
                            <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                                <td><?php echo esc_html($data['attendance'][$day] ?? ''); ?></td>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="<?php echo ($month > 0 && $year > 0) ? (9 + $days_in_month) : 9; ?>">No records found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

// Combined Helper for REST API (Namespaced for Teachers)
function fetch_teacher_attendance_data_helper($request, $educational_center_id, $table_name, $wpdb = null) {
    $result = fetch_teacher_attendance_data_helper2($request, $educational_center_id, $table_name, $wpdb);
    if (!$result['success']) {
        return ['success' => false, 'data' => ['html' => '<p>' . esc_html($result['message']) . '</p>']];
    }
    $html = render_teacher_attendance_table($result['data'], $result['month'], $result['year']);
    return ['success' => true, 'data' => ['html' => $html]];
}


?>