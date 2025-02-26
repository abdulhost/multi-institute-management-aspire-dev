<?php
// student-list.php
function edit_students_institute_dashboard_shortcode() {

// Fetch the current user and their admin_id
$current_user = wp_get_current_user();
$admin_id = $current_user->user_login;
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$charset_collate = $wpdb->get_charset_collate();

// Check if the table exists before creating it
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
    education_center_id varchar(255) NOT NULL,
    class_name varchar(255) NOT NULL,
    sections text NOT NULL,
    PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

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

// Fetch all classes and sections
$class_sections = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id),
    ARRAY_A
);

// Include WordPress media handling functions
require_once(ABSPATH . 'wp-admin/includes/image.php');
?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
        $active_section = 'edit-students';
        include(plugin_dir_path(__FILE__) . 'sidebar.php');
            ?>
        <!-- </div> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">

<!-- Search Form -->
<div class="form-group search-form">
    <div class="input-group">
        <span class="input-group-addon">Search</span>
        <input type="text" id="search_text_2" placeholder="Search by Student Details" class="form-control" />
    </div>
</div>

<!-- Student List Table -->
<div id="result">
    <h3>Student List</h3>
    <table id="students-table_2" border="1" cellpadding="10" cellspacing="0" width="100%">
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
                    $student_class = get_post_meta($student->ID, 'class', true);
                    $student_section = get_post_meta($student->ID, 'section', true);
                    $admission_number = get_post_meta($student->ID, 'admission_number', true);
                    $phone_number = get_post_meta($student->ID, 'phone_number', true);
                    $roll_number = get_post_meta($student->ID, 'roll_number', true);
                    $admission_date = get_post_meta($student->ID, 'admission_date', true);
                    $gender = get_post_meta($student->ID, 'gender', true);
                    $religion = get_post_meta($student->ID, 'religion', true);
                    $blood_group = get_post_meta($student->ID, 'blood_group', true);
                    $date_of_birth = get_post_meta($student->ID, 'date_of_birth', true);
                    $height = get_post_meta($student->ID, 'height', true);
                    $weight = get_post_meta($student->ID, 'weight', true);
                    $current_address = get_post_meta($student->ID, 'current_address', true);
                    $permanent_address = get_post_meta($student->ID, 'permanent_address', true);
                    $profile_picture = get_post_meta($student->ID, 'student_profile_photo', true);
                    if ($profile_picture) {
                        // Get the URL of the image (if it's an attachment ID)
                        $profile_picture_url = wp_get_attachment_url($profile_picture);  
                    } else {
                        // Fallback to a default image URL if no profile image is set
                        $profile_picture_url = '';  // Replace with your default image path
                    }
                    
                    echo '<tr class="student-row">
                            <td>' . esc_html($student_id) . '</td>
                            <td>' . esc_html($student_name) . '</td>
                            <td>' . esc_html($student_email) . '</td>
                            <td>' . esc_html($student_class) . '</td>
                            <td>
                                <a href="#edit-students" class="edit-btn_2"
                                   data-student-id="' . esc_attr($student->ID) . '"
                                   data-student-name="' . esc_attr($student_name) . '"
                                   data-student-email="' . esc_attr($student_email) . '"
                                   data-student-class="' . esc_attr($student_class) . '"
                                   data-student-section="' . esc_attr($student_section) . '"
                                   data-admission-number="' . esc_attr($admission_number) . '"
                                   data-phone-number="' . esc_attr($phone_number) . '"
                                   data-roll-number="' . esc_attr($roll_number) . '"
                                   data-admission-date="' . esc_attr($admission_date) . '"
                                   data-gender="' . esc_attr($gender) . '"
                                   data-religion="' . esc_attr($religion) . '"
                                   data-blood-group="' . esc_attr($blood_group) . '"
                                   data-date-of-birth="' . esc_attr($date_of_birth) . '"
                                   data-height="' . esc_attr($height) . '"
                                   data-weight="' . esc_attr($weight) . '"
                                   data-current-address="' . esc_attr($current_address) . '"
                                   data-permanent-address="' . esc_attr($permanent_address) . '"
                                   data-profile-picture="' . esc_attr($profile_picture_url) .'">Edit</a> |
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
</div>

<!-- Include the edit student modal form -->
<?php include('edit-form-modal.php'); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Bind the keyup event to the search input
    $('#search_text_2').keyup(function() {
        var searchText = $(this).val().toLowerCase(); // Convert input to lowercase

        // Loop through all table rows
        $('#students-table_2 tbody tr').each(function() {
            var studentID = $(this).find('td').eq(0).text().toLowerCase(); // Student ID column
            var studentName = $(this).find('td').eq(1).text().toLowerCase(); // Name column
            var studentEmail = $(this).find('td').eq(2).text().toLowerCase(); // Email column
            var studentClass = $(this).find('td').eq(3).text().toLowerCase(); // Class column

            // If any of the fields match the search text, show the row; otherwise, hide it
            if (studentID.indexOf(searchText) > -1 || studentName.indexOf(searchText) > -1 || studentEmail.indexOf(searchText) > -1 || studentClass.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Open modal when Edit button is clicked
    $(document).on('click', '.edit-btn_2', function() {
        $('#edit-student-form')[0].reset(); // This will reset the form to initial state
        
        // Reset the profile picture input and preview
        $('#edit-profile-picture-input').val(''); // Reset file input
        $('#edit-profile-picture-preview img').hide(); // Hide any previous image preview
        const file =
                document.querySelector('#edit_student_profile_photo');
            file.value = '';
        // Get student data from the button's data attributes
        var studentID = $(this).data('student-id');
        var studentName = $(this).data('student-name');
        var studentEmail = $(this).data('student-email');
        var studentClass = $(this).data('student-class');
        var studentSection = $(this).data('student-section');
        var admissionNumber = $(this).data('admission-number');
        var phoneNumber = $(this).data('phone-number');
        var rollNumber = $(this).data('roll-number');
        var admissionDate = $(this).data('admission-date');
        var gender = $(this).data('gender');
        var religion = $(this).data('religion');
        var bloodGroup = $(this).data('blood-group');
        var dateOfBirth = $(this).data('date-of-birth');
        var height = $(this).data('height');
        var weight = $(this).data('weight');
        var currentAddress = $(this).data('current-address');
        var permanentAddress = $(this).data('permanent-address');
        var profilePicture = $(this).data('profile-picture');

        // Populate the form fields
        $('#edit-student-id').val(studentID);
        $('#edit_student_name').val(studentName);
        $('#edit_student_email').val(studentEmail);
        $('#edit_class_name').val(studentClass).trigger('change'); // Trigger change to load sections
        $('#edit_section').val(studentSection);
        $('#edit_admission_number').val(admissionNumber);
        $('#edit_phone_number').val(phoneNumber);
        $('#edit_roll_number').val(rollNumber);
        $('#edit_admission_date').val(admissionDate);
        $('#edit_gender').val(gender);
        $('#edit_religion').val(religion);
        $('#edit_blood_group').val(bloodGroup);
        $('#edit_date_of_birth').val(dateOfBirth);
        $('#edit_height').val(height);
        $('#edit_weight').val(weight);
        $('#edit_current_address').val(currentAddress);
        $('#edit_permanent_address').val(permanentAddress);

        $('#edit-profile-picture-img').attr('src', profilePicture).show();


        // Show the modal
        $('#edit-student-modal_2').css('display', 'block');
    });

    // Close modal when the close button is clicked
    $('.close-modal').click(function() {
        $('#edit-student-modal_2').css('display', 'none');
    });

    // Close modal when clicking outside the modal
    $(window).click(function(event) {
        if (event.target.id === 'edit-student-modal_2') {
            $('#edit-student-modal_2').css('display', 'none');
        }
    });

    // Preload sections for all classes
    var sectionsData = {};
    <?php
    foreach ($class_sections as $row) {
        echo 'sectionsData["' . esc_attr($row['class_name']) . '"] = ' . json_encode(explode(',', $row['sections'])) . ';';
    }
    ?>

    // Update sections dropdown when class is selected
    $('#edit_class_name').change(function() {
        var selectedClass = $(this).val();
        var sectionSelect = $('#edit_section');

        if (selectedClass && sectionsData[selectedClass]) {
            sectionSelect.html('<option value="">Select Section</option>');
            sectionsData[selectedClass].forEach(function(section) {
                sectionSelect.append('<option value="' + section + '">' + section + '</option>');
            });
            sectionSelect.prop('disabled', false); // Enable the dropdown
        } else {
            sectionSelect.html('<option value="">Select Class First</option>').prop('disabled', true); // Disable the dropdown
        }
    });

    // Initialize the sections dropdown on page load
    $('#edit_class_name').trigger('change');
});
$(document).ready(function() {
    // Handle profile picture upload and preview
    $('#edit_student_profile_photo').change(function(event) {
        var reader = new FileReader();

        // Check if file is selected
        if (this.files && this.files[0]) {
            reader.onload = function(e) {
                // Show the image preview
                $('#edit-profile-picture-preview img').attr('src', e.target.result).show();
            }

            // Read the selected file
            reader.readAsDataURL(this.files[0]);
        } else {
            // Hide the preview if no file is selected
            $('#edit-profile-picture-preview img').hide();
        }
    });
});

function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    const header = section.parentElement.querySelector('.section-header');

    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        header.classList.add('active');
    } else {
        section.style.display = 'none';
        header.classList.remove('active');
    }
}
</script>
<?php
}
add_shortcode('edit_students_institute_dashboard', 'edit_students_institute_dashboard_shortcode');
