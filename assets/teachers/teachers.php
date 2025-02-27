<?php
// teachers.php
function teachers_institute_dashboard_shortcode() {
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    // Query Educational Center
    $educational_center = get_posts([
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ]);

    if (empty($educational_center)) {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);

    // Handle teacher deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['student_id'])) {
        $teacher_id = intval($_GET['student_id']);
        if (wp_delete_post($teacher_id, true)) {
            wp_redirect(home_url('/institute-dashboard/#teachers'));
            exit;
        } else {
            echo '<p class="error-message">Error deleting teacher.</p>';
        }
    }

    // Start output buffering
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'teachers';
        include plugin_dir_path(__FILE__) . '../sidebar.php';
        ?>
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
        
<!-- Add Teacher Button -->
<button id="add-teacher-btn" onclick="toggleForm()">+ Add Teacher</button>

<!-- Add Teacher Form (Hidden by default) -->
<div id="add-teacher-form" style="display: none;">
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
</div>

<!-- Reusable JavaScript -->
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

<?php
// Reusable function to render ACF select fields
function render_acf_select($name, $field_key, $label) {
    $field = get_field_object($field_key);
    if ($field) : ?>
        <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?>:</label>
        <select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>">
            <option value="">Select <?php echo esc_html($label); ?></option>
            <?php foreach ($field['choices'] as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif;
}

// Placeholder for retrieving educational center ID (assuming helper.php provides this)
function get_educational_center_data() {
    $current_user = wp_get_current_user();
    $educational_center = get_posts([
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $current_user->user_login,
        'posts_per_page' => 1,
    ]);
    return $educational_center ? get_post_meta($educational_center[0]->ID, 'educational_center_id', true) : '<p>No Educational Center found.</p>';
}
?>
        </div>
    </div>

    <!-- JavaScript for toggling form and search -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
    function toggleForm() {
        var form = document.getElementById('add-teacher-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    $(document).ready(function() {
        $('#search_text').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#teachers-table tbody tr').each(function() {
                var teacherID = $(this).find('td').eq(0).text().toLowerCase();
                var teacherName = $(this).find('td').eq(1).text().toLowerCase();
                var teacherEmail = $(this).find('td').eq(2).text().toLowerCase();
                if (teacherID.includes(searchText) || teacherName.includes(searchText) || teacherEmail.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('teachers_institute_dashboard', 'teachers_institute_dashboard_shortcode');
?>