<?php
function add_students_institute_dashboard_shortcode($educational_center_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'class_sections';
    $charset_collate = $wpdb->get_charset_collate();

    // Uncomment and adjust table creation if needed
    /*
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            class_name varchar(255) NOT NULL,
            sections text NOT NULL,
            education_center_id varchar(255), -- Added to match query
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    */

    // Fetch educational center ID (assuming this function exists)
    $educational_center_id = get_educational_center_data();
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }
    if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
        return $educational_center_id;
    }

    // Fetch all classes and sections
    $class_sections = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id),
        ARRAY_A
    );
    $new_student_id = get_unique_id_for_role('students', $educational_center_id);

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
        $active_section = 'add-students';
        include(plugin_dir_path(__FILE__) . 'sidebar.php');
            ?>
        <!-- </div> -->
<div id="add-student-form" style="display: block; width:100%">
    <h3>Add Student</h3>
    <form method="POST" enctype="multipart/form-data">
        <!-- Basic Details Section -->
        <div class="form-section">
            <div class="section-header" onclick="toggleSection('basic-details')">
                <h4>Basic Details</h4>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="section-content" id="basic-details">
                <!-- Class Dropdown -->
                <label for="class_name">Class:</label>
                <select name="class_name" id="class_nameadd" required>
    <option value="">Select Class</option>
    <?php foreach ($class_sections as $row) : ?>
        <?php $normalized_class = strtolower(trim($row['class_name'])); ?>
        <option value="<?php echo esc_attr($normalized_class); ?>"><?php echo esc_html($row['class_name']); ?></option>
    <?php endforeach; ?>
</select>
<label for="section">Section:</label>

<select name="section" id="sectionadds" disabled>
    <option value="">Select Class First</option>
</select>
<br>
                <!-- Student Details -->
                <label for="admission_number">Admission Number</label>
                <input type="text" name="admission_number" required>
                <br>
                <label for="student_name">Student Full Name</label>
                <input type="text" name="student_name" required>
                <br>
                <label for="student_email">Student Email</label>
                <input type="email" name="student_email">
                <br>
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number">
                <br>
                <!-- Hidden field for educational center ID -->
                <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">
                <label for="student_id">Student ID (Auto-generated)</label>
                <input type="text" name="student_id" value="<?php echo esc_attr($new_student_id); ?>" readonly>
                <br>
                <!-- Roll Number -->
                <label for="roll_number">Roll Number:</label>
                <input type="number" name="roll_number" id="roll_number" required>
                <br>
                <!-- Admission Date -->
                <label for="admission_date">Admission Date:</label>
                <input type="date" name="admission_date" id="admission_date" required>
            </div>
        </div>

        <!-- Medical Details Section -->
        <div class="form-section">
            <div class="section-header" onclick="toggleSection('medical-details')">
                <h4>Medical Details</h4>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="section-content" id="medical-details" style="display: none;">
                <?php
                $gender_field = get_field_object('field_67ab1ab5978fc');
                if ($gender_field) : ?>
                    <label for="gender">Gender:</label>
                    <select name="gender" id="gender">
                        <option value="">Select Gender</option>
                        <?php foreach ($gender_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <?php
                $religion_field = get_field_object('field_67ab1b6d978fe');
                if ($religion_field) : ?>
                    <label for="religion">Religion:</label>
                    <select name="religion" id="religion">
                        <option value="">Select Religion</option>
                        <?php foreach ($religion_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <br>
                <?php
                $blood_group_field = get_field_object('field_67ab1c0197900');
                if ($blood_group_field) : ?>
                    <label for="blood_group">Blood Group:</label>
                    <select name="blood_group" id="blood_group">
                        <option value="">Select Blood Group</option>
                        <?php foreach ($blood_group_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <br>
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" name="date_of_birth">
                <br>
                <label for="height">Height (in cm):</label>
                <input type="number" name="height" id="height" required>
                <br>
                <label for="weight">Weight (in kg):</label>
                <input type="number" name="weight" id="weight" required>
            </div>
        </div>

        <!-- Address Details Section -->
        <div class="form-section">
            <div class="section-header" onclick="toggleSection('address-details')">
                <h4>Address Details</h4>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="section-content" id="address-details" style="display: none;">
                <label for="current_address">Current Address:</label>
                <textarea name="current_address" id="current_address" rows="4" required></textarea>
                <label for="permanent_address">Permanent Address:</label>
                <textarea name="permanent_address" id="permanent_address" rows="4" required></textarea>
            </div>
        </div>

        <!-- Student Profile Photo -->
        <div class="form-section">
            <div class="section-header" onclick="toggleSection('profile-photo')">
                <h4>Profile Photo</h4>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="section-content" id="profile-photo" style="display: none;">
                <label for="student_profile_photo">Student Profile Photo</label>
                <input type="file" name="student_profile_photo">
            </div>
        </div>

        <!-- Submit Button -->
        <input type="submit" name="add_student" value="Add Student">
    </form>
</div>
</div>

<script>
jQuery(document).ready(function($) {
    var sectionsData = {};
    <?php
    foreach ($class_sections as $row) {
        $normalized_class = strtolower(trim($row['class_name']));
        $sections = !empty($row['sections']) ? explode(',', $row['sections']) : [];
        echo 'sectionsData["' . esc_attr($normalized_class) . '"] = ' . json_encode($sections) . ';';
    }
    ?>

    $('#class_nameadd').change(function() {
        var selectedClass = $(this).val();
        var sectionSelect = $('#sectionadds');
          if (selectedClass && sectionsData[selectedClass] && sectionsData[selectedClass].length > 0) {
            sectionSelect.empty();
            sectionSelect.append('<option value="">Select Section</option>');
            sectionsData[selectedClass].forEach(function(section) {
                sectionSelect.append('<option value="' + section + '">' + section + '</option>');
            });
            sectionSelect.prop('disabled', false);
        } else {
            sectionSelect.empty();
            sectionSelect.append('<option value="">No Sections Available</option>');
            sectionSelect.prop('disabled', true);
       
        }
    });

    $('#class_nameadd').trigger('change');
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const fileInput = document.querySelector('input[name="student_profile_photo"]');

    form.addEventListener('submit', function(event) {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 500 * 1024; // 500KB

            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload a JPEG, PNG, or GIF image.');
                event.preventDefault();
                return;
            }

            if (file.size > maxSize) {
                alert('File size exceeds the 500KB limit.');
                event.preventDefault();
                return;
            }
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
    return ob_get_clean();
}

add_shortcode('add_students_institute_dashboard', 'add_students_institute_dashboard_shortcode');