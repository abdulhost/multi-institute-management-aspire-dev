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
// function custom_login_processing() {
//     // Initialize error message variable
//     $error_message = '';

//     // Check if the form has been submitted
//     if (isset($_POST['submit_login'])) {
//         // Get the user input (admin_id and password)
//         $admin_id = sanitize_text_field($_POST['admin_id']);
//         $password = sanitize_text_field($_POST['password']);

//         // Check if the admin ID starts with 'admin' (for Institute Admin)
//         if (strpos($admin_id, 'admin') === 0) {  // If the admin ID starts with 'admin'
//             // Attempt to find the user by admin ID (login)
//             $user = get_user_by('login', $admin_id);
            
//             // If user exists and password matches
//             if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
//                 // Set authentication cookie
//                 wp_set_auth_cookie($user->ID);
                
//                 // Redirect to the Institute Admin dashboard if the user is an Institute Admin
//                 if (in_array('institute_admin', (array) $user->roles)) {
//                     wp_redirect(home_url('/institute-dashboard/'));  // Redirect to Institute Admin Dashboard
//                     exit;
//                 }
//             } else {
//                 // Invalid login credentials for Institute Admin
//                 $error_message = 'Invalid ID or Password. Please try again.';
//             }
//         } else {
//             // If the admin_id doesn't start with 'admin_' (for Institute Admin)
//             $error_message = 'Incorrect ID Password. Please try again.';
//         }
//     }

//     // Pass error message to the page to display below the form
//     if (!empty($error_message)) {
//         echo "<script type='text/javascript'>
//                 document.addEventListener('DOMContentLoaded', function() {
//                     var errorMessageElement = document.getElementById('login-error-message');
//                     if (errorMessageElement) {
//                         errorMessageElement.innerHTML = '$error_message';
//                         errorMessageElement.style.display = 'block';
//                     }
//                 });
//               </script>";
//     }
// }
// function custom_login_processing($user_type_configs = []) {
//     // Default configurations for user types
//     $default_configs = [
//         'institute_admin' => [
//             'prefix' => 'admin',
//             'role' => 'institute_admin',
//             'redirect' => '/institute-dashboard/',
//         ],
//         'teacher' => [
//             'prefix' => 'TEA-',
//             'role' => 'teacher',
//             'redirect' => '/teacher-dashboard/',
//             'validate_edu_center' => true, // Teachers require educational_center_id validation
//         ],
//         'student' => [
//             'prefix' => 'STU-',
//             'role' => 'student',
//             'redirect' => '/student-dashboard/',
//             'validate_edu_center' => true, // Teachers require educational_center_id validation
//         ],
//     ];

//     // Ensure $user_type_configs is an array
//     if (!is_array($user_type_configs)) {
//         $user_type_configs = [];
//     }

//     $configs = array_merge($default_configs, $user_type_configs);
//     $error_message = '';

//     if (isset($_POST['submit_login'])) {
//         $user_id = sanitize_text_field($_POST['user_id']);
//         $password = sanitize_text_field($_POST['password']);
//         $educational_center_id = isset($_POST['educational_center_id']) ? sanitize_text_field($_POST['educational_center_id']) : '';

//         // Determine user type based on prefix
//         $matched_type = null;
//         foreach ($configs as $type => $config) {
//             if (strpos($user_id, $config['prefix']) === 0) {
//                 $matched_type = $type;
//                 break;
//             }
//         }

//         if ($matched_type) {
//             $config = $configs[$matched_type];

//             // For teachers, educational_center_id is required
//             if ($config['validate_edu_center'] && empty($educational_center_id)) {
//                 $error_message = 'Educational Center ID is required for teachers.';
//             } else {
//                 $user = get_user_by('login', $user_id);

//                 if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
//                     if (in_array($config['role'], (array) $user->roles)) {
//                         // Validate educational_center_id for teachers
//                         if ($config['validate_edu_center']) {
//                             $stored_edu_center_id = get_user_meta($user->ID, 'educational_center_id', true);
//                             error_log("Teacher Login Attempt: User ID $user_id, Input Edu Center ID $educational_center_id, Stored Edu Center ID $stored_edu_center_id");

//                             if (!$stored_edu_center_id) {
//                                 $error_message = 'No Educational Center ID associated with this account.';
//                             } elseif ($stored_edu_center_id !== $educational_center_id) {
//                                 $error_message = 'Invalid Educational Center ID.';
//                             } 
//                             else {
//                                 // Additional validation against teacher post
//                                 $args = array(
//                                     'post_type' => 'teacher',
//                                     'post_status' => 'publish',
//                                     'meta_query' => array(
//                                         'relation' => 'AND',
//                                         array(
//                                             'key' => 'teacher_id',
//                                             'value' => $user_id,
//                                             'compare' => '=',
//                                         ),
//                                         array(
//                                             'key' => 'educational_center_id',
//                                             'value' => $stored_edu_center_id,
//                                             'compare' => '=',
//                                         ),
//                                     ),
//                                 );
                                
//                                 $teacher_query = new WP_Query($args);

//                                 if (!$teacher_query->have_posts()) {
//                                     $error_message = 'Teacher ID and Educational Center ID do not match any records.';
//                                 }
//                             }
//                         }

//                         if (empty($error_message)) {
//                             wp_set_auth_cookie($user->ID);
//                             wp_redirect(home_url($config['redirect']));
//                             exit;
//                         }
//                     } else {
//                         $error_message = 'User does not have the required role.';
//                     }
//                 } else {
//                     $error_message = 'Invalid ID or Password.';
//                 }
//             }
//         } else {
//             $error_message = 'Incorrect ID format. Use a valid Admin or Teacher ID.';
//         }
//     }

//     if (!empty($error_message)) {
//         echo "<script type='text/javascript'>
//                 document.addEventListener('DOMContentLoaded', function() {
//                     var errorMessageElement = document.getElementById('login-error-message');
//                     if (errorMessageElement) {
//                         errorMessageElement.innerHTML = '$error_message';
//                         errorMessageElement.style.display = 'block';
//                     }
//                 });
//               </script>";
//     }
// }
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

    // if (in_array('teacher', $user->roles)) {
    //     return get_user_meta($user->ID, 'teacher_id', true) ?: $user->ID; // Return teacher_id for teacher
    // }

    return false; // Return false if no matching role
}

// Utility: Generate unique IDs (assumed unchanged from previous examples)
// function generate_unique_id($wpdb, $table_name, $prefix, $education_center_id) {
//     $max_attempts = 5;
//     for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
//         $time_part = substr(str_replace('.', '', microtime(true)), -10);
//         $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
//         $id = $prefix . $time_part . $random_part;

//         $exists = $wpdb->get_var($wpdb->prepare(
//             "SELECT COUNT(*) FROM $table_name WHERE " . ($prefix === 'BOOK-' ? 'book_id' : 'item_id') . " = %s AND education_center_id = %s",
//             $id, $education_center_id
//         ));

//         if ($exists == 0) {
//             return $id;
//         }
//         usleep(10000);
//     }
//     return new WP_Error('id_generation_failed', 'Unable to generate a unique ID.');
// }
// Utility: Generate unique IDs (unchanged)
// function generate_unique_id($wpdb, $table_name, $prefix, $education_center_id) {
//     $max_attempts = 5;
//     for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
//         $time_part = substr(str_replace('.', '', microtime(true)), -10);
//         $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
//         $id = $prefix . $time_part . $random_part;

//         $exists = $wpdb->get_var($wpdb->prepare(
//             "SELECT COUNT(*) FROM $table_name WHERE " . ($prefix === 'ITEM-' ? 'item_id' : 'book_id') . " = %s AND education_center_id = %s",
//             $id, $education_center_id
//         ));

//         if ($exists == 0) {
//             return $id;
//         }
//         usleep(10000);
//     }
//     return new WP_Error('id_generation_failed', 'Unable to generate a ID.');
// }


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
            'entity_name' => $wpdb->prefix . 'books',
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
 // Register the teacher as a WordPress user
//  if (!username_exists($student_id) && $teacher_email && $educational_center_id && $teacher_name) {
//     $password = $teacher_phone_number ?: wp_generate_password(12, true);
//     $name_parts = explode(' ', trim($teacher_name));
//     $first_name = $name_parts[0];
//     $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';

//     $user_id = wp_create_user($student_id, $password, $teacher_email);

//     if (!is_wp_error($user_id)) {
//         wp_update_user(array(
//             'ID' => $user_id,
//             'first_name' => $first_name,
//             'last_name' => $last_name,
//             'display_name' => $teacher_name,
//         ));

//         $user = new WP_User($user_id);
//         $user->set_role('teacher');

//         // Store educational_center_id in user meta and log it
//         $edu_center_stored = update_user_meta($user_id, 'educational_center_id', $educational_center_id);
//         error_log("Teacher Registration: User ID $user_id, Teacher ID $teacher_id, Edu Center ID $educational_center_id, Stored: " . ($edu_center_stored ? 'Yes' : 'No'));

//         update_user_meta($user_id, 'teacher_name', $teacher_name);
//         update_post_meta($teacher_post_id, '_user_created', true);
//            $home_url = home_url(); // Corrected function name

//             // Optionally, send a welcome email with credentials
//             $message = "Welcome, $teacher_name!\n\nYour username: $teacher_id\nYour password: $password\n\nLogin at: $home_url/login/";
//              wp_mail($teacher_email, 'Your Teacher Account Credentials', $message);
//             wp_redirect(home_url('/institute-dashboard/teachers'));
//         } else {
//             echo '<p class="error-message">Error creating user: ' . $user_id->get_error_message() . '</p>';
//         }
//     } elseif (username_exists($teacher_id)) {
//         echo '<p class="error-message">A user with this teacher ID already exists.</p>';
//     }
// }
   // Call the reusable registration function
   $role='student';
   register_user_with_role($student_id, $student_email, $student_name, $phone_number, $role, $educational_center_id);
// Register the teacher as a WordPress user
// if (!username_exists($student_id) && $student_email && $educational_center_id && $student_name) {
//     $password = $phone_number ?: wp_generate_password(12, true);
//     $name_parts = explode(' ', trim($student_name));
//     $first_name = $name_parts[0];
//     $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';

//     $user_id = wp_create_user($student_id, $password, $student_email);

//     if (!is_wp_error($user_id)) {
//         wp_update_user(array(
//             'ID' => $user_id,
//             'first_name' => $first_name,
//             'last_name' => $last_name,
//             'display_name' => $student_name,
//         ));

//         $user = new WP_User($user_id);
//         $user->set_role('student');

//         // Store educational_center_id in user meta and log it
//         $edu_center_stored = update_user_meta($user_id, 'educational_center_id', $educational_center_id);
//         error_log("student Registration: User ID $user_id, student ID $student_id, Edu Center ID $educational_center_id, Stored: " . ($edu_center_stored ? 'Yes' : 'No'));

//         update_user_meta($user_id, 'student_name', $student_name);
//         update_post_meta($student_post_id, '_user_created', true);
//            $home_url = home_url(); // Corrected function name

//             // Optionally, send a welcome email with credentials
//             $message = "Welcome, $student_name!\n\nYour username: $student_id\nYour password: $password\n\nLogin at: $home_url/login/";
//              wp_mail($student_email, 'Your Teacher Account Credentials', $message);
//             // wp_redirect(home_url('/institute-dashboard/teachers'));
//         } else {
//             echo '<p class="error-message">Error creating user: ' . $user_id->get_error_message() . '</p>';
//         }
//     } elseif (username_exists($teacher_id)) {
//         echo '<p class="error-message">A user with this studentstudent ID already exists.</p>';
//     }
// }
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
                        exit;
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

// Extracted Delete Function for Fee Templates
// function my_education_erp_delete_fee_template() {
//     global $wpdb;
//     $education_center_id = get_educational_center_data();
    
//     if (empty($education_center_id)) {
//         return '<p>No Educational Center found.</p>';
//     }

//     if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['template_id'])) {
//         $template_id = intval($_GET['template_id']);
//         $deleted = $wpdb->delete($wpdb->prefix . 'fee_templates', ['id' => $template_id]);
        
//         if ($deleted === false) {
//             error_log('Delete error: ' . $wpdb->last_error);
//             return '<p>Error deleting template. Please try again.</p>';
//         }
        
//         wp_redirect(home_url('/institute-dashboard/fees/?section=delete-fee-template'));
//         exit;
//     }
// }


// include plugin_dir_path(__FILE__) . 'assets/edit-classes.php';
// include plugin_dir_path(__FILE__) . 'assets/delete-class.php';
// include plugin_dir_path(__FILE__) . 'assets/attendance/attendance-management.php';
// include plugin_dir_path(__FILE__) . 'assets/attendance/insert-attendance.php';
// include plugin_dir_path(__FILE__) . 'assets/attendance/edit-attendance.php';
// include plugin_dir_path(__FILE__) . 'assets/attendance/bulk-import.php';
// include plugin_dir_path(__FILE__) . 'assets/attendance/export-attendance.php';
// include plugin_dir_path(__FILE__) . 'assets/add-students.php';
// include plugin_dir_path(__FILE__) . 'assets/students.php';
// include plugin_dir_path(__FILE__) . 'assets/edit-students.php';
// include plugin_dir_path(__FILE__) . 'assets/classes.php';
// include plugin_dir_path(__FILE__) . 'interface.php';
// include plugin_dir_path(__FILE__) . 'subjects_interface.php';
// include plugin_dir_path(__FILE__) . 'assets/subjects/subjects-management.php';
// include plugin_dir_path(__FILE__) . 'assets/subjects/add-subjects.php';
// include plugin_dir_path(__FILE__) . 'assets/subjects/edit-delete-subjects.php';
// include plugin_dir_path(__FILE__) . 'assets/subjects/delete-subjects.php';
// include plugin_dir_path(__FILE__) . 'assets/teachers/teachers.php';
// include plugin_dir_path(__FILE__) . 'assets/teachers/add-teachers.php';
// include plugin_dir_path(__FILE__) . 'assets/teachers/update-teachers.php';
// include plugin_dir_path(__FILE__) . 'assets/teachers/delete-teachers.php';



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

    <style>
        /* Reusing existing classes and aligning with your color scheme */
        .card {
            background: #fff;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .shadow-lg {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            padding: 10px 15px;
            font-weight: bold;
        }
        .bg-dark {
            background: #343a40; /* Consistent with your dark headers */
        }
        .text-white {
            color: #fff;
        }
        .card-title {
            font-size: 1.25em;
        }
        .m-0 {
            margin: 0;
        }
        .me-2 {
            margin-right: 8px;
        }
        .card-body {
            padding: 20px;
        }
        .p-4 {
            padding: 20px !important;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda; /* Light green from your success alerts */
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da; /* Light red from your danger alerts */
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Custom form styling with your color scheme */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333; /* Dark gray from your text */
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid #ccc; /* Light gray border */
            border-radius: 4px;
            background: #fff;
        }
        .input-icon {
            padding: 8px;
            background: #f5f5f5; /* Light gray background from your inputs */
            border-right: 1px solid #ccc;
            color: #666; /* Medium gray */
        }
        .input-wrapper input {
            flex: 1;
            padding: 8px;
            border: none;
            outline: none;
            font-size: 1em;
            color: #333;
        }
        .input-wrapper input:focus {
            box-shadow: 0 0 5px rgba(220, 53, 69, 0.5); /* Red shadow from your danger theme */
        }
        .toggle-password {
            padding: 8px 12px;
            border: none;
            background: #f5f5f5;
            cursor: pointer;
            color: #666;
        }
        .toggle-password:hover {
            background: #e5e5e5; /* Slightly darker gray */
        }
        .form-hint {
            display: block;
            font-size: 0.85em;
            color: #666; /* Muted gray from your hints */
            margin-top: 5px;
        }
        .submit-btn {
            padding: 10px 15px;
            background: #007bff; /* Blue from your primary buttons */
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .submit-btn:hover {
            background: #0056b3; /* Darker blue on hover */
        }
        .forgot-link {
            margin-top: 10px;
            font-size: 0.9em;
        }
        .forgot-link a {
            color: #dc3545; /* Red from your danger theme for links */
            text-decoration: none;
        }
        .forgot-link a:hover {
            color: #a71d2a; /* Darker red on hover */
            text-decoration: underline;
        }
    </style>
    <?php
    return ob_get_clean();
}


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
