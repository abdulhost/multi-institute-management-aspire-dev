<?php
if (!defined('ABSPATH')) {
    exit;
}

function results_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    // Handle grade submission
    if (isset($_POST['submit_grades']) && wp_verify_nonce($_POST['nonce'], 'submit_grades_nonce')) {
        $exam_id = $_POST['exam_id']; // Keep as string to match BIGINT
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
    if (isset($_GET['action']) && $_GET['action'] === 'generate_pdf' && isset($_GET['exam_id'])) {
        require_once MY_EDUCATION_ERP_DIR . 'vendor/autoload.php'; // Use Composer autoload
        $exam_id = $_GET['exam_id'];
        $exam = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
            $exam_id, $education_center_id
        ));
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

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, "Mark Sheet for {$exam->name}", 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->Cell(50, 10, 'Student ID', 1);
        foreach ($subjects as $subject) {
            $pdf->Cell(30, 10, $subject->subject_name, 1);
        }
        $pdf->Cell(30, 10, 'Total', 1);
        $pdf->Ln();

        $student_marks = [];
        foreach ($results as $result) {
            $student_marks[$result['student_id']]['name'] = $result['student_id']; // Use student_id as name for now
            $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
        }

        foreach ($student_marks as $student_id => $data) {
            $pdf->Cell(50, 10, $data['name'], 1);
            $total = 0;
            foreach ($subjects as $subject) {
                $mark = isset($data['marks'][$subject->id]) ? $data['marks'][$subject->id] : '-';
                $total += is_numeric($mark) ? $mark : 0;
                $pdf->Cell(30, 10, $mark, 1);
            }
            $pdf->Cell(30, 10, $total, 1);
            $pdf->Ln();
        }
        $pdf->Output("marksheet_{$exam_id}.pdf", 'D');
        exit;
    }

    $exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : 0;
    $exam = $exam_id ? $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
        $exam_id, $education_center_id
    )) : null;

    ob_start();
    ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Manage Exam Results</h3>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch unique students from wp_exam_results for this exam
                                    $students = $wpdb->get_results($wpdb->prepare(
                                        "SELECT DISTINCT student_id 
                                         FROM {$wpdb->prefix}exam_results 
                                         WHERE exam_id = %d AND education_center_id = %s",
                                        $exam_id, $education_center_id
                                    ));

                                    // Fetch all marks for this exam
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
                                        echo '<tr><td colspan="' . (count($subjects) + 1) . '">No students found for this exam.</td></tr>';
                                    } else {
                                        foreach ($students as $student) {
                                            echo '<tr>';
                                            echo '<td>' . esc_html($student->student_id) . '</td>';
                                            foreach ($subjects as $subject) {
                                                $marks = isset($marks_index[$student->student_id][$subject->id]) ? $marks_index[$student->student_id][$subject->id] : '';
                                                echo '<td><input type="number" name="marks[' . esc_attr($student->student_id) . '][' . $subject->id . ']" class="form-control" value="' . esc_attr($marks) . '" step="0.01" min="0" max="' . $subject->max_marks . '"></td>';
                                            }
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
                        <a href="?section=results&exam_id=<?php echo $exam_id; ?>&action=generate_pdf" class="btn btn-success">Generate Mark Sheet</a>
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
add_shortcode('results_institute_dashboard', 'results_institute_dashboard_shortcode');