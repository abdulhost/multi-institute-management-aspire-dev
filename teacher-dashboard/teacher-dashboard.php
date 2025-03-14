<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Enqueue Styles and Scripts
add_action('wp_enqueue_scripts', 'aspire_teacher_dashboard_enqueue');
function aspire_teacher_dashboard_enqueue() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
    wp_enqueue_style('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
    wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js', [], null, true);
    wp_add_inline_style('bootstrap', '
        .dashboard-card { transition: transform 0.2s; border-radius: 10px; }
        .dashboard-card:hover { transform: scale(1.05); }
        .profile-avatar { width: 150px; height: 150px; object-fit: cover; border: 3px solid #007bff; border-radius: 50%; }
        .form-section { margin-bottom: 15px; }
        .section-header { cursor: pointer; background: #f1f1f1; padding: 10px; border-radius: 5px; }
        .section-content { padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 5px; }

        .chat-sidebar { overflow-y: auto; border-right: 1px solid #ddd; background: #f8f9fa; }
        .chat-message { padding: 10px; margin: 5px 0; border-radius: 8px; max-width: 70%; position: relative; }
        .chat-message.sent { background: #d4edda; align-self: flex-end; }
        .chat-message.received { background: #e9ecef; align-self: flex-start; }
        .chat-header { background: #007bff; color: white; padding: 10px; }
        .unread { font-weight: bold; background: #cce5ff; }
        .chat-container { display: flex; flex-direction: column;  }
        .chat-messages { flex-grow: 1; overflow-y: auto; padding: 15px; }
   
    
        ');
}
function educational_center_teacher_id() {
    if (!is_user_logged_in()) {
        return false;
    }

    $current_user = wp_get_current_user();
    if (!in_array('teacher', (array) $current_user->roles)) {
        return false;
    }

    return get_user_meta($current_user->ID, 'educational_center_id', true) ?: false;
}
// Main Teacher Dashboard Shortcode
function aspire_teacher_dashboard_shortcode() {
    global $wpdb;

    if (!function_exists('wp_get_current_user')) {
        return '<div class="alert alert-danger">Error: WordPress environment not fully loaded.</div>';
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $username = $current_user->user_login;

    if (!$user_id || !in_array('teacher', $current_user->roles)) {
        return '<div class="alert alert-danger">Access denied. Please log in as a teacher.</div>';
    }

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $teacher_posts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.* 
             FROM $wpdb->posts p 
             LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'teacher' 
             AND p.post_status = 'publish' 
             AND (pm.meta_key = 'teacher_id' AND pm.meta_value = %d) 
             OR p.post_title = %s 
             LIMIT 1",
            $user_id,
            $username
        )
    );

    if (empty($teacher_posts)) {
        return '<div class="alert alert-warning">Teacher profile not found for user ID ' . esc_html($user_id) . ' or username "' . esc_html($username) . '". Please contact administration.</div>';
    }

    $teacher = get_post($teacher_posts[0]->ID);
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : null;
    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #e6ffe6, #ccffcc); min-height: 100vh;">
        <div class="row">
            <?php 
            $active_section = $section;
            $active_action = $action; // Pass action to sidebar
            include plugin_dir_path(__FILE__) . 'teacher-sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'overview':
                        echo render_teacher_overview($user_id, $teacher);
                        break;
                    case 'profile':
                        echo render_teacher_profile($user_id, $teacher);
                        break;
                        case 'classes':
                            if ($action === 'add-class') {
                                echo render_class_add($user_id, $teacher);
                            } elseif ($action === 'edit-class') {
                                $class_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                echo render_class_edit($user_id, $teacher, $class_id);
                            } elseif ($action === 'delete-class') {
                                $class_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                handle_class_delete($user_id, $class_id);
                                echo render_class_management($user_id, $teacher);
                            } else {
                                echo render_class_management($user_id, $teacher);
                            }
                            break;
                            case 'students':
                                if ($action === 'add-student') {
                                    echo render_student_add($user_id, $teacher);
                                } elseif ($action === 'edit-student') {
                                    $student_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                    echo render_student_edit($user_id, $teacher, $student_id);
                                } elseif ($action === 'delete-student') {
                                    $student_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                    handle_student_delete($user_id, $student_id);
                                    echo render_student_management($user_id, $teacher);
                                } else {
                                    echo render_student_management($user_id, $teacher);
                                }
                                break;
                                case 'homework':
                                    if ($action === 'add-homework') {
                                        echo render_homework_add($user_id, $teacher);
                                    } elseif ($action === 'edit-homework') {
                                        $homework_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                        echo render_homework_edit($user_id, $teacher, $homework_id);
                                    } elseif ($action === 'delete-homework') {
                                        $homework_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                        handle_homework_delete($user_id, $homework_id);
                                        echo render_homework_assignments($user_id, $teacher);
                                    } else {
                                        echo render_homework_assignments($user_id, $teacher);
                                    }
                                    break;
                                    case 'communication':
                                        
                                            echo aspire_teacher_prochat_shortcode();
                                        
                                        break;
                                    case 'noticeboard':
                                        
                                            echo aspire_teacher_notice_board_shortcode();
                                        
                                        break;
                                        case 'exams':
                                            if ($action === 'add-exam') {
                                                echo render_teacher_add_exam($user_id, $teacher);
                                            } elseif ($action === 'edit-exam') {
                                                $exam_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                                echo render_teacher_edit_exam($user_id, $teacher, $exam_id);
                                            } elseif ($action === 'delete-exam') {
                                                $exam_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                                echo render_teacher_delete_exam($user_id, $teacher, $exam_id);
                                            } elseif ($action === 'add-exam-subjects') {
                                                echo render_teacher_add_exam_subjects($user_id, $teacher);
                                            } elseif ($action === 'results') {
                                                echo render_teacher_results($user_id, $teacher);
                                            } else {
                                                echo render_teacher_exams($user_id, $teacher);
                                            }
                                            break;
                                        
                                        case 'attendance':
                                            if ($action === 'add-attendance') {
                                                echo render_attendance_add($user_id, $teacher);
                                            } elseif ($action === 'edit-attendance') {
                                                $attendance_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                                echo render_attendance_edit($user_id, $teacher, $attendance_id);
                                            } elseif ($action === 'delete-attendance') {
                                                $attendance_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                                handle_attendance_delete($user_id, $attendance_id);
                                                echo render_attendance_reports($user_id, $teacher);
                                            } else {
                                                echo render_attendance_reports($user_id, $teacher);
                                            }
                                            break;
                                
                    default:
                        echo render_teacher_overview($user_id, $teacher);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_teacher_dashboard', 'aspire_teacher_dashboard_shortcode');

// Feature 1: Dashboard Overview
function render_teacher_overview($user_id, $teacher) {
    $teacher_name = get_post_meta($teacher->ID, 'teacher_name', true);
    $teacher_email = get_post_meta($teacher->ID, 'teacher_email', true);
    $subjects = ['Math', 'Science']; // Placeholder: Fetch from meta or related CPT
    $today = date('Y-m-d');
    $unread_messages = 5; // Placeholder: Query wp_messages
    $classes_today = 3;   // Placeholder: Query timetable

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="bi bi-house-door me-2"></i>Dashboard Overview</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card dashboard-card bg-light border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-envelope-fill text-primary" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">Unread Messages</h5>
                            <p class="card-text display-6"><?php echo esc_html($unread_messages); ?></p>
                            <a href="?section=inbox" class="btn btn-outline-primary btn-sm">View Inbox</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card bg-light border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">Classes Today</h5>
                            <p class="card-text display-6"><?php echo esc_html($classes_today); ?></p>
                            <a href="?section=classes" class="btn btn-outline-success btn-sm">View Schedule</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card bg-light border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-book text-warning" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">Pending Evaluations</h5>
                            <p class="card-text display-6">2</p>
                            <a href="?section=homework" class="btn btn-outline-warning btn-sm">Evaluate Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title m-0"><i class="bi bi-person me-2"></i>Quick Info</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Teacher ID:</strong> <?php echo esc_html(get_post_meta($teacher->ID, 'teacher_id', true)); ?></p>
                            <p><strong>Name:</strong> <?php echo esc_html($teacher_name); ?></p>
                            <p><strong>Email:</strong> <?php echo esc_html($teacher_email); ?></p>
                            <p><strong>Phone:</strong> <?php echo esc_html(get_post_meta($teacher->ID, 'teacher_phone_number', true)); ?></p>
                            <p><strong>Subjects:</strong> <?php echo esc_html(implode(', ', $subjects)); ?></p>
                            <a href="?section=profile" class="btn btn-info btn-sm">Edit Profile</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title m-0"><i class="bi bi-calendar me-2"></i>Today’s Schedule</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="calendar" style="height: 200px;"></div>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var calendarEl = document.getElementById('calendar');
                                var calendar = new FullCalendar.Calendar(calendarEl, {
                                    initialView: 'timeGridDay',
                                    initialDate: '<?php echo $today; ?>',
                                    events: [
                                        { title: 'Math - Class 10A', start: '<?php echo $today; ?>T09:00:00', end: '<?php echo $today; ?>T10:00:00' },
                                        { title: 'Science - Class 9B', start: '<?php echo $today; ?>T11:00:00', end: '<?php echo $today; ?>T12:00:00' }
                                    ],
                                    height: 'auto',
                                    headerToolbar: { left: '', center: 'title', right: '' }
                                });
                                calendar.render();
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Feature 2: Profile Management
function render_teacher_profile($user_id, $teacher) {
    $teacher_data = [
        'teacher_id' => get_post_meta($teacher->ID, 'teacher_id', true),
        'teacher_name' => get_post_meta($teacher->ID, 'teacher_name', true),
        'teacher_email' => get_post_meta($teacher->ID, 'teacher_email', true),
        'teacher_phone_number' => get_post_meta($teacher->ID, 'teacher_phone_number', true),
        'teacher_roll_number' => get_post_meta($teacher->ID, 'teacher_roll_number', true),
        'teacher_admission_date' => get_post_meta($teacher->ID, 'teacher_admission_date', true),
        'teacher_gender' => get_post_meta($teacher->ID, 'teacher_gender', true),
        'teacher_religion' => get_post_meta($teacher->ID, 'teacher_religion', true),
        'teacher_blood_group' => get_post_meta($teacher->ID, 'teacher_blood_group', true),
        'teacher_date_of_birth' => get_post_meta($teacher->ID, 'teacher_date_of_birth', true),
        'teacher_height' => get_post_meta($teacher->ID, 'teacher_height', true),
        'teacher_weight' => get_post_meta($teacher->ID, 'teacher_weight', true),
        'teacher_current_address' => get_post_meta($teacher->ID, 'teacher_current_address', true),
        'teacher_permanent_address' => get_post_meta($teacher->ID, 'teacher_permanent_address', true),
    ];
    $avatar = wp_get_attachment_url(get_post_meta($teacher->ID, 'teacher_profile_photo', true)) ?: 'https://via.placeholder.com/150';

    if (isset($_POST['update_profile']) && wp_verify_nonce($_POST['profile_nonce'], 'update_profile')) {
        $fields = [
            'teacher_name' => sanitize_text_field($_POST['teacher_name']),
            'teacher_email' => sanitize_email($_POST['teacher_email']),
            'teacher_phone_number' => sanitize_text_field($_POST['teacher_phone_number']),
            'teacher_roll_number' => sanitize_text_field($_POST['teacher_roll_number']),
            'teacher_admission_date' => sanitize_text_field($_POST['teacher_admission_date']),
            'teacher_gender' => sanitize_text_field($_POST['teacher_gender']),
            'teacher_religion' => sanitize_text_field($_POST['teacher_religion']),
            'teacher_blood_group' => sanitize_text_field($_POST['teacher_blood_group']),
            'teacher_date_of_birth' => sanitize_text_field($_POST['teacher_date_of_birth']),
            'teacher_height' => sanitize_text_field($_POST['teacher_height']),
            'teacher_weight' => sanitize_text_field($_POST['teacher_weight']),
            'teacher_current_address' => sanitize_textarea_field($_POST['teacher_current_address']),
            'teacher_permanent_address' => sanitize_textarea_field($_POST['teacher_permanent_address']),
        ];

        foreach ($fields as $key => $value) {
            update_post_meta($teacher->ID, $key, $value);
        }

        if (!empty($_FILES['teacher_profile_photo']['name'])) {
            $upload = wp_handle_upload($_FILES['teacher_profile_photo'], ['test_form' => false]);
            if (isset($upload['file'])) {
                $attachment_id = wp_insert_attachment([
                    'guid' => $upload['url'],
                    'post_mime_type' => $upload['type'],
                    'post_title' => basename($upload['file']),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ], $upload['file']);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
                update_post_meta($teacher->ID, 'teacher_profile_photo', $attachment_id);
                $avatar = $upload['url'];
            }
        }

        $teacher_data = array_merge($teacher_data, $fields);
        echo '<div class="alert alert-success">Profile updated successfully!</div>';
    }

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title m-0"><i class="bi bi-person-circle me-2"></i>Profile Management</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <img src="<?php echo esc_url($avatar); ?>" alt="Avatar" class="profile-avatar mb-3">
                    <h5><?php echo esc_html($teacher_data['teacher_name']); ?></h5>
                    <p class="text-muted">Teacher ID: <?php echo esc_html($teacher_data['teacher_id']); ?></p>
                </div>
                <div class="col-md-8">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Basic Details -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('basic-details')">
                                <h4>Basic Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="basic-details">
                                <div class="mb-3">
                                    <label for="teacher_name" class="form-label">Full Name</label>
                                    <input type="text" name="teacher_name" id="teacher_name" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_email" class="form-label">Email</label>
                                    <input type="email" name="teacher_email" id="teacher_email" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_phone_number" class="form-label">Phone Number</label>
                                    <input type="text" name="teacher_phone_number" id="teacher_phone_number" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_phone_number']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_roll_number" class="form-label">Roll Number</label>
                                    <input type="text" name="teacher_roll_number" id="teacher_roll_number" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_roll_number']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_admission_date" class="form-label">Admission Date</label>
                                    <input type="date" name="teacher_admission_date" id="teacher_admission_date" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_admission_date']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Details -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('medical-details')">
                                <h4>Medical Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="medical-details" style="display: none;">
                                <div class="mb-3">
                                    <label for="teacher_gender" class="form-label">Gender</label>
                                    <select name="teacher_gender" id="teacher_gender" class="form-select">
                                        <option value="Male" <?php selected($teacher_data['teacher_gender'], 'Male'); ?>>Male</option>
                                        <option value="Female" <?php selected($teacher_data['teacher_gender'], 'Female'); ?>>Female</option>
                                        <option value="Other" <?php selected($teacher_data['teacher_gender'], 'Other'); ?>>Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_religion" class="form-label">Religion</label>
                                    <input type="text" name="teacher_religion" id="teacher_religion" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_religion']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_blood_group" class="form-label">Blood Group</label>
                                    <select name="teacher_blood_group" id="teacher_blood_group" class="form-select">
                                        <option value="A+" <?php selected($teacher_data['teacher_blood_group'], 'A+'); ?>>A+</option>
                                        <option value="A-" <?php selected($teacher_data['teacher_blood_group'], 'A-'); ?>>A-</option>
                                        <option value="B+" <?php selected($teacher_data['teacher_blood_group'], 'B+'); ?>>B+</option>
                                        <option value="B-" <?php selected($teacher_data['teacher_blood_group'], 'B-'); ?>>B-</option>
                                        <option value="AB+" <?php selected($teacher_data['teacher_blood_group'], 'AB+'); ?>>AB+</option>
                                        <option value="AB-" <?php selected($teacher_data['teacher_blood_group'], 'AB-'); ?>>AB-</option>
                                        <option value="O+" <?php selected($teacher_data['teacher_blood_group'], 'O+'); ?>>O+</option>
                                        <option value="O-" <?php selected($teacher_data['teacher_blood_group'], 'O-'); ?>>O-</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" name="teacher_date_of_birth" id="teacher_date_of_birth" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_date_of_birth']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_height" class="form-label">Height (cm)</label>
                                    <input type="number" name="teacher_height" id="teacher_height" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_height']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_weight" class="form-label">Weight (kg)</label>
                                    <input type="number" name="teacher_weight" id="teacher_weight" class="form-control" value="<?php echo esc_attr($teacher_data['teacher_weight']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Address Details -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('address-details')">
                                <h4>Address Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="address-details" style="display: none;">
                                <div class="mb-3">
                                    <label for="teacher_current_address" class="form-label">Current Address</label>
                                    <textarea name="teacher_current_address" id="teacher_current_address" class="form-control"><?php echo esc_textarea($teacher_data['teacher_current_address']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_permanent_address" class="form-label">Permanent Address</label>
                                    <textarea name="teacher_permanent_address" id="teacher_permanent_address" class="form-control"><?php echo esc_textarea($teacher_data['teacher_permanent_address']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Photo -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('profile-photo')">
                                <h4>Profile Photo</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="profile-photo" style="display: none;">
                                <div class="mb-3">
                                    <label for="teacher_profile_photo" class="form-label">Profile Photo</label>
                                    <input type="file" name="teacher_profile_photo" id="teacher_profile_photo" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">Max 500KB, JPEG/PNG/GIF only</small>
                                </div>
                            </div>
                        </div>

                        <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>
                        <div class="d-flex gap-2">
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            <a href="?section=overview" class="btn btn-secondary">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    function toggleSection(sectionId) {
        var content = document.getElementById(sectionId);
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
    }
    </script>
    <?php
    return ob_get_clean();
}

// Feature 3: Class Management
// Render Class Management (Main List View)
function render_class_management($user_id, $teacher) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $education_center_id = educational_center_teacher_id();

    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    $classes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id)
    );

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="bi bi-book me-2"></i>Class Management</h3>
            <a href="?section=classes&action=add-class" class="btn btn-light btn-sm">Add Class</a>
        </div>
        <div class="card-body">
            <?php if (empty($classes)): ?>
                <p class="text-muted">No classes assigned yet.</p>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Class Name</th>
                            <th>Sections</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo esc_html($class->id); ?></td>
                                <td><?php echo esc_html($class->class_name); ?></td>
                                <td><?php echo esc_html($class->sections); ?></td>
                                <td>
                                    <a href="?section=classes&action=edit-class&id=<?php echo $class->id; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?section=classes&action=delete-class&id=<?php echo $class->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Render Add Class Form
function render_class_add($user_id, $teacher) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $education_center_id = educational_center_teacher_id();

    if (isset($_POST['add_class']) && wp_verify_nonce($_POST['class_nonce'], 'add_class')) {
        $class_name = sanitize_text_field($_POST['class_name']);
        $sections = sanitize_text_field($_POST['sections']);

        $inserted = $wpdb->insert(
            $table_name,
            [
                'class_name' => $class_name,
                'sections' => $sections,
                'education_center_id' => $education_center_id,
            ],
            ['%s', '%s', '%s']
        );

        if ($inserted) {
            wp_redirect('?section=classes');
            exit;
        } else {
            echo '<div class="alert alert-danger">Error adding class: ' . $wpdb->last_error . '</div>';
        }
    }

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title m-0"><i class="bi bi-book me-2"></i>Add New Class</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="class_name" class="form-label">Class Name</label>
                    <input type="text" name="class_name" id="class_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="sections" class="form-label">Sections (e.g., A, B, C)</label>
                    <input type="text" name="sections" id="sections" class="form-control" required>
                </div>
                <?php wp_nonce_field('add_class', 'class_nonce'); ?>
                <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
                <a href="?section=classes" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
// Render Edit Class Form
function render_class_edit($user_id, $teacher, $class_id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $education_center_id = educational_center_teacher_id();

    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    // If no class_id is provided, show the class list
    if (!$class_id) {
        return render_class_management($user_id, $teacher);
    }

    $class = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND education_center_id = %s", $class_id, $education_center_id)
    );

    if (!$class) {
        return '<div class="alert alert-danger">Class not found.</div>';
    }

    if (isset($_POST['update_class']) && wp_verify_nonce($_POST['class_nonce'], 'update_class')) {
        $data = [
            'class_name' => sanitize_text_field($_POST['class_name']),
            'sections' => sanitize_text_field($_POST['sections'])
        ];
        $updated = $wpdb->update($table_name, $data, ['id' => $class_id, 'education_center_id' => $education_center_id]);

        if ($updated !== false) {
            wp_redirect('?section=classes');
            exit;
        } else {
            echo '<div class="alert alert-danger">Error updating class: ' . $wpdb->last_error . '</div>';
        }
    }

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title m-0"><i class="bi bi-book me-2"></i>Edit Class</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="class_name" class="form-label">Class Name</label>
                    <input type="text" name="class_name" id="class_name" class="form-control" value="<?php echo esc_attr($class->class_name); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="sections" class="form-label">Sections (e.g., A, B, C)</label>
                    <input type="text" name="sections" id="sections" class="form-control" value="<?php echo esc_attr($class->sections); ?>" required>
                </div>
                <?php wp_nonce_field('update_class', 'class_nonce'); ?>
                <button type="submit" name="update_class" class="btn btn-primary">Update Class</button>
                <a href="?section=classes" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Handle Class Deletion
function handle_class_delete($user_id, $class_id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $education_center_id = educational_center_teacher_id();

    if (empty($education_center_id)) {
        return; // No output, handled by caller
    }

    if ($class_id) {
        $deleted = $wpdb->delete($table_name, [
            'id' => $class_id,
            'education_center_id' => $education_center_id
        ]);

        if ($deleted === false) {
            error_log("Delete failed for Class ID: $class_id - " . $wpdb->last_error);
        }
    }
}
use Dompdf\Dompdf;
use Dompdf\Options;

// Feature 4: Student Management
function render_student_management($user_id, $teacher) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    $students = get_posts([
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'educational_center_id',
                'value' => $education_center_id,
                'compare' => '='
            ]
        ]
    ]);

    $exam_results_table = $wpdb->prefix . 'exam_results';
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT marks FROM $exam_results_table WHERE education_center_id = %s", $education_center_id)
    );
    $pass = $fail = 0;
    foreach ($results as $result) {
        $result->marks >= 50 ? $pass++ : $fail++;
    }

    // Generate PDF
    if (isset($_GET['action']) && $_GET['action'] === 'download-report') {
        while (ob_get_level()) {
            ob_end_clean();
        }

        // $dompdf_path = dirname(__FILE__) . '/exam/dompdf/autoload.inc.php';
        $dompdf_path = dirname(__FILE__) . '/../assets/exam/dompdf/autoload.inc.php';
        if (file_exists($dompdf_path)) {
            include $dompdf_path;
        } else {
            error_log("Dompdf autoload file not found at: " . $dompdf_path);
        }
        require_once $dompdf_path;

        // if (empty($students)) {
        //     wp_die('No student data found.');
        // }

        // Use fully qualified class names
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', ABSPATH);
        $options->set('tempDir', sys_get_temp_dir());
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { 
                    margin: 10mm; 
                    border: 2px solid #1a2b5f;
                    padding: 4mm;
                }
                body { 
                    font-family: Helvetica, sans-serif; 
                    font-size: 10pt; 
                    color: #333; 
                    line-height: 1.4;
                }
                .container {
                    width: 100%;
                    padding: 15px;
                    border: 1px solid #ccc;
                    background-color: #fff;
                }
                .header {
                    text-align: center;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #1a2b5f;
                    margin-bottom: 15px;
                }
                .header h1 {
                    font-size: 16pt;
                    color: #1a2b5f;
                    margin: 0;
                }
                .header p {
                    font-size: 12pt;
                    color: #666;
                    margin: 5px 0 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 9pt;
                }
                th, td {
                    border: 1px solid #333;
                    padding: 6px;
                    text-align: left;
                }
                th {
                    background-color: #1a2b5f;
                    color: white;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f5f5f5;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Student Report</h1>
                    <p>Education Center: ' . esc_html($education_center_id) . '</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll Number</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($students as $student) {
            $html .= '<tr>
                <td>' . esc_html(get_post_meta($student->ID, 'student_name', true)) . '</td>
                <td>' . esc_html(get_post_meta($student->ID, 'class_name', true)) . '</td>
                <td>' . esc_html(get_post_meta($student->ID, 'section', true)) . '</td>
                <td>' . esc_html(get_post_meta($student->ID, 'roll_number', true)) . '</td>
                <td>' . esc_html(get_post_meta($student->ID, 'student_email', true) ?: 'N/A') . '</td>
                <td>' . esc_html(get_post_meta($student->ID, 'phone_number', true) ?: 'N/A') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></div></body></html>';

        try {
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdf_content = $dompdf->output();

            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="student_report_' . rawurlencode(uniqid()) . '_' . date('Ymd') . '.pdf"');
            header('Content-Length: ' . strlen($pdf_content));
            header('Cache-Control: no-cache');

            echo $pdf_content;
            flush();
            exit;
        } catch (Exception $e) {
            error_log('Dompdf Error: ' . $e->getMessage());
            wp_die('Error generating PDF: ' . esc_html($e->getMessage()));
        }
    }

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="bi bi-people me-2"></i>Student Management</h3>
            <a href="?section=students&action=add-student" class="btn btn-light btn-sm">Add Student</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" id="student_search" class="form-control mb-3" placeholder="Search students...">
                    <table class="table table-striped table-hover" id="student_table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Roll Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo esc_html(get_post_meta($student->ID, 'student_name', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($student->ID, 'class_name', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($student->ID, 'section', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($student->ID, 'roll_number', true)); ?></td>
                                    <td>
                                        <a href="?section=students&action=edit-student&id=<?php echo $student->ID; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="?section=students&action=delete-student&id=<?php echo $student->ID; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="?section=students&action=download-report" class="btn btn-info">Download Student Report (PDF)</a>
                </div>
                <div class="col-md-4">
                    <h5>Performance Overview</h5>
                    <canvas id="performanceChart" width="200" height="200"></canvas>
                    <h5>Attendance Summary</h5>
                    <p>Present: <?php echo rand(80, 100); ?>% (Placeholder)</p>
                    <p>Absent: <?php echo rand(0, 20); ?>% (Placeholder)</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.getElementById('student_search')?.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('#student_table tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });

    const ctx = document.getElementById('performanceChart')?.getContext('2d');
    if (ctx) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Pass', 'Fail'],
                datasets: [{
                    data: [<?php echo $pass; ?>, <?php echo $fail; ?>],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Exam Performance' }
                }
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

function render_student_add($user_id, $teacher) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    $table_class_name = $wpdb->prefix . 'class_sections';
    $classes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_class_name WHERE education_center_id = %s", $education_center_id),
        ARRAY_A
    );

    // Handle Add Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_teacher']) && wp_verify_nonce($_POST['student_nonce'], 'add_student_teacher')) {
        $admission_number = sanitize_text_field($_POST['admission_number'] ?? '');
        $student_name = sanitize_text_field($_POST['student_name'] ?? '');
        $student_email = sanitize_email($_POST['student_email'] ?? '');
        $student_id = sanitize_text_field($_POST['student_id'] ?? 'STU-' . uniqid());
        $class_name = sanitize_text_field($_POST['class_name'] ?? '');
        $section = sanitize_text_field($_POST['section'] ?? '');
        $religion = sanitize_text_field($_POST['religion'] ?? '');
        $blood_group = sanitize_text_field($_POST['blood_group'] ?? '');
        $date_of_birth = sanitize_text_field($_POST['date_of_birth'] ?? '');
        $phone_number = sanitize_text_field($_POST['phone_number'] ?? '');
        $height = sanitize_text_field($_POST['height'] ?? '');
        $weight = sanitize_text_field($_POST['weight'] ?? '');
        $current_address = sanitize_textarea_field($_POST['current_address'] ?? '');
        $permanent_address = sanitize_textarea_field($_POST['permanent_address'] ?? '');
        $roll_number = sanitize_text_field($_POST['roll_number'] ?? '');
        $admission_date = sanitize_text_field($_POST['admission_date'] ?? '');
        $gender = sanitize_text_field($_POST['gender'] ?? '');

        // Handle profile photo upload
        $attachment_id = null;
        if (!empty($_FILES['student_profile_photo']['name'])) {
            $file = $_FILES['student_profile_photo'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 500 * 1024;

            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_mime_types)) {
                return '<div class="alert alert-danger">Invalid file type. Please upload a JPEG, PNG, or GIF image.</div>';
            }
            if ($file['size'] > $max_file_size) {
                return '<div class="alert alert-danger">File size exceeds the 500KB limit.</div>';
            }

            $upload = wp_handle_upload($file, ['test_form' => false]);
            if (isset($upload['file'])) {
                $attachment_id = wp_insert_attachment([
                    'post_mime_type' => $upload['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ], $upload['file']);

                if (!is_wp_error($attachment_id)) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    wp_generate_attachment_metadata($attachment_id, $upload['file']);
                    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
                }
            }
        }

        $student_post_id = wp_insert_post([
            'post_title' => $student_name,
            'post_type' => 'students',
            'post_status' => 'publish',
            'meta_input' => [
                'educational_center_id' => $education_center_id,
                'admission_number' => $admission_number,
                'student_name' => $student_name,
                'student_email' => $student_email,
                'student_id' => $student_id,
                'class_name' => $class_name,
                'section' => $section,
                'religion' => $religion,
                'blood_group' => $blood_group,
                'date_of_birth' => $date_of_birth,
                'phone_number' => $phone_number,
                'height' => $height,
                'weight' => $weight,
                'current_address' => $current_address,
                'permanent_address' => $permanent_address,
                'roll_number' => $roll_number,
                'admission_date' => $admission_date,
                'gender' => $gender,
            ]
        ]);

        if ($student_post_id) {
            if ($attachment_id) {
                update_post_meta($student_post_id, 'student_profile_photo', $attachment_id);
            }
            wp_redirect(home_url('/teacher-dashboard/?section=students'));
            exit;
        } else {
            return '<div class="alert alert-danger">Error adding student.</div>';
        }
    }

    ob_start();
    ?>
    <!-- <div class="attendance-main-wrapper" style="display: flex;"> -->
    
        <div id="add-student-form" style="display: block; width: 100%;">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-person-plus me-2"></i>Add New Student</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Basic Details Section -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('basic-details')">
                                <h4>Basic Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="basic-details">
                                <label for="class_name">Class:</label>
                                <select name="class_name" id="class_nameadd" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $row) : ?>
                                        <option value="<?php echo esc_attr($row['class_name']); ?>"><?php echo esc_html($row['class_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="section">Section:</label>
                                <select name="section" id="sectionadd" required disabled>
                                    <option value="">Select Class First</option>
                                </select>
                                <br>
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
                                <label for="student_id">Student ID (Auto-generated)</label>
                                <input type="text" name="student_id" value="<?php echo 'STU-' . uniqid(); ?>" readonly>
                                <br>
                                <label for="roll_number">Roll Number:</label>
                                <input type="number" name="roll_number" id="roll_number" required>
                                <br>
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
                                <label for="gender">Gender:</label>
                                <select name="gender" id="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <br>
                                <label for="religion">Religion:</label>
                                <select name="religion" id="religion">
                                    <option value="">Select Religion</option>
                                    <option value="christianity">Christianity</option>
                                    <option value="islam">Islam</option>
                                    <option value="hinduism">Hinduism</option>
                                    <option value="buddhism">Buddhism</option>
                                    <option value="other">Other</option>
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
                                <input type="number" name="height" id="height" required>
                                <br>
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
                                <label for="current_address">Current Address:</label>
                                <textarea name="current_address" id="current_address" rows="4" required></textarea>
                                <br>
                                <label for="permanent_address">Permanent Address:</label>
                                <textarea name="permanent_address" id="permanent_address" rows="4" required></textarea>
                            </div>
                        </div>

                        <!-- Profile Photo Section -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('profile-photo')">
                                <h4>Profile Photo</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="profile-photo" style="display: none;">
                                <label for="student_profile_photo">Student Profile Photo</label>
                                <input type="file" name="student_profile_photo" id="student_profile_photo">
                            </div>
                        </div>

                        <?php wp_nonce_field('add_student_teacher', 'student_nonce'); ?>
                        <button type="submit" name="add_student_teacher" class="btn btn-primary">Add Student</button>
                        <a href="?section=students" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    <!-- </div> -->

    <script>
    jQuery(document).ready(function($) {
        var sectionsData = {};
        <?php
        foreach ($classes as $row) {
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
                const maxSize = 500 * 1024;

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

function handle_student_delete($user_id, $student_id = null) {
    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return; // No output, handled by caller
    }

    if ($student_id) {
        $student = get_post($student_id);
        if ($student && $student->post_type === 'students' && get_post_meta($student_id, 'educational_center_id', true) === $education_center_id) {
            wp_delete_post($student_id, true);
        }
    }
}
function render_student_edit($user_id, $teacher, $student_id = null) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    if (!$student_id) {
        return render_student_management($user_id, $teacher);
    }

    $student = get_post($student_id);
    if (!$student || $student->post_type !== 'students' || get_post_meta($student_id, 'educational_center_id', true) !== $education_center_id) {
        return '<div class="alert alert-danger">Student not found or permission denied.</div>';
    }

    $table_class_name = $wpdb->prefix . 'class_sections';
    $classes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_class_name WHERE education_center_id = %s", $education_center_id),
        ARRAY_A
    );

    // Handle Edit Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student_teacher']) && wp_verify_nonce($_POST['student_nonce'], 'edit_student_teacher')) {
        $admission_number = sanitize_text_field($_POST['admission_number'] ?? '');
        $student_name = sanitize_text_field($_POST['student_name'] ?? '');
        $student_email = sanitize_email($_POST['student_email'] ?? '');
        $class_name = sanitize_text_field($_POST['class_name'] ?? '');
        $section = sanitize_text_field($_POST['section'] ?? '');
        $religion = sanitize_text_field($_POST['religion'] ?? '');
        $blood_group = sanitize_text_field($_POST['blood_group'] ?? '');
        $date_of_birth = sanitize_text_field($_POST['date_of_birth'] ?? '');
        $phone_number = sanitize_text_field($_POST['phone_number'] ?? '');
        $height = sanitize_text_field($_POST['height'] ?? '');
        $weight = sanitize_text_field($_POST['weight'] ?? '');
        $current_address = sanitize_textarea_field($_POST['current_address'] ?? '');
        $permanent_address = sanitize_textarea_field($_POST['permanent_address'] ?? '');
        $roll_number = sanitize_text_field($_POST['roll_number'] ?? '');
        $admission_date = sanitize_text_field($_POST['admission_date'] ?? '');
        $gender = sanitize_text_field($_POST['gender'] ?? '');

        // Handle profile photo upload
        $attachment_id = null;
        if (!empty($_FILES['student_profile_photo']['name'])) {
            $file = $_FILES['student_profile_photo'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 500 * 1024;

            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_mime_types)) {
                return '<div class="alert alert-danger">Invalid file type. Please upload a JPEG, PNG, or GIF image.</div>';
            }
            if ($file['size'] > $max_file_size) {
                return '<div class="alert alert-danger">File size exceeds the 500KB limit.</div>';
            }

            $upload = wp_handle_upload($file, ['test_form' => false]);
            if (isset($upload['file'])) {
                $attachment_id = wp_insert_attachment([
                    'post_mime_type' => $upload['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ], $upload['file']);

                if (!is_wp_error($attachment_id)) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    wp_generate_attachment_metadata($attachment_id, $upload['file']);
                    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
                }
            }
        }

        wp_update_post([
            'ID' => $student_id,
            'post_title' => $student_name,
        ]);

        update_post_meta($student_id, 'admission_number', $admission_number);
        update_post_meta($student_id, 'student_name', $student_name);
        update_post_meta($student_id, 'student_email', $student_email);
        update_post_meta($student_id, 'class_name', $class_name);
        update_post_meta($student_id, 'section', $section);
        update_post_meta($student_id, 'religion', $religion);
        update_post_meta($student_id, 'blood_group', $blood_group);
        update_post_meta($student_id, 'date_of_birth', $date_of_birth);
        update_post_meta($student_id, 'phone_number', $phone_number);
        update_post_meta($student_id, 'height', $height);
        update_post_meta($student_id, 'weight', $weight);
        update_post_meta($student_id, 'current_address', $current_address);
        update_post_meta($student_id, 'permanent_address', $permanent_address);
        update_post_meta($student_id, 'roll_number', $roll_number);
        update_post_meta($student_id, 'admission_date', $admission_date);
        update_post_meta($student_id, 'gender', $gender);
        if ($attachment_id) {
            update_post_meta($student_id, 'student_profile_photo', $attachment_id);
        }

        wp_redirect(home_url('/teacher-dashboard/?section=students'));
        exit;
    }

    ob_start();
    ?>
    <!-- <div class="attendance-main-wrapper" style="display: flex;"> -->
        
        <div id="edit-student-form" style="display: block; width: 100%;">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-pencil me-2"></i>Edit Student</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="student_id" value="<?php echo $student->ID; ?>">

                        <!-- Basic Details Section -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('edit-basic-details')">
                                <h4>Basic Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="edit-basic-details">
                                <label for="edit_class_name">Class:</label>
                                <select name="class_name" id="edit_class_name" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $row) : ?>
                                        <option value="<?php echo esc_attr($row['class_name']); ?>" <?php selected(get_post_meta($student->ID, 'class_name', true), $row['class_name']); ?>><?php echo esc_html($row['class_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="edit_section">Section:</label>
                                <select name="section" id="edit_section" required>
                                    <?php
                                    $sections = explode(',', $wpdb->get_var($wpdb->prepare("SELECT sections FROM $table_class_name WHERE class_name = %s AND education_center_id = %s", get_post_meta($student->ID, 'class_name', true), $education_center_id)));
                                    foreach ($sections as $section_val) {
                                        echo '<option value="' . esc_attr($section_val) . '" ' . selected(get_post_meta($student->ID, 'section', true), $section_val, false) . '>' . esc_html($section_val) . '</option>';
                                    }
                                    ?>
                                </select>
                                <br>
                                <label for="edit_admission_number">Admission Number</label>
                                <input type="text" name="admission_number" id="edit_admission_number" value="<?php echo esc_attr(get_post_meta($student->ID, 'admission_number', true)); ?>" required>
                                <br>
                                <label for="edit_student_name">Student Full Name</label>
                                <input type="text" name="student_name" id="edit_student_name" value="<?php echo esc_attr(get_post_meta($student->ID, 'student_name', true)); ?>" required>
                                <br>
                                <label for="edit_student_email">Student Email</label>
                                <input type="email" name="student_email" id="edit_student_email" value="<?php echo esc_attr(get_post_meta($student->ID, 'student_email', true)); ?>">
                                <br>
                                <label for="edit_phone_number">Phone Number</label>
                                <input type="text" name="phone_number" id="edit_phone_number" value="<?php echo esc_attr(get_post_meta($student->ID, 'phone_number', true)); ?>">
                                <br>
                                <label for="edit_roll_number">Roll Number:</label>
                                <input type="number" name="roll_number" id="edit_roll_number" value="<?php echo esc_attr(get_post_meta($student->ID, 'roll_number', true)); ?>" required>
                                <br>
                                <label for="edit_admission_date">Admission Date:</label>
                                <input type="date" name="admission_date" id="edit_admission_date" value="<?php echo esc_attr(get_post_meta($student->ID, 'admission_date', true)); ?>" required>
                            </div>
                        </div>

                        <!-- Medical Details Section -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('edit-medical-details')">
                                <h4>Medical Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="edit-medical-details" style="display: none;">
                                <label for="edit_gender">Gender:</label>
                                <select name="gender" id="edit_gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php selected(get_post_meta($student->ID, 'gender', true), 'male'); ?>>Male</option>
                                    <option value="female" <?php selected(get_post_meta($student->ID, 'gender', true), 'female'); ?>>Female</option>
                                    <option value="other" <?php selected(get_post_meta($student->ID, 'gender', true), 'other'); ?>>Other</option>
                                </select>
                                <br>
                                <label for="edit_religion">Religion:</label>
                                <select name="religion" id="edit_religion">
                                    <option value="">Select Religion</option>
                                    <option value="christianity" <?php selected(get_post_meta($student->ID, 'religion', true), 'christianity'); ?>>Christianity</option>
                                    <option value="islam" <?php selected(get_post_meta($student->ID, 'religion', true), 'islam'); ?>>Islam</option>
                                    <option value="hinduism" <?php selected(get_post_meta($student->ID, 'religion', true), 'hinduism'); ?>>Hinduism</option>
                                    <option value="buddhism" <?php selected(get_post_meta($student->ID, 'religion', true), 'buddhism'); ?>>Buddhism</option>
                                    <option value="other" <?php selected(get_post_meta($student->ID, 'religion', true), 'other'); ?>>Other</option>
                                </select>
                                <br>
                                <label for="edit_blood_group">Blood Group:</label>
                                <select name="blood_group" id="edit_blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'A+'); ?>>A+</option>
                                    <option value="A-" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'A-'); ?>>A-</option>
                                    <option value="B+" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'B+'); ?>>B+</option>
                                    <option value="B-" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'B-'); ?>>B-</option>
                                    <option value="AB+" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'AB+'); ?>>AB+</option>
                                    <option value="AB-" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'AB-'); ?>>AB-</option>
                                    <option value="O+" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'O+'); ?>>O+</option>
                                    <option value="O-" <?php selected(get_post_meta($student->ID, 'blood_group', true), 'O-'); ?>>O-</option>
                                </select>
                                <br>
                                <label for="edit_date_of_birth">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="edit_date_of_birth" value="<?php echo esc_attr(get_post_meta($student->ID, 'date_of_birth', true)); ?>">
                                <br>
                                <label for="edit_height">Height (in cm):</label>
                                <input type="number" name="height" id="edit_height" value="<?php echo esc_attr(get_post_meta($student->ID, 'height', true)); ?>" required>
                                <br>
                                <label for="edit_weight">Weight (in kg):</label>
                                <input type="number" name="weight" id="edit_weight" value="<?php echo esc_attr(get_post_meta($student->ID, 'weight', true)); ?>" required>
                            </div>
                        </div>

                        <!-- Address Details Section -->
                        <div class="form-section">
                            <div class="section-header" onclick="toggleSection('edit-address-details')">
                                <h4>Address Details</h4>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="section-content" id="edit-address-details" style="display: none;">
                                <label for="edit_current_address">Current Address:</label>
                                <textarea name="current_address" id="edit_current_address" rows="4" required><?php echo esc_textarea(get_post_meta($student->ID, 'current_address', true)); ?></textarea>
                                <br>
                                <label for="edit_permanent_address">Permanent Address:</label>
                                <textarea name="permanent_address" id="edit_permanent_address" rows="4" required><?php echo esc_textarea(get_post_meta($student->ID, 'permanent_address', true)); ?></textarea>
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
                                <?php if ($photo_id = get_post_meta($student->ID, 'student_profile_photo', true)): ?>
                                    <div id="edit-profile-picture-preview">
                                        <img id="edit-profile-picture-img" src="<?php echo wp_get_attachment_url($photo_id); ?>" alt="Profile Picture" style="max-width: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php wp_nonce_field('edit_student_teacher', 'student_nonce'); ?>
                        <button type="submit" name="edit_student_teacher" class="btn btn-primary">Save Changes</button>
                        <a href="?section=students" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    <!-- </div> -->

    <script>
    jQuery(document).ready(function($) {
        var sectionsData = {};
        <?php
        foreach ($classes as $row) {
            echo 'sectionsData["' . esc_attr($row['class_name']) . '"] = ' . json_encode(explode(',', $row['sections'])) . ';';
        }
        ?>

        $('#edit_class_name').change(function() {
            var selectedClass = $(this).val();
            var sectionSelect = $('#edit_section');

            if (selectedClass && sectionsData[selectedClass]) {
                sectionSelect.html('<option value="">Select Section</option>');
                sectionsData[selectedClass].forEach(function(section) {
                    sectionSelect.append('<option value="' + section + '">' + section + '</option>');
                });
                sectionSelect.prop('disabled', false);
            } else {
                sectionSelect.html('<option value="">Select Class First</option>').prop('disabled', true);
            }
            sectionSelect.val('<?php echo esc_js(get_post_meta($student->ID, 'section', true)); ?>');
        });

        $('#edit_class_name').trigger('change');
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const fileInput = document.querySelector('input[name="student_profile_photo"]');

        form.addEventListener('submit', function(event) {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 500 * 1024;

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

// Homework Management Functions
// Homework Management Functions
function render_homework_assignments($user_id, $teacher) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    $homework_table = $wpdb->prefix . 'homework';
    $homeworks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $homework_table WHERE teacher_id = %d AND education_center_id = %s",
            $user_id,
            $education_center_id
        )
    );

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'homework';
        $active_action = '';
        include plugin_dir_path(__FILE__) . 'teacher-sidebar.php';
        ?>
        <div style="display: block; width: 100%;">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title m-0"><i class="bi bi-journal-text me-2"></i>Homework/Assignments</h3>
                    <a href="?section=homework&action=add-homework" class="btn btn-light btn-sm">Assign Homework</a>
                </div>
                <div class="card-body">
                    <?php if (empty($homeworks)): ?>
                        <p class="text-muted">No homework assigned yet.</p>
                    <?php else: ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($homeworks as $homework): ?>
                                    <tr>
                                        <td><?php echo esc_html($homework->title); ?></td>
                                        <td><?php echo esc_html($homework->class_name); ?></td>
                                        <td><?php echo esc_html($homework->section); ?></td>
                                        <td><?php echo esc_html($homework->due_date); ?></td>
                                        <td><?php echo esc_html($homework->status); ?></td>
                                        <td>
                                            <a href="?section=homework&action=edit-homework&id=<?php echo $homework->homework_id; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?section=homework&action=delete-homework&id=<?php echo $homework->homework_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                            <button class="btn btn-sm btn-success mark-complete" data-id="<?php echo $homework->homework_id; ?>" <?php echo $homework->status === 'completed' ? 'disabled' : ''; ?>>Mark Complete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.querySelectorAll('.mark-complete').forEach(button => {
        button.addEventListener('click', function() {
            const homeworkId = this.dataset.id;
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=mark_homework_complete&homework_id=${homeworkId}&nonce=<?php echo wp_create_nonce('mark_homework'); ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').querySelector('td:nth-child(5)').textContent = 'completed';
                    this.disabled = true;
                } else {
                    alert('Error marking homework as complete.');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

function render_homework_add($user_id, $teacher) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    $class_sections_table = $wpdb->prefix . 'class_sections';
    $classes = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $class_sections_table WHERE education_center_id = %s",
            $education_center_id
        )
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_homework']) && wp_verify_nonce($_POST['homework_nonce'], 'add_homework')) {
        $title = sanitize_text_field($_POST['homework_title'] ?? '');
        $description = sanitize_textarea_field($_POST['homework_description'] ?? '');
        $due_date = sanitize_text_field($_POST['due_date'] ?? '');
        $class_name = sanitize_text_field($_POST['class_name'] ?? '');
        $section = sanitize_text_field($_POST['section'] ?? '');

        if (empty($title) || empty($description) || empty($due_date) || empty($class_name) || empty($section)) {
            return '<div class="alert alert-danger">All fields are required.</div>';
        }

        $homework_table = $wpdb->prefix . 'homework';
        $inserted = $wpdb->insert(
            $homework_table,
            [
                'education_center_id' => $education_center_id,
                'teacher_id' => $user_id,
                'class_name' => $class_name,
                'section' => $section,
                'title' => $title,
                'description' => $description,
                'due_date' => $due_date,
                'status' => 'active'
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($inserted) {
            wp_redirect(home_url('/teacher-dashboard/?section=homework'));
            exit;
        } else {
            return '<div class="alert alert-danger">Error assigning homework: ' . esc_html($wpdb->last_error) . '</div>';
        }
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'homework';
        $active_action = 'add-homework';
        include plugin_dir_path(__FILE__) . 'teacher-sidebar.php';
        ?>
        <div style="display: block; width: 100%;">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-journal-text me-2"></i>Assign New Homework</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="homework_title" class="form-label">Title</label>
                            <input type="text" name="homework_title" id="homework_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="homework_description" class="form-label">Description</label>
                            <textarea name="homework_description" id="homework_description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class</label>
                            <select name="class_name" id="class_name" class="form-select" required>
                                <option value="">Select a Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo esc_attr($class->class_name); ?>"><?php echo esc_html($class->class_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select name="section" id="section" class="form-select" required disabled>
                                <option value="">Select Class First</option>
                            </select>
                        </div>
                        <?php wp_nonce_field('add_homework', 'homework_nonce'); ?>
                        <div class="d-flex gap-2">
                            <button type="submit" name="add_homework" class="btn btn-primary">Assign Homework</button>
                            <a href="?section=homework" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var sectionsData = {};
        <?php
        foreach ($classes as $class) {
            echo 'sectionsData["' . esc_attr($class->class_name) . '"] = ' . json_encode(explode(',', $class->sections)) . ';';
        }
        ?>

        $('#class_name').change(function() {
            var selectedClass = $(this).val();
            var sectionSelect = $('#section');

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

        $('#class_name').trigger('change');
    });
    </script>
    <?php
    return ob_get_clean();
}

function render_homework_edit($user_id, $teacher, $homework_id = null) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">Error: Could not fetch Education Center ID.</div>';
    }

    if (!$homework_id) {
        return render_homework_assignments($user_id, $teacher);
    }

    $homework_table = $wpdb->prefix . 'homework';
    $homework = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $homework_table WHERE homework_id = %d AND teacher_id = %d AND education_center_id = %s",
            $homework_id,
            $user_id,
            $education_center_id
        )
    );

    if (!$homework) {
        return '<div class="alert alert-danger">Homework not found or permission denied.</div>';
    }

    $class_sections_table = $wpdb->prefix . 'class_sections';
    $classes = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $class_sections_table WHERE education_center_id = %s",
            $education_center_id
        )
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_homework']) && wp_verify_nonce($_POST['homework_nonce'], 'edit_homework')) {
        $title = sanitize_text_field($_POST['homework_title'] ?? '');
        $description = sanitize_textarea_field($_POST['homework_description'] ?? '');
        $due_date = sanitize_text_field($_POST['due_date'] ?? '');
        $class_name = sanitize_text_field($_POST['class_name'] ?? '');
        $section = sanitize_text_field($_POST['section'] ?? '');

        if (empty($title) || empty($description) || empty($due_date) || empty($class_name) || empty($section)) {
            return '<div class="alert alert-danger">All fields are required.</div>';
        }

        $updated = $wpdb->update(
            $homework_table,
            [
                'title' => $title,
                'description' => $description,
                'due_date' => $due_date,
                'class_name' => $class_name,
                'section' => $section
            ],
            ['homework_id' => $homework_id, 'teacher_id' => $user_id, 'education_center_id' => $education_center_id],
            ['%s', '%s', '%s', '%s', '%s'],
            ['%d', '%d', '%s']
        );

        if ($updated !== false) {
            wp_redirect(home_url('/teacher-dashboard/?section=homework'));
            exit;
        } else {
            return '<div class="alert alert-danger">Error updating homework: ' . esc_html($wpdb->last_error) . '</div>';
        }
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'homework';
        $active_action = 'edit-homework';
        include plugin_dir_path(__FILE__) . 'teacher-sidebar.php';
        ?>
        <div style="display: block; width: 100%;">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-journal-text me-2"></i>Edit Homework</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="homework_title" class="form-label">Title</label>
                            <input type="text" name="homework_title" id="homework_title" class="form-control" value="<?php echo esc_attr($homework->title); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="homework_description" class="form-label">Description</label>
                            <textarea name="homework_description" id="homework_description" class="form-control" rows="3" required><?php echo esc_textarea($homework->description); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" value="<?php echo esc_attr($homework->due_date); ?>" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class</label>
                            <select name="class_name" id="class_name" class="form-select" required>
                                <option value="">Select a Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo esc_attr($class->class_name); ?>" <?php selected($homework->class_name, $class->class_name); ?>><?php echo esc_html($class->class_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select name="section" id="section" class="form-select" required>
                                <?php
                                $sections = explode(',', $wpdb->get_var($wpdb->prepare("SELECT sections FROM $class_sections_table WHERE class_name = %s AND education_center_id = %s", $homework->class_name, $education_center_id)));
                                foreach ($sections as $section_val) {
                                    echo '<option value="' . esc_attr($section_val) . '" ' . selected($homework->section, $section_val, false) . '>' . esc_html($section_val) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <?php wp_nonce_field('edit_homework', 'homework_nonce'); ?>
                        <div class="d-flex gap-2">
                            <button type="submit" name="edit_homework" class="btn btn-primary">Update Homework</button>
                            <a href="?section=homework" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var sectionsData = {};
        <?php
        foreach ($classes as $class) {
            echo 'sectionsData["' . esc_attr($class->class_name) . '"] = ' . json_encode(explode(',', $class->sections)) . ';';
        }
        ?>

        $('#class_name').change(function() {
            var selectedClass = $(this).val();
            var sectionSelect = $('#section');

            if (selectedClass && sectionsData[selectedClass]) {
                sectionSelect.html('<option value="">Select Section</option>');
                sectionsData[selectedClass].forEach(function(section) {
                    sectionSelect.append('<option value="' + section + '">' + section + '</option>');
                });
                sectionSelect.val('<?php echo esc_js($homework->section); ?>');
            } else {
                sectionSelect.html('<option value="">Select Class First</option>');
            }
        });

        $('#class_name').trigger('change');
    });
    </script>
    <?php
    return ob_get_clean();
}

function handle_homework_delete($user_id, $homework_id) {
    global $wpdb;

    $education_center_id = educational_center_teacher_id();
    if (empty($education_center_id)) {
        return;
    }

    $homework_table = $wpdb->prefix . 'homework';
    $wpdb->delete(
        $homework_table,
        ['homework_id' => $homework_id, 'teacher_id' => $user_id, 'education_center_id' => $education_center_id],
        ['%d', '%d', '%s']
    );
}

add_action('wp_ajax_mark_homework_complete', 'mark_homework_complete_callback');
function mark_homework_complete_callback() {
    global $wpdb;

    check_ajax_referer('mark_homework', 'nonce');
    $homework_id = intval($_POST['homework_id'] ?? 0);
    $user_id = get_current_user_id();
    $education_center_id = educational_center_teacher_id();

    if ($homework_id && $education_center_id) {
        $homework_table = $wpdb->prefix . 'homework';
        $updated = $wpdb->update(
            $homework_table,
            ['status' => 'completed'],
            ['homework_id' => $homework_id, 'teacher_id' => $user_id, 'education_center_id' => $education_center_id],
            ['%s'],
            ['%d', '%d', '%s']
        );
        if ($updated !== false) {
            wp_send_json_success();
        }
    }
    wp_send_json_error();
}

//
// Activation: Create messages table
// TEACHER FUNCTIONS
// Helper: Check if User is a Teacher
function aspire_is_teacher($user) {
    return in_array('teacher', $user->roles);
}

// Helper: Check if User is an Institute Admin (for dual roles)
function aspire_is_institute_admin($username, $education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'institute_admins';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE institute_admin_id = %s AND education_center_id = %s",
        $username,
        $education_center_id
    ));
    return $count > 0;
}

// Helper: Get Teacher Username (Refined)
// function aspire_teacher_get_username($post_id) {
//     $user_id = get_post_meta($post_id, 'teacher_id', true);
//     if ($user_id) {
//         // Check if teacher_id is a username (e.g., TEA-67d0728cd4ae5)
//         $user = get_user_by('login', $user_id);
//         if ($user) {
//             return $user->user_login;
//         }
//         // Otherwise, treat it as a user ID
//         $user = get_userdata(intval($user_id));
//         if ($user) {
//             return $user->user_login;
//         }
//         error_log("No user found for post_id=$post_id, user_id=$user_id");
//     } else {
//         error_log("No teacher_id meta for post_id=$post_id");
//     }
//     return 'contact_' . $post_id; // Fallback for any contact type
// }

// Helper: Get Institute Admins
function aspire_teacher_get_admins($education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'institute_admins';
    $admins = $wpdb->get_results($wpdb->prepare(
        "SELECT institute_admin_id AS id, name FROM $table WHERE education_center_id = %s",
        $education_center_id
    ), ARRAY_A);
    return $admins ?: [];
}

// Teacher: Send Message
function aspire_teacher_send_message($sender_id, $receiver_id, $message, $education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';

    error_log("Sending message: sender_id=$sender_id, receiver_id=$receiver_id, message=$message, education_center_id=$education_center_id");

    $result = $wpdb->insert(
        $table,
        [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'education_center_id' => $education_center_id,
            'status' => 'sent',
        ],
        ['%s', '%s', '%s', '%s', '%s']
    );

    return $result !== false;
}

// Teacher: Get Messages
// function aspire_teacher_get_messages($username, $conversation_with = '') {
//     global $wpdb;
//     $table = $wpdb->prefix . 'aspire_messages';
//     $edu_center_id = educational_center_teacher_id();

//     $query = "SELECT * FROM $table WHERE education_center_id = %s";
//     $query_args = [$edu_center_id];

//     if ($conversation_with) {
//         $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s))";
//         $query_args[] = $username;
//         $query_args[] = $conversation_with;
//         $query_args[] = $conversation_with;
//         $query_args[] = $username;
//     } else {
//         $query .= " AND (receiver_id = %s OR receiver_id IN ('all', 'teachers'))";
//         $query_args[] = $username;
//     }

//     $query .= " ORDER BY timestamp DESC LIMIT 50";
//     $results = $wpdb->get_results($wpdb->prepare($query, $query_args));
//     error_log("Teacher messages for $username with $conversation_with: " . print_r($results, true));
//     return $results;
// }

// Teacher: Mark Messages as Read
function aspire_teacher_mark_messages_read($username, $conversation_with) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';

    $wpdb->update(
        $table,
        ['status' => 'read'],
        [
            'receiver_id' => $username,
            'sender_id' => $conversation_with,
            'status' => 'sent'
        ],
        ['%s'],
        ['%s', '%s', '%s']
    );
}

// Teacher: Get Unread Count
function aspire_teacher_get_unread_count($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = educational_center_teacher_id();
    $user = wp_get_current_user();
    $is_teacher = aspire_is_teacher($user);
    $is_admin = aspire_is_institute_admin($username, $edu_center_id);

    $group_receivers = ['all'];
    if ($is_teacher) $group_receivers[] = 'teachers';
    if ($is_admin) $group_receivers[] = 'institute_admins';

    $placeholders = implode(',', array_fill(0, count($group_receivers), '%s'));
    $query_args = array_merge([$edu_center_id, $username], $group_receivers);

    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table 
         WHERE education_center_id = %s 
         AND (receiver_id = %s OR receiver_id IN ($placeholders)) 
         AND status = 'sent'",
        $query_args
    );

    return $wpdb->get_var($query);
}

// Teacher: AJAX Send Message
add_action('wp_ajax_aspire_teacher_send_message', 'aspire_teacher_ajax_send_message');
function aspire_teacher_ajax_send_message() {
    check_ajax_referer('aspire_teacher_nonce', 'nonce');
    $edu_center_id = educational_center_teacher_id();
    $user = wp_get_current_user();
    $sender_id = $user->user_login;
    $message = sanitize_text_field($_POST['message']);
    $target_type = sanitize_text_field($_POST['target_type']);
    $target_value = sanitize_text_field($_POST['target_value']);

    if (!$edu_center_id || !$sender_id || !$message || !$target_type || !$target_value) {
        wp_send_json_error('Missing required fields.');
        return;
    }

    $receiver_id = $target_value; // Directly from dropdown, already a username
    error_log("AJAX send: sender_id=$sender_id, receiver_id=$receiver_id");

    $success = aspire_teacher_send_message($sender_id, $receiver_id, $message, $edu_center_id);
    wp_send_json($success ? ['success' => 'Message sent!'] : ['error' => 'Failed to send message.']);
}

// Teacher: AJAX Fetch Messages
add_action('wp_ajax_aspire_teacher_fetch_messages', 'aspire_teacher_ajax_fetch_messages');
// function aspire_teacher_ajax_fetch_messages() {
//     check_ajax_referer('aspire_teacher_nonce', 'nonce');
//     $user = wp_get_current_user();
//     $username = $user->user_login;
//     $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');

//     $messages = aspire_teacher_get_messages($username, $conversation_with);
//     if ($conversation_with && !in_array($conversation_with, ['all', 'teachers', 'institute_admins'])) {
//         aspire_teacher_mark_messages_read($username, $conversation_with);
//     }

//     $output = '';
//     foreach ($messages as $msg) {
//         $sender = get_user_by('login', $msg->sender_id);
//         $sender_name = $sender ? $sender->display_name : 'Unknown';
//         $output .= '<div class="chat-message ' . ($msg->sender_id === $username ? 'sent text-end' : 'received') . ' ' . ($msg->status == 'sent' ? 'unread' : '') . '">';
//         $output .= '<small>' . esc_html($sender_name) . ' - ' . esc_html($msg->timestamp) . '</small>';
//         $output .= '<p>' . esc_html($msg->message) . '</p>';
//         $output .= '</div>';
//     }

//     wp_send_json_success(['html' => $output, 'unread' => aspire_teacher_get_unread_count($username)]);
// }

// Teacher: AJAX Fetch Conversations
add_action('wp_ajax_aspire_teacher_fetch_conversations', 'aspire_teacher_ajax_fetch_conversations');
function aspire_teacher_ajax_fetch_conversations() {
    check_ajax_referer('aspire_teacher_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = educational_center_teacher_id();
    $active_conversations = aspire_teacher_get_active_conversations($username);

    $output = '';
    $group_names = [
        'all' => 'Everyone in Center',
        'teachers' => 'Teachers',
        'institute_admins' => 'Institute Admins'
    ];
    foreach ($active_conversations as $conv) {
        $conv_with = $conv->conversation_with;
        $name = isset($group_names[$conv_with]) ? $group_names[$conv_with] : 'Unknown';
        if (!isset($group_names[$conv_with])) {
            $user = get_user_by('login', $conv_with);
            $name = $user ? $user->display_name : 'Unknown';
        }
        $output .= '<li class="list-group-item" data-conversation-with="' . esc_attr($conv_with) . '">' . esc_html($name) . '</li>';
    }
    if (empty($active_conversations)) {
        $output = '<li class="list-group-item text-muted">No conversations yet.</li>';
    }

    wp_send_json_success($output);
}
// Teacher: Get Active Conversations (Updated)
// function aspire_teacher_get_active_conversations($username) {
//     global $wpdb;
//     $table = $wpdb->prefix . 'aspire_messages';
//     $edu_center_id = educational_center_teacher_id();
//     $user = wp_get_current_user();
//     $is_teacher = aspire_is_teacher($user);
//     $is_admin = aspire_is_institute_admin($username, $edu_center_id);

//     $group_receivers = ['all'];
//     if ($is_teacher) $group_receivers[] = 'teachers';
//     if ($is_admin) $group_receivers[] = 'institute_admins';

//     $placeholders = implode(',', array_fill(0, count($group_receivers), '%s'));
//     $query_args = array_merge([$username, $edu_center_id, $username, $username], $group_receivers);

//     $query = $wpdb->prepare(
//         "SELECT 
//             CASE 
//                 WHEN sender_id = %s THEN receiver_id 
//                 ELSE sender_id 
//             END AS conversation_with,
//             MAX(timestamp) AS last_message
//          FROM $table 
//          WHERE education_center_id = %s 
//          AND (sender_id = %s OR receiver_id = %s OR receiver_id IN ($placeholders))
//          GROUP BY conversation_with 
//          ORDER BY last_message DESC",
//         $query_args
//     );

//     $results = $wpdb->get_results($query);
//     error_log("Teacher active conversations for $username: " . print_r($results, true));
//     return $results;
// }


// Teacher: Get Messages (Updated for Ascending Order)
// function aspire_teacher_get_messages($username, $conversation_with = '') {
//     global $wpdb;
//     $table = $wpdb->prefix . 'aspire_messages';
//     $edu_center_id = educational_center_teacher_id();

//     $query = "SELECT * FROM $table WHERE education_center_id = %s";
//     $query_args = [$edu_center_id];

//     if ($conversation_with) {
//         $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s))";
//         $query_args[] = $username;
//         $query_args[] = $conversation_with;
//         $query_args[] = $conversation_with;
//         $query_args[] = $username;
//     } else {
//         $query .= " AND (receiver_id = %s OR receiver_id IN ('all', 'teachers'))";
//         $query_args[] = $username;
//     }

//     $query .= " ORDER BY timestamp ASC LIMIT 50"; // Changed to ASC for latest at bottom
//     $results = $wpdb->get_results($wpdb->prepare($query, $query_args));
//     error_log("Teacher messages for $username with $conversation_with: " . print_r($results, true));
//     return $results;
// }

// Teacher: AJAX Fetch Messages (Updated for Full Names and Order)
// function aspire_teacher_ajax_fetch_messages() {
//     check_ajax_referer('aspire_teacher_nonce', 'nonce');
//     $user = wp_get_current_user();
//     $username = $user->user_login;
//     $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');

//     $messages = aspire_teacher_get_messages($username, $conversation_with);
//     if ($conversation_with && !in_array($conversation_with, ['all', 'teachers', 'institute_admins'])) {
//         aspire_teacher_mark_messages_read($username, $conversation_with);
//     }

//     $contacts = get_posts([
//         'post_type' => ['teacher', 'students', 'parent'],
//         'posts_per_page' => -1,
//         'meta_key' => 'educational_center_id',
//         'meta_value' => educational_center_teacher_id(),
//     ]);
//     $admins = aspire_teacher_get_admins(educational_center_teacher_id());

//     $output = '';
//     foreach ($messages as $msg) {
//         $sender_name = ($msg->sender_id === $username) ? 'You' : null;
//         if (!$sender_name) {
//             foreach ($contacts as $contact) {
//                 if (aspire_teacher_get_username($contact->ID) === $msg->sender_id) {
//                     if ($contact->post_type === 'teacher') {
//                         $sender_name = get_post_meta($contact->ID, 'teacher_name', true);
//                     } elseif ($contact->post_type === 'students') {
//                         $sender_name = get_post_meta($contact->ID, 'student_name', true);
//                     } else {
//                         $sender_name = $contact->post_title;
//                     }
//                     break;
//                 }
//             }
//             if (!$sender_name) {
//                 foreach ($admins as $admin) {
//                     if ($admin['id'] === $msg->sender_id) {
//                         $sender_name = $admin['name'];
//                         break;
//                     }
//                 }
//             }
//             if (!$sender_name) {
//                 $sender = get_user_by('login', $msg->sender_id);
//                 $sender_name = $sender ? $sender->display_name : 'Unknown';
//             }
//         }
//         $initials = strtoupper(substr($sender_name === 'You' ? $user->display_name : $sender_name, 0, 2));
//         $output .= '<div class="chat-message ' . ($msg->sender_id === $username ? 'sent' : 'received') . ' ' . ($msg->status == 'sent' ? 'unread' : '') . '">';
//         $output .= '<div class="bubble">';
//         $output .= '<span class="avatar">' . esc_html($initials) . '</span>';
//         $output .= '<p>' . esc_html($msg->message) . '</p>';
//         $output .= '</div>';
//         $output .= '<div class="meta" data-timestamp="' . esc_attr($msg->timestamp) . '">' . esc_html($sender_name) . ' - ' . esc_html($msg->timestamp) . '</div>';
//         $output .= '</div>';
//     }

//     wp_send_json_success(['html' => $output, 'unread' => aspire_teacher_get_unread_count($username)]);
// }

// Teacher: Shortcode (Updated with New Chat Button and UI Tweaks)
function aspire_teacher_prochat_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to use this chat.</p>';
    }
    
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = educational_center_teacher_id();
    
    if (!aspire_is_teacher($user)) {
        return '<p>You do not have the required permissions to use this chat.</p>';
    }
    
    $unread_count = aspire_teacher_get_unread_count($username);
    $active_conversations = aspire_teacher_get_active_conversations($username);
    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);
    $admins = aspire_teacher_get_admins($edu_center_id);
    $is_teacher = aspire_is_teacher($user);
    $is_admin = aspire_is_institute_admin($username, $edu_center_id);

    ob_start();
    ?>
    <div id="aspire-teacher-prochat" class="chat-container">
        <div class="chat-wrapper">
            <div class="chat-sidebar">
                <div class="sidebar-header">
                    <h4>Inbox <span id="unread-badge" class="badge bg-danger"><?php echo $unread_count ?: ''; ?></span></h4>
                    <input type="text" id="conversation-search" class="form-control" placeholder="Search conversations...">
                </div>
                <ul id="aspire-teacher-conversations" class="conversation-list">
                    <?php
                    $group_names = [
                        'all' => 'Everyone in Center',
                        'teachers' => 'Teachers',
                        'institute_admins' => 'Institute Admins'
                    ];
                    foreach ($active_conversations as $conv) {
                        $conv_with = $conv->conversation_with;
                        $name = isset($group_names[$conv_with]) ? $group_names[$conv_with] : null;
                        if (!$name) {
                            foreach ($contacts as $contact) {
                                $contact_username = aspire_teacher_get_username($contact->ID);
                                if ($contact_username === $conv_with) {
                                    if ($contact->post_type === 'teacher') {
                                        $teacher_name = get_post_meta($contact->ID, 'teacher_name', true);
                                        $teacher_id = get_post_meta($contact->ID, 'teacher_id', true);
                                        $name = $teacher_name . ' (' . esc_html($teacher_id) . ' - Teacher)';
                                    } elseif ($contact->post_type === 'students') {
                                        $student_name = get_post_meta($contact->ID, 'student_name', true);
                                        $student_id = get_post_meta($contact->ID, 'student_id', true);
                                        $name = $student_name . ' (' . esc_html($student_id) . ' - Students)';
                                    } else {
                                        $name = $contact->post_title . ' (' . ucfirst($contact->post_type) . ')';
                                    }
                                    break;
                                }
                            }
                            if (!$name) {
                                foreach ($admins as $admin) {
                                    if ($admin['id'] === $conv_with) {
                                        $name = $admin['name'] . ' (Admin)';
                                        break;
                                    }
                                }
                            }
                            if (!$name) {
                                $user = get_user_by('login', $conv_with);
                                $name = $user ? $user->display_name : 'Unknown';
                            }
                        }
                        echo '<li class="conversation-item" data-conversation-with="' . esc_attr($conv_with) . '">' . esc_html($name) . '</li>';
                    }
                    if (empty($active_conversations)) {
                        echo '<li class="conversation-item text-muted">No conversations yet.</li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="chat-main">
                <div class="chat-header">
                    <h5 id="current-conversation">Select a conversation</h5>
                    <div>
                        <button id="new-chat" class="btn btn-outline-primary btn-sm">New Chat</button>
                        <button id="clear-conversation" class="btn btn-outline-secondary btn-sm" style="display:none;">Clear</button>
                    </div>
                </div>
                <div id="aspire-teacher-message-list" class="chat-messages"></div>
                <form id="aspire-teacher-send-form" class="chat-form">
                    <div class="input-group">
                        <textarea id="aspire-teacher-message-input" class="form-control" placeholder="Type your message..." rows="1" required></textarea>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
                    </div>
                    <div id="recipient-select" class="recipient-select" style="display:none;">
                        <select id="aspire-teacher-target-type" class="form-select">
                            <option value="group">Group</option>
                            <option value="individual">Individual</option>
                        </select>
                        <select id="aspire-teacher-target-value" class="form-select aspire-teacher-group-target">
                            <option value="all">Everyone in Center</option>
                            <option value="teachers">Teachers</option>
                            <?php if ($is_admin): ?>
                                <option value="institute_admins">Institute Admins</option>
                            <?php endif; ?>
                        </select>
                        <select id="aspire-teacher-individual-target" class="form-select aspire-teacher-individual-target" style="display:none;">
                            <?php foreach ($contacts as $contact): ?>
                                <?php 
                                $contact_username = aspire_teacher_get_username($contact->ID); 
                                if ($contact_username !== $username): 
                                    $user_data = get_user_by('login', $contact_username);
                                    $display_name = $user_data ? $user_data->display_name : $contact->post_title;
                                    if ($contact->post_type === 'teacher') {
                                        $teacher_name = get_post_meta($contact->ID, 'teacher_name', true);
                                        $teacher_id = get_post_meta($contact->ID, 'teacher_id', true);
                                        $label = $teacher_name . ' (' . esc_html($teacher_id) . ' - ' . ucfirst($contact->post_type) . ')';
                                    } elseif ($contact->post_type === 'students') {
                                        $student_name = get_post_meta($contact->ID, 'student_name', true);
                                        $student_id = get_post_meta($contact->ID, 'student_id', true);
                                        $label = $student_name . ' (' . esc_html($student_id) . ' - ' . ucfirst($contact->post_type) . ')';
                                    } else {
                                        $label = $display_name . ' (' . ucfirst($contact->post_type) . ')';
                                    }
                                ?>
                                    <option value="<?php echo esc_attr($contact_username); ?>">
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php foreach ($admins as $admin): ?>
                                <?php if ($admin['id'] !== $username): ?>
                                    <option value="<?php echo esc_attr($admin['id']); ?>">
                                        <?php echo esc_html($admin['name'] . ' (Admin @ Center ' . $edu_center_id . ')'); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php wp_nonce_field('aspire_teacher_nonce', 'aspire_teacher_nonce_field'); ?>
                </form>
            </div>
        </div>
    </div>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        let selectedConversation = localStorage.getItem('aspire_teacher_selected_conversation') || '';
        let currentRecipient = '';

        function fetchMessages(conversationWith) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_teacher_fetch_messages',
                    conversation_with: conversationWith,
                    nonce: $('#aspire_teacher_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-teacher-message-list').html(response.data.html);
                        $('#unread-badge').text(response.data.unread || '');
                        const chatMessages = document.querySelector('#aspire-teacher-message-list');
                        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
                        updateTimestamps();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                }
            });
        }

        function updateConversations() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_teacher_fetch_conversations',
                    nonce: $('#aspire_teacher_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-teacher-conversations').html(response.data);
                        if (selectedConversation) {
                            $(`#aspire-teacher-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
                        }
                    }
                }
            });
        }

        function updateTimestamps() {
            $('.chat-message .meta').each(function() {
                const timestamp = $(this).data('timestamp');
                $(this).text(`${$(this).text().split(' - ')[0]} - ${moment(timestamp).fromNow()}`);
            });
        }

        $('#aspire-teacher-target-type').change(function() {
            if ($(this).val() === 'individual') {
                $('.aspire-teacher-group-target').hide();
                $('.aspire-teacher-individual-target').show();
            } else {
                $('.aspire-teacher-group-target').show();
                $('.aspire-teacher-individual-target').hide();
            }
        });

        $(document).on('click', '#aspire-teacher-conversations li', function() {
            $('#aspire-teacher-conversations li').removeClass('active');
            $(this).addClass('active');
            selectedConversation = $(this).data('conversation-with');
            currentRecipient = selectedConversation;
            localStorage.setItem('aspire_teacher_selected_conversation', selectedConversation);
            $('#current-conversation').text($(this).text());
            $('#clear-conversation').show();
            $('#new-chat').show();
            $('#recipient-select').hide();
            fetchMessages(selectedConversation);
        });

        $('#clear-conversation').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_teacher_selected_conversation');
            $('#current-conversation').text('Select a conversation');
            $('#clear-conversation').hide();
            $('#new-chat').show();
            $('#recipient-select').hide();
            $('#aspire-teacher-message-list').empty();
            $('#aspire-teacher-conversations li').removeClass('active');
        });

        $('#new-chat').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_teacher_selected_conversation');
            $('#current-conversation').text('New Conversation');
            $('#clear-conversation').show();
            $('#new-chat').hide();
            $('#recipient-select').show();
            $('#aspire-teacher-message-list').empty();
            $('#aspire-teacher-conversations li').removeClass('active');
        });

        $('#aspire-teacher-send-form').submit(function(e) {
            e.preventDefault();
            const message = $('#aspire-teacher-message-input').val();
            let targetType, targetValue;

            if (currentRecipient) {
                targetType = currentRecipient.match(/^(all|teachers|institute_admins)$/) ? 'group' : 'individual';
                targetValue = currentRecipient;
            } else {
                targetType = $('#aspire-teacher-target-type').val();
                targetValue = targetType === 'individual' ? $('#aspire-teacher-individual-target').val() : $('#aspire-teacher-target-value').val();
                currentRecipient = targetValue;
                $(`#aspire-teacher-conversations li[data-conversation-with="${currentRecipient}"]`).addClass('active');
                $('#current-conversation').text($(`#aspire-teacher-conversations li[data-conversation-with="${currentRecipient}"]`).text() || 'New Conversation');
                $('#clear-conversation').show();
                $('#new-chat').hide();
                $('#recipient-select').hide();
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_teacher_send_message',
                    message: message,
                    target_type: targetType,
                    target_value: targetValue,
                    nonce: $('#aspire_teacher_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-teacher-message-input').val('');
                        fetchMessages(currentRecipient);
                        updateConversations();
                    } else {
                        console.error('Send failed:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                }
            });
        });

        $('#conversation-search').on('input', function() {
            const search = $(this).val().toLowerCase();
            $('.conversation-item').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(search));
            });
        });

        // Initial load
        if (selectedConversation) {
            $(`#aspire-teacher-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
            $('#current-conversation').text($(`#aspire-teacher-conversations li[data-conversation-with="${selectedConversation}"]`).text());
            $('#clear-conversation').show();
            $('#new-chat').show();
            $('#recipient-select').hide();
            fetchMessages(selectedConversation);
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_teacher_prochat', 'aspire_teacher_prochat_shortcode');
// Teacher: AJAX Fetch Messages (Updated for UI)
// function aspire_teacher_ajax_fetch_messages() {
//     check_ajax_referer('aspire_teacher_nonce', 'nonce');
//     $user = wp_get_current_user();
//     $username = $user->user_login;
//     $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');

//     $messages = aspire_teacher_get_messages($username, $conversation_with);
//     if ($conversation_with && !in_array($conversation_with, ['all', 'teachers', 'institute_admins'])) {
//         aspire_teacher_mark_messages_read($username, $conversation_with);
//     }

//     $output = '';
//     foreach ($messages as $msg) {
//         $sender = get_user_by('login', $msg->sender_id);
//         $sender_name = $sender ? $sender->display_name : 'Unknown';
//         $initials = $sender ? strtoupper(substr($sender_name, 0, 2)) : '??';
//         $output .= '<div class="chat-message ' . ($msg->sender_id === $username ? 'sent' : 'received') . ' ' . ($msg->status == 'sent' ? 'unread' : '') . '">';
//         $output .= '<div class="bubble">';
//         $output .= '<span class="avatar">' . esc_html($initials) . '</span>';
//         $output .= '<p>' . esc_html($msg->message) . '</p>';
//         $output .= '</div>';
//         $output .= '<div class="meta" data-timestamp="' . esc_attr($msg->timestamp) . '">' . esc_html($sender_name) . ' - ' . esc_html($msg->timestamp) . '</div>';
//         $output .= '</div>';
//     }

//     wp_send_json_success(['html' => $output, 'unread' => aspire_teacher_get_unread_count($username)]);
// }

// Teacher: Get Messages (Separate Group and Individual Messages)
// function aspire_teacher_get_messages($username, $conversation_with = '') {
//     global $wpdb;
//     $table = $wpdb->prefix . 'aspire_messages';
//     $edu_center_id = educational_center_teacher_id();
//     $user = wp_get_current_user();
//     $is_teacher = aspire_is_teacher($user);
//     $is_admin = aspire_is_institute_admin($username, $edu_center_id);

//     $query = "SELECT * FROM $table WHERE education_center_id = %s";
//     $query_args = [$edu_center_id];

//     if ($conversation_with) {
//         if (in_array($conversation_with, ['all', 'teachers', 'institute_admins'])) {
//             // Fetch only group messages for the selected group
//             $query .= " AND receiver_id = %s";
//             $query_args[] = $conversation_with;
//         } else {
//             // Fetch only individual conversation messages
//             $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s))";
//             $query_args[] = $username;
//             $query_args[] = $conversation_with;
//             $query_args[] = $conversation_with;
//             $query_args[] = $username;
//         }
//     } else {
//         // Fetch all relevant messages for the user, including applicable groups
//         $group_receivers = ['all'];
//         if ($is_teacher) $group_receivers[] = 'teachers';
//         if ($is_admin) $group_receivers[] = 'institute_admins';
//         $placeholders = implode(',', array_fill(0, count($group_receivers), '%s'));
//         $query .= " AND (receiver_id = %s OR receiver_id IN ($placeholders))";
//         $query_args[] = $username;
//         $query_args = array_merge($query_args, $group_receivers);
//     }

//     $query .= " ORDER BY timestamp ASC LIMIT 50";
//     $prepared_query = $wpdb->prepare($query, $query_args);
//     error_log("Teacher query: $prepared_query");
//     $results = $wpdb->get_results($prepared_query);
//     error_log("Teacher messages for $username with $conversation_with: " . print_r($results, true));
//     return $results;
// }

// Teacher: AJAX Fetch Messages (No Change Needed)
function aspire_teacher_ajax_fetch_messages() {
    check_ajax_referer('aspire_teacher_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;
    $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');
    error_log("Fetching messages for $username with conversation_with: $conversation_with");

    $messages = aspire_teacher_get_messages($username, $conversation_with);
    if ($conversation_with && !in_array($conversation_with, ['all', 'teachers', 'institute_admins'])) {
        aspire_teacher_mark_messages_read($username, $conversation_with);
    }

    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => educational_center_teacher_id(),
    ]);
    $admins = aspire_teacher_get_admins(educational_center_teacher_id());

    $output = '';
    foreach ($messages as $msg) {
        $sender_name = ($msg->sender_id === $username) ? 'You' : null;
        if (!$sender_name) {
            foreach ($contacts as $contact) {
                if (aspire_teacher_get_username($contact->ID) === $msg->sender_id) {
                    if ($contact->post_type === 'teacher') {
                        $sender_name = get_post_meta($contact->ID, 'teacher_name', true);
                    } elseif ($contact->post_type === 'students') {
                        $sender_name = get_post_meta($contact->ID, 'student_name', true);
                    } else {
                        $sender_name = $contact->post_title;
                    }
                    break;
                }
            }
            if (!$sender_name) {
                foreach ($admins as $admin) {
                    if ($admin['id'] === $msg->sender_id) {
                        $sender_name = $admin['name'];
                        break;
                    }
                }
            }
            if (!$sender_name) {
                $sender = get_user_by('login', $msg->sender_id);
                $sender_name = $sender ? $sender->display_name : 'Unknown';
            }
        }
        $initials = strtoupper(substr($sender_name === 'You' ? $user->display_name : $sender_name, 0, 2));
        $output .= '<div class="chat-message ' . ($msg->sender_id === $username ? 'sent' : 'received') . ' ' . ($msg->status == 'sent' ? 'unread' : '') . '">';
        $output .= '<div class="bubble">';
        $output .= '<span class="avatar">' . esc_html($initials) . '</span>';
        $output .= '<p>' . esc_html($msg->message) . '</p>';
        $output .= '</div>';
        $output .= '<div class="meta" data-timestamp="' . esc_attr($msg->timestamp) . '">' . esc_html($sender_name) . ' - ' . esc_html($msg->timestamp) . '</div>';
        $output .= '</div>';
    }

    wp_send_json_success(['html' => $output, 'unread' => aspire_teacher_get_unread_count($username)]);
}

// Teacher: Get Active Conversations (Fixed to Use sender_id and receiver_id)
function aspire_teacher_get_active_conversations($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = educational_center_teacher_id();
    $user = wp_get_current_user();
    $is_teacher = aspire_is_teacher($user);
    $is_admin = aspire_is_institute_admin($username, $edu_center_id);

    $group_receivers = ['all'];
    if ($is_teacher) $group_receivers[] = 'teachers';
    if ($is_admin) $group_receivers[] = 'institute_admins';
    $placeholders = implode(',', array_fill(0, count($group_receivers), '%s'));

    // Union distinct receivers (when user is sender) and senders (when user is receiver) with group receivers
    $query = "
        SELECT DISTINCT receiver_id AS conversation_with 
        FROM $table 
        WHERE education_center_id = %s 
        AND sender_id = %s
        UNION
        SELECT DISTINCT sender_id AS conversation_with 
        FROM $table 
        WHERE education_center_id = %s 
        AND receiver_id = %s 
        AND sender_id != %s
        UNION
        SELECT DISTINCT receiver_id AS conversation_with 
        FROM $table 
        WHERE education_center_id = %s 
        AND receiver_id IN ($placeholders)
    ";
    $query_args = array_merge(
        [$edu_center_id, $username],              // First query: user as sender
        [$edu_center_id, $username, $username],   // Second query: user as receiver, exclude self-sent
        [$edu_center_id],                         // Third query: group receivers
        $group_receivers
    );

    $prepared_query = $wpdb->prepare($query, $query_args);
    error_log("Teacher active conversations query: $prepared_query");
    $results = $wpdb->get_results($prepared_query);
    error_log("Teacher active conversations for $username: " . print_r($results, true));

    return $results;
}

// Teacher: Get Messages (Unchanged from Last Refinement)
function aspire_teacher_get_messages($username, $conversation_with = '') {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = educational_center_teacher_id();
    $user = wp_get_current_user();
    $is_teacher = aspire_is_teacher($user);
    $is_admin = aspire_is_institute_admin($username, $edu_center_id);

    $query = "SELECT * FROM $table WHERE education_center_id = %s";
    $query_args = [$edu_center_id];

    if ($conversation_with) {
        if (in_array($conversation_with, ['all', 'teachers', 'institute_admins'])) {
            $query .= " AND receiver_id = %s";
            $query_args[] = $conversation_with;
        } else {
            $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s))";
            $query_args[] = $username;
            $query_args[] = $conversation_with;
            $query_args[] = $conversation_with;
            $query_args[] = $username;
            $query .= " LIMIT 50";
        }
    } else {
        $group_receivers = ['all'];
        if ($is_teacher) $group_receivers[] = 'teachers';
        if ($is_admin) $group_receivers[] = 'institute_admins';
        $placeholders = implode(',', array_fill(0, count($group_receivers), '%s'));
        $query .= " AND (receiver_id = %s OR receiver_id IN ($placeholders))";
        $query_args[] = $username;
        $query_args = array_merge($query_args, $group_receivers);
    }

    $query .= " ORDER BY timestamp ASC";
    $prepared_query = $wpdb->prepare($query, $query_args);
    error_log("Teacher query: $prepared_query");
    $results = $wpdb->get_results($prepared_query);
    error_log("Teacher messages for $username with $conversation_with: " . print_r($results, true));
    return $results;
}
