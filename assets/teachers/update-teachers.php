<?php
// teacher-list.php
function edit_teachers_institute_dashboard_shortcode() {
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ));

    // if (empty($educational_center)) {
    //     return '<p>No Educational Center found for this Admin ID.</p>';
    // }
    if (empty($educational_center)) {
        wp_redirect(home_url('/login'));
        exit();
    
    }
    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    
    }
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    ob_start();
    // [Previous HTML form code remains the same until the form submission handling]
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          echo render_admin_header(wp_get_current_user());
          if (!is_center_subscribed($educational_center_id)) {
              return render_subscription_expired_message($educational_center_id);
          }
        $active_section = 'update-teacher';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');
        ?>
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <div class="form-group search-form">
                <div class="input-group">
                    <span class="input-group-addon">Search</span>
                    <input type="text" id="search_text_teacher" placeholder="Search by Teacher Details" class="form-control" />
                </div>
            </div>

            <div id="result">
                <h3>Teacher List</h3>
                <table id="teachers-table" border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Teacher ID</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $teachers = get_posts(array(
                            'post_type' => 'teacher',
                            'meta_key' => 'educational_center_id',
                            'meta_value' => $educational_center_id,
                            'posts_per_page' => -1,
                        ));

                        if (!empty($teachers)) {
                            foreach ($teachers as $teacher) {
                                $teacher_id = get_post_meta($teacher->ID, 'teacher_id', true);
                                $teacher_email = get_post_meta($teacher->ID, 'teacher_email', true);
                                $teacher_phone_number = get_post_meta($teacher->ID, 'teacher_phone_number', true);
                                $teacher_roll_number = get_post_meta($teacher->ID, 'teacher_roll_number', true);
                                $teacher_admission_date = get_post_meta($teacher->ID, 'teacher_admission_date', true);
                                $teacher_gender = get_post_meta($teacher->ID, 'teacher_gender', true);
                                $teacher_religion = get_post_meta($teacher->ID, 'teacher_religion', true);
                                $teacher_blood_group = get_post_meta($teacher->ID, 'teacher_blood_group', true);
                                $teacher_date_of_birth = get_post_meta($teacher->ID, 'teacher_date_of_birth', true);
                                $teacher_height = get_post_meta($teacher->ID, 'teacher_height', true);
                                $teacher_weight = get_post_meta($teacher->ID, 'teacher_weight', true);
                                $teacher_current_address = get_post_meta($teacher->ID, 'teacher_current_address', true);
                                $teacher_permanent_address = get_post_meta($teacher->ID, 'teacher_permanent_address', true);
                                $profile_picture = get_post_meta($teacher->ID, 'teacher_profile_photo', true);
                                $profile_picture_url = $profile_picture ? wp_get_attachment_url($profile_picture) : '';

                                echo '<tr class="teacher-row">
                                    <td>' . esc_html($teacher_id) . '</td>
                                    <td>' . esc_html($teacher_email) . '</td>
                                    <td>' . esc_html($teacher_phone_number) . '</td>
                                    <td>
                                        <a href="#edit-teachers" class="edit-btn_teacher"
                                           data-teacher-post-id="' . esc_attr($teacher->ID) . '"
                                           data-teacher-id="' . esc_attr($teacher_id) . '"
                                           data-teacher-email="' . esc_attr($teacher_email) . '"
                                           data-teacher-phone-number="' . esc_attr($teacher_phone_number) . '"
                                           data-teacher-roll-number="' . esc_attr($teacher_roll_number) . '"
                                           data-teacher-admission-date="' . esc_attr($teacher_admission_date) . '"
                                           data-teacher-gender="' . esc_attr($teacher_gender) . '"
                                           data-teacher-religion="' . esc_attr($teacher_religion) . '"
                                           data-teacher-blood-group="' . esc_attr($teacher_blood_group) . '"
                                           data-teacher-date-of-birth="' . esc_attr($teacher_date_of_birth) . '"
                                           data-teacher-height="' . esc_attr($teacher_height) . '"
                                           data-teacher-weight="' . esc_attr($teacher_weight) . '"
                                           data-teacher-current-address="' . esc_attr($teacher_current_address) . '"
                                           data-teacher-permanent-address="' . esc_attr($teacher_permanent_address) . '"
                                           data-profile-picture="' . esc_attr($profile_picture_url) . '">Edit</a> |
                                        <a href="?action=delete&teacher_post_id=' . $teacher->ID . '" onclick="return confirm(\'Are you sure you want to delete this teacher?\')">Delete</a>
                                    </td>
                                  </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">No teachers found for this Educational Center.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Previous HTML form content here -->
    <div id="edit-teacher-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Edit Teacher</h3>
            <form id="edit-teacher-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_teacher_submission">
                <input type="hidden" name="teacher_post_id" id="edit_teacher_post_id">
                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-basic-details')">
                        <h4>Basic Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-basic-details">
                        <label for="edit_teacher_id">Teacher ID:</label>
                        <input type="text" name="teacher_id" id="edit_teacher_id" readonly>
                        <label for="edit_teacher_email">Email:</label>
                        <input type="email" name="teacher_email" id="edit_teacher_email" required>
                        <label for="edit_teacher_phone_number">Phone Number:</label>
                        <input type="text" name="teacher_phone_number" id="edit_teacher_phone_number">
                        <label for="edit_teacher_roll_number">Roll Number:</label>
                        <input type="number" name="teacher_roll_number" id="edit_teacher_roll_number" required>
                        <label for="edit_teacher_admission_date">Admission Date:</label>
                        <input type="date" name="teacher_admission_date" id="edit_teacher_admission_date" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-medical-details')">
                        <h4>Medical Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-medical-details" style="display: none;">
                  
                        <?php
                $religion_field = get_field_object('field_67baed90b66de');
                if ($religion_field) : ?>
                    <label for="edit_teacher_gender">Gender:</label>
                    <select name="teacher_gender" id="edit_teacher_gender">
                        <option value="">Select Gender</option>
                        <?php foreach ($religion_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?> 
 <!-- Religion Dropdown -->
 <?php
                $religion_field = get_field_object('field_67baed90bdebb');
                if ($religion_field) : ?>
                    <label for="edit_teacher_religion">Religion:</label>
                    <select name="teacher_religion" id="edit_teacher_religion">
                        <option value="">Select Religion</option>
                        <?php foreach ($religion_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <?php
                $religion_field = get_field_object('field_67baed90c555b');
                if ($religion_field) : ?>
                    <label for="edit_teacher_blood_group">Blood Group:</label>
                    <select name="teacher_blood_group" id="edit_teacher_blood_group">
                        <option value="">Select Blood Group</option>
                        <?php foreach ($religion_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?> <br>

                        <label for="edit_teacher_date_of_birth">Date of Birth:</label>
                        <input type="date" name="teacher_date_of_birth" id="edit_teacher_date_of_birth">
                        <label for="edit_teacher_height">Height (cm):</label>
                        <input type="number" name="teacher_height" id="edit_teacher_height">
                        <label for="edit_teacher_weight">Weight (kg):</label>
                        <input type="number" name="teacher_weight" id="edit_teacher_weight">
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-address-details')">
                        <h4>Address Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-address-details" style="display: none;">
                        <label for="edit_teacher_current_address">Current Address:</label>
                        <textarea name="teacher_current_address" id="edit_teacher_current_address"></textarea>
                        <label for="edit_teacher_permanent_address">Permanent Address:</label>
                        <textarea name="teacher_permanent_address" id="edit_teacher_permanent_address"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('edit-profile-photo')">
                        <h4>Profile Photo</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="edit-profile-photo" style="display: none;">
                        <label for="edit_teacher_profile_photo">Profile Photo:</label>
                        <div id="edit-profile-picture-preview">
                            <img id="edit-profile-picture-img" style="max-width: 200px; display: none;" />
                        </div>
                        <input type="file" name="teacher_profile_photo" id="edit_teacher_profile_photo">
                    </div>
                </div>

                <input type="submit" name="edit_teacher" value="Update Teacher">
            </form>
        </div>
    </div>

    <!-- Previous JavaScript code remains the same -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#search_text_teacher').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#teachers-table tbody tr').each(function() {
                var teacherID = $(this).find('td').eq(0).text().toLowerCase();
                var teacherEmail = $(this).find('td').eq(1).text().toLowerCase();
                var teacherPhone = $(this).find('td').eq(2).text().toLowerCase();
                if (teacherID.includes(searchText) || teacherEmail.includes(searchText) || teacherPhone.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $(document).on('click', '.edit-btn_teacher', function() {
            $('#edit-teacher-form')[0].reset();
            $('#edit-profile-picture-img').hide();
            const file = document.querySelector('#edit_teacher_profile_photo');
            file.value = '';

            var teacherPostId = $(this).data('teacher-post-id');
            var teacherID = $(this).data('teacher-id');
            var teacherEmail = $(this).data('teacher-email');
            var teacherPhoneNumber = $(this).data('teacher-phone-number');
            var teacherRollNumber = $(this).data('teacher-roll-number');
            var teacherAdmissionDate = $(this).data('teacher-admission-date');
            var teacherGender = $(this).data('teacher-gender');
            var teacherReligion = $(this).data('teacher-religion');
            var teacherBloodGroup = $(this).data('teacher-blood-group');
            var teacherDateOfBirth = $(this).data('teacher-date-of-birth');
            var teacherHeight = $(this).data('teacher-height');
            var teacherWeight = $(this).data('teacher-weight');
            var teacherCurrentAddress = $(this).data('teacher-current-address');
            var teacherPermanentAddress = $(this).data('teacher-permanent-address');
            var profilePicture = $(this).data('profile-picture');

            $('#edit_teacher_post_id').val(teacherPostId);
            $('#edit_teacher_id').val(teacherID);
            $('#edit_teacher_email').val(teacherEmail);
            $('#edit_teacher_phone_number').val(teacherPhoneNumber);
            $('#edit_teacher_roll_number').val(teacherRollNumber);
            $('#edit_teacher_admission_date').val(teacherAdmissionDate);
            $('#edit_teacher_gender').val(teacherGender || '');
            $('#edit_teacher_religion').val(teacherReligion || '');
            $('#edit_teacher_blood_group').val(teacherBloodGroup || '');
            $('#edit_teacher_date_of_birth').val(teacherDateOfBirth);
            $('#edit_teacher_height').val(teacherHeight);
            $('#edit_teacher_weight').val(teacherWeight);
            $('#edit_teacher_current_address').val(teacherCurrentAddress);
            $('#edit_teacher_permanent_address').val(teacherPermanentAddress);
            if (profilePicture) {
                $('#edit-profile-picture-img').attr('src', profilePicture).show();
            }

            $('#edit-teacher-modal').css('display', 'block');
        });

        $('.close-modal').click(function() {
            $('#edit-teacher-modal').css('display', 'none');
        });

        $(window).click(function(event) {
            if (event.target.id === 'edit-teacher-modal') {
                $('#edit-teacher-modal').css('display', 'none');
            }
        });

        $('#edit_teacher_profile_photo').change(function(event) {
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

add_shortcode('edit_teachers_institute_dashboard', 'edit_teachers_institute_dashboard_shortcode');

?>