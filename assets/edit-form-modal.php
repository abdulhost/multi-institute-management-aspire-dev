
<!-- Edit Student Modal -->
<div id="edit-student-modal_2" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Edit Student Details</h3>
        <form id="edit-student-form" method="POST" enctype="multipart/form-data">
            <!-- Hidden field to store student ID -->
            <input type="hidden" name="student_id" id="edit-student-id">

            <!-- Basic Details Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('edit-basic-details')">
                    <h4>Basic Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="edit-basic-details">
                    <!-- Class Dropdown -->
                    <label for="edit_class_name">Class:</label>
                    <select name="class_name" id="edit_class_name" required>
                        <option value="">Select Class</option>
                        <?php foreach ($class_sections as $row) : ?>
                            <option value="<?php echo esc_attr($row['class_name']); ?>"><?php echo esc_html($row['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Section Dropdown -->
                    <label for="edit_section">Section:</label>
                    <select name="section" id="edit_section" required>
                        <option value="">Select Section</option>
                    </select>
                    <br>
                    <!-- Student Details -->
                    <label for="edit_admission_number">Admission Number</label>
                    <input type="text" name="admission_number" id="edit_admission_number" required>

                    <label for="edit_student_name">Student Full Name</label>
                    <input type="text" name="student_name" id="edit_student_name" required>

                    <label for="edit_student_email">Student Email</label>
                    <input type="email" name="student_email" id="edit_student_email">

                    <label for="edit_phone_number">Phone Number</label>
                    <input type="text" name="phone_number" id="edit_phone_number">

                    <!-- Roll Number -->
                    <label for="edit_roll_number">Roll Number:</label>
                    <input type="number" name="roll_number" id="edit_roll_number" required>

                    <!-- Admission Date -->
                    <label for="edit_admission_date">Admission Date:</label>
                    <input type="date" name="admission_date" id="edit_admission_date" required>
                </div>
            </div>

            <!-- Medical Details Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('edit-medical-details')">
                    <h4>Medical Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="edit-medical-details" style="display: none;">
                
 <!-- Gender Dropdown -->
 <?php
                $gender_field = get_field_object('field_67ab1ab5978fc');
                if ($gender_field) : ?>
                    <label for="gender">Gender:</label>
                    <select name="gender" id="edit_gender">
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
                    <select name="religion" id="edit_religion">
                        <option value="">Select Religion</option>
                        <?php foreach ($religion_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?> <br>
                    <!-- Blood Group Dropdown -->
                    
                    <?php
                $blood_group_field = get_field_object('field_67ab1c0197900');
                if ($blood_group_field) : ?>
                    <label for="blood_group">Blood Group:</label>
                    <select name="blood_group" id="edit_blood_group">
                        <option value="">Select Blood Group</option>
                        <?php foreach ($blood_group_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <br>
                    <label for="edit_date_of_birth">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="edit_date_of_birth">

                    <!-- Height -->
                    <label for="edit_height">Height (in cm):</label>
                    <input type="number" name="height" id="edit_height" required>

                    <!-- Weight -->
                    <label for="edit_weight">Weight (in kg):</label>
                    <input type="number" name="weight" id="edit_weight" required>
                </div>
            </div>

            <!-- Address Details Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('edit-address-details')">
                    <h4>Address Details</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="edit-address-details" style="display: none;">
                    <!-- Current Address -->
                    <label for="edit_current_address">Current Address:</label>
                    <textarea name="current_address" id="edit_current_address" rows="4" required></textarea>

                    <!-- Permanent Address -->
                    <label for="edit_permanent_address">Permanent Address:</label>
                    <textarea name="permanent_address" id="edit_permanent_address" rows="4" required></textarea>
                </div>
            </div>

            <!-- Profile Photo Section -->
            <div class="form-section">
                <div class="section-header" onclick="toggleSection('edit-profile-photo')">
                    <h4>Profile Photo</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-content" id="edit-profile-photo" style="display: none;">
                     <label for="edit_student_profile_photo">Student Profile Photo</label>
                    <input type="file" name="student_profile_photo" id="edit_student_profile_photo">
                   <!-- Display existing profile picture -->
                   <div id="edit-profile-picture-preview">
    <img id="edit-profile-picture-img" src="" alt="Profile Picture" style="max-width: 100px; display: none;">
</div>
     </div>
            </div>

            <!-- Submit Button -->
            <input type="submit" name="edit_student" value="Save Changes">
        </form>
    </div>
</div>

