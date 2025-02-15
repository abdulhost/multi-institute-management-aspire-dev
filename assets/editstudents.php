<!-- edit students file -->
<!-- Student List Table -->
<div id="result">
    <h3>Student List</h3>
    <table id="students-table" border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to get students associated with the current Educational Center
            $students = get_posts(array(
                'post_type' => 'students',
                'meta_key' => 'educational_center_id',
                'meta_value' => $educational_center_id,
                'posts_per_page' => -1, // Get all posts
            ));

            // Display the list of students in the table
            if (!empty($students)) {
                foreach ($students as $student) {
                    $student_id = get_post_meta($student->ID, 'student_id', true);
                    $student_name = $student->post_title;
                    $student_email = get_post_meta($student->ID, 'student_email', true);
                    $student_class = get_post_meta($student->ID, 'student_class', true);

                    echo '<tr class="student-row">
                            <td>' . esc_html($student_id) . '</td>
                            <td>' . esc_html($student_name) . '</td>
                            <td>' . esc_html($student_email) . '</td>
                            <td>' . esc_html($student_class) . '</td>
                            <td>
                                <a href="#students" class="edit-btn" data-student-id="' . esc_attr($student->ID) . '" data-student-name="' . esc_attr($student_name) . '" data-student-email="' . esc_attr($student_email) . '" data-student-class="' . esc_attr($student_class) . '">Edit</a> |
                                <a href="?action=delete&student_id=' . $student->ID . '" onclick="return confirm(\'Are you sure you want to delete this student?\')">Delete</a>
                            </td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="5">No students found for this Educational Center.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Edit Student Modal -->
<div id="edit-student-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Edit Student</h3>
        <form id="edit-student-form" method="POST">
            <label for="edit_student_name">Student Name</label>
            <input type="text" id="edit_student_name" name="student_name" required>

            <label for="edit_student_email">Student Email</label>
            <input type="email" id="edit_student_email" name="student_email">

            <label for="edit_student_class">Class</label>
            <input type="text" id="edit_student_class" name="student_class">

            <input type="hidden" id="edit_student_id" name="student_id">
            <input type="submit" name="edit_student" value="Update Student">
        </form>
    </div>
</div>