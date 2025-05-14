<?php
/*
Plugin Name: Multiple Institute Management System
Description: A plugin to manage multiple educational institutes.
Version: 2.0
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


function custom_login_processing($user_type_configs = []) {
    // Default configurations for user types
    $default_configs = [
        'institute_admin' => [
            'prefix' => 'admin',
            'role' => 'institute_admin',
            'redirect' => '/institute-dashboard/',
            'validate_edu_center' => false,
        ],
        'teacher' => [
            'prefix' => 'TEA-',
            'role' => 'teacher',
            'redirect' => '/teacher-dashboard/',
            'validate_edu_center' => true,
            'post_type' => 'teacher',
            'meta_key' => 'teacher_id',
        ],
        'student' => [
            'prefix' => 'STU-',
            'role' => 'student',
            'redirect' => '/student-dashboard/',
            'validate_edu_center' => true,
            'post_type' => 'students',
            'meta_key' => 'student_id',
        ],
        'parent' => [
            'prefix' => 'PAR-',
            'role' => 'parent',
            'redirect' => '/parent-dashboard/',
            'validate_edu_center' => true,
            'post_type' => 'parent',
            'meta_key' => 'parent_id',
        ],
        'su_p' => [
            'prefix' => 'su_p-',
            'role' => 'su_p',
            'redirect' => '/su_p-dashboard/',
            'validate_edu_center' => false,
        ],
    ];

    // Validate and merge configurations
    $user_type_configs = is_array($user_type_configs) ? $user_type_configs : [];
    $configs = array_merge($default_configs, $user_type_configs);
    
    // Return early if not a login attempt
    if (!isset($_POST['submit_login'])) {
        return;
    }

    try {
        // Sanitize and validate inputs
        $user_id = sanitize_text_field($_POST['user_id'] ?? '');
        $password = $_POST['password'] ?? ''; // Don't sanitize password here to preserve special characters
        $educational_center_id = sanitize_text_field($_POST['educational_center_id'] ?? '');

        // Prevent empty submissions
        if (empty($user_id) || empty($password)) {
            throw new Exception('User ID and password are required.');
        }

        // Determine user type
        $matched_type = null;
        foreach ($configs as $type => $config) {
            if (strpos($user_id, $config['prefix']) === 0) {
                $matched_type = $type;
                break;
            }
        }

        if (!$matched_type) {
            throw new Exception('Invalid ID format. Please use a valid prefix.');
        }

        $config = $configs[$matched_type];

        // Validate educational center ID when required
        if ($config['validate_edu_center']) {
            if (empty($educational_center_id)) {
                throw new Exception('Educational Center ID is required.');
            }

            // Rate limiting to prevent brute force
            $login_attempts = get_transient('login_attempts_' . $user_id);
            if ($login_attempts && $login_attempts >= 5) {
                throw new Exception('Too many login attempts. Please try again later.');
            }
        }

        // Get user and verify credentials
        $user = get_user_by('login', $user_id);
        if (!$user || !wp_check_password($password, $user->user_pass, $user->ID)) {
            // Increment login attempts on failure
            if ($config['validate_edu_center']) {
                $attempts = ($login_attempts ?: 0) + 1;
                set_transient('login_attempts_' . $user_id, $attempts, 15 * MINUTE_IN_SECONDS);
            }
            throw new Exception('Invalid ID or Password.');
        }

        // Verify user role
        if (!in_array($config['role'], (array) $user->roles)) {
            throw new Exception('Insufficient permissions for this role.');
        }

        // Validate educational center for applicable roles
        if ($config['validate_edu_center']) {
            $stored_edu_center_id = get_user_meta($user->ID, 'educational_center_id', true);
            
            if (empty($stored_edu_center_id)) {
                throw new Exception('No Educational Center ID associated with this account.');
            }
            
            if ($stored_edu_center_id !== $educational_center_id) {
                throw new Exception('Invalid Educational Center ID.');
            }

            // Validate against custom post type
            $args = [
                'post_type' => $config['post_type'],
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => $config['meta_key'],
                        'value' => $user_id,
                        'compare' => '=',
                    ],
                    [
                        'key' => 'educational_center_id',
                        'value' => $stored_edu_center_id,
                        'compare' => '=',
                    ],
                ],
            ];

            $query = new WP_Query($args);
            if (!$query->have_posts()) {
                throw new Exception(ucfirst($config['role']) . ' ID and Educational Center ID do not match any records.');
            }
        }

        // Successful login
        wp_set_auth_cookie($user->ID, isset($_POST['remember']));
        delete_transient('login_attempts_' . $user_id);
        
        // Log successful login
        error_log("Successful login: User ID $user_id, Role: {$config['role']}");
        
        wp_safe_redirect(home_url($config['redirect']));
        exit;

    } catch (Exception $e) {
        $error_message = esc_js($e->getMessage());
        
        // Output error message
        echo "<script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                var errorElement = document.getElementById('login-error-message');
                if (errorElement) {
                    errorElement.innerHTML = '$error_message';
                    errorElement.style.display = 'block';
                }
            });
        </script>";
    }
}
add_action('template_redirect', 'custom_login_processing');

function get_secure_logout_url_by_role() {
    $user = wp_get_current_user();
    $nonce = wp_create_nonce('logout_nonce');

    if (in_array('teacher', (array) $user->roles)) {
        $redirect = home_url('/teacher-login/');
    } elseif (in_array('institute_admin', (array) $user->roles)) {
        $redirect = home_url('/institute-login/');
    }
    // elseif (in_array('parent', (array) $user->roles)) {
    //     $redirect = home_url('/login/');
    // }
     else {
        $redirect = home_url('/login/');
    }

    return esc_url(add_query_arg(array('action' => 'logout', 'logout_nonce' => $nonce), $redirect));
}

function custom_logout_processing($redirect_to = '') {
    $default_redirect = home_url('/login/');
    $redirect_url = !empty($redirect_to) ? $redirect_to : $default_redirect;

    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        if (!isset($_GET['logout_nonce']) || !wp_verify_nonce($_GET['logout_nonce'], 'logout_nonce')) {
            wp_die('Invalid logout request.');
        }

        wp_logout();
        wp_clear_auth_cookie();
        wp_redirect($redirect_url);
        exit;
    }
}

add_action('template_redirect', function() {
    custom_logout_processing();
});


// function restrict_dashboard_access() {
//     if (is_page('institute-dashboard') || is_page('edit-class-section')|| is_page('delete-class-section')|| is_page('attendance-management')|| is_page('attendance-entry-form')) {
        
//         // Check if the user is logged in
//         if (!is_user_logged_in()) {
//             // wp_redirect(home_url('/login'));  // Redirect to login page
//             // exit;
//             return false;
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

// add_action('template_redirect', 'restrict_dashboard_access');


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
    $current_url = home_url(add_query_arg([]));
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg)(\?.*)?$/i', $current_url)) {
        error_log("get_educational_center_data() called in invalid context (asset): $current_url");
        return null;
    }
    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ));

    error_log("inside edu func check");
    if (empty($educational_center)) {
        error_log("No Educational Center found, preparing detailed error");

        // Get call stack details using debug_backtrace
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2); // Limit to 2 levels for clarity
        $caller = isset($backtrace[1]) ? $backtrace[1] : array('function' => 'unknown', 'file' => 'unknown', 'line' => 'unknown');
        $calling_function = $caller['function'] ?? 'unknown';
        $calling_file = $caller['file'] ?? 'unknown';
        $calling_line = $caller['line'] ?? 'unknown';

        // Build detailed error message
        $error_message = "<div style='border: 2px solid red; padding: 20px; margin: 20px; background: #ffe6e6;'>";
        $error_message .= "<h2>Debug Error: No Educational Center Found</h2>";
        $error_message .= "<p><strong>Current User:</strong> " . (is_user_logged_in() ? wp_get_current_user()->user_login : "Not logged in") . "</p>";
        $error_message .= "<p><strong>Admin ID:</strong> " . esc_html($admin_id) . "</p>";
        $error_message .= "<p><strong>Post Type Queried:</strong> educational-center</p>";
        $error_message .= "<p><strong>Meta Key:</strong> admin_id</p>";
        $error_message .= "<p><strong>Meta Value Expected:</strong> " . esc_html($admin_id) . "</p>";
        $error_message .= "<p><strong>Posts Returned:</strong> " . (is_array($educational_center) ? count($educational_center) : "Not an array") . "</p>";
        $error_message .= "<p><strong>Raw Query Result:</strong> <pre>" . print_r($educational_center, true) . "</pre></p>";
        $error_message .= "<p><strong>Current Page URL:</strong> " . esc_url(home_url(add_query_arg([]))) . "</p>";
        $error_message .= "<p><strong>Called From:</strong> Function: " . esc_html($calling_function) . " in File: " . esc_html(basename($calling_file)) . " at Line: " . esc_html($calling_line) . "</p>";
        $error_message .= "<p><strong>Possible Issues:</strong></p>";
        $error_message .= "<ul>";
        $error_message .= "<li>Is the user logged in? Check if <code>is_user_logged_in()</code> is false.</li>";
        $error_message .= "<li>Does an 'educational-center' post exist with meta 'admin_id' = '$admin_id'?</li>";
        $error_message .= "<li>Is this code running on the login page or unexpectedly?</li>";
        $error_message .= "</ul>";
        $error_message .= "<p>Please <a href='" . esc_url(home_url('/login')) . "'>log in again</a> or contact support with this info.</p>";
        $error_message .= "</div>";

        // Enhanced error logging
        error_log("Educational Center Error for user: $admin_id");
        error_log("User Logged In: " . (is_user_logged_in() ? 'Yes' : 'No'));
        error_log("Admin ID: $admin_id");
        error_log("Posts Returned: " . (is_array($educational_center) ? count($educational_center) : "Not an array"));
        error_log("Raw Query Result: " . print_r($educational_center, true));
        error_log("Current URL: " . home_url(add_query_arg([])));
        error_log("Called From: Function: $calling_function in File: " . basename($calling_file) . " at Line: $calling_line");

        return $error_message;
    }

    error_log("inside edu func check2");
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

    // if (in_array('teacher', $user->roles)) {
    //     return get_user_meta($user->ID, 'teacher_id', true) ?: $user->ID; // Return teacher_id for teacher
    // }

    return false; // Return false if no matching role
}


function generate_unique_id($wpdb, $type, $entity_name, $prefix, $field_name, $context = [], $max_attempts = 5) {
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        // Generate ID: prefix + 8-digit timestamp + separator + 2 random chars
        $time_part = substr((string) time(), -8); // Last 8 digits of Unix timestamp (e.g., "64627451")
        $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2)); // 2 random chars (e.g., "AB")
        $id = $prefix . $time_part . $random_part; // e.g., "ITEM-64627451-AB"

        if ($type === 'table') {
            $where_conditions = ["$field_name = %s"];
            $where_values = [$id];

            if (!empty($context)) {
                foreach ($context as $key => $value) {
                    $where_conditions[] = "$key = %s";
                    $where_values[] = $value;
                }
            }

            $query = "SELECT COUNT(*) FROM " . esc_sql($entity_name) . " WHERE " . implode(' AND ', $where_conditions);
            error_log("Generated Query: " . $wpdb->prepare($query, $where_values));
            $exists = $wpdb->get_var($wpdb->prepare($query, $where_values));

            if ($exists === null) {
                error_log("SQL Error: " . $wpdb->last_error);
                return new WP_Error('db_error', 'Database query failed: ' . $wpdb->last_error);
            }

            if ($exists == 0) {
                return $id;
            }
        } elseif ($type === 'post_type') {
            $args = [
                'post_type'      => $entity_name,
                'post_status'    => 'any',
                'meta_query'     => [
                    [
                        'key'     => $field_name,
                        'value'   => $id,
                        'compare' => '=',
                    ],
                ],
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ];

            if (!empty($context)) {
                foreach ($context as $key => $value) {
                    $args['meta_query'][] = [
                        'key'     => $key,
                        'value'   => $value,
                        'compare' => '=',
                    ];
                }
            }

            $query = new WP_Query($args);
            if ($query->post_count == 0) {
                return $id;
            }
        } else {
            return new WP_Error('invalid_type', 'Invalid type specified.');
        }

        usleep(10000); // 10ms delay between attempts
    }

    return new WP_Error('id_generation_failed', "Unable to generate a unique ID after $max_attempts attempts.");
}

function get_unique_id_for_role($role_type, $center_id = '') {
    global $wpdb;

    $configs = [
        'educational-center' => [
            'type'        => 'post_type',
            'entity_name' => 'educational-center',
            'prefix'      => 'EDU-',
            'field_name'  => 'educational_center_id',
        ],
        'students' => [
            'type'        => 'post_type',
            'entity_name' => 'students',
            'prefix'      => 'STU-',
            'field_name'  => 'student_id',
        ],
        'inventory' => [
            'type'        => 'table',
            'entity_name' => $wpdb->prefix . 'inventory',
            'prefix'      => 'ITEM-',
            'field_name'  => 'item_id', 
        ],
        'parents' => [
            'type'        => 'post_type',
            'entity_name' => 'parent',
            'prefix'      => 'PAR-',
            'field_name'  => 'parent_id', 
        ],
        'book' => [
            'type'        => 'table',
            'entity_name' => $wpdb->prefix . 'library',
            'prefix'      => 'BOOK-',
            'field_name'  => 'book_id',
        ],
        'institute_admins' => [
            'type'        => 'table',
            'entity_name' => $wpdb->prefix . 'institute_admins',
            'prefix'      => 'admin-',
            'field_name'  => 'institute_admin_id',
        ],
    ];

    if (!isset($configs[$role_type])) {
        return new WP_Error('invalid_role', "Unknown role type: $role_type");
    }

    $config = $configs[$role_type];
    $context = !empty($center_id) ? ['education_center_id' => $center_id] : [];

    error_log("Calling generate_unique_id with: type={$config['type']}, entity_name={$config['entity_name']}, prefix={$config['prefix']}, field_name={$config['field_name']}, context=" . print_r($context, true));

    return generate_unique_id(
        $wpdb,
        $config['type'],
        $config['entity_name'],
        $config['prefix'],
        $config['field_name'],
        $context
    );
}

function generate_unique_staff_id($wpdb, $table_name, $education_center_id) {
    $max_attempts = 5;
    $prefix = 'STF-';
    $id_length = 12;

    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        $time_part = substr(str_replace('.', '', microtime(true)), -10);
        $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
        $staff_id = $prefix . $time_part . $random_part;

        if (strlen($staff_id) > 20) {
            $staff_id = substr($staff_id, 0, 20);
        }

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE staff_id = %s AND education_center_id = %s",
            $staff_id, $education_center_id
        ));

        if ($exists == 0) {
            return $staff_id;
        }
        usleep(10000);
    }
    return new WP_Error('staff_id_generation_failed', 'Unable to generate a unique Staff ID after multiple attempts.');
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
            'post_title' => $student_id,
            'post_type' => 'students',
            'post_status' => 'publish',
            'meta_input' => array(
                'admission_number' => $admission_number,
                'student_email' => $student_email,
                'educational_center_id' => $educational_center_id,
                'student_name' => $student_name,
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

        // if ($student_post_id && $attachment_id) {
        //     update_field('field_67ab1bd7978ff', $attachment_id, $student_post_id);
        //     wp_redirect(home_url('/institute-dashboard/students'));
        //     exit;
        // } elseif ($student_post_id) {
        //     wp_redirect(home_url('/institute-dashboard/students'));
        //     exit;
        // } else {
        //     echo '<p class="error-message">Error adding student.</p>';
        // }
        if ($student_post_id) {
            // Handle attachment if it exists
            if ($attachment_id) {
                update_field('field_67ab1bd7978ff', $attachment_id, $student_post_id); // ACF field key for teacher_profile_photo
            }

   // Call the reusable registration function
   $role='student';
   register_user_with_role($student_id, $student_email, $student_name, $phone_number, $role, $educational_center_id);

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


function register_user_with_role($user_id, $email, $name, $phone_number, $role, $educational_center_id) {
    // Ensure the user does not already exist and required fields are present
    if (!username_exists($user_id) && $email && $name && $educational_center_id) {
        // Generate password if phone number isn't provided
        $password = $phone_number ?: wp_generate_password(12, true);

        // Split the name into first and last names
        $name_parts = explode(' ', trim($name));
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';

        // Create the user
        $created_user_id = wp_create_user($user_id, $password, $email);

        // Check if the user was successfully created
        if (!is_wp_error($created_user_id)) {
            // Update user info (first name, last name, display name)
            wp_update_user(array(
                'ID' => $created_user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $name,
            ));

            // Set user role
            $user = new WP_User($created_user_id);
            $user->set_role($role);

            // Store educational_center_id in user meta
            $edu_center_stored = update_user_meta($created_user_id, 'educational_center_id', $educational_center_id);
            error_log("User Registration: User ID $created_user_id, Role $role, Edu Center ID $educational_center_id, Stored: " . ($edu_center_stored ? 'Yes' : 'No'));

            // Send welcome email with login credentials
            $home_url = home_url();
            $message = "Welcome, $name!\n\nYour username: $user_id\nYour password: $password\n\nLogin at: $home_url/login/";
            wp_mail($email, 'Your Account Credentials', $message);

            // Redirect after successful registration
            wp_redirect($_SERVER['REQUEST_URI']);
                        return;
        } else {
            echo '<p class="error-message">Error creating user: ' . $created_user_id->get_error_message() . '</p>';
        }
    } elseif (username_exists($user_id)) {
        echo '<p class="error-message">A user with this ID already exists.</p>';
    }
}


function handle_add_teacher_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
        $educational_center_id = get_educational_center_data();

        if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
            echo $educational_center_id;
            return;
        }

        $gender = isset($_POST['teacher_gender']) ? sanitize_text_field($_POST['teacher_gender']) : '';
        $attachment_id = null;

        if (isset($_FILES['teacher_profile_photo']) && $_FILES['teacher_profile_photo']['error'] === 0) {
            $file = $_FILES['teacher_profile_photo'];
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

        $teacher_id = sanitize_text_field($_POST['teacher_id']);
        $teacher_name = sanitize_text_field($_POST['teacher_name']);
        $teacher_email = sanitize_email($_POST['teacher_email']);
        $teacher_phone_number = sanitize_text_field($_POST['teacher_phone_number']);
        $teacher_roll_number = sanitize_text_field($_POST['teacher_roll_number']);
        $teacher_admission_date = sanitize_text_field($_POST['teacher_admission_date']);
        $teacher_religion = sanitize_text_field($_POST['teacher_religion']);
        $teacher_blood_group = sanitize_text_field($_POST['teacher_blood_group']);
        $teacher_date_of_birth = sanitize_text_field($_POST['teacher_date_of_birth']);
        $teacher_height = isset($_POST['teacher_height']) ? sanitize_text_field($_POST['teacher_height']) : '';
        $teacher_weight = isset($_POST['teacher_weight']) ? sanitize_text_field($_POST['teacher_weight']) : '';
        $teacher_current_address = isset($_POST['teacher_current_address']) ? sanitize_textarea_field($_POST['teacher_current_address']) : '';
        $teacher_permanent_address = isset($_POST['teacher_permanent_address']) ? sanitize_textarea_field($_POST['teacher_permanent_address']) : '';

        $teacher_post_id = wp_insert_post(array(
            'post_title' => $teacher_id,
            'post_type' => 'teacher', // Assumes a 'teacher' custom post type
            'post_status' => 'publish',
            'meta_input' => array(
                'teacher_name' => $teacher_name,
                'teacher_email' => $teacher_email,
                'educational_center_id' => $educational_center_id,
                'teacher_id' => $teacher_id,
                'teacher_phone_number' => $teacher_phone_number,
                'teacher_roll_number' => $teacher_roll_number,
                'teacher_admission_date' => $teacher_admission_date,
                'teacher_gender' => $gender,
                'teacher_religion' => $teacher_religion,
                'teacher_blood_group' => $teacher_blood_group,
                'teacher_date_of_birth' => $teacher_date_of_birth,
                'teacher_height' => $teacher_height,
                'teacher_weight' => $teacher_weight,
                'teacher_current_address' => $teacher_current_address,
                'teacher_permanent_address' => $teacher_permanent_address,
                '_user_created' => false, // Flag to track if user has been created
            ),
        ));

        // if ($teacher_post_id && $attachment_id) {
        //     update_field('field_67baed90c18fe', $attachment_id, $teacher_post_id); // ACF field key for teacher_profile_photo
        //     // wp_redirect(home_url('/institute-dashboard/teachers'));
        //     // exit;
        // }
        // elseif ($teacher_post_id) {
        //     wp_redirect(home_url('/institute-dashboard/teachers'));
        //     exit;
        // } else {
        //     echo '<p class="error-message">Error adding teacher.</p>';
        // }
        if ($teacher_post_id) {
            // Handle attachment if it exists
            if ($attachment_id) {
                update_field('field_67baed90c18fe', $attachment_id, $teacher_post_id); // ACF field key for teacher_profile_photo
            }

         // Register the teacher as a WordPress user
         if (!username_exists($teacher_id) && $teacher_email && $educational_center_id && $teacher_name) {
            $password = $teacher_phone_number ?: wp_generate_password(12, true);
            $name_parts = explode(' ', trim($teacher_name));
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';

            $user_id = wp_create_user($teacher_id, $password, $teacher_email);

            if (!is_wp_error($user_id)) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $teacher_name,
                ));

                $user = new WP_User($user_id);
                $user->set_role('teacher');

                // Store educational_center_id in user meta and log it
                $edu_center_stored = update_user_meta($user_id, 'educational_center_id', $educational_center_id);
                error_log("Teacher Registration: User ID $user_id, Teacher ID $teacher_id, Edu Center ID $educational_center_id, Stored: " . ($edu_center_stored ? 'Yes' : 'No'));

                update_user_meta($user_id, 'teacher_name', $teacher_name);
                update_post_meta($teacher_post_id, '_user_created', true);
                   $home_url = home_url(); // Corrected function name

                    // Optionally, send a welcome email with credentials
                    $message = "Welcome, $teacher_name!\n\nYour username: $teacher_id\nYour password: $password\n\nLogin at: $home_url/login/";
                     wp_mail($teacher_email, 'Your Teacher Account Credentials', $message);
                    wp_redirect(home_url('/institute-dashboard/teachers'));
                } else {
                    echo '<p class="error-message">Error creating user: ' . $user_id->get_error_message() . '</p>';
                }
            } elseif (username_exists($teacher_id)) {
                echo '<p class="error-message">A user with this teacher ID already exists.</p>';
            }
        }
    }

 
     if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['teacher_post_id'])) {
        $teacher_post_id = intval($_GET['teacher_post_id']);
        if (wp_delete_post($teacher_post_id, true)) {
            wp_redirect(home_url('/institute-dashboard/edit-teachers'));
            exit;
        } else {
            echo '<p class="error-message">Error deleting Teacher.</p>';
        }
    }
}

// Hook the function to run on init
add_action('init', 'handle_add_teacher_submission');



function handle_teacher_update_submission() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit_teacher']) || !isset($_POST['action']) || $_POST['action'] !== 'edit_teacher_submission') {
        return;
    }

    if (!current_user_can('edit_posts')) {
        wp_die('Insufficient permissions to update teacher information.');
        return;
    }

    $teacher_post_id = isset($_POST['teacher_post_id']) ? intval($_POST['teacher_post_id']) : 0;
    if ($teacher_post_id <= 0) {
        wp_die('Invalid teacher post ID: ' . $teacher_post_id);
        return;
    }

    $post = get_post($teacher_post_id);
    if (!$post || $post->post_type !== 'teacher') {
        wp_die('Teacher post not found or invalid post type for ID: ' . $teacher_post_id);
        return;
    }

    $fields = array(
        'teacher_email' => sanitize_email($_POST['teacher_email'] ?? ''),
        'teacher_phone_number' => sanitize_text_field($_POST['teacher_phone_number'] ?? ''),
        'teacher_roll_number' => sanitize_text_field($_POST['teacher_roll_number'] ?? ''),
        'teacher_admission_date' => sanitize_text_field($_POST['teacher_admission_date'] ?? ''),
        'teacher_gender' => sanitize_text_field($_POST['teacher_gender'] ?? ''),
        'teacher_religion' => sanitize_text_field($_POST['teacher_religion'] ?? ''),
        'teacher_blood_group' => sanitize_text_field($_POST['teacher_blood_group'] ?? ''),
        'teacher_date_of_birth' => sanitize_text_field($_POST['teacher_date_of_birth'] ?? ''),
        'teacher_height' => sanitize_text_field($_POST['teacher_height'] ?? ''),
        'teacher_weight' => sanitize_text_field($_POST['teacher_weight'] ?? ''),
        'teacher_current_address' => sanitize_textarea_field($_POST['teacher_current_address'] ?? ''),
        'teacher_permanent_address' => sanitize_textarea_field($_POST['teacher_permanent_address'] ?? ''),
    );

    foreach ($fields as $meta_key => $value) {
        $old_value = get_post_meta($teacher_post_id, $meta_key, true);
        if ($old_value !== $value) { // Only update if the value has changed
            $updated = update_post_meta($teacher_post_id, $meta_key, $value);
            if ($updated === false) {
                global $wpdb;
                $last_error = $wpdb->last_error;
                error_log("Failed to update meta_key: $meta_key for post ID: $teacher_post_id");
                error_log("Old value: " . print_r($old_value, true));
                error_log("New value: " . print_r($value, true));
                error_log("DB error: " . ($last_error ? $last_error : 'None reported'));
                error_log("Current user ID: " . get_current_user_id());
                wp_die("Failed to update " . esc_html($meta_key) . ". Check debug.log for details.");
                return;
            }
        }
    }

    // Handle other updates (post title, file upload) as before
    $teacher_id = sanitize_text_field($_POST['teacher_id'] ?? '');
    wp_update_post(array('ID' => $teacher_post_id, 'post_title' => $teacher_id));

    if (!empty($_FILES['teacher_profile_photo']['name'])) {
        $file = $_FILES['teacher_profile_photo'];
        $upload = wp_handle_upload($file, array('test_form' => false));
        if (isset($upload['file'])) {
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $upload['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            ), $upload['file']);
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
            update_post_meta($teacher_post_id, 'teacher_profile_photo', $attachment_id);
        }
    }

    wp_redirect(home_url('/institute-dashboard/edit-teachers'));
    // exit;
}

add_action('admin_post_edit_teacher_submission', 'handle_teacher_update_submission');
add_action('admin_post_nopriv_edit_teacher_submission', 'handle_teacher_update_submission');



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

// Function to include all files from a given directory
function include_files_from_directory($directory) {
    // Get all PHP files in the directory
    $files = glob(plugin_dir_path(__FILE__) . $directory . '/*.php');
    
    // Include each file
    foreach ($files as $file) {
        include_once $file;
    }
}

// Include all files from the teachers folder
include_files_from_directory('assets/teachers');

// Include all files from the subjects folder
include_files_from_directory('assets/subjects');
include_files_from_directory('assets/attendance');
include_files_from_directory('assets/parents');
include_files_from_directory('assets/fees');
include_files_from_directory('assets/transport');
include_files_from_directory('assets/exam');
include_files_from_directory('assets/asset');
include_files_from_directory('teacher-dashboard');
include_files_from_directory('student-dashboard');
include_files_from_directory('parent-dashboard');
include_files_from_directory('su-p');
include_files_from_directory('demo-dashboard');

// Include all other necessary files
include plugin_dir_path(__FILE__) . 'assets/edit-classes.php';
include plugin_dir_path(__FILE__) . 'assets/delete-class.php';
include plugin_dir_path(__FILE__) . 'assets/add-students.php';
include plugin_dir_path(__FILE__) . 'assets/students.php';
include plugin_dir_path(__FILE__) . 'assets/edit-students.php';
include plugin_dir_path(__FILE__) . 'assets/classes.php';
include plugin_dir_path(__FILE__) . 'assets/class-timetable.php';
include plugin_dir_path(__FILE__) . 'interface.php';
include plugin_dir_path(__FILE__) . 'subjects_interface.php';
include plugin_dir_path(__FILE__) . 'db.php';
include plugin_dir_path(__FILE__) . 'subs-db.php';
include plugin_dir_path(__FILE__) . 'helper-func.php';
// include plugin_dir_path(__FILE__) . 'test.php';



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
            'publish_posts' => true,
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
    if ( !get_role( 'su_p' ) ) {
        add_role( 'su_p', 'Su_P', array(
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
   // First, include Select2 library (you'll need to add these to your theme's header/footer)
wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);

    // Enqueue Bootstrap, Select2, and jQuery
    wp_enqueue_script('jquery');
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.3.0', true);
    // wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    // wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);
    // wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');

}
add_action('wp_enqueue_scripts', 'institute_dashboard_scripts');
function enqueue_font_awesome() {
    wp_enqueue_style(
        'font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', 
        array(), 
        '6.5.1', 
        'all'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');


//common functions
function aspire_get_admins($edu_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'institute_admins';
    return $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $table WHERE educational_center_id = %s", $edu_center_id), ARRAY_A);
}

//change
function render_change_password($current_user, $post_id = null) {
    global $wpdb;

    // Ensure user is logged in
    if (!is_user_logged_in() || !$current_user instanceof WP_User) {
        return '<div class="alert alert-danger">You must be logged in to change your password.</div>';
    }

    $user_id = $current_user->ID;
    $user_type = $current_user->roles[0] ?? 'unknown';
    $nonce = wp_create_nonce('change_password_nonce_' . $user_id);
    $message = ''; // Unified message for both forms

    // Handle Change Password Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password_submit'])) {
        if (!wp_verify_nonce($_POST['change_password_nonce'], 'change_password_nonce_' . $user_id)) {
            $message = '<div class="alert alert-danger">Security check failed. Please try again.</div>';
        } else {
            $current_password = sanitize_text_field($_POST['current_password'] ?? '');
            $new_password = sanitize_text_field($_POST['new_password'] ?? '');
            $confirm_password = sanitize_text_field($_POST['confirm_new_password'] ?? '');

            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $message = '<div class="alert alert-danger">All fields are required.</div>';
            } elseif (!wp_check_password($current_password, $current_user->user_pass, $user_id)) {
                $message = '<div class="alert alert-danger">Current password is incorrect.</div>';
            } elseif ($new_password !== $confirm_password) {
                $message = '<div class="alert alert-danger">New passwords do not match.</div>';
            } elseif (strlen($new_password) < 8) {
                $message = '<div class="alert alert-danger">New password must be at least 8 characters long.</div>';
            } else {
                wp_set_password($new_password, $user_id);
                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                $message = '<div class="alert alert-success">Password changed successfully!</div>';
            }
        }
    }

    // Handle Forgot Password Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password_submit'])) {
        $email = sanitize_email($_POST['forgot_email'] ?? '');
        if (empty($email)) {
            $message = '<div class="alert alert-danger">Please enter your email address.</div>';
        } elseif (!is_email($email) || !email_exists($email)) {
            $message = '<div class="alert alert-danger">Invalid email address or no account found.</div>';
        } else {
            $user = get_user_by('email', $email);
            $reset_key = get_password_reset_key($user);
            if (is_wp_error($reset_key)) {
                $message = '<div class="alert alert-danger">Error generating reset link. Please try again.</div>';
            } else {
                $reset_link = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
                $subject = 'Password Reset Request';
                $email_message = "Someone requested a password reset for your account. Click the link below to reset your password:\n\n";
                $email_message .= "$reset_link\n\n";
                $email_message .= "If you did not request this, please ignore this email.";
                $sent = wp_mail($email, $subject, $email_message);

                if ($sent) {
                    $message = '<div class="alert alert-success">A password reset link has been sent to your email.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to send reset email. Please try again later.</div>';
                }
            }
        }
    }

    ob_start();
    ?>
    <div class="change-password card shadow-lg">
        <div class="card-header bg-dark text-white" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title m-0"><i class="fas fa-lock me-2"></i>Change Password</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body p-4">
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <!-- Change Password Form -->
            <form id="change-password-form" method="post" action="" <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password_submit'])) ? 'style="display: none;"' : ''; ?>>
                <input type="hidden" name="change_password_nonce" value="<?php echo esc_attr($nonce); ?>">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fas fa-key"></i></span>
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-password" data-target="current_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="new_password" name="new_password" required>
                        <button type="button" class="toggle-password" data-target="new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="form-hint">Minimum 8 characters.</span>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                        <button type="button" class="toggle-password" data-target="confirm_new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" name="change_password_submit" class="submit-btn">
                    <i class="fas fa-save me-2"></i>Change Password
                </button>
                <p class="forgot-link"><a href="#" id="show-forgot-form">Forgot Password?</a></p>
            </form>

            <!-- Forgot Password Form -->
            <form id="forgot-password-form" method="post" action="" <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password_submit'])) ? '' : 'style="display: none;"'; ?>>
                <div class="form-group">
                    <label for="forgot_email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="forgot_email" name="forgot_email" required>
                    </div>
                </div>
                <button type="submit" name="forgot_password_submit" class="submit-btn">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
                <p class="forgot-link"><a href="#" id="show-change-form">Back to Change Password</a></p>
            </form>
        </div>
    </div>

    <!-- External Libraries (Font Awesome Only) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Form validation (client-side)
            const changeForm = document.getElementById('change-password-form');
            changeForm.addEventListener('submit', function(e) {
                const currentPassword = document.getElementById('current_password').value;
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_new_password').value;

                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('New password must be at least 8 characters long.');
                    return;
                }
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match.');
                    return;
                }
            });

            // Toggle between forms
            const changeFormDiv = document.getElementById('change-password-form');
            const forgotFormDiv = document.getElementById('forgot-password-form');
            document.getElementById('show-forgot-form').addEventListener('click', function(e) {
                e.preventDefault();
                changeFormDiv.style.display = 'none';
                forgotFormDiv.style.display = 'block';
            });
            document.getElementById('show-change-form').addEventListener('click', function(e) {
                e.preventDefault();
                forgotFormDiv.style.display = 'none';
                changeFormDiv.style.display = 'block';
            });
        });
    </script>

   
    <?php
    return ob_get_clean();
}
?>
<script>
function toggleSection(sectionId) {
    var content = document.getElementById(sectionId);
    var icon = content.previousElementSibling.querySelector('.toggle-icon');
    content.style.display = content.style.display === 'none' ? 'block' : 'none';
    icon.textContent = content.style.display === 'none' ? '▼' : '▲';
}
function toggleForm() {
    var form = document.getElementById('add-student-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

</script>

