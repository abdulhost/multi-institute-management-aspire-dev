<?php
if (!defined('ABSPATH')) {
    exit;
}

// Include Dompdf manually
$dompdf_path = plugin_dir_path(__FILE__) . 'dompdf/autoload.inc.php';
if (file_exists($dompdf_path)) {
    require_once $dompdf_path;
} else {
    wp_die('Dompdf autoload file not found at: ' . $dompdf_path . '. Please ensure Dompdf is correctly installed in the plugin directory.');
}

function results_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

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

    // Generate PDF with Dompdf
    if (isset($_GET['action']) && in_array($_GET['action'], ['generate_pdf', 'generate_student_pdf']) && isset($_GET['exam_id'])) {
        // Prevent any output before this point
        if (headers_sent($file, $line)) {
            wp_die("Headers already sent in $file on line $line. Cannot generate PDF.");
        }

        // Clean all output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Setup Dompdf with options
        $options = new Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', plugin_dir_path(__FILE__));
        $options->set('tempDir', sys_get_temp_dir()); // Ensure temp directory is writable
        
        $dompdf = new Dompdf\Dompdf($options);

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
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT er.*, es.subject_name 
             FROM {$wpdb->prefix}exam_results er 
             JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
             WHERE er.exam_id = %d AND er.education_center_id = %s",
            $exam_id, $education_center_id
        ), ARRAY_A);

        if (empty($results)) {
            wp_die('No results found for this exam.');
        }

        $student_marks = [];
        foreach ($results as $result) {
            $student_marks[$result['student_id']]['name'] = $result['student_id'];
            $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
        }

        $logo_path = plugin_dir_path(__FILE__) . 'logo.jpg';
        if (!file_exists($logo_path)) {
            error_log('Logo not found at: ' . $logo_path);
            $logo_path = '';
        } else {
            // Convert to absolute path for Dompdf (Windows compatible)
            $logo_path = 'file://' . str_replace('\\', '/', realpath($logo_path));
        }

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { margin: 20mm; }
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 20px; }
                .header img { max-width: 100px; }
                .header h1 { font-size: 24px; margin: 10px 0; }
                .info { font-size: 14px; margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th, td { border: 1px solid #333; padding: 8px; text-align: center; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; }
                .page-break { page-break-before: always; }
            </style>
        </head>
        <body>';

        if ($_GET['action'] === 'generate_student_pdf' && isset($_GET['student_id'])) {
            $student_id = sanitize_text_field($_GET['student_id']);
            if (!isset($student_marks[$student_id])) {
                wp_die('Student not found.');
            }

            $html .= generate_student_marksheet($exam, $subjects, $student_marks[$student_id], $logo_path);
            $filename = "marksheet_{$exam_id}_{$student_id}.pdf";
        } else {
            $first = true;
            foreach ($student_marks as $student_id => $data) {
                if (!$first) {
                    $html .= '<div class="page-break"></div>';
                }
                $html .= generate_student_marksheet($exam, $subjects, $data, $logo_path);
                $first = false;
            }
            $filename = "marksheet_{$exam_id}_all.pdf";
        }

        $html .= '</body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Get PDF content
        $pdf_content = $dompdf->output();

        // Set secure headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        header('X-Download-Options: noopen');
        header('Content-Transfer-Encoding: binary');
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // Output PDF content directly
        echo $pdf_content;
        flush();
        exit;
    }

    $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
    $exam = $exam_id ? $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
        $exam_id, $education_center_id
    )) : null;

    ob_start();
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Manage Exam Results</h3>
            <?php if ($exam): ?>
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
                        foreach ($exams as $e) {
                            $selected = ($exam_id == $e->id) ? 'selected' : '';
                            echo "<option value='{$e->id}' $selected>{$e->name}</option>";
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

                                    if (empty($students)) {
                                        echo '<tr><td colspan="' . (count($subjects) + 2) . '">No results found for this exam.</td></tr>';
                                    } else {
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

function generate_student_marksheet($exam, $subjects, $student_data, $logo_path) {
    $html = '
        <div class="header">
            ' . ($logo_path ? '<img src="' . $logo_path . '" alt="School Logo">' : '') . '
            <h1>Your School Name</h1>
            <div class="info">Exam: ' . esc_html($exam->name) . '</div>
            <div class="info">Date: ' . esc_html($exam->exam_date ?: 'N/A') . '</div>
            <div class="info">Class: ' . esc_html($exam->class_id ?: 'N/A') . '</div>
            <div class="info">Student ID: ' . esc_html($student_data['name']) . '</div>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 60%;">Subject</th>
                    <th>Marks</th>
                    <th>Max Marks</th>
                </tr>
            </thead>
            <tbody>';

    $total = 0;
    $max_total = 0;
    foreach ($subjects as $subject) {
        $mark = isset($student_data['marks'][$subject->id]) ? $student_data['marks'][$subject->id] : '-';
        $total += is_numeric($mark) ? $mark : 0;
        $max_total += floatval($subject->max_marks);
        $html .= '<tr>
            <td>' . esc_html($subject->subject_name) . '</td>
            <td>' . $mark . '</td>
            <td>' . $subject->max_marks . '</td>
        </tr>';
    }

    $html .= '
            <tr>
                <td><strong>Total</strong></td>
                <td><strong>' . $total . '</strong></td>
                <td><strong>' . $max_total . '</strong></td>
            </tr>
            </tbody>
        </table>
        <div class="footer">
            Generated on ' . date('Y-m-d') . '
        </div>';

    return $html;
}

add_shortcode('results_institute_dashboard', 'results_institute_dashboard_shortcode');
