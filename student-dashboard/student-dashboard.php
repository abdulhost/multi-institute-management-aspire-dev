<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Function to retrieve Educational Center ID for a student
function educational_center_student_id() {
    if (!is_user_logged_in()) {
        return false;
        // wp_redirect(home_url('/login'));
        // exit();  
    }

    $current_user = wp_get_current_user();
    if (!in_array('student', (array) $current_user->roles)) {
        // return false;
        wp_redirect(home_url('/login'));
        exit();  
    }

    return get_user_meta($current_user->ID, 'educational_center_id', true) ?: false;
}

// Function to get the current student's ID (username)
function aspire_get_current_student_id() {
    $user = wp_get_current_user();

    if (!$user || !$user->exists()) {
        // error_log("No current user found or user not logged in.");
        // return null;
        wp_redirect(home_url('/login'));
        exit();  
    }

    if (!in_array('student', $user->roles)) {
        // error_log("Current user {$user->user_login} is not a student.");
        // return null;
        wp_redirect(home_url('/login'));
        exit();  
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
        // wp_redirect(home_url('/login'));
        // exit();  
    }

    return in_array('student', (array) $user->roles);
}

// Main Student Dashboard Shortcode
function aspire_student_dashboard_shortcode() {
    global $wpdb;

    if (!function_exists('wp_get_current_user')) {
        return '<div class="alert alert-danger">Some Error occured.</div>';
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $username = $current_user->user_login;

    if (!$user_id || !in_array('student', $current_user->roles)) {
        wp_redirect(home_url('/login'));
        exit();      }

    $education_center_id = educational_center_student_id();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();  
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
        return '<div class="alert alert-warning">Student profile not found. Please contact administration.</div>';
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
            echo render_student_header($current_user, $student);
            if (!is_center_subscribed($education_center_id)) {
                return render_subscription_expired_message($education_center_id);
            }
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
                    case 'report':
                        echo render_student_report($user_id, $student);
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
                    case 'library':
                        echo student_library_transactions_shortcode($student); // Placeholder for noticeboard
                        break;
                    case 'fees':
                        echo student_fees_shortcode($student);
                        break;
                    case 'transport':
                        echo student_transport_fees_shortcode($student);
                        break;
                    case 'inventory':
                        echo student_inventory_transactions_shortcode($student);
                        break;
                        case 'change_password':
                            echo render_change_password($current_user,  $student);
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

//
function render_student_header($student_user, $student) {
    global $wpdb;

    // Use student post data for name and profile photo
    $user_name = get_field('student_id', $student->ID) ?: "Student"; // Student name from ACF field
    $user_email = $student_user->user_email; // Email from WP_User

    // Get the student profile photo (assuming it’s an ACF image array)
    // $avatar_data = get_field('student_profile_photo', $student->ID);
    // $avatar_url = $avatar_data ? esc_url($avatar_data['url']) : 'https://via.placeholder.com/150';
    $avatar_url =  wp_get_attachment_url(get_post_meta($student->ID, 'student_profile_photo', true)) ?: 'https://via.placeholder.com/150';
    // if ($avatar_url) {
    //     // If it’s already a string URL, just use it with esc_url
    //     $avatar_url = esc_url($avatar_url);
    // } else {
    //     // Fallback to a placeholder if no photo is set
    //     $avatar_url = 'https://via.placeholder.com/150';
    // }
    $dashboard_link = esc_url(home_url('/student-dashboard'));
    $notifications_link = esc_url(home_url('/student-dashboard?section=notifications'));
    $settings_link = esc_url(home_url('/student-dashboard?section=change_password'));
    $logout_link = get_secure_logout_url_by_role();
    $communication_link = esc_url(home_url('/student-dashboard?section=communication'));

    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    $notifications_table = $wpdb->prefix . 'aspire_announcements';
    $student_id = $student_user->ID; // Use user ID for notifications/messages
    // error_log("stdduent id chat =: $user_name");

    // Count notifications specific to this student or broadcast to 'students'
    $notifications_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $notifications_table 
         WHERE (receiver_id = %d OR receiver_id = 'students') 
         AND sender_id != %s 
         AND timestamp > %s",
        $user_name,
        'enigma_overlord',
        $seven_days_ago
    ));
    
    // Fetch unread notifications, excluding enigma_overlord
    $unread_notifications = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp FROM $notifications_table 
         WHERE (receiver_id = %d OR receiver_id = 'students') 
         AND sender_id != %s 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $user_name,
        'enigma_overlord',
        $seven_days_ago
    ));

    // Messages count and data for students
    $messages_table = $wpdb->prefix . 'aspire_messages';
    $messages_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $messages_table 
         WHERE (receiver_id = %d OR receiver_id = 'students') 
         AND status = 'sent' 
         AND timestamp > %s",
        $user_name,
        $seven_days_ago
    ));

    $unread_messages = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $messages_table 
         WHERE (receiver_id = %d OR receiver_id = 'students') 
         AND status = 'sent' 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $user_name,
        $seven_days_ago
    ));

    ob_start();
    ?>
    <header class="su_p-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?php echo $dashboard_link; ?>" class="header-logo">
                    <img decoding="async" class="logo-image" src="<?php echo esc_url($avatar_url); ?>" alt="Student Logo">
                    <span class="logo-text">Student Dashboard</span>
                </a>
                <div class="header-search">
                    <input type="text" placeholder="Search" class="search-input" id="header-search-input" aria-label="Search">
                    <div class="search-dropdown" id="search-results">
                        <ul class="results-list" id="search-results-list"></ul>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <nav class="header-nav">
                    <a href="<?php echo $dashboard_link; ?>" class="nav-item active">Dashboard</a>
                    <a href="<?php echo esc_url(home_url('/student-dashboard?section=courses')); ?>" class="nav-item">Courses</a>
                    <a href="<?php echo esc_url(home_url('/student-dashboard?section=grades')); ?>" class="nav-item">Grades</a>
                    <a href="<?php echo $communication_link; ?>" class="nav-item">Messages</a>
                </nav>
                <div class="header-actions">
                    <!-- Quick Links Dropdown -->
                    <div class="header-quick-links">
                        <a href="#" class="action-btn" id="quick-links-toggle">
                            <i class="fas fa-link fa-lg"></i>
                        </a>
                        <div class="dropdown quick-links-dropdown" id="quick-links-dropdown">
                            <div class="dropdown-header">
                                <span>Quick Links</span>
                            </div>
                            <ul class="dropdown-list">
                                <li><a href="<?php echo esc_url(home_url('/student-dashboard?section=schedule')); ?>" class="dropdown-link">Schedule</a></li>
                                <li><a href="<?php echo esc_url(home_url('/student-dashboard?section=assignments')); ?>" class="dropdown-link">Assignments</a></li>
                                <li><a href="https://support.instituto.edu" target="_blank" class="dropdown-link">Support</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Messages Dropdown -->
                    <div class="header-messages">
                        <a href="<?php echo $communication_link; ?>" class="action-btn" id="messages-toggle">
                            <i class="fas fa-envelope fa-lg"></i>
                            <span class="action-badge <?php echo $messages_count ? '' : 'd-none'; ?>" id="messages-count">
                                <?php echo esc_html($messages_count ?: 0); ?>
                            </span>
                        </a>
                        <div class="dropdown messages-dropdown" id="messages-dropdown">
                            <div class="dropdown-header">
                                <span>Messages (Last 7 Days)</span>
                            </div>
                            <ul class="dropdown-list">
                                <?php if (!empty($unread_messages)): ?>
                                    <?php foreach ($unread_messages as $msg): ?>
                                        <li>
                                            <span class="msg-content">
                                                <span class="msg-sender"><?php echo esc_html($msg->sender_id); ?></span>:
                                                <span class="msg-preview"><?php echo esc_html(wp_trim_words($msg->message, 5, '...')); ?></span>
                                            </span>
                                            <span class="msg-time"><?php echo esc_html(date('M d, Y', strtotime($msg->timestamp))); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><span class="msg-preview">No new messages</span></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (!empty($unread_messages)): ?>
                                <a href="<?php echo $communication_link; ?>" class="dropdown-footer">View All Messages</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notifications Dropdown -->
                    <div class="header-notifications">
                        <a href="<?php echo $notifications_link; ?>" class="action-btn" id="notifications-toggle">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="action-badge <?php echo $notifications_count ? '' : 'd-none'; ?>" id="notifications-count">
                                <?php echo esc_html($notifications_count ?: 0); ?>
                            </span>
                        </a>
                        <div class="dropdown notifications-dropdown" id="notifications-dropdown">
                            <div class="dropdown-header">
                                <span>Notifications (Last 7 Days)</span>
                            </div>
                            <ul class="dropdown-list">
                                <?php if (!empty($unread_notifications)): ?>
                                    <?php foreach ($unread_notifications as $ann): ?>
                                        <li>
                                            <span class="msg-content">
                                                <span class="msg-sender"><?php echo esc_html($ann->sender_id); ?></span>:
                                                <span class="notif-text"><?php echo esc_html(wp_trim_words($ann->message, 5, '...')); ?></span>
                                            </span>
                                            <span class="notif-time"><?php echo esc_html(date('M d, Y', strtotime($ann->timestamp))); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><span class="notif-text">No new notifications</span></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (!empty($unread_notifications)): ?>
                                <a href="<?php echo $notifications_link; ?>" class="dropdown-footer">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="header-settings">
                        <a href="<?php echo $settings_link; ?>" class="action-btn" id="settings-toggle">
                            <i class="fas fa-cog fa-lg"></i>
                        </a>
                    </div>

                    <!-- Help/Support -->
                    <div class="header-help">
                        <a href="https://support.instituto.edu" target="_blank" class="action-btn" id="help-toggle">
                            <i class="fas fa-question-circle fa-lg"></i>
                        </a>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <div class="header-dark-mode">
                        <button class="action-btn" id="dark-mode-toggle">
                            <i class="fas fa-moon fa-lg"></i>
                        </button>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="header-profile">
                        <div class="profile-toggle" id="profile-toggle">
                            <img decoding="async" src="<?php echo esc_url($avatar_url); ?>" alt="Profile" class="profile-img">
                            <i class="fas fa-caret-down profile-arrow"></i>
                        </div>
                        <div class="action-dropdown profile-dropdown" id="profile-dropdown">
                            <div class="profile-info">
                                <img decoding="async" src="<?php echo esc_url($avatar_url); ?>" alt="Profile" class="profile-img-large">
                                <div>
                                    <span class="profile-name"><?php echo esc_html($user_name); ?></span><br>
                                    <span class="profile-email"><?php echo esc_html($user_email); ?></span>
                                </div>
                            </div>
                            <ul class="dropdown-list">
                                <li><a href="<?php echo $settings_link; ?>" class="profile-link">Change Password</a></li>
                                <li><a href="<?php echo $logout_link; ?>" class="profile-link logout">Logout</a></li>
                            </ul>
                        </div>
                    </div>

                    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle Navigation">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <!-- Rest of the script remains unchanged -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        const fontAwesomeLink = document.createElement('link');
        fontAwesomeLink.rel = 'stylesheet';
        fontAwesomeLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
        fontAwesomeLink.crossOrigin = 'anonymous';
        fontAwesomeLink.onload = () => console.log('Font Awesome loaded');
        fontAwesomeLink.onerror = () => console.error('Failed to load Font Awesome');
        document.head.appendChild(fontAwesomeLink);

        const searchInput = document.getElementById('header-search-input');
        const searchResults = document.getElementById('search-results');
        const navToggle = document.getElementById('nav-toggle');
        const headerNav = document.querySelector('.header-nav');
        const messagesToggle = document.getElementById('messages-toggle');
        const messagesDropdown = document.getElementById('messages-dropdown');
        const notificationsToggle = document.getElementById('notifications-toggle');
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        const quickLinksToggle = document.getElementById('quick-links-toggle');
        const quickLinksDropdown = document.getElementById('quick-links-dropdown');
        const settingsToggle = document.getElementById('settings-toggle');
        const helpToggle = document.getElementById('help-toggle');
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const profileToggle = document.getElementById('profile-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');

        let activeDropdown = null;

        function toggleDropdown(toggle, dropdown, isLink = false) {
            toggle.addEventListener('mouseenter', () => {
                closeAllDropdowns();
                dropdown.classList.add('visible');
                activeDropdown = dropdown;
            });

            dropdown.addEventListener('mouseenter', () => {
                dropdown.classList.add('visible');
                activeDropdown = dropdown;
            });

            toggle.addEventListener('mouseleave', () => {
                dropdown.addEventListener('mouseleave', () => {
                    dropdown.classList.remove('visible');
                    activeDropdown = null;
                });
            });

            if (!isLink) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (dropdown.classList.contains('visible')) {
                        dropdown.classList.remove('visible');
                        activeDropdown = null;
                    } else {
                        closeAllDropdowns();
                        dropdown.classList.add('visible');
                        activeDropdown = dropdown;
                    }
                });
            }
        }

        function closeAllDropdowns() {
            [messagesDropdown, notificationsDropdown, quickLinksDropdown, profileDropdown, searchResults].forEach(dropdown => {
                if (dropdown) dropdown.classList.remove('visible');
            });
            activeDropdown = null;
        }

        if (searchInput && searchResults) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const query = this.value.trim();
                    if (query.length < 2) {
                        searchResults.classList.remove('visible');
                        return;
                    }

                    searchResults.querySelector('.results-list').innerHTML = '<li>Loading...</li>';
                    searchResults.classList.add('visible');

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'search_student_sections',
                            query: query
                        }).toString()
                    })
                    .then(response => response.json())
                    .then(data => {
                        const resultsList = searchResults.querySelector('.results-list');
                        resultsList.innerHTML = '';
                        if (data.success && data.data.length > 0) {
                            data.data.forEach(item => {
                                resultsList.innerHTML += `<li><a href="${item.url}">${item.title}</a></li>`;
                            });
                        } else {
                            resultsList.innerHTML = '<li>No results found</li>';
                        }
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        searchResults.querySelector('.results-list').innerHTML = '<li>Error fetching results</li>';
                    });
                }, 150);
            });
        }

        if (navToggle && headerNav) {
            navToggle.addEventListener('click', () => {
                headerNav.classList.toggle('visible');
            });
        }

        if (messagesToggle && messagesDropdown) {
            toggleDropdown(messagesToggle, messagesDropdown, true);
        }

        if (notificationsToggle && notificationsDropdown) {
            toggleDropdown(notificationsToggle, notificationsDropdown, true);
        }

        if (quickLinksToggle && quickLinksDropdown) {
            toggleDropdown(quickLinksToggle, quickLinksDropdown);
        }

        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                darkModeToggle.querySelector('i').classList.toggle('fa-moon', !isDark);
                darkModeToggle.querySelector('i').classList.toggle('fa-sun', isDark);
            });
        }

        if (profileToggle && profileDropdown) {
            toggleDropdown(profileToggle, profileDropdown);
        }

        document.addEventListener('click', (e) => {
            if (searchInput && searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('visible');
            }
            if (activeDropdown && !activeDropdown.contains(e.target) && !e.target.closest('.action-btn, .profile-toggle')) {
                activeDropdown.classList.remove('visible');
                activeDropdown = null;
            }
        });
    });

    jQuery(document).ready(function($) {
        $('a[href*="section=communication"]').on('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_student_mark_messages_read',
                    nonce: '<?php echo wp_create_nonce('aspire_student_header_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#messages-count').text('0').addClass('d-none');
                        $('#messages-dropdown .dropdown-list').html('<li><span class="msg-preview">No new messages</span></li>');
                        $('#messages-dropdown .dropdown-footer').remove();
                        window.location.href = '<?php echo $communication_link; ?>';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error marking messages as read:', error);
                }
            });
        });

        $('a[href*="section=notifications"]').on('click', function(e) {
            e.preventDefault();
            $('#notifications-count').text('0').addClass('d-none');
            $('#notifications-dropdown .dropdown-list').html('<li><span class="notif-text">No new notifications</span></li>');
            $('#notifications-dropdown .dropdown-footer').remove();
            window.location.href = '<?php echo $notifications_link; ?>';
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_search_student_sections', 'search_student_sections_callback');
function search_student_sections_callback() {
    $query = sanitize_text_field($_POST['query']);
    $results = [
        ['title' => 'Courses', 'url' => home_url('/student-dashboard?section=courses')],
        ['title' => 'Grades', 'url' => home_url('/student-dashboard?section=grades')],
        ['title' => 'Schedule', 'url' => home_url('/student-dashboard?section=schedule')],
        ['title' => 'Assignments', 'url' => home_url('/student-dashboard?section=assignments')],
    ];
    wp_send_json_success(array_filter($results, function($item) use ($query) {
        return stripos($item['title'], $query) !== false;
    }));
}

add_action('wp_ajax_aspire_student_mark_messages_read', 'aspire_student_mark_messages_read_callback');
function aspire_student_mark_messages_read_callback() {
    check_ajax_referer('aspire_student_header_nonce', 'nonce');
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'aspire_messages',
        ['status' => 'read'],
        ['receiver_id' => get_current_user_id(), 'status' => 'sent'],
        ['%s'],
        ['%d', '%s']
    );
    wp_send_json_success();
}

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

//homework
function render_student_homework($user_id, $student) {
    global $wpdb;

    // Get educational center ID
    $education_center_id = educational_center_student_id();
    // if (empty($education_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found.</div>';
    // }

    // Get student details
    $student_id = get_post_meta($student->ID, 'student_id', true);
    $class = get_post_meta($student->ID, 'class', true);
    $section = get_post_meta($student->ID, 'section', true);
    if (empty($student_id) || empty($class) || empty($section)) {
        return '<div class="alert alert-warning">Student details not assigned. Please contact administration.</div>';
    }

    $homework_table = $wpdb->prefix . 'homework';
    $subjects_table = $wpdb->prefix . 'subjects';

    // Fetch homework with subject names
    $homeworks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT h.*, s.subject_name 
             FROM $homework_table h
             LEFT JOIN $subjects_table s ON h.subject_id = s.subject_id AND h.education_center_id = s.education_center_id
             WHERE h.education_center_id = %s 
             AND h.class_name = %s 
             AND h.section = %s 
             ORDER BY h.due_date ASC",
            $education_center_id,
            $class,
            $section
        )
    );

    // Get unique subjects for filter
    $subjects = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT s.subject_name 
             FROM $homework_table h
             JOIN $subjects_table s ON h.subject_id = s.subject_id AND h.education_center_id = s.education_center_id
             WHERE h.education_center_id = %s 
             AND h.class_name = %s 
             AND h.section = %s",
            $education_center_id,
            $class,
            $section
        )
    );

    ob_start();
    ?>
    <div class="card shadow-sm border-0 homework-card">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title m-0"><i class="bi bi-book me-2"></i>My Homework</h3>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <span class="badge bg-light text-primary"><?php echo count($homeworks); ?> Tasks</span>
                <select id="status-filter" class="form-select form-select-sm shadow-sm">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="overdue">Overdue</option>
                </select>
                <select id="subject-filter" class="form-select form-select-sm shadow-sm">
                    <option value="all">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo esc_attr($subject); ?>"><?php echo esc_html($subject); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (empty($homeworks)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-emoji-smile me-2"></i>No homework assigned yet. Enjoy your free time!
                </div>
            <?php else: ?>
                <div class="homework-list" id="homework-list">
                    <?php
                    // Separate active (active/overdue) and completed homework
                    $active_homeworks = [];
                    $completed_homeworks = [];
                    $now = new DateTime(current_time('Y-m-d'));

                    foreach ($homeworks as $homework) {
                        $due_date = new DateTime($homework->due_date);
                        $is_overdue = $due_date < $now && $homework->status !== 'completed';
                        if ($homework->status === 'completed') {
                            $completed_homeworks[] = $homework;
                        } else {
                            $active_homeworks[] = $homework;
                        }
                    }

                    // Merge: active first, then completed
                    $sorted_homeworks = array_merge($active_homeworks, $completed_homeworks);

                    foreach ($sorted_homeworks as $homework):
                        $due_date = new DateTime($homework->due_date);
                        $is_overdue = $due_date < $now && $homework->status !== 'completed';
                        $status_class = $is_overdue ? 'bg-danger-subtle' : ($homework->status === 'completed' ? 'bg-success-subtle' : 'bg-warning-subtle');
                        $status_text = $is_overdue ? 'overdue' : strtolower($homework->status);
                    ?>
                        <div class="homework-item card mb-3 <?php echo $status_class; ?> shadow-sm" 
                             data-status="<?php echo esc_attr($status_text); ?>" 
                             data-subject="<?php echo esc_attr($homework->subject_name ?? 'No Subject'); ?>">
                            <div class="card-header d-flex justify-content-between align-items-center" 
                                 data-bs-toggle="collapse" 
                                 data-bs-target="#homework-<?php echo $homework->homework_id; ?>" 
                                 aria-expanded="true">
                                <h5 class="card-title mb-0"><?php echo esc_html($homework->title); ?></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge <?php echo $is_overdue ? 'bg-danger' : ($homework->status === 'completed' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo $is_overdue ? 'Overdue' : esc_html($homework->status); ?>
                                    </span>
                                    <i class="bi bi-chevron-down toggle-icon"></i>
                                </div>
                            </div>
                            <div id="homework-<?php echo $homework->homework_id; ?>" class="collapse show">
                                <div class="card-body">
                                    <p class="text-muted mb-2">
                                        <strong>Class:</strong> <?php echo esc_html($homework->class_name); ?> - <?php echo esc_html($homework->section); ?>
                                        | <strong>Subject:</strong> <?php echo esc_html($homework->subject_name ?? 'No Subject'); ?>
                                        | <strong>Due:</strong> <?php echo esc_html($due_date->format('F j, Y')); ?>
                                    </p>
                                    <p class="description mb-0"><?php echo esc_html($homework->description); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function filterHomework() {
            const statusFilter = $('#status-filter').val();
            const subjectFilter = $('#subject-filter').val();

            $('.homework-item').each(function() {
                const status = $(this).data('status');
                const subject = $(this).data('subject');
                const statusMatch = statusFilter === 'all' || status === statusFilter;
                const subjectMatch = subjectFilter === 'all' || subject === subjectFilter;

                if (statusMatch && subjectMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Initial filter
        filterHomework();

        // Filter on change
        $('#status-filter, #subject-filter').on('change', filterHomework);

        // Bootstrap collapse toggle class handling
        $('.homework-item .card-header').on('click', function() {
            $(this).toggleClass('collapsed');
        });
    });
    </script>
    <?php
    return ob_get_clean();
}


function render_student_exams($user_id, $student) {
    global $wpdb;

    $exams_table = $wpdb->prefix . 'exams';

    // Get educational center ID (assuming this function exists)
    $education_center_id = educational_center_student_id();
    // if (empty($education_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found.</div>';
    // }

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
    // if (!$education_center_id) {
    //     error_log('fetch_student_exams_data: No educational center ID');
    //     return array(
    //         'success' => false,
    //         'message' => 'Educational center ID not found'
    //     );
    // }

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
    // if (empty($education_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found.</div>';
    // }

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
        // .attendance-table th {
        //     background: #f8f9fa;
        //     font-weight: 600;
        //     vertical-align: middle;
        // }
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
    // if (empty($education_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found.</div>';
    // }

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
    // if (!$education_center_id) {
    //     return array('success' => false, 'message' => 'Educational center ID not found');
    // }

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

//report
function render_student_report($user_id, $student) {
    global $wpdb;

    // Get educational center ID
    $education_center_id = educational_center_student_id();
    // if (empty($education_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found.</div>';
    // }

    // Get student_id from post meta
    $student_id = get_post_meta($student->ID, 'student_id', true);
    if (empty($student_id)) {
        return '<div class="alert alert-warning">Student ID not assigned. Please contact administration.</div>';
    }

    // Generate nonce and REST URL
    $nonce = wp_create_nonce('wp_rest');
    $fetch_url = rest_url('reports/v1/fetch');

    ob_start();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white">
            <h3 class="card-title m-0"><i class="bi bi-bar-chart me-2"></i>My Reports</h3>
        </div>
        <div class="card-body">
            <div class="filter mb-3">
                <select id="time-period" class="form-select w-auto">
                    <option value="30days">Last 30 Days</option>
                    <option value="semester">Last Semester (6 Months)</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div class="reports-wrapper" id="reports-container">
                <p class="loading-message"><span class="spinner"></span> Loading reports...</p>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function loadReports() {
            var studentId = '<?php echo esc_js($student_id); ?>';
            var period = $('#time-period').val();

            $.ajax({
                url: '<?php echo esc_js($fetch_url); ?>',
                method: 'POST',
                data: {
                    student_id: studentId,
                    period: period,
                    _wpnonce: '<?php echo esc_js($nonce); ?>'
                },
                headers: {
                    'X-WP-Nonce': '<?php echo esc_js($nonce); ?>'
                },
                beforeSend: function() {
                    $('#reports-container').html('<p class="loading-message"><span class="spinner"></span> Loading reports...</p>');
                },
                success: function(response) {
                    console.log('Fetch Reports Response:', response);
                    if (response.success && response.data && response.data.html) {
                        $('#reports-container').html(response.data.html);
                    } else {
                        $('#reports-container').html('<p>No data available for this period.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#reports-container').html('<p>Error loading reports: ' + (xhr.responseJSON?.message || error) + '</p>');
                    console.log('AJAX Error:', xhr.responseText);
                }
            });
        }

        loadReports();
        $('#time-period').on('change', loadReports);
    });
    </script>
    <?php
    return ob_get_clean();
}

// Register REST endpoint
add_action('rest_api_init', function () {
    register_rest_route('reports/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_student_reports_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

function fetch_student_reports_data($request) {
    global $wpdb;
    $attendance_table = $wpdb->prefix . 'student_attendance';
    $exam_results_table = $wpdb->prefix . 'exam_results';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $exams_table = $wpdb->prefix . 'exams';
    $params = $request->get_params();

    $education_center_id = educational_center_student_id();
    // if (!$education_center_id) {
    //     return array('success' => false, 'message' => 'Educational center ID not found');
    // }

    $student_id = sanitize_text_field($params['student_id'] ?? '');
    $period = sanitize_text_field($params['period'] ?? '30days');
    if (empty($student_id)) {
        return array('success' => false, 'message' => 'Student ID missing');
    }

    // Determine date range
    $current_date = current_time('Y-m-d');
    switch ($period) {
        case '30days':
            $date_condition = " AND date >= DATE_SUB('$current_date', INTERVAL 30 DAY)";
            $exam_date_condition = " AND e.exam_date >= DATE_SUB('$current_date', INTERVAL 30 DAY)";
            break;
        case 'semester':
            $date_condition = " AND date >= DATE_SUB('$current_date', INTERVAL 6 MONTH)";
            $exam_date_condition = " AND e.exam_date >= DATE_SUB('$current_date', INTERVAL 6 MONTH)";
            break;
        case 'all':
        default:
            $date_condition = '';
            $exam_date_condition = '';
            break;
    }

    // Fetch attendance data
    $attendance_query = "
        SELECT date, status 
        FROM $attendance_table 
        WHERE education_center_id = %s 
        AND student_id = %s 
        $date_condition 
        ORDER BY date DESC";
    $results = $wpdb->get_results($wpdb->prepare($attendance_query, $education_center_id, $student_id));

    if ($wpdb->last_error) {
        error_log('Attendance Query Error: ' . $wpdb->last_error);
        error_log('Attendance Query: ' . $wpdb->last_query);
        return array('success' => false, 'message' => 'Database error in attendance query');
    }

    // Process attendance data
    $attendance_counts = ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0];
    $attendance_summary = [
        'Present' => 0,
        'Late' => 0,
        'Absent' => 0,
        'Full Day' => 0,
        'Holiday' => 0
    ];

    foreach ($results as $attendance) {
        switch ($attendance->status) {
            case 'Present':
                $attendance_counts['P']++;
                $attendance_summary['Present']++;
                break;
            case 'Late':
                $attendance_counts['L']++;
                $attendance_summary['Late']++;
                break;
            case 'Absent':
                $attendance_counts['A']++;
                $attendance_summary['Absent']++;
                break;
            case 'Full Day':
                $attendance_counts['F']++;
                $attendance_summary['Full Day']++;
                break;
            case 'Holiday':
                $attendance_counts['H']++;
                $attendance_summary['Holiday']++;
                break;
        }
    }

    $total_attendance = array_sum($attendance_counts);
    $attendance_percentages = [];
    foreach ($attendance_summary as $status => $count) {
        $attendance_percentages[$status] = $total_attendance > 0 ? round(($count / $total_attendance) * 100, 2) : 0;
    }

  // Fetch exam performance data (only exams with results)
  $exam_query = "
  SELECT e.id, e.name, e.exam_date, AVG(er.marks) as avg_marks, SUM(es.max_marks) as total_max
  FROM $exams_table e
  INNER JOIN $exam_results_table er ON e.id = er.exam_id AND er.student_id = %s AND er.education_center_id = %s
  INNER JOIN $exam_subjects_table es ON er.subject_id = es.id
  WHERE e.education_center_id = %s
  $exam_date_condition
  GROUP BY e.id, e.name, e.exam_date
  HAVING avg_marks IS NOT NULL
  ORDER BY e.exam_date ASC";
$exam_data = $wpdb->get_results($wpdb->prepare($exam_query, $student_id, $education_center_id, $education_center_id));

    if ($wpdb->last_error) {
        error_log('Exam Query Error: ' . $wpdb->last_error);
        error_log('Exam Query: ' . $wpdb->last_query);
        return array('success' => false, 'message' => 'Database error in exam query');
    }

    // Generate HTML
    ob_start();
    ?>
    <div class="report-section">
        <h4>Attendance Summary</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_summary as $status => $count) : ?>
                    <tr>
                        <td class="<?php echo esc_attr(strtolower(str_replace(' ', '-', $status))); ?>">
                            <?php echo esc_html($status); ?>
                        </td>
                        <td><?php echo esc_html($count); ?></td>
                        <td><?php echo esc_html($attendance_percentages[$status]); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td><?php echo esc_html($total_attendance); ?></td>
                    <td>100%</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="report-section">
        <h4>Exam Performance</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Exam Name</th>
                    <th>Date</th>
                    <th>Average Marks</th>
                    <th>Max Possible</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($exam_data)) : ?>
                    <?php foreach ($exam_data as $exam) : ?>
                        <?php
                        $avg_marks = $exam->avg_marks ? round((float) $exam->avg_marks, 2) : 0;
                        $max_marks = $exam->total_max ? (float) $exam->total_max : 0;
                        $percentage = $max_marks > 0 ? round(($avg_marks / $max_marks) * 100, 2) : 0;
                        ?>
                        <tr>
                            <td><?php echo esc_html($exam->name); ?></td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($exam->exam_date))); ?></td>
                            <td><?php echo esc_html($avg_marks); ?></td>
                            <td><?php echo esc_html($max_marks); ?></td>
                            <td><?php echo esc_html($percentage); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="5">No exam data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    $html = ob_get_clean();

    return array(
        'success' => true,
        'data' => array(
            'html' => $html
        )
    );
}



//
// communication
// Helper: Check if user is a student
function aspire_is_student($user) {
    return in_array('student', $user->roles);
}

function aspire_student_get_username($post_id) {
    $user_id = get_post_field('post_author', $post_id);
    $user = get_user_by('id', $user_id);
    $username = $user ? $user->user_login : '';
    error_log("aspire_student_get_username: post_id=$post_id, user_id=$user_id, username=$username");
    return $username;
}
function aspire_student_communication_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to use this chat.</p>';
    }

    $user = wp_get_current_user();
    if (!aspire_is_student($user)) {
        return '<p>You do not have the required permissions to use this chat.</p>';
    }

    $username = $user->user_login;
    $edu_center_id = educational_center_student_id();
    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);

    ob_start();
    ?>
    <div id="aspire-student-prochat" class="chat-container">
        <div class="chat-wrapper">
            <div class="chat-sidebar">
                <div class="sidebar-header">
                    <h4>Inbox</h4>
                </div>
                <ul id="aspire-student-conversations" class="conversation-list"></ul>
            </div>
            <div class="chat-main">
                <div class="chat-header">
                    <h5 id="current-conversation">Select a conversation</h5>
                    <div>
                        <button id="new-chat" class="btn btn-outline-primary btn-sm">New Chat</button>
                        <button id="clear-conversation" class="btn btn-outline-secondary btn-sm" style="display:none;">Clear</button>
                    </div>
                </div>
                <div id="aspire-student-message-list" class="chat-messages"></div>
                <form id="aspire-student-send-form" class="chat-form">
                    <div class="input-group">
                        <textarea id="aspire-student-message-input" class="form-control" placeholder="Type your message..." rows="1" required></textarea>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
                    </div>
                    <div id="recipient-select" class="recipient-select" style="display:none;">
                        <select id="aspire-student-target-value" class="form-select">
                            <option value="">Select a recipient</option>
                            <!-- Group Options -->
                            <option value="all">All</option>
                            <option value="teachers">All Teachers</option>
                            <option value="institute_admins">All Admins</option>
                            <option value="parents">All Parents</option>
                            <!-- Individual Contacts -->
                            <?php
                            $unique_contacts = []; // Track unique contact IDs
                            foreach ($contacts as $contact) {
                                if ($contact->post_type === 'teacher') {
                                    $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
                                    $teacher_name = get_post_meta($contact->ID, 'teacher_name', true);
                                    $label = "$teacher_name ($contact_id - Teacher)";
                                } elseif ($contact->post_type === 'students') {
                                    $contact_id = get_post_meta($contact->ID, 'student_id', true);
                                    $student_name = get_post_meta($contact->ID, 'student_name', true);
                                    $label = "$student_name ($contact_id - Student)";
                                } else { // Parent
                                    $contact_id = get_post_meta($contact->ID, 'parent_id', true) ?: $contact->post_title;
                                    $label = "$contact->post_title ($contact_id - Parent)";
                                }

                                // Skip if no valid ID or if it's the current user
                                if (!$contact_id || $contact_id === $username) continue;

                                // Add only if not already seen
                                if (!isset($unique_contacts[$contact_id])) {
                                    $unique_contacts[$contact_id] = $label;
                                    error_log("Contact: post_id={$contact->ID}, contact_id=$contact_id, label=$label");
                                    echo '<option value="' . esc_attr($contact_id) . '">' . esc_html($label) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php wp_nonce_field('aspire_student_nonce', 'aspire_student_nonce_field'); ?>
                </form>
            </div>
        </div>
    </div>
   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        let selectedConversation = localStorage.getItem('aspire_student_selected_conversation') || '';
        let currentRecipient = '';

        function fetchMessages(conversationWith) {
            // Show loading indicator
            $('#aspire-student-message-list').html('<div class="chat-loading active"><div class="spinner"></div><p>Loading messages...</p></div>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_student_fetch_messages',
                    conversation_with: conversationWith,
                    nonce: $('#aspire_student_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-student-message-list').html(response.data.html);
                        const chatMessages = document.querySelector('#aspire-student-message-list');
                        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
                        updateTimestamps();
                    } else {
                        $('#aspire-student-message-list').html('<p>Error loading messages.</p>');
                        console.error('Fetch messages failed:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $('#aspire-student-message-list').html('<p>Network error occurred.</p>');
                    console.error('AJAX error:', status, error);
                }
            });
        }

        function updateConversations() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_student_fetch_conversations',
                    nonce: $('#aspire_student_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-student-conversations').html(response.data);
                        if (selectedConversation) {
                            $(`#aspire-student-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
                        }
                    } else {
                        console.error('Failed to fetch conversations:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                }
            });
        }

        function updateTimestamps() {
            $('.chat-message .meta').each(function() {
                const timestamp = $(this).data('timestamp');
                $(this).text(`${$(this).text().split(' - ')[0]} - ${moment(timestamp).fromNow()}`);
            });
        }

        $(document).on('click', '#aspire-student-conversations li', function() {
            $('#aspire-student-conversations li').removeClass('active');
            $(this).addClass('active');
            selectedConversation = $(this).data('conversation-with');
            currentRecipient = selectedConversation;
            localStorage.setItem('aspire_student_selected_conversation', selectedConversation);
            $('#current-conversation').text($(this).text());
            $('#clear-conversation').show();
            $('#new-chat').show();
            $('#recipient-select').hide();
            fetchMessages(selectedConversation);
        });

        $(document).on('click', '#aspire-student-conversations li', function() {
            $('#aspire-student-conversations li').removeClass('active');
            $(this).addClass('active');
            selectedConversation = $(this).data('conversation-with');
            currentRecipient = selectedConversation;
            localStorage.setItem('aspire_student_selected_conversation', selectedConversation);
            $('#current-conversation').text($(this).text());
            $('#clear-conversation').show();
            $('#new-chat').show();
            $('#recipient-select').hide();
            fetchMessages(selectedConversation);
        });

        $('#clear-conversation').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_student_selected_conversation');
            $('#current-conversation').text('Select a conversation');
            $('#clear-conversation').hide();
            $('#new-chat').show();
            $('#recipient-select').hide();
            $('#aspire-student-message-list').empty();
            $('#aspire-student-conversations li').removeClass('active');
        });

        $('#new-chat').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_student_selected_conversation');
            $('#current-conversation').text('New Conversation');
            $('#clear-conversation').show();
            $('#new-chat').hide();
            $('#recipient-select').show();
            $('#aspire-student-message-list').empty();
            $('#aspire-student-conversations li').removeClass('active');
            $('#aspire-student-target-value').val('');
        });

        $('#aspire-student-send-form').submit(function(e) {
            e.preventDefault();
            const message = $('#aspire-student-message-input').val().trim();
            if (!message) return;

            let targetValue = $('#recipient-select').is(':visible') ? $('#aspire-student-target-value').val() : currentRecipient;
            if (!targetValue) {
                alert('Please select a recipient.');
                return;
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_student_send_message',
                    message: message,
                    target_value: targetValue,
                    nonce: $('#aspire_student_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-student-message-input').val('');
                        if ($('#recipient-select').is(':visible')) {
                            currentRecipient = targetValue;
                            $('#current-conversation').text($(`#aspire-student-target-value option[value="${targetValue}"]`).text());
                            $('#clear-conversation').show();
                            $('#new-chat').hide();
                            $('#recipient-select').hide();
                        }
                        fetchMessages(currentRecipient);
                        setTimeout(updateConversations, 500);
                    } else {
                        alert('Failed to send message: ' + (response.data?.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    alert('Network error occurred. Please try again.');
                }
            });
        });

        updateConversations();
        if (selectedConversation) {
            $(`#aspire-student-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
            $('#current-conversation').text($(`#aspire-student-conversations li[data-conversation-with="${selectedConversation}"]`).text());
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
add_shortcode('aspire_student_communication', 'aspire_student_communication_shortcode');
add_action('wp_ajax_aspire_student_send_message', 'aspire_student_ajax_send_message');
function aspire_student_ajax_send_message() {
    error_log("aspire_student_ajax_send_message function called");
    check_ajax_referer('aspire_student_nonce', 'nonce');

    $edu_center_id = educational_center_student_id();
    $user = wp_get_current_user();
    $sender_id = $user->user_login;
    $message = sanitize_text_field($_POST['message'] ?? '');
    $target_value = sanitize_text_field($_POST['target_value'] ?? '');

    if (!$edu_center_id || !$sender_id || !$message || !$target_value) {
        error_log("Missing fields: edu_center_id=$edu_center_id, sender_id=$sender_id, message=$message, target_value=$target_value");
        wp_send_json_error(['error' => 'Missing required fields']);
        return;
    }

    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);

    $unique_contacts = []; // Map of contact IDs
    foreach ($contacts as $contact) {
        if ($contact->post_type === 'teacher') {
            $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
        } elseif ($contact->post_type === 'students') {
            $contact_id = get_post_meta($contact->ID, 'student_id', true);
        } else { // Parent
            $contact_id = get_post_meta($contact->ID, 'parent_id', true) ?: $contact->post_title;
        }
        if ($contact_id && $contact_id !== $sender_id) {
            $unique_contacts[$contact_id] = $contact->post_type;
        }
    }

    $success = false;
    if (in_array($target_value, ['all', 'institute_admins', 'teachers', 'parents'])) {
        // Group message
        // $recipients = [];
        // foreach ($unique_contacts as $contact_id => $post_type) {
        //     if ($target_value === 'ALL' ||
        //         ($target_value === 'ALL_TEACHERS' && $post_type === 'teacher') ||
        //         ($target_value === 'ALL_STUDENTS' && $post_type === 'students') ||
        //         ($target_value === 'ALL_PARENTS' && $post_type === 'parent')) {
        //         $recipients[] = $contact_id;
        //     }
        // }

        // foreach ($recipients as $receiver_id) {
        //     error_log("AJAX send (group): sender_id=$sender_id, receiver_id=$receiver_id");
        //     $success = aspire_student_send_message($sender_id, $receiver_id, $message, $edu_center_id) || $success;
        // }
        $receiver_id = $target_value;
        error_log("AJAX send group sendiing : sender_id=$sender_id, receiver_id=$receiver_id");

        $success = aspire_student_send_message($sender_id, $receiver_id, $message, $edu_center_id);

    } else {
        // Individual message
        $receiver_id = $target_value;
        error_log("AJAX send: sender_id=$sender_id, receiver_id=$receiver_id");
        $success = aspire_student_send_message($sender_id, $receiver_id, $message, $edu_center_id);
    }

    if ($success) {
        wp_send_json_success(['success' => 'Message sent!']);
    } else {
        wp_send_json_error(['error' => 'Failed to send message']);
    }
}
function aspire_student_send_message($sender_id, $receiver_id, $message, $education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';

    error_log("Student sending message: sender_id=$sender_id, receiver_id=$receiver_id, message=$message, education_center_id=$education_center_id");

    $result = $wpdb->insert(
        $table,
        [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'education_center_id' => $education_center_id,
            'status' => 'sent',
            'timestamp' => current_time('mysql'),
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s']
    );

    if ($result === false) {
        error_log("Insert failed: " . $wpdb->last_error);
    }
    return $result !== false;
}

add_action('wp_ajax_aspire_student_fetch_messages', 'aspire_student_ajax_fetch_messages');
function aspire_student_ajax_fetch_messages() {
    ob_start();
    check_ajax_referer('aspire_student_nonce', 'nonce');

    $user = wp_get_current_user();
    $username = $user->user_login;
    $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');

    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = educational_center_student_id();
    
    $group_types = ['all', 'teachers', 'institute_admins', 'parents'];
    $group_names = [
        'all' => 'Everyone in Center',
        'teachers' => 'Teachers',
        'institute_admins' => 'Institute Admins',
        'parents' => 'Parents'
    ];

    $query = "SELECT * FROM $table WHERE education_center_id = %s";
    $query_args = [$edu_center_id];

    if ($conversation_with) {
        if (in_array($conversation_with, $group_types)) {
            // Fetch group messages
            $query .= " AND (";
            $query .= " (receiver_id = %s)"; // Messages sent to this group
            $query .= " OR (sender_id = %s AND receiver_id = %s)"; // Messages sent by user to this group
            $query .= ")";
            $query .= " LIMIT 50";
            $query_args[] = $conversation_with;
            $query_args[] = $username;
            $query_args[] = $conversation_with;
        } else {
            // Fetch individual conversations
            $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s)) LIMIT 50";
            $query_args[] = $username;
            $query_args[] = $conversation_with;
            $query_args[] = $conversation_with;
            $query_args[] = $username;
        }
    }

    $prepared_query = $wpdb->prepare($query, $query_args);
    $messages = $wpdb->get_results($prepared_query);

    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);

    // Build a map of contact IDs to names
    $contact_map = [];
    foreach ($contacts as $contact) {
        if ($contact->post_type === 'teacher') {
            $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
            $teacher_name = get_post_meta($contact->ID, 'teacher_name', true);
            $label = "$teacher_name ($contact_id - Teacher)";
        } elseif ($contact->post_type === 'students') {
            $contact_id = get_post_meta($contact->ID, 'student_id', true);
            $student_name = get_post_meta($contact->ID, 'student_name', true);
            $label = "$student_name ($contact_id - Student)";
        } else {
            $contact_id = get_post_meta($contact->ID, 'parent_id', true) ?: $contact->post_title;
            $label = "$contact->post_title ($contact_id - Parent)";
        }
        if ($contact_id) {
            $contact_map[$contact_id] = $label;
        }
    }

    $output = '';
    foreach ($messages as $msg) {
        // Handle group sender/receiver names
        if (in_array($msg->receiver_id, $group_types)) {
            $sender_name = ($msg->sender_id === $username) ? 'You' : ($contact_map[$msg->sender_id] ?? (get_user_by('login', $msg->sender_id)->display_name ?? 'Unknown'));
            $receiver_display = "Group: " . ($group_names[$msg->receiver_id] ?? $msg->receiver_id);
        } else {
            $sender_name = ($msg->sender_id === $username) ? 'You' : ($contact_map[$msg->sender_id] ?? (get_user_by('login', $msg->sender_id)->display_name ?? 'Unknown'));
            $receiver_display = $contact_map[$msg->receiver_id] ?? (get_user_by('login', $msg->receiver_id)->display_name ?? 'Unknown');
        }
        
        $initials = strtoupper(substr($sender_name === 'You' ? $user->display_name : $sender_name, 0, 2));
        $output .= '<div class="chat-message ' . ($msg->sender_id === $username ? 'sent' : 'received') . '">';
        $output .= '<div class="bubble">';
        $output .= '<span class="avatar">' . esc_html($initials) . '</span>';
        $output .= '<p>' . esc_html($msg->message) . '</p>';
        $output .= '</div>';
        $timestamp = $msg->timestamp ?? 'N/A';
        $output .= '<div class="meta" data-timestamp="' . esc_attr($timestamp) . '">' . 
                  esc_html($sender_name) . ' to ' . esc_html($receiver_display) . ' - ' . esc_html($timestamp) . 
                  '</div>';
        $output .= '</div>';
    }

    ob_end_clean();
    wp_send_json_success(['html' => $output]);
}

add_action('wp_ajax_aspire_student_fetch_conversations', 'aspire_student_ajax_fetch_conversations');
function aspire_student_ajax_fetch_conversations() {
    ob_start();
    check_ajax_referer('aspire_student_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;

    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = educational_center_student_id();
    
    $group_names = [
        'all' => 'Everyone in Center',
        'teachers' => 'Teachers',
        'institute_admins' => 'Institute Admins',
        'parents' => 'Parents'
    ];

    // Query for both individual and group conversations
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
        AND sender_id NOT IN ('all', 'teachers', 'institute_admins', 'parents')
        UNION
        SELECT DISTINCT receiver_id AS conversation_with 
        FROM $table 
        WHERE education_center_id = %s 
        AND receiver_id IN ('all', 'teachers', 'institute_admins', 'parents')
        AND sender_id != %s
    ";
    $query_args = [$edu_center_id, $username, $edu_center_id, $username, $edu_center_id, $username];
    $active_conversations = $wpdb->get_results($wpdb->prepare($query, $query_args));

    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);

    $contact_map = [];
    foreach ($contacts as $contact) {
        if ($contact->post_type === 'teacher') {
            $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
            $teacher_name = get_post_meta($contact->ID, 'teacher_name', true);
            $label = "$teacher_name ($contact_id - Teacher)";
        } elseif ($contact->post_type === 'students') {
            $contact_id = get_post_meta($contact->ID, 'student_id', true);
            $student_name = get_post_meta($contact->ID, 'student_name', true);
            $label = "$student_name ($contact_id - Student)";
        } else {
            $contact_id = get_post_meta($contact->ID, 'parent_id', true) ?: $contact->post_title;
            $label = "$contact->post_title ($contact_id - Parent)";
        }
        if ($contact_id && !isset($contact_map[$contact_id])) {
            $contact_map[$contact_id] = $label;
        }
    }

    $output = '';
    $has_conversations = false;
    foreach ($active_conversations as $conv) {
        $conv_with = $conv->conversation_with;
        if (isset($group_names[$conv_with])) {
            // Group conversation
            $name = "Group: " . $group_names[$conv_with];
        } else {
            // Individual conversation
            $name = $contact_map[$conv_with] ?? (get_user_by('login', $conv_with)->display_name ?? 'Unknown');
        }
        $output .= '<li class="conversation-item" data-conversation-with="' . esc_attr($conv_with) . '">' . esc_html($name) . '</li>';
        $has_conversations = true;
    }
    
    if (!$has_conversations) {
        $output = '<li class="conversation-item text-muted">No conversations yet.</li>';
    }

    ob_end_clean();
    wp_send_json_success($output);
}

//noticeboard
// Student Notice Board Shortcode
function aspire_student_notice_board_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view the notice board.</p>';
    }
    
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = get_educational_center_data();
    
    if (!aspire_is_student($user)) {
        return '<p>You do not have permission to view this notice board.</p>';
    }

    $announcements = aspire_get_announcements_student($username, 'student');
    $recipients = aspire_get_recipient_options();
    ob_start();
    ?>
    <div id="aspire-student-notice-board" class="notice-board-container">
        <div class="notice-board-header">Student Notice Board</div>
        <div id="announcement-list" class="announcement-list">
            <?php foreach ($announcements as $ann): ?>
                <div class="announcement-item">
                    <div class="announcement-content">
                        <span class="announcement-meta">
                            <?php echo esc_html($ann->sender_id); ?> 
                            to <?php echo esc_html($recipients[$ann->receiver_id] ?? ucfirst($ann->receiver_id)); ?>
                        </span>
                        <p class="announcement-message"><?php echo esc_html($ann->message); ?></p>
                    </div>
                    <span class="announcement-timestamp" data-timestamp="<?php echo esc_attr($ann->timestamp); ?>">
                        <?php echo esc_html($ann->timestamp); ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?>
                <div class="announcement-item">
                    <p class="announcement-message text-muted">No announcements yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $script = "
    jQuery(document).ready(function($) {
        function fetchAnnouncements() {
            $.ajax({
                url: '" . admin_url('admin-ajax.php') . "',
                method: 'POST',
                data: {
                    action: 'aspire_student_fetch_announcements',
                    nonce: '" . wp_create_nonce('aspire_student_notice_nonce') . "'
                },
                success: function(response) {
                    if (response.success) {
                        $('#announcement-list').html(response.data.html);
                        updateTimestamps();
                    } else {
                        console.log('Fetch failed: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX fetch error: ' + error);
                }
            });
        }

        function updateTimestamps() {
            $('.announcement-timestamp').each(function() {
                const timestamp = $(this).data('timestamp');
                if (typeof moment !== 'undefined') {
                    $(this).text(moment(timestamp).fromNow());
                } else {
                    console.log('Moment.js not loaded');
                }
            });
        }

        fetchAnnouncements();
        setInterval(fetchAnnouncements, 30000);
        updateTimestamps();
    });
    ";
    wp_add_inline_script('moment', $script, 'after');
    return ob_get_clean();
}
add_shortcode('aspire_student_notice_board', 'aspire_student_notice_board_shortcode');

// Student AJAX Handler
function aspire_student_fetch_announcements() {
    check_ajax_referer('aspire_student_notice_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;
    $announcements = aspire_get_announcements_student($username, 'student');
    $recipients = aspire_get_recipient_options();

    $output = '';
    foreach ($announcements as $ann) {
        $output .= '<div class="announcement-item">';
        $output .= '<div class="announcement-content">';
        $output .= '<span class="announcement-meta">' . esc_html($ann->sender_id) . ' to ';
        $output .= esc_html($recipients[$ann->receiver_id] ?? ucfirst($ann->receiver_id)) . '</span>';
        $output .= '<p class="announcement-message">' . esc_html($ann->message) . '</p>';
        $output .= '</div>';
        $output .= '<span class="announcement-timestamp" data-timestamp="' . esc_attr($ann->timestamp) . '">' . esc_html($ann->timestamp) . '</span>';
        $output .= '</div>';
    }
    if (empty($announcements)) {
        $output = '<div class="announcement-item"><p class="announcement-message text-muted">No announcements yet.</p></div>';
    }
    wp_send_json_success(['html' => $output]);
}
add_action('wp_ajax_aspire_student_fetch_announcements', 'aspire_student_fetch_announcements');

// Modified shared function to include student role
function aspire_get_announcements_student($username, $role ) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_announcements';
    $edu_center_id = educational_center_student_id();

    $query = "SELECT * FROM $table WHERE education_center_id = %s";
    $query_args = [$edu_center_id];

  
        $query .= " AND receiver_id IN ('all', 'students')";
    

    $query .= " ORDER BY timestamp DESC";
    $prepared_query = $wpdb->prepare($query, $query_args);
    error_log("$role announcements query: $prepared_query");
    $results = $wpdb->get_results($prepared_query);
    error_log("$role announcements for $username: " . print_r($results, true));
    return $results;
}

//library
function student_library_transactions_shortcode($student) {
    global $wpdb;

    // Get the current logged-in user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view your library transactions.</div>';
    }

    $student_id = get_post_meta($student->ID, 'student_id', true);
    

    if (empty($student_id)) {
        return '<div class="alert alert-warning">No student ID associated with your account. Contact support.</div>';
    }

    // Get educational center ID
    $education_center_id = educational_center_student_id();

    // Verify student exists in the 'students' post type
    $student_exists = get_posts([
        'post_type' => 'students',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'student_id',
                'value' => $student_id,
                'compare' => '='
            ],
            [
                'key' => 'educational_center_id',
                'value' => $education_center_id,
                'compare' => '='
            ]
        ]
    ]);

    if (empty($student_exists)) {
        return '<div class="alert alert-warning">Student ID ' . esc_html($student_id) . ' not found or does not belong to this educational center.</div>';
    }

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';

    // Fetch all transactions for the student (no initial filter applied)
    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, l.title 
         FROM $trans_table t 
         JOIN $library_table l ON t.book_id = l.book_id 
         WHERE l.education_center_id = %s AND t.user_id = %s AND t.user_type = 'Student' 
         ORDER BY t.issue_date DESC",
        $education_center_id,
        $student_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #28a745; background: #f4fff4;">
        <div class="card-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Your Library Transactions (Student ID: <?php echo esc_html($student_id); ?>)</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"View all books issued to you, including pending and returned"</p>
                <div class="d-flex align-items-center">
                    <select id="transactionFilter" class="form-select me-2" style="width: 150px;">
                        <option value="all" selected>All Transactions</option>
                        <option value="pending">Pending</option>
                        <option value="returned">Returned</option>
                    </select>
                    <button id="exportCsv" class="btn btn-outline-success">Export to CSV</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="studentTransactionTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6ffe6;">
                        <tr>
                            <th class="sortable" data-sort="book_id">Book ID</th>
                            <th class="sortable" data-sort="title">Title</th>
                            <th class="sortable" data-sort="issue_date">Issue Date</th>
                            <th class="sortable" data-sort="due_date">Due Date</th>
                            <th>Days Remaining/Overdue</th>
                            <th>Return Date</th>
                            <th>Fine (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($transactions)) {
                            echo '<tr><td colspan="7" class="text-center py-4">No transactions found.</td></tr>';
                        } else {
                            $total_fine = 0;
                            foreach ($transactions as $trans) {
                                $is_overdue = !$trans->return_date && strtotime($trans->due_date) < current_time('timestamp');
                                $days_diff = $is_overdue 
                                    ? max(0, (current_time('timestamp') - strtotime($trans->due_date)) / (60 * 60 * 24)) 
                                    : ($trans->return_date ? 0 : max(0, (strtotime($trans->due_date) - current_time('timestamp')) / (60 * 60 * 24)));
                                $fine = $trans->return_date ? $trans->fine : ($is_overdue ? $days_diff * 0.50 : 0);
                                $total_fine += $fine;

                                echo '<tr data-returned="' . ($trans->return_date ? 'true' : 'false') . '">';
                                echo '<td>' . esc_html($trans->book_id) . '</td>';
                                echo '<td>' . esc_html($trans->title) . '</td>';
                                echo '<td>' . esc_html($trans->issue_date) . '</td>';
                                echo '<td>' . esc_html($trans->due_date) . '</td>';
                                echo '<td>';
                                if ($trans->return_date) {
                                    echo '<span class="badge bg-success">Returned</span>';
                                } elseif ($is_overdue) {
                                    echo '<span class="badge bg-danger">Overdue by ' . floor($days_diff) . ' days</span>';
                                } else {
                                    echo '<span class="badge bg-info">' . floor($days_diff) . ' days remaining</span>';
                                }
                                echo '</td>';
                                echo '<td>' . ($trans->return_date ? esc_html($trans->return_date) : '<span class="badge bg-warning">Pending</span>') . '</td>';
                                echo '<td>' . number_format($fine, 2) . ($trans->return_date ? '' : ' (Pending)') . '</td>';
                                echo '</tr>';
                            }
                            // Summary row (always visible)
                            echo '<tr class="table-info" id="totalFineRow">';
                            echo '<td colspan="6" class="text-end fw-bold">Total Fine Owed:</td>';
                            echo '<td id="totalFine">' . number_format($total_fine, 2) . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('studentTransactionTable');
            const tbody = table.querySelector('tbody');
            const filterSelect = document.getElementById('transactionFilter');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#totalFineRow)')); // Exclude summary row
            const totalFineCell = document.getElementById('totalFine');

            // Filter function
            function applyFilter() {
                const filter = filterSelect.value;
                let visibleFineTotal = 0;

                rows.forEach(row => {
                    const isReturned = row.dataset.returned === 'true';
                    let shouldShow = false;

                    if (filter === 'all') {
                        shouldShow = true;
                    } else if (filter === 'pending' && !isReturned) {
                        shouldShow = true;
                    } else if (filter === 'returned' && isReturned) {
                        shouldShow = true;
                    }

                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) {
                        const fineText = row.cells[6].textContent.split(' ')[0]; // Extract numeric fine
                        visibleFineTotal += parseFloat(fineText) || 0;
                    }
                });

                // Update total fine
                totalFineCell.textContent = visibleFineTotal.toFixed(2);
            }

            // Apply filter on change
            filterSelect.addEventListener('change', applyFilter);

            // Initial filter application
            applyFilter();

            // Table Sorting
            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);

                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    visibleRows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                    });

                    visibleRows.forEach(row => tbody.insertBefore(row, tbody.querySelector('#totalFineRow')));
                });
            });

            // Export to CSV
            document.getElementById('exportCsv').addEventListener('click', function() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                const allRows = [...visibleRows, tbody.querySelector('#totalFineRow')];
                const csvContent = allRows.map(row => {
                    const cols = Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
                    return cols.join(',');
                }).join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'student_<?php echo esc_js($student_id); ?>_transactions.csv';
                link.click();
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('student_library_transactions', 'student_library_transactions_shortcode');


function get_educational_center_data_student($educational_center_id) {
    $current_user = wp_get_current_user();
    // $educational_center_id = educational_center_student_id(); 

    $args = array(
        'post_type' => 'educational-center',
        'meta_query' => array(
            array(
                'key' => 'educational_center_id',
                'value' => $educational_center_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $education_center_id = get_field('educational_center_id', $post_id) ?: $post_id;
        $institute_name = get_the_title($post_id);
        $logo = get_field('institute_logo', $post_id);
        $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
        wp_reset_postdata();
        return array(
            'id' => $education_center_id,
            'name' => $institute_name,
            'logo_url' => $logo_url
        );
    }
    return false;
}

//fees
function student_fees_shortcode($student) {
    global $wpdb;

    // Get the current logged-in user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view your fee details.</div>';
    }

    $student_id = get_post_meta($student->ID, 'student_id', true);


    if (empty($student_id)) {
        return '<div class="alert alert-warning">No student ID associated with your account. Contact support.</div>';
    }

    // Get educational center ID and details
    $education_center_id = educational_center_student_id();
    $center_data = get_educational_center_data_student($education_center_id);

    if (!$center_data) {
        return '<div class="alert alert-warning">Educational center data not found.</div>';
    }

    $institute_name = $center_data['name'];
    $institute_logo = $center_data['logo_url']; // Web-accessible URL

    // Verify student exists
    $student_exists = get_posts([
        'post_type' => 'students',
        'posts_per_page' => 1,
        'meta_query' => [
            ['key' => 'student_id', 'value' => $student_id, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $education_center_id, 'compare' => '=']
        ]
    ]);

    if (empty($student_exists)) {
        return '<div class="alert alert-warning">Student ID ' . esc_html($student_id) . ' not found or does not belong to this educational center.</div>';
    }

    $student_post = $student_exists[0];
    $student_name = get_the_title($student_post->ID);
    $student_class = get_post_meta($student_post->ID, 'class', true) ?: 'Unknown Class';
    $student_section = get_post_meta($student_post->ID, 'section', true) ?: 'Unknown Section';
    $admission_date = get_post_meta($student_post->ID, 'admission_date', true) ?: date('Y-m');

    $table_name = $wpdb->prefix . 'student_fees';
    $template_table = $wpdb->prefix . 'fee_templates';

    // Fetch fees
    $fees = $wpdb->get_results($wpdb->prepare(
        "SELECT sf.*, ft.name AS template_name 
         FROM $table_name sf 
         LEFT JOIN $template_table ft ON sf.template_id = ft.id 
         WHERE sf.education_center_id = %s AND sf.student_id = %s 
         ORDER BY sf.month_year",
        $education_center_id,
        $student_id
    ));

    // Pending months calculation
    $current_date = new DateTime(date('Y-m-01'));
    $admission_date_obj = new DateTime(date('Y-m-01', strtotime($admission_date)));
    $pending_months = [];
    $paid_months = [];
    $interval = DateInterval::createFromDateString('1 month');
    $period = new DatePeriod($admission_date_obj, $interval, $current_date);

    foreach ($fees as $fee) {
        if ($fee->status === 'paid') {
            $paid_months[] = $fee->month_year;
        }
    }

    foreach ($period as $dt) {
        $month_year = $dt->format('Y-m');
        if (!in_array($month_year, $paid_months)) {
            $pending_months[] = $month_year;
        }
    }

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #28a745; background: #f4fff4;">
        <div class="card-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-wallet me-2"></i>Your Fee Details (Student ID: <?php echo esc_html($student_id); ?>)</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"View your fee status for <?php echo esc_html($student_name); ?> (Class: <?php echo esc_html($student_class . ' - ' . $student_section); ?>)"</p>
                <div class="d-flex align-items-center">
                    <select id="feeFilter" class="form-select me-2" style="width: 150px;">
                        <option value="all" selected>All Fees</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    <button id="exportCsv" class="btn btn-outline-success me-2">Export to CSV</button>
                    <button id="downloadAllReceipts" class="btn btn-primary">Download All Receipts</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="studentFeesTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6ffe6;">
                        <tr>
                            <th class="sortable" data-sort="month_year">Month</th>
                            <th class="sortable" data-sort="template_name">Fee Type</th>
                            <th class="sortable" data-sort="amount">Amount</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th>Payment Method</th>
                            <th>Paid Date</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($fees)) {
                            echo '<tr><td colspan="7" class="text-center py-4">No fee records found.</td></tr>';
                        } else {
                            $total_amount = 0;
                            $total_paid = 0;
                            foreach ($fees as $index => $fee) {
                                $total_amount += floatval($fee->amount);
                                if ($fee->status === 'paid') {
                                    $total_paid += floatval($fee->amount);
                                }
                                echo '<tr data-status="' . esc_attr($fee->status) . '">';
                                echo '<td>' . esc_html(date('F Y', strtotime($fee->month_year))) . '</td>';
                                echo '<td>' . esc_html($fee->template_name ?: 'N/A') . '</td>';
                                echo '<td>' . esc_html(number_format(floatval($fee->amount), 2)) . '</td>';
                                echo '<td><span class="badge bg-' . ($fee->status === 'paid' ? 'success' : ($fee->status === 'pending' ? 'warning' : 'danger')) . '">' . esc_html(ucfirst($fee->status)) . '</span></td>';
                                echo '<td>' . esc_html($fee->payment_method ?: 'N/A') . '</td>';
                                echo '<td>' . esc_html($fee->paid_date ?: 'N/A') . '</td>';
                                echo '<td>';
                                if ($fee->status === 'paid') {
                                    echo '<button class="download-receipt-btn btn btn-sm btn-primary" data-index="' . $index . '">Download</button>';
                                } else {
                                    echo 'N/A';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '<tr class="table-info" id="totalRow">';
                            echo '<td colspan="2" class="text-end fw-bold">Total Amount:</td>';
                            echo '<td>' . esc_html(number_format($total_amount, 2)) . '</td>';
                            echo '<td colspan="2" class="text-end fw-bold">Total Paid:</td>';
                            echo '<td id="totalPaid">' . esc_html(number_format($total_paid, 2)) . '</td>';
                            echo '<td></td>';
                            echo '</tr>';
                            echo '<tr class="table-warning" id="pendingRow">';
                            echo '<td colspan="7">';
                            echo '<div class="pending-months-toggle">';
                            echo '<strong>Pending Months (' . count($pending_months) . '):</strong> ';
                            if (empty($pending_months)) {
                                echo 'None';
                            } else {
                                echo '<button class="toggle-pending-btn btn btn-link p-0 ml-2" style="text-decoration: none;">Show</button>';
                                echo '<div class="pending-months-list" style="display: none; margin-top: 5px;">';
                                foreach ($pending_months as $month) {
                                    echo '<div>' . esc_html(date('F Y', strtotime($month))) . '</div>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('studentFeesTable');
            const tbody = table.querySelector('tbody');
            const filterSelect = document.getElementById('feeFilter');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#totalRow):not(#pendingRow)'));
            const totalPaidCell = document.getElementById('totalPaid');

            // Filter function
            function applyFilter() {
                const filter = filterSelect.value;
                let visiblePaidTotal = 0;
                rows.forEach(row => {
                    const status = row.dataset.status;
                    let shouldShow = filter === 'all' || filter === status;
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow && status === 'paid') {
                        visiblePaidTotal += parseFloat(row.cells[2].textContent.replace(/[^0-9.]/g, '')) || 0;
                    }
                });
                totalPaidCell.textContent = visiblePaidTotal.toFixed(2);
            }
            filterSelect.addEventListener('change', applyFilter);
            applyFilter();

            // Table Sorting
            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);
                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    visibleRows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue, { numeric: true }) : bValue.localeCompare(aValue, { numeric: true });
                    });
                    visibleRows.forEach(row => tbody.insertBefore(row, tbody.querySelector('#totalRow')));
                });
            });

            // Toggle Pending Months
            const toggleBtn = document.querySelector('.toggle-pending-btn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const list = document.querySelector('.pending-months-list');
                    const isVisible = list.style.display === 'block';
                    list.style.display = isVisible ? 'none' : 'block';
                    this.textContent = isVisible ? 'Show' : 'Hide';
                });
            }

            // Export to CSV
            document.getElementById('exportCsv').addEventListener('click', function() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                const allRows = [...visibleRows, tbody.querySelector('#totalRow'), tbody.querySelector('#pendingRow')];
                const csvContent = [
                    '"Month","Fee Type","Amount","Status","Payment Method","Paid Date","Receipt"',
                    ...allRows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'student_<?php echo esc_js($student_id); ?>_fees.csv';
                link.click();
            });

            // Helper function to style and add PDF content
            function generateReceipt(doc, isSingle = false, row = null) {
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';
                const pageWidth = doc.internal.pageSize.width;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10; // 10mm margin
                const borderColor = [26, 43, 95]; // #1a2b5f

                // Page border
                doc.setDrawColor(...borderColor);
                doc.setLineWidth(1); // 4px equivalent in mm
                doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

                // Header
                if (logoUrl) {
                    try {
                        doc.addImage(logoUrl, 'PNG', (pageWidth - 24) / 2, 15, 24, 24); // 60px = ~24mm
                    } catch (e) {
                        console.log('Logo loading failed:', e);
                        doc.setFontSize(10);
                        doc.text('No logo available', (pageWidth - 20) / 2, 20, { align: 'center' });
                    }
                }
                doc.setFontSize(18);
                doc.setTextColor(...borderColor);
                doc.text('<?php echo esc_js(strtoupper($institute_name)); ?>', pageWidth / 2, 45, { align: 'center' });
                doc.setFontSize(12);
                doc.setTextColor(102); // #666
                doc.text('Fee Receipt', pageWidth / 2, 55, { align: 'center' });
                doc.setDrawColor(...borderColor);
                doc.line(margin + 5, 60, pageWidth - margin - 5, 60); // Border-bottom equivalent

                // Details Table
                const details = [
                    ['Student Name', '<?php echo esc_js($student_name); ?>'],
                    ['Student ID', '<?php echo esc_js($student_id); ?>'],
                    ['Class/Course', '<?php echo esc_js($student_class . ' - ' . $student_section); ?>'],
                    ['Date', '<?php echo date('Y-m-d'); ?>'],
                    ...(isSingle ? [['Month', row.cells[0].textContent.trim()]] : [])
                ];
                let y = 70;
                details.forEach(([label, value]) => {
                    doc.setFillColor(245, 245, 245); // #f5f5f5
                    doc.rect(margin + 5, y, 50, 6, 'F');
                    doc.setTextColor(...borderColor);
                    doc.setFont('helvetica', 'bold');
                    doc.text(label, margin + 7, y + 4);
                    doc.setTextColor(51); // #333
                    doc.setFont('helvetica', 'normal');
                    doc.text(value, margin + 60, y + 4);
                    y += 6;
                });

                // Fees Table
                const body = isSingle
                    ? [Array.from(row.cells).slice(0, 6).map(cell => cell.textContent.trim())]
                    : rows.filter(r => r.dataset.status === 'paid').map(r => Array.from(r.cells).slice(0, 6).map(cell => cell.textContent.trim()));
                if (!isSingle) {
                    body.push(['Total Paid', '', '<?php echo esc_js(number_format($total_paid, 2)); ?>', '', '', '']);
                }
                doc.autoTable({
                    startY: y + 10,
                    head: [['Month', 'Fee Type', 'Amount', 'Status', 'Payment Method', 'Paid Date']],
                    body: body,
                    theme: 'striped',
                    styles: {
                        fontSize: 11,
                        cellPadding: 2,
                        overflow: 'linebreak',
                        halign: 'center',
                        textColor: [51, 51, 51] // #333
                    },
                    headStyles: {
                        fillColor: borderColor,
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: { fillColor: [249, 249, 249] }, // #f9f9f9
                    didParseCell: function(data) {
                        if (!isSingle && data.row.index === body.length - 1) {
                            data.cell.styles.fillColor = [230, 240, 250]; // #e6f0fa
                            data.cell.styles.fontStyle = 'bold';
                        }
                    }
                });

                // Footer
                const finalY = doc.lastAutoTable.finalY || y + 10;
                doc.setFontSize(9);
                doc.setTextColor(102); // #666
                doc.text(`This is an Online Generated Receipt issued by <?php echo esc_js($institute_name); ?>`, pageWidth / 2, finalY + 20, { align: 'center' });
                doc.text('Generated on <?php echo date('Y-m-d'); ?>', pageWidth / 2, finalY + 25, { align: 'center' });
                doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });
            }

            // Download All Paid Receipts
            document.getElementById('downloadAllReceipts').addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                generateReceipt(doc);
                doc.save('fee_receipt_<?php echo esc_js($student_id); ?>_<?php echo date('Ymd'); ?>.pdf');
            });

            // Individual Receipt Download
            document.querySelectorAll('.download-receipt-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = this.dataset.index;
                    const row = rows[index];
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                    generateReceipt(doc, true, row);
                    const month = row.cells[0].textContent.trim().replace(/\s+/g, '_');
                    doc.save(`fee_receipt_<?php echo esc_js($student_id); ?>_${month}.pdf`);
                });
            });
        });
    </script>
    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <?php
    return ob_get_clean();
}
add_shortcode('student_fees', 'student_fees_shortcode');

//transport fees
function student_transport_fees_shortcode($student) {
    global $wpdb;

    // Get the current logged-in user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view your transport fee details.</div>';
    }

    $student_id = get_post_meta($student->ID, 'student_id', true);


    // Get educational center ID and details
    $education_center_id = educational_center_student_id(); // Assuming this returns the student's center ID
    $center_data = get_educational_center_data_student($education_center_id);

    if (!$center_data) {
        return '<div class="alert alert-warning">Educational center data not found.</div>';
    }

    $institute_name = $center_data['name'];
    $institute_logo = $center_data['logo_url']; // Web-accessible URL

    // Verify student exists
    $student_exists = get_posts([
        'post_type' => 'students',
        'posts_per_page' => 1,
        'meta_query' => [
            ['key' => 'student_id', 'value' => $student_id, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $education_center_id, 'compare' => '=']
        ]
    ]);

    if (empty($student_exists)) {
        return '<div class="alert alert-warning">Student ID ' . esc_html($student_id) . ' not found or does not belong to this educational center.</div>';
    }

    $student_post = $student_exists[0];
    $student_name = get_the_title($student_post->ID);
    $student_class = get_post_meta($student_post->ID, 'class', true) ?: 'Unknown Class';
    $student_section = get_post_meta($student_post->ID, 'section', true) ?: 'Unknown Section';
    $enrollment_date = $wpdb->get_var($wpdb->prepare(
        "SELECT enrollment_date FROM {$wpdb->prefix}transport_enrollments WHERE student_id = %s AND education_center_id = %s ORDER BY enrollment_date ASC LIMIT 1",
        $student_id,
        $education_center_id
    )) ?: date('Y-m');

    $table_name = $wpdb->prefix . 'transport_fees';
    $template_table = $wpdb->prefix . 'fee_templates';

    // Fetch transport fees for this student
    $fees = $wpdb->get_results($wpdb->prepare(
        "SELECT tf.*, ft.name AS template_name 
         FROM $table_name tf 
         LEFT JOIN $template_table ft ON tf.template_id = ft.id 
         WHERE tf.education_center_id = %s AND tf.student_id = %s 
         ORDER BY tf.month_year",
        $education_center_id,
        $student_id
    ));

    // Pending months calculation
    $current_date = new DateTime(date('Y-m-01'));
    $enrollment_date_obj = new DateTime(date('Y-m-01', strtotime($enrollment_date)));
    $pending_months = [];
    $paid_months = [];
    $interval = DateInterval::createFromDateString('1 month');
    $period = new DatePeriod($enrollment_date_obj, $interval, $current_date);

    foreach ($fees as $fee) {
        if ($fee->status === 'paid') {
            $paid_months[] = $fee->month_year;
        }
    }

    foreach ($period as $dt) {
        $month_year = $dt->format('Y-m');
        if (!in_array($month_year, $paid_months)) {
            $pending_months[] = $month_year;
        }
    }

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #28a745; background: #f4fff4;">
        <div class="card-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-bus-front me-2"></i>Your Transport Fee Details (Student ID: <?php echo esc_html($student_id); ?>)</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"View your transport fee status for <?php echo esc_html($student_name); ?> (Class: <?php echo esc_html($student_class . ' - ' . $student_section); ?>)"</p>
                <div class="d-flex align-items-center">
                    <select id="feeFilter" class="form-select me-2" style="width: 150px;">
                        <option value="all" selected>All Fees</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    <button id="exportCsv" class="btn btn-outline-success me-2">Export to CSV</button>
                    <button id="downloadAllReceipts" class="btn btn-primary">Download All Receipts</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="transportFeesTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6ffe6;">
                        <tr>
                            <th class="sortable" data-sort="month_year">Month</th>
                            <th class="sortable" data-sort="template_name">Fee Type</th>
                            <th class="sortable" data-sort="amount">Amount</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th>Payment Method</th>
                            <th>Paid Date</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($fees)) {
                            echo '<tr><td colspan="7" class="text-center py-4">No transport fee records found.</td></tr>';
                        } else {
                            $total_amount = 0;
                            $total_paid = 0;
                            foreach ($fees as $index => $fee) {
                                $total_amount += floatval($fee->amount);
                                if ($fee->status === 'paid') {
                                    $total_paid += floatval($fee->amount);
                                }
                                echo '<tr data-status="' . esc_attr($fee->status) . '">';
                                echo '<td>' . esc_html(date('F Y', strtotime($fee->month_year))) . '</td>';
                                echo '<td>' . esc_html($fee->template_name ?: 'N/A') . '</td>';
                                echo '<td>' . esc_html(number_format(floatval($fee->amount), 2)) . '</td>';
                                echo '<td><span class="badge bg-' . ($fee->status === 'paid' ? 'success' : ($fee->status === 'pending' ? 'warning' : 'danger')) . '">' . esc_html(ucfirst($fee->status)) . '</span></td>';
                                echo '<td>' . esc_html($fee->payment_method ?: 'N/A') . '</td>';
                                echo '<td>' . esc_html($fee->paid_date ?: 'N/A') . '</td>';
                                echo '<td>';
                                if ($fee->status === 'paid') {
                                    echo '<button class="download-receipt-btn btn btn-sm btn-primary" data-index="' . $index . '">Download</button>';
                                } else {
                                    echo 'N/A';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '<tr class="table-info" id="totalRow">';
                            echo '<td colspan="2" class="text-end fw-bold">Total Amount:</td>';
                            echo '<td>' . esc_html(number_format($total_amount, 2)) . '</td>';
                            echo '<td colspan="2" class="text-end fw-bold">Total Paid:</td>';
                            echo '<td id="totalPaid">' . esc_html(number_format($total_paid, 2)) . '</td>';
                            echo '<td></td>';
                            echo '</tr>';
                            echo '<tr class="table-warning" id="pendingRow">';
                            echo '<td colspan="7">';
                            echo '<div class="pending-months-toggle">';
                            echo '<strong>Pending Months (' . count($pending_months) . '):</strong> ';
                            if (empty($pending_months)) {
                                echo 'None';
                            } else {
                                echo '<button class="toggle-pending-btn btn btn-link p-0 ml-2" style="text-decoration: none;">Show</button>';
                                echo '<div class="pending-months-list" style="display: none; margin-top: 5px;">';
                                foreach ($pending_months as $month) {
                                    echo '<div>' . esc_html(date('F Y', strtotime($month))) . '</div>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('transportFeesTable');
            const tbody = table.querySelector('tbody');
            const filterSelect = document.getElementById('feeFilter');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#totalRow):not(#pendingRow)'));
            const totalPaidCell = document.getElementById('totalPaid');

            // Filter function
            function applyFilter() {
                const filter = filterSelect.value;
                let visiblePaidTotal = 0;
                rows.forEach(row => {
                    const status = row.dataset.status;
                    let shouldShow = filter === 'all' || filter === status;
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow && status === 'paid') {
                        visiblePaidTotal += parseFloat(row.cells[2].textContent.replace(/[^0-9.]/g, '')) || 0;
                    }
                });
                totalPaidCell.textContent = visiblePaidTotal.toFixed(2);
            }
            filterSelect.addEventListener('change', applyFilter);
            applyFilter();

            // Table Sorting
            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);
                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    visibleRows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue, { numeric: true }) : bValue.localeCompare(aValue, { numeric: true });
                    });
                    visibleRows.forEach(row => tbody.insertBefore(row, tbody.querySelector('#totalRow')));
                });
            });

            // Toggle Pending Months
            const toggleBtn = document.querySelector('.toggle-pending-btn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const list = document.querySelector('.pending-months-list');
                    const isVisible = list.style.display === 'block';
                    list.style.display = isVisible ? 'none' : 'block';
                    this.textContent = isVisible ? 'Show' : 'Hide';
                });
            }

            // Export to CSV
            document.getElementById('exportCsv').addEventListener('click', function() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                const allRows = [...visibleRows, tbody.querySelector('#totalRow'), tbody.querySelector('#pendingRow')];
                const csvContent = [
                    '"Month","Fee Type","Amount","Status","Payment Method","Paid Date","Receipt"',
                    ...allRows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'transport_fees_<?php echo esc_js($student_id); ?>.csv';
                link.click();
            });

            // Helper function to style and add PDF content
            function generateReceipt(doc, isSingle = false, row = null) {
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';
                const pageWidth = doc.internal.pageSize.width;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10; // 10mm margin
                const borderColor = [26, 43, 95]; // #1a2b5f

                // Page border
                doc.setDrawColor(...borderColor);
                doc.setLineWidth(1); // 4px equivalent in mm
                doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

                // Header
                if (logoUrl) {
                    try {
                        doc.addImage(logoUrl, 'PNG', (pageWidth - 24) / 2, 15, 24, 24); // 60px = ~24mm
                    } catch (e) {
                        console.log('Logo loading failed:', e);
                        doc.setFontSize(10);
                        doc.text('No logo available', (pageWidth - 20) / 2, 20, { align: 'center' });
                    }
                }
                doc.setFontSize(18);
                doc.setTextColor(...borderColor);
                doc.text('<?php echo esc_js(strtoupper($institute_name)); ?>', pageWidth / 2, 45, { align: 'center' });
                doc.setFontSize(12);
                doc.setTextColor(102); // #666
                doc.text('Transport Fee Receipt', pageWidth / 2, 55, { align: 'center' });
                doc.setDrawColor(...borderColor);
                doc.line(margin + 5, 60, pageWidth - margin - 5, 60); // Border-bottom equivalent

                // Details Table
                const details = [
                    ['Student Name', '<?php echo esc_js($student_name); ?>'],
                    ['Student ID', '<?php echo esc_js($student_id); ?>'],
                    ['Class/Course', '<?php echo esc_js($student_class . ' - ' . $student_section); ?>'],
                    ['Date', '<?php echo date('Y-m-d'); ?>'],
                    ...(isSingle ? [['Month', row.cells[0].textContent.trim()]] : [])
                ];
                let y = 70;
                details.forEach(([label, value]) => {
                    doc.setFillColor(245, 245, 245); // #f5f5f5
                    doc.rect(margin + 5, y, 50, 6, 'F');
                    doc.setTextColor(...borderColor);
                    doc.setFont('helvetica', 'bold');
                    doc.text(label, margin + 7, y + 4);
                    doc.setTextColor(51); // #333
                    doc.setFont('helvetica', 'normal');
                    doc.text(value, margin + 60, y + 4);
                    y += 6;
                });

                // Fees Table
                const body = isSingle
                    ? [Array.from(row.cells).slice(0, 6).map(cell => cell.textContent.trim())]
                    : rows.filter(r => r.dataset.status === 'paid').map(r => Array.from(r.cells).slice(0, 6).map(cell => cell.textContent.trim()));
                if (!isSingle) {
                    body.push(['Total Paid', '', '<?php echo esc_js(number_format($total_paid, 2)); ?>', '', '', '']);
                }
                doc.autoTable({
                    startY: y + 10,
                    head: [['Month', 'Fee Type', 'Amount', 'Status', 'Payment Method', 'Paid Date']],
                    body: body,
                    theme: 'striped',
                    styles: {
                        fontSize: 11,
                        cellPadding: 2,
                        overflow: 'linebreak',
                        halign: 'center',
                        textColor: [51, 51, 51] // #333
                    },
                    headStyles: {
                        fillColor: borderColor,
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: { fillColor: [249, 249, 249] }, // #f9f9f9
                    didParseCell: function(data) {
                        if (!isSingle && data.row.index === body.length - 1) {
                            data.cell.styles.fillColor = [230, 240, 250]; // #e6f0fa
                            data.cell.styles.fontStyle = 'bold';
                        }
                    }
                });

                // Footer
                const finalY = doc.lastAutoTable.finalY || y + 10;
                doc.setFontSize(9);
                doc.setTextColor(102); // #666
                doc.text(`This is an Online Generated Receipt issued by <?php echo esc_js($institute_name); ?>`, pageWidth / 2, finalY + 20, { align: 'center' });
                doc.text('Generated on <?php echo date('Y-m-d'); ?>', pageWidth / 2, finalY + 25, { align: 'center' });
                doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });
            }

            // Download All Paid Receipts
            document.getElementById('downloadAllReceipts').addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                generateReceipt(doc);
                doc.save('transport_fee_receipt_<?php echo esc_js($student_id); ?>_<?php echo date('Ymd'); ?>.pdf');
            });

            // Individual Receipt Download
            document.querySelectorAll('.download-receipt-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = this.dataset.index;
                    const row = rows[index];
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                    generateReceipt(doc, true, row);
                    const month = row.cells[0].textContent.trim().replace(/\s+/g, '_');
                    doc.save(`transport_fee_receipt_<?php echo esc_js($student_id); ?>_${month}.pdf`);
                });
            });
        });
    </script>
    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <!-- Bootstrap Icons for UI -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php
    return ob_get_clean();
}
add_shortcode('student_transport_fees', 'student_transport_fees_shortcode');

//inventory
function student_inventory_transactions_shortcode($student) {
    global $wpdb;

    // Get the current logged-in user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view your inventory transactions.</div>';
    }

    $student_id = get_post_meta($student->ID, 'student_id', true);
    
    if (empty($student_id)) {
        return '<div class="alert alert-warning">No student ID associated with your account. Contact support.</div>';
    }

    // Get educational center ID
    $education_center_id = educational_center_student_id();

    // Verify student exists in the 'students' post type
    $student_exists = get_posts([
        'post_type' => 'students',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'student_id',
                'value' => $student_id,
                'compare' => '='
            ],
            [
                'key' => 'educational_center_id',
                'value' => $education_center_id,
                'compare' => '='
            ]
        ]
    ]);

    if (empty($student_exists)) {
        return '<div class="alert alert-warning">Student ID ' . esc_html($student_id) . ' not found or does not belong to this educational center.</div>';
    }

    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $inventory_table = $wpdb->prefix . 'inventory';

    // Fetch all transactions for the student
    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, i.name 
         FROM $trans_table t 
         JOIN $inventory_table i ON t.item_id = i.item_id 
         WHERE i.education_center_id = %s AND t.user_id = %s AND t.user_type = 'Student' 
         ORDER BY t.date DESC",
        $education_center_id,
        $student_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #17a2b8; background: #f0faff;">
        <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i>Your Inventory Transactions (Student ID: <?php echo esc_html($student_id); ?>)</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"View all items issued to you, including pending and returned"</p>
                <div class="d-flex align-items-center">
                    <select id="transactionFilter" class="form-select me-2" style="width: 150px;">
                        <option value="all" selected>All Transactions</option>
                        <option value="pending">Pending</option>
                        <option value="returned">Returned</option>
                        <option value="issued">Issued</option>
                    </select>
                    <button id="exportCsv" class="btn btn-outline-info">Export to CSV</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="studentInventoryTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6f3ff;">
                        <tr>
                            <th class="sortable" data-sort="item_id">Item ID</th>
                            <th class="sortable" data-sort="name">Item Name</th>
                            <th class="sortable" data-sort="action">Action</th>
                            <th class="sortable" data-sort="date">Transaction Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($transactions)) {
                            echo '<tr><td colspan="5" class="text-center py-4">No inventory transactions found.</td></tr>';
                        } else {
                            foreach ($transactions as $trans) {
                                $status_badge = $trans->status === 'Completed' ? 'bg-success' : 'bg-warning';
                                $is_returned = $trans->action === 'Return' && $trans->status === 'Completed';
                                $is_issued = $trans->action === 'Issue' && $trans->status === 'Completed' && !$wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM $trans_table WHERE item_id = %s AND user_id = %s AND action = 'Return' AND status = 'Completed'",
                                    $trans->item_id,
                                    $student_id
                                ));

                                echo '<tr data-status="' . esc_attr($trans->status) . '" data-action="' . esc_attr($trans->action) . '" data-returned="' . ($is_returned ? 'true' : 'false') . '" data-issued="' . ($is_issued ? 'true' : 'false') . '">';
                                echo '<td>' . esc_html($trans->item_id) . '</td>';
                                echo '<td>' . esc_html($trans->name) . '</td>';
                                echo '<td>' . esc_html($trans->action) . '</td>';
                                echo '<td>' . esc_html($trans->date) . '</td>';
                                echo '<td><span class="badge ' . $status_badge . '">' . esc_html($trans->status) . '</span></td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('studentInventoryTable');
            const tbody = table.querySelector('tbody');
            const filterSelect = document.getElementById('transactionFilter');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Filter function
            function applyFilter() {
                const filter = filterSelect.value;

                rows.forEach(row => {
                    const status = row.dataset.status;
                    const action = row.dataset.action;
                    const isReturned = row.dataset.returned === 'true';
                    const isIssued = row.dataset.issued === 'true';
                    let shouldShow = false;

                    if (filter === 'all') {
                        shouldShow = true;
                    } else if (filter === 'pending' && status === 'Pending') {
                        shouldShow = true;
                    } else if (filter === 'returned' && isReturned) {
                        shouldShow = true;
                    } else if (filter === 'issued' && isIssued) {
                        shouldShow = true;
                    }

                    row.style.display = shouldShow ? '' : 'none';
                });
            }

            // Apply filter on change
            filterSelect.addEventListener('change', applyFilter);

            // Initial filter application
            applyFilter();

            // Table Sorting
            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);

                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    visibleRows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue, { numeric: sortKey === 'item_id' }) : bValue.localeCompare(aValue, { numeric: sortKey === 'item_id' });
                    });

                    visibleRows.forEach(row => tbody.appendChild(row));
                });
            });

            // Export to CSV
            document.getElementById('exportCsv').addEventListener('click', function() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                const csvContent = [
                    '"Item ID","Item Name","Action","Transaction Date","Status"',
                    ...visibleRows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'student_<?php echo esc_js($student_id); ?>_inventory_transactions.csv';
                link.click();
            });
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php
    return ob_get_clean();
}
add_shortcode('student_inventory_transactions', 'student_inventory_transactions_shortcode');