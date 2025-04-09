<?php
// addteachers.php
function add_teachers_institute_dashboard_shortcode() {
    $educational_center_id = get_educational_center_data();

    if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
        return $educational_center_id;
    }
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }  // Start output buffering
    ob_start();
    ?>
       <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        echo render_admin_header(wp_get_current_user());
        if (!is_center_subscribed($educational_center_id)) {
            return render_subscription_expired_message($educational_center_id);
        }
        $active_section = 'add-teacher';
        include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?>
            <div class="form-container attendance-entry-wrapper attendance-content-wrapper">

    <!-- Add Teacher Form (Hidden by default) -->
    <div id="add-teacher-form" style="display: block;">
        <h3>Add Teacher</h3>
        <form method="POST" enctype="multipart/form-data">
            <!-- Basic Details Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('basic-details')">
                    <h4>Basic Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="basic-details">
                    <label for="teacher_id">Teacher ID (Auto-generated):</label>
                    <input type="text" name="teacher_id" value="<?php echo 'TEA-' . uniqid(); ?>" readonly>
                    <input type="hidden" name="educational_center_id" value="<?php echo esc_attr(get_educational_center_data()); ?>">
                    <label for="teacher_name">Teacher Full Name</label>
                <input type="text" name="teacher_name" required>
                
                    <label for="teacher_email">Email:</label>
                    <input type="email" name="teacher_email" required>
                    <label for="teacher_phone_number">Phone Number:</label>
                    <input type="text" name="teacher_phone_number">
                    <label for="teacher_roll_number">Roll Number:</label>
                    <input type="number" name="teacher_roll_number" required>
                    <label for="teacher_admission_date">Admission Date:</label>
                    <input type="date" name="teacher_admission_date" required>
                </div>
            </div>

            <!-- Medical Details Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('medical-details')">
                    <h4>Medical Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="medical-details" style="display: none;">
                    <?php render_acf_select('teacher_gender', 'field_67baed90b66de', 'Gender'); ?>
                    <?php render_acf_select('teacher_religion', 'field_67baed90bdebb', 'Religion'); ?>
                    <?php render_acf_select('teacher_blood_group', 'field_67baed90c555b', 'Blood Group'); ?>
                    <label for="teacher_date_of_birth">Date of Birth:</label>
                    <input type="date" name="teacher_date_of_birth">
                    <label for="teacher_height">Height (cm):</label>
                    <input type="number" name="teacher_height" required>
                    <label for="teacher_weight">Weight (kg):</label>
                    <input type="number" name="teacher_weight" required>
                </div>
            </div>

            <!-- Address Details Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('address-details')">
                    <h4>Address Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="address-details" style="display: none;">
                    <label for="teacher_current_address">Current Address:</label>
                    <textarea name="teacher_current_address" required></textarea>
                    <label for="teacher_permanent_address">Permanent Address:</label>
                    <textarea name="teacher_permanent_address" required></textarea>
                </div>
            </div>

            <!-- Profile Photo Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('profile-photo')">
                    <h4>Profile Photo</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="profile-photo" style="display: none;">
                    <label for="teacher_profile_photo">Profile Photo:</label>
                    <input type="file" name="teacher_profile_photo">
                </div>
            </div>

            <input type="submit" name="add_teacher" value="Add Teacher">
        </form>

        <!-- Form-specific JavaScript -->
        <script>
        function toggleSection(sectionId) {
            var content = document.getElementById(sectionId);
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const fileInput = document.querySelector('input[name="teacher_profile_photo"]');

            form.addEventListener('submit', function(event) {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    const maxSize = 500 * 1024; // 500KB

                    if (!allowedTypes.includes(file.type)) {
                        alert('Invalid file type. Please upload a JPEG, PNG, or GIF image.');
                        event.preventDefault();
                    } else if (file.size > maxSize) {
                        alert('File size exceeds the 500KB limit.');
                        event.preventDefault();
                    }
                }
            });
        });
        </script>
    </div>
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}

add_shortcode('add_teachers_institute_dashboard', 'add_teachers_institute_dashboard_shortcode');
?>