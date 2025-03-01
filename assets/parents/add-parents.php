<?php
// addparents.php
function add_parents_institute_dashboard_shortcode() {
    // Start output buffering
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'add-parent';
        include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?>
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <!-- Add Parent Form (Hidden by default) -->
            <div id="add-parent-form" style="display: block;">
                <h3>Add Parent</h3>
                <form method="POST" enctype="multipart/form-data">
                    <!-- Basic Details Section -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection('basic-details')">
                            <h4>Basic Details</h4>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="section-content" id="basic-details">
                            <label for="parent_id">Parent ID (Auto-generated):</label>
                            <input type="text" name="parent_id" value="<?php echo 'PAR-' . uniqid(); ?>" readonly>
                            <input type="hidden" name="educational_center_id" value="<?php echo esc_attr(get_educational_center_data()); ?>">
                            <label for="parent_student_ids">Student IDs:</label>
                            <input type="text" name="parent_student_ids">
                            <label for="parent_name">Parent Full Name:</label>
                            <input type="text" name="parent_name" required>
                            <label for="parent_email">Email:</label>
                            <input type="email" name="parent_email" required>
                            <label for="parent_phone_number">Phone Number:</label>
                            <input type="text" name="parent_phone_number">
                        </div>
                    </div>

                    <!-- Personal Details Section -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection('personal-details')">
                            <h4>Personal Details</h4>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="section-content" id="personal-details" style="display: none;">
                            <?php render_acf_select('teacher_gender', 'field_67c2c1083625b', 'Gender'); ?>
                            <label for="parent_date_of_birth">Date of Birth:</label>
                            <input type="date" name="parent_date_of_birth">
                            <?php render_acf_select('parent_religion', 'field_67c2c1083d672', 'Religion'); ?>
                            <?php render_acf_select('parent_blood_group', 'field_67c2c10844aec', 'Blood Group'); ?>
                            <label for="parent_height">Height (cm):</label>
                            <input type="number" name="parent_height" step="0.1">
                            <label for="parent_weight">Weight (kg):</label>
                            <input type="number" name="parent_weight" step="0.1">
                        </div>
                    </div>

                    <!-- Address Details Section -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection('address-details')">
                            <h4>Address Details</h4>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="section-content" id="address-details" style="display: none;">
                            <label for="parent_current_address">Current Address:</label>
                            <textarea name="parent_current_address"></textarea>
                            <label for="parent_permanent_address">Permanent Address:</label>
                            <textarea name="parent_permanent_address"></textarea>
                        </div>
                    </div>

                    <!-- Profile Photo Section -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection('profile-photo')">
                            <h4>Profile Photo</h4>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="section-content" id="profile-photo" style="display: none;">
                            <label for="parent_profile_photo">Profile Photo:</label>
                            <input type="file" name="parent_profile_photo">
                        </div>
                    </div>

                    <input type="submit" name="add_parent" value="Add Parent">
                </form>

                <!-- Form-specific JavaScript -->
                <script>
                function toggleSection(sectionId) {
                    var content = document.getElementById(sectionId);
                    content.style.display = content.style.display === 'none' ? 'block' : 'none';
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('form');
                    const fileInput = document.querySelector('input[name="parent_profile_photo"]');

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
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}

add_shortcode('add_parents_institute_dashboard', 'add_parents_institute_dashboard_shortcode');
?>