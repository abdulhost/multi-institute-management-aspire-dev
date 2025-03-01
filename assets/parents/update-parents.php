<?php
// parent-list.php
function edit_parents_institute_dashboard_shortcode() {
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ));

    if (empty($educational_center)) {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'update-parent';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');
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
                                $parent_gender = get_post_meta($parent->ID, 'teacher_gender', true);
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
                                        <a href="?action=delete&parent_post_id=' . $parent->ID . '" onclick="return confirm(\'Are you sure you want to delete this parent?\')">Delete</a>
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
                            <select name="teacher_gender" id="edit_parent_gender">
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
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
?>