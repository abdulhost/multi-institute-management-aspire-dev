<?php
if (!defined('ABSPATH')) {
    exit;
}

$dompdf_path = plugin_dir_path(__FILE__) . 'dompdf/autoload.inc.php';
if (file_exists($dompdf_path)) {
    require_once $dompdf_path;
} else {
    wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
}

use Dompdf\Dompdf;
use Dompdf\Options;

function results_institute_dashboard_shortcode() {
    global $wpdb;

    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view your dashboard.</p>';
    }

    // Fetch educational center data
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    $args = array(
        'post_type' => 'educational-center',
        'meta_query' => array(
            array(
                'key' => 'admin_id',
                'value' => $admin_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    );

    $educational_center = new WP_Query($args);

    if ($educational_center->have_posts()) {
        $educational_center->the_post();
        $post_id = get_the_ID();
        $education_center_id = get_field('educational_center_id', $post_id) ?: $post_id;
        $institute_name = get_the_title($post_id);
        $logo = get_field('institute_logo', $post_id);
        $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
    } else {
        wp_reset_postdata();
        return '<p>No Educational Center found for this Admin ID.</p>';
    }
    wp_reset_postdata();

    // Logo path handling
    if ($logo_url) {
        $logo_path = str_replace(home_url(), ABSPATH, $logo_url);
        if (!file_exists($logo_path)) {
            error_log("Logo file not found on filesystem: $logo_path");
            $logo_path = $logo_url;
            error_log("Using URL for logo: $logo_url");
        } else {
            $logo_path = 'file://' . str_replace('\\', '/', realpath($logo_path));
            error_log("Using filesystem path for logo: $logo_path");
        }
    } else {
        $logo_path = '';
        error_log("No logo URL found for educational center ID: $post_id. Check 'institute_logo' field value: " . var_export($logo, true));
    }

    // Handle grade submission
    if (isset($_POST['submit_grades']) && wp_verify_nonce($_POST['nonce'], 'submit_grades_nonce')) {
        $exam_id = intval($_POST['exam_id']);
        foreach ($_POST['marks'] as $student_id => $subjects) {
            foreach ($subjects as $subject_id => $marks) {
                $marks = floatval($marks);
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}exam_results WHERE exam_id = %d AND subject_id = %d AND student_id = %s",
                    $exam_id, $subject_id, $student_id
                ));
                if ($existing) {
                    $wpdb->update(
                        $wpdb->prefix . 'exam_results',
                        ['marks' => $marks],
                        ['id' => $existing],
                        ['%f'],
                        ['%d']
                    );
                } else {
                    $wpdb->insert($wpdb->prefix . 'exam_results', [
                        'exam_id' => $exam_id,
                        'subject_id' => $subject_id,
                        'student_id' => $student_id,
                        'marks' => $marks,
                        'education_center_id' => $education_center_id,
                    ]);
                }
            }
        }
        echo '<div class="alert alert-success">Grades submitted successfully!</div>';
    }

    // Generate PDF
    if (isset($_GET['action']) && in_array($_GET['action'], ['generate_pdf', 'generate_student_pdf']) && isset($_GET['exam_id'])) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $exam_id = intval($_GET['exam_id']);
        $exam = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
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

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT er.*, es.subject_name 
             FROM {$wpdb->prefix}exam_results er 
             JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
             WHERE er.exam_id = %d AND er.education_center_id = %s",
            $exam_id, $education_center_id
        ), ARRAY_A);
        if (empty($results)) {
            wp_die('No results found.');
        }

        $student_marks = [];
        foreach ($results as $result) {
            $student_marks[$result['student_id']]['name'] = $result['student_id'];
            $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
        }

        if ($_GET['action'] === 'generate_student_pdf' && isset($_GET['student_id'])) {
            $student_id = sanitize_text_field($_GET['student_id']);
            if (!isset($student_marks[$student_id])) {
                wp_die('Student not found.');
            }
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', ABSPATH);
        $options->set('tempDir', sys_get_temp_dir());
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
        @page { 
            margin: 10mm; 
            border: 4px solid #1a2b5f;
            padding: 4mm;
        }
        body { 
            font-family: Helvetica, sans-serif; 
            font-size: 10pt; 
            color: #333; 
            line-height: 1.4;
        }
        .container {
            width: 100%;
            max-width: 100%;
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #fff;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a2b5f;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18pt;
            color: #1a2b5f;
            margin: 0;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 12pt;
            color: #666;
            margin: 0;
        }
        .header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 4px;
            object-fit: cover;
        }
        .details-table, .marks-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11pt;
            table-layout: fixed;
        }
        .details-table td, .marks-table td, .marks-table th {
            padding: 6px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .details-table td.label {
            width: 25%;
            font-weight: bold;
            background-color: #f5f5f5;
            color: #1a2b5f;
        }
        .marks-table th, .marks-table td {
            width: auto;
            text-align: center;
        }
        .marks-table th {
            background-color: #1a2b5f;
            color: white;
            font-weight: bold;
        }
        .marks-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .marks-table .total-row td {
            font-weight: bold;
            background-color: #e6f0fa;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            .container {
                width: 100%;
                max-width: 100%;
            }
            .details-table, .marks-table {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
        </head>
        <body>';

        if ($_GET['action'] === 'generate_student_pdf' && isset($_GET['student_id'])) {
            $student_id = sanitize_text_field($_GET['student_id']);
            $html .= '<div class="container">';
            $html .= generate_student_marksheet($exam, $subjects, $student_marks[$student_id], $logo_path, $institute_name);
            $html .= '</div>';
            $filename = "marksheet_{$exam_id}_{$student_id}.pdf";
        } else {
            $first = true;
            foreach ($student_marks as $student_id => $data) {
                if (!$first) {
                    $html .= '<div class="page-break"></div>';
                }
                $html .= '<div class="container">';
                $html .= generate_student_marksheet($exam, $subjects, $data, $logo_path, $institute_name);
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

    // Dashboard output
    ob_start();
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Manage Exam Results</h3>
            <?php
            $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
            $exam = $exam_id ? $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
                $exam_id, $education_center_id
            )) : null;
            if ($exam): ?>
                <a href="?section=results&exam_id=<?php echo $exam_id; ?>&action=generate_pdf" class="btn btn-success">Generate All Mark Sheets</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <label for="exam_id" class="input-group-text">Select Exam</label>
                    <select name="exam_id" id="exam_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Exam --</option>
                        <?php
                        $exams = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %s",
                            $education_center_id
                        ));
                        if (empty($exams)) {
                            echo '<option value="">No exams found</option>';
                        } else {
                            foreach ($exams as $e) {
                                $selected = ($exam_id == $e->id) ? 'selected' : '';
                                echo "<option value='{$e->id}' $selected>{$e->name}</option>";
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" name="section" value="results">
                </div>
            </form>

            <?php if ($exam): ?>
                <h4>Grades for <?php echo esc_html($exam->name); ?></h4>
                <?php
                $subjects = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
                    $exam_id
                ));
                if (empty($subjects)): ?>
                    <div class="alert alert-warning">No subjects defined. <a href="?section=add-exam-subjects&exam_id=<?php echo $exam_id; ?>">Add subjects</a></div>
                <?php else: ?>
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <?php foreach ($subjects as $subject): ?>
                                            <th><?php echo esc_html($subject->subject_name); ?> (<?php echo $subject->max_marks; ?>)</th>
                                        <?php endforeach; ?>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $students = $wpdb->get_results($wpdb->prepare(
                                        "SELECT DISTINCT student_id 
                                         FROM {$wpdb->prefix}exam_results 
                                         WHERE exam_id = %d AND education_center_id = %s",
                                        $exam_id, $education_center_id
                                    ));
                                    if (empty($students)) {
                                        echo '<tr><td colspan="' . (count($subjects) + 2) . '">No students found for this exam.</td></tr>';
                                    } else {
                                        $marks_results = $wpdb->get_results($wpdb->prepare(
                                            "SELECT student_id, subject_id, marks 
                                             FROM {$wpdb->prefix}exam_results 
                                             WHERE exam_id = %d AND education_center_id = %s",
                                            $exam_id, $education_center_id
                                        ), ARRAY_A);
                                        $marks_index = [];
                                        foreach ($marks_results as $result) {
                                            $marks_index[$result['student_id']][$result['subject_id']] = $result['marks'];
                                        }

                                        foreach ($students as $student) {
                                            echo '<tr>';
                                            echo '<td>' . esc_html($student->student_id) . '</td>';
                                            foreach ($subjects as $subject) {
                                                $marks = isset($marks_index[$student->student_id][$subject->id]) ? $marks_index[$student->student_id][$subject->id] : '';
                                                echo '<td><input type="number" name="marks[' . esc_attr($student->student_id) . '][' . $subject->id . ']" class="form-control" value="' . esc_attr($marks) . '" step="0.01" min="0" max="' . $subject->max_marks . '"></td>';
                                            }
                                            echo '<td><a href="?section=results&exam_id=' . $exam_id . '&action=generate_student_pdf&student_id=' . urlencode($student->student_id) . '" class="btn btn-sm btn-info">Download Mark Sheet</a></td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                        <?php wp_nonce_field('submit_grades_nonce', 'nonce'); ?>
                        <button type="submit" name="submit_grades" class="btn btn-primary">Submit Grades</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">Please select an exam to manage results.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generate_student_marksheet($exam, $subjects, $student_data, $logo_path, $institute_name) {
    global $wpdb;

    $student_id = $student_data['name'];

    // Attempt to fetch student details from 'student' post type
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
        // Broader search in posts and meta
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
        $student_name = $student->post_title;
        $student_roll = get_field('roll_number', $student->ID) ?: 'N/A';
        $student_dob = get_field('date_of_birth', $student->ID) ?: 'N/A';
        $student_gender = get_field('gender', $student->ID) ?: 'N/A';
        error_log("Student found for ID: $student_id - Name: $student_name, Post Type: {$student->post_type}, ID: {$student->ID}");
    } else {
        $student_name = 'Unknown (' . esc_html($student_id) . ')';
        $student_roll = 'N/A';
        $student_dob = 'N/A';
        $student_gender = 'N/A';
        error_log("Student not found for ID: $student_id. Searched 'student' post type and all posts/meta. Check database for 'STU-67bddcdc84aad'.");
    }

    $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

    $html = '
        <div class="header">
            ' . ($logo_path ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">' . esc_html($exam->name) . '</div>
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
    foreach ($subjects as $subject) {
        $mark = isset($student_data['marks'][$subject->id]) ? $student_data['marks'][$subject->id] : '-';
        $total += is_numeric($mark) ? floatval($mark) : 0;
        $max_total += floatval($subject->max_marks);
        $html .= '
            <tr>
                <td>' . esc_html($subject->subject_name) . '</td>
                <td>' . $mark . '</td>
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
            <p>This is an Online Generated Marksheet issued by ' . esc_html($institute_name) . '</p>
            <p>Generated on ' . date('Y-m-d') . '</p>
            <div class="signature">
                <p>___________________________</p>
                <p>Registrar / Authorized Signatory</p>
            </div>
            <div class="generated-by">
                Managed by Instituto Educational Center Management System
            </div>
        </div>';

    return $html;
}

add_shortcode('results_institute_dashboard', 'results_institute_dashboard_shortcode');