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
   // Include WordPress media handling functions
require_once(ABSPATH . 'wp-admin/includes/image.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    // Initialize variables with default values
    $gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
    // $educational_center_id = isset($_POST['educational_center_id']) ? sanitize_text_field($_POST['educational_center_id']) : '';

    // Check if file is uploaded and there are no errors
    if (isset($_FILES['student_profile_photo']) && $_FILES['student_profile_photo']['error'] === 0) {
        // File upload logic (unchanged)
        $file = $_FILES['student_profile_photo'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 500 * 1024; // 500KB

        // Check file type
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_mime_types)) {
            echo 'Invalid file type. Please upload a JPEG, PNG, or GIF image.';
            return;
        }

        // Check file size
        if ($file['size'] > $max_file_size) {
            echo 'File size exceeds the 500KB limit.';
            return;
        }

        // Generate a unique file name
        $upload_dir = wp_upload_dir();
        $file_name = 'profile_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $target_path = $upload_dir['path'] . '/' . $file_name;

        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $file_url = $upload_dir['url'] . '/' . $file_name;

            // Create the attachment
            $attachment = array(
                'guid' => $file_url,
                'post_mime_type' => $file_type,
                'post_title' => sanitize_text_field($file_name),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            // Insert the attachment
            $attachment_id = wp_insert_attachment($attachment, $target_path);

            // Generate metadata for the attachment
            if (!is_wp_error($attachment_id)) {
                $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $target_path);
                wp_update_attachment_metadata($attachment_id, $attachment_metadata);
            } else {
                echo 'Error creating media attachment.';
                return;
            }
        } else {
            echo 'Error uploading the file.';
            return;
        }
    }

    // Sanitize and validate form data
    $admission_number = sanitize_text_field($_POST['admission_number']);
    $student_name = sanitize_text_field($_POST['student_name']);
    $student_email = sanitize_email($_POST['student_email']);
    // $student_class = sanitize_text_field($_POST['student_class']);
    // $student_id = 'STU-' . uniqid();
    $student_id = sanitize_text_field($_POST['student_id']);
    $class_name = sanitize_text_field($_POST['class_name']);
    $section = sanitize_text_field($_POST['section']);
    $religion = sanitize_text_field($_POST['religion']);
    $blood_group = sanitize_text_field($_POST['blood_group']);
    $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $height = isset($_POST['height']) ? sanitize_text_field($_POST['height']) : '';
    $weight = isset($_POST['weight']) ? sanitize_text_field($_POST['weight']) : '';
    $current_address = isset($_POST['current_address']) ? sanitize_textarea_field($_POST['current_address']) : '';
    $permanent_address = isset($_POST['permanent_address']) ? sanitize_textarea_field($_POST['permanent_address']) : '';
    $roll_number = isset($_POST['roll_number']) ? sanitize_text_field($_POST['roll_number']) : '';
    $admission_date = isset($_POST['admission_date']) ? sanitize_text_field($_POST['admission_date']) : '';

    // Insert the student into the 'students' CPT
    $student_post_id = wp_insert_post(array(
        'post_title'   => $student_name,
        'post_type'    => 'students',
        'post_status'  => 'publish',
        'meta_input'   => array(
            'admission_number'          => $admission_number,
            'student_email'             => $student_email,
            'educational_center_id'    => $educational_center_id,
            'student_id'                => $student_id,
            'phone_number'              => $phone_number,
            'class'                     => $class_name,
            'section'                   => $section,
            'religion'                  => $religion,
            'blood_group'               => $blood_group,
            'date_of_birth'             => $date_of_birth,
            'gender'                    => $gender,
            'height'            => $height,
            'weight'            => $weight,
            'current_address'   => $current_address,
            'permanent_address' => $permanent_address,
            'roll_number'       => $roll_number,
            'admission_date'    => $admission_date,
        ),
    ));

    // If the post was successfully inserted, update the ACF field with the attachment ID
    if ($student_post_id && $attachment_id) {
        update_field('field_67ab1bd7978ff', $attachment_id, $student_post_id);
        echo 'Student profile created successfully with profile photo!';
        wp_redirect(home_url('/institute-dashboard/#students'));
        echo '<p class="success-message">Student added successfully!</p>';
        exit;
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
    $class_name = sanitize_text_field($_POST['class_name']);
    $section = sanitize_text_field($_POST['section']);
    $admission_number = sanitize_text_field($_POST['admission_number']);
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $roll_number = sanitize_text_field($_POST['roll_number']);
    $admission_date = sanitize_text_field($_POST['admission_date']);
    $gender = sanitize_text_field($_POST['gender']);
    $religion = sanitize_text_field($_POST['religion']);
    $blood_group = sanitize_text_field($_POST['blood_group']);
    $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
    $height = sanitize_text_field($_POST['height']);
    $weight = sanitize_text_field($_POST['weight']);
    $current_address = sanitize_textarea_field($_POST['current_address']);
    $permanent_address = sanitize_textarea_field($_POST['permanent_address']);

    // Handle profile picture upload
    if (!empty($_FILES['student_profile_photo']['name'])) {
        $file = $_FILES['student_profile_photo'];
        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['file'])) {
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $upload['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ), $upload['file']);

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Update the profile picture meta field
            update_post_meta($student_id, 'student_profile_photo', $attachment_id);
        }
    }

    // Update the student post
    wp_update_post(array(
        'ID'           => $student_id,
        'post_title'   => $student_name,
    ));

    // Update meta fields
    update_post_meta($student_id, 'student_email', $student_email);
    update_post_meta($student_id, 'class', $class_name);
    update_post_meta($student_id, 'section', $section);
    update_post_meta($student_id, 'admission_number', $admission_number);
    update_post_meta($student_id, 'phone_number', $phone_number);
    update_post_meta($student_id, 'roll_number', $roll_number);
    update_post_meta($student_id, 'admission_date', $admission_date);
    update_post_meta($student_id, 'gender', $gender);
    update_post_meta($student_id, 'religion', $religion);
    update_post_meta($student_id, 'blood_group', $blood_group);
    update_post_meta($student_id, 'date_of_birth', $date_of_birth);
    update_post_meta($student_id, 'height', $height);
    update_post_meta($student_id, 'weight', $weight);
    update_post_meta($student_id, 'current_address', $current_address);
    update_post_meta($student_id, 'permanent_address', $permanent_address);

    // Redirect to the same page
    wp_redirect(home_url('/institute-dashboard/#edit-students'));
    exit;
}
    // Start output buffering
    ob_start();
    ?>
    <?php
    include plugin_dir_path(__FILE__) . 'helper.php'; 
    include plugin_dir_path(__FILE__) . 'addstudents.php'; 
    ?>

<?php
    include plugin_dir_path(__FILE__) . 'editstudents.php'; // Ensure the path is correct
    ?>


<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->

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