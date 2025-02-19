<?php
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$charset_collate = $wpdb->get_charset_collate();

// Check if the table exists before creating it
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        class_name varchar(255) NOT NULL,
        sections text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Fetch all classes and sections
$class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>

<div id="add-student-form" style="display: block;">
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
                        <option value="<?php echo esc_attr($row['class_name']); ?>"><?php echo esc_html($row['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Section Dropdown -->
                <label for="section">Section:</label>
                <select name="section" id="sectionadd" required disabled>
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
                <!-- Hidden field to pass the educational center ID -->
                <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">
                <!-- <br> -->
                <!-- Student ID Field -->
                <label for="student_id">Student ID (Auto-generated)</label>
                <input type="text" name="student_id" value="<?php echo 'STU-' . uniqid(); ?>" readonly>
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
                <!-- Gender Dropdown -->
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

                <!-- Religion Dropdown -->
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
                <!-- Blood Group Dropdown -->
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
                <!-- Height -->
                <label for="height">Height (in cm):</label>
                <input type="number" name="height" id="height" required>
                <br>
                <!-- Weight -->
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
                <!-- Current Address -->
                <label for="current_address">Current Address:</label>
                <textarea name="current_address" id="current_address" rows="4" required></textarea>

                <!-- Permanent Address -->
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
<script>
jQuery(document).ready(function($) {
    // Preload sections for all classes
    var sectionsData = {};
    <?php
    foreach ($class_sections as $row) {
        echo 'sectionsData["' . esc_attr($row['class_name']) . '"] = ' . json_encode(explode(',', $row['sections'])) . ';';
    }
    ?>

    // Update sections dropdown when class is selected
    $('#class_nameadd').change(function() {
        var selectedClass = $(this).val();
        var sectionSelect = $('#sectionadd');

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
// /section wise toggle
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    const header = section.parentElement.querySelector('.section-header');
    const icon = header.querySelector('.toggle-icon');

    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        header.classList.add('active');
    } else {
        section.style.display = 'none';
        header.classList.remove('active');
    }
}
</script>