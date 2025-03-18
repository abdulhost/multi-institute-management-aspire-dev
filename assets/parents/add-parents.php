<?php
// addparents.php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

function add_parents_institute_dashboard_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

    if (empty($educational_center_id)) {
        return '<p>No Educational Center found for this user.</p>';
    }

    // Start output buffering
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          if (is_teacher($atts)) { 
        } else {
        $active_section = 'add-parent';
        include plugin_dir_path(__FILE__) . '../sidebar.php';} // Adjust path as needed
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
                            <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">
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

function handle_add_parent_submission($atts = []) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_parent'])) {
        // Determine educational_center_id and teacher_id based on user type
        $current_user = wp_get_current_user();
        if (!$current_user->exists()) {
            echo '<p>Please log in to access this feature.</p>';
            return;
        }
        if (is_teacher($atts)) { 
            $educational_center_id = educational_center_teacher_id();
            $current_teacher_id = aspire_get_current_teacher_id();
        } else {
            $educational_center_id = get_educational_center_data();
            $current_teacher_id = get_current_teacher_id();
        }

        if (empty($educational_center_id)) {
            echo '<p>No Educational Center found for this user.</p>';
            return;
        }

        // Use the educational_center_id from the form if provided, otherwise fallback to determined value
        $educational_center_id = isset($_POST['educational_center_id']) ? sanitize_text_field($_POST['educational_center_id']) : $educational_center_id;

        $gender = isset($_POST['teacher_gender']) ? sanitize_text_field($_POST['teacher_gender']) : '';
        $attachment_id = null;

        if (isset($_FILES['parent_profile_photo']) && $_FILES['parent_profile_photo']['error'] === 0) {
            $file = $_FILES['parent_profile_photo'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 500 * 1024;

            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_mime_types)) {
                echo 'Invalid file type. Please upload a JPEG, PNG, or GIF image.';
                return;
            }

            if ($file['size'] > $max_file_size) {
                echo 'File size exceeds the 500KB limit.';
                return;
            }

            $upload_dir = wp_upload_dir();
            $file_name = 'profile_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $target_path = $upload_dir['path'] . '/' . $file_name;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $file_url = $upload_dir['url'] . '/' . $file_name;
                $attachment = array(
                    'guid' => $file_url,
                    'post_mime_type' => $file_type,
                    'post_title' => sanitize_text_field($file_name),
                    'post_content' => '',
                    'post_status' => 'inherit',
                );

                $attachment_id = wp_insert_attachment($attachment, $target_path);
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

        $parent_id = sanitize_text_field($_POST['parent_id']);
        $parent_student_ids = sanitize_text_field($_POST['parent_student_ids']);
        $parent_name = sanitize_text_field($_POST['parent_name']);
        $parent_email = sanitize_email($_POST['parent_email']);
        $parent_phone_number = sanitize_text_field($_POST['parent_phone_number']);
        $parent_religion = sanitize_text_field($_POST['parent_religion']);
        $parent_blood_group = sanitize_text_field($_POST['parent_blood_group']);
        $parent_date_of_birth = sanitize_text_field($_POST['parent_date_of_birth']);
        $parent_height = isset($_POST['parent_height']) ? sanitize_text_field($_POST['parent_height']) : '';
        $parent_weight = isset($_POST['parent_weight']) ? sanitize_text_field($_POST['parent_weight']) : '';
        $parent_current_address = isset($_POST['parent_current_address']) ? sanitize_textarea_field($_POST['parent_current_address']) : '';
        $parent_permanent_address = isset($_POST['parent_permanent_address']) ? sanitize_textarea_field($_POST['parent_permanent_address']) : '';

        $parent_post_id = wp_insert_post(array(
            'post_title' => $parent_id,
            'post_type' => 'parent',
            'post_status' => 'publish',
            'meta_input' => array(
                'parent_id' => $parent_id,
                'parent_student_ids' => $parent_student_ids,
                'parent_name' => $parent_name,
                'parent_email' => $parent_email,
                'educational_center_id' => $educational_center_id,
                'parent_phone_number' => $parent_phone_number,
                'teacher_gender' => $gender,
                'parent_religion' => $parent_religion,
                'parent_blood_group' => $parent_blood_group,
                'parent_date_of_birth' => $parent_date_of_birth,
                'parent_height' => $parent_height,
                'parent_weight' => $parent_weight,
                'parent_current_address' => $parent_current_address,
                'parent_permanent_address' => $parent_permanent_address,
            ),
        ));
     
        if ($parent_post_id && $attachment_id) {
            update_field('field_67c2c10841064', $attachment_id, $parent_post_id); // ACF field key for parent_profile_photo
           // Redirect based on whether the user is a teacher or not
           if (is_teacher($current_user->ID)) {
        wp_redirect(home_url('/teacher-dashboard/?section=parents'));
    } else {
        wp_redirect(home_url('/institute-dashboard/parents'));
    }
    exit;
        } elseif ($parent_post_id) {           
           // Redirect based on whether the user is a teacher or not
           if (is_teacher($current_user->ID)) {
        wp_redirect(home_url('/teacher-dashboard/?section=parents'));
    } else {
        wp_redirect(home_url('/institute-dashboard/parents'));
    }
    exit;

        } else {
            echo '<p class="error-message">Error adding parent.</p>';
        }
    }

   
}

// Hook the function to run on init
add_action('init', 'handle_add_parent_submission');


