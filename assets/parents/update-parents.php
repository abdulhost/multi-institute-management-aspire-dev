<?php
// parent-list.php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

function edit_parents_institute_dashboard_shortcode($atts) {
    if (!is_user_logged_in()) {
        // wp_redirect(home_url('/login'));
        // exit();
        return false;
    }

    // Get current user
    $current_user = wp_get_current_user();
    $is_teacher_user = is_teacher($current_user->ID);
    
    // Determine educational_center_id and teacher_id based on user type
    if ($is_teacher_user) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login'));
            exit();
    }

    // Handle parent deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['parent_post_id'])) {
        $parent_post_id = intval($_GET['parent_post_id']);
        $stored_edu_center_id = get_post_meta($parent_post_id, 'educational_center_id', true);
        if ($stored_edu_center_id === $educational_center_id && wp_delete_post($parent_post_id, true)) {
            $redirect_url = $is_teacher_user ? home_url('/teacher-dashboard/?section=parents&action=edit-parents') : home_url('/institute-dashboard/edit-parents');
            wp_redirect($redirect_url);
            exit;
        } else {
            echo '<p class="error-message">Error deleting parent or insufficient permissions.</p>';
        }
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        if (!$is_teacher_user) {
            $active_section = 'update-parent';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');
        }
        ?>
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <div class="form-group search-form">
                <div class="input-group">
                    <span class="input-group-addon">Search</span>
                    <input type="text" id="search_text_parent" placeholder="Search by Parent Details" class="form-control" />
                </div>
            </div>

            <div id="result">
                <h3>Parent List</h3>
                <table id="parents-table" border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Parent ID</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $parents = get_posts(array(
                            'post_type' => 'parent',
                            'meta_key' => 'educational_center_id',
                            'meta_value' => $educational_center_id,
                            'posts_per_page' => -1,
                        ));

                        if (!empty($parents)) {
                            foreach ($parents as $parent) {
                                $parent_id = get_post_meta($parent->ID, 'parent_id', true);
                                $parent_email = get_post_meta($parent->ID, 'parent_email', true);
                                $parent_phone_number = get_post_meta($parent->ID, 'parent_phone_number', true);
                                $parent_student_ids = get_post_meta($parent->ID, 'parent_student_ids', true);
                                $parent_name = get_post_meta($parent->ID, 'parent_name', true);
                                $parent_gender = get_post_meta($parent->ID, 'parent_gender', true);
                                $parent_date_of_birth = get_post_meta($parent->ID, 'parent_date_of_birth', true);
                                $parent_religion = get_post_meta($parent->ID, 'parent_religion', true);
                                $parent_blood_group = get_post_meta($parent->ID, 'parent_blood_group', true);
                                $parent_height = get_post_meta($parent->ID, 'parent_height', true);
                                $parent_weight = get_post_meta($parent->ID, 'parent_weight', true);
                                $parent_current_address = get_post_meta($parent->ID, 'parent_current_address', true);
                                $parent_permanent_address = get_post_meta($parent->ID, 'parent_permanent_address', true);
                                $profile_picture = get_post_meta($parent->ID, 'parent_profile_photo', true);
                                $profile_picture_url = $profile_picture ? wp_get_attachment_url($profile_picture) : '';

                                echo '<tr class="parent-row">
                                    <td>' . esc_html($parent_id) . '</td>
                                    <td>' . esc_html($parent_email) . '</td>
                                    <td>' . esc_html($parent_phone_number) . '</td>
                                    <td>
                                        <a href="#edit-parents" class="edit-btn_parent"
                                           data-parent-post-id="' . esc_attr($parent->ID) . '"
                                           data-parent-id="' . esc_attr($parent_id) . '"
                                           data-parent-email="' . esc_attr($parent_email) . '"
                                           data-parent-phone-number="' . esc_attr($parent_phone_number) . '"
                                           data-parent-student-ids="' . esc_attr($parent_student_ids) . '"
                                           data-parent-name="' . esc_attr($parent_name) . '"
                                           data-parent-gender="' . esc_attr($parent_gender) . '"
                                           data-parent-date-of-birth="' . esc_attr($parent_date_of_birth) . '"
                                           data-parent-religion="' . esc_attr($parent_religion) . '"
                                           data-parent-blood-group="' . esc_attr($parent_blood_group) . '"
                                           data-parent-height="' . esc_attr($parent_height) . '"
                                           data-parent-weight="' . esc_attr($parent_weight) . '"
                                           data-parent-current-address="' . esc_attr($parent_current_address) . '"
                                           data-parent-permanent-address="' . esc_attr($parent_permanent_address) . '"
                                           data-profile-picture="' . esc_attr($profile_picture_url) . '">Edit</a> |
                                      <button class="button button-secondary delete-parent-btn"
                                                data-parent-post-id="' . esc_attr($parent->ID) . '"
                                                data-edu-center-id="' . esc_attr($educational_center_id) . '"
                                                data-nonce="' . wp_create_nonce('delete_parent_' . $parent->ID) . '">Delete</button>
                                        <span class="delete-message" style="display: none; margin-left: 10px;"></span>
                                          </td>
                                  </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">No parents found for this Educational Center.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="edit-parent-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Edit Parent</h3>
            <form id="edit-parent-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_parent_submission">
                <input type="hidden" name="parent_post_id" id="edit_parent_post_id">
                <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">
                <input type="hidden" name="redirect_url" value="<?php echo esc_attr($is_teacher_user ? home_url('/teacher-dashboard/?section=parents&action=edit-parents') : home_url('/institute-dashboard/edit-parents')); ?>">
                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-basic-details')">
                        <h4>Basic Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-basic-details">
                        <label for="edit_parent_id">Parent ID:</label>
                        <input type="text" name="parent_id" id="edit_parent_id" readonly>
                        <label for="edit_parent_student_ids">Student IDs:</label>
                        <input type="text" name="parent_student_ids" id="edit_parent_student_ids">
                        <label for="edit_parent_name">Parent Name:</label>
                        <input type="text" name="parent_name" id="edit_parent_name" required>
                        <label for="edit_parent_email">Email:</label>
                        <input type="email" name="parent_email" id="edit_parent_email" required>
                        <label for="edit_parent_phone_number">Phone Number:</label>
                        <input type="text" name="parent_phone_number" id="edit_parent_phone_number">
                    </div>
                </div>

                <!-- Other form sections remain unchanged for brevity -->
                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-personal-details')">
                        <h4>Personal Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-personal-details" style="display: none;">
                        <?php
                        $gender_field = get_field_object('field_67c2c1083625b');
                        if ($gender_field) : ?>
                            <label for="edit_parent_gender">Gender:</label>
                            <select name="parent_gender" id="edit_parent_gender">
                                <option value="">Select Gender</option>
                                <?php foreach ($gender_field['choices'] as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <label for="edit_parent_date_of_birth">Date of Birth:</label>
                        <input type="date" name="parent_date_of_birth" id="edit_parent_date_of_birth">
                        <?php
                        $religion_field = get_field_object('field_67c2c1083d672');
                        if ($religion_field) : ?>
                            <label for="edit_parent_religion">Religion:</label>
                            <select name="parent_religion" id="edit_parent_religion">
                                <option value="">Select Religion</option>
                                <?php foreach ($religion_field['choices'] as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <?php
                        $blood_group_field = get_field_object('field_67c2c10844aec');
                        if ($blood_group_field) : ?>
                            <label for="edit_parent_blood_group">Blood Group:</label>
                            <select name="parent_blood_group" id="edit_parent_blood_group">
                                <option value="">Select Blood Group</option>
                                <?php foreach ($blood_group_field['choices'] as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <label for="edit_parent_height">Height (cm):</label>
                        <input type="number" name="parent_height" id="edit_parent_height" step="0.1">
                        <label for="edit_parent_weight">Weight (kg):</label>
                        <input type="number" name="parent_weight" id="edit_parent_weight" step="0.1">
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-address-details')">
                        <h4>Address Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-address-details" style="display: none;">
                        <label for="edit_parent_current_address">Current Address:</label>
                        <textarea name="parent_current_address" id="edit_parent_current_address"></textarea>
                        <label for="edit_parent_permanent_address">Permanent Address:</label>
                        <textarea name="parent_permanent_address" id="edit_parent_permanent_address"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-profile-photo')">
                        <h4>Profile Photo</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-profile-photo" style="display: none;">
                        <label for="edit_parent_profile_photo">Profile Photo:</label>
                        <div id="edit-profile-picture-preview">
                            <img id="edit-profile-picture-img" style="max-width: 200px; display: none;" />
                        </div>
                        <input type="file" name="parent_profile_photo" id="edit_parent_profile_photo">
                    </div>
                </div>

                <input type="submit" name="edit_parent" value="Update Parent">
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#search_text_parent').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#parents-table tbody tr').each(function() {
                var parentID = $(this).find('td').eq(0).text().toLowerCase();
                var parentEmail = $(this).find('td').eq(1).text().toLowerCase();
                var parentPhone = $(this).find('td').eq(2).text().toLowerCase();
                if (parentID.includes(searchText) || parentEmail.includes(searchText) || parentPhone.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $(document).on('click', '.edit-btn_parent', function() {
            $('#edit-parent-form')[0].reset();
            $('#edit-profile-picture-img').hide();
            const file = document.querySelector('#edit_parent_profile_photo');
            file.value = '';

            var parentPostId = $(this).data('parent-post-id');
            var parentID = $(this).data('parent-id');
            var parentEmail = $(this).data('parent-email');
            var parentPhoneNumber = $(this).data('parent-phone-number');
            var parentStudentIds = $(this).data('parent-student-ids');
            var parentName = $(this).data('parent-name');
            var parentGender = $(this).data('parent-gender');
            var parentDateOfBirth = $(this).data('parent-date-of-birth');
            var parentReligion = $(this).data('parent-religion');
            var parentBloodGroup = $(this).data('parent-blood-group');
            var parentHeight = $(this).data('parent-height');
            var parentWeight = $(this).data('parent-weight');
            var parentCurrentAddress = $(this).data('parent-current-address');
            var parentPermanentAddress = $(this).data('parent-permanent-address');
            var profilePicture = $(this).data('profile-picture');

            $('#edit_parent_post_id').val(parentPostId);
            $('#edit_parent_id').val(parentID);
            $('#edit_parent_email').val(parentEmail);
            $('#edit_parent_phone_number').val(parentPhoneNumber);
            $('#edit_parent_student_ids').val(parentStudentIds);
            $('#edit_parent_name').val(parentName);
            $('#edit_parent_gender').val(parentGender || '');
            $('#edit_parent_date_of_birth').val(parentDateOfBirth);
            $('#edit_parent_religion').val(parentReligion || '');
            $('#edit_parent_blood_group').val(parentBloodGroup || '');
            $('#edit_parent_height').val(parentHeight);
            $('#edit_parent_weight').val(parentWeight);
            $('#edit_parent_current_address').val(parentCurrentAddress);
            $('#edit_parent_permanent_address').val(parentPermanentAddress);
            if (profilePicture) {
                $('#edit-profile-picture-img').attr('src', profilePicture).show();
            }

            $('#edit-parent-modal').css('display', 'block');
        });

        $('.close-modal').click(function() {
            $('#edit-parent-modal').css('display', 'none');
        });

        $(window).click(function(event) {
            if (event.target.id === 'edit-parent-modal') {
                $('#edit-parent-modal').css('display', 'none');
            }
        });

        $('#edit_parent_profile_photo').change(function(event) {
            var reader = new FileReader();
            if (this.files && this.files[0]) {
                reader.onload = function(e) {
                    $('#edit-profile-picture-img').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                $('#edit-profile-picture-img').hide();
            }
        });

    $(document).on('click', '.delete-parent-btn', function(e) {
            e.preventDefault();

            var $button = $(this);
            var parentPostId = $button.data('parent-post-id');
            var eduCenterId = $button.data('edu-center-id');
            var nonce = $button.data('nonce');
            var $row = $button.closest('tr');
            var $message = $row.find('.delete-message');

            if (!confirm('Are you sure you want to delete this parent?')) {
                return;
            }

            $button.prop('disabled', true);
            $message.hide().removeClass('success error').text('Deleting...').show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'delete_parent',
                    parent_post_id: parentPostId,
                    educational_center_id: eduCenterId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $message.text(response.data).addClass('success').show();
                        setTimeout(function() {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                if ($('#parents-table tbody tr').length === 0) {
                                    $('#parents-table').replaceWith('<tr><td colspan="4">No parents found for this Educational Center.</td></tr>');
                                }
                            });
                        }, 1000);
                    } else {
                        $message.text(response.data).addClass('error').show();
                        $button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $message.text('Error occurred while deleting: ' + error).addClass('error').show();
                    $button.prop('disabled', false);
                }
            });
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

add_shortcode('edit_parents_institute_dashboard', 'edit_parents_institute_dashboard_shortcode');

function handle_parent_update_submission() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit_parent']) || !isset($_POST['action']) || $_POST['action'] !== 'edit_parent_submission') {
        return;
    }

    if (!is_user_logged_in()) {
        wp_die('Please log in to update parent information.');
        return;
    }

    $current_user = wp_get_current_user();
    $is_teacher_user = is_teacher($current_user->ID);

    // Determine educational_center_id and teacher_id based on user type
    if ($is_teacher_user) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

    if (empty($educational_center_id)) {
        wp_die('No Educational Center found for this user.');
        return;
    }

    $parent_post_id = isset($_POST['parent_post_id']) ? intval($_POST['parent_post_id']) : 0;
    if ($parent_post_id <= 0) {
        wp_die('Invalid parent post ID: ' . $parent_post_id);
        return;
    }

    $post = get_post($parent_post_id);
    if (!$post || $post->post_type !== 'parent') {
        wp_die('Parent post not found or invalid post type for ID: ' . $parent_post_id);
        return;
    }

    // Verify the post belongs to this educational_center_id
    $stored_edu_center_id = get_post_meta($parent_post_id, 'educational_center_id', true);
    $form_edu_center_id = sanitize_text_field($_POST['educational_center_id'] ?? '');
    if ($stored_edu_center_id !== $educational_center_id || ($form_edu_center_id && $form_edu_center_id !== $stored_edu_center_id)) {
        wp_die('You do not have permission to update this parent.');
        return;
    }

    if (!current_user_can('edit_posts')) {
        wp_die('Insufficient permissions to update parent information.');
        return;
    }

    $fields = array(
        'parent_student_ids' => sanitize_text_field($_POST['parent_student_ids'] ?? ''),
        'parent_name' => sanitize_text_field($_POST['parent_name'] ?? ''),
        'parent_email' => sanitize_email($_POST['parent_email'] ?? ''),
        'parent_phone_number' => sanitize_text_field($_POST['parent_phone_number'] ?? ''),
        'parent_gender' => sanitize_text_field($_POST['parent_gender'] ?? ''),
        'parent_religion' => sanitize_text_field($_POST['parent_religion'] ?? ''),
        'parent_blood_group' => sanitize_text_field($_POST['parent_blood_group'] ?? ''),
        'parent_date_of_birth' => sanitize_text_field($_POST['parent_date_of_birth'] ?? ''),
        'parent_height' => sanitize_text_field($_POST['parent_height'] ?? ''),
        'parent_weight' => sanitize_text_field($_POST['parent_weight'] ?? ''),
        'parent_current_address' => sanitize_textarea_field($_POST['parent_current_address'] ?? ''),
        'parent_permanent_address' => sanitize_textarea_field($_POST['parent_permanent_address'] ?? ''),
    );

    foreach ($fields as $meta_key => $value) {
        $old_value = get_post_meta($parent_post_id, $meta_key, true);
        if ($old_value !== $value) { // Only update if the value has changed
            $updated = update_post_meta($parent_post_id, $meta_key, $value);
            if ($updated === false) {
                global $wpdb;
                $last_error = $wpdb->last_error;
                error_log("Failed to update meta_key: $meta_key for post ID: $parent_post_id");
                error_log("Old value: " . print_r($old_value, true));
                error_log("New value: " . print_r($value, true));
                error_log("DB error: " . ($last_error ? $last_error : 'None reported'));
                error_log("Current user ID: " . get_current_user_id());
                wp_die("Failed to update " . esc_html($meta_key) . ". Check debug.log for details.");
                return;
            }
        }
    }

    // Handle other updates (post title, file upload)
    $parent_id = sanitize_text_field($_POST['parent_id'] ?? '');
    wp_update_post(array('ID' => $parent_post_id, 'post_title' => $parent_id));

    if (!empty($_FILES['parent_profile_photo']['name'])) {
        $file = $_FILES['parent_profile_photo'];
        $upload = wp_handle_upload($file, array('test_form' => false));
        if (isset($upload['file'])) {
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $upload['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            ), $upload['file']);
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
            update_post_meta($parent_post_id, 'parent_profile_photo', $attachment_id);
        }
    }

    // Redirect based on user type
    $redirect_url = sanitize_text_field($_POST['redirect_url'] ?? ($is_teacher_user ? home_url('/teacher-dashboard/?section=parents&action=edit-parents') : home_url('/institute-dashboard/edit-parents')));
    wp_redirect($redirect_url);
    exit;
}

add_action('admin_post_edit_parent_submission', 'handle_parent_update_submission');
add_action('admin_post_nopriv_edit_parent_submission', 'handle_parent_update_submission');

// AJAX handler for parent deletion
add_action('wp_ajax_delete_parent', 'ajax_delete_parent');
function ajax_delete_parent() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_parent_' . $_POST['parent_post_id'])) {
        wp_send_json_error('Security check failed.');
        return;
    }

    // Only proceed if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('Please log in to delete parent information.');
        return;
    }

    $current_user = wp_get_current_user();
    $is_teacher_user = is_teacher($current_user->ID);

    // Determine educational_center_id
    if ($is_teacher_user) { 
        $educational_center_id = educational_center_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
    }

    $form_edu_center_id = sanitize_text_field($_POST['educational_center_id'] ?? '');
    if (empty($educational_center_id) || $educational_center_id !== $form_edu_center_id) {
        wp_send_json_error('Invalid or mismatched Educational Center ID.');
        return;
    }

    $parent_post_id = intval($_POST['parent_post_id'] ?? 0);
    if ($parent_post_id <= 0) {
        wp_send_json_error('Invalid parent post ID.');
        return;
    }

    // Verify the post exists and is of type 'parent'
    $post = get_post($parent_post_id);
    if (!$post || $post->post_type !== 'parent') {
        wp_send_json_error('Parent post not found or invalid post type.');
        return;
    }

    // Verify the post belongs to this educational_center_id
    $stored_edu_center_id = get_post_meta($parent_post_id, 'educational_center_id', true);
    if ($stored_edu_center_id != $educational_center_id) {
        wp_send_json_error('You do not have permission to delete this parent.');
        return;
    }

    // Check if user has permission to delete posts
    // if (!current_user_can('delete_posts')) {
    //     wp_send_json_error('You do not have permission to delete this parent.');
    //     return;
    // }

    // Attempt to delete the post
    if (wp_delete_post($parent_post_id, true)) {
        wp_send_json_success('Parent deleted successfully!');
    } else {
        error_log('Failed to delete parent post ID: ' . $parent_post_id);
        wp_send_json_error('Error deleting parent.');
    }
}