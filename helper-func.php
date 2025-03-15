<?php

function generate_student_marksheet($exam, $subjects, $student_data, $logo_path, $institute_name) {
    global $wpdb;

    $student_id = $student_data['name'];

    // Fetch student details
    $student = $wpdb->get_row($wpdb->prepare(
        "SELECT p.* 
         FROM {$wpdb->prefix}posts p 
         JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
         WHERE pm.meta_key = 'student_id' 
         AND pm.meta_value = %s 
         AND p.post_type = 'student' 
         AND p.post_status = 'publish'",
        $student_id
    ));

    if (!$student) {
        $student_name = 'Unknown (' . esc_html($student_id) . ')';
        $student_roll = 'N/A';
        $student_dob = 'N/A';
        $student_gender = 'N/A';
        error_log("Student not found for ID: $student_id.");
    } else {
        $student_name = $student->post_title;
        $student_roll = get_post_meta($student->ID, 'roll_number', true) ?: 'N/A';
        $student_dob = get_post_meta($student->ID, 'date_of_birth', true) ?: 'N/A';
        $student_gender = get_post_meta($student->ID, 'gender', true) ?: 'N/A';
    }

    $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

    $html = '
        <div class="header">
            ' . ($logo_path ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">' . esc_html($exam->name) . '</div>
        </div>
        <table class="details-table">
            <tr><td class="label">Student Name</td><td>' . esc_html($student_name) . '</td></tr>
            <tr><td class="label">Student ID</td><td>' . esc_html($student_id) . '</td></tr>
            <tr><td class="label">Roll Number</td><td>' . esc_html($student_roll) . '</td></tr>
            <tr><td class="label">Date of Birth</td><td>' . esc_html($student_dob) . '</td></tr>
            <tr><td class="label">Gender</td><td>' . esc_html($student_gender) . '</td></tr>
            <tr><td class="label">Examination</td><td>' . esc_html($exam->name) . '</td></tr>
            <tr><td class="label">Class/Course</td><td>' . esc_html($exam->class_id ?: 'N/A') . '</td></tr>
            <tr><td class="label">Academic Year</td><td>' . $exam_year . '</td></tr>
            <tr><td class="label">Exam Date</td><td>' . esc_html($exam->exam_date ?: 'N/A') . '</td></tr>
        </table>
        <table class="marks-table">
            <tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th></tr>';

    $total = 0;
    $max_total = 0;
    foreach ($subjects as $subject) {
        $mark = isset($student_data['marks'][$subject->id]) ? $student_data['marks'][$subject->id] : '-';
        $total += is_numeric($mark) ? floatval($mark) : 0;
        $max_total += floatval($subject->max_marks);
        $html .= '<tr><td>' . esc_html($subject->subject_name) . '</td><td>' . $mark . '</td><td>' . $subject->max_marks . '</td></tr>';
    }

    $percentage = $max_total > 0 ? round(($total / $max_total) * 100, 2) : 0;

    $html .= '
            <tr class="total-row"><td>Total</td><td>' . $total . '</td><td>' . $max_total . '</td></tr>
            <tr class="total-row"><td colspan="2">Percentage</td><td>' . $percentage . '%</td></tr>
        </table>
        <div class="footer">
            <p>This is an Online Generated Marksheet issued by ' . esc_html($institute_name) . '</p>
            <p>Generated on ' . date('Y-m-d') . '</p>
            <div class="signature"><p>___________________________</p><p>Registrar / Authorized Signatory</p></div>
            <div class="generated-by">Managed by Instituto Educational Center Management System</div>
        </div>';

    return $html;
}