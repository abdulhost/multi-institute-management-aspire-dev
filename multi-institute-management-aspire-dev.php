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

function restrict_institute_dashboard_access() {
    if (is_page('institute-dashboard')) {
        
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

add_action('template_redirect', 'restrict_institute_dashboard_access');


require_once plugin_dir_path(__FILE__) . 'dashboard.php';


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
