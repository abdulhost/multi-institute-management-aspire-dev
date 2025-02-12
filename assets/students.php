<?php
// students.php

// Function to render the Students section
function render_students_section_content() {
    // Fetch the current user and their admin_id
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    // Query the Educational Center based on the admin_id
    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1, // Limit to 1 post
    ));

    // Check if there is an Educational Center for this admin
    if (empty($educational_center)) {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);

    // Handle form submission to add student
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
        // Sanitize and validate form data
        $student_name = sanitize_text_field($_POST['student_name']);
        $student_email = sanitize_email($_POST['student_email']);
        $student_class = sanitize_text_field($_POST['student_class']);
        $student_id = 'STU-' . uniqid(); // Generate unique Student ID

        // Insert the student into the 'students' CPT
        $student_post_id = wp_insert_post(array(
            'post_title'   => $student_name,
            'post_type'    => 'students',
            'post_status'  => 'publish',
            'meta_input'   => array(
                'student_email'           => $student_email,
                'student_class'           => $student_class,
                'educational_center_id'   => $educational_center_id,
                'student_id'              => $student_id,
            ),
        ));

        // Provide feedback and redirect
        if ($student_post_id) {
            wp_redirect(home_url('/institute-dashboard/#students')); // Redirect to the same page
            exit; // Stop further code execution after redirect
        } else {
            echo '<p class="error-message">Error adding student.</p>';
        }
    }

    // Handle student deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['student_id'])) {
        $student_id = intval($_GET['student_id']);
        if (wp_delete_post($student_id, true)) {
            wp_redirect(home_url('/institute-dashboard/#students')); // Redirect to the same page
            exit;
        } else {
            echo '<p class="error-message">Error deleting student.</p>';
        }
    }

    // Handle student editing
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
        $student_id = intval($_POST['student_id']);
        $student_name = sanitize_text_field($_POST['student_name']);
        $student_email = sanitize_email($_POST['student_email']);
        $student_class = sanitize_text_field($_POST['student_class']);
        $profile = get_field('student_profile_photo', $post_id);


        // Update the student post
        wp_update_post(array(
            'ID'           => $student_id,
            'post_title'   => $student_name,
        ));

        // Update meta fields
        update_post_meta($student_id, 'student_email', $student_email);
        update_post_meta($student_id, 'student_class', $student_class);

        wp_redirect(home_url('/institute-dashboard/#students')); // Redirect to the same page
        exit;
    }

    // Start output buffering
    ob_start();
    ?>

    <!-- Add Student Button -->
    <button id="add-student-btn" onclick="toggleForm()">+ Add Student</button>

    <!-- Add Student Form (Hidden by default) -->
    <div id="add-student-form" style="display: none;">
        <h3>Add Student</h3>
        <form method="POST">
        <label for="admission_number">Admission Number</label>
        <input type="text" name="admission_number" required>
            <label for="student_name">Student Full Name</label>
            <input type="text" name="student_name" required>

            <label for="student_email">Student Email</label>
            <input type="email" name="student_email">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number">
            <label for="student_class">Class</label>
            <input type="text" name="student_class">

            <!-- Hidden field to pass the educational center ID -->
            <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">

            <!-- Student ID Field -->
            <label for="student_id">Student ID (Auto-generated)</label>
            <input type="text" name="student_id" value="<?php echo 'STU-' . uniqid(); ?>" readonly>

            <?php
// gender the field key
$field_key = 'field_67ab1ab5978fc'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
    <label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
    <select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
        <option value="">Select</option>
        <?php
        // Loop through the choices and display them as options
        foreach ($field['choices'] as $value => $label):
            // Remove any unwanted HTML tags (including <br> tags)
            $label = wp_strip_all_tags($label); // Strips all HTML tags
            
            // Check if this option is the selected one
            $selected = ($value == $gender) ? ' selected' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        endforeach;
        ?>
    </select>
<?php else: ?>
    <p>No gender field found.</p>
<?php endif; ?>

<?php
// religion the field key
$field_key = 'field_67ab1b6d978fe'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
    <label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
    <select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
        <option value="">Select</option>
        <?php
        // Loop through the choices and display them as options
        foreach ($field['choices'] as $value => $label):
            // Remove any unwanted HTML tags (including <br> tags)
            $label = wp_strip_all_tags($label); // Strips all HTML tags
            
            // Check if this option is the selected one
            $selected = ($value == $gender) ? ' selected' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        endforeach;
        ?>
    </select>
<?php else: ?>
    <p>No gender field found.</p>
<?php endif; ?>

<?php
// blood group the field key
$field_key = 'field_67ab1c0197900'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
    <label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
    <select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
        <option value="">Select</option>
        <?php
        // Loop through the choices and display them as options
        foreach ($field['choices'] as $value => $label):
            // Remove any unwanted HTML tags (including <br> tags)
            $label = wp_strip_all_tags($label); // Strips all HTML tags
            
            // Check if this option is the selected one
            $selected = ($value == $gender) ? ' selected' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        endforeach;
        ?>
    </select>
<?php else: ?>
    <p>No gender field found.</p>
<?php endif; ?>

<?php
// class group the field key
$field_key = 'field_67acbf1021580'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
    <label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
    <select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
        <option value="">Select</option>
        <?php
        // Loop through the choices and display them as options
        foreach ($field['choices'] as $value => $label):
            // Remove any unwanted HTML tags (including <br> tags)
            $label = wp_strip_all_tags($label); // Strips all HTML tags
            
            // Check if this option is the selected one
            $selected = ($value == $gender) ? ' selected' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        endforeach;
        ?>
    </select>
<?php else: ?>
    <p>No gender field found.</p>
<?php endif; ?>

<label for="date_of_birth">Date of Birth</label>
<input type="date" name="date_of_birth">



<label for="student_profile_photo">Student Profile Photo</label>
        <input type="file" name="student_profile_photo">


            <input type="submit" name="add_student" value="Add Student">
        </form>
    </div>

   <!-- Search Form -->
<div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">Search</span>
        <input type="text" id="search_text" placeholder="Search by Student Details" class="form-control" />
    </div>
</div>

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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<!-- JavaScript to toggle the form visibility and handle AJAX search -->
<script>
function toggleForm() {
    var form = document.getElementById('add-student-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    var modal = document.getElementById('edit-student-modal');

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName('close-modal')[0];

    // When the user clicks on an edit button, open the modal
    document.querySelectorAll('.edit-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var studentId = this.getAttribute('data-student-id');
            var studentName = this.getAttribute('data-student-name');
            var studentEmail = this.getAttribute('data-student-email');
            var studentClass = this.getAttribute('data-student-class');

            document.getElementById('edit_student_id').value = studentId;
            document.getElementById('edit_student_name').value = studentName;
            document.getElementById('edit_student_email').value = studentEmail;
            document.getElementById('edit_student_class').value = studentClass;

            modal.style.display = 'block';
        });
    });

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = 'none';
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});

$(document).ready(function(){
    // Bind the keyup event to the search input
    $('#search_text').keyup(function(){
        var searchText = $(this).val().toLowerCase(); // Convert input to lowercase

        // Loop through all table rows
        $('#students-table tbody tr').each(function() {
            var studentID = $(this).find('td').eq(0).text().toLowerCase(); // Student ID column
            var studentName = $(this).find('td').eq(1).text().toLowerCase(); // Name column
            var studentEmail = $(this).find('td').eq(2).text().toLowerCase(); // Email column
            var studentClass = $(this).find('td').eq(3).text().toLowerCase(); // Class column

            // If any of the fields match the search text, show the row; otherwise, hide it
            if (studentID.indexOf(searchText) > -1 ||studentName.indexOf(searchText) > -1 || studentEmail.indexOf(searchText) > -1 || studentClass.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>

    <?php
    // End output buffering and return the content
    return ob_get_clean();
}

?>