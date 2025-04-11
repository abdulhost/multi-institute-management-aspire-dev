<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Function to check if user is a parent
function is_parent($user_id) {
    $user = get_user_by('id', $user_id);
    return $user && in_array('parent', (array)$user->roles);
}

function educational_center_parent_id() {
    // if (!is_user_logged_in()) {
    //     return false;
    //     // wp_redirect(home_url('/login'));
    //     // exit();  
    // }

    $current_user = wp_get_current_user();
    if (!in_array('parent', (array)$current_user->roles)) {
        return false;
        // wp_redirect(home_url('/login'));
        // exit();  
    }

    return get_user_meta($current_user->ID, 'educational_center_id', true) ?: false;
}

// Main Parent Dashboard Shortcode
function aspire_parent_dashboard_shortcode() {
    global $wpdb;
    
    if (!function_exists('wp_get_current_user')) {
        return '<div class="alert alert-danger">Some Error occured</div>';
    }
    error_log("parent dash");
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $username = $current_user->user_login;

    if (!$current_user->ID || !is_parent($current_user->ID)) {
        // wp_redirect(home_url('/login'));
        // exit();  
        return '<div class="alert alert-danger">Some Error occured</div>';
    }
    $edu_center_id = educational_center_parent_id();
    $parent_posts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.* 
             FROM $wpdb->posts p 
             LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'parent' 
             AND p.post_status = 'publish' 
             AND ((pm.meta_key = 'parent_id' AND pm.meta_value = %d) 
             OR p.post_title = %s) 
             LIMIT 1",
            $user_id,
            $username
        )
    );

    if (!$parent_posts) {
        return '<div class="alert alert-warning">Profile not found. Please contact administration.</div>';
    }

    $parent_post = get_post($parent_posts[0]->ID);
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #f0f8ff, #e6e6fa); min-height: 100vh;">
        <div class="row">
            <?php 
            // echo render_parent_header($current_user, $parent_post);
            
            $active_section = $section;
            $active_action = $action;
            echo render_parent_header($parent_post->ID); 
              if (!is_center_subscribed($edu_center_id)) {
                return render_subscription_expired_message($edu_center_id);
            } 
            include plugin_dir_path(__FILE__) . 'parent-sidebar.php';
            ?>
            
            <div class="main-content">
                <?php
                ob_start();
              
                switch ($section) {
                    case 'overview':
                        echo render_parent_overview($current_user, $parent_post->ID);
                        break;
                    case 'profile':
                        // Placeholder for render_parent_profile if it doesn't exist
                        if (function_exists('render_parent_profile')) {
                            echo render_parent_profile($current_user, $parent_post->ID);
                        } else {
                            echo '<div class="alert alert-warning">Profile section not implemented yet.</div>';
                        }
                        break;
                    case 'child_selection':
                        echo render_child_selection($current_user, $parent_post->ID);
                            break;
                            case 'detailed_attendance':
                                echo render_child_attendance($current_user, $parent_post->ID);
                                break;
                            
                            case 'timetable':
                                echo render_child_timetable($current_user, $parent_post->ID);
                                break;
                                case 'exams':
                                    echo render_child_exams($current_user, $parent_post->ID);
                                    break;
                                case 'results':
                                    echo render_child_results($current_user, $parent_post->ID);
                                    break;
                                case 'fees':
                                    echo render_parent_fees($current_user,  $parent_post->ID);break;
                                case 'parent_calendar':
                                    echo render_parent_calendar($current_user,  $parent_post->ID);break;
                                case 'transport_fees':
                                    echo render_parent_transport_fees($current_user,  $parent_post->ID);
                                    break;
                                    case 'notice_board':
                                        echo render_parent_notice_board($current_user, $parent_post->ID);
                                        break;
                                        case 'communication':
                                            echo render_parent_communication($current_user, $parent_post->ID);
                                            break;
                                        case 'change_password':
                                            echo render_change_password($current_user,  $parent_post->ID);
                                                                                        break;
                                        
                                        case 'homework':
                                            echo render_parent_homework($current_user,  $parent_post->ID);
                                                                                        break;
                                        case 'library':
                                            echo render_parent_library($current_user,  $parent_post->ID);
                                                                                        break;
                                        
                    default:
                        echo render_parent_overview($current_user, $parent_post->ID);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_parent_dashboard', 'aspire_parent_dashboard_shortcode');

//
function render_parent_header2($parent_user, $parent) {
    global $wpdb;

    // Use parent post data for name and profile photo
    $user_name = get_field('parent_name', $parent->ID) ?: "Parent"; // Parent name from ACF field
    $user_email = $parent_user->user_email; // Email from WP_User

    // Get the parent profile photo (assuming it’s an ACF image array)
    $avatar_data = get_field('parent_profile_photo', $parent->ID);
    $avatar_url = $avatar_data ? esc_url($avatar_data['url']) : 'https://via.placeholder.com/150';

    $dashboard_link = esc_url(home_url('/parent-dashboard'));
    $notifications_link = esc_url(home_url('/parent-dashboard?section=notifications'));
    $settings_link = esc_url(home_url('/parent-dashboard?section=settings'));
    $logout_link = get_secure_logout_url_by_role();
    $communication_link = esc_url(home_url('/parent-dashboard?section=communication'));

    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    $notifications_table = $wpdb->prefix . 'aspire_announcements';
    $parent_id = $parent_user->ID; // Use user ID for notifications/messages

    // Count notifications specific to this parent or broadcast to 'parents'
    $notifications_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $notifications_table 
         WHERE (receiver_id = %d OR receiver_id = 'parents') 
         AND timestamp > %s",
        $parent_id,
        $seven_days_ago
    ));

    $unread_notifications = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp FROM $notifications_table 
         WHERE (receiver_id = %d OR receiver_id = 'parents') 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $parent_id,
        $seven_days_ago
    ));

    // Messages count and data for parents
    $messages_table = $wpdb->prefix . 'aspire_messages';
    $messages_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $messages_table 
         WHERE (receiver_id = %d OR receiver_id = 'parents') 
         AND status = 'sent' 
         AND timestamp > %s",
        $parent_id,
        $seven_days_ago
    ));

    $unread_messages = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $messages_table 
         WHERE (receiver_id = %d OR receiver_id = 'parents') 
         AND status = 'sent' 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $parent_id,
        $seven_days_ago
    ));

    ob_start();
    ?>
    <header class="su_p-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?php echo $dashboard_link; ?>" class="header-logo">
                    <img decoding="async" class="logo-image" src="<?php echo esc_url($avatar_url); ?>" alt="Parent Logo">
                    <span class="logo-text">Parent Dashboard</span>
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
                    <a href="<?php echo esc_url(home_url('/parent-dashboard?section=children')); ?>" class="nav-item">Children</a>
                    <a href="<?php echo esc_url(home_url('/parent-dashboard?section=progress')); ?>" class="nav-item">Progress</a>
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
                                <li><a href="<?php echo esc_url(home_url('/parent-dashboard?section=attendance')); ?>" class="dropdown-link">Attendance</a></li>
                                <li><a href="<?php echo esc_url(home_url('/parent-dashboard?section=schedule')); ?>" class="dropdown-link">Schedule</a></li>
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
                                <li><a href="<?php echo $settings_link; ?>" class="profile-link">Settings</a></li>
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
                            action: 'search_parent_sections',
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
                    action: 'aspire_parent_mark_messages_read',
                    nonce: '<?php echo wp_create_nonce('aspire_parent_header_nonce'); ?>'
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

//header
function render_parent_header($parent_user) {
    global $wpdb;

    // Ensure $parent_user is a WP_User object
    if (!($parent_user instanceof WP_User)) {
        $parent_user = wp_get_current_user();
    }
    $username = $parent_user->user_login;

    // Fetch parent post meta
    $parent_post = get_posts([
        'post_type' => 'parent',
        'meta_key' => 'parent_id',
        'meta_value' => $username,
        'posts_per_page' => 1,
    ]);
    $parent_post_id = $parent_post ? $parent_post[0]->ID : null;

    $user_name = $parent_post_id ? get_post_meta($parent_post_id, 'parent_name', true) : $parent_user->display_name;
    $user_email = $parent_post_id ? get_post_meta($parent_post_id, 'parent_email', true) : $parent_user->user_email;
    $avatar_url = $parent_post_id ? wp_get_attachment_url(get_post_meta($parent_post_id, 'parent_profile_photo', true)) : 'https://via.placeholder.com/150';

    $profile_link = esc_url(home_url('/parent-dashboard?section=profile'));
    $logout_link = get_secure_logout_url_by_role() ;
    $dashboard_link = esc_url(home_url('/parent-dashboard'));
    $communication_link = esc_url(home_url('/parent-dashboard?section=communication'));
    $notifications_link = esc_url(home_url('/parent-dashboard?section=notice_board'));
    $settings_link = esc_url(home_url('/parent-dashboard?section=change_password'));
    $edu_center_id = educational_center_parent_id();
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

    // Messages count and data
    $messages_table = $wpdb->prefix . 'aspire_messages';
    $messages_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $messages_table 
         WHERE education_center_id = %s 
         AND (receiver_id = %s OR receiver_id IN ('all', 'parents')) 
         AND status = 'sent' 
         AND sender_id != %s 
         AND timestamp > %s",
        $edu_center_id, $username, $username, $seven_days_ago
    ));

    $unread_messages = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $messages_table 
         WHERE education_center_id = %s 
         AND (receiver_id = %s OR receiver_id IN ('all', 'parents')) 
         AND status = 'sent' 
         AND sender_id != %s 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $edu_center_id, $username, $username, $seven_days_ago
    ));

    // Notifications count and data
    $announcements_table = $wpdb->prefix . 'aspire_announcements';
    $notifications_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $announcements_table 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'parents') 
         AND sender_id != %s 
         AND timestamp > %s",
        $edu_center_id,
        'enigma_overlord',
        $seven_days_ago
    ));
    
    // Fetch unread announcements, excluding enigma_overlord
    $unread_announcements = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $announcements_table 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'parents') 
         AND sender_id != %s 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $edu_center_id,
        'enigma_overlord',
        $seven_days_ago
    ));

    ob_start();
    ?>
    <header class="parent-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?php echo $dashboard_link; ?>" class="header-logo">
                    <img decoding="async" class="logo-image" src="<?php echo plugin_dir_url(__FILE__) . '../logo instituto.jpg'; ?>">
                    <span class="logo-text">Instituto</span>
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
                    <a href="#" class="nav-item">Exams</a>
                    <a href="#" class="nav-item">Reports</a>
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
                                <li><a href="<?php echo esc_url(home_url('/parent-dashboard?section=calendar')); ?>" class="dropdown-link">Calendar</a></li>
                                <li><a href="<?php echo esc_url(home_url('/parent-dashboard?section=fees')); ?>" class="dropdown-link">Fees</a></li>
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
                                <?php if (!empty($unread_announcements)): ?>
                                    <?php foreach ($unread_announcements as $ann): ?>
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
                            <?php if (!empty($unread_announcements)): ?>
                                <a href="<?php echo $notifications_link; ?>" class="dropdown-footer">View All Notifications</a>
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
                                <li><a href="<?php echo $profile_link; ?>" class="profile-link">Profile</a></li>
                                <li><a href="<?php echo esc_url(home_url('/parent-dashboard?section=change_password')); ?>" class="profile-link">Change Password</a></li>
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
    // Hover behavior
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

    // Click behavior (optional, for touch devices or explicit toggle)
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
                            action: 'search_dashboard_sections',
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
            toggleDropdown(messagesToggle, messagesDropdown, true); // True for link behavior
        }

        if (notificationsToggle && notificationsDropdown) {
            toggleDropdown(notificationsToggle, notificationsDropdown, true); // True for link behavior
        }

        if (quickLinksToggle && quickLinksDropdown) {
            toggleDropdown(quickLinksToggle, quickLinksDropdown);
        }

        if (settingsToggle) {
            // Settings is just a link, no dropdown
        }

        if (helpToggle) {
            // Help is just a link, no dropdown
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
                    action: 'aspire_parent_mark_messages_read',
                    nonce: '<?php echo wp_create_nonce('aspire_parent_header_nonce'); ?>'
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

        $('a[href*="section=notice_board"]').on('click', function(e) {
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

// AJAX Handlers (unchanged)
add_action('wp_ajax_search_dashboard_sections', 'search_dashboard_sections_callback');
function search_dashboard_sections_callback() {
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $sections = [
        ['title' => 'Dashboard', 'url' => esc_url(home_url('/parent-dashboard')), 'keywords' => ['dashboard', 'home']],
        ['title' => 'Profile', 'url' => esc_url(home_url('/parent-dashboard?section=profile')), 'keywords' => ['profile', 'account']],
        ['title' => 'Exams', 'url' => esc_url(home_url('/parent-dashboard?section=exams')), 'keywords' => ['exams', 'tests']],
        ['title' => 'Reports', 'url' => esc_url(home_url('/parent-dashboard?section=reports')), 'keywords' => ['reports', 'grades']],
        ['title' => 'Communication', 'url' => esc_url(home_url('/parent-dashboard?section=communication')), 'keywords' => ['messages', 'chat']],
        ['title' => 'Notice Board', 'url' => esc_url(home_url('/parent-dashboard?section=notice_board')), 'keywords' => ['notices', 'announcements']]
    ];

    $results = [];
    if (!empty($query)) {
        foreach ($sections as $section) {
            if (stripos($section['title'], $query) !== false || array_reduce($section['keywords'], fn($carry, $kw) => $carry || stripos($kw, $query) !== false, false)) {
                $results[] = ['title' => $section['title'], 'url' => $section['url']];
            }
        }
    }
    wp_send_json_success($results);
    wp_die();
}

add_action('wp_ajax_aspire_parent_mark_messages_read', 'aspire_parent_mark_messages_read');
function aspire_parent_mark_messages_read() {
    check_ajax_referer('aspire_parent_header_nonce', 'nonce');

    global $wpdb;
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = educational_center_parent_id();
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

    $messages_table = $wpdb->prefix . 'aspire_messages';
    $wpdb->query($wpdb->prepare(
        "UPDATE $messages_table 
         SET status = 'read' 
         WHERE education_center_id = %s 
         AND receiver_id = %s 
         AND status = 'sent' 
         AND timestamp > %s",
        $edu_center_id, $username, $seven_days_ago
    ));

    $wpdb->query($wpdb->prepare(
        "UPDATE $messages_table 
         SET status = 'read' 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'parents') 
         AND status = 'sent' 
         AND sender_id != %s 
         AND timestamp > %s",
        $edu_center_id, $username, $seven_days_ago
    ));

    wp_send_json_success();
}

// Enqueue jQuery only
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], '3.6.0', true);
});


// Feature 1: Overview
function render_parent_overview($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'student_id',
                    'value' => trim($student_id),
                    'compare' => '='
                ],
                [
                    'key' => 'educational_center_id',
                    'value' => $educational_center_id,
                    'compare' => '='
                ]
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'post_id' => $student_post->ID
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    $nonce = wp_create_nonce('parent_overview_nonce');          
    ?>
    <div class="parent-dashboard">
        <div class="dashboard-header">
            <h3><i class="icon-home"></i> Parent Dashboard</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        
        <div class="child-selector">
            <label for="child_select">View Child:</label>
            <select id="child_select" data-nonce="<?php echo esc_attr($nonce); ?>">
                <?php foreach ($children as $child): ?>
                    <option value="<?php echo esc_attr($child->student_id); ?>">
                        <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="dashboard-content" class="dashboard-content">
            <p class="loading">Loading data for <?php echo esc_html($children[0]->name); ?>...</p>
        </div>

        <!-- External Libraries -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('child_select');
            const content = document.getElementById('dashboard-content');
            const nonce = select.dataset.nonce;

            // Helper function to extract dashboard data
            function getDashboardData() {
                const cards = document.querySelectorAll('.overview-card');
                return Array.from(cards).map(card => {
                    const title = card.querySelector('h5').textContent;
                    const details = Array.from(card.querySelectorAll('p, .expandable-content p'))
                        .map(p => p.textContent.trim());
                    return { title, details };
                });
            }

            // Helper function to format data as a table
            function formatAsTable(data) {
                const rows = data.map(item => [
                    item.title,
                    item.details.join('; ')
                ]);
                return [['Section', 'Details'], ...rows];
            }

            // Helper function to generate PDF
            function generatePDF(studentId, instituteName) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                const pageWidth = doc.internal.pageSize.width;
                const margin = 10;
                const borderColor = [70, 130, 180]; // Steel Blue

                doc.setDrawColor(...borderColor);
                doc.setLineWidth(0.5);
                doc.rect(margin, margin, pageWidth - 2 * margin, doc.internal.pageSize.height - 2 * margin);

                doc.setFontSize(16);
                doc.setTextColor(...borderColor);
                doc.text(instituteName.toUpperCase(), pageWidth / 2, 20, { align: 'center' });
                doc.setFontSize(10);
                doc.setTextColor(51);
                doc.text('Student Dashboard Report', pageWidth / 2, 28, { align: 'center' });
                doc.line(margin + 5, 32, pageWidth - margin - 5, 32);

                const data = getDashboardData();
                let y = 40;
                data.forEach((item, index) => {
                    if (y > 260) {
                        doc.addPage();
                        y = 20;
                    }
                    doc.setFontSize(11);
                    doc.setTextColor(...borderColor);
                    doc.text(item.title, margin + 5, y);
                    y += 5;
                    item.details.forEach(detail => {
                        if (y > 270) {
                            doc.addPage();
                            y = 20;
                        }
                        doc.setFontSize(9);
                        doc.setTextColor(51);
                        const lines = doc.splitTextToSize(detail, pageWidth - 2 * margin - 10);
                        lines.forEach(line => {
                            doc.text(line, margin + 5, y);
                            y += 5;
                        });
                    });
                    y += 5;
                });

                doc.setFontSize(8);
                doc.setTextColor(102);
                doc.text('Generated on <?php echo date('Y-m-d'); ?> by Instituto Educational Center Management System', pageWidth / 2, doc.internal.pageSize.height - 10, { align: 'center' });

                doc.save(`student_${studentId}_dashboard_${new Date().toISOString().slice(0,10)}.pdf`);
            }

            // Helper function for print view
            function generatePrintView() {
                window.print();
            }

            // Export to CSV
            function exportToCSV(studentId) {
                const data = getDashboardData();
                const table = formatAsTable(data);
                const csvContent = table.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `student_${studentId}_dashboard_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            // Export to Excel
            function exportToExcel(studentId) {
                if (!window.XLSX) {
                    console.error('XLSX library not loaded');
                    return;
                }
                const data = getDashboardData();
                const table = formatAsTable(data);
                const ws = XLSX.utils.aoa_to_sheet(table);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Dashboard');
                XLSX.writeFile(wb, `student_${studentId}_dashboard_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            // Copy to Clipboard
            function copyToClipboard() {
                const data = getDashboardData();
                const text = data.map(item => `${item.title}:\n${item.details.join('\n')}`).join('\n\n');
                navigator.clipboard.writeText(text).then(() => {
                    alert('Dashboard data copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            }

            function loadDashboard(studentId) {
                content.innerHTML = '<p class="loading">Loading data...</p>';
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_parent_dashboard_data&student_id=${encodeURIComponent(studentId)}&nonce=${nonce}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        content.innerHTML = data.data.html;
                        setupExpandables();
                        setupExportButtons(studentId);
                    } else {
                        content.innerHTML = '<p class="error">Error: ' + (data.data.message || 'Failed to load data') + '</p>';
                    }
                })
                .catch(error => {
                    content.innerHTML = '<p class="error">Error loading data: ' + error.message + '</p>';
                    console.error('AJAX Error:', error);
                });
            }

            function setupExpandables() {
                document.querySelectorAll('.expandable').forEach(el => {
                    el.addEventListener('click', function() {
                        const content = this.nextElementSibling;
                        content.style.display = content.style.display === 'block' ? 'none' : 'block';
                        this.textContent = content.style.display === 'block' ? 'Hide Details' : 'Show Details';
                    });
                });
            }

            function setupExportButtons(studentId) {
                const exportCsvBtn = document.querySelector('.export-csv');
                const exportPdfBtn = document.querySelector('.export-pdf');
                const exportExcelBtn = document.querySelector('.export-excel');
                const copyBtn = document.querySelector('.export-copy');
                const printBtn = document.querySelector('.export-print');

                if (exportCsvBtn) {
                    exportCsvBtn.addEventListener('click', () => exportToCSV(studentId));
                }
                if (exportPdfBtn) {
                    exportPdfBtn.addEventListener('click', () => generatePDF(studentId, '<?php echo esc_js($institute_name ?? 'INSTITUTE'); ?>'));
                }
                if (exportExcelBtn) {
                    exportExcelBtn.addEventListener('click', () => exportToExcel(studentId));
                }
                if (copyBtn) {
                    copyBtn.addEventListener('click', copyToClipboard);
                }
                if (printBtn) {
                    printBtn.addEventListener('click', generatePrintView);
                }
            }

            loadDashboard(select.value);
            select.addEventListener('change', function() { loadDashboard(this.value); });
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler
add_action('wp_ajax_get_parent_dashboard_data', 'get_parent_dashboard_data_callback');
function get_parent_dashboard_data_callback() {
    global $wpdb;

    ob_start();
    ob_clean();

    if (!check_ajax_referer('parent_overview_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $educational_center_id = educational_center_parent_id();

    // if (empty($student_id) || empty($educational_center_id)) {
    //     wp_send_json_error(['message' => 'Invalid student or center ID']);
    //     exit;
    // }

    $wpdb->show_errors = false;

    $student = get_posts([
        'post_type' => 'students',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'student_id', 'value' => $student_id, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
        ]
    ]);

    if (empty($student)) {
        wp_send_json_error(['message' => 'Student not found']);
        exit;
    }

    $student_post = $student[0];
    $student_data = (object)[
        'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
        'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
        'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
        'admission_date' => get_post_meta($student_post->ID, 'admission_date', true) ?: date('Y-m')
    ];

    $center_data = get_educational_center_data_teachers($educational_center_id);
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? '';

    $today = date('Y-m-d');
    $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));

    $attendance_summary = $wpdb->get_results($wpdb->prepare(
        "SELECT date, status FROM {$wpdb->prefix}student_attendance WHERE student_id = %s AND education_center_id = %s ORDER BY date DESC LIMIT 5",
        $student_id, $educational_center_id
    ));
    $attendance_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present 
         FROM {$wpdb->prefix}student_attendance WHERE student_id = %s AND education_center_id = %s AND date >= %s",
        $student_id, $educational_center_id, $thirty_days_ago
    ));
    $attendance_percentage = $attendance_stats->total > 0 ? round(($attendance_stats->present / $attendance_stats->total) * 100, 1) : 0;
    $attendance_details = $wpdb->get_results($wpdb->prepare(
        "SELECT date, status FROM {$wpdb->prefix}student_attendance WHERE student_id = %s AND education_center_id = %s AND date >= %s ORDER BY date DESC LIMIT 30",
        $student_id, $educational_center_id, $thirty_days_ago
    ));

    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT name, exam_date FROM {$wpdb->prefix}exams WHERE education_center_id = %s AND class_id = %s AND exam_date >= %s ORDER BY exam_date ASC LIMIT 3",
        $educational_center_id, $student_data->class, $today
    ));

    $exam_results_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}exam_results'") === "{$wpdb->prefix}exam_results";
    $exam_results = [];
    if ($exam_results_exists) {
        $exam_results = $wpdb->get_results($wpdb->prepare(
            "SELECT er.exam_id, e.name, er.marks AS marks_obtained 
             FROM {$wpdb->prefix}exam_results er 
             JOIN {$wpdb->prefix}exams e ON er.exam_id = e.id 
             WHERE er.student_id = %s AND er.education_center_id = %s ORDER BY e.exam_date DESC LIMIT 3",
            $student_id, $educational_center_id
        ));
        if ($wpdb->last_error) {
            error_log("Database error in exam results query: " . $wpdb->last_error);
            $exam_results = [];
        }
    }
    $progress_avg = $exam_results ? array_sum(array_map(fn($r) => floatval($r->marks_obtained), $exam_results)) / count($exam_results) : 0;

    $homework = $wpdb->get_results($wpdb->prepare(
        "SELECT title, due_date, status FROM {$wpdb->prefix}homework 
         WHERE education_center_id = %s AND class_name = %s AND section = %s AND status != 'completed' 
         ORDER BY due_date ASC LIMIT 3",
        $educational_center_id, $student_data->class, $student_data->section
    ));

    $fees = $wpdb->get_results($wpdb->prepare(
        "SELECT month_year, amount, status FROM {$wpdb->prefix}student_fees 
         WHERE education_center_id = %s AND student_id = %s ORDER BY month_year DESC LIMIT 3",
        $educational_center_id, $student_id
    ));
    $pending_fees = array_filter($fees, fn($f) => $f->status !== 'paid');
    $total_pending_amount = array_sum(array_map(fn($f) => floatval($f->amount), $pending_fees));

    $transport_fees = $wpdb->get_results($wpdb->prepare(
        "SELECT month_year, amount, status FROM {$wpdb->prefix}transport_fees 
         WHERE education_center_id = %s AND student_id = %s ORDER BY month_year DESC LIMIT 3",
        $educational_center_id, $student_id
    ));
    $pending_transport = array_filter($transport_fees, fn($f) => $f->status !== 'paid');
    $total_transport_pending = array_sum(array_map(fn($f) => floatval($f->amount), $pending_transport));

    $library = [];
    $library_books_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}library_books'") === "{$wpdb->prefix}library_books";
    $library_transactions_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}library_transactions'") === "{$wpdb->prefix}library_transactions";
    if ($library_books_exists && $library_transactions_exists) {
        $library = $wpdb->get_results($wpdb->prepare(
            "SELECT t.item_id, i.name, t.action, t.status, t.due_date 
             FROM {$wpdb->prefix}library_transactions t 
             JOIN {$wpdb->prefix}library_books i ON t.item_id = i.book_id 
             WHERE t.education_center_id = %s AND t.user_id = %s AND t.user_type = 'Student' 
             AND t.status != 'Returned' ORDER BY t.due_date ASC LIMIT 3",
            $educational_center_id, $student_id
        ));
    }

    $notices = [];
    $notices_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}notices'") === "{$wpdb->prefix}notices";
    if ($notices_exists) {
        $notices = $wpdb->get_results($wpdb->prepare(
            "SELECT title, notice_date, content 
             FROM {$wpdb->prefix}notices 
             WHERE education_center_id = %s 
             AND (class_id = %s OR class_id = 'All') 
             AND (section = %s OR section = 'All') 
             ORDER BY notice_date DESC LIMIT 3",
            $educational_center_id, $student_data->class, $student_data->section
        ));
    }

    $timetable = [];
    $timetable_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}timetable'") === "{$wpdb->prefix}timetable";
    if ($timetable_exists) {
        $timetable = $wpdb->get_results($wpdb->prepare(
            "SELECT day, period, subject 
             FROM {$wpdb->prefix}timetable 
             WHERE education_center_id = %s AND class_id = %s AND section = %s 
             ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), period",
            $educational_center_id, $student_data->class, $student_data->section
        ));
    }

    ob_start();
    ?>
    <div class="overview-grid">
        <div class="export-tools">
            <button class="export-btn export-csv" aria-label="Export to CSV">
                <i class="fas fa-file-csv"></i>
                <span class="tooltip">Export to CSV</span>
            </button>
            <button class="export-btn export-pdf" aria-label="Export to PDF">
                <i class="fas fa-file-pdf"></i>
                <span class="tooltip">Export to PDF</span>
            </button>
            <button class="export-btn export-excel" aria-label="Export to Excel">
                <i class="fas fa-file-excel"></i>
                <span class="tooltip">Export to Excel</span>
            </button>
            <button class="export-btn export-copy" aria-label="Copy to Clipboard">
                <i class="fas fa-copy"></i>
                <span class="tooltip">Copy to Clipboard</span>
            </button>
            <button class="export-btn export-print" aria-label="Print">
                <i class="fas fa-print"></i>
                <span class="tooltip">Print</span>
            </button>
        </div>

        <div class="overview-card card-student">
            <h5>Student Info</h5>
            <p><strong>Name:</strong> <?php echo esc_html($student_data->name); ?></p>
            <p><strong>ID:</strong> <?php echo esc_html($student_id); ?></p>
            <p><strong>Class:</strong> <?php echo esc_html($student_data->class . ' - ' . $student_data->section); ?></p>
        </div>

        <div class="overview-card card-attendance">
            <h5>Recent Attendance</h5>
            <?php if ($attendance_summary): ?>
                <?php foreach ($attendance_summary as $record): ?>
                    <p>
                        <?php 
                        echo esc_html(date('M d', strtotime($record->date))) . ': ';
                        $badge_class = $record->status === 'Present' ? 'status-present' : 'status-absent';
                        echo "<span class='status-badge $badge_class'>" . esc_html($record->status) . "</span>";
                        ?>
                    </p>
                <?php endforeach; ?>
                <p><strong>30-Day:</strong> <?php echo esc_html($attendance_percentage); ?>%</p>
                <?php if ($attendance_details): ?>
                    <span class="expandable">Show Details</span>
                    <div class="expandable-content">
                        <?php foreach ($attendance_details as $detail): ?>
                            <p>
                                <?php 
                                echo esc_html(date('M d', strtotime($detail->date))) . ': ';
                                $badge_class = $detail->status === 'Present' ? 'status-present' : 'status-absent';
                                echo "<span class='status-badge $badge_class'>" . esc_html($detail->status) . "</span>";
                                ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No recent attendance data.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-exams">
            <h5>Upcoming Exams</h5>
            <?php if ($exams): ?>
                <?php foreach ($exams as $exam): ?>
                    <p><?php echo esc_html($exam->name . ' - ' . date('M d', strtotime($exam->exam_date))); ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No upcoming exams.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-homework">
            <h5>Pending Homework</h5>
            <?php if ($homework): ?>
                <?php foreach ($homework as $hw): ?>
                    <p>
                        <?php 
                        echo esc_html($hw->title) . ' (Due: ' . date('M d', strtotime($hw->due_date)) . ')';
                        $badge_class = $hw->status === 'completed' ? 'status-completed' : 'status-pending';
                        echo " <span class='status-badge $badge_class'>" . esc_html(ucfirst($hw->status)) . "</span>";
                        ?>
                    </p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No pending homework.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-fees">
            <h5>Fees Status</h5>
            <p><strong>Pending Total:</strong> <?php echo esc_html(number_format($total_pending_amount, 2)); ?></p>
            <?php if ($fees): ?>
                <span class="expandable">Show Details</span>
                <div class="expandable-content">
                    <?php foreach ($fees as $fee): ?>
                        <p>
                            <?php 
                            echo esc_html(date('F Y', strtotime($fee->month_year))) . ': ' . number_format($fee->amount, 2);
                            $badge_class = $fee->status === 'paid' ? 'status-paid' : 'status-overdue';
                            echo " <span class='status-badge $badge_class'>" . esc_html(ucfirst($fee->status)) . "</span>";
                            ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No recent fee records.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-transport">
            <h5>Transport Fees</h5>
            <p><strong>Pending Total:</strong> <?php echo esc_html(number_format($total_transport_pending, 2)); ?></p>
            <?php if ($transport_fees): ?>
                <span class="expandable">Show Details</span>
                <div class="expandable-content">
                    <?php foreach ($transport_fees as $tf): ?>
                        <p>
                            <?php 
                            echo esc_html(date('F Y', strtotime($tf->month_year))) . ': ' . number_format($tf->amount, 2);
                            $badge_class = $tf->status === 'paid' ? 'status-paid' : 'status-overdue';
                            echo " <span class='status-badge $badge_class'>" . esc_html(ucfirst($tf->status)) . "</span>";
                            ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No transport fee records.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-library">
            <h5>Library Items</h5>
            <?php if ($library_books_exists && $library_transactions_exists && $library): ?>
                <?php foreach ($library as $item): ?>
                    <p>
                        <?php 
                        echo esc_html($item->name) . ' (Due: ' . ($item->due_date ? date('M d', strtotime($item->due_date)) : 'N/A') . ')';
                        $badge_class = $item->status === 'Returned' ? 'status-completed' : 'status-pending';
                        echo " <span class='status-badge $badge_class'>" . esc_html($item->action) . "</span>";
                        ?>
                    </p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No active library items or library data unavailable.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-results">
            <h5>Recent Exam Results</h5>
            <?php if ($exam_results_exists && $exam_results): ?>
                <?php foreach ($exam_results as $result): ?>
                    <p><?php echo esc_html($result->name . ': ' . $result->marks_obtained); ?></p>
                <?php endforeach; ?>
                <span class="expandable">Show Details</span>
                <div class="expandable-content">
                    <?php foreach ($exam_results as $result): ?>
                        <p><?php echo esc_html($result->name . ': ' . $result->marks_obtained); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No recent exam results or data unavailable.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-notices">
            <h5>Recent Notices</h5>
            <?php if ($notices_exists && $notices): ?>
                <?php foreach ($notices as $notice): ?>
                    <p><?php echo esc_html($notice->title . ' - ' . date('M d', strtotime($notice->notice_date))); ?></p>
                <?php endforeach; ?>
                <span class="expandable">Show Details</span>
                <div class="expandable-content">
                    <?php foreach ($notices as $notice): ?>
                        <p>
                            <strong><?php echo esc_html($notice->title); ?></strong> (<?php echo date('M d', strtotime($notice->notice_date)); ?>)<br>
                            <?php echo esc_html(wp_trim_words($notice->content, 20, '...')); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No recent notices or data unavailable.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-timetable">
            <h5>Class Timetable</h5>
            <?php if ($timetable_exists && $timetable): ?>
                <?php 
                $days = array_unique(array_column($timetable, 'day'));
                foreach ($days as $day): ?>
                    <p><strong><?php echo esc_html($day); ?>:</strong></p>
                    <?php foreach ($timetable as $slot): ?>
                        <?php if ($slot->day === $day): ?>
                            <p><?php echo esc_html("Period $slot->period: $slot->subject"); ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <span class="expandable">Show Full Timetable</span>
                <div class="expandable-content">
                    <?php foreach ($timetable as $slot): ?>
                        <p><?php echo esc_html("$slot->day, Period $slot->period: $slot->subject"); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No timetable available.</p>
            <?php endif; ?>
        </div>

        <div class="overview-card card-progress">
            <h5>Progress Report</h5>
            <?php if ($exam_results_exists && $exam_results): ?>
            <p><strong>Average Marks:</strong> <?php echo esc_html(round($progress_avg, 1)); ?></p>
            <span class="expandable">Show Details</span>
            <div class="expandable-content">
                <?php foreach ($exam_results as $result): ?>
                    <p><?php echo esc_html($result->name . ': ' . $result->marks_obtained); ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No progress data available.</p>
        <?php endif; ?>
    </div>
  </div>
 <?php
    $html = ob_get_clean();

    if (ob_get_length() > 0) {
        error_log("Stray output detected: " . ob_get_contents());
        ob_clean();
    }

    wp_send_json_success(['html' => $html]);
    exit;
}

// Feature 2: Profile
function render_parent_profile($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    // Fetch parent meta data
    $parent_id = get_post_meta($parent_post_id, 'parent_id', true);
    $parent_student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    $parent_name = get_post_meta($parent_post_id, 'parent_name', true);
    $parent_email = get_post_meta($parent_post_id, 'parent_email', true);
    $parent_phone_number = get_post_meta($parent_post_id, 'parent_phone_number', true);
    $teacher_gender = get_post_meta($parent_post_id, 'teacher_gender', true);
    $parent_religion = get_post_meta($parent_post_id, 'parent_religion', true);
    $parent_blood_group = get_post_meta($parent_post_id, 'parent_blood_group', true);
    $parent_date_of_birth = get_post_meta($parent_post_id, 'parent_date_of_birth', true);
    $parent_height = get_post_meta($parent_post_id, 'parent_height', true);
    $parent_weight = get_post_meta($parent_post_id, 'parent_weight', true);
    $parent_current_address = get_post_meta($parent_post_id, 'parent_current_address', true);
    $parent_permanent_address = get_post_meta($parent_post_id, 'parent_permanent_address', true);

    if (empty($parent_student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $parent_student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'student_id',
                    'value' => trim($student_id),
                    'compare' => '='
                ],
                [
                    'key' => 'educational_center_id',
                    'value' => $educational_center_id,
                    'compare' => '='
                ]
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'roll_number' => get_post_meta($student_post->ID, 'roll_number', true) ?: 'N/A',
                'post_id' => $student_post->ID
            ];
        }
    }

    $update_nonce = wp_create_nonce('update_parent_profile_nonce');

    ob_start();
    ?>
    <div class="parent-profile">
        <div class="profile-header">
            <h3><i class="fas fa-user"></i> Profile</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>

        <div class="profile-section">
            <div class="profile-grid">
                <!-- Parent Info -->
                <div class="profile-card">
                    <h5>Parent Information</h5>
                    <p><strong>Parent ID:</strong> <?php echo esc_html($parent_id); ?></p>
                    <p><strong>Name:</strong> <?php echo esc_html($parent_name); ?></p>
                    <p><strong>Email:</strong> <?php echo esc_html($parent_email); ?></p>
                    <p><strong>Phone:</strong> <?php echo esc_html($parent_phone_number); ?></p>
                    <p><strong>Gender:</strong> <?php echo esc_html($teacher_gender); ?></p>
                    <p><strong>Religion:</strong> <?php echo esc_html($parent_religion); ?></p>
                    <p><strong>Blood Group:</strong> <?php echo esc_html($parent_blood_group); ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo esc_html($parent_date_of_birth); ?></p>
                    <p><strong>Height:</strong> <?php echo esc_html($parent_height); ?></p>
                    <p><strong>Weight:</strong> <?php echo esc_html($parent_weight); ?></p>
                    <p><strong>Current Address:</strong> <?php echo esc_html($parent_current_address); ?></p>
                    <p><strong>Permanent Address:</strong> <?php echo esc_html($parent_permanent_address); ?></p>
                    <button class="edit-contact-btn">Edit Profile</button>

                    <!-- Edit Form -->
                    <form id="update-profile-form" class="update-profile-form" style="display: none;" method="post">
                        <input type="hidden" name="action" value="update_parent_profile">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr($update_nonce); ?>">
                        <input type="hidden" name="parent_post_id" value="<?php echo esc_attr($parent_post_id); ?>">
                        <div class="form-group">
                            <label for="parent_name">Name:</label>
                            <input type="text" id="parent_name" name="parent_name" value="<?php echo esc_attr($parent_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="parent_email">Email:</label>
                            <input type="email" id="parent_email" name="parent_email" value="<?php echo esc_attr($parent_email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="parent_phone_number">Phone:</label>
                            <input type="tel" id="parent_phone_number" name="parent_phone_number" value="<?php echo esc_attr($parent_phone_number); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="teacher_gender">Gender:</label>
                            <input type="text" id="teacher_gender" name="teacher_gender" value="<?php echo esc_attr($teacher_gender); ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_religion">Religion:</label>
                            <input type="text" id="parent_religion" name="parent_religion" value="<?php echo esc_attr($parent_religion); ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_blood_group">Blood Group:</label>
                            <input type="text" id="parent_blood_group" name="parent_blood_group" value="<?php echo esc_attr($parent_blood_group); ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_date_of_birth">Date of Birth:</label>
                            <input type="date" id="parent_date_of_birth" name="parent_date_of_birth" value="<?php echo esc_attr($parent_date_of_birth); ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_height">Height:</label>
                            <input type="text" id="parent_height" name="parent_height" value="<?php echo esc_attr($parent_height); ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_weight">Weight:</label>
                            <input type="text" id="parent_weight" name="parent_weight" value="<?php echo esc_attr($parent_weight); ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_current_address">Current Address:</label>
                            <textarea id="parent_current_address" name="parent_current_address"><?php echo esc_textarea($parent_current_address); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="parent_permanent_address">Permanent Address:</label>
                            <textarea id="parent_permanent_address" name="parent_permanent_address"><?php echo esc_textarea($parent_permanent_address); ?></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Update</button>
                        <button type="button" class="cancel-btn">Cancel</button>
                    </form>
                </div>

                <!-- Child Info with Add/Remove -->
                <div class="profile-card child-management">
                <h5>Linked Children</h5>
                    <ul class="student-list">
                        <?php foreach ($children as $child): ?>
                            <li data-student-id="<?php echo esc_attr($child->student_id); ?>">
                                <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                                <button class="remove-student-btn" data-student-id="<?php echo esc_attr($child->student_id); ?>">Remove</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="add-student">
                        <input type="text" id="new_student_id" placeholder="Enter Student ID">
                        <button id="add-student-btn">Add Student</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- External Libraries -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.querySelector('.edit-contact-btn');
            const form = document.getElementById('update-profile-form');
            const cancelBtn = document.querySelector('.cancel-btn');
            const addStudentBtn = document.getElementById('add-student-btn');
            const newStudentIdInput = document.getElementById('new_student_id');
            const removeButtons = document.querySelectorAll('.remove-student-btn');

            // Toggle edit form
            editBtn.addEventListener('click', () => {
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            });

            cancelBtn.addEventListener('click', () => {
                form.style.display = 'none';
            });

            // Update parent profile
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Profile updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.data?.message || 'Update failed'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error updating profile: ' + error.message);
                });
            });

            // Add student
            addStudentBtn.addEventListener('click', () => {
                const studentId = newStudentIdInput.value.trim();
                if (!studentId) {
                    alert('Please enter a student ID.');
                    return;
                }

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=manage_student_ids&nonce=<?php echo esc_attr($update_nonce); ?>&parent_post_id=<?php echo esc_attr($parent_post_id); ?>&student_id=${encodeURIComponent(studentId)}&operation=add`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Student added successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to add student'));
                    }
                })
                .catch(error => {
                    alert('Error adding student: ' + error.message);
                });
            });

            // Remove student
            removeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const studentId = btn.dataset.studentId;
                    if (confirm(`Are you sure you want to remove student ${studentId}?`)) {
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `action=manage_student_ids&nonce=<?php echo esc_attr($update_nonce); ?>&parent_post_id=<?php echo esc_attr($parent_post_id); ?>&student_id=${encodeURIComponent(studentId)}&operation=remove`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Student removed successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + (data.data?.message || 'Failed to remove student'));
                            }
                        })
                        .catch(error => {
                            alert('Error removing student: ' + error.message);
                        });
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler for updating parent profile
add_action('wp_ajax_update_parent_profile', 'update_parent_profile_callback');
function update_parent_profile_callback() {
    // Start output buffering to catch any stray output
    ob_start();

    if (!check_ajax_referer('update_parent_profile_nonce', 'nonce', false)) {
        ob_end_clean();
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $parent_post_id = intval($_POST['parent_post_id']);
    // if (!current_user_can('edit_post', $parent_post_id)) {
    //     ob_end_clean();
    //     wp_send_json_error(['message' => 'Unauthorized']);
    //     exit;
    // }

    $post = get_post($parent_post_id);
    if (!$post || $post->post_type !== 'parent') {
        ob_end_clean();
        wp_send_json_error(['message' => 'Invalid parent post']);
        exit;
    }

    $educational_center_id = educational_center_parent_id();
    $stored_edu_center_id = get_post_meta($parent_post_id, 'educational_center_id', true);
    if ($stored_edu_center_id !== $educational_center_id) {
        // wp_redirect(home_url('/login'));
        // exit();  
    }

    $fields = [
        'parent_name' => sanitize_text_field($_POST['parent_name'] ?? ''),
        'parent_email' => sanitize_email($_POST['parent_email'] ?? ''),
        'parent_phone_number' => sanitize_text_field($_POST['parent_phone_number'] ?? ''),
        'teacher_gender' => sanitize_text_field($_POST['teacher_gender'] ?? ''),
        'parent_religion' => sanitize_text_field($_POST['parent_religion'] ?? ''),
        'parent_blood_group' => sanitize_text_field($_POST['parent_blood_group'] ?? ''),
        'parent_date_of_birth' => sanitize_text_field($_POST['parent_date_of_birth'] ?? ''),
        'parent_height' => sanitize_text_field($_POST['parent_height'] ?? ''),
        'parent_weight' => sanitize_text_field($_POST['parent_weight'] ?? ''),
        'parent_current_address' => sanitize_textarea_field($_POST['parent_current_address'] ?? ''),
        'parent_permanent_address' => sanitize_textarea_field($_POST['parent_permanent_address'] ?? ''),
    ];

    if (empty($fields['parent_name']) || empty($fields['parent_email']) || empty($fields['parent_phone_number'])) {
        ob_end_clean();
        wp_send_json_error(['message' => 'Required fields (name, email, phone) cannot be empty']);
        exit;
    }

    if (!is_email($fields['parent_email'])) {
        ob_end_clean();
        wp_send_json_error(['message' => 'Invalid email']);
        exit;
    }

    foreach ($fields as $meta_key => $value) {
        $old_value = get_post_meta($parent_post_id, $meta_key, true);
        if ($old_value !== $value) {
            $updated = update_post_meta($parent_post_id, $meta_key, $value);
            if ($updated === false) {
                global $wpdb;
                $last_error = $wpdb->last_error;
                error_log("Failed to update meta_key: $meta_key for post ID: $parent_post_id");
                error_log("Old value: " . print_r($old_value, true));
                error_log("New value: " . print_r($value, true));
                error_log("DB error: " . ($last_error ? $last_error : 'None reported'));
                ob_end_clean();
                wp_send_json_error(['message' => "Failed to update $meta_key. Check debug.log for details."]);
                exit;
            }
        }
    }

    // Clean up any stray output and send success
    $stray_output = ob_get_clean();
    if ($stray_output) {
        error_log("Stray output detected in update_parent_profile_callback: " . $stray_output);
    }
    wp_send_json_success(['message' => 'Profile updated successfully']);
    exit;
}

// AJAX Handler for managing student IDs (unchanged, included for completeness)
add_action('wp_ajax_manage_student_ids', 'manage_student_ids_callback');
function manage_student_ids_callback() {
    global $wpdb;

    ob_start();

    if (!check_ajax_referer('update_parent_profile_nonce', 'nonce', false)) {
        ob_end_clean();
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $parent_post_id = intval($_POST['parent_post_id']);
    $student_id = sanitize_text_field($_POST['student_id']);
    $operation = sanitize_text_field($_POST['operation']);

    // if (!current_user_can('edit_post', $parent_post_id)) {
    //     ob_end_clean();
    //     wp_send_json_error(['message' => 'Unauthorized']);
    //     exit;
    // }

    $current_student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    $student_ids_array = array_filter(explode(',', $current_student_ids));

    if ($operation === 'add') {
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts p 
             JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'students' AND p.post_status = 'publish' 
             AND pm.meta_key = 'student_id' AND pm.meta_value = %s",
            $student_id
        ));

        if (!$student) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Student ID does not exist']);
            exit;
        }

        if (!in_array($student_id, $student_ids_array)) {
            $student_ids_array[] = $student_id;
            update_post_meta($parent_post_id, 'parent_student_ids', implode(',', $student_ids_array));
            ob_end_clean();
            wp_send_json_success(['message' => 'Student added']);
            exit;
        } else {
            ob_end_clean();
            wp_send_json_error(['message' => 'Student already linked']);
            exit;
        }
    } elseif ($operation === 'remove') {
        if (in_array($student_id, $student_ids_array)) {
            $student_ids_array = array_diff($student_ids_array, [$student_id]);
            update_post_meta($parent_post_id, 'parent_student_ids', implode(',', $student_ids_array));
            ob_end_clean();
            wp_send_json_success(['message' => 'Student removed']);
            exit;
        } else {
            ob_end_clean();
            wp_send_json_error(['message' => 'Student not found']);
            exit;
        }
    } else {
        ob_end_clean();
        wp_send_json_error(['message' => 'Invalid operation']);
        exit;
    }
}

//feature 3
function render_child_selection($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'student_id',
                    'value' => trim($student_id),
                    'compare' => '='
                ],
                [
                    'key' => 'educational_center_id',
                    'value' => $educational_center_id,
                    'compare' => '='
                ]
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[$student_id] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'roll_number' => get_post_meta($student_post->ID, 'roll_number', true) ?: 'N/A',
                'post_id' => $student_post->ID,
                'last_attendance' => get_last_attendance_status($student_id, $educational_center_id) ?: 'N/A',
                'last_result' => get_last_result($student_id, $educational_center_id) ?: 'N/A'
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    $nonce = wp_create_nonce('child_selection_nonce');
    $default_student_id = array_key_first($children);

    ob_start();
    ?>
    <div class="child-selection card shadow-sm border-0">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="bi bi-users me-2"></i>Child Selection</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="child-tabs d-flex flex-wrap gap-2 mb-4">
                <?php foreach ($children as $child): ?>
                    <div class="child-tab card <?php echo $child->student_id === $default_student_id ? 'bg-info text-white' : 'bg-light'; ?>" 
                         data-student-id="<?php echo esc_attr($child->student_id); ?>">
                        <div class="card-bod p-2">
                            <span class="child-name"><?php echo esc_html($child->name); ?></span>
                            <span class="child-id">(<?php echo esc_html($child->student_id); ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="child-details">
                <?php foreach ($children as $child): ?>
                    <div class="child-detail-card card mb-3" 
                         data-student-id="<?php echo esc_attr($child->student_id); ?>" 
                         style="display: <?php echo $child->student_id === $default_student_id ? 'block' : 'none'; ?>;">
                        <div class="card-body d-flex flex-wrap gap-3">
                            <div class="child-info flex-grow-1">
                                <h4><?php echo esc_html($child->name); ?></h4>
                                <p><strong>Student ID:</strong> <?php echo esc_html($child->student_id); ?></p>
                                <p><strong>Roll Number:</strong> <?php echo esc_html($child->roll_number); ?></p>
                                <p><strong>Class:</strong> <?php echo esc_html($child->class . ' - ' . $child->section); ?></p>
                                <p><strong>Last Attendance:</strong> <?php echo esc_html($child->last_attendance); ?></p>
                                <p><strong>Last Result:</strong> <?php echo esc_html($child->last_result); ?></p>
                            </div>
                            <div class="child-actions d-flex flex-column gap-2">
                                <button class="action-btn btn btn-outline-info" data-action="overview" data-student-id="<?php echo esc_attr($child->student_id); ?>">
                                    <i class="bi bi-house-door"></i> Overview
                                </button>
                                <button class="action-btn btn btn-outline-success" data-action="attendance" data-student-id="<?php echo esc_attr($child->student_id); ?>">
                                    <i class="bi bi-calendar-check"></i> Attendance
                                </button>
                                <button class="action-btn btn btn-outline-danger" data-action="results" data-student-id="<?php echo esc_attr($child->student_id); ?>">
                                    <i class="bi bi-clipboard-check"></i> Results
                                </button>
                                <button class="action-btn btn btn-outline-primary" data-action="homework" data-student-id="<?php echo esc_attr($child->student_id); ?>">
                                    <i class="bi bi-book"></i> Homework
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="child-content" class="card mt-3">
                <div class="card-body">
                    <p class="loading text-muted">Select an action to view detailed information...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.child-tab');
            const detailCards = document.querySelectorAll('.child-detail-card');
            const actionButtons = document.querySelectorAll('.action-btn');
            const contentArea = document.getElementById('child-content');

            function switchChild(studentId) {
                tabs.forEach(tab => {
                    tab.classList.toggle('bg-info', tab.dataset.studentId === studentId);
                    tab.classList.toggle('text-white', tab.dataset.studentId === studentId);
                    tab.classList.toggle('bg-light', tab.dataset.studentId !== studentId);
                });
                detailCards.forEach(card => {
                    card.style.display = card.dataset.studentId === studentId ? 'block' : 'none';
                });
                contentArea.innerHTML = '<p class="loading text-muted">Select an action to view detailed information...</p>';
            }

            function loadChildData(studentId, action) {
                contentArea.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading detailed data...</p>';
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_child_data&nonce=<?php echo esc_attr($nonce); ?>&student_id=${encodeURIComponent(studentId)}&data_type=${encodeURIComponent(action)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        contentArea.innerHTML = data.data.html;
                    } else {
                        contentArea.innerHTML = `<p class="text-danger">${data.data.message || 'Failed to load data'}</p>`;
                    }
                })
                .catch(error => {
                    contentArea.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                });
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', () => switchChild(tab.dataset.studentId));
            });

            actionButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const studentId = btn.dataset.studentId;
                    const action = btn.dataset.action;
                    switchChild(studentId);
                    loadChildData(studentId, action);
                });
            });
        });
    </script>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> -->
    <?php
    return ob_get_clean();
}

// AJAX Handler
add_action('wp_ajax_get_child_data', 'get_child_data_callback');
function get_child_data_callback() {
    global $wpdb;

    if (!check_ajax_referer('child_selection_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $data_type = sanitize_text_field($_POST['data_type']);
    $educational_center_id = educational_center_parent_id();

    $student_post = get_posts([
        'post_type' => 'students',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'student_id',
                'value' => $student_id,
                'compare' => '='
            ],
            [
                'key' => 'educational_center_id',
                'value' => $educational_center_id,
                'compare' => '='
            ]
        ]
    ])[0] ?? null;

    if (!$student_post) {
        wp_send_json_error(['message' => 'Student not found']);
        exit;
    }

    ob_start();
    switch ($data_type) {
        case 'overview':
            echo '<h4 class="mb-3">Detailed Overview</h4>';
            echo '<table>';
            echo '<tr><th>Field</th><th>Value</th></tr>';
            echo '<tr><td>Name</td><td>' . esc_html(get_post_meta($student_post->ID, 'student_name', true)) . '</td></tr>';
            echo '<tr><td>Student ID</td><td>' . esc_html($student_id) . '</td></tr>';
            echo '<tr><td>Roll Number</td><td>' . esc_html(get_post_meta($student_post->ID, 'roll_number', true)) . '</td></tr>';
            echo '<tr><td>Class</td><td>' . esc_html(get_post_meta($student_post->ID, 'class', true) . ' - ' . get_post_meta($student_post->ID, 'section', true)) . '</td></tr>';
            echo '<tr><td>Date of Birth</td><td>' . esc_html(get_post_meta($student_post->ID, 'date_of_birth', true) ?: 'N/A') . '</td></tr>';
            echo '<tr><td>Blood Group</td><td>' . esc_html(get_post_meta($student_post->ID, 'blood_group', true) ?: 'N/A') . '</td></tr>';
            echo '<tr><td>Address</td><td>' . esc_html(get_post_meta($student_post->ID, 'current_address', true) ?: 'N/A') . '</td></tr>';
            echo '</table>';
            break;

        case 'attendance':
            $table_name = $wpdb->prefix . 'student_attendance';
            $attendance = $wpdb->get_results($wpdb->prepare(
                "SELECT date, status, subject FROM $table_name 
                 WHERE student_id = %s AND education_center_id = %s 
                 ORDER BY date DESC LIMIT 30",
                $student_id,
                $educational_center_id
            ));
            echo '<h4 class="mb-3">Attendance History</h4>';
            if ($attendance) {
                echo '<table>';
                echo '<thead><tr><th>Date</th><th>Status</th><th>Subject</th></tr></thead>';
                echo '<tbody>';
                foreach ($attendance as $record) {
                    echo '<tr>';
                    echo '<td>' . esc_html($record->date) . '</td>';
                    echo '<td>' . esc_html($record->status) . '</td>';
                    echo '<td>' . esc_html($record->subject ?: 'N/A') . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-muted">No attendance records found.</p>';
            }
            break;

        case 'results':
            $exams_table = $wpdb->prefix . 'exams';
            $class = get_post_meta($student_post->ID, 'class', true);
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT name, exam_date, class_id FROM $exams_table 
                 WHERE education_center_id = %s AND class_id = %s 
                 ORDER BY exam_date DESC LIMIT 20",
                $educational_center_id,
                $class
            ));
            echo '<h4 class="mb-3">Exam Results</h4>';
            if ($results) {
                echo '<table>';
                echo '<thead><tr><th>Exam Name</th><th>Date</th><th>Class</th></tr></thead>';
                echo '<tbody>';
                foreach ($results as $exam) {
                    echo '<tr>';
                    echo '<td>' . esc_html($exam->name) . '</td>';
                    echo '<td>' . esc_html($exam->exam_date) . '</td>';
                    echo '<td>' . esc_html($exam->class_id) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-muted">No exam results found.</p>';
            }
            break;

        case 'homework':
            $homework_table = $wpdb->prefix . 'homework';
            $subjects_table = $wpdb->prefix . 'subjects';
            $class = get_post_meta($student_post->ID, 'class', true);
            $section = get_post_meta($student_post->ID, 'section', true);
            $homework = $wpdb->get_results($wpdb->prepare(
                "SELECT h.*, s.subject_name FROM $homework_table h 
                 LEFT JOIN $subjects_table s ON h.subject_id = s.subject_id 
                 WHERE h.education_center_id = %s AND h.class_name = %s AND h.section = %s 
                 ORDER BY h.due_date DESC LIMIT 20",
                $educational_center_id,
                $class,
                $section
            ));
            echo '<h4 class="mb-3">Homework Assignments</h4>';
            if ($homework) {
                echo '<table>';
                echo '<thead><tr><th>Title</th><th>Subject</th><th>Due Date</th><th>Status</th></tr></thead>';
                echo '<tbody>';
                foreach ($homework as $task) {
                    $now = new DateTime(current_time('Y-m-d'));
                    $due_date = new DateTime($task->due_date);
                    $status = $task->status === 'completed' ? 'Completed' : ($due_date < $now ? 'Overdue' : 'Pending');
                    echo '<tr>';
                    echo '<td>' . esc_html($task->title) . '</td>';
                    echo '<td>' . esc_html($task->subject_name ?: 'N/A') . '</td>';
                    echo '<td>' . esc_html($task->due_date) . '</td>';
                    echo '<td>' . esc_html($status) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-muted">No homework assignments found.</p>';
            }
            break;

        default:
            echo '<p class="text-danger">Invalid action selected.</p>';
            break;
    }
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
    exit;
}

// Helper Functions
function get_last_attendance_status($student_id, $educational_center_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    return $wpdb->get_var($wpdb->prepare(
        "SELECT status FROM $table_name 
         WHERE student_id = %s AND education_center_id = %s 
         ORDER BY date DESC LIMIT 1",
        $student_id,
        $educational_center_id
    )) ?: 'Not recorded';
}

function get_last_result($student_id, $educational_center_id) {
    global $wpdb;
    $exams_table = $wpdb->prefix . 'exams';
    $student_post = get_posts([
        'post_type' => 'students',
        'meta_query' => [['key' => 'student_id', 'value' => $student_id]]
    ])[0];
    $class = get_post_meta($student_post->ID, 'class', true);
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT name FROM $exams_table 
         WHERE education_center_id = %s AND class_id = %s 
         ORDER BY exam_date DESC LIMIT 1",
        $educational_center_id,
        $class
    ));
    return $exam ? $exam->name : 'Not recorded';
}

//child attendance
function render_child_attendance($parent_user, $parent_post_id) {
    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account.</div>';
    }

    $nonce = wp_create_nonce('child_attendance_nonce');
    $educational_center_id = educational_center_parent_id();
    $center_data = get_educational_center_data_teachers($educational_center_id); // Assuming this function exists
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? ''; // Web-accessible URL
    ob_start();
    ?>
    <div class="child-attendance card shadow-sm border-0">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="bi bi-calendar-check me-2"></i>Child Attendance</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="attendance-filters mb-3 d-flex gap-3 flex-wrap">
                <select id="child-select" class="form-select w-auto">
                    <option value="">Select a Child</option>
                    <?php
                    foreach (array_filter(explode(',', $student_ids)) as $student_id) {
                        $student = get_posts([
                            'post_type' => 'students',
                            'meta_query' => [['key' => 'student_id', 'value' => trim($student_id)]]
                        ])[0];
                        if ($student) {
                            $name = get_post_meta($student->ID, 'student_name', true) ?: get_the_title($student->ID);
                            echo '<option value="' . esc_attr($student_id) . '">' . esc_html($name) . ' (' . esc_html($student_id) . ')</option>';
                        }
                    }
                    ?>
                </select>
                <select id="period-filter" class="form-select w-auto">
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month" selected>Month</option>
                </select>
                <input type="date" id="date-picker" class="form-control w-auto" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div id="attendance-content" class="attendance-table-wrapper">
                <p class="loading text-muted">Select a child and period to view attendance...</p>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const childSelect = document.getElementById('child-select');
            const periodFilter = document.getElementById('period-filter');
            const datePicker = document.getElementById('date-picker');
            const contentArea = document.getElementById('attendance-content');
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = '<?php echo esc_attr($nonce); ?>';

            function getAttendanceData() {
                const table = document.querySelector('.attendance-table');
                if (!table) return [];
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                    return Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
                });
                return [headers, ...rows];
            }

            function exportToCSV(studentId) {
                const data = getAttendanceData();
                const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `attendance_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel(studentId) {
                if (!window.XLSX) {
                    console.error('XLSX library not loaded');
                    return;
                }
                const data = getAttendanceData();
                const ws = XLSX.utils.aoa_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
                XLSX.writeFile(wb, `attendance_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function generatePDF(studentId) {
    const data = getAttendanceData();
    const studentName = childSelect.options[childSelect.selectedIndex]?.text.split(' (')[0] || 'Unknown';
    const period = periodFilter.value.charAt(0).toUpperCase() + periodFilter.value.slice(1);
    generate_reusable_pdf({
        student_id: studentId,
        student_name: studentName,
        title: `Attendance Report (${period})`,
        table_data: data,
        details: [
            ['Period', period],
            ['Date', datePicker.value]
        ],
        filename_prefix: 'attendance',
        orientation: 'portrait',
        institute_name: '<?php echo esc_js($institute_name); ?>',
        institute_logo: '<?php echo esc_js($institute_logo); ?>'
    });
    }

            function copyToClipboard() {
                const data = getAttendanceData();
                const text = data.map(row => row.join('\t')).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Attendance copied to clipboard!'));
            }

            function loadAttendance() {
                const studentId = childSelect.value;
                const period = periodFilter.value;
                const date = datePicker.value;
                if (!studentId) {
                    contentArea.innerHTML = '<p class="text-muted">Please select a child.</p>';
                    return;
                }

                contentArea.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading attendance...</p>';
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_child_attendance&nonce=${nonce}&student_id=${encodeURIComponent(studentId)}&period=${encodeURIComponent(period)}&date=${encodeURIComponent(date)}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('AJAX Response:', data); // Debug the response
                    if (data && typeof data === 'object') {
                        if (data.success && data.data && data.data.html) {
                            contentArea.innerHTML = data.data.html;
                            setupExportButtons(studentId);
                        } else {
                            contentArea.innerHTML = `<p class="text-danger">${(data.data && data.data.message) || 'Failed to load attendance'}</p>`;
                        }
                    } else {
                        contentArea.innerHTML = '<p class="text-danger">Invalid response from server</p>';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    contentArea.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                });
            }

            function setupExportButtons(studentId) {
                const tools = document.createElement('div');
                tools.className = 'export-tools';
                tools.innerHTML = `
                    <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                    <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                    <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                    <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                    <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
                `;
                contentArea.prepend(tools);

                tools.querySelector('.export-csv').addEventListener('click', () => exportToCSV(studentId));
                tools.querySelector('.export-pdf').addEventListener('click', () => generatePDF(studentId));
                tools.querySelector('.export-excel').addEventListener('click', () => exportToExcel(studentId));
                tools.querySelector('.export-copy').addEventListener('click', copyToClipboard);
                tools.querySelector('.export-print').addEventListener('click', () => window.print());
            }

            childSelect.addEventListener('change', loadAttendance);
            periodFilter.addEventListener('change', loadAttendance);
            datePicker.addEventListener('change', loadAttendance);
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_get_child_attendance', 'get_child_attendance_callback');
function get_child_attendance_callback() {
    global $wpdb;

    if (!check_ajax_referer('child_attendance_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $period = sanitize_text_field($_POST['period']);
    $date = sanitize_text_field($_POST['date']);
    $educational_center_id = educational_center_parent_id();
    $table_name = $wpdb->prefix . 'student_attendance';

    $student_post = get_posts([
        'post_type' => 'students',
        'meta_query' => [['key' => 'student_id', 'value' => $student_id]]
    ])[0];
    if (!$student_post) {
        wp_send_json_error(['message' => 'Student not found']);
        exit;
    }

    $start_date = new DateTime($date);
    $end_date = clone $start_date;

    switch ($period) {
        case 'day':
            break;
        case 'week':
            $start_date->modify('monday this week');
            $end_date->modify('sunday this week');
            break;
        case 'month':
            $start_date->modify('first day of this month');
            $end_date->modify('last day of this month');
            break;
    }

    $query = $wpdb->prepare(
        "SELECT date, status, subject 
         FROM $table_name 
         WHERE student_id = %s AND education_center_id = %s 
         AND date BETWEEN %s AND %s 
         ORDER BY date DESC",
        $student_id,
        $educational_center_id,
        $start_date->format('Y-m-d'),
        $end_date->format('Y-m-d')
    );
    $attendance = $wpdb->get_results($query);

    $counts = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
    foreach ($attendance as $record) {
        $counts[$record->status] = ($counts[$record->status] ?? 0) + 1;
    }
    $total_days = array_sum($counts);
    $percentage = $total_days ? round(($counts['Present'] / $total_days) * 100, 2) : 0;

    ob_start();
    ?>
    <table class="attendance-table">
        <thead>
            <tr><th>Date</th><th>Status</th><th>Subject</th></tr>
        </thead>
        <tbody>
            <?php if ($attendance): ?>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record->date); ?></td>
                        <td class="<?php echo esc_attr(strtolower($record->status)); ?>"><?php echo esc_html($record->status); ?></td>
                        <td><?php echo esc_html($record->subject ?: 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="summary-row">
                    <td>Total</td>
                    <td colspan="2">
                        Present: <?php echo $counts['Present']; ?> | 
                        Absent: <?php echo $counts['Absent']; ?> | 
                        Late: <?php echo $counts['Late']; ?> | 
                        Percentage: <?php echo $percentage; ?>%
                    </td>
                </tr>
            <?php else: ?>
                <tr><td colspan="3">No attendance records found for this period.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
    exit;
}
//timetable 
function render_child_timetable($parent_user, $parent_post_id) {
    global $wpdb;

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account.</div>';
    }

    $nonce = wp_create_nonce('child_timetable_nonce');
    $educational_center_id = educational_center_parent_id();

    // Fetch educational center details
    $center_data = get_educational_center_data_teachers($educational_center_id); // Assuming this function exists
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? ''; // Web-accessible URL

    ob_start();
    ?>
  <div class="child-timetable">
        <div class="timetable-header">
            <h3><i class="fas fa-calendar-alt"></i> Child Timetable</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body p-4">
            <div class="timetable-filters mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="child-select-wrapper position-relative">
                    <select id="child-select" class="form-select w-auto">
                        <option value="">Select a Child</option>
                        <?php
                        foreach (array_filter(explode(',', $student_ids)) as $student_id) {
                            $student = get_posts([
                                'post_type' => 'students',
                                'meta_query' => [['key' => 'student_id', 'value' => trim($student_id)]]
                            ])[0];
                            if ($student) {
                                $name = get_post_meta($student->ID, 'student_name', true) ?: get_the_title($student->ID);
                                echo '<option value="' . esc_attr($student_id) . '" data-name="' . esc_attr($name) . '">' . esc_html($name) . ' (' . esc_html($student_id) . ')</option>';
                            }
                        }
                        ?>
                    </select>
                    <span id="child-loading" class="spinner-border spinner-border-sm position-absolute" style="display: none; right: 30px; top: 50%; transform: translateY(-50%);"></span>
                </div>
            </div>
            <div id="timetable-content" class="timetable-table-wrapper"></div>
        </div>
    </div>

    <script>
       

        // Main script
        document.addEventListener('DOMContentLoaded', function() {
            const childSelect = document.getElementById('child-select');
            const contentArea = document.getElementById('timetable-content');
            const childLoading = document.getElementById('child-loading');
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = '<?php echo esc_attr($nonce); ?>';

            function getTimetableData() {
                const table = document.querySelector('.timetable-table');
                if (!table) return [];
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                    return Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
                });
                return [headers, ...rows];
            }

            function exportToCSV(studentId) {
                const data = getTimetableData();
                const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `timetable_${studentId}_<?php echo date('Ymd'); ?>.csv`;
                link.click();
            }

            function exportToExcel(studentId) {
                if (!window.XLSX) {
                    console.error('XLSX library not loaded');
                    return;
                }
                const data = getTimetableData();
                const ws = XLSX.utils.aoa_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Timetable');
                XLSX.writeFile(wb, `timetable_${studentId}_<?php echo date('Ymd'); ?>.xlsx`);
            }

            function generatePDF(studentId) {
                const data = getTimetableData();
                const studentName = childSelect.selectedOptions[0]?.dataset.name || 'Unknown';
                generate_reusable_pdf({
                    student_id: studentId,
                    student_name: studentName,
                    title: 'Weekly Timetable',
                    table_data: data,
                    details: [],
                    filename_prefix: 'timetable',
                    orientation: 'landscape',
                    institute_name: '<?php echo esc_js($institute_name); ?>',
                    institute_logo: '<?php echo esc_js($institute_logo); ?>'
                });
            }

            function copyToClipboard() {
                const data = getTimetableData();
                const text = data.map(row => row.join('\t')).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Timetable copied to clipboard!'));
            }

            function printTimetable(studentId) {
                const printWindow = window.open('', '_blank');
                const data = getTimetableData();
                const studentName = childSelect.selectedOptions[0]?.dataset.name || 'Unknown';
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';

                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Timetable - ${studentId}</title>
                        <style>
                            @media print {
                                body { font-family: Helvetica, sans-serif; margin: 10mm; }
                                .page { border: 4px solid #1a2b5f; padding: 5mm; width: 100%; }
                                .header { text-align: center; border-bottom: 2px solid #1a2b5f; margin-bottom: 10mm; }
                                .header img { width: 60px; height: 60px; border-radius: 50%; margin-bottom: 5mm; }
                                .header h1 { font-size: 18pt; color: #1a2b5f; margin: 0; text-transform: uppercase; }
                                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                                table { width: 100%; border-collapse: collapse; margin: 10mm 0; }
                                th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                                th { background: #1a2b5f; color: white; font-weight: bold; }
                                tr:nth-child(even) { background: #f9f9f9; }
                                td:first-child { background: #f5f5f5; font-weight: bold; }
                                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page">
                            <div class="header">
                                ${logoUrl ? `<img src="${logoUrl}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                                <h1><?php echo esc_html(strtoupper($institute_name)); ?></h1>
                                <p class="subtitle">Weekly Timetable</p>
                            </div>
                            <p><strong>Student Name:</strong> ${studentName}</p>
                            <p><strong>Student ID:</strong> ${studentId}</p>
                            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            <table>
                                <thead>
                                    <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                                </thead>
                                <tbody>
                                    ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                                </tbody>
                            </table>
                            <div class="footer">
                                <p>This is an Online Generated Timetable issued by <?php echo esc_html($institute_name); ?></p>
                                <p>Generated on <?php echo date('Y-m-d'); ?></p>
                                <p>___________________________</p>
                                <p>Registrar / Authorized Signatory</p>
                                <p>Managed by Instituto Educational Center Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            function loadTimetable() {
                const studentId = childSelect.value;
                if (!studentId) {
                    contentArea.innerHTML = '<p class="text-muted">Please select a child.</p>';
                    childLoading.style.display = 'none';
                    return;
                }

                childLoading.style.display = 'inline-block';
                contentArea.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading timetable...</p>';
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_child_timetable&nonce=${nonce}&student_id=${encodeURIComponent(studentId)}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    contentArea.innerHTML = data.success ? data.data.html : `<p class="text-danger">${data.data.message || 'Failed to load timetable'}</p>`;
                    if (data.success) setupExportButtons(studentId);
                })
                .catch(error => {
                    console.error('Timetable Fetch Error:', error);
                    contentArea.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                })
                .finally(() => {
                    childLoading.style.display = 'none';
                });
            }

            function setupExportButtons(studentId) {
                const tools = document.createElement('div');
                tools.className = 'export-tools';
                tools.innerHTML = `
                    <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                    <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                    <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                    <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                    <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
                `;
                contentArea.prepend(tools);

                tools.querySelector('.export-csv').addEventListener('click', () => exportToCSV(studentId));
                tools.querySelector('.export-pdf').addEventListener('click', () => generatePDF(studentId));
                tools.querySelector('.export-excel').addEventListener('click', () => exportToExcel(studentId));
                tools.querySelector('.export-copy').addEventListener('click', () => copyToClipboard);
                tools.querySelector('.export-print').addEventListener('click', () => printTimetable(studentId));
            }

            childSelect.addEventListener('change', loadTimetable);
        });
    </script>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> -->
    <?php
    return ob_get_clean();
}

// Revised AJAX Handler for Timetable
add_action('wp_ajax_get_child_timetable', 'get_child_timetable_callback');
function get_child_timetable_callback() {
    global $wpdb;

    if (!check_ajax_referer('child_timetable_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $educational_center_id = educational_center_parent_id();
    $student_post = get_posts([
        'post_type' => 'students',
        'meta_query' => [['key' => 'student_id', 'value' => $student_id]]
    ])[0];
    if (!$student_post) {
        wp_send_json_error(['message' => 'Student not found']);
        exit;
    }

    $class_name = get_post_meta($student_post->ID, 'class', true);
    $section = get_post_meta($student_post->ID, 'section', true);
    $class_section = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}class_sections 
         WHERE education_center_id = %s AND class_name = %s AND FIND_IN_SET(%s, sections)",
        $educational_center_id, $class_name, $section
    ));

    if (!$class_section) {
        wp_send_json_error(['message' => 'Class or section not found']);
        exit;
    }

    $timetable = $wpdb->get_results($wpdb->prepare(
        "SELECT t.day, t.start_time, t.end_time, t.section, c.class_name, s.subject_name 
         FROM {$wpdb->prefix}timetables t 
         JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
         LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
         WHERE t.education_center_id = %s AND t.class_id = %d AND t.section = %s 
         ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time",
        $educational_center_id, $class_section->id, $section
    ));

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $time_slots = [];
    foreach ($timetable as $slot) {
        $time_slots[$slot->start_time . '-' . $slot->end_time] = true;
    }
    ksort($time_slots);
    $time_slot_array = array_keys($time_slots);

    ob_start();
    ?>
    <div class="table-responsive">
        <table class="timetable-table">
            <thead>
                <tr><th class="day-column">Day</th>
                    <?php foreach ($time_slot_array as $time_range) : ?>
                        <th><?php echo esc_html($time_range); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($timetable): ?>
                    <?php foreach ($days as $day) : ?>
                        <tr>
                            <td class="day-column"><?php echo esc_html($day); ?></td>
                            <?php foreach ($time_slot_array as $time_range) : 
                                [$start, $end] = explode('-', $time_range);
                                $found = false;
                                foreach ($timetable as $slot) :
                                    if ($slot->day === $day && $slot->start_time === $start && $slot->end_time === $end) : ?>
                                        <td class="subject"><?php echo esc_html($slot->class_name . ' (' . $slot->section . ') - ' . ($slot->subject_name ?: 'N/A')); ?></td>
                                        <?php $found = true; break;
                                    endif;
                                endforeach;
                                if (!$found) : ?>
                                    <td class="empty">-</td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="<?php echo count($time_slot_array) + 1; ?>">No timetable entries found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
    exit;
}


// Exams Function
function render_child_exams($parent_user, $parent_post_id) {
    global $wpdb;

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account.</div>';
    }

    $nonce = wp_create_nonce('child_exams_nonce');
    $educational_center_id = educational_center_parent_id();

    // Fetch educational center details for PDF
    $center_data = get_educational_center_data_teachers($educational_center_id); // Assuming this function exists
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? ''; // Web-accessible URL

    ob_start();
    ?>
   <div class="child-exams card shadow-lg" >
        <div class="card-header bg-danger text-white">
            <h3 class="card-title m-0"><i class="bi bi-clipboard-check me-2"></i>Child Exams</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="exam-filters mb-4 d-flex gap-3 flex-wrap">
                <div class="child-select-wrapper">
                    <select id="child-select" class="form-select w-auto">
                        <option value="">Select a Child</option>
                        <?php
                        foreach (array_filter(explode(',', $student_ids)) as $student_id) {
                            $student = get_posts([
                                'post_type' => 'students',
                                'meta_query' => [['key' => 'student_id', 'value' => trim($student_id)]]
                            ])[0];
                            if ($student) {
                                $name = get_post_meta($student->ID, 'student_name', true) ?: get_the_title($student->ID);
                                echo '<option value="' . esc_attr($student_id) . '" data-class-id="' . esc_attr(get_post_meta($student->ID, 'class', true)) . '" data-name="' . esc_attr($name) . '">' . esc_html($name) . ' (' . esc_html($student_id) . ')</option>';
                            }
                        }
                        ?>
                    </select>
                    <span id="child-loading" class="spinner-border spinner-border-sm position-absolute" style="display: none; right: 30px; top: 50%; transform: translateY(-50%);"></span>
                </div>
                <select id="exam-filter" class="form-select w-auto">
                    <option value="all" selected>All</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past</option>
                </select>
            </div>
            <div id="exams-content" class="exams-table-wrapper">
                <p class="loading text-muted">Select a child to view exams...</p>
            </div>
        </div>
    </div>
    <!-- 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> -->
   
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const childSelect = document.getElementById('child-select');
            const examFilter = document.getElementById('exam-filter');
            const contentArea = document.getElementById('exams-content');
            const childLoading = document.getElementById('child-loading');
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = '<?php echo esc_attr($nonce); ?>';

            function getExamsData() {
                const table = document.querySelector('.exams-table');
                if (!table) return [];
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                    return Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
                });
                return [headers, ...rows];
            }

            function exportToCSV(studentId) {
                const data = getExamsData();
                const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `exams_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel(studentId) {
                if (!window.XLSX) {
                    console.error('XLSX library not loaded');
                    return;
                }
                const data = getExamsData();
                const ws = XLSX.utils.aoa_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Exams');
                XLSX.writeFile(wb, `exams_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function generatePDF(studentId) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                const data = getExamsData();
                const studentName = childSelect.selectedOptions[0]?.dataset.name || 'Unknown';
                const filterText = examFilter.options[examFilter.selectedIndex].text;
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';
                const pageWidth = doc.internal.pageSize.width;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10;
                const borderColor = [220, 53, 69]; // #dc3545

                // Page border
                doc.setDrawColor(...borderColor);
                doc.setLineWidth(1);
                doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

                // Header
                if (logoUrl) {
                    try {
                        doc.addImage(logoUrl, 'PNG', (pageWidth - 24) / 2, 15, 24, 24);
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
                doc.text('Exam Schedule', pageWidth / 2, 55, { align: 'center' });
                doc.setDrawColor(...borderColor);
                doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

                // Student Details
                const details = [
                    ['Student Name', studentName],
                    ['Student ID', studentId],
                    ['Filter', filterText],
                    ['Date', new Date().toLocaleDateString()]
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

                // Exams Table
                if (typeof doc.autoTable === 'function') {
                    doc.autoTable({
                        startY: y + 10,
                        head: [data[0]],
                        body: data.slice(1),
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
                        alternateRowStyles: { fillColor: [249, 249, 249] } // #f9f9f9
                    });

                    // Footer
                    const finalY = doc.lastAutoTable.finalY || y + 10;
                    doc.setFontSize(9);
                    doc.setTextColor(102); // #666
                    doc.text(`This is an Online Generated Exam Schedule issued by <?php echo esc_js($institute_name); ?>`, pageWidth / 2, finalY + 20, { align: 'center' });
                    doc.text('Generated on <?php echo date('Y-m-d'); ?>', pageWidth / 2, finalY + 25, { align: 'center' });
                    doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                    doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                    doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

                    doc.save(`exams_${studentId}_${new Date().toISOString().slice(0,10)}.pdf`);
                } else {
                    console.error('jsPDF autoTable plugin not loaded');
                    alert('PDF generation failed: autoTable plugin not available');
                }
            }

            function copyToClipboard() {
                const data = getExamsData();
                const text = data.map(row => row.join('\t')).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Exams copied to clipboard!'));
            }

            function printExams(studentId) {
                const printWindow = window.open('', '_blank');
                const data = getExamsData();
                const studentName = childSelect.selectedOptions[0]?.dataset.name || 'Unknown';
                const filterText = examFilter.options[examFilter.selectedIndex].text;
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';

                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Exam Schedule - ${studentId}</title>
                        <style>
                            @media print {
                                body { font-family: Helvetica, sans-serif; margin: 10mm; }
                                .page { border: 4px solid #dc3545; padding: 5mm; }
                                .header { text-align: center; border-bottom: 2px solid #dc3545; margin-bottom: 10mm; }
                                .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                                .header h1 { font-size: 18pt; color: #dc3545; margin: 0; text-transform: uppercase; }
                                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                                table { width: 100%; border-collapse: collapse; margin: 10mm 0; }
                                th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; }
                                th { background: #dc3545; color: white; font-weight: bold; }
                                tr:nth-child(even) { background: #f9f9f9; }
                                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page">
                            <div class="header">
                                ${logoUrl ? `<img src="${logoUrl}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                                <h1><?php echo esc_html(strtoupper($institute_name)); ?></h1>
                                <p class="subtitle">Exam Schedule</p>
                            </div>
                            <p><strong>Student Name:</strong> ${studentName}</p>
                            <p><strong>Student ID:</strong> ${studentId}</p>
                            <p><strong>Filter:</strong> ${filterText}</p>
                            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            <table>
                                <thead>
                                    <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                                </thead>
                                <tbody>
                                    ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                                </tbody>
                            </table>
                            <div class="footer">
                                <p>This is an Online Generated Exam Schedule issued by <?php echo esc_html($institute_name); ?></p>
                                <p>Generated on <?php echo date('Y-m-d'); ?></p>
                                <p>___________________________</p>
                                <p>Registrar / Authorized Signatory</p>
                                <p>Managed by Instituto Educational Center Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            function loadExams() {
                const studentId = childSelect.value;
                const classId = childSelect.selectedOptions[0]?.dataset.classId;
                const filter = examFilter.value;
                if (!studentId || !classId) {
                    contentArea.innerHTML = '<p class="text-muted">Please select a child.</p>';
                    childLoading.style.display = 'none';
                    return;
                }

                childLoading.style.display = 'inline-block';
                contentArea.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading exams...</p>';
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_child_exams&nonce=${nonce}&student_id=${encodeURIComponent(studentId)}&class_id=${encodeURIComponent(classId)}&filter=${encodeURIComponent(filter)}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('AJAX Response:', data);
                    contentArea.innerHTML = data.success ? data.data.html : `<p class="text-danger">${data.data.message || 'Failed to load exams'}</p>`;
                    if (data.success) setupExportButtons(studentId);
                })
                .catch(error => {
                    console.error('Exams Fetch Error:', error);
                    contentArea.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                })
                .finally(() => {
                    childLoading.style.display = 'none';
                });
            }

            function setupExportButtons(studentId) {
                const tools = document.createElement('div');
                tools.className = 'export-tools';
                tools.innerHTML = `
                    <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                    <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                    <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                    <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                    <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
                `;
                contentArea.prepend(tools);

                tools.querySelector('.export-csv').addEventListener('click', () => exportToCSV(studentId));
                tools.querySelector('.export-pdf').addEventListener('click', () => generatePDF(studentId));
                tools.querySelector('.export-excel').addEventListener('click', () => exportToExcel(studentId));
                tools.querySelector('.export-copy').addEventListener('click', () => copyToClipboard);
                tools.querySelector('.export-print').addEventListener('click', () => printExams(studentId));
            }

            childSelect.addEventListener('change', loadExams);
            examFilter.addEventListener('change', loadExams);
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_get_child_exams', 'get_child_exams_callback');
function get_child_exams_callback() {
    global $wpdb;

    if (!check_ajax_referer('child_exams_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $class_id = sanitize_text_field($_POST['class_id']);
    $filter = sanitize_text_field($_POST['filter']);
    $educational_center_id = educational_center_parent_id();

    if (empty($educational_center_id)) {
        wp_send_json_error(['message' => 'No educational center found']);
        exit;
    }

    if (empty($class_id)) {
        wp_send_json_error(['message' => 'Class ID missing']);
        exit;
    }

    $exams_table = $wpdb->prefix . 'exams';
    $current_date = current_time('Y-m-d');

    $query = "SELECT name, exam_date, class_id 
              FROM $exams_table 
              WHERE education_center_id = %s 
              AND class_id = %s";
    $query_args = [$educational_center_id, $class_id];

    if ($filter === 'upcoming') {
        $query .= " AND exam_date >= %s";
        $query_args[] = $current_date;
    } elseif ($filter === 'past') {
        $query .= " AND exam_date < %s";
        $query_args[] = $current_date;
    }

    $query .= " ORDER BY exam_date ASC";
    $exams = $wpdb->get_results($wpdb->prepare($query, $query_args));

    if ($wpdb->last_error) {
        error_log('get_child_exams_callback: SQL Error - ' . $wpdb->last_error);
        error_log('Query: ' . $wpdb->last_query);
    } else {
        error_log('get_child_exams_callback: Query - ' . $wpdb->last_query);
        error_log('Results count: ' . count($exams));
    }

    ob_start();
    ?>
    <table class="exams-table">
        <thead>
            <tr><th>Exam Name</th><th>Date</th><th>Class ID</th></tr>
        </thead>
        <tbody>
            <?php if ($exams): ?>
                <?php foreach ($exams as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam->name); ?></td>
                        <td><?php echo esc_html($exam->exam_date); ?></td>
                        <td><?php echo esc_html($exam->class_id); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">No exams found for this filter.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
    exit;
}

// Results Function
function render_child_results($parent_user, $parent_post_id) {
    global $wpdb;

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account.</div>';
    }

    $nonce = wp_create_nonce('child_results_nonce');
    $educational_center_id = educational_center_parent_id();

    // Fetch educational center details for PDF
    $center_data = get_educational_center_data_teachers($educational_center_id); // Assuming this function exists
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? ''; // Web-accessible URL

    ob_start();
    ?>
    <div class="child-results card shadow-lg">
        <div class="card-header bg-primary text-white" >
            <h3 class="card-title mb-0"><i class="bi bi-award me-2"></i>Child Results</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body p-4">
            <div class="results-filters mb-4 d-flex gap-3 flex-wrap">
                <div class="child-select-wrapper position-relative">
                    <select id="child-select" class="form-select w-auto">
                        <option value="">Select a Child</option>
                        <?php
                        foreach (array_filter(explode(',', $student_ids)) as $student_id) {
                            $student = get_posts([
                                'post_type' => 'students',
                                'meta_query' => [['key' => 'student_id', 'value' => trim($student_id)]]
                            ])[0];
                            if ($student) {
                                $name = get_post_meta($student->ID, 'student_name', true) ?: get_the_title($student->ID);
                                echo '<option value="' . esc_attr($student_id) . '" data-class-id="' . esc_attr(get_post_meta($student->ID, 'class', true)) . '" data-name="' . esc_attr($name) . '">' . esc_html($name) . ' (' . esc_html($student_id) . ')</option>';
                            }
                        }
                        ?>
                    </select>
                    <span id="child-loading" class="spinner-border spinner-border-sm position-absolute" style="display: none; right: 30px; top: 50%; transform: translateY(-50%);"></span>
                </div>
                <select id="exam-select" class="form-select w-auto" disabled>
                    <option value="">Select an Exam</option>
                </select>
            </div>
            <div id="results-content" class="results-table-wrapper">
                <p class="loading text-muted">Select a child and exam to view results...</p>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const childSelect = document.getElementById('child-select');
            const examSelect = document.getElementById('exam-select');
            const contentArea = document.getElementById('results-content');
            const childLoading = document.getElementById('child-loading');
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = '<?php echo esc_attr($nonce); ?>';

            function getResultsData() {
                const table = document.querySelector('.results-table');
                if (!table) return [];
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                const rows = Array.from(table.querySelectorAll('tbody tr, tfoot tr')).map(row => {
                    return Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
                });
                return [headers, ...rows];
            }

            function exportToCSV(studentId, examId) {
                const data = getResultsData();
                const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `results_${studentId}_${examId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel(studentId, examId) {
                if (!window.XLSX) {
                    console.error('XLSX library not loaded');
                    return;
                }
                const data = getResultsData();
                const ws = XLSX.utils.aoa_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Results');
                XLSX.writeFile(wb, `results_${studentId}_${examId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function generatePDF(studentId, examId) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                const data = getResultsData();
                const studentName = childSelect.selectedOptions[0]?.dataset.name || 'Unknown';
                const examName = examSelect.options[examSelect.selectedIndex]?.text || 'Unknown Exam';
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';
                const pageWidth = doc.internal.pageSize.width;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10;
                const borderColor = [0, 123, 255]; // #007bff

                // Page border
                doc.setDrawColor(...borderColor);
                doc.setLineWidth(1);
                doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

                // Header
                if (logoUrl) {
                    try {
                        doc.addImage(logoUrl, 'PNG', (pageWidth - 24) / 2, 15, 24, 24);
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
                doc.text('Marksheet', pageWidth / 2, 55, { align: 'center' });
                doc.setDrawColor(...borderColor);
                doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

                // Student Details
                const details = [
                    ['Student Name', studentName],
                    ['Student ID', studentId],
                    ['Examination', examName],
                    ['Date', new Date().toLocaleDateString()]
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

                // Results Table
                if (typeof doc.autoTable === 'function') {
                    doc.autoTable({
                        startY: y + 10,
                        head: [data[0]],
                        body: data.slice(1),
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
                            if (data.row.section === 'footer') {
                                data.cell.styles.fillColor = [230, 240, 250]; // #e6f0fa
                                data.cell.styles.fontStyle = 'bold';
                            }
                        }
                    });

                    // Footer
                    const finalY = doc.lastAutoTable.finalY || y + 10;
                    doc.setFontSize(9);
                    doc.setTextColor(102); // #666
                    doc.text(`This is an Online Generated Marksheet issued by <?php echo esc_js($institute_name); ?>`, pageWidth / 2, finalY + 20, { align: 'center' });
                    doc.text('Generated on <?php echo date('Y-m-d'); ?>', pageWidth / 2, finalY + 25, { align: 'center' });
                    doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                    doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                    doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

                    doc.save(`marksheet_${studentId}_${examId}_${new Date().toISOString().slice(0,10)}.pdf`);
                } else {
                    console.error('jsPDF autoTable plugin not loaded');
                    alert('PDF generation failed: autoTable plugin not available');
                }
            }

            function copyToClipboard() {
                const data = getResultsData();
                const text = data.map(row => row.join('\t')).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Results copied to clipboard!'));
            }

            function printResults(studentId, examId) {
                const printWindow = window.open('', '_blank');
                const data = getResultsData();
                const studentName = childSelect.selectedOptions[0]?.dataset.name || 'Unknown';
                const examName = examSelect.options[examSelect.selectedIndex]?.text || 'Unknown Exam';
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';

                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Marksheet - ${studentId} - ${examId}</title>
                        <style>
                            @media print {
                                body { font-family: Helvetica, sans-serif; margin: 10mm; }
                                .page { border: 4px solid #007bff; padding: 5mm; }
                                .header { text-align: center; border-bottom: 2px solid #007bff; margin-bottom: 10mm; }
                                .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                                .header h1 { font-size: 18pt; color: #007bff; margin: 0; text-transform: uppercase; }
                                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                                table { width: 100%; border-collapse: collapse; margin: 10mm 0; }
                                th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; }
                                th { background: #007bff; color: white; font-weight: bold; }
                                tr:nth-child(even) { background: #f9f9f9; }
                                tfoot td { background: #e6f3ff; font-weight: bold; }
                                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page">
                            <div class="header">
                                ${logoUrl ? `<img src="${logoUrl}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                                <h1><?php echo esc_html(strtoupper($institute_name)); ?></h1>
                                <p class="subtitle">Marksheet</p>
                            </div>
                            <p><strong>Student Name:</strong> ${studentName}</p>
                            <p><strong>Student ID:</strong> ${studentId}</p>
                            <p><strong>Examination:</strong> ${examName}</p>
                            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            <table>
                                <thead>
                                    <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                                </thead>
                                <tbody>
                                    ${data.slice(1, -2).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                                </tbody>
                                <tfoot>
                                    ${data.slice(-2).map(row => `<tr>${row.map(cell => `<td${row[0] === 'Percentage' ? ' colspan="2"' : ''}>${cell}</td>`).join('')}</tr>`).join('')}
                                </tfoot>
                            </table>
                            <div class="footer">
                                <p>This is an Online Generated Marksheet issued by <?php echo esc_html($institute_name); ?></p>
                                <p>Generated on <?php echo date('Y-m-d'); ?></p>
                                <p>___________________________</p>
                                <p>Registrar / Authorized Signatory</p>
                                <p>Managed by Instituto Educational Center Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            function loadExams(studentId, classId) {
                childLoading.style.display = 'inline-block';
                examSelect.disabled = true;
                examSelect.innerHTML = '<option value="">Loading exams...</option>';

                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_child_exams_for_results&nonce=${nonce}&student_id=${encodeURIComponent(studentId)}&class_id=${encodeURIComponent(classId)}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Parsed Exams Response:', data);
                    if (data.success && data.data && data.data.exams) {
                        examSelect.innerHTML = '<option value="">Select an Exam</option>';
                        data.data.exams.forEach(exam => {
                            const option = document.createElement('option');
                            option.value = exam.id;
                            option.textContent = `${exam.name} (${exam.exam_date})`;
                            examSelect.appendChild(option);
                        });
                        examSelect.disabled = false;
                    } else {
                        examSelect.innerHTML = '<option value="">No exams available</option>';
                        examSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Exams Fetch Error:', error);
                    examSelect.innerHTML = '<option value="">Error loading exams</option>';
                })
                .finally(() => {
                    childLoading.style.display = 'none';
                });
            }

            function loadResults() {
                const studentId = childSelect.value;
                const examId = examSelect.value;
                if (!studentId || !examId) {
                    contentArea.innerHTML = '<p class="text-muted">Please select a child and an exam.</p>';
                    return;
                }

                contentArea.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading results...</p>';
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_child_results&nonce=${nonce}&student_id=${encodeURIComponent(studentId)}&exam_id=${encodeURIComponent(examId)}`
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw Results Response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed Results Response:', data);
                        contentArea.innerHTML = data.success ? data.data.html : `<p class="text-danger">${data.data.message || 'Failed to load results'}</p>`;
                        if (data.success) setupExportButtons(studentId, examId);
                    } catch (e) {
                        console.error('Results JSON Parse Error:', e, 'Raw Response:', text);
                        contentArea.innerHTML = '<p class="text-danger">Error parsing results data</p>';
                    }
                })
                .catch(error => {
                    console.error('Results Fetch Error:', error);
                    contentArea.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                });
            }

            function setupExportButtons(studentId, examId) {
                const tools = document.createElement('div');
                tools.className = 'export-tools';
                tools.innerHTML = `
                    <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                    <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Download Marksheet</span></button>
                    <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                    <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                    <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
                `;
                contentArea.prepend(tools);

                tools.querySelector('.export-csv').addEventListener('click', () => exportToCSV(studentId, examId));
                tools.querySelector('.export-pdf').addEventListener('click', () => generatePDF(studentId, examId));
                tools.querySelector('.export-excel').addEventListener('click', () => exportToExcel(studentId, examId));
                tools.querySelector('.export-copy').addEventListener('click', () => copyToClipboard);
                tools.querySelector('.export-print').addEventListener('click', () => printResults(studentId, examId));
            }

            childSelect.addEventListener('change', function() {
                const studentId = this.value;
                const classId = this.selectedOptions[0]?.dataset.classId;
                contentArea.innerHTML = '<p class="text-muted">Select a child and exam to view results...</p>';
                if (studentId && classId) {
                    loadExams(studentId, classId);
                } else {
                    examSelect.disabled = true;
                    examSelect.innerHTML = '<option value="">Select an Exam</option>';
                    childLoading.style.display = 'none';
                }
            });

            examSelect.addEventListener('change', loadResults);
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_get_child_results', 'get_child_results_callback');
function get_child_results_callback() {
    global $wpdb;

    while (ob_get_level() > 0) ob_end_clean();

    if (!check_ajax_referer('child_results_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce'], 403);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id'] ?? '');
    $exam_id = sanitize_text_field($_POST['exam_id'] ?? '');
    $educational_center_id = educational_center_parent_id();

    error_log('get_child_results_callback: student_id=' . $student_id . ', exam_id=' . $exam_id . ', education_center_id=' . $educational_center_id);

    if (empty($educational_center_id)) {
        wp_send_json_error(['message' => 'No educational center found'], 400);
        exit;
    }

    if (empty($student_id) || empty($exam_id)) {
        wp_send_json_error(['message' => 'Missing student ID or exam ID'], 400);
        exit;
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT id, subject_name, max_marks 
         FROM {$wpdb->prefix}exam_subjects 
         WHERE exam_id = %d",
        $exam_id
    ));

    if ($wpdb->last_error) {
        error_log('get_child_results_callback: Subjects SQL Error - ' . $wpdb->last_error);
        error_log('Subjects Query: ' . $wpdb->last_query);
        wp_send_json_error(['message' => 'Database error fetching subjects: ' . $wpdb->last_error], 500);
        exit;
    }

    if (empty($subjects)) {
        wp_send_json_error(['message' => 'No subjects found for this exam'], 404);
        exit;
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.subject_id, er.marks 
         FROM {$wpdb->prefix}exam_results er 
         WHERE er.exam_id = %d 
         AND er.student_id = %s 
         AND er.education_center_id = %s",
        $exam_id,
        $student_id,
        $educational_center_id
    ), OBJECT_K);

    if ($wpdb->last_error) {
        error_log('get_child_results_callback: Results SQL Error - ' . $wpdb->last_error);
        error_log('Results Query: ' . $wpdb->last_query);
        wp_send_json_error(['message' => 'Database error fetching results: ' . $wpdb->last_error], 500);
        exit;
    }

    error_log('get_child_results_callback: Subjects count: ' . count($subjects));
    error_log('get_child_results_callback: Results count: ' . count($results));

    $results_data = [];
    $total_obtained = 0;
    $total_max = 0;
    foreach ($subjects as $subject) {
        $marks = isset($results[$subject->id]) ? $results[$subject->id]->marks : 'N/A';
        $results_data[] = [
            'subject_name' => $subject->subject_name,
            'marks' => $marks,
            'max_marks' => $subject->max_marks
        ];
        if (is_numeric($marks)) {
            $total_obtained += (float) $marks;
            $total_max += (float) $subject->max_marks;
        } else {
            $total_max += (float) $subject->max_marks;
        }
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
            <?php foreach ($results_data as $row): ?>
                <tr>
                    <td><?php echo esc_html($row['subject_name']); ?></td>
                    <td><?php echo esc_html($row['marks']); ?></td>
                    <td><?php echo esc_html($row['max_marks']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
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
    </table>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
    exit;
}

add_action('wp_ajax_get_child_exams_for_results', 'get_child_exams_for_results_callback');
function get_child_exams_for_results_callback() {
    global $wpdb;

    while (ob_get_level() > 0) ob_end_clean();

    if (!check_ajax_referer('child_results_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce'], 403);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id'] ?? '');
    $class_id = sanitize_text_field($_POST['class_id'] ?? '');
    $educational_center_id = educational_center_parent_id();

    error_log('get_child_exams_for_results_callback: student_id=' . $student_id . ', class_id=' . $class_id . ', education_center_id=' . $educational_center_id);

    // if (empty($educational_center_id)) {
    //     wp_send_json_error(['message' => 'No educational center found'], 400);
    //     exit;
    // }

    if (empty($class_id)) {
        wp_send_json_error(['message' => 'Class ID missing'], 400);
        exit;
    }

    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT id, name, exam_date 
         FROM {$wpdb->prefix}exams 
         WHERE education_center_id = %s AND class_id = %s 
         ORDER BY exam_date DESC",
        $educational_center_id,
        $class_id
    ));

    if ($wpdb->last_error) {
        error_log('get_child_exams_for_results_callback: SQL Error - ' . $wpdb->last_error);
        error_log('Query: ' . $wpdb->last_query);
        wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error], 500);
        exit;
    } else {
        error_log('get_child_exams_for_results_callback: Query - ' . $wpdb->last_query);
        error_log('Results count: ' . count($exams));
    }

    wp_send_json_success(['exams' => $exams]);
    exit;
}

//noticeboard
function render_parent_notice_board($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    // Fetch announcements directly from wp_aspire_announcements
    $table = $wpdb->prefix . 'aspire_announcements';
    $query = $wpdb->prepare(
        "SELECT sender_id, receiver_id, message, timestamp 
         FROM $table 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'students', 'parents') 
         ORDER BY timestamp DESC 
         LIMIT 10",
        $educational_center_id
    );

    $announcements = $wpdb->get_results($query);

    if ($wpdb->last_error) {
        error_log('render_parent_notice_board: SQL Error - ' . $wpdb->last_error);
        return '<div class="alert alert-danger">Database error: ' . esc_html($wpdb->last_error) . '</div>';
    }

    // Recipient labels (based on your sample code)
    $recipients = [
        'all' => 'Everyone',
        'students' => 'Students',
        'parents' => 'Parents',
        'teachers' => 'Teachers',
        'institute_admins' => 'Institute Admins'
    ];

    ob_start();
    ?>
    <div class="parent-notice-board card shadow-lg">
        <div class="card-header bg-info text-white">
            <h3 class="card-title mb-0"><i class="fas fa-bell me-2"></i>Notice Board</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body p-4">
            <div id="notice-board-content" class="notice-board-content announcement-list">
                <?php if ($announcements): ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="notice-item announcement-item">
                            <div class="notice-header announcement-content">
                                <span class="notice-meta announcement-meta">
                                    <?php echo esc_html($ann->sender_id); ?> 
                                    to <?php echo esc_html($recipients[$ann->receiver_id] ?? ucfirst($ann->receiver_id)); ?>
                                </span>
                                <p class="notice-content announcement-message"><?php echo esc_html($ann->message); ?></p>
                            </div>
                            <span class="notice-date announcement-timestamp" data-timestamp="<?php echo esc_attr($ann->timestamp); ?>">
                                <?php echo esc_html(date('M d, Y H:i', strtotime($ann->timestamp))); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No announcements available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateTimestamps() {
            document.querySelectorAll('.notice-date').forEach(span => {
                const timestamp = span.dataset.timestamp;
                if (typeof moment !== 'undefined') {
                    span.textContent = moment(timestamp).fromNow();
                } else {
                    console.log('Moment.js not loaded');
                }
            });
        }

        // Update timestamps on load
        updateTimestamps();

        // Auto-refresh the page every 30 seconds
        setInterval(() => {
            window.location.reload();
        }, 30000);
    });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <?php
    return ob_get_clean();
}

// Helper function to get username
function aspire_parent_get_username($post_id) {
    $user_id = get_post_field('post_author', $post_id);
    $user = get_user_by('id', $user_id);
    $username = $user ? $user->user_login : '';
    error_log("aspire_parent_get_username: post_id=$post_id, user_id=$user_id, username=$username");
    return $username;
}

// Main function for parent communication section
function render_parent_communication($parent_user, $parent_post_id) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to use this chat.</p>';
    }

    global $wpdb;
    $edu_center_id = educational_center_parent_id();
    // if (empty($edu_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $username = $parent_user->user_login;

    // Fetch teachers only as contacts
    $contacts = get_posts([
        'post_type' => 'teacher',
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);

    // Build contact map for teachers
    $contact_map = [];
    foreach ($contacts as $contact) {
        $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
        $teacher_name = get_post_meta($contact->ID, 'teacher_name', true) ?: $contact->post_title;
        if ($contact_id && $contact_id !== $username) {
            $contact_map[$contact_id] = "$teacher_name ($contact_id - Teacher)";
        }
    }

    // Fetch initial conversations relevant to the parent
    $table = $wpdb->prefix . 'aspire_messages';
    $group_types = ['all', 'teachers', 'institute_admins', 'parents'];
    $group_names = [
        'all' => 'Everyone in Center',
        'teachers' => 'Teachers',
        'institute_admins' => 'Institute Admins',
        'parents' => 'Parents'
    ];

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
        AND receiver_id IN ('all', 'parents')
        AND sender_id != %s
    ";
    $query_args = [$edu_center_id, $username, $edu_center_id, $username, $edu_center_id, $username];
    $active_conversations = $wpdb->get_results($wpdb->prepare($query, $query_args));

    ob_start();
    ?>
    <div class="parent-communication card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0"><i class="fas fa-comments me-2"></i>Communication</h3>
        </div>
        <div class="card-body p-0">
            <div class="chat-wrapper">
                <div class="chat-sidebar">
                    <div class="sidebar-header">
                        <h4>Inbox</h4>
                    </div>
                    <ul id="aspire-parent-conversations" class="conversation-list">
                        <?php
                        $has_conversations = false;
                        foreach ($active_conversations as $conv) {
                            $conv_with = $conv->conversation_with;
                            if (isset($group_names[$conv_with])) {
                                $name = "Group: " . $group_names[$conv_with];
                            } else {
                                $name = $contact_map[$conv_with] ?? (get_user_by('login', $conv_with)->display_name ?? $conv_with);
                            }
                            echo '<li class="conversation-item" data-conversation-with="' . esc_attr($conv_with) . '">' . esc_html($name) . '</li>';
                            $has_conversations = true;
                        }
                        if (!$has_conversations) {
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
                    <div id="aspire-parent-message-list" class="chat-messages"></div>
                    <form id="aspire-parent-send-form" class="chat-form">
                        <div class="input-group">
                            <textarea id="aspire-parent-message-input" class="form-control" placeholder="Type your message..." rows="1" required></textarea>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
                        </div>
                        <div id="recipient-select" class="recipient-select" style="display:none;">
                            <select id="aspire-parent-target-value" class="form-select">
                                <option value="">Select a recipient</option>
                                <option value="all">All</option>
                                <option value="teachers">All Teachers</option>
                                <?php
                                foreach ($contacts as $contact) {
                                    $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
                                    $teacher_name = get_post_meta($contact->ID, 'teacher_name', true) ?: $contact->post_title;
                                    if ($contact_id && $contact_id !== $username) {
                                        echo '<option value="' . esc_attr($contact_id) . '">' . esc_html("$teacher_name ($contact_id - Teacher)") . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <?php wp_nonce_field('aspire_parent_nonce', 'aspire_parent_nonce_field'); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        let selectedConversation = localStorage.getItem('aspire_parent_selected_conversation') || '';
        let currentRecipient = '';

        function fetchMessages(conversationWith) {
            $('#aspire-parent-message-list').html('<div class="chat-loading active"><div class="spinner"></div><p>Loading messages...</p></div>');
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_parent_fetch_messages',
                    conversation_with: conversationWith,
                    nonce: $('#aspire_parent_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-parent-message-list').html(response.data.html);
                        const chatMessages = document.querySelector('#aspire-parent-message-list');
                        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
                        updateTimestamps();
                    } else {
                        $('#aspire-parent-message-list').html('<p>Error loading messages.</p>');
                        console.error('Fetch messages failed:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $('#aspire-parent-message-list').html('<p>Network error occurred.</p>');
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

        $(document).on('click', '#aspire-parent-conversations li', function() {
            $('#aspire-parent-conversations li').removeClass('active');
            $(this).addClass('active');
            selectedConversation = $(this).data('conversation-with');
            currentRecipient = selectedConversation;
            localStorage.setItem('aspire_parent_selected_conversation', selectedConversation);
            $('#current-conversation').text($(this).text());
            $('#clear-conversation').show();
            $('#new-chat').show();
            $('#recipient-select').hide();
            fetchMessages(selectedConversation);
        });

        $('#clear-conversation').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_parent_selected_conversation');
            $('#current-conversation').text('Select a conversation');
            $('#clear-conversation').hide();
            $('#new-chat').show();
            $('#recipient-select').hide();
            $('#aspire-parent-message-list').empty();
            $('#aspire-parent-conversations li').removeClass('active');
        });

        $('#new-chat').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_parent_selected_conversation');
            $('#current-conversation').text('New Conversation');
            $('#clear-conversation').show();
            $('#new-chat').hide();
            $('#recipient-select').show();
            $('#aspire-parent-message-list').empty();
            $('#aspire-parent-conversations li').removeClass('active');
            $('#aspire-parent-target-value').val('');
        });

        $('#aspire-parent-send-form').submit(function(e) {
            e.preventDefault();
            const message = $('#aspire-parent-message-input').val().trim();
            if (!message) return;

            let targetValue = $('#recipient-select').is(':visible') ? $('#aspire-parent-target-value').val() : currentRecipient;
            if (!targetValue) {
                alert('Please select a recipient.');
                return;
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_parent_send_message',
                    message: message,
                    target_value: targetValue,
                    nonce: $('#aspire_parent_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-parent-message-input').val('');
                        if ($('#recipient-select').is(':visible')) {
                            currentRecipient = targetValue;
                            $('#current-conversation').text($(`#aspire-parent-target-value option[value="${targetValue}"]`).text());
                            $('#clear-conversation').show();
                            $('#new-chat').hide();
                            $('#recipient-select').hide();
                        }
                        fetchMessages(currentRecipient);
                        setTimeout(() => location.reload(), 500);
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

        if (selectedConversation) {
            $(`#aspire-parent-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
            $('#current-conversation').text($(`#aspire-parent-conversations li[data-conversation-with="${selectedConversation}"]`).text());
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

// AJAX Handlers
add_action('wp_ajax_aspire_parent_send_message', 'aspire_parent_ajax_send_message');
function aspire_parent_ajax_send_message() {
    check_ajax_referer('aspire_parent_nonce', 'nonce');

    $edu_center_id = educational_center_parent_id();
    $user = wp_get_current_user();
    $sender_id = $user->user_login;
    $message = sanitize_text_field($_POST['message'] ?? '');
    $target_value = sanitize_text_field($_POST['target_value'] ?? '');

    if (!$edu_center_id || !$sender_id || !$message || !$target_value) {
        wp_redirect(home_url('/login'));
        exit();  
    }

    $success = aspire_parent_send_message($sender_id, $target_value, $message, $edu_center_id);

    if ($success) {
        wp_send_json_success(['success' => 'Message sent!']);
    } else {
        wp_send_json_error(['error' => 'Failed to send message']);
    }
}

function aspire_parent_send_message($sender_id, $receiver_id, $message, $education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';

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

add_action('wp_ajax_aspire_parent_fetch_messages', 'aspire_parent_ajax_fetch_messages');
function aspire_parent_ajax_fetch_messages() {
    ob_start();
    check_ajax_referer('aspire_parent_nonce', 'nonce');

    $user = wp_get_current_user();
    $username = $user->user_login;
    $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');

    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = educational_center_parent_id();

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
            $query .= " AND (receiver_id = %s OR (sender_id = %s AND receiver_id = %s)) LIMIT 50";
            $query_args[] = $conversation_with;
            $query_args[] = $username;
            $query_args[] = $conversation_with;
        } else {
            $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s)) LIMIT 50";
            $query_args[] = $username;
            $query_args[] = $conversation_with;
            $query_args[] = $conversation_with;
            $query_args[] = $username;
        }
    }

    $messages = $wpdb->get_results($wpdb->prepare($query, $query_args));

    $contacts = get_posts([
        'post_type' => 'teacher',
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);

    $contact_map = [];
    foreach ($contacts as $contact) {
        $contact_id = get_post_meta($contact->ID, 'teacher_id', true);
        $teacher_name = get_post_meta($contact->ID, 'teacher_name', true) ?: $contact->post_title;
        if ($contact_id) {
            $contact_map[$contact_id] = "$teacher_name ($contact_id - Teacher)";
        }
    }

    $output = '';
    foreach ($messages as $msg) {
        if (in_array($msg->receiver_id, $group_types)) {
            $sender_name = ($msg->sender_id === $username) ? 'You' : ($contact_map[$msg->sender_id] ?? (get_user_by('login', $msg->sender_id)->display_name ?? $msg->sender_id));
            $receiver_display = "Group: " . ($group_names[$msg->receiver_id] ?? $msg->receiver_id);
        } else {
            $sender_name = ($msg->sender_id === $username) ? 'You' : ($contact_map[$msg->sender_id] ?? (get_user_by('login', $msg->sender_id)->display_name ?? $msg->sender_id));
            $receiver_display = $contact_map[$msg->receiver_id] ?? (get_user_by('login', $msg->receiver_id)->display_name ?? $msg->receiver_id);
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

//fees
function render_parent_fees($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'student_id', 'value' => trim($student_id), 'compare' => '='],
                ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[$student_id] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'post_id' => $student_post->ID
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    $nonce = wp_create_nonce('parent_fees_nonce');
    $default_student_id = array_key_first($children);

    ob_start();
    ?>
    <div class="parent-fees card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="fas fa-wallet me-2"></i>Fees Overview</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="child-selector mb-4">
                <label for="child_select" class="me-2">Select Child:</label>
                <select id="child_select" data-nonce="<?php echo esc_attr($nonce); ?>">
                    <?php foreach ($children as $child): ?>
                        <option value="<?php echo esc_attr($child->student_id); ?>">
                            <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="fees-content" class="fees-content">
                <p class="loading text-muted">Loading fees for <?php echo esc_html($children[$default_student_id]->name); ?>...</p>
            </div>
        </div>

        <!-- External Libraries -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('child_select');
            const content = document.getElementById('fees-content');
            const nonce = select.dataset.nonce;

            function getFeesData() {
                const rows = document.querySelectorAll('#fees-table tbody tr:not(.total-row)');
                return Array.from(rows).map(row => {
                    const cells = row.querySelectorAll('td');
                    return {
                        month: cells[0].textContent,
                        type: cells[1].textContent,
                        amount: cells[2].textContent,
                        status: cells[3].textContent,
                        method: cells[4].textContent,
                        date: cells[5].textContent
                    };
                });
            }

            function generatePDF(studentId, instituteName) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                const pageWidth = doc.internal.pageSize.width;
                const margin = 10;
                const borderColor = [70, 130, 180];

                doc.setDrawColor(...borderColor);
                doc.setLineWidth(0.5);
                doc.rect(margin, margin, pageWidth - 2 * margin, doc.internal.pageSize.height - 2 * margin);

                doc.setFontSize(16);
                doc.setTextColor(...borderColor);
                doc.text(instituteName.toUpperCase(), pageWidth / 2, 20, { align: 'center' });
                doc.setFontSize(10);
                doc.setTextColor(51);
                doc.text('Fees Report', pageWidth / 2, 28, { align: 'center' });
                doc.line(margin + 5, 32, pageWidth - margin - 5, 32);

                const data = getFeesData();
                doc.autoTable({
                    startY: 40,
                    head: [['Month', 'Fee Type', 'Amount', 'Status', 'Payment Method', 'Paid Date']],
                    body: data.map(d => [d.month, d.type, d.amount, d.status, d.method, d.date]),
                    theme: 'striped',
                    styles: { fontSize: 9, cellPadding: 2, halign: 'center' },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255] },
                });

                doc.setFontSize(8);
                doc.setTextColor(102);
                doc.text(`Generated on <?php echo date('Y-m-d'); ?>`, pageWidth / 2, doc.internal.pageSize.height - 10, { align: 'center' });
                doc.save(`fees_${studentId}_${new Date().toISOString().slice(0,10)}.pdf`);
            }

            function exportToCSV(studentId) {
                const data = getFeesData();
                const csvContent = [
                    '"Month","Fee Type","Amount","Status","Payment Method","Paid Date"',
                    ...data.map(d => `"${d.month}","${d.type}","${d.amount}","${d.status}","${d.method}","${d.date}"`)
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `fees_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel(studentId) {
                if (!window.XLSX) return;
                const data = getFeesData();
                const ws = XLSX.utils.json_to_sheet(data, { header: ['month', 'type', 'amount', 'status', 'method', 'date'] });
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Fees');
                XLSX.writeFile(wb, `fees_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function copyToClipboard() {
                const data = getFeesData();
                const text = data.map(d => `${d.month}: ${d.type}, ${d.amount}, ${d.status}, ${d.method}, ${d.date}`).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Fees data copied to clipboard!'));
            }

            function loadFees(studentId) {
                content.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading fees...</p>';
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_parent_fees_data&student_id=${encodeURIComponent(studentId)}&nonce=${nonce}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        content.innerHTML = data.data.html;
                        setupExportButtons(studentId);
                    } else {
                        content.innerHTML = `<p class="text-danger">${data.data.message || 'Failed to load fees'}</p>`;
                    }
                })
                .catch(error => {
                    content.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                });
            }

            function setupExportButtons(studentId) {
                const exportCsvBtn = content.querySelector('.export-csv');
                const exportPdfBtn = content.querySelector('.export-pdf');
                const exportExcelBtn = content.querySelector('.export-excel');
                const copyBtn = content.querySelector('.export-copy');
                const printBtn = content.querySelector('.export-print');

                if (exportCsvBtn) exportCsvBtn.addEventListener('click', () => exportToCSV(studentId));
                if (exportPdfBtn) exportPdfBtn.addEventListener('click', () => generatePDF(studentId, '<?php echo esc_js(get_educational_center_data_student($educational_center_id)['name'] ?? 'INSTITUTE'); ?>'));
                if (exportExcelBtn) exportExcelBtn.addEventListener('click', () => exportToExcel(studentId));
                if (copyBtn) copyBtn.addEventListener('click', copyToClipboard);
                if (printBtn) printBtn.addEventListener('click', () => window.print());
            }

            loadFees(select.value);
            select.addEventListener('change', () => loadFees(select.value));
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler for Fees Data
add_action('wp_ajax_get_parent_fees_data', 'get_parent_fees_data_callback');
function get_parent_fees_data_callback() {
    global $wpdb;

    if (!check_ajax_referer('parent_fees_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $educational_center_id = educational_center_parent_id();

    $student_post = get_posts([
        'post_type' => 'students',
        'meta_query' => [
            ['key' => 'student_id', 'value' => $student_id],
            ['key' => 'educational_center_id', 'value' => $educational_center_id]
        ]
    ])[0] ?? null;

    if (!$student_post) {
        wp_send_json_error(['message' => 'Student not found']);
        exit;
    }

    $table_name = $wpdb->prefix . 'student_fees';
    $template_table = $wpdb->prefix . 'fee_templates';
    $fees = $wpdb->get_results($wpdb->prepare(
        "SELECT sf.*, ft.name AS template_name 
         FROM $table_name sf 
         LEFT JOIN $template_table ft ON sf.template_id = ft.id 
         WHERE sf.education_center_id = %s AND sf.student_id = %s 
         ORDER BY sf.month_year DESC",
        $educational_center_id,
        $student_id
    ));

    ob_start();
    ?>
    <div class="fees-overview">
        <div class="export-tools mb-3">
            <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
            <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
            <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
            <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
        </div>
        <table id="fees-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Paid Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_amount = 0;
                $total_paid = 0;
                foreach ($fees as $fee) {
                    $total_amount += floatval($fee->amount);
                    if ($fee->status === 'paid') $total_paid += floatval($fee->amount);
                    ?>
                    <tr>
                        <td><?php echo esc_html(date('F Y', strtotime($fee->month_year))); ?></td>
                        <td><?php echo esc_html($fee->template_name ?: 'N/A'); ?></td>
                        <td><?php echo esc_html(number_format($fee->amount, 2)); ?></td>
                        <td><span class="status-badge <?php echo $fee->status === 'paid' ? 'status-paid' : 'status-overdue'; ?>">
                            <?php echo esc_html(ucfirst($fee->status)); ?>
                        </span></td>
                        <td><?php echo esc_html($fee->payment_method ?: 'N/A'); ?></td>
                        <td><?php echo esc_html($fee->paid_date ?: 'N/A'); ?></td>
                    </tr>
                <?php } ?>
                <tr class="total-row">
                    <td colspan="2"><strong>Total Amount:</strong></td>
                    <td><?php echo esc_html(number_format($total_amount, 2)); ?></td>
                    <td colspan="2"><strong>Total Paid:</strong></td>
                    <td><?php echo esc_html(number_format($total_paid, 2)); ?></td>
                </tr>
            </tbody>
        </table>
        <?php if (empty($fees)) echo '<p class="text-muted">No fee records found.</p>'; ?>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
    exit;
}

//transport
function render_parent_transport_fees($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'student_id', 'value' => trim($student_id), 'compare' => '='],
                ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[$student_id] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'post_id' => $student_post->ID
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    $nonce = wp_create_nonce('parent_transport_fees_nonce');
    $default_student_id = array_key_first($children);

    ob_start();
    ?>
    <div class="parent-transport-fees card shadow-sm border-0">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="fas fa-bus me-2"></i>Transport Fees Overview</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="child-selector mb-4">
                <label for="child_select" class="me-2">Select Child:</label>
                <select id="child_select" data-nonce="<?php echo esc_attr($nonce); ?>">
                    <?php foreach ($children as $child): ?>
                        <option value="<?php echo esc_attr($child->student_id); ?>">
                            <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="transport-fees-content" class="transport-fees-content">
                <p class="loading text-muted">Loading transport fees for <?php echo esc_html($children[$default_student_id]->name); ?>...</p>
            </div>
        </div>

        <!-- External Libraries -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('child_select');
            const content = document.getElementById('transport-fees-content');
            const nonce = select.dataset.nonce;

            function getTransportFeesData() {
                const rows = document.querySelectorAll('#transport-fees-table tbody tr:not(.total-row)');
                return Array.from(rows).map(row => {
                    const cells = row.querySelectorAll('td');
                    return {
                        month: cells[0].textContent,
                        type: cells[1].textContent,
                        amount: cells[2].textContent,
                        status: cells[3].textContent,
                        method: cells[4].textContent,
                        date: cells[5].textContent
                    };
                });
            }

            function generatePDF(studentId, instituteName) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                const pageWidth = doc.internal.pageSize.width;
                const margin = 10;
                const borderColor = [70, 130, 180];

                doc.setDrawColor(...borderColor);
                doc.setLineWidth(0.5);
                doc.rect(margin, margin, pageWidth - 2 * margin, doc.internal.pageSize.height - 2 * margin);

                doc.setFontSize(16);
                doc.setTextColor(...borderColor);
                doc.text(instituteName.toUpperCase(), pageWidth / 2, 20, { align: 'center' });
                doc.setFontSize(10);
                doc.setTextColor(51);
                doc.text('Transport Fees Report', pageWidth / 2, 28, { align: 'center' });
                doc.line(margin + 5, 32, pageWidth - margin - 5, 32);

                const data = getTransportFeesData();
                doc.autoTable({
                    startY: 40,
                    head: [['Month', 'Fee Type', 'Amount', 'Status', 'Payment Method', 'Paid Date']],
                    body: data.map(d => [d.month, d.type, d.amount, d.status, d.method, d.date]),
                    theme: 'striped',
                    styles: { fontSize: 9, cellPadding: 2, halign: 'center' },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255] },
                });

                doc.setFontSize(8);
                doc.setTextColor(102);
                doc.text(`Generated on <?php echo date('Y-m-d'); ?>`, pageWidth / 2, doc.internal.pageSize.height - 10, { align: 'center' });
                doc.save(`transport_fees_${studentId}_${new Date().toISOString().slice(0,10)}.pdf`);
            }

            function exportToCSV(studentId) {
                const data = getTransportFeesData();
                const csvContent = [
                    '"Month","Fee Type","Amount","Status","Payment Method","Paid Date"',
                    ...data.map(d => `"${d.month}","${d.type}","${d.amount}","${d.status}","${d.method}","${d.date}"`)
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `transport_fees_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel(studentId) {
                if (!window.XLSX) return;
                const data = getTransportFeesData();
                const ws = XLSX.utils.json_to_sheet(data, { header: ['month', 'type', 'amount', 'status', 'method', 'date'] });
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Transport Fees');
                XLSX.writeFile(wb, `transport_fees_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function copyToClipboard() {
                const data = getTransportFeesData();
                const text = data.map(d => `${d.month}: ${d.type}, ${d.amount}, ${d.status}, ${d.method}, ${d.date}`).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Transport fees data copied to clipboard!'));
            }

            function loadTransportFees(studentId) {
                content.innerHTML = '<p class="loading text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading transport fees...</p>';
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_parent_transport_fees_data&student_id=${encodeURIComponent(studentId)}&nonce=${nonce}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        content.innerHTML = data.data.html;
                        setupExportButtons(studentId);
                    } else {
                        content.innerHTML = `<p class="text-danger">${data.data.message || 'Failed to load transport fees'}</p>`;
                    }
                })
                .catch(error => {
                    content.innerHTML = `<p class="text-danger">Error: ${error.message}</p>`;
                });
            }

            function setupExportButtons(studentId) {
                const exportCsvBtn = content.querySelector('.export-csv');
                const exportPdfBtn = content.querySelector('.export-pdf');
                const exportExcelBtn = content.querySelector('.export-excel');
                const copyBtn = content.querySelector('.export-copy');
                const printBtn = content.querySelector('.export-print');

                if (exportCsvBtn) exportCsvBtn.addEventListener('click', () => exportToCSV(studentId));
                if (exportPdfBtn) exportPdfBtn.addEventListener('click', () => generatePDF(studentId, '<?php echo esc_js(get_educational_center_data_student($educational_center_id)['name'] ?? 'INSTITUTE'); ?>'));
                if (exportExcelBtn) exportExcelBtn.addEventListener('click', () => exportToExcel(studentId));
                if (copyBtn) copyBtn.addEventListener('click', copyToClipboard);
                if (printBtn) printBtn.addEventListener('click', () => window.print());
            }

            loadTransportFees(select.value);
            select.addEventListener('change', () => loadTransportFees(select.value));
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler for Transport Fees Data
add_action('wp_ajax_get_parent_transport_fees_data', 'get_parent_transport_fees_data_callback');
function get_parent_transport_fees_data_callback() {
    global $wpdb;

    if (!check_ajax_referer('parent_transport_fees_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $student_id = sanitize_text_field($_POST['student_id']);
    $educational_center_id = educational_center_parent_id();

    $student_post = get_posts([
        'post_type' => 'students',
        'meta_query' => [
            ['key' => 'student_id', 'value' => $student_id],
            ['key' => 'educational_center_id', 'value' => $educational_center_id]
        ]
    ])[0] ?? null;

    if (!$student_post) {
        wp_send_json_error(['message' => 'Student not found']);
        exit;
    }

    $table_name = $wpdb->prefix . 'transport_fees';
    $template_table = $wpdb->prefix . 'fee_templates';
    $fees = $wpdb->get_results($wpdb->prepare(
        "SELECT tf.*, ft.name AS template_name 
         FROM $table_name tf 
         LEFT JOIN $template_table ft ON tf.template_id = ft.id 
         WHERE tf.education_center_id = %s AND tf.student_id = %s 
         ORDER BY tf.month_year DESC",
        $educational_center_id,
        $student_id
    ));

    ob_start();
    ?>
    <div class="transport-fees-overview">
        <div class="export-tools mb-3">
            <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
            <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
            <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
            <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
        </div>
        <table id="transport-fees-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Paid Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_amount = 0;
                $total_paid = 0;
                foreach ($fees as $fee) {
                    $total_amount += floatval($fee->amount);
                    if ($fee->status === 'paid') $total_paid += floatval($fee->amount);
                    ?>
                    <tr>
                        <td><?php echo esc_html(date('F Y', strtotime($fee->month_year))); ?></td>
                        <td><?php echo esc_html($fee->template_name ?: 'N/A'); ?></td>
                        <td><?php echo esc_html(number_format($fee->amount, 2)); ?></td>
                        <td><span class="status-badge <?php echo $fee->status === 'paid' ? 'status-paid' : 'status-overdue'; ?>">
                            <?php echo esc_html(ucfirst($fee->status)); ?>
                        </span></td>
                        <td><?php echo esc_html($fee->payment_method ?: 'N/A'); ?></td>
                        <td><?php echo esc_html($fee->paid_date ?: 'N/A'); ?></td>
                    </tr>
                <?php } ?>
                <tr class="total-row">
                    <td colspan="2"><strong>Total Amount:</strong></td>
                    <td><?php echo esc_html(number_format($total_amount, 2)); ?></td>
                    <td colspan="2"><strong>Total Paid:</strong></td>
                    <td><?php echo esc_html(number_format($total_paid, 2)); ?></td>
                </tr>
            </tbody>
        </table>
        <?php if (empty($fees)) echo '<p class="text-muted">No transport fee records found.</p>'; ?>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
    exit;
}


//calender
function render_parent_calendar($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'student_id', 'value' => trim($student_id), 'compare' => '='],
                ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[$student_id] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'post_id' => $student_post->ID
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    // Fetch educational center details for PDF
    $center_data = get_educational_center_data_teachers($educational_center_id); // Assuming this exists
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? '';

    // Fetch calendar events server-side
    $events_by_student = [];
    $all_events = [];

    foreach ($children as $student_id => $child) {
        $class_id = $child->class;
        $events = [];

        // Fetch Exams
        $exams_table = $wpdb->prefix . 'exams';
        $exams = $wpdb->get_results($wpdb->prepare(
            "SELECT name, exam_date, class_id 
             FROM $exams_table 
             WHERE education_center_id = %s 
             AND class_id = %s",
            $educational_center_id,
            $class_id
        ));
        foreach ($exams as $exam) {
            $events[] = [
                'title' => $exam->name,
                'start' => $exam->exam_date,
                'type' => 'exam',
                'student_id' => $student_id
            ];
            $all_events[] = $events[count($events) - 1];
        }

        // Fetch Fees
        $fees_table = $wpdb->prefix . 'student_fees';
        $template_table = $wpdb->prefix . 'fee_templates';
        $fees = $wpdb->get_results($wpdb->prepare(
            "SELECT sf.month_year, sf.template_id 
             FROM $fees_table sf 
             WHERE sf.education_center_id = %s 
             AND sf.student_id = %s",
            $educational_center_id,
            $student_id
        ));
        foreach ($fees as $fee) {
            $template_name = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM $template_table WHERE id = %d",
                $fee->template_id
            ));
            $events[] = [
                'title' => "Fee Due: " . ($template_name ?: 'N/A'),
                'start' => date('Y-m-01', strtotime($fee->month_year)),
                'type' => 'fee',
                'student_id' => $student_id
            ];
            $all_events[] = $events[count($events) - 1];
        }

        // Fetch Transport Fees
        $transport_table = $wpdb->prefix . 'transport_fees';
        $transport_fees = $wpdb->get_results($wpdb->prepare(
            "SELECT tf.month_year, tf.template_id 
             FROM $transport_table tf 
             WHERE tf.education_center_id = %s 
             AND tf.student_id = %s",
            $educational_center_id,
            $student_id
        ));
        foreach ($transport_fees as $fee) {
            $template_name = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM $template_table WHERE id = %d",
                $fee->template_id
            ));
            $events[] = [
                'title' => "Transport Fee Due: " . ($template_name ?: 'N/A'),
                'start' => date('Y-m-01', strtotime($fee->month_year)),
                'type' => 'transport_fee',
                'student_id' => $student_id
            ];
            $all_events[] = $events[count($events) - 1];
        }

        $events_by_student[$student_id] = $events;
    }

    // Fetch Notices (applicable to all students)
    $notice_table = $wpdb->prefix . 'aspire_announcements';
    $notices = $wpdb->get_results($wpdb->prepare(
        "SELECT message, timestamp 
         FROM $notice_table 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'students', 'parents')",
        $educational_center_id
    ));
    foreach ($notices as $notice) {
        $notice_event = [
            'title' => "Notice: " . substr($notice->message, 0, 30) . "...",
            'start' => date('Y-m-d', strtotime($notice->timestamp)),
            'type' => 'notice',
            'student_id' => 'all'
        ];
        $all_events[] = $notice_event;
        foreach ($children as $student_id => $child) {
            $events_by_student[$student_id][] = $notice_event;
        }
    }

    // Encode events for JavaScript
    $events_json = json_encode([
        'all' => $all_events,
        'by_student' => $events_by_student
    ]);

    ob_start();
    ?>
    <div class="parent-calendar card shadow-sm border-0">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="fas fa-calendar-alt me-2"></i>Calendar</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body">
            <div class="calendar-controls mb-4 d-flex gap-3 flex-wrap">
                <div class="child-selector">
                    <label for="child_select" class="me-2">Select Child:</label>
                    <select id="child_select" class="form-select w-auto">
                        <option value="all">All Children</option>
                        <?php foreach ($children as $child): ?>
                            <option value="<?php echo esc_attr($child->student_id); ?>" data-name="<?php echo esc_attr($child->name); ?>">
                                <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="export-tools">
                    <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                    <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                    <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                    <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                    <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
                </div>
            </div>
            <div id="calendar" class="calendar-container"></div>
        </div>
    </div>

    <!-- External Libraries -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const childSelect = document.getElementById('child_select');
            const eventsData = <?php echo $events_json; ?>;
            let calendar;

            function initializeCalendar(events) {
                if (calendar) calendar.destroy();
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: events,
                    eventClick: function(info) {
                        alert(`${info.event.title}\n${info.event.start.toLocaleString()}`);
                    },
                    eventBackgroundColor: function(arg) {
                        switch (arg.event.extendedProps.type) {
                            case 'exam': return '#dc3545'; // Red
                            case 'fee': return '#007bff'; // Blue
                            case 'transport_fee': return '#28a745'; // Green
                            case 'notice': return '#17a2b8'; // Cyan
                            default: return '#6c757d'; // Gray
                        }
                    },
                    height: 'auto',
                    contentHeight: 'auto'
                });
                calendar.render();
            }

            function getCalendarEvents() {
                return calendar.getEvents().map(event => ({
                    title: event.title,
                    start: event.start.toISOString().slice(0, 10),
                    type: event.extendedProps.type
                }));
            }

            function generatePDF(studentId) {
                const data = getCalendarEvents();
                const studentName = childSelect.value === 'all' ? 'All Children' : (childSelect.selectedOptions[0]?.dataset.name || 'Unknown');
                generate_reusable_pdf({
                    student_id: studentId,
                    student_name: studentName,
                    title: 'Calendar Events',
                    table_data: data.map(e => [e.title, e.start, e.type.replace('_', ' ').toUpperCase()]),
                    details: [
                        ['Generated', new Date().toLocaleDateString()]
                    ],
                    filename_prefix: 'calendar',
                    orientation: 'portrait',
                    institute_name: '<?php echo esc_js($institute_name); ?>',
                    institute_logo: '<?php echo esc_js($institute_logo); ?>'
                });
            }

            function exportToCSV() {
                const events = getCalendarEvents();
                const csvContent = [
                    '"Title","Date","Type"',
                    ...events.map(e => `"${e.title}","${e.start}","${e.type.replace('_', ' ').toUpperCase()}"`)
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const studentId = childSelect.value === 'all' ? 'all' : childSelect.value;
                link.href = URL.createObjectURL(blob);
                link.download = `calendar_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel() {
                if (!window.XLSX) return;
                const events = getCalendarEvents();
                const ws = XLSX.utils.json_to_sheet(events, { header: ['title', 'start', 'type'] });
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Calendar');
                const studentId = childSelect.value === 'all' ? 'all' : childSelect.value;
                XLSX.writeFile(wb, `calendar_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function copyToClipboard() {
                const events = getCalendarEvents();
                const text = events.map(e => `${e.title}: ${e.start} (${e.type})`).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Calendar events copied to clipboard!'));
            }

            function loadCalendar(studentId) {
                const events = studentId === 'all' ? eventsData.all : (eventsData.by_student[studentId] || []);
                initializeCalendar(events);
            }

            // Setup export buttons
            document.querySelector('.export-pdf').addEventListener('click', () => generatePDF(childSelect.value));
            document.querySelector('.export-csv').addEventListener('click', exportToCSV);
            document.querySelector('.export-excel').addEventListener('click', exportToExcel);
            document.querySelector('.export-copy').addEventListener('click', copyToClipboard);
            document.querySelector('.export-print').addEventListener('click', () => window.print());

            // Load initial calendar
            loadCalendar('all');
            childSelect.addEventListener('change', () => loadCalendar(childSelect.value));
        });
    </script>

    <?php
    return ob_get_clean();
}

//homework
function render_parent_homework($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'student_id', 'value' => trim($student_id), 'compare' => '='],
                ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[$student_id] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'class' => get_post_meta($student_post->ID, 'class', true) ?: 'N/A',
                'section' => get_post_meta($student_post->ID, 'section', true) ?: 'N/A',
                'post_id' => $student_post->ID
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    // Fetch homework for all children
    $homework_table = $wpdb->prefix . 'homework';
    $subjects_table = $wpdb->prefix . 'subjects';
    $homeworks_by_student = [];
    $all_homeworks = [];

    foreach ($children as $student_id => $child) {
        $class_name = $child->class;
        $section = $child->section;
        $homeworks = $wpdb->get_results($wpdb->prepare(
            "SELECT h.*, s.subject_name 
             FROM $homework_table h 
             LEFT JOIN $subjects_table s ON h.subject_id = s.subject_id 
             WHERE h.education_center_id = %s 
             AND h.class_name = %s 
             AND h.section = %s",
            $educational_center_id,
            $class_name,
            $section
        ));

        foreach ($homeworks as $homework) {
            $homework->student_id = $student_id;
            $homeworks_by_student[$student_id][] = $homework;
            $all_homeworks[] = $homework;
        }
    }

    $homeworks_json = json_encode([
        'all' => $all_homeworks,
        'by_student' => $homeworks_by_student
    ]);

    $center_data = get_educational_center_data_teachers($educational_center_id);
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? '';

    ob_start();
    ?>
    <div class="parent-homework card shadow-lg">
        <div class="card-header bg-primary text-white" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title m-0"><i class="fas fa-book me-2"></i>Homework/Assignments</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body p-4">
            <div class="child-selector mb-4">
                <label for="child_select" class="me-2">Select Child:</label>
                <select id="child_select" class="form-select">
                    <option value="all" selected>All Children</option>
                    <?php foreach ($children as $child): ?>
                        <option value="<?php echo esc_attr($child->student_id); ?>" data-name="<?php echo esc_attr($child->name); ?>">
                            <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="export-tools mb-4">
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
            </div>
            <div id="homework-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Child</th>
                        </tr>
                    </thead>
                    <tbody id="homework-table-body">
                        <?php if (empty($all_homeworks)): ?>
                            <tr><td colspan="7" class="text-muted">No homework assigned yet for your children.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const childSelect = document.getElementById('child_select');
            const tableBody = document.getElementById('homework-table-body');
            const homeworkData = <?php echo $homeworks_json; ?>;
            const childrenNames = <?php echo json_encode(array_column($children, 'name', 'student_id')); ?>;

            function updateTable(studentId) {
                if (!tableBody) {
                    console.error('Table body not found');
                    return;
                }
                tableBody.innerHTML = ''; // Clear existing rows
                const homeworks = studentId === 'all' ? homeworkData.all : (homeworkData.by_student[studentId] || []);
                if (!homeworks || homeworks.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-muted">No homework assigned for this selection.</td></tr>';
                    return;
                }
                homeworks.forEach(homework => {
                    const now = new Date();
                    const dueDate = new Date(homework.due_date);
                    const status = homework.status === 'completed' ? 'Completed' : (dueDate < now ? 'Overdue' : 'Pending');
                    const row = document.createElement('tr');
                    row.setAttribute('data-student-id', homework.student_id);
                    row.innerHTML = `
                        <td>${escapeHtml(homework.title)}</td>
                        <td>${escapeHtml(homework.subject_name || 'N/A')}</td>
                        <td>${escapeHtml(homework.class_name)}</td>
                        <td>${escapeHtml(homework.section)}</td>
                        <td>${escapeHtml(homework.due_date)}</td>
                        <td class="status-${status.toLowerCase()}">${escapeHtml(status)}</td>
                        <td>${escapeHtml(childrenNames[homework.student_id])}</td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            function escapeHtml(text) {
                const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
                return text.replace(/[&<>"']/g, m => map[m]);
            }

            function getTableData() {
                return Array.from(tableBody.querySelectorAll('tr:not(.text-muted)')).map(row => ({
                    title: row.cells[0].textContent,
                    subject: row.cells[1].textContent,
                    class: row.cells[2].textContent,
                    section: row.cells[3].textContent,
                    due_date: row.cells[4].textContent,
                    status: row.cells[5].textContent,
                    child: row.cells[6].textContent
                }));
            }

            function generatePDF(studentId) {
    const data = getTableData();
    if (data.length === 0) {
        alert('No homework data to export.');
        return;
    }
    const studentName = childSelect.value === 'all' ? 'All Children' : (childSelect.selectedOptions[0]?.dataset.name || 'Unknown');

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'landscape' });
    const pageWidth = doc.internal.pageSize.width;
    const pageHeight = doc.internal.pageSize.height;
    const margin = 10;
    const borderColor = [26, 43, 95]; // #1a2b5f

    // Draw border
    doc.setDrawColor(...borderColor);
    doc.setLineWidth(1);
    doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

    // Add institute logo (if available)
    const instituteLogo = '<?php echo esc_js($institute_logo); ?>';
    if (instituteLogo) {
        try {
            doc.addImage(instituteLogo, 'PNG', (pageWidth - 24) / 2, 15, 24, 24);
        } catch (e) {
            console.log('Logo loading failed:', e);
            doc.setFontSize(10);
            doc.text('No logo available', pageWidth / 2, 20, { align: 'center' });
        }
    }

    // Institute name and title
    const instituteName = '<?php echo esc_js($institute_name); ?>';
    doc.setFontSize(18);
    doc.setTextColor(...borderColor);
    doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
    doc.setFontSize(12);
    doc.setTextColor(102);
    doc.text('Homework/Assignments', pageWidth / 2, 55, { align: 'center' });
    doc.setDrawColor(...borderColor);
    doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

    // Details section
    let y = 70;
    const details = [
        ['Student Name', studentName],
        ['Student ID', studentId],
        ['Date', new Date().toLocaleDateString()],
        ['Generated', new Date().toLocaleDateString()]
    ];
    details.forEach(([label, value]) => {
        doc.setFillColor(245, 245, 245); // Light gray background
        doc.rect(margin + 5, y, 50, 6, 'F');
        doc.setTextColor(...borderColor);
        doc.setFont('helvetica', 'bold');
        doc.text(label, margin + 7, y + 4);
        doc.setTextColor(51);
        doc.setFont('helvetica', 'normal');
        doc.text(value, margin + 60, y + 4);
        y += 6;
    });

    // Table generation
    if (typeof doc.autoTable === 'function') {
        const headers = ['Title', 'Subject', 'Class', 'Section', 'Due Date', 'Status', 'Child'];
        const body = data.map(row => [
            row.title,
            row.subject,
            row.class,
            row.section,
            row.due_date,
            row.status,
            row.child
        ]);

        doc.autoTable({
            startY: y + 10,
            head: [headers],
            body: body,
            theme: 'striped',
            styles: {
                fontSize: 11,
                cellPadding: 2,
                overflow: 'linebreak',
                halign: 'center',
                textColor: [51, 51, 51]
            },
            headStyles: {
                fillColor: borderColor,
                textColor: [255, 255, 255],
                fontStyle: 'bold'
            },
            alternateRowStyles: { fillColor: [249, 249, 249] },
            tableLineColor: [204, 204, 204],
            tableLineWidth: 0.1
        });

        // Footer
        const finalY = doc.lastAutoTable.finalY || y + 10;
        doc.setFontSize(9);
        doc.setTextColor(102);
        doc.text(`This is an Online Generated Homework/Assignments issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
        doc.text(`Generated on ${new Date().toISOString().slice(0, 10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
        doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
        doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
        doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });
    } else {
        console.error('jsPDF autoTable plugin not loaded');
        alert('PDF generation failed: autoTable plugin missing');
        return;
    }

    // Save the PDF
    doc.save(`homework_${studentId}_${new Date().toISOString().slice(0,10)}.pdf`);
    }

            function exportToCSV() {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No homework data to export.');
                    return;
                }
                const csvContent = [
                    '"Title","Subject","Class","Section","Due Date","Status","Child"',
                    ...data.map(h => `"${h.title.replace(/"/g, '""')}","${h.subject.replace(/"/g, '""')}","${h.class.replace(/"/g, '""')}","${h.section.replace(/"/g, '""')}","${h.due_date.replace(/"/g, '""')}","${h.status.replace(/"/g, '""')}","${h.child.replace(/"/g, '""')}"`)
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const studentId = childSelect.value === 'all' ? 'all' : childSelect.value;
                link.href = URL.createObjectURL(blob);
                link.download = `homework_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel() {
                if (!window.XLSX) return;
                const data = getTableData();
                if (data.length === 0) {
                    alert('No homework data to export.');
                    return;
                }
                const ws = XLSX.utils.json_to_sheet(data, { header: ['title', 'subject', 'class', 'section', 'due_date', 'status', 'child'] });
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Homework');
                const studentId = childSelect.value === 'all' ? 'all' : childSelect.value;
                XLSX.writeFile(wb, `homework_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function copyToClipboard() {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No homework data to copy.');
                    return;
                }
                const text = data.map(h => `${h.title}: ${h.due_date} (${h.status}) - ${h.child}`).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Homework copied to clipboard!'));
            }
            function printHomework(studentId) {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No homework data to print.');
                    return;
                }
                const studentName = childSelect.value === 'all' ? 'All Children' : (childSelect.selectedOptions[0]?.dataset.name || 'Unknown');
                const instituteName = '<?php echo esc_js($institute_name); ?>';
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';

                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Homework - ${studentId}</title>
                        <style>
                            @media print {
                                body { font-family: Helvetica, sans-serif; margin: 10mm; }
                                .page { border: 4px solid #007bff; padding: 5mm; }
                                .header { text-align: center; border-bottom: 2px solid #007bff; margin-bottom: 10mm; }
                                .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                                .header h1 { font-size: 18pt; color: #007bff; margin: 0; text-transform: uppercase; }
                                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                                .details p { margin: 5px 0; }
                                .details strong { color: #007bff; }
                                table { width: 100%; border-collapse: collapse; margin: 10mm 0; }
                                th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; }
                                th { background: #007bff; color: white; font-weight: bold; }
                                tr:nth-child(even) { background: #f9f9f9; }
                                .status-completed { color: #2e7d32; font-weight: bold; }
                                .status-pending { color: #f1c40f; }
                                .status-overdue { color: #c62828; font-weight: bold; }
                                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page">
                            <div class="header">
                                ${logoUrl ? `<img src="${logoUrl}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                                <h1><?php echo esc_html(strtoupper($institute_name)); ?></h1>
                                <p class="subtitle">Homework/Assignments</p>
                            </div>
                            <div class="details">
                                <p><strong>Student Name:</strong> ${escapeHtml(studentName)}</p>
                                <p><strong>Student ID:</strong> ${escapeHtml(studentId)}</p>
                                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Child</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.map(row => `
                                        <tr>
                                            <td>${escapeHtml(row.title)}</td>
                                            <td>${escapeHtml(row.subject)}</td>
                                            <td>${escapeHtml(row.class)}</td>
                                            <td>${escapeHtml(row.section)}</td>
                                            <td>${escapeHtml(row.due_date)}</td>
                                            <td class="status-${row.status.toLowerCase()}">${escapeHtml(row.status)}</td>
                                            <td>${escapeHtml(row.child)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            <div class="footer">
                                <p>This is an Online Generated Homework/Assignments issued by ${instituteName}</p>
                                <p>Generated on ${new Date().toISOString().slice(0, 10)}</p>
                                <p>___________________________</p>
                                <p>Registrar / Authorized Signatory</p>
                                <p>Managed by Instituto Educational Center Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                // Optional: Uncomment to auto-close the window after printing
                // printWindow.close();
            }

            childSelect.addEventListener('change', () => updateTable(childSelect.value));
            document.querySelector('.export-pdf')?.addEventListener('click', () => generatePDF(childSelect.value));
            document.querySelector('.export-csv')?.addEventListener('click', () => exportToCSV);
            document.querySelector('.export-excel')?.addEventListener('click', () => exportToExcel);
            document.querySelector('.export-copy')?.addEventListener('click', () => copyToClipboard);
            document.querySelector('.export-print')?.addEventListener('click', () => printHomework(childSelect.value));

            updateTable('all');
        });
    </script>


    <?php
    return ob_get_clean();
}

//library
function render_parent_library($parent_user, $parent_post_id) {
    global $wpdb;

    $educational_center_id = educational_center_parent_id();
    // if (empty($educational_center_id)) {
    //     return '<div class="alert alert-danger">No Educational Center found for this parent account.</div>';
    // }

    $student_ids = get_post_meta($parent_post_id, 'parent_student_ids', true);
    if (empty($student_ids)) {
        return '<div class="alert alert-warning">No children linked to your account. Please contact administration.</div>';
    }

    $student_ids_array = array_filter(explode(',', $student_ids));
    $children = [];

    foreach ($student_ids_array as $student_id) {
        $student = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'student_id', 'value' => trim($student_id), 'compare' => '='],
                ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
            ]
        ]);

        if (!empty($student)) {
            $student_post = $student[0];
            $children[$student_id] = (object)[
                'student_id' => trim($student_id),
                'name' => get_post_meta($student_post->ID, 'student_name', true) ?: get_the_title($student_post->ID),
                'post_id' => $student_post->ID
            ];
        }
    }

    if (empty($children)) {
        return '<div class="alert alert-warning">No valid student records found for your children.</div>';
    }

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';
    $transactions_by_student = [];
    $all_transactions = [];

    foreach ($children as $student_id => $child) {
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, l.title, l.isbn 
             FROM $trans_table t 
             JOIN $library_table l ON t.book_id = l.book_id 
             WHERE t.user_id = %s AND t.user_type = 'Student' AND l.education_center_id = %s",
            $student_id, $educational_center_id
        ));

        foreach ($transactions as $transaction) {
            $transaction->student_id = $student_id;
            $transactions_by_student[$student_id][] = $transaction;
            $all_transactions[] = $transaction;
        }
    }

    $transactions_json = json_encode([
        'all' => $all_transactions,
        'by_student' => $transactions_by_student
    ]);

    $center_data = get_educational_center_data_teachers($educational_center_id);
    $institute_name = $center_data['name'] ?? 'Unknown Institute';
    $institute_logo = $center_data['logo_url'] ?? '';

    ob_start();
    ?>
    <div class="parent-library card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
        <div class="card-header bg-primary text-white" style="display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h3 class="card-title m-0"><i class="fas fa-book-reader me-2"></i>Library Transactions</h3>
            <span><?php echo esc_html(date('l, F j, Y')); ?></span>
        </div>
        <div class="card-body p-4">
            <div class="child-selector mb-4">
                <label for="child_select" class="me-2 fw-bold">Select Child:</label>
                <select id="child_select" class="form-select" style="border-radius: 20px;">
                    <option value="all" selected>All Children</option>
                    <?php foreach ($children as $child): ?>
                        <option value="<?php echo esc_attr($child->student_id); ?>" data-name="<?php echo esc_attr($child->name); ?>">
                            <?php echo esc_html($child->name . ' (' . $child->student_id . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="export-tools mb-4">
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
            </div>
            <div id="library-table-container">
                <table class="data-table table table-hover" id="libraryTable">
                    <thead style="background: #e6f3ff;">
                        <tr>
                            <th>Book ID</th>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                            <th>Child</th>
                        </tr>
                    </thead>
                    <tbody id="library-table-body">
                        <?php if (empty($all_transactions)): ?>
                            <tr><td colspan="9" class="text-muted text-center py-4">No library transactions recorded for your children.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const childSelect = document.getElementById('child_select');
            const tableBody = document.getElementById('library-table-body');
            const transactionsData = <?php echo $transactions_json; ?>;
            const childrenNames = <?php echo json_encode(array_column($children, 'name', 'student_id')); ?>;

            function updateTable(studentId) {
                if (!tableBody) {
                    console.error('Table body not found');
                    return;
                }
                tableBody.innerHTML = '';
                const transactions = studentId === 'all' ? transactionsData.all : (transactionsData.by_student[studentId] || []);
                if (!transactions || transactions.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="9" class="text-muted text-center py-4">No library transactions for this selection.</td></tr>';
                    return;
                }
                transactions.forEach(trans => {
                    const now = new Date();
                    const dueDate = new Date(trans.due_date);
                    const returnDate = trans.return_date ? new Date(trans.return_date) : null;
                    let status = returnDate ? 'Returned' : (dueDate < now ? 'Overdue' : 'Borrowed');
                    const daysOverdue = !returnDate && dueDate < now ? Math.max(0, Math.floor((now - dueDate) / (1000 * 60 * 60 * 24))) : 0;
                    const fine = trans.fine > 0 ? trans.fine : (daysOverdue * 0.50).toFixed(2);

                    const row = document.createElement('tr');
                    row.setAttribute('data-student-id', trans.student_id);
                    row.innerHTML = `
                        <td>${escapeHtml(trans.book_id)}</td>
                        <td>${escapeHtml(trans.isbn || 'N/A')}</td>
                        <td>${escapeHtml(trans.title)}</td>
                        <td>${escapeHtml(trans.issue_date)}</td>
                        <td>${escapeHtml(trans.due_date)}</td>
                        <td>${returnDate ? escapeHtml(trans.return_date) : '<span class="badge bg-warning">Pending</span>'}</td>
                        <td class="status-${status.toLowerCase()}">${escapeHtml(status)}</td>
                        <td>${fine} ${!returnDate && daysOverdue > 0 ? '(Pending)' : ''}</td>
                        <td>${escapeHtml(childrenNames[trans.student_id])}</td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            function escapeHtml(text) {
                const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
                return text.toString().replace(/[&<>"']/g, m => map[m]);
            }

            function getTableData() {
                return Array.from(tableBody.querySelectorAll('tr:not(.text-muted)')).map(row => ({
                    book_id: row.cells[0].textContent,
                    isbn: row.cells[1].textContent,
                    title: row.cells[2].textContent,
                    issue_date: row.cells[3].textContent,
                    due_date: row.cells[4].textContent,
                    return_date: row.cells[5].textContent.includes('Pending') ? 'Pending' : row.cells[5].textContent,
                    status: row.cells[6].textContent,
                    fine: row.cells[7].textContent,
                    child: row.cells[8].textContent
                }));
            }

            function generatePDF(studentId) {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No library data to export.');
                    return;
                }
                const studentName = childSelect.value === 'all' ? 'All Children' : (childSelect.selectedOptions[0]?.dataset.name || 'Unknown');

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'landscape' });
                const pageWidth = doc.internal.pageSize.width;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10;
                const borderColor = [0, 123, 255];

                doc.setDrawColor(...borderColor);
                doc.setLineWidth(1);
                doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

                const instituteLogo = '<?php echo esc_js($institute_logo); ?>';
                if (instituteLogo) {
                    try {
                        doc.addImage(instituteLogo, 'PNG', (pageWidth - 24) / 2, 15, 24, 24);
                    } catch (e) {
                        console.log('Logo loading failed:', e);
                        doc.setFontSize(10);
                        doc.text('No logo available', pageWidth / 2, 20, { align: 'center' });
                    }
                }

                const instituteName = '<?php echo esc_js($institute_name); ?>';
                doc.setFontSize(18);
                doc.setTextColor(...borderColor);
                doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
                doc.setFontSize(12);
                doc.setTextColor(102);
                doc.text('Library Transactions', pageWidth / 2, 55, { align: 'center' });
                doc.setDrawColor(...borderColor);
                doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

                let y = 70;
                const details = [
                    ['Student Name', studentName],
                    ['Student ID', studentId],
                    ['Date', new Date().toLocaleDateString()]
                ];
                details.forEach(([label, value]) => {
                    doc.setFillColor(245, 245, 245);
                    doc.rect(margin + 5, y, 50, 6, 'F');
                    doc.setTextColor(...borderColor);
                    doc.setFont('helvetica', 'bold');
                    doc.text(label, margin + 7, y + 4);
                    doc.setTextColor(51);
                    doc.setFont('helvetica', 'normal');
                    doc.text(value, margin + 60, y + 4);
                    y += 6;
                });

                if (typeof doc.autoTable === 'function') {
                    const headers = ['Book ID', 'ISBN', 'Title', 'Issue Date', 'Due Date', 'Return Date', 'Status', 'Fine', 'Child'];
                    const body = data.map(row => [row.book_id, row.isbn, row.title, row.issue_date, row.due_date, row.return_date, row.status, row.fine, row.child]);

                    doc.autoTable({
                        startY: y + 10,
                        head: [headers],
                        body: body,
                        theme: 'striped',
                        styles: { fontSize: 10, cellPadding: 2, overflow: 'linebreak', halign: 'center', textColor: [51, 51, 51] },
                        headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                        alternateRowStyles: { fillColor: [249, 249, 249] },
                        tableLineColor: [204, 204, 204],
                        tableLineWidth: 0.1
                    });

                    const finalY = doc.lastAutoTable.finalY || y + 10;
                    doc.setFontSize(9);
                    doc.setTextColor(102);
                    doc.text(`This is an Online Generated Library Transactions Report issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
                    doc.text(`Generated on ${new Date().toISOString().slice(0, 10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
                    doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                    doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                    doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });
                } else {
                    console.error('jsPDF autoTable plugin not loaded');
                    alert('PDF generation failed: autoTable plugin missing');
                    return;
                }

                doc.save(`library_transactions_${studentId}_${new Date().toISOString().slice(0,10)}.pdf`);
            }

            function exportToCSV() {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No library data to export.');
                    return;
                }
                const csvContent = [
                    '"Book ID","ISBN","Title","Issue Date","Due Date","Return Date","Status","Fine","Child"',
                    ...data.map(t => `"${t.book_id.replace(/"/g, '""')}","${t.isbn.replace(/"/g, '""')}","${t.title.replace(/"/g, '""')}","${t.issue_date.replace(/"/g, '""')}","${t.due_date.replace(/"/g, '""')}","${t.return_date.replace(/"/g, '""')}","${t.status.replace(/"/g, '""')}","${t.fine.replace(/"/g, '""')}","${t.child.replace(/"/g, '""')}"`)
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const studentId = childSelect.value === 'all' ? 'all' : childSelect.value;
                link.href = URL.createObjectURL(blob);
                link.download = `library_transactions_${studentId}_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel() {
                if (!window.XLSX) return;
                const data = getTableData();
                if (data.length === 0) {
                    alert('No library data to export.');
                    return;
                }
                const ws = XLSX.utils.json_to_sheet(data, { header: ['book_id', 'isbn', 'title', 'issue_date', 'due_date', 'return_date', 'status', 'fine', 'child'] });
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Library Transactions');
                const studentId = childSelect.value === 'all' ? 'all' : childSelect.value;
                XLSX.writeFile(wb, `library_transactions_${studentId}_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function copyToClipboard() {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No library data to copy.');
                    return;
                }
                const text = data.map(t => `${t.title}: Issued ${t.issue_date}, Due ${t.due_date}, ${t.status} - ${t.child}`).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Library transactions copied to clipboard!'));
            }

            function printLibrary(studentId) {
                const data = getTableData();
                if (data.length === 0) {
                    alert('No library data to print.');
                    return;
                }
                const studentName = childSelect.value === 'all' ? 'All Children' : (childSelect.selectedOptions[0]?.dataset.name || 'Unknown');
                const instituteName = '<?php echo esc_js($institute_name); ?>';
                const logoUrl = '<?php echo esc_js($institute_logo); ?>';

                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Library Transactions - ${studentId}</title>
                        <style>
                            @media print {
                                body { font-family: Helvetica, sans-serif; margin: 10mm; }
                                .page { border: 4px solid #007bff; padding: 5mm; }
                                .header { text-align: center; border-bottom: 2px solid #007bff; margin-bottom: 10mm; }
                                .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                                .header h1 { font-size: 18pt; color: #007bff; margin: 0; text-transform: uppercase; }
                                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                                .details p { margin: 5px 0; }
                                .details strong { color: #007bff; }
                                table { width: 100%; border-collapse: collapse; margin: 10mm 0; }
                                th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; }
                                th { background: #007bff; color: white; font-weight: bold; }
                                tr:nth-child(even) { background: #f9f9f9; }
                                .status-returned { color: #2e7d32; font-weight: bold; }
                                .status-borrowed { color: #f1c40f; }
                                .status-overdue { color: #c62828; font-weight: bold; }
                                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page">
                            <div class="header">
                                ${logoUrl ? `<img src="${logoUrl}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                                <h1><?php echo esc_html(strtoupper($institute_name)); ?></h1>
                                <p class="subtitle">Library Transactions</p>
                            </div>
                            <div class="details">
                                <p><strong>Student Name:</strong> ${escapeHtml(studentName)}</p>
                                <p><strong>Student ID:</strong> ${escapeHtml(studentId)}</p>
                                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Book ID</th>
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Fine</th>
                                        <th>Child</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.map(row => `
                                        <tr>
                                            <td>${escapeHtml(row.book_id)}</td>
                                            <td>${escapeHtml(row.isbn)}</td>
                                            <td>${escapeHtml(row.title)}</td>
                                            <td>${escapeHtml(row.issue_date)}</td>
                                            <td>${escapeHtml(row.due_date)}</td>
                                            <td>${row.return_date === 'Pending' ? '<span class="badge bg-warning">Pending</span>' : escapeHtml(row.return_date)}</td>
                                            <td class="status-${row.status.toLowerCase()}">${escapeHtml(row.status)}</td>
                                            <td>${escapeHtml(row.fine)}</td>
                                            <td>${escapeHtml(row.child)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            <div class="footer">
                                <p>This is an Online Generated Library Transactions Report issued by ${instituteName}</p>
                                <p>Generated on ${new Date().toISOString().slice(0, 10)}</p>
                                <p>___________________________</p>
                                <p>Registrar / Authorized Signatory</p>
                                <p>Managed by Instituto Educational Center Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            childSelect.addEventListener('change', () => updateTable(childSelect.value));
            document.querySelector('.export-pdf')?.addEventListener('click', () => generatePDF(childSelect.value));
            document.querySelector('.export-csv')?.addEventListener('click', exportToCSV);
            document.querySelector('.export-excel')?.addEventListener('click', exportToExcel);
            document.querySelector('.export-copy')?.addEventListener('click', copyToClipboard);
            document.querySelector('.export-print')?.addEventListener('click', () => printLibrary(childSelect.value));

            updateTable('all');
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-dashboard', plugin_dir_url(__FILE__) . 'style.css', [], '1.0.0');
});
add_action('wp_enqueue_scripts', function() {
    // Enqueue jsPDF and its autotable plugin
    wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true);
    wp_enqueue_script('jspdf-autotable', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js', ['jspdf'], '3.8.2', true);
    wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js', [], '2.29.4', true);
    // Enqueue custom pdf-helper.js
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    // wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');

    wp_enqueue_script('pdf-helper', plugin_dir_url(__FILE__) . 'js/pdf-helper.js', ['jspdf', 'jspdf-autotable'], '1.0', true);
});
