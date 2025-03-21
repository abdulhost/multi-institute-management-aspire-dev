<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// // Enqueue Styles and Scripts
// add_action('wp_enqueue_scripts', 'aspire_teacher_dashboard_enqueue');
// function aspire_teacher_dashboard_enqueue() {
//     wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
//     wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
//     wp_enqueue_style('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css');
//     wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
//     wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js', [], null, true);
//     wp_add_inline_style('bootstrap', '
//         .dashboard-card { transition: transform 0.2s; border-radius: 10px; }
//         .dashboard-card:hover { transform: scale(1.05); }
//         .profile-avatar { width: 150px; height: 150px; object-fit: cover; border: 3px solid #007bff; border-radius: 50%; }
//         .form-section { margin-bottom: 15px; }
//         .section-header { cursor: pointer; background: #f1f1f1; padding: 10px; border-radius: 5px; }
//         .section-content { padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 5px; }

//         .chat-sidebar { overflow-y: auto; border-right: 1px solid #ddd; background: #f8f9fa; }
//         .chat-message { padding: 10px; margin: 5px 0; border-radius: 8px; max-width: 70%; position: relative; }
//         .chat-message.sent { background: #d4edda; align-self: flex-end; }
//         .chat-message.received { background: #e9ecef; align-self: flex-start; }
//         .chat-header { background: #007bff; color: white; padding: 10px; }
//         .unread { font-weight: bold; background: #cce5ff; }
//         .chat-container { display: flex; flex-direction: column;  }
//         .chat-messages { flex-grow: 1; overflow-y: auto; padding: 15px; }
   
    
//         ');
// }

// Function to retrieve Educational Center ID for a student
function educational_center_student_id() {
    if (!is_user_logged_in()) {
        return false;
    }

    $current_user = wp_get_current_user();
    if (!in_array('student', (array) $current_user->roles)) {
        return false;
    }

    return get_user_meta($current_user->ID, 'educational_center_id', true) ?: false;
}

// Function to get the current student's ID (username)
function aspire_get_current_student_id() {
    $user = wp_get_current_user();

    if (!$user || !$user->exists()) {
        error_log("No current user found or user not logged in.");
        return null;
    }

    if (!in_array('student', $user->roles)) {
        error_log("Current user {$user->user_login} is not a student.");
        return null;
    }

    $student_id = $user->user_login;
    error_log("Fetched current student ID: $student_id");
    return $student_id;
}

// Function to check if a user is a student
function is_student($user_id) {
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return false;
    }

    return in_array('student', (array) $user->roles);
}

// Main Student Dashboard Shortcode
function aspire_student_dashboard_shortcode() {
    global $wpdb;

    if (!function_exists('wp_get_current_user')) {
        return '<div class="alert alert-danger">Error: WordPress environment not fully loaded.</div>';
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $username = $current_user->user_login;

    if (!$user_id || !in_array('student', $current_user->roles)) {
        return '<div class="alert alert-danger">Access denied. Please log in as a student.</div>';
    }

    $education_center_id = educational_center_student_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $student_posts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.* 
             FROM $wpdb->posts p 
             LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'student' 
             AND p.post_status = 'publish' 
             AND (pm.meta_key = 'student_id' AND pm.meta_value = %d) 
             OR p.post_title = %s 
             LIMIT 1",
            $user_id,
            $username
        )
    );

    if (empty($student_posts)) {
        return '<div class="alert alert-warning">Student profile not found for user ID ' . esc_html($user_id) . ' or username "' . esc_html($username) . '". Please contact administration.</div>';
    }

    $student = get_post($student_posts[0]->ID);
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #f0f8ff, #e6e6fa); min-height: 100vh;">
        <div class="row">
            <?php 
            $active_section = $section;
            $active_action = $action;
            include plugin_dir_path(__FILE__) . 'student-sidebar.php'; // Assume a student-specific sidebar
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'overview':
                        echo render_student_overview($user_id, $student);
                        break;
                    case 'profile':
                        echo render_student_profile($user_id, $student);
                        break;
                    case 'classes':
                        echo render_student_classes($user_id, $student);
                        break;
                    case 'homework':
                        echo render_student_homework($user_id, $student);
                        break;
                    case 'exams':
                        echo render_student_exams($user_id, $student);
                        break;
                    case 'result':
                        echo render_student_result($user_id, $student);
                        break;
                    case 'attendance':
                        echo render_student_attendance($user_id, $student);
                        break;
                    case 'timetable':
                        echo render_student_timetable($user_id, $student);
                        break;
                    case 'communication':
                        echo aspire_student_communication_shortcode(); // Placeholder for student-specific communication
                        break;
                    case 'noticeboard':
                        echo aspire_student_notice_board_shortcode(); // Placeholder for noticeboard
                        break;
                    case 'fees':
                        echo render_student_fees($user_id, $student);
                        break;
                    default:
                        echo render_student_overview($user_id, $student);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_student_dashboard', 'aspire_student_dashboard_shortcode');

// Feature 1: Dashboard Overview
function render_student_overview($user_id, $student) {
    $student_name = get_post_meta($student->ID, 'student_name', true);
    $student_email = get_post_meta($student->ID, 'student_email', true);
    $class = get_post_meta($student->ID, 'student_class', true); // Placeholder: Fetch class info
    $today = date('Y-m-d');
    $unread_messages = 3; // Placeholder: Query wp_messages
    $classes_today = 4;   // Placeholder: Query timetable

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
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
                            <a href="?section=communication" class="btn btn-outline-primary btn-sm">View Messages</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card bg-light border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">Classes Today</h5>
                            <p class="card-text display-6"><?php echo esc_html($classes_today); ?></p>
                            <a href="?section=timetable" class="btn btn-outline-success btn-sm">View Timetable</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card bg-light border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-book text-warning" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">Pending Homework</h5>
                            <p class="card-text display-6">2</p>
                            <a href="?section=homework" class="btn btn-outline-warning btn-sm">View Homework</a>
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
                            <p><strong>Student ID:</strong> <?php echo esc_html(get_post_meta($student->ID, 'student_id', true)); ?></p>
                            <p><strong>Name:</strong> <?php echo esc_html($student_name); ?></p>
                            <p><strong>Email:</strong> <?php echo esc_html($student_email); ?></p>
                            <p><strong>Class:</strong> <?php echo esc_html($class); ?></p>
                            <a href="?section=profile" class="btn btn-info btn-sm">View Profile</a>
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
                                        { title: 'Science - Class 10A', start: '<?php echo $today; ?>T11:00:00', end: '<?php echo $today; ?>T12:00:00' }
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
function render_student_profile($user_id, $student) {
    $student_data = [
        'student_id' => get_post_meta($student->ID, 'student_id', true),
        'student_name' => get_post_meta($student->ID, 'student_name', true),
        'student_email' => get_post_meta($student->ID, 'student_email', true),
        'student_phone_number' => get_post_meta($student->ID, 'phone_number', true),
        'student_roll_number' => get_post_meta($student->ID, 'roll_number', true),
        'student_admission_date' => get_post_meta($student->ID, 'admission_date', true),
        'student_gender' => get_post_meta($student->ID, 'gender', true),
        'student_religion' => get_post_meta($student->ID, 'religion', true),
        'student_blood_group' => get_post_meta($student->ID, 'blood_group', true),
        'student_date_of_birth' => get_post_meta($student->ID, 'date_of_birth', true),
        'student_height' => get_post_meta($student->ID, 'height', true),
        'student_weight' => get_post_meta($student->ID, 'weight', true),
        'student_current_address' => get_post_meta($student->ID, 'current_address', true),
        'student_permanent_address' => get_post_meta($student->ID, 'permanent_address', true),
    ];
    $avatar = wp_get_attachment_url(get_post_meta($student->ID, 'student_profile_photo', true)) ?: 'https://via.placeholder.com/150';

    if (isset($_POST['update_profile']) && wp_verify_nonce($_POST['profile_nonce'], 'update_profile')) {
        $fields = [
            'student_name' => sanitize_text_field($_POST['student_name']),
            'student_email' => sanitize_email($_POST['student_email']),
            'phone_number' => sanitize_text_field($_POST['student_phone_number']),
            'roll_number' => sanitize_text_field($_POST['student_roll_number']),
            'admission_date' => sanitize_text_field($_POST['student_admission_date']),
            'gender' => sanitize_text_field($_POST['student_gender']),
            'religion' => sanitize_text_field($_POST['student_religion']),
            'blood_group' => sanitize_text_field($_POST['student_blood_group']),
            'date_of_birth' => sanitize_text_field($_POST['student_date_of_birth']),
            'height' => sanitize_text_field($_POST['student_height']),
            'weight' => sanitize_text_field($_POST['student_weight']),
            'current_address' => sanitize_textarea_field($_POST['student_current_address']),
            'permanent_address' => sanitize_textarea_field($_POST['student_permanent_address']),
        ];

        foreach ($fields as $key => $value) {
            update_post_meta($student->ID, $key, $value);
        }

        if (!empty($_FILES['student_profile_photo']['name'])) {
            $upload = wp_handle_upload($_FILES['student_profile_photo'], ['test_form' => false]);
            if (isset($upload['file'])) {
                $attachment_id = wp_insert_attachment([
                    'guid' => $upload['url'],
                    'post_mime_type' => $upload['type'],
                    'post_title' => basename($upload['file']),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ], $upload['file']);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
                update_post_meta($student->ID, 'student_profile_photo', $attachment_id);
                $avatar = $upload['url'];
            }
        }

        $student_data = array_merge($student_data, $fields);
        echo '<div class="alert alert-success">Profile updated successfully!</div>';
        header("Refresh:0");}

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white">
            <h3 class="card-title m-0"><i class="bi bi-person-circle me-2"></i>Profile Management</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <img src="<?php echo esc_url($avatar); ?>" alt="Avatar" class="profile-avatar mb-3">
                    <h5><?php echo esc_html($student_data['student_name']); ?></h5>
                    <p class="text-muted">Student ID: <?php echo esc_html($student_data['student_id']); ?></p>
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
                                    <label for="student_name" class="form-label">Full Name</label>
                                    <input type="text" name="student_name" id="student_name" class="form-control" value="<?php echo esc_attr($student_data['student_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="student_email" class="form-label">Email</label>
                                    <input type="email" name="student_email" id="student_email" class="form-control" value="<?php echo esc_attr($student_data['student_email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="student_phone_number" class="form-label">Phone Number</label>
                                    <input type="text" name="student_phone_number" id="student_phone_number" class="form-control" value="<?php echo esc_attr($student_data['student_phone_number']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="student_roll_number" class="form-label">Roll Number</label>
                                    <input type="text" name="student_roll_number" id="student_roll_number" class="form-control" value="<?php echo esc_attr($student_data['student_roll_number']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="student_admission_date" class="form-label">Admission Date</label>
                                    <input type="date" name="student_admission_date" id="student_admission_date" class="form-control" value="<?php echo esc_attr($student_data['student_admission_date']); ?>" required>
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
                                    <label for="student_gender" class="form-label">Gender</label>
                                    <select name="student_gender" id="student_gender" class="form-select">
                                        <option value="Male" <?php selected($student_data['student_gender'], 'Male'); ?>>Male</option>
                                        <option value="Female" <?php selected($student_data['student_gender'], 'Female'); ?>>Female</option>
                                        <option value="Other" <?php selected($student_data['student_gender'], 'Other'); ?>>Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="student_religion" class="form-label">Religion</label>
                                    <input type="text" name="student_religion" id="student_religion" class="form-control" value="<?php echo esc_attr($student_data['student_religion']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="student_blood_group" class="form-label">Blood Group</label>
                                    <select name="student_blood_group" id="student_blood_group" class="form-select">
                                        <option value="A+" <?php selected($student_data['student_blood_group'], 'A+'); ?>>A+</option>
                                        <option value="A-" <?php selected($student_data['student_blood_group'], 'A-'); ?>>A-</option>
                                        <option value="B+" <?php selected($student_data['student_blood_group'], 'B+'); ?>>B+</option>
                                        <option value="B-" <?php selected($student_data['student_blood_group'], 'B-'); ?>>B-</option>
                                        <option value="AB+" <?php selected($student_data['student_blood_group'], 'AB+'); ?>>AB+</option>
                                        <option value="AB-" <?php selected($student_data['student_blood_group'], 'AB-'); ?>>AB-</option>
                                        <option value="O+" <?php selected($student_data['student_blood_group'], 'O+'); ?>>O+</option>
                                        <option value="O-" <?php selected($student_data['student_blood_group'], 'O-'); ?>>O-</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="student_date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" name="student_date_of_birth" id="student_date_of_birth" class="form-control" value="<?php echo esc_attr($student_data['student_date_of_birth']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="student_height" class="form-label">Height (cm)</label>
                                    <input type="number" name="student_height" id="student_height" class="form-control" value="<?php echo esc_attr($student_data['student_height']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="student_weight" class="form-label">Weight (kg)</label>
                                    <input type="number" name="student_weight" id="student_weight" class="form-control" value="<?php echo esc_attr($student_data['student_weight']); ?>">
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
                                    <label for="student_current_address" class="form-label">Current Address</label>
                                    <textarea name="student_current_address" id="student_current_address" class="form-control"><?php echo esc_textarea($student_data['student_current_address']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="student_permanent_address" class="form-label">Permanent Address</label>
                                    <textarea name="student_permanent_address" id="student_permanent_address" class="form-control"><?php echo esc_textarea($student_data['student_permanent_address']); ?></textarea>
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
                                    <label for="student_profile_photo" class="form-label">Profile Photo</label>
                                    <input type="file" name="student_profile_photo" id="student_profile_photo" class="form-control" accept="image/*">
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

// Placeholder Functions for Other Sections (to be implemented as needed)
function render_student_classes($user_id, $student) {
    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h3 class="card-title m-0"><i class="bi bi-book me-2"></i>My Classes</h3>
        </div>
        <div class="card-body">
            <p>Display list of classes for student ID: <?php echo esc_html($user_id); ?></p>
            <!-- Add logic to fetch and display classes -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function render_student_homework($user_id, $student) {
    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-white">
            <h3 class="card-title m-0"><i class="bi bi-pencil-square me-2"></i>My Homework</h3>
        </div>
        <div class="card-body">
            <p>Display homework assignments for student ID: <?php echo esc_html($user_id); ?></p>
            <!-- Add logic to fetch and display homework -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function render_student_exams($user_id, $student) {
    global $wpdb;

    $exams_table = $wpdb->prefix . 'exams';

    // Get educational center ID (assuming this function exists)
    $education_center_id = educational_center_student_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    // Get student’s class_id from post meta (adjust key if different)
    $class_id = get_post_meta($student->ID, 'class', true); // e.g., "class 11"
    if (empty($class_id)) {
        return '<div class="alert alert-warning">Class not assigned. Please contact administration.</div>';
    }

    // Generate nonce and REST URL
    $nonce = wp_create_nonce('wp_rest');
    $rest_url = rest_url('exams/v1/fetch');

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-danger text-white">
            <h3 class="card-title m-0"><i class="bi bi-clipboard-check me-2"></i>My Exams</h3>
        </div>
        <div class="card-body">
            <div class="filter mb-3">
                <select id="exam-filter" class="form-select w-auto">
                    <option value="all">All Exams</option>
                    <option value="upcoming">Upcoming Exams</option>
                    <option value="past">Past Exams</option>
                </select>
            </div>
            <div class="exams-table-wrapper" id="exams-table-container">
                <p class="loading-message"><span class="spinner"></span> Loading exam data...</p>
            </div>
        </div>
    </div>

    <style>
        .loading-message .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ccc;
            border-top: 2px solid #333;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 5px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .exams-table {
            width: 100%;
            border-collapse: collapse;
        }
        .exams-table th, .exams-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .exams-table th {
            background-color: #dc3545; /* Matches bg-danger */
            color: white;
        }
        .exams-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        function loadExamsTable() {
            var classId = '<?php echo esc_js($class_id); ?>';
            var filter = $('#exam-filter').val();

            $.ajax({
                url: '<?php echo esc_js($rest_url); ?>',
                method: 'POST',
                data: {
                    class_id: classId,
                    filter: filter,
                    _wpnonce: '<?php echo esc_js($nonce); ?>'
                },
                headers: {
                    'X-WP-Nonce': '<?php echo esc_js($nonce); ?>'
                },
                beforeSend: function() {
                    $('#exams-table-container').html('<p class="loading-message"><span class="spinner"></span> Loading exam data...</p>');
                },
                success: function(response) {
                    console.log('Full AJAX Response:', response); // Debug
                    if (response.success && response.data && response.data.html) {
                        $('#exams-table-container').html(response.data.html);
                    } else {
                        $('#exams-table-container').html('<p>No exams found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#exams-table-container').html('<p>Error loading data: ' + (xhr.responseJSON?.message || error) + '</p>');
                    console.log('AJAX Error:', xhr.responseText);
                }
            });
        }

        // Load table on page load and on filter change
        loadExamsTable();
        $('#exam-filter').on('change', loadExamsTable);
    });
    </script>
    <?php
    return ob_get_clean();
}

// Register REST endpoint
add_action('rest_api_init', function () {
    register_rest_route('exams/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_student_exams_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

function fetch_student_exams_data($request) {
    global $wpdb;
    $exams_table = $wpdb->prefix . 'exams';
    $params = $request->get_params();

    $education_center_id = educational_center_student_id();
    if (!$education_center_id) {
        error_log('fetch_student_exams_data: No educational center ID');
        return array(
            'success' => false,
            'message' => 'Educational center ID not found'
        );
    }

    $class_id = sanitize_text_field($params['class_id'] ?? '');
    if (empty($class_id)) {
        error_log('fetch_student_exams_data: No class ID');
        return array(
            'success' => false,
            'message' => 'Class ID missing'
        );
    }

    $filter = sanitize_text_field($params['filter'] ?? 'all');
    $current_date = current_time('Y-m-d'); // WordPress current date

    // Build query
    $query = "SELECT name, exam_date, class_id 
              FROM $exams_table 
              WHERE education_center_id = %s 
              AND class_id = %s";
    $query_args = [$education_center_id, $class_id];

    if ($filter === 'upcoming') {
        $query .= " AND exam_date >= %s";
        $query_args[] = $current_date;
    } elseif ($filter === 'past') {
        $query .= " AND exam_date < %s";
        $query_args[] = $current_date;
    }

    $query .= " ORDER BY exam_date ASC";
    $results = $wpdb->get_results($wpdb->prepare($query, $query_args));

    // Debug query execution
    if ($wpdb->last_error) {
        error_log('fetch_student_exams_data: SQL Error - ' . $wpdb->last_error);
        error_log('Query: ' . $wpdb->last_query);
    } else {
        error_log('fetch_student_exams_data: Query - ' . $wpdb->last_query);
        error_log('Results count: ' . count($results));
    }

    // Generate table HTML
    ob_start();
    ?>
    <table class="exams-table">
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Date</th>
                <th>Class Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)) : ?>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row->name); ?></td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($row->exam_date))); ?></td>
                        <td><?php echo esc_html($row->class_id); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3">No exams found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $table_html = ob_get_clean();

    $response = array(
        'success' => true,
        'data' => array(
            'html' => $table_html
        )
    );
    error_log('fetch_student_exams_data: Response - ' . json_encode($response));
    return $response;
}

function render_student_attendance($user_id, $student) {
    global $wpdb;

    // Get educational center ID
    $education_center_id = educational_center_student_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    // Get student ID
    $student_id = get_post_meta($student->ID, 'student_id', true);
    if (empty($student_id)) {
        return '<div class="alert alert-warning">Student ID not assigned. Please contact administration.</div>';
    }

    $table_name = $wpdb->prefix . 'student_attendance';

    // Get available months and subjects for filters
    $months = $wpdb->get_col("SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') FROM $table_name WHERE student_id = '$student_id' AND education_center_id = '$education_center_id' ORDER BY date DESC");
    $subjects = $wpdb->get_col("SELECT DISTINCT subject FROM $table_name WHERE student_id = '$student_id' AND education_center_id = '$education_center_id'");

    // Initial display uses current month
    $selected_month = date('Y-m');
    $selected_subject = '';

    // Build the initial HTML
    $output = '<div class="attendance-container" data-student-id="' . esc_attr($student_id) . '" data-center-id="' . esc_attr($education_center_id) . '">';
    
    // Filter form
    $output .= '<div class="attendance-filters">';
    $output .= '<select name="attendance_month" id="attendance-month">';
    foreach ($months as $month_option) {
        $selected = ($month_option == $selected_month) ? 'selected' : '';
        $output .= "<option value='$month_option' $selected>" . date('F Y', strtotime($month_option . '-01')) . "</option>";
    }
    $output .= '</select>';

    if (!empty($subjects)) {
        $output .= '<select name="attendance_subject" id="attendance-subject">';
        $output .= '<option value="">All Subjects</option>';
        foreach ($subjects as $subject) {
            $selected = ($subject == $selected_subject) ? 'selected' : '';
            $output .= "<option value='$subject' $selected>$subject</option>";
        }
        $output .= '</select>';
    }
    $output .= '</div>';

    // Table container
    $output .= '<div class="attendance-table-wrapper">';
    $output .= '<div id="attendance-table-content">Loading attendance data...</div>';
    $output .= '</div></div>';

    // CSS
    $output .= '<style>
        .attendance-container {
            max-width: 100%;
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        .attendance-filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .attendance-filters select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .attendance-table-wrapper {
            overflow-x: auto;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .attendance-table th,
        .attendance-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e5e5;
        }
        .attendance-table th {
            background: #f8f9fa;
            font-weight: 600;
            vertical-align: middle;
        }
        .attendance-table th small {
            font-weight: normal;
            font-size: 0.8em;
            color: #666;
        }
        .attendance-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .attendance-table tr:hover {
            background: #f5f5f5;
        }
        .present { color: #28a745; font-weight: bold; }
        .late { color: #ffc107; font-weight: bold; }
        .absent { color: #dc3545; font-weight: bold; }
        .full-day { color: #17a2b8; font-weight: bold; }
        .holiday { color: #6c757d; font-weight: bold; }
    </style>';

    // JavaScript for AJAX
    $output .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.querySelector(".attendance-container");
            const monthSelect = document.getElementById("attendance-month");
            const subjectSelect = document.getElementById("attendance-subject");
            const tableContent = document.getElementById("attendance-table-content");
            
            function loadAttendance() {
                const studentId = container.dataset.studentId;
                const centerId = container.dataset.centerId;
                const month = monthSelect.value;
                const subject = subjectSelect ? subjectSelect.value : "";
                
                tableContent.innerHTML = "Loading...";
                
                fetch("' . admin_url('admin-ajax.php') . '", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `action=get_attendance_data&student_id=${studentId}&center_id=${centerId}&month=${month}&subject=${subject}`
                })
                .then(response => response.text())
                .then(data => {
                    tableContent.innerHTML = data;
                })
                .catch(error => {
                    tableContent.innerHTML = "Error loading attendance data";
                    console.error("Error:", error);
                });
            }

            // Initial load
            loadAttendance();

            // Event listeners for filter changes
            monthSelect.addEventListener("change", loadAttendance);
            if (subjectSelect) {
                subjectSelect.addEventListener("change", loadAttendance);
            }
        });
    </script>';

    return $output;
}

// AJAX handler
add_action('wp_ajax_get_attendance_data', 'get_attendance_data_callback');
function get_attendance_data_callback() {
    global $wpdb;

    $student_id = sanitize_text_field($_POST['student_id']);
    $center_id = sanitize_text_field($_POST['center_id']);
    $selected_month = sanitize_text_field($_POST['month']);
    $selected_subject = sanitize_text_field($_POST['subject']);

    $table_name = $wpdb->prefix . 'student_attendance';

    $query = "SELECT student_id, student_name, class, section, date, status, subject 
              FROM $table_name 
              WHERE student_id = %s 
              AND education_center_id = %s 
              AND DATE_FORMAT(date, '%Y-%m') = %s";
    
    $params = [$student_id, $center_id, $selected_month];
    
    if (!empty($selected_subject)) {
        $query .= " AND subject = %s";
        $params[] = $selected_subject;
    }
    
    $query .= " ORDER BY date DESC";
    
    $results = $wpdb->get_results($wpdb->prepare($query, $params));

    if (empty($results)) {
        echo '<div class="alert alert-info">No attendance records found.</div>';
        wp_die();
    }

    // Process data
    $student_data = [];
    foreach ($results as $attendance) {
        $student_key = $attendance->student_id;
        if (!isset($student_data[$student_key])) {
            $student_data[$student_key] = [
                'name' => $attendance->student_name,
                'class' => $attendance->class,
                'section' => $attendance->section,
                'attendance' => [],
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0],
            ];
        }

        $day = (int) date('j', strtotime($attendance->date));
        $status_short = '';
        switch ($attendance->status) {
            case 'Present': $status_short = 'P'; $student_data[$student_key]['counts']['P']++; break;
            case 'Late': $status_short = 'L'; $student_data[$student_key]['counts']['L']++; break;
            case 'Absent': $status_short = 'A'; $student_data[$student_key]['counts']['A']++; break;
            case 'Full Day': $status_short = 'F'; $student_data[$student_key]['counts']['F']++; break;
            case 'Holiday': $status_short = 'H'; $student_data[$student_key]['counts']['H']++; break;
        }
        $student_data[$student_key]['attendance'][$day] = $status_short;
    }

    $month = date('n', strtotime($selected_month . '-01'));
    $year = date('Y', strtotime($selected_month . '-01'));
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Build table
    $output = '<table class="attendance-table">';
    $output .= '<thead><tr>';
    $output .= '<th rowspan="2">Name</th>';
    $output .= '<th rowspan="2">Adm No</th>';
    $output .= '<th rowspan="2">Class</th>';
    $output .= "<th colspan='5'>Attendance Summary</th>";
    $output .= "<th colspan='$days_in_month'>". date('F Y', strtotime($selected_month . '-01')) ."</th>";
    $output .= '</tr><tr>';
    $output .= '<th>P</th><th>L</th><th>A</th><th>F</th><th>H</th>';
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $day_name = date('D', strtotime($date));
        $output .= '<th>' . $day . '<br><small>' . $day_name . '</small></th>';
    }
    $output .= '</tr></thead>';
    $output .= '<tbody>';

    foreach ($student_data as $student_id => $data) {
        $total_days = $days_in_month - $data['counts']['H'];
        $percent = ($total_days > 0) ? round(($data['counts']['P'] / $total_days) * 100) : 0;

        $output .= '<tr>';
        $output .= '<td>' . esc_html($data['name']) . '</td>';
        $output .= '<td>' . esc_html($student_id) . '</td>';
        $output .= '<td>' . esc_html($data['class'] . ' - ' . $data['section']) . '</td>';
        $output .= '<td class="present">' . esc_html($data['counts']['P']) . '</td>';
        $output .= '<td class="late">' . esc_html($data['counts']['L']) . '</td>';
        $output .= '<td class="absent">' . esc_html($data['counts']['A']) . '</td>';
        $output .= '<td class="full-day">' . esc_html($data['counts']['F']) . '</td>';
        $output .= '<td class="holiday">' . esc_html($data['counts']['H']) . '</td>';

        for ($day = 1; $day <= $days_in_month; $day++) {
            $status = $data['attendance'][$day] ?? '';
            $class = strtolower($status);
            $output .= "<td class='$class'>" . esc_html($status) . '</td>';
        }
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';

    echo $output;
    wp_die();
}
 
        use Dompdf\Dompdf;
    use Dompdf\Options;

    function render_student_timetable($user_id, $student) {
        global $wpdb;
    
        // Get the educational center ID for the student
        $education_center_id = educational_center_student_id();
        if (empty($education_center_id)) {
            ob_start();
            ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-calendar me-2"></i>My Timetable</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">No Educational Center found.</div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    
        // Fetch student's class and section from post meta (stored as text)
        $student_class_name = get_post_meta($student->ID, 'class', true); // e.g., "Class 10"
        $student_section_name = get_post_meta($student->ID, 'section', true); // e.g., "A"
    
        if (empty($student_class_name) || empty($student_section_name)) {
            ob_start();
            ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-calendar me-2"></i>My Timetable</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">Class or section not assigned. Please contact administration.</div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    
        // Map student's class name and section to class_id from wp_class_sections
        $class_section = $wpdb->get_row($wpdb->prepare(
            "SELECT id, class_name, sections 
             FROM {$wpdb->prefix}class_sections 
             WHERE education_center_id = %s 
             AND class_name = %s 
             AND FIND_IN_SET(%s, sections)",
            $education_center_id,
            $student_class_name,
            $student_section_name
        ));
    
        if (!$class_section) {
            ob_start();
            ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title m-0"><i class="bi bi-calendar me-2"></i>My Timetable</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">No matching class or section found in the system.</div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    
        $class_id = $class_section->id;
        $section = $student_section_name;
    
        // Fetch timetable data for the student's class and section
        $timetable = $wpdb->get_results($wpdb->prepare(
            "SELECT t.day, t.start_time, t.end_time, t.section, c.class_name, s.subject_name 
             FROM {$wpdb->prefix}timetables t 
             JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
             LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
             WHERE t.education_center_id = %s 
             AND t.class_id = %d 
             AND t.section = %s 
             ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time",
            $education_center_id,
            $class_id,
            $section
        ));
    
        // Define days in order
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
        // Collect unique time slots
        $time_slots = [];
        foreach ($timetable as $slot) {
            if (!is_object($slot) || !isset($slot->start_time) || !isset($slot->end_time)) {
                continue;
            }
            $time_range = $slot->start_time . '-' . $slot->end_time;
            $time_slots[$time_range] = true;
        }
        ksort($time_slots);
        $time_slot_array = array_keys($time_slots);
    
        ob_start();
        ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0"><i class="bi bi-calendar me-2"></i>My Timetable</h3>
                <div>
                    <span>Class: <?php echo esc_html($student_class_name . ' (' . $student_section_name . ')'); ?></span>
                    <button class="btn btn-light ml-2 generate-student-pdf-btn" 
                            data-class-id="<?php echo esc_attr($class_id); ?>" 
                            data-filter-section="<?php echo esc_attr($section); ?>" 
                            data-nonce="<?php echo wp_create_nonce('generate_student_timetable_pdf'); ?>">Export as PDF</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($timetable)) : ?>
                    <div class="alert alert-info">No timetable entries found for your class and section.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" style="background-color: #fff;">
                            <thead class="table-dark" style="background-color: #007bff; color: white;">
                                <tr>
                                    <th style="width: 100px;">Day</th>
                                    <?php foreach ($time_slot_array as $time_range) : ?>
                                        <th><?php echo esc_html($time_range); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($days as $day) : ?>
                                    <tr>
                                        <td class="bg-light font-weight-bold"><?php echo esc_html($day); ?></td>
                                        <?php foreach ($time_slot_array as $time_range) : 
                                            [$start, $end] = explode('-', $time_range);
                                            $found = false;
                                            foreach ($timetable as $slot) :
                                                if (!is_object($slot)) continue;
                                                if ($slot->day === $day && $slot->start_time === $start && $slot->end_time === $end) : ?>
                                                    <td style="background-color: #e9f7ef;">
                                                        <?php echo esc_html($slot->class_name . ' (' . $slot->section . ') - ' . ($slot->subject_name ?: 'N/A')); ?>
                                                    </td>
                                                    <?php $found = true;
                                                    break;
                                                endif;
                                            endforeach;
                                            if (!$found) : ?>
                                                <td class="text-muted">-</td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
                const pdfButton = document.querySelector('.generate-student-pdf-btn');
    
                pdfButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const button = this;
                    const classId = button.getAttribute('data-class-id');
                    const filterSection = button.getAttribute('data-filter-section');
                    const pdfNonce = button.getAttribute('data-nonce');
    
                    button.disabled = true;
                    button.textContent = 'Generating...';
    
                    const data = new FormData();
                    data.append('action', 'generate_student_timetable_pdf');
                    data.append('nonce', pdfNonce);
                    data.append('class_id', classId);
                    data.append('filter_section', filterSection);
    
                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `timetable_${classId}_${filterSection}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        button.disabled = false;
                        button.textContent = 'Export as PDF';
                    })
                    .catch(error => {
                        console.error('PDF Generation Error:', error);
                        alert('An error occurred while generating the PDF.');
                        button.disabled = false;
                        button.textContent = 'Export as PDF';
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
    
    // Register AJAX handler for student timetable PDF generation
    add_action('wp_ajax_generate_student_timetable_pdf', 'generate_student_timetable_pdf_callback');
    function generate_student_timetable_pdf_callback() {
        global $wpdb;
    
        // Verify nonce
        check_ajax_referer('generate_student_timetable_pdf', 'nonce');
    
        // Get parameters from POST
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        $filter_section = isset($_POST['filter_section']) ? sanitize_text_field($_POST['filter_section']) : '';
        $education_center_id = educational_center_student_id();
    
        if (empty($education_center_id) || empty($class_id) || empty($filter_section)) {
            wp_send_json_error('Missing required parameters.');
            wp_die();
        }
    
        // Fetch timetable data
        $timetable = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, c.class_name, s.subject_name 
             FROM {$wpdb->prefix}timetables t 
             JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
             LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
             WHERE t.education_center_id = %s 
             AND t.class_id = %d 
             AND t.section = %s 
             ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time",
            $education_center_id,
            $class_id,
            $filter_section
        ));
    
        if (empty($timetable)) {
            wp_send_json_error('No timetable data found.');
            wp_die();
        }
    
        // Load Dompdf
        // require_once dirname(__FILE__) . '/exam/dompdf/autoload.inc.php';
        require_once dirname(__FILE__) . '/../assets/exam/dompdf/autoload.inc.php';

    
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', ABSPATH);
        $options->set('tempDir', sys_get_temp_dir());
        $options->set('defaultFont', 'Helvetica');
    
        $dompdf = new Dompdf($options);
    
        // Prepare days and time slots
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $time_slots = [];
        foreach ($timetable as $slot) {
            if (!is_object($slot) || !isset($slot->start_time) || !isset($slot->end_time)) {
                continue;
            }
            $time_slots[$slot->start_time . '-' . $slot->end_time] = true;
        }
        ksort($time_slots);
        $time_slot_array = array_keys($time_slots);
    
        // Generate HTML (exact match to sample code)
        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { margin: 10mm; border: 2px solid #1a2b5f; padding: 4mm; }
                body { font-family: Helvetica, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
                .container { width: 100%; padding: 15px; border: 1px solid #ccc; background-color: #fff; }
                .header { text-align: center; padding-bottom: 10px; border-bottom: 2px solid #1a2b5f; margin-bottom: 15px; }
                .header h1 { font-size: 16pt; color: #1a2b5f; margin: 0; }
                .header p { font-size: 12pt; color: #666; margin: 5px 0 0; }
                table { width: 100%; border-collapse: collapse; font-size: 9pt; }
                th, td { border: 1px solid #333; padding: 6px; text-align: center; min-width: 100px; }
                th { background-color: #1a2b5f; color: white; font-weight: bold; }
                .day-column { background-color: #f5f5f5; font-weight: bold; width: 80px; }
                .subject { font-size: 8pt; word-wrap: break-word; }
                .empty { background-color: #f0f0f0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Weekly Timetable</h1>
                    <p>Education Center: ' . esc_html($education_center_id) . ' - Class: ' . esc_html($wpdb->get_var($wpdb->prepare("SELECT class_name FROM {$wpdb->prefix}class_sections WHERE id = %d", $class_id))) . ' - Section: ' . esc_html($filter_section) . '</p>
                </div>
                <table>
                    <thead>
                        <tr><th>Day</th>';
        foreach ($time_slot_array as $time_range) {
            $html .= '<th>' . esc_html($time_range) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
    
        foreach ($days as $day) {
            $html .= '<tr><td class="day-column">' . $day . '</td>';
            foreach ($time_slot_array as $time_range) {
                [$start, $end] = explode('-', $time_range);
                $found = false;
                foreach ($timetable as $slot) {
                    if ($slot->day === $day && $slot->start_time === $start && $slot->end_time === $end) {
                        $html .= '<td class="subject">' . esc_html($slot->class_name . ' (' . $slot->section . ') - ' . ($slot->subject_name ?: 'N/A')) . '</td>';
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $html .= '<td class="empty">-</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></body></html>';
    
        // Render PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
    
        // Output PDF with headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="timetable_' . rawurlencode($education_center_id . '_class_' . $class_id . '_section_' . $filter_section) . '.pdf"');
        header('Content-Length: ' . strlen($dompdf->output()));
        echo $dompdf->output();
        wp_die(); // Ensure no extra output
    }
function render_student_fees($user_id, $student) {
    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white">
            <h3 class="card-title m-0"><i class="bi bi-wallet me-2"></i>My Fees</h3>
        </div>
        <div class="card-body">
            <p>Display fee status for student ID: <?php echo esc_html($user_id); ?></p>
            <!-- Add logic to fetch and display fee details -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

//result
function render_student_result($user_id, $student) {
    global $wpdb;

    $exams_table = $wpdb->prefix . 'exams';

    // Get educational center ID
    $education_center_id = educational_center_student_id();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    // Get student_id and class_id from post meta
    $student_id = get_post_meta($student->ID, 'student_id', true); // e.g., "asa11"
    $class_id = get_post_meta($student->ID, 'class', true); // e.g., "class 11"
    if (empty($student_id) || empty($class_id)) {
        return '<div class="alert alert-warning">Student ID or class not assigned. Please contact administration.</div>';
    }

    // Fetch exams for the student's class
    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT id, name 
         FROM $exams_table 
         WHERE education_center_id = %s 
         AND class_id = %s 
         ORDER BY exam_date DESC",
        $education_center_id,
        $class_id
    ));

    // Generate nonce and REST URLs
    $nonce = wp_create_nonce('wp_rest');
    $fetch_url = rest_url('results/v1/fetch');
    $pdf_url = rest_url('results/v1/pdf');

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h3 class="card-title m-0"><i class="bi bi-file-earmark-text me-2"></i>My Results</h3>
        </div>
        <div class="card-body">
            <div class="filter mb-3">
                <select id="exam-select" class="form-select w-auto">
                    <option value="">Select an Exam</option>
                    <?php foreach ($exams as $exam) : ?>
                        <option value="<?php echo esc_attr($exam->id); ?>"><?php echo esc_html($exam->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="download-pdf" class="btn btn-primary ms-2" disabled>Download PDF</button>
            </div>
            <div class="results-table-wrapper" id="results-table-container">
                <p>Select an exam to view results.</p>
            </div>
        </div>
    </div>

    <style>
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }
        .results-table th, .results-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .results-table th {
            background-color: #28a745; /* Matches bg-success */
            color: white;
        }
        .results-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .results-table tfoot {
            font-weight: bold;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        function loadResultsTable() {
            var examId = $('#exam-select').val();
            var studentId = '<?php echo esc_js($student_id); ?>';

            if (!examId) {
                $('#results-table-container').html('<p>Select an exam to view results.</p>');
                $('#download-pdf').prop('disabled', true);
                return;
            }

            $.ajax({
                url: '<?php echo esc_js($fetch_url); ?>',
                method: 'POST',
                data: {
                    exam_id: examId,
                    student_id: studentId,
                    _wpnonce: '<?php echo esc_js($nonce); ?>'
                },
                headers: {
                    'X-WP-Nonce': '<?php echo esc_js($nonce); ?>'
                },
                beforeSend: function() {
                    $('#results-table-container').html('<p>Loading results...</p>');
                },
                success: function(response) {
                    if (response.success && response.data && response.data.html) {
                        $('#results-table-container').html(response.data.html);
                        $('#download-pdf').prop('disabled', false);
                    } else {
                        $('#results-table-container').html('<p>No results found for this exam.</p>');
                        $('#download-pdf').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    $('#results-table-container').html('<p>Error loading results: ' + (xhr.responseJSON?.message || error) + '</p>');
                    $('#download-pdf').prop('disabled', true);
                }
            });
        }

        // Handle PDF download via AJAX
        $('#download-pdf').on('click', function() {
            var examId = $('#exam-select').val();
            var studentId = '<?php echo esc_js($student_id); ?>';

            $.ajax({
                url: '<?php echo esc_js($pdf_url); ?>',
                method: 'POST',
                data: {
                    exam_id: examId,
                    student_id: studentId,
                    _wpnonce: '<?php echo esc_js($nonce); ?>'
                },
                headers: {
                    'X-WP-Nonce': '<?php echo esc_js($nonce); ?>'
                },
                xhrFields: {
                    responseType: 'blob' // Expect binary response (PDF)
                },
                beforeSend: function() {
                    $('#download-pdf').prop('disabled', true).text('Generating...');
                },
                success: function(response, status, xhr) {
                    var filename = 'marksheet_' + examId + '_' + studentId + '.pdf';
                    var blob = new Blob([response], { type: 'application/pdf' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    $('#download-pdf').prop('disabled', false).text('Download PDF');
                },
                error: function(xhr, status, error) {
                    // alert('Error generating PDF: ' + (xhr.responseJSON?.message || error));
                    alert('No Result Found');
                    $('#download-pdf').prop('disabled', false).text('Download PDF');
                }
            });
        });

        // Load results on exam selection
        $('#exam-select').on('change', loadResultsTable);
    });
    </script>
    <?php
    return ob_get_clean();
}

// Register REST endpoints
add_action('rest_api_init', function () {
    register_rest_route('results/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_student_results_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    register_rest_route('results/v1', '/pdf', array(
        'methods' => 'POST', // Changed to POST for AJAX
        'callback' => 'generate_student_results_pdf',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

function fetch_student_results_data($request) {
    global $wpdb;
    $exam_results_table = $wpdb->prefix . 'exam_results';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $params = $request->get_params();

    $education_center_id = educational_center_student_id();
    if (!$education_center_id) {
        return array('success' => false, 'message' => 'Educational center ID not found');
    }

    $exam_id = sanitize_text_field($params['exam_id'] ?? '');
    $student_id = sanitize_text_field($params['student_id'] ?? '');
    if (empty($exam_id) || empty($student_id)) {
        return array('success' => false, 'message' => 'Exam ID or Student ID missing');
    }

    $query = "
        SELECT es.subject_name, er.marks, es.max_marks 
        FROM $exam_results_table er
        INNER JOIN $exam_subjects_table es ON er.subject_id = es.id
        WHERE er.exam_id = %s 
        AND er.student_id = %s 
        AND er.education_center_id = %s";
    $results = $wpdb->get_results($wpdb->prepare($query, $exam_id, $student_id, $education_center_id));

    if ($wpdb->last_error) {
        error_log('fetch_student_results_data: SQL Error - ' . $wpdb->last_error);
        error_log('Query: ' . $wpdb->last_query);
        return array('success' => false, 'message' => 'Database error');
    }

    $total_obtained = 0;
    $total_max = 0;
    foreach ($results as $row) {
        $total_obtained += (float) $row->marks;
        $total_max += (float) $row->max_marks;
    }
    $percentage = $total_max > 0 ? round(($total_obtained / $total_max) * 100, 2) : 0;

    ob_start();
    ?>
    <table class="results-table">
        <thead>
            <tr>
                <th>Subject Name</th>
                <th>Marks Obtained</th>
                <th>Maximum Marks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)) : ?>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row->subject_name); ?></td>
                        <td><?php echo esc_html($row->marks); ?></td>
                        <td><?php echo esc_html($row->max_marks); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3">No results found</td></tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($results)) : ?>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td><?php echo esc_html($total_obtained); ?></td>
                    <td><?php echo esc_html($total_max); ?></td>
                </tr>
                <tr>
                    <td>Percentage</td>
                    <td colspan="2"><?php echo esc_html($percentage); ?>%</td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
    <?php
    $table_html = ob_get_clean();

    return array(
        'success' => true,
        'data' => array(
            'html' => $table_html
        )
    );
}

function generate_student_results_pdf($request) {
    global $wpdb;

    $params = $request->get_params();
    $education_center_id = educational_center_student_id();
    $exam_id = sanitize_text_field($params['exam_id'] ?? '');
    $student_id = sanitize_text_field($params['student_id'] ?? '');

    if (empty($exam_id) || empty($student_id) || !$education_center_id) {
        return new WP_Error('invalid_params', 'Invalid request parameters', array('status' => 400));
    }

    // Check for results first to avoid unnecessary PDF generation
    $exam_results_table = $wpdb->prefix . 'exam_results';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT es.subject_name, er.marks, es.max_marks 
         FROM $exam_results_table er
         INNER JOIN $exam_subjects_table es ON er.subject_id = es.id
         WHERE er.exam_id = %s 
         AND er.student_id = %s 
         AND er.education_center_id = %s",
        $exam_id, $student_id, $education_center_id
    ));

    if (empty($results)) {
        return new WP_Error('no_results', 'No results found for this exam', array('status' => 404));
    }

    // Fetch center data (assuming this function exists)
    $center_data = get_educational_center_data_teachers($education_center_id);
    $institute_name = $center_data['name'] ?? 'Instituto Educational Center';
    $logo_url = $center_data['logo_url'] ?? '';

    // Call the helper function if results exist
    generate_teacher_student_marksheet($exam_id, $education_center_id, $institute_name, $student_id, $logo_url, true);
}





// Placeholder Shortcodes (to be implemented as needed)
function aspire_student_communication_shortcode() {
    return '<p>Student communication section placeholder.</p>';
}

function aspire_student_notice_board_shortcode() {
    return '<p>Student notice board section placeholder.</p>';
}
?>