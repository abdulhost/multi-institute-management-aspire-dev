<!-- Add Student Button -->
<button id="add-student-btn" onclick="toggleForm()">+ Add Student</button>
<?php
global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $charset_collate = $wpdb->get_charset_collate();

// Fetch all classes and sections
// $educational_center_id = get_educational_center_data();
if (empty($educational_center_id)) {
    wp_redirect(home_url('/login')); // Redirect to login page
    exit();
}
// if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
//     return $educational_center_id;
// }
// Fetch all classes and sections
$class_sections = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $educational_center_id),
    ARRAY_A
);
// $class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>

<!-- Add Student Form (Hidden by default) -->
<div id="add-student-form" style="display: none;">
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
                <label for="class">Class:</label>
                <select name="class" id="class_nameadd" required>
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
                <input type="text" name="admission_number" maxlength="50">
                <br>
                <label for="student_name">Student Full Name</label>
                <input type="text" name="student_name" maxlength="255" required>
                <br>
                <label for="student_email">Student Email</label>
                <input type="email" name="student_email" maxlength="100">
                <br>
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" maxlength="50">
                <br>
                <!-- Hidden field for educational center ID -->
                <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">
                <label for="student_id">Student ID (Auto-generated)</label>
                <input type="text" name="student_id" value="<?php echo 'STU-' . uniqid(); ?>" maxlength="255" readonly>
                <br>
                <!-- Roll Number -->
                <label for="roll_number">Roll Number:</label>
                <input type="text" name="roll_number" id="roll_number" maxlength="50">
                <br>
                <!-- Admission Date -->
                <label for="admission_date">Admission Date:</label>
                <input type="date" name="admission_date" id="admission_date">
            </div>
        </div>

         <!-- Medical Details Section -->
         <div class="form-section">
                <div class="section-header" onclick="toggleSection('medical-details')">
                    <h4>Medical Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="medical-details" style="display: none;">
                    <label for="gender">Gender:</label>
                    <select name="gender" id="gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <br>
                    <label for="religion">Religion:</label>
                    <select name="religion" id="religion">
                        <option value="">Select Religion</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Muslim">Muslim</option>
                        <option value="Christian">Christian</option>
                        <option value="Sikh">Sikh</option>
                        <option value="Other">Other</option>
                    </select>
                    <br>
                    <label for="blood_group">Blood Group:</label>
                    <select name="blood_group" id="blood_group">
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    <br>
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" name="date_of_birth">
                    <br>
                    <label for="height">Height (in cm):</label>
                    <input type="number" name="height" step="0.1">
                    <br>
                    <label for="weight">Weight (in kg):</label>
                    <input type="number" name="weight" step="0.1">
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
                <textarea name="current_address" id="current_address" rows="4"></textarea>
                <label for="permanent_address">Permanent Address:</label>
                <textarea name="permanent_address" id="permanent_address" rows="4"></textarea>
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
                <input type="file" name="student_profile_photo" accept="image/jpeg,image/png,image/gif">
            </div>
        </div>

        <!-- Submit Button -->
        <input type="submit" name="add_student" value="Add Student">
    </form>
</div>

<!-- JavaScript to dynamically populate sections based on selected class -->
<script>
// function toggleForm() {
//     var form = document.getElementById('add-student-form');
//     if (form.style.display === 'none') {
//         form.style.display = 'block';
//     } else {
//         form.style.display = 'none';
//     }
// }
jQuery(document).ready(function($) {
    var sectionsData = {};
    <?php
    foreach ($class_sections as $row) {
        echo 'sectionsData["' . esc_attr($row['class_name']) . '"] = ' . json_encode(explode(',', $row['sections'])) . ';';
    }
    ?>

    $('#class_nameadd').change(function() {
        var selectedClass = $(this).val();
        var sectionSelect = $('#sectionadd');

        if (selectedClass && sectionsData[selectedClass]) {
            sectionSelect.html('<option value="">Select Section</option>');
            sectionsData[selectedClass].forEach(function(section) {
                sectionSelect.append('<option value="' + section + '">' + section + '</option>');
            });
            sectionSelect.prop('disabled', false);
        } else {
            sectionSelect.html('<option value="">Select Class First</option>').prop('disabled', true);
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
</script>