<?php
/*
Plugin Name: Multiple Institute Management System
Description: A plugin to manage multiple educational institutes.
Version: 1.0
Author: Aspire Dev
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_filter( 'show_admin_bar', '__return_false' );

function generate_random_meta_fields($post_id) {
    // Avoid running on autosave or if it's not a valid post type
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Ensure it's the correct custom post type
    if (get_post_type($post_id) !== 'educational-center') {
        return;
    }

    // Check if 'educational_center_id' (Educational Center ID) is empty and set it if necessary
    if (get_field('educational_center_id', $post_id) == '') {
        $eid = strtoupper(bin2hex(random_bytes(6))); // 12-character random ID
        update_field('educational_center_id', $eid, $post_id);
    }

    // Check if 'admin_id' is empty and set it if necessary
    $admin_id = get_field('admin_id', $post_id);
    if ($admin_id == '') {
        $admin_id = 'admin' . strtoupper(bin2hex(random_bytes(6))); // Prepend 'admin' to the random ID
        update_field('admin_id', $admin_id, $post_id);
    }

    // Ensure the post is being published and is a new post (triggered only on publishing)
    if (get_post_status($post_id) !== 'publish') {
        return; // Skip if the post is not published yet
    }

    // Avoid executing this for updates, only for new posts (check if '_user_created' exists)
    if (get_post_meta($post_id, '_user_created', true)) {
        return; // Skip if the user has already been created
    }

    // Check if 'mobile_number' and 'email_id' are set and valid
    $mobile_number = get_field('mobile_number', $post_id);
    $email_id = get_field('email_id', $post_id);

    if ($mobile_number && $email_id && $admin_id) {
        // Check if the username already exists
        if (!username_exists($admin_id)) {
            // Create the user with admin_id as username, mobile_number as password, and email_id as email
            $user_id = wp_create_user($admin_id, $mobile_number, $email_id);

            // Check if user was created successfully
            if (!is_wp_error($user_id)) {
                // Set the user role to 'institute_admin'
                $user = new WP_User($user_id);
                $user->set_role('institute_admin');

                // Mark the post as having created the user
                update_post_meta($post_id, '_user_created', true);
            }
        }
    }
}

// Hook the function to run when saving the post
add_action('save_post', 'generate_random_meta_fields');



//form login 
// Custom Login Authentication
// Custom Login Authentication with Error Message Display
function custom_login_processing() {
    // Initialize error message variable
    $error_message = '';

    // Check if the form has been submitted
    if (isset($_POST['submit_login'])) {
        // Get the user input (admin_id and password)
        $admin_id = sanitize_text_field($_POST['admin_id']);
        $password = sanitize_text_field($_POST['password']);

        // Check if the admin ID starts with 'admin' (for Institute Admin)
        if (strpos($admin_id, 'admin') === 0) {  // If the admin ID starts with 'admin'
            // Attempt to find the user by admin ID (login)
            $user = get_user_by('login', $admin_id);
            
            // If user exists and password matches
            if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
                // Set authentication cookie
                wp_set_auth_cookie($user->ID);
                
                // Redirect to the Institute Admin dashboard if the user is an Institute Admin
                if (in_array('institute_admin', (array) $user->roles)) {
                    wp_redirect(home_url('/institute-dashboard/'));  // Redirect to Institute Admin Dashboard
                    exit;
                }
            } else {
                // Invalid login credentials for Institute Admin
                $error_message = 'Invalid Admin ID or Password. Please try again.';
            }
        } else {
            // If the admin_id doesn't start with 'admin_' (for Institute Admin)
            $error_message = 'Admin ID should start with "admin_". Please try again.';
        }
    }

    // Pass error message to the page to display below the form
    if (!empty($error_message)) {
        echo "<script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
                    var errorMessageElement = document.getElementById('login-error-message');
                    if (errorMessageElement) {
                        errorMessageElement.innerHTML = '$error_message';
                        errorMessageElement.style.display = 'block';
                    }
                });
              </script>";
    }
}

// Hook the custom login processing to run on the template_redirect hook
add_action('template_redirect', 'custom_login_processing');

function restrict_dashboard_access() {
    if (is_page('institute-dashboard') || is_page('edit-class-section')|| is_page('delete-class-section')|| is_page('attendance-management')|| is_page('attendance-entry-form')) {
        
        // Check if the user is logged in
        if (!is_user_logged_in()) {
            wp_redirect(home_url('/login'));  // Redirect to login page
            exit;
        }

        // Get the current logged-in user
        $user = wp_get_current_user();

        // Check if the user has the 'institute_admin' or 'Administrator' role
        if (!in_array('institute_admin', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            wp_redirect(home_url('/'));  // Redirect to homepage or any other page
            exit;
        }
    }
}

add_action('template_redirect', 'restrict_dashboard_access');


require_once plugin_dir_path(__FILE__) . 'dashboard.php';

// Add admin menu for managing classes and sections
add_action('admin_menu', 'class_section_admin_menu');

function class_section_admin_menu() {
    add_menu_page(
        'Class Section Manager',
        'Class Sections',
        'manage_options',
        'class-section-manager',
        'class_section_admin_page',
        'dashicons-list-view',
        6
    );
}

// Admin page to add new classes and sections
function class_section_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    // Handle form submission
    if (isset($_POST['submit_class_section'])) {
        $education_center_id = sanitize_text_field($_POST['education_center_id']);
        $class_name = sanitize_text_field($_POST['class_name']);
        $sections = sanitize_text_field($_POST['sections']);

        if (!empty($class_name) && !empty($sections)) {
            // Insert new class and sections into the table
            $wpdb->insert(
                $table_name,
                array(
                    'education_center_id' => $education_center_id,
                    'class_name' => $class_name,
                    'sections' => $sections,
                )
            );
            echo '<div class="notice notice-success"><p>Class and sections added successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Please fill in all fields.</p></div>';
        }
    }

    // Fetch all classes and sections
    $class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    ?>
    <div class="wrap">
        <h1>Class Section Manager</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="education_center_id">Education Center ID</label></th>
                    <td><input name="education_center_id" type="text" id="education_center_id" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="class_name">Class Name</label></th>
                    <td><input name="class_name" type="text" id="class_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="sections">Sections (comma-separated)</label></th>
                    <td><input name="sections" type="text" id="sections" class="regular-text" required></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_class_section" class="button-primary" value="Add Class & Sections">
            </p>
        </form>

        <h2>Existing Classes and Sections</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Education Center ID</th>
                    <th>Class Name</th>
                    <th>Sections</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($class_sections as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row['education_center_id']); ?></td>
                        <td><?php echo esc_html($row['class_name']); ?></td>
                        <td><?php echo esc_html($row['sections']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}


// Initialize plugin
function mim_initialize_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        education_center_id varchar(255) NOT NULL,
        class_name varchar(255) NOT NULL,
        sections text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($count == 0) {
        $wpdb->insert($table_name, array('education_center_id' => 'AFC46B9CEE17', 'class_name' => 'Class 1', 'sections' => 'a,b,c'));
        $wpdb->insert($table_name, array('education_center_id' => 'AFC46B9CEE17', 'class_name' => 'Class 2', 'sections' => 'x,y,z'));
    }
}
add_action('plugins_loaded', 'mim_initialize_plugin');

// Get educational center ID
function get_educational_center_data() {
    global $wpdb;
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ));

    if (empty($educational_center)) {
        return "<p>No Educational Center found for this Admin ID.</p>";
    }

    return get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
}
// Helper function to get teacher ID
function get_current_teacher_id() {
    $user = wp_get_current_user();
    if (!$user->exists()) {
        return false;
    }

    // Check user role and return appropriate value
    if (in_array('administrator', $user->roles)) {
        return $user->user_email; // Return email for administrator
    }

    if (in_array('institute_admin', $user->roles)) {
        return $user->user_email; // Return email for institute admin
    }

    if (in_array('teacher', $user->roles)) {
        return get_user_meta($user->ID, 'teacher_id', true) ?: $user->ID; // Return teacher_id for teacher
    }

    return false; // Return false if no matching role
}


// Handle student addition
function handle_add_student_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
        $educational_center_id = get_educational_center_data();

        if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
            echo $educational_center_id;
            return;
        }

        $gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
        $attachment_id = null;

        if (isset($_FILES['student_profile_photo']) && $_FILES['student_profile_photo']['error'] === 0) {
            $file = $_FILES['student_profile_photo'];
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

        $admission_number = sanitize_text_field($_POST['admission_number']);
        $student_name = sanitize_text_field($_POST['student_name']);
        $student_email = sanitize_email($_POST['student_email']);
        $student_id = sanitize_text_field($_POST['student_id']);
        $class_name = sanitize_text_field($_POST['class_name']);
        $section = sanitize_text_field($_POST['section']);
        $religion = sanitize_text_field($_POST['religion']);
        $blood_group = sanitize_text_field($_POST['blood_group']);
        $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $height = isset($_POST['height']) ? sanitize_text_field($_POST['height']) : '';
        $weight = isset($_POST['weight']) ? sanitize_text_field($_POST['weight']) : '';
        $current_address = isset($_POST['current_address']) ? sanitize_textarea_field($_POST['current_address']) : '';
        $permanent_address = isset($_POST['permanent_address']) ? sanitize_textarea_field($_POST['permanent_address']) : '';
        $roll_number = isset($_POST['roll_number']) ? sanitize_text_field($_POST['roll_number']) : '';
        $admission_date = isset($_POST['admission_date']) ? sanitize_text_field($_POST['admission_date']) : '';

        $student_post_id = wp_insert_post(array(
            'post_title' => $student_name,
            'post_type' => 'students',
            'post_status' => 'publish',
            'meta_input' => array(
                'admission_number' => $admission_number,
                'student_email' => $student_email,
                'educational_center_id' => $educational_center_id,
                'student_id' => $student_id,
                'phone_number' => $phone_number,
                'class' => $class_name,
                'section' => $section,
                'religion' => $religion,
                'blood_group' => $blood_group,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'height' => $height,
                'weight' => $weight,
                'current_address' => $current_address,
                'permanent_address' => $permanent_address,
                'roll_number' => $roll_number,
                'admission_date' => $admission_date,
            ),
        ));

        if ($student_post_id && $attachment_id) {
            update_field('field_67ab1bd7978ff', $attachment_id, $student_post_id);
            wp_redirect(home_url('/institute-dashboard/students'));
            exit;
        } elseif ($student_post_id) {
            wp_redirect(home_url('/institute-dashboard/students'));
            exit;
        } else {
            echo '<p class="error-message">Error adding student.</p>';
        }
    }


   // Handle student editing
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $student_id = intval($_POST['student_id']);
    $student_name = sanitize_text_field($_POST['student_name']);
    $student_email = sanitize_email($_POST['student_email']);
    $class_name = sanitize_text_field($_POST['class_name']);
    $section = sanitize_text_field($_POST['section']);
    $admission_number = sanitize_text_field($_POST['admission_number']);
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $roll_number = sanitize_text_field($_POST['roll_number']);
    $admission_date = sanitize_text_field($_POST['admission_date']);
    $gender = sanitize_text_field($_POST['gender']);
    $religion = sanitize_text_field($_POST['religion']);
    $blood_group = sanitize_text_field($_POST['blood_group']);
    $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
    $height = sanitize_text_field($_POST['height']);
    $weight = sanitize_text_field($_POST['weight']);
    $current_address = sanitize_textarea_field($_POST['current_address']);
    $permanent_address = sanitize_textarea_field($_POST['permanent_address']);

    // Handle profile picture upload
    if (!empty($_FILES['student_profile_photo']['name'])) {
        $file = $_FILES['student_profile_photo'];
        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['file'])) {
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $upload['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ), $upload['file']);

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Update the profile picture meta field
            update_post_meta($student_id, 'student_profile_photo', $attachment_id);
        }
    }

    // Update the student post
    wp_update_post(array(
        'ID'           => $student_id,
        'post_title'   => $student_name,
    ));

    // Update meta fields
    update_post_meta($student_id, 'student_email', $student_email);
    update_post_meta($student_id, 'class', $class_name);
    update_post_meta($student_id, 'section', $section);
    update_post_meta($student_id, 'admission_number', $admission_number);
    update_post_meta($student_id, 'phone_number', $phone_number);
    update_post_meta($student_id, 'roll_number', $roll_number);
    update_post_meta($student_id, 'admission_date', $admission_date);
    update_post_meta($student_id, 'gender', $gender);
    update_post_meta($student_id, 'religion', $religion);
    update_post_meta($student_id, 'blood_group', $blood_group);
    update_post_meta($student_id, 'date_of_birth', $date_of_birth);
    update_post_meta($student_id, 'height', $height);
    update_post_meta($student_id, 'weight', $weight);
    update_post_meta($student_id, 'current_address', $current_address);
    update_post_meta($student_id, 'permanent_address', $permanent_address);

    // Redirect to the same page
    wp_redirect(home_url('/institute-dashboard/edit-students'));
    exit;
}

}
add_action('template_redirect', 'handle_add_student_submission');






include plugin_dir_path(__FILE__) . 'assets/edit-classes.php';
include plugin_dir_path(__FILE__) . 'assets/delete-class.php';
include plugin_dir_path(__FILE__) . 'assets/attendance/attendance-management.php';
include plugin_dir_path(__FILE__) . 'assets/attendance/insert-attendance.php';
include plugin_dir_path(__FILE__) . 'assets/attendance/edit-attendance.php';
include plugin_dir_path(__FILE__) . 'assets/attendance/bulk-import.php';
include plugin_dir_path(__FILE__) . 'assets/attendance/export-attendance.php';
include plugin_dir_path(__FILE__) . 'assets/add-students.php';
include plugin_dir_path(__FILE__) . 'assets/students.php';
include plugin_dir_path(__FILE__) . 'assets/edit-students.php';
include plugin_dir_path(__FILE__) . 'assets/classes.php';
include plugin_dir_path(__FILE__) . 'interface.php';
include plugin_dir_path(__FILE__) . 'subjects_interface.php';

function create_custom_user_roles() {
    // Check if 'student' role exists, if not create it
    if ( !get_role( 'student' ) ) {
        add_role( 'student', 'Student', array(
            'read' => true, // Allow the user to read posts and pages
            'edit_posts' => false, // Disable editing posts
            'publish_posts' => false, // Disable publishing posts
            'edit_pages' => false, // Disable editing pages
            'manage_options' => false, // Disable admin options
        ));
    }

    // Check if 'parent' role exists, if not create it
    if ( !get_role( 'parent' ) ) {
        add_role( 'parent', 'Parent', array(
            'read' => true,
            'edit_posts' => false,
            'publish_posts' => false,
            'edit_pages' => false,
            'manage_options' => false,
        ));
    }

    // Check if 'teacher' role exists, if not create it
    if ( !get_role( 'teacher' ) ) {
        add_role( 'teacher', 'Teacher', array(
            'read' => true,
            'edit_posts' => true,
            'publish_posts' => false,
            'edit_pages' => false,
            'manage_options' => false,
        ));
    }

    // Check if 'accountant' role exists, if not create it
    if ( !get_role( 'accountant' ) ) {
        add_role( 'accountant', 'Accountant', array(
            'read' => true,
            'edit_posts' => false,
            'publish_posts' => false,
            'edit_pages' => false,
            'manage_options' => false,
        ));
    }

    // Check if 'institute_admin' role exists, if not create it
    if ( !get_role( 'institute_admin' ) ) {
        add_role( 'institute_admin', 'Institute Admin', array(
            'read' => true,
            'edit_posts' => true,
            'publish_posts' => true,
            'edit_pages' => true,
            'manage_options' => true, // Grant access to manage admin options
        ));
    }
}
add_action( 'init', 'create_custom_user_roles' );





function institute_dashboard_scripts() {
    wp_enqueue_style('institute-dashboard-style', plugin_dir_url(__FILE__) . '/css/style.css');
    wp_enqueue_script('institute-dashboard-script', plugin_dir_url(__FILE__) . '/js/js.js', array('jquery'), null, true);
   
}
add_action('wp_enqueue_scripts', 'institute_dashboard_scripts');


// function restrict_edit_class_section_access() {
//     if (is_page('edit-class-section')) {
        
//         // Check if the user is logged in
//         if (!is_user_logged_in()) {
//             wp_redirect(home_url('/login'));  // Redirect to login page
//             exit;
//         }

//         // Get the current logged-in user
//         $user = wp_get_current_user();

//         // Check if the user has the 'institute_admin' or 'Administrator' role
//         if (!in_array('institute_admin', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
//             wp_redirect(home_url('/'));  // Redirect to homepage or any other page
//             exit;
//         }
//     }
// }

// add_action('template_redirect', 'restrict_edit_class_section_access');
// Hook to initialize the plugin
// add_action('init', 'custom_institute_dashboard_init');

// function custom_institute_dashboard_init() {
//     // Register custom roles
//     add_role('institute_admin', 'Institute Admin', array(
//         'read' => true,               // Basic permission to read
//         'edit_posts' => false,        // Disable editing posts
//         'edit_institute_data' => true // Custom capability to edit institute data
//     ));

//     // Add custom capabilities for 'institute_admin'
//     $role = get_role('institute_admin');
//     if ($role) {
//         $role->add_cap('edit_institute_data');
//     }
// }

// Register custom post types for Institutes (Now using 'educational-center' instead of 'institute')
// add_action('init', 'custom_educational_center_post_type');
// function custom_educational_center_post_type() {
//     register_post_type('educational-center', array(
//         'labels' => array(
//             'name' => __('Educational Centers'),
//             'singular_name' => __('Educational Center'),
//         ),
//         'public' => true,
//         'supports' => array('title', 'editor', 'thumbnail'),
//         'show_in_rest' => true,  // Enable REST API for editing
//     ));
// }

// // Login and redirect user to their dashboard
// add_action('wp_login', 'custom_redirect_on_login', 10, 2);
// function custom_redirect_on_login($user_login, $user) {
//     if (in_array('institute_admin', (array) $user->roles)) {
//         // Redirect to custom dashboard for 'institute_admin'
//         wp_redirect(home_url('/institute-dashboard/'));
//         exit;
//     }
// }

// // Restrict access to the dashboard based on the user's associated educational center
// function restrict_dashboard_access() {
//     if (is_page('institute-dashboard') && is_user_logged_in()) {
//         $current_user = wp_get_current_user();
//         $educational_center_id = get_user_meta($current_user->ID, 'educational_center_id', true);  // Custom field that holds the educational center ID

//         if (empty($educational_center_id)) {
//             // If no educational center is associated with the user, redirect them to the homepage or a login page
//             wp_redirect(home_url());
//             exit;
//         }

//         // Now query the educational center's custom post type and verify if they have access to that specific post
//         $educational_center_post = get_posts(array(
//             'post_type' => 'educational-center',
//             'meta_key' => 'educational_center_id',  // Custom ACF field to link users to educational centers
//             'meta_value' => $educational_center_id,
//             'posts_per_page' => 1
//         ));

//         if (empty($educational_center_post)) {
//             // If no matching educational center post is found, deny access
//             wp_redirect(home_url());
//             exit;
//         }
//     }
// }
// add_action('template_redirect', 'restrict_dashboard_access');

// // Add a custom admin menu page for each educational center
// add_action('admin_menu', 'custom_educational_center_dashboard_menu');
// function custom_educational_center_dashboard_menu() {
//     add_menu_page(
//         'Educational Center Dashboard',  // Page Title
//         'Educational Center Dashboard',  // Menu Title
//         'edit_institute_data',           // Capability
//         'educational-center-dashboard',  // Menu Slug
//         'custom_educational_center_dashboard_page',  // Function to display page
//         'dashicons-building',            // Icon
//         6                                // Position
//     );
// }

// function custom_educational_center_dashboard_page() {
//     if (!is_user_logged_in()) {
//         echo "You must be logged in to access this page.";
//         return;
//     }
    
//     $current_user = wp_get_current_user();
//     $educational_center_id = get_user_meta($current_user->ID, 'educational_center_id', true);

//     // Display the dashboard form for managing the educational center's data
//     echo '<div class="wrap"><h1>Welcome to Your Educational Center Dashboard</h1>';

//     // Fetch the educational center post based on user-assigned educational_center_id
//     $educational_center = get_posts(array(
//         'post_type' => 'educational-center',
//         'meta_key' => 'educational_center_id',
//         'meta_value' => $educational_center_id,
//         'posts_per_page' => 1
//     ));

//     if ($educational_center) {
//         $educational_center_post = $educational_center[0];
//         $center_name = get_field('center_name', $educational_center_post->ID);
//         $center_location = get_field('center_location', $educational_center_post->ID);

//         // Show the form with current values
//         echo '<form method="post" action="">';
//         echo '<label for="center_name">Educational Center Name:</label>';
//         echo '<input type="text" name="center_name" value="' . esc_attr($center_name) . '" />';
//         echo '<label for="center_location">Educational Center Location:</label>';
//         echo '<input type="text" name="center_location" value="' . esc_attr($center_location) . '" />';
//         echo '<input type="submit" name="submit_educational_center_data" value="Save Changes" />';
//         echo '</form>';

//         // Handle form submission
//         if (isset($_POST['submit_educational_center_data'])) {
//             update_field('center_name', sanitize_text_field($_POST['center_name']), $educational_center_post->ID);
//             update_field('center_location', sanitize_text_field($_POST['center_location']), $educational_center_post->ID);
//             echo '<p>Educational Center data updated successfully!</p>';
//         }
//     } else {
//         echo "<p>Educational Center not found.</p>";
//     }
//     echo '</div>';
// }
