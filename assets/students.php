<?php
// students.php
function students_institute_dashboard_shortcode() {

// Function to render the Students section
// function render_students_section_content() {
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
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }
    // Handle form submission to add student
   // Include WordPress media handling functions
require_once(ABSPATH . 'wp-admin/includes/image.php');



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

    // Start output buffering
    ob_start();
    ?>
     <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php

echo render_admin_header(wp_get_current_user());
if (!is_center_subscribed($educational_center_id)) {
    return render_subscription_expired_message($educational_center_id);
}
        $active_section = 'students';
        include(plugin_dir_path(__FILE__) . 'sidebar.php');
            ?>
        <!-- </div> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">

    <?php
    include plugin_dir_path(__FILE__) . 'helper.php'; 
    include plugin_dir_path(__FILE__) . 'addstudents.php'; 
    ?>

<?php
    include plugin_dir_path(__FILE__) . 'editstudents.php'; // Ensure the path is correct
    ?>
</div>

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
add_shortcode('students_institute_dashboard', 'students_institute_dashboard_shortcode');
?>