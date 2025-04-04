<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register su_p Dashboard Shortcode
function su_p_dashboard_shortcode() {
    if (!is_user_logged_in() || !current_user_can('su_p')) {
        return '<p>You must be logged to view this dashboard.</p>';
    }

    ob_start();
    $current_user = wp_get_current_user();
    ?>
    <div class="su_p-wrapper" style="display: flex; min-height: 100vh; background: #f5f7fa;">
        <?php
        echo render_su_p_header($current_user);
        $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
     
        // $active_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
        // $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
       
        $active_section = $section;
        $active_action = $action; // Pass action to sidebar
     
        
        include(plugin_dir_path(__FILE__) . 'su-p-sidebar.php');
        ?>
        <div class="su_p-content-wrapper" style="flex: 1; padding: 20px; width: calc(100% - 250px); margin-top:60px;">
            <?php
            switch ($active_section) {
                case 'overview':
                    echo render_su_p_overview();
                    break;
                case 'centers':
                    echo render_su_p_centers();
                    break;
                case 'teacher':
                    echo render_su_p_teachers();
                    break;
                case 'students':
                    echo render_su_p_students();
                    break;
                case 'staff':
                    echo render_su_p_staff();
                    break;
                case 'roles':
                    echo render_su_p_roles();
                    break;
                case 'classes':
                    if ($action === 'add-class') {
                        echo render_su_p_add_class_form();
                    } elseif ($action === 'edit-class') {
                        echo render_su_p_edit_class_form();
                    } 
                    elseif ($action === 'delete-class') {
                        echo render_su_p_delete_class_form();
                    }
                    elseif ($action === 'student-count') {
                        echo render_su_p_student_count();
                    }
                     else {
                       echo render_su_p_class_management();
                    }
                    break;
                case 'exams':
                    if ($action === 'add-exams') {
                        echo render_su_p_add_exam();
                    } elseif ($action === 'edit-exams') {
                        echo render_su_p_edit_exam();
                    } 
                    elseif ($action === 'delete-exams') {
                        echo render_su_p_delete_exam();
                    }
                     else {
                       echo render_su_p_exam_management();
                    }
                    break;
                case 'results':
                       echo render_su_p_exam_results();
                    break;

                // case 'attendance':
                //     echo render_su_p_student_attendance();
                //     break;
                    case 'attendance':
                        if ($action === 'teachers-attendance') {
                            echo render_su_p_teacher_attendance();
                        } elseif ($action === 'staff-attendance') {
                            echo 
                            render_su_p_staff_attendance();
                        } 
                        // elseif ($action === 'bulk-import') {
                        //     echo bulk_import_attendance_shortcode($user_id);
                        // }
                         else {
                            echo render_su_p_student_attendance();
                        }
                        break;
                    case 'library':
                        if ($action === 'add-library') {
                            echo render_su_p_teacher_attendance();
                        } elseif ($action === 'staff-attendance') {
                            echo 
                            render_su_p_staff_attendance();
                        } 
                        // elseif ($action === 'bulk-import') {
                        //     echo bulk_import_attendance_shortcode($user_id);
                        // }
                         else {
                            echo su_p_library_management_dashboard_shortcode();
                        }
                        break;
                case 'timetable':
                    echo render_su_p_timetable();
                    break;
                case 'reports':
                    echo su_p_reports_dashboard_shortcode();
                    break;
                case 'notifications':
                    echo render_su_p_notifications();
                    break;
                case 'settings':
                    echo render_su_p_settings();
                    break;
                case 'audit':
                    echo render_su_p_audit_logs();
                    break;
                case 'support':
                    echo render_su_p_support();
                    break;
                default:
                    echo render_su_p_overview();
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('su_p_dashboard', 'su_p_dashboard_shortcode');

// su_p Header
function render_su_p_header($su_p_user) {
    global $wpdb;
    $user_name = $su_p_user->display_name;
    $user_email = $su_p_user->user_email;
    $avatar_url = get_user_meta($su_p_user->ID, 'su_p_profile_photo', true) ? wp_get_attachment_url(get_user_meta($su_p_user->ID, 'su_p_profile_photo', true)) : 'https://via.placeholder.com/150';

    $dashboard_link = esc_url(home_url('/su_p-dashboard'));
    $notifications_link = esc_url(home_url('/su_p-dashboard?section=notifications'));
    $settings_link = esc_url(home_url('/su_p-dashboard?section=settings'));
    $logout_link = wp_logout_url(home_url()); // Assuming get_secure_logout_url_by_role() isn't defined yet
    $communication_link = esc_url(home_url('/su_p-dashboard?section=communication'));

    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    $notifications_table = $wpdb->prefix . 'aspire_announcements';
    $notifications_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $notifications_table WHERE receiver_id = 'su_p' AND timestamp > %s",
        $seven_days_ago
    ));

    $unread_notifications = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp FROM $notifications_table WHERE receiver_id = 'su_p' AND timestamp > %s ORDER BY timestamp DESC LIMIT 5",
        $seven_days_ago
    ));

    // Messages count and data (assuming su_p receives system-wide messages)
    $messages_table = $wpdb->prefix . 'aspire_messages';
    $messages_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $messages_table 
         WHERE (receiver_id = 'su_p' OR receiver_id = 'all') 
         AND status = 'sent' 
         AND timestamp > %s",
        $seven_days_ago
    ));

    $unread_messages = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $messages_table 
         WHERE (receiver_id = 'su_p' OR receiver_id = 'all') 
         AND status = 'sent' 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $seven_days_ago
    ));

    ob_start();
    ?>
    <header class="su_p-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?php echo $dashboard_link; ?>" class="header-logo">
                    <img decoding="async" class="logo-image" src="<?php echo plugin_dir_url(__FILE__) . 'logo-su_p.png'; ?>" alt="su_p Logo">
                    <span class="logo-text">su_p Dashboard</span>
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
                    <a href="<?php echo esc_url(home_url('/su_p-dashboard?section=centers')); ?>" class="nav-item">Centers</a>
                    <a href="<?php echo esc_url(home_url('/su_p-dashboard?section=users')); ?>" class="nav-item">Users</a>
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
                                <li><a href="<?php echo esc_url(home_url('/su_p-dashboard?section=reports')); ?>" class="dropdown-link">Reports</a></li>
                                <li><a href="<?php echo esc_url(home_url('/su_p-dashboard?section=audit')); ?>" class="dropdown-link">Audit Logs</a></li>
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
                            action: 'search_su_p_sections',
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
                    action: 'aspire_su_p_mark_messages_read',
                    nonce: '<?php echo wp_create_nonce('aspire_su_p_header_nonce'); ?>'
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

// AJAX Handler for Search
add_action('wp_ajax_search_su_p_sections', 'search_su_p_sections_callback');
function search_su_p_sections_callback() {
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $sections = [
        ['title' => 'Dashboard', 'url' => esc_url(home_url('/su_p-dashboard')), 'keywords' => ['dashboard', 'home']],
        ['title' => 'Centers', 'url' => esc_url(home_url('/su_p-dashboard?section=centers')), 'keywords' => ['centers', 'institutes']],
        ['title' => 'Users', 'url' => esc_url(home_url('/su_p-dashboard?section=users')), 'keywords' => ['users', 'accounts']],
        ['title' => 'Messages', 'url' => esc_url(home_url('/su_p-dashboard?section=communication')), 'keywords' => ['messages', 'communication']],
        ['title' => 'Notifications', 'url' => esc_url(home_url('/su_p-dashboard?section=notifications')), 'keywords' => ['notifications', 'alerts']],
        ['title' => 'Reports', 'url' => esc_url(home_url('/su_p-dashboard?section=reports')), 'keywords' => ['reports', 'analytics']],
        ['title' => 'Settings', 'url' => esc_url(home_url('/su_p-dashboard?section=settings')), 'keywords' => ['settings', 'configuration']]
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

// AJAX Handler for Marking Messages Read
add_action('wp_ajax_aspire_su_p_mark_messages_read', 'aspire_su_p_mark_messages_read');
function aspire_su_p_mark_messages_read() {
    check_ajax_referer('aspire_su_p_header_nonce', 'nonce');

    global $wpdb;
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    $messages_table = $wpdb->prefix . 'aspire_messages';
    $wpdb->query($wpdb->prepare(
        "UPDATE $messages_table 
         SET status = 'read' 
         WHERE (receiver_id = 'su_p' OR receiver_id = 'all') 
         AND status = 'sent' 
         AND timestamp > %s",
        $seven_days_ago
    ));

    wp_send_json_success();
    wp_die();
}

// Overview Section
function render_su_p_overview() {
    global $wpdb;
    $centers_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'educational-center' AND post_status = 'publish'");
    $teachers_count = count(get_users(['role' => 'teacher']));
    $students_count = count(get_users(['role' => 'student']));
    $parents_count = count(get_users(['role' => 'parent']));
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">System Overview</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><h5>Total Centers: <?php echo $centers_count; ?></h5></div>
                <div class="col-md-3"><h5>Total Teachers: <?php echo $teachers_count; ?></h5></div>
                <div class="col-md-3"><h5>Total Students: <?php echo $students_count; ?></h5></div>
                <div class="col-md-3"><h5>Total Parents: <?php echo $parents_count; ?></h5></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Educational Centers Management
function render_su_p_centers() {
    global $wpdb;
    ob_start();
    ?>
    <div class="edu-centers-container" style="margin-top: 80px;">
        <!-- Loader SVG -->
        <div id="edu-loader" class="edu-loader" style="display: none;">
            <div class="edu-loader-container">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
            </div>
        </div>
        <h2 class="edu-centers-title">Educational Centers Management</h2>
        <div class="edu-centers-actions">
            <button class="edu-button edu-button-primary" id="add-center-btn">Add New Center</button>
            <input type="text" id="center-search" class="edu-search-input" placeholder="Search Centers..." style="margin-left: 20px; padding: 8px; width: 300px;">
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="centers-per-page">Show:</label>
            <select id="centers-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <div class="export-tools" id="export-tools" style="margin-bottom: 10px;"></div>
            <table class="edu-table" id="centers-table">
                <thead>
                    <tr>
                        <th>Educational Center ID</th>
                        <th>Name</th>
                        <th>Admin ID</th>
                        <th>Logo</th>
                        <th>Location</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="centers-table-body">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Center Modal -->
    <div class="edu-modal" id="add-center-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-center-modal">×</span>
            <h3>Add New Center</h3>
            <form id="add-center-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-name">Center Name</label>
                    <input type="text" class="edu-form-input" id="center-name" name="center_name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-admin-id">Admin ID</label>
                    <input type="text" class="edu-form-input" id="center-admin-id" name="admin_id" readonly placeholder="Assigned when adding new admin">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-edu-id">Educational Center ID</label>
                    <input type="text" class="edu-form-input" id="center-edu-id" name="educational_center_id" readonly placeholder="Generated on save">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-logo">Institute Logo</label>
                    <input type="file" class="edu-form-input" id="center-logo" name="institute_logo" accept="image/*">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-location">Location</label>
                    <input type="text" class="edu-form-input" id="center-location" name="location">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-mobile">Mobile Number</label>
                    <input type="number" class="edu-form-input" id="center-mobile" name="mobile_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-email">Email ID</label>
                    <input type="email" class="edu-form-input" id="center-email" name="email_id" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_center_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="save-center">Save Center</button>
            </form>
            <div class="edu-form-message" id="add-center-message"></div>
        </div>
    </div>

    <!-- Edit Center Modal -->
    <div class="edu-modal" id="edit-center-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-center-modal">×</span>
            <h3>Edit Center</h3>
            <form id="edit-center-form" class="edu-form">
                <input type="hidden" id="edit-center-id" name="center_id">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-name">Center Name</label>
                    <input type="text" class="edu-form-input" id="edit-center-name" name="center_name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-admin-id">Admin ID</label>
                    <input type="text" class="edu-form-input" id="edit-center-admin-id" name="admin_id" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-edu-id">Educational Center ID</label>
                    <input type="text" class="edu-form-input" id="edit-center-edu-id" name="educational_center_id" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-logo">Institute Logo</label>
                    <input type="file" class="edu-form-input" id="edit-center-logo" name="institute_logo" accept="image/*">
                    <img id="edit-center-logo-preview" src="" alt="Current Logo" class="edu-logo-preview" style="display: none;">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-location">Location</label>
                    <input type="text" class="edu-form-input" id="edit-center-location" name="location">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-mobile">Mobile Number</label>
                    <input type="number" class="edu-form-input" id="edit-center-mobile" name="mobile_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center-email">Email ID</label>
                    <input type="email" class="edu-form-input" id="edit-center-email" name="email_id" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_center_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="update-center">Update Center</button>
            </form>
            <div class="edu-form-message" id="edit-center-message"></div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="edu-modal" id="change-password-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="change-password-modal">×</span>
            <h3>Change Institute Admin Password</h3>
            <form id="change-password-form" class="edu-form">
                <input type="hidden" id="change-center-id" name="center_id">
                <p>Send a password reset email to the institute admin.</p>
                <button type="button" class="edu-button edu-button-primary" id="send-reset-link">Send Reset Link</button>
            </form>
            <div class="edu-form-message" id="change-password-message"></div>
        </div>
    </div>

    <!-- Add New Admin Modal -->
    <div class="edu-modal" id="add-new-admin-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-new-admin-modal">×</span>
            <h3>Add New Institute Admin</h3>
            <form id="add-new-admin-form" class="edu-form">
                <input type="hidden" id="new-admin-center-id" name="center_id">
                <input type="hidden" id="new-admin-educational-center-id" name="educational_center_id">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="new-admin-id">Admin ID</label>
                    <input type="text" class="edu-form-input" id="new-admin-id" name="admin_id" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="new-admin-name">Admin Name</label>
                    <input type="text" class="edu-form-input" id="new-admin-name" name="admin_name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="new-admin-email">Email ID</label>
                    <input type="email" class="edu-form-input" id="new-admin-email" name="email_id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="new-admin-password">Password</label>
                    <input type="password" class="edu-form-input" id="new-admin-password" name="password" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="new-admin-mobile">Mobile Number</label>
                    <input type="number" class="edu-form-input" id="new-admin-mobile" name="mobile_number" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_center_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="save-new-admin">Save New Admin</button>
            </form>
            <div class="edu-form-message" id="add-new-admin-message"></div>
        </div>
    </div>

    <!-- Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';

        function showLoader() {
            $('#edu-loader').show();
        }
        function hideLoader() {
            $('#edu-loader').hide();
        }

        function openModal(modalId) {
            showLoader();
            $(modalId).css('display', 'block');
            setTimeout(hideLoader, 100);
        }
        function closeModal(modalId) {
            $(modalId).css('display', 'none');
            $(modalId + ' .edu-form-message').removeClass('edu-success edu-error').text('');
            hideLoader();
        }

        function getTableData() {
            const table = $('#centers-table')[0];
            if (!table) return [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim()).slice(0, -1); // Exclude Actions
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                const cells = Array.from(row.querySelectorAll('td')).slice(0, -1); // Exclude Actions
                return cells.map(td => td.textContent.trim());
            });
            return [headers, ...rows];
        }

        function exportToCSV() {
            const data = getTableData();
            const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `centers_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
        }

        function exportToExcel() {
            if (!window.XLSX) {
                console.error('XLSX library not loaded');
                return;
            }
            const data = getTableData();
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Centers');
            XLSX.writeFile(wb, `centers_${new Date().toISOString().slice(0,10)}.xlsx`);
        }
        function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
    const data = getTableData();
    const instituteName = 'Istituto';
    const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo instituto.jpg'); ?>'; // Your logo path
    const pageWidth = doc.internal.pageSize.width;
    const pageHeight = doc.internal.pageSize.height;
    const margin = 10;
    const borderColor = [70, 131, 180]; // #4683b4

    // Page border
    doc.setDrawColor(...borderColor);
    doc.setLineWidth(1);
    doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

    // Header
    if (instituteLogo) {
        try {
            doc.addImage(instituteLogo, 'JPEG', (pageWidth - 24) / 2, 15, 24, 24); // Changed to 'JPEG' for .jpg
        } catch (e) {
            console.log('Logo loading failed:', e);
            doc.setFontSize(10);
            doc.text('No logo available', (pageWidth - 20) / 2, 20, { align: 'center' });
        }
    }
    doc.setFontSize(18);
    doc.setTextColor(...borderColor);
    doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
    doc.setFontSize(12);
    doc.setTextColor(102); // #666
    doc.text('Educational Centers List', pageWidth / 2, 55, { align: 'center' });
    doc.setDrawColor(...borderColor);
    doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

    // Details
    const details = [
        ['Date', new Date().toLocaleDateString()],
        ['Total Centers', String(data.length - 1)]
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
        doc.text(String(value), margin + 60, y + 4);
        y += 6;
    });

    // Table
    if (typeof doc.autoTable === 'function' && data.length > 1) {
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
                textColor: [51, 51, 51]
            },
            headStyles: {
                fillColor: borderColor,
                textColor: [255, 255, 255],
                fontStyle: 'bold'
            },
            alternateRowStyles: { fillColor: [249, 249, 249] }
        });

        // Footer
        const finalY = doc.lastAutoTable.finalY || y + 10;
        doc.setFontSize(9);
        doc.setTextColor(102); // #666
        doc.text(`This is an Online Generated Centers List issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
        doc.text(`Generated on ${new Date().toISOString().slice(0, 10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
        doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
        doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
        doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

        doc.save(`centers_${new Date().toISOString().slice(0,10)}.pdf`);
    } else {
        console.error('jsPDF autoTable plugin not loaded or no data');
        alert('PDF generation failed');
    }
    }
    function printCenters() {
    const printWindow = window.open('', '_blank');
    const data = getTableData();
    const instituteName = 'Istituto';
    const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . '/../logo instituto.jpg'); ?>'; // Your logo path

    printWindow.document.write(`
        <html>
        <head>
            <title>Educational Centers List</title>
            <style>
                @media print {
                    body { 
                        font-family: Helvetica, sans-serif; 
                        margin: 10mm; 
                        width: 190mm;
                    }
                    .page { 
                        border: 4px solid #4683b4; 
                        padding: 5mm; 
                        box-sizing: border-box; 
                        width: 100%; 
                        max-width: 190mm;
                    }
                    .header { 
                        text-align: center; 
                        border-bottom: 2px solid #4683b4; 
                        margin-bottom: 10mm; 
                    }
                    .header img { 
                        width: 60px; 
                        height: 60px; 
                        margin-bottom: 5mm; 
                    }
                    .header h1 { 
                        font-size: 18pt; 
                        color: #4683b4; 
                        margin: 0; 
                        text-transform: uppercase; 
                    }
                    .header .subtitle { 
                        font-size: 12pt; 
                        color: #666; 
                        margin: 0; 
                    }
                    table { 
                        width: 100%; 
                        max-width: 100%; 
                        border-collapse: collapse; 
                        margin: 10mm 0; 
                        table-layout: fixed;
                    }
                    th, td { 
                        border: 1px solid #e5e5e5; 
                        padding: 8px; 
                        text-align: center; 
                        word-wrap: break-word; 
                        font-size: 10pt;
                    }
                    th { 
                        background: #4683b4; 
                        color: white; 
                        font-weight: bold; 
                    }
                    tr:nth-child(even) { 
                        background: #f9f9f9; 
                    }
                    .footer { 
                        text-align: center; 
                        font-size: 9pt; 
                        color: #666; 
                        margin-top: 10mm; 
                    }
                    @page { 
                        size: A4; 
                        margin: 10mm;
                    }
                }
            </style>
        </head>
        <body>
            <div class="page">
                <div class="header">
                    ${instituteLogo ? `<img src="${instituteLogo}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;"></p>` : '<p></p>'}
                    <h1>${instituteName.toUpperCase()}</h1>
                    <p class="subtitle">Educational Centers List</p>
                </div>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Total Centers:</strong> ${data.length - 1}</p>
                <table>
                    <thead>
                        <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                    </thead>
                    <tbody>
                        ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                    </tbody>
                </table>
                <div class="footer">
                    <p>This is an Online Generated Centers List issued by ${instituteName}</p>
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
        function copyToClipboard() {
            const data = getTableData();
            const text = data.map(row => row.join('\t')).join('\n');
            navigator.clipboard.writeText(text).then(() => alert('Centers copied to clipboard!'));
        }

        function printCenters() {
    const printWindow = window.open('', '_blank');
    const data = getTableData();
    const instituteName = 'Instituto';
    const instituteLogo = '<?php echo esc_js(get_option('custom_logo_url', '')); ?>';

    printWindow.document.write(`
        <html>
        <head>
            <title>Educational Centers List</title>
            <style>
                @media print {
                    body { 
                        font-family: Helvetica, sans-serif; 
                        margin: 10mm; 
                        width: 190mm; /* Adjusted to fit A4 with margins */
                    }
                    .page { 
                        border: 4px solid #4683b4; 
                        padding: 5mm; 
                        box-sizing: border-box; 
                        width: 100%; 
                        max-width: 190mm; /* Matches body width */
                    }
                    .header { 
                        text-align: center; 
                        border-bottom: 2px solid #4683b4; 
                        margin-bottom: 10mm; 
                    }
                    .header img { 
                        width: 60px; 
                        height: 60px; 
                        margin-bottom: 5mm; 
                    }
                    .header h1 { 
                        font-size: 18pt; 
                        color: #4683b4; 
                        margin: 0; 
                        text-transform: uppercase; 
                    }
                    .header .subtitle { 
                        font-size: 12pt; 
                        color: #666; 
                        margin: 0; 
                    }
                    table { 
                        width: 100%; 
                        max-width: 100%; /* Prevents overflow */
                        border-collapse: collapse; 
                        margin: 10mm 0; 
                        table-layout: fixed; /* Forces column width control */
                    }
                    th, td { 
                        border: 1px solid #e5e5e5; 
                        padding: 8px; 
                        text-align: center; 
                        word-wrap: break-word; /* Wraps long content */
                        font-size: 10pt; /* Smaller font to fit more */
                    }
                    th { 
                        background: #4683b4; 
                        color: white; 
                        font-weight: bold; 
                    }
                    tr:nth-child(even) { 
                        background: #f9f9f9; 
                    }
                    .footer { 
                        text-align: center; 
                        font-size: 9pt; 
                        color: #666; 
                        margin-top: 10mm; 
                    }
                    @page { 
                        size: A4; 
                        margin: 10mm; /* Matches body margin */
                    }
                }
            </style>
        </head>
        <body>
            <div class="page">
                <div class="header">
                    ${instituteLogo ? `<img src="${instituteLogo}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                    <h1>${instituteName.toUpperCase()}</h1>
                    <p class="subtitle">Educational Centers List</p>
                </div>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Total Centers:</strong> ${data.length - 1}</p>
                <table>
                    <thead>
                        <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                    </thead>
                    <tbody>
                        ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                    </tbody>
                </table>
                <div class="footer">
                    <p>This is an Online Generated Centers List issued by ${instituteName}</p>
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
        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
            `);

            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
            tools.find('.export-copy').on('click', copyToClipboard);
            tools.find('.export-print').on('click', printCenters);
        }

        function loadCenters(page, limit, query) {
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_fetch_centers',
                    page: page,
                    per_page: limit,
                    search: query,
                    nonce: '<?php echo wp_create_nonce('su_p_center_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const centers = response.data.centers;
                        const total = response.data.total;
                        let html = '';
                        centers.forEach(center => {
                            html += `
                                <tr data-center-id="${center.ID}">
                                    <td>${center.educational_center_id || 'N/A'}</td>
                                    <td>${center.educational_center_name || center.post_title}</td>
                                    <td>${center.admin_id || 'Unassigned'}</td>
                                    <td><img src="${center.institute_logo || 'https://via.placeholder.com/50'}" alt="Logo" class="edu-logo"></td>
                                    <td>${center.location || 'N/A'}</td>
                                    <td>${center.mobile_number || 'N/A'}</td>
                                    <td>${center.email_id || 'N/A'}</td>
                                    <td>
                                        <button class="edu-button edu-button-edit edit-center" data-center-id="${center.ID}">Edit</button>
                                        <button class="edu-button edu-button-delete delete-center" data-center-id="${center.ID}">Delete</button>
                                        <button class="edu-button edu-button-password change-password" data-center-id="${center.ID}" data-admin-id="${center.admin_id || ''}">Change Password</button>
                                        <button class="edu-button edu-button-add-admin add-new-admin" data-center-id="${center.ID}">Add New Admin</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#centers-table-body').html(html);
                        setupExportButtons();

                        const totalPages = Math.ceil(total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages}`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                    } else {
                        $('#centers-table-body').html('<tr><td colspan="8">No centers found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#centers-table-body').html('<tr><td colspan="8">Error loading centers: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        // Initial load
        loadCenters(currentPage, perPage, searchQuery);

        // Search
        $('#center-search').on('input', function() {
            searchQuery = $(this).val();
            currentPage = 1;
            loadCenters(currentPage, perPage, searchQuery);
        });

        // Pagination
        $('#centers-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadCenters(currentPage, perPage, searchQuery);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadCenters(currentPage, perPage, searchQuery);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadCenters(currentPage, perPage, searchQuery);
        });

        // Add Center
        $('#add-center-btn').on('click', function() { openModal('#add-center-modal'); });
        $('#save-center').on('click', function() {
            const formData = new FormData($('#add-center-form')[0]);
            formData.append('action', 'su_p_add_center');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#add-center-message').addClass('edu-success').text('Center added successfully!');
                        $('#center-edu-id').val(response.data.educational_center_id);
                        setTimeout(() => {
                            closeModal('#add-center-modal');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                    } else {
                        $('#add-center-message').addClass('edu-error').text('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-center-message').addClass('edu-error').text('Error adding center: ' + error);
                }
            });
        });

        // Edit Center
        $(document).on('click', '.edit-center', function() {
            const centerId = $(this).data('center-id');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_get_center',
                    center_id: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_center_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const center = response.data;
                        $('#edit-center-id').val(center.ID);
                        $('#edit-center-name').val(center.educational_center_name);
                        $('#edit-center-admin-id').val(center.admin_id);
                        $('#edit-center-edu-id').val(center.educational_center_id);
                        $('#edit-center-location').val(center.location);
                        $('#edit-center-mobile').val(center.mobile_number);
                        $('#edit-center-email').val(center.email_id);
                        if (center.institute_logo) {
                            $('#edit-center-logo-preview').attr('src', center.institute_logo).show();
                        } else {
                            $('#edit-center-logo-preview').hide();
                        }
                        openModal('#edit-center-modal');
                    } else {
                        alert('Error fetching center: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error fetching center: ' + error);
                }
            });
        });

        $('#update-center').on('click', function() {
            const formData = new FormData($('#edit-center-form')[0]);
            formData.append('action', 'su_p_update_center');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#edit-center-message').addClass('edu-success').text('Center updated successfully!');
                        setTimeout(() => {
                            closeModal('#edit-center-modal');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                    } else {
                        $('#edit-center-message').addClass('edu-error').text('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-center-message').addClass('edu-error').text('Error updating center: ' + error);
                }
            });
        });

        // Delete Center
        $(document).on('click', '.delete-center', function() {
            if (!confirm('Are you sure you want to delete this center?')) return;
            const centerId = $(this).data('center-id');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_delete_center',
                    center_id: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_center_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        loadCenters(currentPage, perPage, searchQuery);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error deleting center: ' + error);
                }
            });
        });

        // Change Password
        $(document).on('click', '.change-password', function() {
            const centerId = $(this).data('center-id');
            $('#change-center-id').val(centerId);
            openModal('#change-password-modal');
        });

        $('#send-reset-link').on('click', function() {
            const centerId = $('#change-center-id').val();
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_reset_admin_password',
                    center_id: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_center_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#change-password-message').addClass('edu-success').text('Reset link sent successfully!');
                        setTimeout(() => closeModal('#change-password-modal'), 1000);
                    } else {
                        $('#change-password-message').addClass('edu-error').text('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#change-password-message').addClass('edu-error').text('Error sending reset link: ' + error);
                }
            });
        });

        // Add New Admin
        $(document).on('click', '.add-new-admin', function() {
            const centerId = $(this).data('center-id');
            $('#new-admin-center-id').val(centerId);
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_get_center',
                    center_id: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_center_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#new-admin-educational-center-id').val(response.data.educational_center_id);
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            method: 'POST',
                            data: {
                                action: 'su_p_generate_admin_id',
                                center_id: centerId,
                                nonce: '<?php echo wp_create_nonce('su_p_center_nonce'); ?>'
                            },
                            success: function(adminResponse) {
                                hideLoader();
                                if (adminResponse.success) {
                                    $('#new-admin-id').val(adminResponse.data.admin_id);
                                    openModal('#add-new-admin-modal');
                                } else {
                                    $('#add-new-admin-message').addClass('edu-error').text('Error generating admin ID: ' + adminResponse.data.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                hideLoader();
                                $('#add-new-admin-message').addClass('edu-error').text('Error: ' + error);
                            }
                        });
                    } else {
                        hideLoader();
                        $('#add-new-admin-message').addClass('edu-error').text('Error fetching center: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-new-admin-message').addClass('edu-error').text('Error: ' + error);
                }
            });
        });

        $('#save-new-admin').on('click', function() {
            const formData = new FormData($('#add-new-admin-form')[0]);
            formData.append('action', 'su_p_add_new_admin');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#add-new-admin-message').addClass('edu-success').text('New admin added successfully!');
                        $('#center-admin-id').val(response.data.admin_id);
                        $('#edit-center-admin-id').val(response.data.admin_id);
                        setTimeout(() => {
                            closeModal('#add-new-admin-modal');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                    } else {
                        $('#add-new-admin-message').addClass('edu-error').text('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-new-admin-message').addClass('edu-error').text('Error adding new admin: ' + error);
                }
            });
        });
    });
    </script>

    
    <?php
    return ob_get_clean();
}

// AJAX Handlers
add_action('wp_ajax_su_p_fetch_centers', 'su_p_fetch_centers');
function su_p_fetch_centers() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    global $wpdb;
    $page = intval($_POST['page']) ?: 1;
    $per_page = intval($_POST['per_page']) ?: 10;
    $search = sanitize_text_field($_POST['search']);
    $offset = ($page - 1) * $per_page;

    $args = [
        'post_type' => 'educational-center',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'offset' => $offset,
    ];

    if (!empty($search)) {
        $args['meta_query'] = [
            'relation' => 'OR',
            [
                'key' => 'educational_center_name',
                'value' => $search,
                'compare' => 'LIKE'
            ],
            [
                'key' => 'admin_id',
                'value' => $search,
                'compare' => 'LIKE'
            ],
            [
                'key' => 'location',
                'value' => $search,
                'compare' => 'LIKE'
            ],
            [
                'key' => 'mobile_number',
                'value' => $search,
                'compare' => 'LIKE'
            ],
            [
                'key' => 'email_id',
                'value' => $search,
                'compare' => 'LIKE'
            ]
        ];
    }

    $query = new WP_Query($args);
    $centers = [];
    foreach ($query->posts as $center) {
        $centers[] = [
            'ID' => $center->ID, // Still needed for actions
            'post_title' => $center->post_title,
            'educational_center_id' => get_post_meta($center->ID, 'educational_center_id', true),
            'educational_center_name' => get_post_meta($center->ID, 'educational_center_name', true),
            'admin_id' => get_post_meta($center->ID, 'admin_id', true),
            'institute_logo' => wp_get_attachment_url(get_post_meta($center->ID, 'institute_logo', true)),
            'location' => get_post_meta($center->ID, 'location', true),
            'mobile_number' => get_post_meta($center->ID, 'mobile_number', true),
            'email_id' => get_post_meta($center->ID, 'email_id', true)
        ];
    }

    $total = $query->found_posts;
    wp_send_json_success(['centers' => $centers, 'total' => $total]);
    wp_die();
}

add_action('wp_ajax_su_p_add_center', 'su_p_add_center');
function su_p_add_center() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_name = sanitize_text_field($_POST['center_name']);
    $location = sanitize_text_field($_POST['location']);
    $mobile = sanitize_text_field($_POST['mobile_number']);
    $email = sanitize_email($_POST['email_id']);

    $center_id = wp_insert_post([
        'post_title' => $center_name,
        'post_type' => 'educational-center',
        'post_status' => 'publish'
    ]);

    if (is_wp_error($center_id)) {
        wp_send_json_error(['message' => $center_id->get_error_message()]);
        wp_die();
    }

    $edu_center_id = get_unique_id_for_role('institute_admins', $center_id);
    update_post_meta($center_id, 'educational_center_name', $center_name);
    update_post_meta($center_id, 'educational_center_id', $edu_center_id);
    if ($location) update_post_meta($center_id, 'location', $location);
    if ($mobile) update_post_meta($center_id, 'mobile_number', $mobile);
    if ($email) update_post_meta($center_id, 'email_id', $email);

    if (!empty($_FILES['institute_logo']['name'])) {
        $upload = wp_handle_upload($_FILES['institute_logo'], ['test_form' => false]);
        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
            wp_die();
        }
        $attachment_id = wp_insert_attachment([
            'guid' => $upload['url'],
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name($_FILES['institute_logo']['name']),
            'post_content' => '',
            'post_status' => 'inherit'
        ], $upload['file'], $center_id);

        if (!is_wp_error($attachment_id)) {
            update_post_meta($center_id, 'institute_logo', $attachment_id);
        }
    }

    wp_send_json_success(['educational_center_id' => $edu_center_id]);
    wp_die();
}

add_action('wp_ajax_su_p_get_center', 'su_p_get_center');
function su_p_get_center() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_id = intval($_POST['center_id']);
    $center = get_post($center_id);
    if (!$center || $center->post_type !== 'educational-center') {
        wp_send_json_error(['message' => 'Center not found']);
        wp_die();
    }

    $data = [
        'ID' => $center->ID,
        'educational_center_name' => get_post_meta($center->ID, 'educational_center_name', true),
        'admin_id' => get_post_meta($center->ID, 'admin_id', true),
        'educational_center_id' => get_post_meta($center->ID, 'educational_center_id', true),
        'institute_logo' => wp_get_attachment_url(get_post_meta($center->ID, 'institute_logo', true)),
        'location' => get_post_meta($center->ID, 'location', true),
        'mobile_number' => get_post_meta($center->ID, 'mobile_number', true),
        'email_id' => get_post_meta($center->ID, 'email_id', true)
    ];

    wp_send_json_success($data);
    wp_die();
}

add_action('wp_ajax_su_p_update_center', 'su_p_update_center');
function su_p_update_center() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_id = intval($_POST['center_id']);
    $center_name = sanitize_text_field($_POST['center_name']);
    $location = sanitize_text_field($_POST['location']);
    $mobile = sanitize_text_field($_POST['mobile_number']);
    $email = sanitize_email($_POST['email_id']);

    $updated = wp_update_post([
        'ID' => $center_id,
        'post_title' => $center_name
    ]);

    if (is_wp_error($updated)) {
        wp_send_json_error(['message' => $updated->get_error_message()]);
        wp_die();
    }

    update_post_meta($center_id, 'educational_center_name', $center_name);
    update_post_meta($center_id, 'location', $location);
    update_post_meta($center_id, 'mobile_number', $mobile);
    update_post_meta($center_id, 'email_id', $email);

    if (!empty($_FILES['institute_logo']['name'])) {
        $upload = wp_handle_upload($_FILES['institute_logo'], ['test_form' => false]);
        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
            wp_die();
        }
        $attachment_id = wp_insert_attachment([
            'guid' => $upload['url'],
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name($_FILES['institute_logo']['name']),
            'post_content' => '',
            'post_status' => 'inherit'
        ], $upload['file'], $center_id);

        if (!is_wp_error($attachment_id)) {
            update_post_meta($center_id, 'institute_logo', $attachment_id);
        }
    }

    wp_send_json_success();
    wp_die();
}

add_action('wp_ajax_su_p_delete_center', 'su_p_delete_center');
function su_p_delete_center() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_id = intval($_POST['center_id']);
    $admin_user_id = get_post_meta($center_id, 'admin_user_id', true);
    if ($admin_user_id) {
        wp_delete_user($admin_user_id);
    }
    $deleted = wp_delete_post($center_id, true);

    if ($deleted) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => 'Failed to delete center']);
    }
    wp_die();
}

add_action('wp_ajax_su_p_reset_admin_password', 'su_p_reset_admin_password');
function su_p_reset_admin_password() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_id = intval($_POST['center_id']);
    $admin_user_id = get_post_meta($center_id, 'admin_user_id', true);
    if (!$admin_user_id) {
        wp_send_json_error(['message' => 'No admin assigned to this center']);
        wp_die();
    }

    $user = get_user_by('ID', $admin_user_id);
    if (!$user) {
        wp_send_json_error(['message' => 'Admin not found']);
        wp_die();
    }

    $reset_key = get_password_reset_key($user);
    if (is_wp_error($reset_key)) {
        wp_send_json_error(['message' => $reset_key->get_error_message()]);
        wp_die();
    }

    $reset_link = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
    $message = "Click the link to reset your password: $reset_link";
    $sent = wp_mail($user->user_email, 'Password Reset Request', $message);

    if ($sent) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => 'Failed to send reset email']);
    }
    wp_die();
}

add_action('wp_ajax_su_p_generate_admin_id', 'su_p_generate_admin_id');
function su_p_generate_admin_id() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_id = intval($_POST['center_id']);
    $admin_id = get_unique_id_for_role('institute_admins', $center_id);

    wp_send_json_success(['admin_id' => $admin_id]);
    wp_die();
}

add_action('wp_ajax_su_p_add_new_admin', 'su_p_add_new_admin');
function su_p_add_new_admin() {
    check_ajax_referer('su_p_center_nonce', 'nonce');

    $center_id = intval($_POST['center_id']);
    $admin_id = sanitize_text_field($_POST['admin_id']); // Use generated admin_id
    $admin_name = sanitize_text_field($_POST['admin_name']);
    $email = sanitize_email($_POST['email_id']);
    $password = $_POST['password'];
    $mobile = sanitize_text_field($_POST['mobile_number']);
    $educational_center_id = sanitize_text_field($_POST['educational_center_id']); // From hidden input
    // Delete existing admin if present
    $old_admin_user_id = get_post_meta($center_id, 'admin_user_id', true);
    if ($old_admin_user_id) {
        wp_delete_user($old_admin_user_id);
    }

    // Create new admin user
    $username = sanitize_user(strtolower(str_replace(' ', '-', $admin_name)));
    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
        wp_die();
    }
    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => $admin_name,
    ));
    $user = new WP_User($user_id);
    $user->set_role('institute_admin');
    update_user_meta($user_id, 'mobile_number', $mobile);
    $edu_center_stored = update_user_meta($created_user_id, 'educational_center_id', $educational_center_id);
    $home_url = home_url();
    $message = "Welcome, $admin_name!\n\nYour username: $admin_id\nYour password: $password\n\nLogin at: $home_url/login/\n\n Educaional Center ID $educational_center_id";
    wp_mail($email, 'Your Account Credentials', $message);

    // Update center with new admin_id and user_id
    update_post_meta($center_id, 'admin_id', $admin_id);
    update_post_meta($center_id, 'admin_user_id', $user_id);

    wp_send_json_success(['admin_id' => $admin_id]);
    wp_die();
}

// function get_unique_id_for_role($role_type, $center_id = '') {
//     $prefix = ($role_type === 'institute_admins') ? 'IA' : 'UNK';
//     return $prefix . '-' . $center_id . '-' . uniqid();
// }

// render_su_p_teachers Management (Stub)
function render_su_p_teachers() {
    global $wpdb;
    ob_start();
    ?>
    <div class="edu-teachers-container" style="margin-top: 80px;">
        <!-- Loader SVG -->
        <div id="edu-loader" class="edu-loader" style="display: none;">
            <div class="edu-loader-container">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
            </div>
        </div>
        <h2 class="edu-teachers-title">Managing Teachers</h2>
        <div class="edu-teachers-actions">
            <button class="edu-button edu-button-primary" id="add-teacher-btn">Add New Teacher</button>
            <input type="text" id="teacher-search" class="edu-search-input" placeholder="Search Teachers..." style="margin-left: 20px; padding: 8px; width: 300px;">
            <select id="center-filter" class="edu-select" style="margin-left: 20px; padding: 8px;">
                <option value="">All Educational Centers</option>
                <?php
                $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
                foreach ($centers as $center) {
                    $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                    echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                }
                ?>
            </select>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="teachers-per-page">Show:</label>
            <select id="teachers-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <div class="export-tools" id="export-tools" style="margin-bottom: 10px;"></div>
            <table class="edu-table" id="teachers-table">
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Edu Center ID</th>
                        <th>Edu Center Name</th>
                        <th>Teacher Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Roll Number</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="teachers-table-body">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div class="edu-modal" id="add-teacher-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-teacher-modal">×</span>
            <h3>Add New Teacher</h3>
            <form id="add-teacher-form" class="edu-form" enctype="multipart/form-data">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-id">Teacher ID</label>
                    <input type="text" class="edu-form-input" id="teacher-id" name="teacher_id" value="TEA-<?php echo uniqid(); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="educational-center-id">Educational Center ID</label>
                    <select class="edu-form-input" id="educational-center-id" name="educational_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-name">Teacher Name</label>
                    <input type="text" class="edu-form-input" id="teacher-name" name="teacher_name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-email">Email</label>
                    <input type="email" class="edu-form-input" id="teacher-email" name="teacher_email" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-phone">Phone Number</label>
                    <input type="text" class="edu-form-input" id="teacher-phone" name="teacher_phone_number">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-gender">Gender</label>
                    <select class="edu-form-input" id="teacher-gender" name="teacher_gender">
                        <option value="">Select Gender</option>
                        <?php $gender_field = get_field_object('field_67baed90b66de'); foreach ($gender_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-dob">Date of Birth</label>
                    <input type="date" class="edu-form-input" id="teacher-dob" name="teacher_date_of_birth">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-religion">Religion</label>
                    <select class="edu-form-input" id="teacher-religion" name="teacher_religion">
                        <option value="">Select Religion</option>
                        <?php $religion_field = get_field_object('field_67baed90bdebb'); foreach ($religion_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-photo">Profile Photo</label>
                    <input type="file" class="edu-form-input" id="teacher-photo" name="teacher_profile_photo" accept="image/*">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-blood">Blood Group</label>
                    <select class="edu-form-input" id="teacher-blood" name="teacher_blood_group">
                        <option value="">Select Blood Group</option>
                        <?php $blood_field = get_field_object('field_67baed90c555b'); foreach ($blood_field['choices'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-height">Height (cm)</label>
                    <input type="number" class="edu-form-input" id="teacher-height" name="teacher_height">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-weight">Weight (kg)</label>
                    <input type="number" class="edu-form-input" id="teacher-weight" name="teacher_weight">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-current-addr">Current Address</label>
                    <textarea class="edu-form-input" id="teacher-current-addr" name="teacher_current_address"></textarea>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-permanent-addr">Permanent Address</label>
                    <textarea class="edu-form-input" id="teacher-permanent-addr" name="teacher_permanent_address"></textarea>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-roll">Roll Number</label>
                    <input type="number" class="edu-form-input" id="teacher-roll" name="teacher_roll_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-admission">Admission Date</label>
                    <input type="date" class="edu-form-input" id="teacher-admission" name="teacher_admission_date" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-dept">Department</label>
                    <input type="text" class="edu-form-input" id="teacher-dept" name="teacher_department">
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="save-teacher">Save Teacher</button>
            </form>
            <div class="edu-form-message" id="add-teacher-message"></div>
        </div>
    </div>

    <!-- Edit Teacher Modal -->
 <!-- Edit Teacher Modal -->
    <div class="edu-modal" id="edit-teacher-modal">
    <div class="edu-modal-content">
        <span class="edu-modal-close" data-modal="edit-teacher-modal">×</span>
        <h3>Edit Teacher</h3>
        <form id="edit-teacher-form" class="edu-form" enctype="multipart/form-data">
            <input type="hidden" id="edit-teacher-post-id" name="teacher_post_id">
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-id">Teacher ID</label>
                <input type="text" class="edu-form-input" id="edit-teacher-id" name="teacher_id" readonly>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-educational-center-id">Educational Center ID</label>
                <input type="text" class="edu-form-input" id="edit-educational-center-id" name="educational_center_id" readonly>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-name">Teacher Name</label>
                <input type="text" class="edu-form-input" id="edit-teacher-name" name="teacher_name" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-email">Email</label>
                <input type="email" class="edu-form-input" id="edit-teacher-email" name="teacher_email" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-phone">Phone Number</label>
                <input type="text" class="edu-form-input" id="edit-teacher-phone" name="teacher_phone_number">
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-gender">Gender</label>
                <select class="edu-form-input" id="edit-teacher-gender" name="teacher_gender">
                    <option value="">Select Gender</option>
                    <?php $gender_field = get_field_object('field_67baed90b66de'); foreach ($gender_field['choices'] as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-dob">Date of Birth</label>
                <input type="date" class="edu-form-input" id="edit-teacher-dob" name="teacher_date_of_birth">
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-religion">Religion</label>
                <select class="edu-form-input" id="edit-teacher-religion" name="teacher_religion">
                    <option value="">Select Religion</option>
                    <?php $religion_field = get_field_object('field_67baed90bdebb'); foreach ($religion_field['choices'] as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-photo">Profile Photo</label>
                <input type="file" class="edu-form-input" id="edit-teacher-photo" name="teacher_profile_photo" accept="image/*">
                <img id="edit-teacher-photo-preview" style="max-width: 200px; display: none;" />
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-blood">Blood Group</label>
                <select class="edu-form-input" id="edit-teacher-blood" name="teacher_blood_group">
                    <option value="">Select Blood Group</option>
                    <?php $blood_field = get_field_object('field_67baed90c555b'); foreach ($blood_field['choices'] as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-height">Height (cm)</label>
                <input type="number" class="edu-form-input" id="edit-teacher-height" name="teacher_height">
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-weight">Weight (kg)</label>
                <input type="number" class="edu-form-input" id="edit-teacher-weight" name="teacher_weight">
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-current-addr">Current Address</label>
                <textarea class="edu-form-input" id="edit-teacher-current-addr" name="teacher_current_address"></textarea>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-permanent-addr">Permanent Address</label>
                <textarea class="edu-form-input" id="edit-teacher-permanent-addr" name="teacher_permanent_address"></textarea>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-roll">Roll Number</label>
                <input type="number" class="edu-form-input" id="edit-teacher-roll" name="teacher_roll_number" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-admission">Admission Date</label>
                <input type="date" class="edu-form-input" id="edit-teacher-admission" name="teacher_admission_date" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="edit-teacher-dept">Department</label>
                <select class="edu-form-input" id="edit-teacher-dept" name="teacher_department">
                    <option value="">Select Department</option>
                    <!-- Options populated via JavaScript -->
                </select>
            </div>
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>">
            <button type="button" class="edu-button edu-button-primary" id="update-teacher">Update Teacher</button>
        </form>
        <div class="edu-form-message" id="edit-teacher-message"></div>
    </div>
    </div>

    <!-- Dependencies -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> -->

    <script>
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let centerFilter = '';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
        showLoader(); 
        $(modalId).css('display', 'block'); 
        setTimeout(hideLoader, 100); 
    }
    function closeModal(modalId) { 
        $(modalId).css('display', 'none'); 
        $(modalId + ' .edu-form-message').removeClass('edu-success edu-error').text(''); 
        hideLoader(); 
    }

    // Close modal when clicking the close button
    $('.edu-modal-close').on('click', function() {
        const modalId = '#' + $(this).data('modal');
        closeModal(modalId);
    });

    // Close modal when clicking outside the modal content
    $(document).on('click', function(event) {
        if ($(event.target).hasClass('edu-modal')) {
            closeModal('#' + event.target.id);
        }
    });
        function getTableData() {
            const table = $('#teachers-table')[0];
            if (!table) return [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim()).slice(0, -1); // Exclude Actions
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                const cells = Array.from(row.querySelectorAll('td')).slice(0, -1); // Exclude Actions
                return cells.map(td => td.textContent.trim());
            });
            return [headers, ...rows];
        }

        function exportToCSV() {
            const data = getTableData();
            const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `teachers_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
        }

        function exportToExcel() {
            if (!window.XLSX) { console.error('XLSX library not loaded'); return; }
            const data = getTableData();
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Teachers');
            XLSX.writeFile(wb, `teachers_${new Date().toISOString().slice(0,10)}.xlsx`);
        }

        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'mm', format: 'a4' });
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';
            const pageWidth = doc.internal.pageSize.width;
            const pageHeight = doc.internal.pageSize.height;
            const margin = 10;
            const borderColor = [70, 131, 180];

            doc.setDrawColor(...borderColor);
            doc.setLineWidth(1);
            doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

            if (instituteLogo) {
                try {
                    doc.addImage(instituteLogo, 'JPEG', (pageWidth - 24) / 2, 15, 24, 24);
                } catch (e) {
                    console.log('Logo loading failed:', e);
                    doc.setFontSize(10);
                    doc.text('No logo available', (pageWidth - 20) / 2, 20, { align: 'center' });
                }
            }
            doc.setFontSize(18);
            doc.setTextColor(...borderColor);
            doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
            doc.setFontSize(12);
            doc.setTextColor(102);
            doc.text('Teachers List', pageWidth / 2, 55, { align: 'center' });
            doc.setDrawColor(...borderColor);
            doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

            const details = [
                ['Date', new Date().toLocaleDateString()],
                ['Total Teachers', String(data.length - 1)]
            ];
            let y = 70;
            details.forEach(([label, value]) => {
                doc.setFillColor(245, 245, 245);
                doc.rect(margin + 5, y, 50, 6, 'F');
                doc.setTextColor(...borderColor);
                doc.setFont('helvetica', 'bold');
                doc.text(label, margin + 7, y + 4);
                doc.setTextColor(51);
                doc.setFont('helvetica', 'normal');
                doc.text(String(value), margin + 60, y + 4);
                y += 6;
            });

            if (typeof doc.autoTable === 'function' && data.length > 1) {
                doc.autoTable({
                    startY: y + 10,
                    head: [data[0]],
                    body: data.slice(1),
                    theme: 'striped',
                    styles: { fontSize: 11, cellPadding: 2, overflow: 'linebreak', halign: 'center', textColor: [51, 51, 51] },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [249, 249, 249] }
                });

                const finalY = doc.lastAutoTable.finalY || y + 10;
                doc.setFontSize(9);
                doc.setTextColor(102);
                doc.text(`This is an Online Generated Teachers List issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
                doc.text(`Generated on ${new Date().toISOString().slice(0,10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
                doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

                doc.save(`teachers_${new Date().toISOString().slice(0,10)}.pdf`);
            } else {
                console.error('jsPDF autoTable plugin not loaded or no data');
                alert('PDF generation failed');
            }
        }

        function copyToClipboard() {
            const data = getTableData();
            const text = data.map(row => row.join('\t')).join('\n');
            navigator.clipboard.writeText(text).then(() => alert('Teachers copied to clipboard!'));
        }

        function printTeachers() {
            const printWindow = window.open('', '_blank');
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';

            printWindow.document.write(`
                <html>
                <head>
                    <title>Teachers List</title>
                    <style>
                        @media print {
                            body { font-family: Helvetica, sans-serif; margin: 10mm; width: 190mm; }
                            .page { border: 4px solid #4683b4; padding: 5mm; box-sizing: border-box; width: 100%; max-width: 190mm; }
                            .header { text-align: center; border-bottom: 2px solid #4683b4; margin-bottom: 10mm; }
                            .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                            .header h1 { font-size: 18pt; color: #4683b4; margin: 0; text-transform: uppercase; }
                            .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                            table { width: 100%; max-width: 100%; border-collapse: collapse; margin: 10mm 0; table-layout: fixed; }
                            th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; word-wrap: break-word; font-size: 10pt; }
                            th { background: #4683b4; color: white; font-weight: bold; }
                            tr:nth-child(even) { background: #f9f9f9; }
                            .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            @page { size: A4; margin: 10mm; }
                        }
                    </style>
                </head>
                <body>
                    <div class="page">
                        <div class="header">
                            ${instituteLogo ? `<img src="${instituteLogo}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                            <h1>${instituteName.toUpperCase()}</h1>
                            <p class="subtitle">Teachers List</p>
                        </div>
                        <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                        <p><strong>Total Teachers:</strong> ${data.length - 1}</p>
                        <table>
                            <thead>
                                <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                            </thead>
                            <tbody>
                                ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                            </tbody>
                        </table>
                        <div class="footer">
                            <p>This is an Online Generated Teachers List issued by ${instituteName}</p>
                            <p>Generated on ${new Date().toISOString().slice(0,10)}</p>
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

        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
            `);
            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
            tools.find('.export-copy').on('click', copyToClipboard);
            tools.find('.export-print').on('click', printTeachers);
        }

        function loadTeachers(page, limit, query, center) {
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_fetch_teachers',
                    page: page,
                    per_page: limit,
                    search: query,
                    center_filter: center,
                    nonce: '<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const teachers = response.data.teachers;
                        const total = response.data.total;
                        let html = '';
                        teachers.forEach(teacher => {
                            html += `
                                <tr data-teacher-id="${teacher.ID}">
                                    <td>${teacher.teacher_id || 'N/A'}</td>
                                    <td>${teacher.educational_center_id || 'N/A'}</td>
                                    <td>${teacher.center_name || 'N/A'}</td>
                                    <td>${teacher.teacher_name || 'N/A'}</td>
                                    <td>${teacher.teacher_email || 'N/A'}</td>
                                    <td>${teacher.teacher_phone_number || 'N/A'}</td>
                                    <td>${teacher.teacher_roll_number || 'N/A'}</td>
                                    <td>${teacher.teacher_department || 'N/A'}</td>
                                    <td>
                                        <button class="edu-button edu-button-edit edit-teacher" data-teacher-id="${teacher.ID}">Edit</button>
                                        <button class="edu-button edu-button-delete delete-teacher" data-teacher-id="${teacher.ID}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#teachers-table-body').html(html);
                        setupExportButtons();

                        const totalPages = Math.ceil(total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages}`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                    } else {
                        $('#teachers-table-body').html('<tr><td colspan="9">No teachers found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#teachers-table-body').html('<tr><td colspan="9">Error loading teachers: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        // Initial load
        loadTeachers(currentPage, perPage, searchQuery, centerFilter);

        // Search and Filter
        $('#teacher-search').on('input', function() {
            searchQuery = $(this).val();
            currentPage = 1;
            loadTeachers(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadTeachers(currentPage, perPage, searchQuery, centerFilter);
        });

        // Pagination
        $('#teachers-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadTeachers(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadTeachers(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadTeachers(currentPage, perPage, searchQuery, centerFilter);
        });

        // Add Teacher
        $('#add-teacher-btn').on('click', function() { openModal('#add-teacher-modal'); });
        $('#save-teacher').on('click', function() {
            const formData = new FormData($('#add-teacher-form')[0]);
            formData.append('action', 'su_p_add_teacher');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#add-teacher-message').addClass('edu-success').text('Teacher added successfully!');
                        setTimeout(() => {
                            closeModal('#add-teacher-modal');
                            loadTeachers(currentPage, perPage, searchQuery, centerFilter);
                        }, 1000);
                    } else {
                        $('#add-teacher-message').addClass('edu-error').text('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-teacher-message').addClass('edu-error').text('Error adding teacher: ' + error);
                }
            });
        });

        // Edit Teacher
// Edit Teacher
$(document).on('click', '.edit-teacher', function() {
    const teacherId = $(this).data('teacher-id');
    showLoader();
    $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        method: 'POST',
        data: {
            action: 'su_p_get_teacher',
            teacher_id: teacherId,
            nonce: '<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>'
        },
        success: function(response) {
            hideLoader();
            if (response.success) {
                const teacher = response.data;
                $('#edit-teacher-post-id').val(teacher.ID);
                $('#edit-teacher-id').val(teacher.teacher_id);
                $('#edit-educational-center-id').val(teacher.educational_center_id);
                $('#edit-teacher-name').val(teacher.teacher_name);
                $('#edit-teacher-email').val(teacher.teacher_email);
                $('#edit-teacher-phone').val(teacher.teacher_phone_number);
                $('#edit-teacher-gender').val(teacher.teacher_gender);
                $('#edit-teacher-dob').val(teacher.teacher_date_of_birth);
                $('#edit-teacher-religion').val(teacher.teacher_religion);
                if (teacher.teacher_profile_photo_url) {
                    $('#edit-teacher-photo-preview').attr('src', teacher.teacher_profile_photo_url).show();
                } else {
                    $('#edit-teacher-photo-preview').hide();
                }
                $('#edit-teacher-blood').val(teacher.teacher_blood_group);
                $('#edit-teacher-height').val(teacher.teacher_height);
                $('#edit-teacher-weight').val(teacher.teacher_weight);
                $('#edit-teacher-current-addr').val(teacher.teacher_current_address);
                $('#edit-teacher-permanent-addr').val(teacher.teacher_permanent_address);
                $('#edit-teacher-roll').val(teacher.teacher_roll_number);
                $('#edit-teacher-admission').val(teacher.teacher_admission_date);

                // Load departments based on readonly educational_center_id
                const centerId = teacher.educational_center_id;
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'su_p_get_departments',
                        center_id: centerId,
                        nonce: '<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>'
                    },
                    success: function(deptResponse) {
                        if (deptResponse.success) {
                            const departments = deptResponse.data;
                            let options = '<option value="">Select Department</option>';
                            departments.forEach(dept => {
                                options += `<option value="${dept.department_name}" ${dept.department_name === teacher.teacher_department ? 'selected' : ''}>${dept.department_name}</option>`;
                            });
                            $('#edit-teacher-dept').html(options);
                        } else {
                            $('#edit-teacher-dept').html('<option value="">No departments found</option>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#edit-teacher-dept').html('<option value="">Error loading departments</option>');
                    }
                });

                openModal('#edit-teacher-modal');
            } else {
                alert('Error fetching teacher: ' + response.data.message);
            }
        },
        error: function(xhr, status, error) {
            hideLoader();
            alert('Error fetching teacher: ' + error);
        }
    });
});

$('#edit-teacher-photo').on('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#edit-teacher-photo-preview').attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
    }
});

// Update department dropdown when educational center changes
// $('#edit-educational-center-id').on('change', function() {
//     const centerId = $(this).val();
//     showLoader();
//     $.ajax({
//         url: '<?php echo admin_url('admin-ajax.php'); ?>',
//         method: 'POST',
//         data: {
//             action: 'su_p_get_departments',
//             center_id: centerId,
//             nonce: '<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>'
//         },
//         success: function(response) {
//             hideLoader();
//             if (response.success) {
//                 const departments = response.data;
//                 let options = '<option value="">Select Department</option>';
//                 departments.forEach(dept => {
//                     options += `<option value="${dept.department_name}">${dept.department_name}</option>`;
//                 });
//                 $('#edit-teacher-dept').html(options);
//             } else {
//                 $('#edit-teacher-dept').html('<option value="">No departments found</option>');
//             }
//         },
//         error: function(xhr, status, error) {
//             hideLoader();
//             $('#edit-teacher-dept').html('<option value="">Error loading departments</option>');
//         }
//     });
// });

// Update Teacher
$('#update-teacher').on('click', function() {
    const formData = new FormData($('#edit-teacher-form')[0]);
    formData.append('action', 'su_p_update_teacher');
    showLoader();
    $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoader();
            if (response.success) {
                $('#edit-teacher-message').addClass('edu-success').text('Teacher updated successfully!');
                setTimeout(() => {
                    closeModal('#edit-teacher-modal');
                    loadTeachers(currentPage, perPage, searchQuery, centerFilter);
                }, 1000);
            } else {
                $('#edit-teacher-message').addClass('edu-error').text('Error: ' + (response.data.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            hideLoader();
            $('#edit-teacher-message').addClass('edu-error').text('Error updating teacher: ' + error);
        }
    });
});

        // Delete Teacher
        $(document).on('click', '.delete-teacher', function() {
            if (!confirm('Are you sure you want to delete this teacher?')) return;
            const teacherId = $(this).data('teacher-id');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_delete_teacher',
                    teacher_id: teacherId,
                    nonce: '<?php echo wp_create_nonce('su_p_teacher_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        loadTeachers(currentPage, perPage, searchQuery, centerFilter);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error deleting teacher: ' + error);
                }
            });
        });
    });
    </script>

 
    <?php
    return ob_get_clean();
}

// AJAX Handlers
add_action('wp_ajax_su_p_fetch_teachers', 'su_p_fetch_teachers');
function su_p_fetch_teachers() {
    check_ajax_referer('su_p_teacher_nonce', 'nonce');
    global $wpdb;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $center_filter = isset($_POST['center_filter']) ? sanitize_text_field($_POST['center_filter']) : '';
    $offset = ($page - 1) * $per_page;

    $args = [
        'post_type' => 'teacher',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'post_status' => 'publish'
    ];
    if ($search) {
        $args['meta_query'] = [
            'relation' => 'OR',
            ['key' => 'teacher_id', 'value' => $search, 'compare' => 'LIKE'],
            ['key' => 'teacher_name', 'value' => $search, 'compare' => 'LIKE'],
            ['key' => 'teacher_email', 'value' => $search, 'compare' => 'LIKE'],
            ['key' => 'teacher_phone_number', 'value' => $search, 'compare' => 'LIKE']
        ];
    }
    if ($center_filter) {
        $args['meta_query'][] = ['key' => 'educational_center_id', 'value' => $center_filter, 'compare' => '='];
    }

    $query = new WP_Query($args);
    $teachers = [];
    foreach ($query->posts as $post) {
        $center_id = get_post_meta($post->ID, 'educational_center_id', true);
        $center = $center_id ? get_posts(['post_type' => 'educational-center', 'meta_key' => 'educational_center_id', 'meta_value' => $center_id, 'posts_per_page' => 1])[0] : null;
        $teachers[] = [
            'ID' => $post->ID,
            'teacher_id' => get_post_meta($post->ID, 'teacher_id', true),
            'educational_center_id' => $center_id,
            'center_name' => $center ? $center->post_title : 'N/A',
            'teacher_name' => get_post_meta($post->ID, 'teacher_name', true),
            'teacher_email' => get_post_meta($post->ID, 'teacher_email', true),
            'teacher_phone_number' => get_post_meta($post->ID, 'teacher_phone_number', true),
            'teacher_roll_number' => get_post_meta($post->ID, 'teacher_roll_number', true),
            'teacher_department' => get_post_meta($post->ID, 'teacher_department', true)
        ];
    }
    $total = $query->found_posts;
    wp_send_json_success(['teachers' => $teachers, 'total' => $total]);
}
add_action('wp_ajax_su_p_get_departments', 'su_p_get_departments');
function su_p_get_departments() {
    check_ajax_referer('su_p_teacher_nonce', 'nonce');
    global $wpdb;
    $center_id = sanitize_text_field($_POST['center_id']);
    $table_name = $wpdb->prefix . 'departments'; // Ensure this matches your table name

    $departments = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT department_id, department_name FROM $table_name WHERE education_center_id = %s",
            $center_id
        ),
        ARRAY_A
    );

    if ($departments) {
        wp_send_json_success($departments);
    } else {
        wp_send_json_error(['message' => 'No departments found for this center']);
    }
}
add_action('wp_ajax_su_p_add_teacher', 'su_p_add_teacher');
function su_p_add_teacher() {
    check_ajax_referer('su_p_teacher_nonce', 'nonce');
    $post_id = wp_insert_post([
        'post_title' => sanitize_text_field($_POST['teacher_name']),
        'post_type' => 'teacher',
        'post_status' => 'publish'
    ]);
    if ($post_id) {
        update_post_meta($post_id, 'teacher_id', sanitize_text_field($_POST['teacher_id']));
        update_post_meta($post_id, 'educational_center_id', sanitize_text_field($_POST['educational_center_id']));
        update_post_meta($post_id, 'teacher_name', sanitize_text_field($_POST['teacher_name']));
        update_post_meta($post_id, 'teacher_email', sanitize_email($_POST['teacher_email']));
        update_post_meta($post_id, 'teacher_phone_number', sanitize_text_field($_POST['teacher_phone_number']));
        update_post_meta($post_id, 'teacher_gender', sanitize_text_field($_POST['teacher_gender']));
        update_post_meta($post_id, 'teacher_date_of_birth', sanitize_text_field($_POST['teacher_date_of_birth']));
        update_post_meta($post_id, 'teacher_religion', sanitize_text_field($_POST['teacher_religion']));
        if (!empty($_FILES['teacher_profile_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_id = media_handle_upload('teacher_profile_photo', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'teacher_profile_photo', $attachment_id);
            }
        }
        update_post_meta($post_id, 'teacher_blood_group', sanitize_text_field($_POST['teacher_blood_group']));
        update_post_meta($post_id, 'teacher_height', sanitize_text_field($_POST['teacher_height']));
        update_post_meta($post_id, 'teacher_weight', sanitize_text_field($_POST['teacher_weight']));
        update_post_meta($post_id, 'teacher_current_address', sanitize_textarea_field($_POST['teacher_current_address']));
        update_post_meta($post_id, 'teacher_permanent_address', sanitize_textarea_field($_POST['teacher_permanent_address']));
        update_post_meta($post_id, 'teacher_roll_number', sanitize_text_field($_POST['teacher_roll_number']));
        update_post_meta($post_id, 'teacher_admission_date', sanitize_text_field($_POST['teacher_admission_date']));
        update_post_meta($post_id, 'teacher_department', sanitize_text_field($_POST['teacher_department']));
        wp_send_json_success(['message' => 'Teacher added']);
    } else {
        wp_send_json_error(['message' => 'Failed to add teacher']);
    }
}

add_action('wp_ajax_su_p_get_teacher', 'su_p_get_teacher');
function su_p_get_teacher() {
    check_ajax_referer('su_p_teacher_nonce', 'nonce');
    $teacher_id = intval($_POST['teacher_id']);
    $post = get_post($teacher_id);
    if ($post && $post->post_type === 'teacher') {
        $teacher = [
            'ID' => $post->ID,
            'teacher_id' => get_post_meta($post->ID, 'teacher_id', true),
            'educational_center_id' => get_post_meta($post->ID, 'educational_center_id', true),
            'teacher_name' => get_post_meta($post->ID, 'teacher_name', true),
            'teacher_email' => get_post_meta($post->ID, 'teacher_email', true),
            'teacher_phone_number' => get_post_meta($post->ID, 'teacher_phone_number', true),
            'teacher_gender' => get_post_meta($post->ID, 'teacher_gender', true),
            'teacher_date_of_birth' => get_post_meta($post->ID, 'teacher_date_of_birth', true),
            'teacher_religion' => get_post_meta($post->ID, 'teacher_religion', true),
            'teacher_profile_photo_url' => wp_get_attachment_url(get_post_meta($post->ID, 'teacher_profile_photo', true)),
            'teacher_blood_group' => get_post_meta($post->ID, 'teacher_blood_group', true),
            'teacher_height' => get_post_meta($post->ID, 'teacher_height', true),
            'teacher_weight' => get_post_meta($post->ID, 'teacher_weight', true),
            'teacher_current_address' => get_post_meta($post->ID, 'teacher_current_address', true),
            'teacher_permanent_address' => get_post_meta($post->ID, 'teacher_permanent_address', true),
            'teacher_roll_number' => get_post_meta($post->ID, 'teacher_roll_number', true),
            'teacher_admission_date' => get_post_meta($post->ID, 'teacher_admission_date', true),
            'teacher_department' => get_post_meta($post->ID, 'teacher_department', true)
        ];
        wp_send_json_success($teacher);
    } else {
        wp_send_json_error(['message' => 'Teacher not found']);
    }
}

add_action('wp_ajax_su_p_update_teacher', 'su_p_update_teacher');
function su_p_update_teacher() {
    check_ajax_referer('su_p_teacher_nonce', 'nonce');

    // Get post ID and key identifiers
    $post_id = intval($_POST['teacher_post_id']);
    $teacher_id = sanitize_text_field($_POST['teacher_id']);
    $educational_center_id = sanitize_text_field($_POST['educational_center_id']);

    // Verify the post exists
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'teacher') {
        wp_send_json_error(['message' => 'Invalid teacher post ID']);
        return;
    }

    // Check uniqueness of teacher_id and educational_center_id combination
    $args = [
        'post_type' => 'teacher',
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'teacher_id', 'value' => $teacher_id, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
        ],
        'posts_per_page' => 1,
        'exclude' => [$post_id] // Exclude current post
    ];
    $existing = new WP_Query($args);
    // if ($existing->have_posts()) {
    //     wp_send_json_error(['message' => 'Teacher ID and Educational Center ID combination must be unique']);
    //     return;
    // }

    // Update the post
    $updated = wp_update_post([
        'ID' => $post_id,
        'post_title' => sanitize_text_field($_POST['teacher_name']),
    ]);

    if ($updated && !is_wp_error($updated)) {
        // Update meta fields (educational_center_id and teacher_id remain unchanged due to readonly)
        update_post_meta($post_id, 'teacher_name', sanitize_text_field($_POST['teacher_name']));
        update_post_meta($post_id, 'teacher_email', sanitize_email($_POST['teacher_email']));
        update_post_meta($post_id, 'teacher_phone_number', sanitize_text_field($_POST['teacher_phone_number']));
        update_post_meta($post_id, 'teacher_gender', sanitize_text_field($_POST['teacher_gender']));
        update_post_meta($post_id, 'teacher_date_of_birth', sanitize_text_field($_POST['teacher_date_of_birth']));
        update_post_meta($post_id, 'teacher_religion', sanitize_text_field($_POST['teacher_religion']));
        if (!empty($_FILES['teacher_profile_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_id = media_handle_upload('teacher_profile_photo', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'teacher_profile_photo', $attachment_id);
            } else {
                wp_send_json_error(['message' => 'Failed to upload profile photo: ' . $attachment_id->get_error_message()]);
                return;
            }
        }
        update_post_meta($post_id, 'teacher_blood_group', sanitize_text_field($_POST['teacher_blood_group']));
        update_post_meta($post_id, 'teacher_height', sanitize_text_field($_POST['teacher_height']));
        update_post_meta($post_id, 'teacher_weight', sanitize_text_field($_POST['teacher_weight']));
        update_post_meta($post_id, 'teacher_current_address', sanitize_textarea_field($_POST['teacher_current_address']));
        update_post_meta($post_id, 'teacher_permanent_address', sanitize_textarea_field($_POST['teacher_permanent_address']));
        update_post_meta($post_id, 'teacher_roll_number', sanitize_text_field($_POST['teacher_roll_number']));
        update_post_meta($post_id, 'teacher_admission_date', sanitize_text_field($_POST['teacher_admission_date']));
        update_post_meta($post_id, 'teacher_department', sanitize_text_field($_POST['teacher_department']));

        wp_send_json_success(['message' => 'Teacher updated successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to update teacher post']);
    }
}

add_action('wp_ajax_su_p_delete_teacher', 'su_p_delete_teacher');
function su_p_delete_teacher() {
    check_ajax_referer('su_p_teacher_nonce', 'nonce');
    $teacher_id = intval($_POST['teacher_id']);
    if (wp_delete_post($teacher_id, true)) {
        wp_send_json_success(['message' => 'Teacher deleted']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete teacher']);
    }
}

// students
function render_su_p_students() {
    global $wpdb;
    ob_start();
    ?>
    <div class="edu-students-container" style="margin-top: 80px;">
        <!-- Loader SVG -->
        <div id="edu-loader" class="edu-loader" style="display: none;">
            <div class="edu-loader-container">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
            </div>
        </div>
        <h2 class="edu-students-title">Managing Students</h2>
        <div class="edu-students-actions">
            <button class="edu-button edu-button-primary" id="add-student-btn">Add New Student</button>
            <input type="text" id="student-search" class="edu-search-input" placeholder="Search Students..." style="margin-left: 20px; padding: 8px; width: 300px;">
            <select id="center-filter" class="edu-select" style="margin-left: 20px; padding: 8px;">
                <option value="">All Educational Centers</option>
                <?php
                $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
                foreach ($centers as $center) {
                    $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                    echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                }
                ?>
            </select>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="students-per-page">Show:</label>
            <select id="students-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <div class="export-tools" id="export-tools" style="margin-bottom: 10px;"></div>
            <table class="edu-table" id="students-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Edu Center ID</th>
                        <th>Edu Center Name</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll Number</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="students-table-body">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="edu-modal" id="add-student-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-student-modal">×</span>
            <h3>Add New Student</h3>
            <form id="add-student-form" class="edu-form" enctype="multipart/form-data">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-id">Student ID</label>
                    <input type="text" class="edu-form-input" id="student-id" name="student_id" value="STU-<?php echo uniqid(); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="educational-center-id">Educational Center ID</label>
                    <select class="edu-form-input" id="educational-center-id" name="educational_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="admission-number">Admission Number</label>
                    <input type="text" class="edu-form-input" id="admission-number" name="admission_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-name">Student Name</label>
                    <input type="text" class="edu-form-input" id="student-name" name="student_name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-email">Email</label>
                    <input type="email" class="edu-form-input" id="student-email" name="student_email">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-phone">Phone Number</label>
                    <input type="text" class="edu-form-input" id="student-phone" name="phone_number">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="class-name">Class</label>
                    <select class="edu-form-input" id="class-name" name="class_name" required>
                        <option value="">Select Class</option>
                        <!-- Populated via JavaScript -->
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="section">Section</label>
                    <select class="edu-form-input" id="section" name="section" required disabled>
                        <option value="">Select Class First</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-gender">Gender</label>
                    <select class="edu-form-input" id="student-gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-dob">Date of Birth</label>
                    <input type="date" class="edu-form-input" id="student-dob" name="date_of_birth">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-religion">Religion</label>
                    <select class="edu-form-input" id="student-religion" name="religion">
                        <option value="">Select Religion</option>
                        <option value="christianity">Christianity</option>
                        <option value="islam">Islam</option>
                        <option value="hinduism">Hinduism</option>
                        <option value="buddhism">Buddhism</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-photo">Profile Photo</label>
                    <input type="file" class="edu-form-input" id="student-photo" name="student_profile_photo" accept="image/*">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-blood">Blood Group</label>
                    <select class="edu-form-input" id="student-blood" name="blood_group">
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
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-height">Height (cm)</label>
                    <input type="number" class="edu-form-input" id="student-height" name="height">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-weight">Weight (kg)</label>
                    <input type="number" class="edu-form-input" id="student-weight" name="weight">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-current-addr">Current Address</label>
                    <textarea class="edu-form-input" id="student-current-addr" name="current_address"></textarea>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-permanent-addr">Permanent Address</label>
                    <textarea class="edu-form-input" id="student-permanent-addr" name="permanent_address"></textarea>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-roll">Roll Number</label>
                    <input type="number" class="edu-form-input" id="student-roll" name="roll_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-admission">Admission Date</label>
                    <input type="date" class="edu-form-input" id="student-admission" name="admission_date" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_student_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="save-student">Save Student</button>
            </form>
            <div class="edu-form-message" id="add-student-message"></div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="edu-modal" id="edit-student-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-student-modal">×</span>
            <h3>Edit Student</h3>
            <form id="edit-student-form" class="edu-form" enctype="multipart/form-data">
                <input type="hidden" id="edit-student-post-id" name="student_post_id">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-id">Student ID</label>
                    <input type="text" class="edu-form-input" id="edit-student-id" name="student_id" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-educational-center-id">Educational Center ID</label>
                    <input type="text" class="edu-form-input" id="edit-educational-center-id" name="educational_center_id" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-admission-number">Admission Number</label>
                    <input type="text" class="edu-form-input" id="edit-admission-number" name="admission_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-name">Student Name</label>
                    <input type="text" class="edu-form-input" id="edit-student-name" name="student_name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-email">Email</label>
                    <input type="email" class="edu-form-input" id="edit-student-email" name="student_email">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-phone">Phone Number</label>
                    <input type="text" class="edu-form-input" id="edit-student-phone" name="phone_number">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-class-name">Class</label>
                    <select class="edu-form-input" id="edit-class-name" name="class_name" required>
                        <option value="">Select Class</option>
                        <!-- Populated via JavaScript -->
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-section">Section</label>
                    <select class="edu-form-input" id="edit-section" name="section" required disabled>
                        <option value="">Select Class First</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-gender">Gender</label>
                    <select class="edu-form-input" id="edit-student-gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-dob">Date of Birth</label>
                    <input type="date" class="edu-form-input" id="edit-student-dob" name="date_of_birth">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-religion">Religion</label>
                    <select class="edu-form-input" id="edit-student-religion" name="religion">
                        <option value="">Select Religion</option>
                        <option value="christianity">Christianity</option>
                        <option value="islam">Islam</option>
                        <option value="hinduism">Hinduism</option>
                        <option value="buddhism">Buddhism</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-photo">Profile Photo</label>
                    <input type="file" class="edu-form-input" id="edit-student-photo" name="student_profile_photo" accept="image/*">
                    <img id="edit-student-photo-preview" style="max-width: 200px; display: none;" />
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-blood">Blood Group</label>
                    <select class="edu-form-input" id="edit-student-blood" name="blood_group">
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
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-height">Height (cm)</label>
                    <input type="number" class="edu-form-input" id="edit-student-height" name="height">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-weight">Weight (kg)</label>
                    <input type="number" class="edu-form-input" id="edit-student-weight" name="weight">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-current-addr">Current Address</label>
                    <textarea class="edu-form-input" id="edit-student-current-addr" name="current_address"></textarea>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-permanent-addr">Permanent Address</label>
                    <textarea class="edu-form-input" id="edit-student-permanent-addr" name="permanent_address"></textarea>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-roll">Roll Number</label>
                    <input type="number" class="edu-form-input" id="edit-student-roll" name="roll_number" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-admission">Admission Date</label>
                    <input type="date" class="edu-form-input" id="edit-student-admission" name="admission_date" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_student_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="update-student">Update Student</button>
            </form>
            <div class="edu-form-message" id="edit-student-message"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let centerFilter = '';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
            showLoader(); 
            $(modalId).css('display', 'block'); 
            setTimeout(hideLoader, 100); 
        }
        function closeModal(modalId) { 
            $(modalId).css('display', 'none'); 
            $(modalId + ' .edu-form-message').removeClass('edu-success edu-error').text(''); 
            hideLoader(); 
        }

        $('.edu-modal-close').on('click', function() {
            const modalId = '#' + $(this).data('modal');
            closeModal(modalId);
        });

        $(document).on('click', function(event) {
            if ($(event.target).hasClass('edu-modal')) {
                closeModal('#' + event.target.id);
            }
        });

        function getTableData() {
            const table = $('#students-table')[0];
            if (!table) return [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim()).slice(0, -1); // Exclude Actions
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                const cells = Array.from(row.querySelectorAll('td')).slice(0, -1); // Exclude Actions
                return cells.map(td => td.textContent.trim());
            });
            return [headers, ...rows];
        }

        function exportToCSV() {
            const data = getTableData();
            const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `students_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
        }

        function exportToExcel() {
            if (!window.XLSX) { console.error('XLSX library not loaded'); return; }
            const data = getTableData();
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Students');
            XLSX.writeFile(wb, `students_${new Date().toISOString().slice(0,10)}.xlsx`);
        }

        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'mm', format: 'a4' });
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';
            const pageWidth = doc.internal.pageSize.width;
            const pageHeight = doc.internal.pageSize.height;
            const margin = 10;
            const borderColor = [70, 131, 180];

            doc.setDrawColor(...borderColor);
            doc.setLineWidth(1);
            doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

            if (instituteLogo) {
                try {
                    doc.addImage(instituteLogo, 'JPEG', (pageWidth - 24) / 2, 15, 24, 24);
                } catch (e) {
                    console.log('Logo loading failed:', e);
                }
            }
            doc.setFontSize(18);
            doc.setTextColor(...borderColor);
            doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
            doc.setFontSize(12);
            doc.setTextColor(102);
            doc.text('Students List', pageWidth / 2, 55, { align: 'center' });
            doc.setDrawColor(...borderColor);
            doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

            const details = [
                ['Date', new Date().toLocaleDateString()],
                ['Total Students', String(data.length - 1)]
            ];
            let y = 70;
            details.forEach(([label, value]) => {
                doc.setFillColor(245, 245, 245);
                doc.rect(margin + 5, y, 50, 6, 'F');
                doc.setTextColor(...borderColor);
                doc.setFont('helvetica', 'bold');
                doc.text(label, margin + 7, y + 4);
                doc.setTextColor(51);
                doc.setFont('helvetica', 'normal');
                doc.text(String(value), margin + 60, y + 4);
                y += 6;
            });

            if (typeof doc.autoTable === 'function' && data.length > 1) {
                doc.autoTable({
                    startY: y + 10,
                    head: [data[0]],
                    body: data.slice(1),
                    theme: 'striped',
                    styles: { fontSize: 11, cellPadding: 2, overflow: 'linebreak', halign: 'center', textColor: [51, 51, 51] },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [249, 249, 249] }
                });

                const finalY = doc.lastAutoTable.finalY || y + 10;
                doc.setFontSize(9);
                doc.setTextColor(102);
                doc.text(`This is an Online Generated Students List issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
                doc.text(`Generated on ${new Date().toISOString().slice(0,10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
                doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

                doc.save(`students_${new Date().toISOString().slice(0,10)}.pdf`);
            } else {
                console.error('jsPDF autoTable plugin not loaded or no data');
                alert('PDF generation failed');
            }
        }

        function copyToClipboard() {
            const data = getTableData();
            const text = data.map(row => row.join('\t')).join('\n');
            navigator.clipboard.writeText(text).then(() => alert('Students copied to clipboard!'));
        }

        function printStudents() {
            const printWindow = window.open('', '_blank');
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';

            printWindow.document.write(`
                <html>
                <head>
                    <title>Students List</title>
                    <style>
                        @media print {
                            body { font-family: Helvetica, sans-serif; margin: 10mm; width: 190mm; }
                            .page { border: 4px solid #4683b4; padding: 5mm; box-sizing: border-box; width: 100%; max-width: 190mm; }
                            .header { text-align: center; border-bottom: 2px solid #4683b4; margin-bottom: 10mm; }
                            .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                            .header h1 { font-size: 18pt; color: #4683b4; margin: 0; text-transform: uppercase; }
                            .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                            table { width: 100%; max-width: 100%; border-collapse: collapse; margin: 10mm 0; table-layout: fixed; }
                            th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; word-wrap: break-word; font-size: 10pt; }
                            th { background: #4683b4; color: white; font-weight: bold; }
                            tr:nth-child(even) { background: #f9f9f9; }
                            .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            @page { size: A4; margin: 10mm; }
                        }
                    </style>
                </head>
                <body>
                    <div class="page">
                        <div class="header">
                            ${instituteLogo ? `<img src="${instituteLogo}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                            <h1>${instituteName.toUpperCase()}</h1>
                            <p class="subtitle">Students List</p>
                        </div>
                        <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                        <p><strong>Total Students:</strong> ${data.length - 1}</p>
                        <table>
                            <thead>
                                <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                            </thead>
                            <tbody>
                                ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                            </tbody>
                        </table>
                        <div class="footer">
                            <p>This is an Online Generated Students List issued by ${instituteName}</p>
                            <p>Generated on ${new Date().toISOString().slice(0,10)}</p>
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

        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
            `);
            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
            tools.find('.export-copy').on('click', copyToClipboard);
            tools.find('.export-print').on('click', printStudents);
        }

        function loadStudents(page, limit, query, center) {
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_fetch_students',
                    page: page,
                    per_page: limit,
                    search: query,
                    center_filter: center,
                    nonce: '<?php echo wp_create_nonce('su_p_student_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const students = response.data.students;
                        const total = response.data.total;
                        let html = '';
                        students.forEach(student => {
                            html += `
                                <tr data-student-id="${student.ID}">
                                    <td>${student.student_id || 'N/A'}</td>
                                    <td>${student.educational_center_id || 'N/A'}</td>
                                    <td>${student.center_name || 'N/A'}</td>
                                    <td>${student.student_name || 'N/A'}</td>
                                    <td>${student.class_name || 'N/A'}</td>
                                    <td>${student.section || 'N/A'}</td>
                                    <td>${student.roll_number || 'N/A'}</td>
                                    <td>${student.student_email || 'N/A'}</td>
                                    <td>${student.phone_number || 'N/A'}</td>
                                    <td>
                                        <button class="edu-button edu-button-edit edit-student" data-student-id="${student.ID}">Edit</button>
                                        <button class="edu-button edu-button-delete delete-student" data-student-id="${student.ID}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#students-table-body').html(html);
                        setupExportButtons();

                        const totalPages = Math.ceil(total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages}`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                    } else {
                        $('#students-table-body').html('<tr><td colspan="10">No students found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#students-table-body').html('<tr><td colspan="10">Error loading students: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        // Initial load
        loadStudents(currentPage, perPage, searchQuery, centerFilter);

        // Search and Filter
        $('#student-search').on('input', function() {
            searchQuery = $(this).val();
            currentPage = 1;
            loadStudents(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadStudents(currentPage, perPage, searchQuery, centerFilter);
        });

        // Pagination
        $('#students-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadStudents(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadStudents(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadStudents(currentPage, perPage, searchQuery, centerFilter);
        });

        // Add Student
        $('#add-student-btn').on('click', function() { openModal('#add-student-modal'); });

        function populateClassesAndSections(centerId, classSelectId, sectionSelectId, selectedClass, selectedSection) {
            if (!centerId) return;
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_get_class_sections',
                    center_id: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_student_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const classes = response.data;
                        let classOptions = '<option value="">Select Class</option>';
                        classes.forEach(cls => {
                            classOptions += `<option value="${cls.class_name}" ${cls.class_name === selectedClass ? 'selected' : ''}>${cls.class_name}</option>`;
                        });
                        $(classSelectId).html(classOptions);

                        $(classSelectId).off('change').on('change', function() {
                            const selectedClass = $(this).val();
                            const sectionSelect = $(sectionSelectId);
                            if (selectedClass) {
                                const selectedClassData = classes.find(cls => cls.class_name === selectedClass);
                                let sectionOptions = '<option value="">Select Section</option>';
                                if (selectedClassData && selectedClassData.sections) {
                                    const sections = selectedClassData.sections.split(',');
                                    sections.forEach(section => {
                                        sectionOptions += `<option value="${section}" ${section === selectedSection ? 'selected' : ''}>${section}</option>`;
                                    });
                                }
                                sectionSelect.html(sectionOptions).prop('disabled', false);
                            } else {
                                sectionSelect.html('<option value="">Select Class First</option>').prop('disabled', true);
                            }
                        });
                        $(classSelectId).trigger('change');
                    } else {
                        $(classSelectId).html('<option value="">No classes found</option>');
                        $(sectionSelectId).html('<option value="">Select Class First</option>').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $(classSelectId).html('<option value="">Error loading classes</option>');
                    $(sectionSelectId).html('<option value="">Error</option>').prop('disabled', true);
                }
            });
        }

        $('#educational-center-id').on('change', function() {
            const centerId = $(this).val();
            populateClassesAndSections(centerId, '#class-name', '#section');
        });

        $('#save-student').on('click', function() {
            const formData = new FormData($('#add-student-form')[0]);
            formData.append('action', 'su_p_add_student');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#add-student-message').addClass('edu-success').text('Student added successfully!');
                        setTimeout(() => {
                            closeModal('#add-student-modal');
                            loadStudents(currentPage, perPage, searchQuery, centerFilter);
                        }, 1000);
                    } else {
                        $('#add-student-message').addClass('edu-error').text('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-student-message').addClass('edu-error').text('Error adding student: ' + error);
                }
            });
        });

        // Edit Student
        $(document).on('click', '.edit-student', function() {
            const studentId = $(this).data('student-id');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_get_student',
                    student_id: studentId,
                    nonce: '<?php echo wp_create_nonce('su_p_student_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const student = response.data;
                        $('#edit-student-post-id').val(student.ID);
                        $('#edit-student-id').val(student.student_id);
                        $('#edit-educational-center-id').val(student.educational_center_id);
                        $('#edit-admission-number').val(student.admission_number);
                        $('#edit-student-name').val(student.student_name);
                        $('#edit-student-email').val(student.student_email);
                        $('#edit-student-phone').val(student.phone_number);
                        $('#edit-student-gender').val(student.gender);
                        $('#edit-student-dob').val(student.date_of_birth);
                        $('#edit-student-religion').val(student.religion);
                        if (student.student_profile_photo_url) {
                            $('#edit-student-photo-preview').attr('src', student.student_profile_photo_url).show();
                        } else {
                            $('#edit-student-photo-preview').hide();
                        }
                        $('#edit-student-blood').val(student.blood_group);
                        $('#edit-student-height').val(student.height);
                        $('#edit-student-weight').val(student.weight);
                        $('#edit-student-current-addr').val(student.current_address);
                        $('#edit-student-permanent-addr').val(student.permanent_address);
                        $('#edit-student-roll').val(student.roll_number);
                        $('#edit-student-admission').val(student.admission_date);

                        populateClassesAndSections(student.educational_center_id, '#edit-class-name', '#edit-section', student.class_name, student.section);
                        openModal('#edit-student-modal');
                    } else {
                        alert('Error fetching student: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error fetching student: ' + error);
                }
            });
        });

        $('#edit-student-photo').on('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#edit-student-photo-preview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        $('#update-student').on('click', function() {
            const formData = new FormData($('#edit-student-form')[0]);
            formData.append('action', 'su_p_update_student');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#edit-student-message').addClass('edu-success').text('Student updated successfully!');
                        setTimeout(() => {
                            closeModal('#edit-student-modal');
                            loadStudents(currentPage, perPage, searchQuery, centerFilter);
                        }, 1000);
                    } else {
                        $('#edit-student-message').addClass('edu-error').text('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-student-message').addClass('edu-error').text('Error updating student: ' + error);
                }
            });
        });

        // Delete Student
        $(document).on('click', '.delete-student', function() {
            if (!confirm('Are you sure you want to delete this student?')) return;
            const studentId = $(this).data('student-id');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_delete_student',
                    student_id: studentId,
                    nonce: '<?php echo wp_create_nonce('su_p_student_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        loadStudents(currentPage, perPage, searchQuery, centerFilter);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error deleting student: ' + error);
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
// Fetch Students
add_action('wp_ajax_su_p_fetch_students', 'su_p_fetch_students');
function su_p_fetch_students() {
    check_ajax_referer('su_p_student_nonce', 'nonce');
    global $wpdb;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $center_filter = isset($_POST['center_filter']) ? sanitize_text_field($_POST['center_filter']) : '';
    $offset = ($page - 1) * $per_page;

    $args = [
        'post_type' => 'students',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'post_status' => 'publish'
    ];
    if ($search) {
        $args['meta_query'] = [
            'relation' => 'OR',
            ['key' => 'student_id', 'value' => $search, 'compare' => 'LIKE'],
            ['key' => 'student_name', 'value' => $search, 'compare' => 'LIKE'],
            ['key' => 'student_email', 'value' => $search, 'compare' => 'LIKE'],
            ['key' => 'phone_number', 'value' => 'LIKE']
        ];
    }
    if ($center_filter) {
        $args['meta_query'][] = ['key' => 'educational_center_id', 'value' => $center_filter, 'compare' => '='];
    }

    $query = new WP_Query($args);
    $students = [];
    foreach ($query->posts as $post) {
        $center_id = get_post_meta($post->ID, 'educational_center_id', true);
        $center = $center_id ? get_posts(['post_type' => 'educational-center', 'meta_key' => 'educational_center_id', 'meta_value' => $center_id, 'posts_per_page' => 1])[0] : null;
        $students[] = [
            'ID' => $post->ID,
            'student_id' => get_post_meta($post->ID, 'student_id', true),
            'educational_center_id' => $center_id,
            'center_name' => $center ? $center->post_title : 'N/A',
            'student_name' => get_post_meta($post->ID, 'student_name', true),
            'class_name' => get_post_meta($post->ID, 'class_name', true),
            'section' => get_post_meta($post->ID, 'section', true),
            'roll_number' => get_post_meta($post->ID, 'roll_number', true),
            'student_email' => get_post_meta($post->ID, 'student_email', true),
            'phone_number' => get_post_meta($post->ID, 'phone_number', true)
        ];
    }
    $total = $query->found_posts;
    wp_send_json_success(['students' => $students, 'total' => $total]);
}

// Fetch Class Sections
add_action('wp_ajax_su_p_get_class_sections', 'su_p_get_class_sections');
function su_p_get_class_sections() {
    check_ajax_referer('su_p_student_nonce', 'nonce');
    global $wpdb;
    $center_id = sanitize_text_field($_POST['center_id']);
    $table_name = $wpdb->prefix . 'class_sections';

    $classes = $wpdb->get_results(
        $wpdb->prepare("SELECT class_name, sections FROM $table_name WHERE education_center_id = %s", $center_id),
        ARRAY_A
    );

    if ($classes) {
        wp_send_json_success($classes);
    } else {
        wp_send_json_error(['message' => 'No classes found for this center']);
    }
}

// Add Student
add_action('wp_ajax_su_p_add_student', 'su_p_add_student');
function su_p_add_student() {
    check_ajax_referer('su_p_student_nonce', 'nonce');
    $post_id = wp_insert_post([
        'post_title' => sanitize_text_field($_POST['student_name']),
        'post_type' => 'students',
        'post_status' => 'publish'
    ]);
    if ($post_id) {
        update_post_meta($post_id, 'student_id', sanitize_text_field($_POST['student_id']));
        update_post_meta($post_id, 'educational_center_id', sanitize_text_field($_POST['educational_center_id']));
        update_post_meta($post_id, 'admission_number', sanitize_text_field($_POST['admission_number']));
        update_post_meta($post_id, 'student_name', sanitize_text_field($_POST['student_name']));
        update_post_meta($post_id, 'student_email', sanitize_email($_POST['student_email']));
        update_post_meta($post_id, 'phone_number', sanitize_text_field($_POST['phone_number']));
        update_post_meta($post_id, 'class_name', sanitize_text_field($_POST['class_name']));
        update_post_meta($post_id, 'section', sanitize_text_field($_POST['section']));
        update_post_meta($post_id, 'gender', sanitize_text_field($_POST['gender']));
        update_post_meta($post_id, 'date_of_birth', sanitize_text_field($_POST['date_of_birth']));
        update_post_meta($post_id, 'religion', sanitize_text_field($_POST['religion']));
        if (!empty($_FILES['student_profile_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_id = media_handle_upload('student_profile_photo', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'student_profile_photo', $attachment_id);
            }
        }
        update_post_meta($post_id, 'blood_group', sanitize_text_field($_POST['blood_group']));
        update_post_meta($post_id, 'height', sanitize_text_field($_POST['height']));
        update_post_meta($post_id, 'weight', sanitize_text_field($_POST['weight']));
        update_post_meta($post_id, 'current_address', sanitize_textarea_field($_POST['current_address']));
        update_post_meta($post_id, 'permanent_address', sanitize_textarea_field($_POST['permanent_address']));
        update_post_meta($post_id, 'roll_number', sanitize_text_field($_POST['roll_number']));
        update_post_meta($post_id, 'admission_date', sanitize_text_field($_POST['admission_date']));
        wp_send_json_success(['message' => 'Student added']);
    } else {
        wp_send_json_error(['message' => 'Failed to add student']);
    }
}

// Get Student
add_action('wp_ajax_su_p_get_student', 'su_p_get_student');
function su_p_get_student() {
    check_ajax_referer('su_p_student_nonce', 'nonce');
    $student_id = intval($_POST['student_id']);
    $post = get_post($student_id);
    if ($post && $post->post_type === 'students') {
        $student = [
            'ID' => $post->ID,
            'student_id' => get_post_meta($post->ID, 'student_id', true),
            'educational_center_id' => get_post_meta($post->ID, 'educational_center_id', true),
            'admission_number' => get_post_meta($post->ID, 'admission_number', true),
            'student_name' => get_post_meta($post->ID, 'student_name', true),
            'student_email' => get_post_meta($post->ID, 'student_email', true),
            'phone_number' => get_post_meta($post->ID, 'phone_number', true),
            'class_name' => get_post_meta($post->ID, 'class_name', true),
            'section' => get_post_meta($post->ID, 'section', true),
            'gender' => get_post_meta($post->ID, 'gender', true),
            'date_of_birth' => get_post_meta($post->ID, 'date_of_birth', true),
            'religion' => get_post_meta($post->ID, 'religion', true),
            'student_profile_photo_url' => wp_get_attachment_url(get_post_meta($post->ID, 'student_profile_photo', true)),
            'blood_group' => get_post_meta($post->ID, 'blood_group', true),
            'height' => get_post_meta($post->ID, 'height', true),
            'weight' => get_post_meta($post->ID, 'weight', true),
            'current_address' => get_post_meta($post->ID, 'current_address', true),
            'permanent_address' => get_post_meta($post->ID, 'permanent_address', true),
            'roll_number' => get_post_meta($post->ID, 'roll_number', true),
            'admission_date' => get_post_meta($post->ID, 'admission_date', true)
        ];
        wp_send_json_success($student);
    } else {
        wp_send_json_error(['message' => 'Student not found']);
    }
}

// Update Student
add_action('wp_ajax_su_p_update_student', 'su_p_update_student');
function su_p_update_student() {
    check_ajax_referer('su_p_student_nonce', 'nonce');
    $post_id = intval($_POST['student_post_id']);
    $student_id = sanitize_text_field($_POST['student_id']);
    $educational_center_id = sanitize_text_field($_POST['educational_center_id']);

    $args = [
        'post_type' => 'students',
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'student_id', 'value' => $student_id, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
        ],
        'posts_per_page' => 1,
        'exclude' => [$post_id]
    ];
    $existing = new WP_Query($args);
    // if ($existing->have_posts()) {
    //     wp_send_json_error(['message' => 'Student ID and Educational Center ID combination must be unique']);
    //     return;
    // }

    $updated = wp_update_post([
        'ID' => $post_id,
        'post_title' => sanitize_text_field($_POST['student_name'])
    ]);
    if ($updated && !is_wp_error($updated)) {
        update_post_meta($post_id, 'admission_number', sanitize_text_field($_POST['admission_number']));
        update_post_meta($post_id, 'student_name', sanitize_text_field($_POST['student_name']));
        update_post_meta($post_id, 'student_email', sanitize_email($_POST['student_email']));
        update_post_meta($post_id, 'phone_number', sanitize_text_field($_POST['phone_number']));
        update_post_meta($post_id, 'class_name', sanitize_text_field($_POST['class_name']));
        update_post_meta($post_id, 'section', sanitize_text_field($_POST['section']));
        update_post_meta($post_id, 'gender', sanitize_text_field($_POST['gender']));
        update_post_meta($post_id, 'date_of_birth', sanitize_text_field($_POST['date_of_birth']));
        update_post_meta($post_id, 'religion', sanitize_text_field($_POST['religion']));
        if (!empty($_FILES['student_profile_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_id = media_handle_upload('student_profile_photo', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'student_profile_photo', $attachment_id);
            }
        }
        update_post_meta($post_id, 'blood_group', sanitize_text_field($_POST['blood_group']));
        update_post_meta($post_id, 'height', sanitize_text_field($_POST['height']));
        update_post_meta($post_id, 'weight', sanitize_text_field($_POST['weight']));
        update_post_meta($post_id, 'current_address', sanitize_textarea_field($_POST['current_address']));
        update_post_meta($post_id, 'permanent_address', sanitize_textarea_field($_POST['permanent_address']));
        update_post_meta($post_id, 'roll_number', sanitize_text_field($_POST['roll_number']));
        update_post_meta($post_id, 'admission_date', sanitize_text_field($_POST['admission_date']));
        wp_send_json_success(['message' => 'Student updated']);
    } else {
        wp_send_json_error(['message' => 'Failed to update student']);
    }
}

// Delete Student
add_action('wp_ajax_su_p_delete_student', 'su_p_delete_student');
function su_p_delete_student() {
    check_ajax_referer('su_p_student_nonce', 'nonce');
    $student_id = intval($_POST['student_id']);
    if (wp_delete_post($student_id, true)) {
        wp_send_json_success(['message' => 'Student deleted']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete student']);
    }
}

//staff
function render_su_p_staff() {
    global $wpdb;
    ob_start();
    ?>
    <div class="edu-staff-container" style="margin-top: 80px;">
        <!-- Loader SVG -->
        <div id="edu-loader" class="edu-loader" style="display: none;">
            <div class="edu-loader-container">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
            </div>
        </div>
        <h2 class="edu-staff-title">Managing Staff</h2>
        <div class="edu-staff-actions">
            <button class="edu-button edu-button-primary" id="add-staff-btn">Add New Staff</button>
            <input type="text" id="staff-search" class="edu-search-input" placeholder="Search Staff..." style="margin-left: 20px; padding: 8px; width: 300px;">
            <select id="center-filter" class="edu-select" style="margin-left: 20px; padding: 8px;">
                <option value="">All Educational Centers</option>
                <?php
                $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
                foreach ($centers as $center) {
                    $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                    echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                }
                ?>
            </select>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="staff-per-page">Show:</label>
            <select id="staff-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <div class="export-tools" id="export-tools" style="margin-bottom: 10px;"></div>
            <table class="edu-table" id="staff-table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Edu Center ID</th>
                        <th>Edu Center Name</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Base Salary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="staff-table-body">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="edu-modal" id="add-staff-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-staff-modal">×</span>
            <h3>Add New Staff</h3>
            <form id="add-staff-form" class="edu-form" enctype="multipart/form-data">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-id">Staff ID</label>
                    <input type="text" class="edu-form-input" id="staff-id" name="staff_id" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="educational-center-id">Educational Center ID</label>
                    <select class="edu-form-input" id="educational-center-id" name="educational_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-name">Name</label>
                    <input type="text" class="edu-form-input" id="staff-name" name="name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-role">Role</label>
                    <select class="edu-form-input" id="staff-role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Accountant">Accountant</option>
                        <option value="Administrator">Administrator</option>
                        <option value="Librarian">Librarian</option>
                        <option value="Support Staff">Support Staff</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-salary">Base Salary (Monthly)</label>
                    <input type="number" class="edu-form-input" id="staff-salary" name="salary_base" step="0.01" min="0" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_staff_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="save-staff">Save Staff</button>
            </form>
            <div class="edu-form-message" id="add-staff-message"></div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div class="edu-modal" id="edit-staff-modal">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-staff-modal">×</span>
            <h3>Edit Staff</h3>
            <form id="edit-staff-form" class="edu-form" enctype="multipart/form-data">
                <input type="hidden" id="edit-staff-id-hidden" name="staff_id">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-staff-id">Staff ID</label>
                    <input type="text" class="edu-form-input" id="edit-staff-id" name="staff_id_display" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-educational-center-id">Educational Center ID</label>
                    <input type="text" class="edu-form-input" id="edit-educational-center-id" name="educational_center_id" readonly>                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-staff-name">Name</label>
                    <input type="text" class="edu-form-input" id="edit-staff-name" name="name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-staff-role">Role</label>
                    <select class="edu-form-input" id="edit-staff-role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Accountant">Accountant</option>
                        <option value="Administrator">Administrator</option>
                        <option value="Librarian">Librarian</option>
                        <option value="Support Staff">Support Staff</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-staff-salary">Base Salary (Monthly)</label>
                    <input type="number" class="edu-form-input" id="edit-staff-salary" name="salary_base" step="0.01" min="0" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_staff_nonce'); ?>">
                <button type="button" class="edu-button edu-button-primary" id="update-staff">Update Staff</button>
            </form>
            <div class="edu-form-message" id="edit-staff-message"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let centerFilter = '';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
            showLoader(); 
            $(modalId).css('display', 'block'); 
            setTimeout(hideLoader, 100); 
        }
        function closeModal(modalId) { 
            $(modalId).css('display', 'none'); 
            $(modalId + ' .edu-form-message').removeClass('edu-success edu-error').text(''); 
            hideLoader(); 
        }

        $('.edu-modal-close').on('click', function() {
            const modalId = '#' + $(this).data('modal');
            closeModal(modalId);
        });

        $(document).on('click', function(event) {
            if ($(event.target).hasClass('edu-modal')) {
                closeModal('#' + event.target.id);
            }
        });

        function getTableData() {
            const table = $('#staff-table')[0];
            if (!table) return [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim()).slice(0, -1); // Exclude Actions
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                const cells = Array.from(row.querySelectorAll('td')).slice(0, -1); // Exclude Actions
                return cells.map(td => td.textContent.trim());
            });
            return [headers, ...rows];
        }

        function exportToCSV() {
            const data = getTableData();
            const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `staff_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
        }

        function exportToExcel() {
            if (!window.XLSX) { console.error('XLSX library not loaded'); return; }
            const data = getTableData();
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Staff');
            XLSX.writeFile(wb, `staff_${new Date().toISOString().slice(0,10)}.xlsx`);
        }

        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'mm', format: 'a4' });
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';
            const pageWidth = doc.internal.pageSize.width;
            const pageHeight = doc.internal.pageSize.height;
            const margin = 10;
            const borderColor = [70, 131, 180];

            doc.setDrawColor(...borderColor);
            doc.setLineWidth(1);
            doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

            if (instituteLogo) {
                try {
                    doc.addImage(instituteLogo, 'JPEG', (pageWidth - 24) / 2, 15, 24, 24);
                } catch (e) {
                    console.log('Logo loading failed:', e);
                }
            }
            doc.setFontSize(18);
            doc.setTextColor(...borderColor);
            doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
            doc.setFontSize(12);
            doc.setTextColor(102);
            doc.text('Staff List', pageWidth / 2, 55, { align: 'center' });
            doc.setDrawColor(...borderColor);
            doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

            const details = [
                ['Date', new Date().toLocaleDateString()],
                ['Total Staff', String(data.length - 1)]
            ];
            let y = 70;
            details.forEach(([label, value]) => {
                doc.setFillColor(245, 245, 245);
                doc.rect(margin + 5, y, 50, 6, 'F');
                doc.setTextColor(...borderColor);
                doc.setFont('helvetica', 'bold');
                doc.text(label, margin + 7, y + 4);
                doc.setTextColor(51);
                doc.setFont('helvetica', 'normal');
                doc.text(String(value), margin + 60, y + 4);
                y += 6;
            });

            if (typeof doc.autoTable === 'function' && data.length > 1) {
                doc.autoTable({
                    startY: y + 10,
                    head: [data[0]],
                    body: data.slice(1),
                    theme: 'striped',
                    styles: { fontSize: 11, cellPadding: 2, overflow: 'linebreak', halign: 'center', textColor: [51, 51, 51] },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [249, 249, 249] }
                });

                const finalY = doc.lastAutoTable.finalY || y + 10;
                doc.setFontSize(9);
                doc.setTextColor(102);
                doc.text(`This is an Online Generated Staff List issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
                doc.text(`Generated on ${new Date().toISOString().slice(0,10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
                doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

                doc.save(`staff_${new Date().toISOString().slice(0,10)}.pdf`);
            } else {
                console.error('jsPDF autoTable plugin not loaded or no data');
                alert('PDF generation failed');
            }
        }

        function copyToClipboard() {
            const data = getTableData();
            const text = data.map(row => row.join('\t')).join('\n');
            navigator.clipboard.writeText(text).then(() => alert('Staff copied to clipboard!'));
        }

        function printStaff() {
            const printWindow = window.open('', '_blank');
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';

            printWindow.document.write(`
                <html>
                <head>
                    <title>Staff List</title>
                    <style>
                        @media print {
                            body { font-family: Helvetica, sans-serif; margin: 10mm; width: 190mm; }
                            .page { border: 4px solid #4683b4; padding: 5mm; box-sizing: border-box; width: 100%; max-width: 190mm; }
                            .header { text-align: center; border-bottom: 2px solid #4683b4; margin-bottom: 10mm; }
                            .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                            .header h1 { font-size: 18pt; color: #4683b4; margin: 0; text-transform: uppercase; }
                            .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                            table { width: 100%; max-width: 100%; border-collapse: collapse; margin: 10mm 0; table-layout: fixed; }
                            th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; word-wrap: break-word; font-size: 10pt; }
                            th { background: #4683b4; color: white; font-weight: bold; }
                            tr:nth-child(even) { background: #f9f9f9; }
                            .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                            @page { size: A4; margin: 10mm; }
                        }
                    </style>
                </head>
                <body>
                    <div class="page">
                        <div class="header">
                            ${instituteLogo ? `<img src="${instituteLogo}" alt="Logo" onerror="this.style.display='none';this.nextSibling.style.display='block';"><p style="display:none;">No logo available</p>` : '<p>No logo available</p>'}
                            <h1>${instituteName.toUpperCase()}</h1>
                            <p class="subtitle">Staff List</p>
                        </div>
                        <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                        <p><strong>Total Staff:</strong> ${data.length - 1}</p>
                        <table>
                            <thead>
                                <tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr>
                            </thead>
                            <tbody>
                                ${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                            </tbody>
                        </table>
                        <div class="footer">
                            <p>This is an Online Generated Staff List issued by ${instituteName}</p>
                            <p>Generated on ${new Date().toISOString().slice(0,10)}</p>
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

        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
            `);
            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
            tools.find('.export-copy').on('click', copyToClipboard);
            tools.find('.export-print').on('click', printStaff);
        }

        function loadStaff(page, limit, query, center) {
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_fetch_staff',
                    page: page,
                    per_page: limit,
                    search: query,
                    center_filter: center,
                    nonce: '<?php echo wp_create_nonce('su_p_staff_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const staff = response.data.staff;
                        const total = response.data.total;
                        let html = '';
                        staff.forEach(member => {
                            html += `
                                <tr data-staff-id="${member.staff_id}">
                                    <td>${member.staff_id || 'N/A'}</td>
                                    <td>${member.educational_center_id || 'N/A'}</td>
                                    <td>${member.center_name || 'N/A'}</td>
                                    <td>${member.name || 'N/A'}</td>
                                    <td>${member.role || 'N/A'}</td>
                                    <td>${member.salary_base || '0.00'}</td>
                                    <td>
                                        <button class="edu-button edu-button-edit edit-staff" data-staff-id="${member.staff_id}">Edit</button>
                                        <button class="edu-button edu-button-delete delete-staff" data-staff-id="${member.staff_id}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#staff-table-body').html(html);
                        setupExportButtons();

                        const totalPages = Math.ceil(total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages}`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                    } else {
                        $('#staff-table-body').html('<tr><td colspan="7">No staff found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#staff-table-body').html('<tr><td colspan="7">Error loading staff: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        // Initial load
        loadStaff(currentPage, perPage, searchQuery, centerFilter);

        // Search and Filter
        $('#staff-search').on('input', function() {
            searchQuery = $(this).val();
            currentPage = 1;
            loadStaff(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadStaff(currentPage, perPage, searchQuery, centerFilter);
        });

        // Pagination
        $('#staff-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadStaff(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadStaff(currentPage, perPage, searchQuery, centerFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadStaff(currentPage, perPage, searchQuery, centerFilter);
        });

        // Add Staff
        $('#add-staff-btn').on('click', function() { 
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_generate_staff_id',
                    nonce: '<?php echo wp_create_nonce('su_p_staff_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#staff-id').val(response.data.staff_id);
                        openModal('#add-staff-modal');
                    } else {
                        alert('Error generating staff ID: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error generating staff ID: ' + error);
                }
            });
        });

        $('#save-staff').on('click', function() {
            const formData = new FormData($('#add-staff-form')[0]);
            formData.append('action', 'su_p_add_staff');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#add-staff-message').addClass('edu-success').text('Staff added successfully!');
                        setTimeout(() => {
                            closeModal('#add-staff-modal');
                            loadStaff(currentPage, perPage, searchQuery, centerFilter);
                        }, 1000);
                    } else {
                        $('#add-staff-message').addClass('edu-error').text('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-staff-message').addClass('edu-error').text('Error adding staff: ' + error);
                }
            });
        });

        // Edit Staff
        $(document).on('click', '.edit-staff', function() {
    const staffId = $(this).data('staff-id');
    showLoader();
    $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        method: 'POST',
        data: {
            action: 'su_p_get_staff',
            staff_id: staffId,
            nonce: '<?php echo wp_create_nonce('su_p_staff_nonce'); ?>'
        },
        success: function(response) {
            hideLoader();
            if (response.success) {
                const staff = response.data;
                console.log('Staff Data:', staff); // Debug log
                $('#edit-staff-id-hidden').val(staff.staff_id || '');
                $('#edit-staff-id').val(staff.staff_id || '');
                $('#edit-educational-center-id').val(staff.educational_center_id || 'N/A'); // Fallback to 'N/A'
                $('#edit-staff-name').val(staff.name || '');
                $('#edit-staff-role').val(staff.role || '');
                $('#edit-staff-salary').val(staff.salary_base || '');
                openModal('#edit-staff-modal');
            } else {
                alert('Error fetching staff: ' + response.data.message);
            }
        },
        error: function(xhr, status, error) {
            hideLoader();
            alert('Error fetching staff: ' + error);
        }
    });
    });

        $('#update-staff').on('click', function() {
            const formData = new FormData($('#edit-staff-form')[0]);
            formData.append('action', 'su_p_update_staff');
            formData.set('staff_id', $('#edit-staff-id-hidden').val()); // Ensure correct staff_id
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#edit-staff-message').addClass('edu-success').text('Staff updated successfully!');
                        setTimeout(() => {
                            closeModal('#edit-staff-modal');
                            loadStaff(currentPage, perPage, searchQuery, centerFilter);
                        }, 1000);
                    } else {
                        $('#edit-staff-message').addClass('edu-error').text('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-staff-message').addClass('edu-error').text('Error updating staff: ' + error);
                }
            });
        });

        // Delete Staff
        $(document).on('click', '.delete-staff', function() {
            if (!confirm('Are you sure you want to delete this staff member?')) return;
            const staffId = $(this).data('staff-id');
            showLoader();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'su_p_delete_staff',
                    staff_id: staffId,
                    nonce: '<?php echo wp_create_nonce('su_p_staff_nonce'); ?>'
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        loadStaff(currentPage, perPage, searchQuery, centerFilter);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error deleting staff: ' + error);
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
// Generate Unique Staff ID
add_action('wp_ajax_su_p_generate_staff_id', 'su_p_generate_staff_id');
function su_p_generate_staff_id() {
    check_ajax_referer('su_p_staff_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff';
    $education_center_id = sanitize_text_field($_POST['educational_center_id'] ?? 'default_center'); // Fallback if not provided
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
            wp_send_json_success(['staff_id' => $staff_id]);
            return;
        }
        usleep(10000);
    }
    wp_send_json_error(['message' => 'Unable to generate a unique Staff ID after multiple attempts']);
}

// Fetch Staff
add_action('wp_ajax_su_p_fetch_staff', 'su_p_fetch_staff');
function su_p_fetch_staff() {
    check_ajax_referer('su_p_staff_nonce', 'nonce');
    global $wpdb;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $center_filter = isset($_POST['center_filter']) ? sanitize_text_field($_POST['center_filter']) : '';
    $offset = ($page - 1) * $per_page;
    $table_name = $wpdb->prefix . 'staff';

    $where = "WHERE 1=1";
    if ($search) {
        $where .= $wpdb->prepare(" AND (staff_id LIKE %s OR name LIKE %s OR role LIKE %s)", "%$search%", "%$search%", "%$search%");
    }
    if ($center_filter) {
        $where .= $wpdb->prepare(" AND education_center_id = %s", $center_filter);
    }

    $staff = $wpdb->get_results(
        "SELECT * FROM $table_name $where ORDER BY staff_id LIMIT $offset, $per_page",
        ARRAY_A
    );
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");

    $staff_data = [];
    foreach ($staff as $member) {
        $center = get_posts(['post_type' => 'educational-center', 'meta_key' => 'educational_center_id', 'meta_value' => $member['education_center_id'], 'posts_per_page' => 1])[0] ?? null;
        $staff_data[] = [
            'staff_id' => $member['staff_id'],
            'educational_center_id' => $member['education_center_id'],
            'center_name' => $center ? $center->post_title : 'N/A',
            'name' => $member['name'],
            'role' => $member['role'],
            'salary_base' => number_format($member['salary_base'], 2)
        ];
    }

    wp_send_json_success(['staff' => $staff_data, 'total' => $total]);
}

// Add Staff
add_action('wp_ajax_su_p_add_staff', 'su_p_add_staff');
function su_p_add_staff() {
    check_ajax_referer('su_p_staff_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff';

    $staff_id = sanitize_text_field($_POST['staff_id']);
    $educational_center_id = sanitize_text_field($_POST['educational_center_id']);
    $name = sanitize_text_field($_POST['name']);
    $role = sanitize_text_field($_POST['role']);
    $salary_base = floatval($_POST['salary_base']);

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE staff_id = %s OR (name = %s AND role = %s AND education_center_id = %s)",
        $staff_id, $name, $role, $educational_center_id
    ));

    if ($exists > 0) {
        wp_send_json_error(['message' => 'Staff ID or Name/Role combination already exists']);
        return;
    }

    $result = $wpdb->insert(
        $table_name,
        [
            'staff_id' => $staff_id,
            'education_center_id' => $educational_center_id,
            'name' => $name,
            'role' => $role,
            'salary_base' => $salary_base
        ],
        ['%s', '%s', '%s', '%s', '%f']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to add staff: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success(['message' => 'Staff added successfully']);
    }
}

// Get Staff
add_action('wp_ajax_su_p_get_staff', 'su_p_get_staff');
function su_p_get_staff() {
    check_ajax_referer('su_p_staff_nonce', 'nonce');
    global $wpdb;
    $staff_id = sanitize_text_field($_POST['staff_id']);
    $table_name = $wpdb->prefix . 'staff';

    $staff = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE staff_id = %s",
        $staff_id
    ), ARRAY_A);

    if ($staff) {
        // Ensure all expected keys are present, even if null
        $staff_data = [
            'staff_id' => $staff['staff_id'],
            'educational_center_id' => $staff['education_center_id'] ?? 'N/A',
            'name' => $staff['name'],
            'role' => $staff['role'],
            'salary_base' => $staff['salary_base']
        ];
        wp_send_json_success($staff_data);
    } else {
        wp_send_json_error(['message' => 'Staff not found']);
    }
}

// Update Staff
add_action('wp_ajax_su_p_update_staff', 'su_p_update_staff');
function su_p_update_staff() {
    check_ajax_referer('su_p_staff_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff';

    $staff_id = sanitize_text_field($_POST['staff_id']);
    $educational_center_id = sanitize_text_field($_POST['educational_center_id']);
    $name = sanitize_text_field($_POST['name']);
    $role = sanitize_text_field($_POST['role']);
    $salary_base = floatval($_POST['salary_base']);

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE name = %s AND role = %s AND education_center_id = %s AND staff_id != %s",
        $name, $role, $educational_center_id, $staff_id
    ));

    // if ($exists > 0) {
    //     wp_send_json_error(['message' => 'Another staff member with this name and role already exists']);
    //     return;
    // }

    $result = $wpdb->update(
        $table_name,
        [
            'name' => $name,
            'role' => $role,
            'salary_base' => $salary_base
        ],
        ['staff_id' => $staff_id, 'education_center_id' => $educational_center_id],
        ['%s', '%s', '%f'],
        ['%s', '%s']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update staff: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success(['message' => 'Staff updated successfully']);
    }
}

// Delete Staff
add_action('wp_ajax_su_p_delete_staff', 'su_p_delete_staff');
function su_p_delete_staff() {
    check_ajax_referer('su_p_staff_nonce', 'nonce');
    global $wpdb;
    $staff_id = sanitize_text_field($_POST['staff_id']);
    $table_name = $wpdb->prefix . 'staff';

    $result = $wpdb->delete($table_name, ['staff_id' => $staff_id], ['%s']);

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to delete staff: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success(['message' => 'Staff deleted successfully']);
    }
}

//attendance
// Enqueue Scripts and Styles
function su_p_enqueue_student_attendance_scripts() {
    // if (!is_admin()) {
        // Enqueue styles (uncomment if you still want to use an external CSS file)
        // wp_enqueue_style('su-p-attendance-css', plugin_dir_url(__FILE__) . 'css/su-p-attendance.css', [], '1.0');

        // Enqueue external libraries for export functionality
        wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true);
        wp_enqueue_script('jspdf-autotable', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js', ['jspdf'], '3.5.23', true);
        wp_enqueue_script('xlsx', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', [], '0.18.5', true);

        // Note: We won't use wp_localize_script since the JS is inline now
    // }
}
add_action('wp_enqueue_scripts', 'su_p_enqueue_student_attendance_scripts');
// Main Dashboard Function
function render_su_p_student_attendance() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $student_table = $wpdb->prefix . 'students';
    $subjects_table = $wpdb->prefix . 'subjects';
    ob_start();

    $subjects = $wpdb->get_results("SELECT subject_id, subject_name FROM $subjects_table ORDER BY subject_name");
    ?>
    <!-- HTML remains unchanged from the previous version -->
    <div class="attendance-main-wrapper">
        <div class="attendance-content-wrapper">
            <div id="edu-loader" class="edu-loader" style="display: none;">
                <div class="edu-loader-container">
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                </div>
            </div>
            <div class="attendance-management">
                <h2>Student Attendance</h2>
                <div class="search-filters">
                    <input type="text" id="student-id-search" placeholder="Search by Student ID...">
                    <input type="text" id="student-name-search" placeholder="Search by Student Name...">
                    <select id="center-filter">
                        <option value="">All Educational Centers</option>
                        <?php
                        $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                    <select id="class-filter">
                        <option value="">All Classes</option>
                        <?php
                        $classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_name ORDER BY class");
                        foreach ($classes as $class) {
                            echo "<option value='" . esc_attr($class) . "'>" . esc_html($class) . "</option>";
                        }
                        ?>
                    </select>
                    <select id="section-filter">
                        <option value="">All Sections</option>
                        <?php
                        $sections = $wpdb->get_col("SELECT DISTINCT section FROM $table_name ORDER BY section");
                        foreach ($sections as $section) {
                            echo "<option value='" . esc_attr($section) . "'>" . esc_html($section) . "</option>";
                        }
                        ?>
                    </select>
                    <select id="month-filter">
                        <option value="">Select Month</option>
                        <?php
                        $month_names = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        $current_month = date('n');
                        $current_year = date('Y');
                        $dates = $wpdb->get_results("SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month FROM $table_name ORDER BY year DESC, month DESC");
                        foreach ($dates as $date) {
                            $selected = ($date->month == $current_month && $date->year == $current_year) ? 'selected' : '';
                            echo "<option value='{$date->month}' data-year='{$date->year}' $selected>" . esc_html($month_names[$date->month] . ' ' . $date->year) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="actions">
                    <button id="add-attendance-btn">Add Attendance</button>
                    <label for="attendance-per-page">Show:</label>
                    <select id="attendance-per-page">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <button id="prev-page" disabled>Previous</button>
                    <span id="page-info"></span>
                    <button id="next-page">Next</button>
                    <button id="refresh-data">Refresh</button>
                </div>
                <div class="attendance-table-wrapper">
                    <div class="actions" id="export-tools"></div>
                    <table id="attendance-table">
                        <thead id="attendance-table-head"></thead>
                        <tbody id="attendance-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="edu-modal" id="add-attendance-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-attendance-modal">×</span>
            <h3>Add Student Attendance</h3>
            <form id="add-attendance-form">
                <div class="search-filters">
                    <label for="add-center-id">Education Center</label>
                    <select id="add-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-student-id">Student</label>
                    <select id="add-student-id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php
                        $students = $wpdb->get_results("SELECT student_id, student_name, class, section, education_center_id FROM $student_table ORDER BY student_name");
                        foreach ($students as $student) {
                            echo "<option value='" . esc_attr($student->student_id) . "' data-center='" . esc_attr($student->education_center_id) . "' data-class='" . esc_attr($student->class) . "' data-section='" . esc_attr($student->section) . "'>" . esc_html($student->student_name . " (" . $student->student_id . ")") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-class">Class</label>
                    <input type="text" id="add-class" name="class" readonly required>
                </div>
                <div class="search-filters">
                    <label for="add-section">Section</label>
                    <input type="text" id="add-section" name="section" readonly required>
                </div>
                <div class="search-filters">
                    <label for="add-attendance-date">Date</label>
                    <input type="date" id="add-attendance-date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="search-filters">
                    <label for="add-status">Status</label>
                    <select id="add-status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="Full Day">Full Day</option>
                        <option value="Holiday">Holiday</option>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-subject">Subject</label>
                    <select id="add-subject" name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php
                        foreach ($subjects as $subject) {
                            echo "<option value='" . esc_attr($subject->subject_id) . "'>" . esc_html($subject->subject_name) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-teacher-id">Teacher ID</label>
                    <input type="text" id="add-teacher-id" name="teacher_id" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>">
                <button type="button" id="save-attendance">Save Attendance</button>
            </form>
            <div id="add-attendance-message" class="message"></div>
        </div>
    </div>

    <!-- Date Selection Modal for Edit -->
    <div class="edu-modal" id="edit-date-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-date-modal">×</span>
            <h3>Select Attendance Date to Edit</h3>
            <form id="edit-date-form">
                <div class="search-filters">
                    <label for="edit-student-id-display">Student</label>
                    <input type="text" id="edit-student-id-display" readonly>
                    <input type="hidden" id="edit-student-id" name="student_id">
                </div>
                <div class="search-filters">
                    <label for="edit-date-select">Select Date</label>
                    <select id="edit-date-select" name="date" required>
                        <option value="">Choose a date</option>
                    </select>
                </div>
                <button type="button" id="proceed-edit">Proceed to Edit</button>
            </form>
            <div id="edit-date-message" class="message"></div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="edu-modal" id="edit-attendance-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-attendance-modal">×</span>
            <h3>Edit Student Attendance</h3>
            <form id="edit-attendance-form">
                <input type="hidden" id="edit-attendance-id" name="sa_id">
                <div class="search-filters">
                    <label for="edit-center-id">Education Center ID</label>
                    <input type="text" id="edit-center-id" name="education_center_id" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-student-id-display">Student</label>
                    <input type="text" id="edit-student-id-display" name="student_id_display" readonly>
                    <input type="hidden" id="edit-student-id-hidden" name="student_id">
                </div>
                <div class="search-filters">
                    <label for="edit-class">Class</label>
                    <input type="text" id="edit-class" name="class" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-section">Section</label>
                    <input type="text" id="edit-section" name="section" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-attendance-date">Date</label>
                    <input type="date" id="edit-attendance-date" name="date" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-status">Status</label>
                    <select id="edit-status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="Full Day">Full Day</option>
                        <option value="Holiday">Holiday</option>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="edit-subject">Subject</label>
                    <select id="edit-subject" name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php
                        foreach ($subjects as $subject) {
                            echo "<option value='" . esc_attr($subject->subject_id) . "'>" . esc_html($subject->subject_name) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="edit-teacher-id">Teacher ID</label>
                    <input type="text" id="edit-teacher-id" name="teacher_id" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>">
                <button type="button" id="update-attendance">Update Attendance</button>
            </form>
            <div id="edit-attendance-message" class="message"></div>
        </div>
    </div>

    <!-- JavaScript remains unchanged from the previous version -->
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>';

        let currentPage = 1;
        let perPage = 10;
        let studentIdSearch = '';
        let studentNameSearch = '';
        let centerFilter = '';
        let classFilter = '';
        let sectionFilter = '';
        let monthFilter = '<?php echo date('n'); ?>';
        let yearFilter = '<?php echo date('Y'); ?>';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
            showLoader(); 
            $(modalId).css('display', 'flex'); 
            setTimeout(hideLoader, 100); 
            clearMessages(modalId);
        }
        function closeModal(modalId) { 
            $(modalId).css('display', 'none'); 
            clearMessages(modalId); 
            hideLoader(); 
        }
        function clearMessages(modalId) {
            $(modalId + ' .message').css({'background': '', 'color': ''}).text('');
        }

        $('.edu-modal-close').on('click', function() {
            closeModal('#' + $(this).data('modal'));
        });

        $(document).on('click', function(event) {
            if ($(event.target).hasClass('edu-modal')) {
                closeModal('#' + event.target.id);
            }
        });

        function getTableData() {
            const table = $('#attendance-table')[0];
            if (!table) return [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => 
                Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim())
            );
            return [headers, ...rows];
        }

        function exportToCSV() {
            showLoader();
            const data = getTableData();
            const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `student_attendance_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            hideLoader();
        }

        function exportToExcel() {
            if (!window.XLSX) { console.error('XLSX library not loaded'); return; }
            showLoader();
            const data = getTableData();
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Student Attendance');
            XLSX.writeFile(wb, `student_attendance_${new Date().toISOString().slice(0,10)}.xlsx`);
            hideLoader();
        }

        function generatePDF() {
            showLoader();
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';
            const pageWidth = doc.internal.pageSize.width;
            const pageHeight = doc.internal.pageSize.height;
            const margin = 10;
            const borderColor = [70, 131, 180];
            const filters = `Filters: Student ID: ${studentIdSearch || 'All'}, Name: ${studentNameSearch || 'All'}, Center: ${centerFilter || 'All'}, Class: ${classFilter || 'All'}, Section: ${sectionFilter || 'All'}, Month: ${monthFilter ? month_names[parseInt(monthFilter)] : 'All'} ${yearFilter}`;

            doc.setDrawColor(...borderColor);
            doc.setLineWidth(1);
            doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);
            if (instituteLogo) {
                try {
                    doc.addImage(instituteLogo, 'JPEG', margin + 5, margin + 5, 20, 20);
                } catch (e) { console.log('Logo loading failed:', e); }
            }
            doc.setFontSize(16);
            doc.setTextColor(...borderColor);
            doc.text(instituteName.toUpperCase(), pageWidth / 2, margin + 15, { align: 'center' });
            doc.setFontSize(12);
            doc.setTextColor(0);
            doc.text('Student Attendance Report', pageWidth / 2, margin + 25, { align: 'center' });
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(filters, margin + 5, margin + 35, { maxWidth: pageWidth - 2 * margin - 10 });

            if (typeof doc.autoTable === 'function' && data.length > 1) {
                doc.autoTable({
                    startY: margin + 40,
                    head: [data[0]],
                    body: data.slice(1),
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak', halign: 'center' },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [245, 245, 245] },
                    columnStyles: {
                        0: { cellWidth: 30 },
                        1: { cellWidth: 20 },
                        2: { cellWidth: 20 },
                        3: { cellWidth: 10 },
                        4: { cellWidth: 10 },
                        5: { cellWidth: 10 },
                        6: { cellWidth: 10 },
                        7: { cellWidth: 10 },
                        8: { cellWidth: 15 },
                    },
                    didDrawPage: function(data) {
                        const pageNumber = doc.internal.getNumberOfPages();
                        doc.setFontSize(8);
                        doc.text(`Page ${pageNumber}`, pageWidth - margin - 10, pageHeight - margin - 5, { align: 'right' });
                    }
                });
                const finalY = doc.lastAutoTable.finalY || margin + 40;
                doc.setFontSize(9);
                doc.setTextColor(100);
                doc.text(`Generated on ${new Date().toISOString().slice(0,10)}`, pageWidth / 2, finalY + 10, { align: 'center' });
                doc.save(`student_attendance_${new Date().toISOString().slice(0,10)}.pdf`);
            } else {
                console.error('jsPDF autoTable plugin not loaded or no data');
                alert('PDF generation failed');
            }
            hideLoader();
        }

        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            `);
            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
        }

        function loadAttendance(page, limit, sid, sname, center, classFilter, sectionFilter, month, year, showLoading = true) {
            if (showLoading) showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_student_attendance',
                    page: page,
                    per_page: limit,
                    search_student_id: sid,
                    search_student_name: sname,
                    center_filter: center,
                    class_filter: classFilter,
                    section_filter: sectionFilter,
                    month: month,
                    year: year,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (showLoading) hideLoader();
                    if (response.success) {
                        $('#attendance-table-head').html(response.data.table_head);
                        $('#attendance-table-body').html(response.data.attendance);
                        const totalPages = Math.ceil(response.data.total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages} (Total Records: ${response.data.total})`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                        setupExportButtons();
                    } else {
                        $('#attendance-table-body').html('<tr><td colspan="10">No attendance found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    if (showLoading) hideLoader();
                    $('#attendance-table-body').html('<tr><td colspan="10">Error loading attendance: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);

        $('#student-id-search').on('input', debounce(function() {
            studentIdSearch = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        }, 500));

        $('#student-name-search').on('input', debounce(function() {
            studentNameSearch = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        }, 500));

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#class-filter').on('change', function() {
            classFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#section-filter').on('change', function() {
            sectionFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#month-filter').on('change', function() {
            monthFilter = $(this).val();
            yearFilter = $(this).find('option:selected').data('year') || '<?php echo date('Y'); ?>';
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#attendance-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#refresh-data').on('click', function() {
            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter);
        });

        $('#add-attendance-btn').on('click', function() {
            openModal('#add-attendance-modal');
        });

        $('#add-student-id').on('change', function() {
            const $option = $(this).find('option:selected');
            $('#add-center-id').val($option.data('center'));
            $('#add-class').val($option.data('class'));
            $('#add-section').val($option.data('section'));
        });

        $('#save-attendance').on('click', function() {
    const formData = new FormData($('#add-attendance-form')[0]);
    formData.append('action', 'su_p_add_staff_attendance');
    showLoader();
    $.ajax({
        url: ajaxUrl, // Ensure this is defined (e.g., via wp_localize_script)
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoader();
            const $message = $('#add-attendance-message');
            if (response.success) {
                $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                setTimeout(() => {
                    closeModal('#add-attendance-modal');
                    loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter, false);
                }, 2000);
            } else {
                $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                setTimeout(() => clearMessages('#add-attendance-modal'), 3000);
            }
        },
        error: function(xhr, status, error) {
            hideLoader();
            $('#add-attendance-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error adding attendance: ' + error);
            setTimeout(() => clearMessages('#add-attendance-modal'), 3000);
        }
    });
});

        $(document).on('click', '.edit-attendance', function() {
            const studentId = $(this).data('student-id');
            const studentName = $(this).closest('tr').find('td:first').text();
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_get_student_attendance_dates',
                    student_id: studentId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#edit-student-id').val(studentId);
                        $('#edit-student-id-display').val(studentName);
                        const $dateSelect = $('#edit-date-select');
                        $dateSelect.empty().append('<option value="">Choose a date</option>');
                        response.data.forEach(date => {
                            $dateSelect.append(`<option value="${date.date}">${date.date}</option>`);
                        });
                        openModal('#edit-date-modal');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error fetching attendance dates: ' + error);
                }
            });
        });

        $('#proceed-edit').on('click', function() {
    const teacherId = $('#edit-teacher-id').val();
    const date = $('#edit-date-select').val();
    if (!date) {
        $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Please select a date.');
        setTimeout(() => clearMessages('#edit-date-modal'), 3000);
        return;
    }
    showLoader();
    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: {
            action: 'su_p_get_teacher_attendance',
            teacher_id: teacherId,
            date: date,
            nonce: ajaxNonce
        },
        success: function(response) {
            hideLoader();
            if (response.success) {
                const record = response.data;
                console.log('Edit Attendance Data:', record); // Debug: Check the response data
                $('#edit-attendance-id').val(record.ta_id || '');
                $('#edit-center-id').val(record.education_center_id || '');
                // Explicitly set teacher display field with name and ID
                const teacherDisplay = (record.teacher_name && record.teacher_id) 
                    ? `${record.teacher_name} (${record.teacher_id})` 
                    : record.teacher_name || record.teacher_id || 'Unknown Teacher';
                $('#edit-teacher-id-display').val(teacherDisplay);
                $('#edit-teacher-id-hidden').val(record.teacher_id || '');
                $('#edit-department').val(record.department_name || 'N/A');
                $('#edit-attendance-date').val(record.date || '');
                $('#edit-status').val(record.status || 'Present');
                closeModal('#edit-date-modal');
                openModal('#edit-attendance-modal');
            } else {
                $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data || 'Unknown error'));
                setTimeout(() => clearMessages('#edit-date-modal'), 3000);
            }
        },
        error: function(xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error, xhr.responseText); // Debug: Log error details
            $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error fetching attendance: ' + error);
            setTimeout(() => clearMessages('#edit-date-modal'), 3000);
        }
    });
});
        $('#update-attendance').on('click', function() {
            const formData = new FormData($('#edit-attendance-form')[0]);
            formData.append('action', 'su_p_update_student_attendance');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#edit-attendance-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#edit-attendance-modal');
                            loadAttendance(currentPage, perPage, studentIdSearch, studentNameSearch, centerFilter, classFilter, sectionFilter, monthFilter, yearFilter, false);
                        }, 2000);
                        setTimeout(() => clearMessages('#edit-attendance-modal'), 1500);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data.message || 'Unknown error'));
                        setTimeout(() => clearMessages('#edit-attendance-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-attendance-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error updating attendance: ' + error);
                    setTimeout(() => clearMessages('#edit-attendance-modal'), 3000);
                }
            });
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        const month_names = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    });
    </script>
    <?php
    return ob_get_clean();
}
// AJAX Handler: Fetch Attendance Data
add_action('wp_ajax_su_p_fetch_student_attendance', 'su_p_fetch_student_attendance');
function su_p_fetch_student_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $offset = ($page - 1) * $per_page;
    $student_id = sanitize_text_field($_POST['search_student_id'] ?? '');
    $student_name = sanitize_text_field($_POST['search_student_name'] ?? '');
    $center_filter = sanitize_text_field($_POST['center_filter'] ?? '');
    $class_filter = sanitize_text_field($_POST['class_filter'] ?? '');
    $section_filter = sanitize_text_field($_POST['section_filter'] ?? '');
    $month = isset($_POST['month']) ? intval($_POST['month']) : 0;
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;

    $query = "SELECT sa.*, COUNT(*) OVER() as total FROM $table_name sa WHERE 1=1";
    $count_query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
    $args = [];

    if ($student_id) {
        $query .= " AND sa.student_id = %s";
        $count_query .= " AND student_id = %s";
        $args[] = $student_id;
    }
    if ($student_name) {
        $query .= " AND sa.student_name LIKE %s";
        $count_query .= " AND student_name LIKE %s";
        $args[] = '%' . $wpdb->esc_like($student_name) . '%';
    }
    if ($center_filter) {
        $query .= " AND sa.education_center_id = %s";
        $count_query .= " AND education_center_id = %s";
        $args[] = $center_filter;
    }
    if ($class_filter) {
        $query .= " AND sa.class = %s";
        $count_query .= " AND class = %s";
        $args[] = $class_filter;
    }
    if ($section_filter) {
        $query .= " AND sa.section = %s";
        $count_query .= " AND section = %s";
        $args[] = $section_filter;
    }
    if ($month && $year) {
        $query .= " AND YEAR(sa.date) = %d AND MONTH(sa.date) = %d";
        $count_query .= " AND YEAR(date) = %d AND MONTH(date) = %d";
        $args[] = $year;
        $args[] = $month;
    }

    $query .= " ORDER BY sa.date DESC LIMIT %d OFFSET %d";
    $args[] = $per_page;
    $args[] = $offset;

    $results = $wpdb->get_results($wpdb->prepare($query, $args));
    $total = $results ? $results[0]->total : $wpdb->get_var($wpdb->prepare($count_query, array_slice($args, 0, -2)));

    $days_in_month = ($month && $year) ? cal_days_in_month(CAL_GREGORIAN, $month, $year) : 0;
    $head_html = "<tr><th>Student Name</th><th>Student ID</th><th>Edu Center ID</th><th>Class</th><th>Section</th><th>Date</th><th>Status</th><th>Subject</th><th>Teacher ID</th><th>Actions</th></tr>";
    if ($month && $year) {
        $head_html = "<tr><th>Student Name</th><th>Student ID</th><th>Edu Center ID</th><th>Class</th><th>Section</th><th>P</th><th>L</th><th>A</th><th>F</th><th>H</th><th>%</th>";
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $day_name = date('D', strtotime($date));
            $head_html .= "<th>$day<br>$day_name</th>";
        }
        $head_html .= "<th>Actions</th></tr>";
    }

    $body_html = '';
    if ($results) {
        if ($month && $year) {
            $student_data = [];
            foreach ($results as $row) {
                $key = $row->student_id;
                if (!isset($student_data[$key])) {
                    $student_data[$key] = [
                        'name' => $row->student_name,
                        'edu_center' => $row->education_center_id,
                        'class' => $row->class,
                        'section' => $row->section,
                        'attendance' => array_fill(1, $days_in_month, ''),
                        'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0],
                        'last_date' => $row->date,
                    ];
                }
                $day = (int)date('j', strtotime($row->date));
                $status_short = '';
                switch ($row->status) {
                    case 'Present': $status_short = 'P'; $student_data[$key]['counts']['P']++; break;
                    case 'Late': $status_short = 'L'; $student_data[$key]['counts']['L']++; break;
                    case 'Absent': $status_short = 'A'; $student_data[$key]['counts']['A']++; break;
                    case 'Full Day': $status_short = 'F'; $student_data[$key]['counts']['F']++; break;
                    case 'Holiday': $status_short = 'H'; $student_data[$key]['counts']['H']++; break;
                }
                $student_data[$key]['attendance'][$day] = $status_short;
            }
            foreach ($student_data as $student_id => $data) {
                $total_days = $days_in_month - $data['counts']['H'];
                $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0;
                $body_html .= "<tr data-student-id='$student_id'>
                    <td>" . esc_html($data['name']) . "</td>
                    <td>" . esc_html($student_id) . "</td>
                    <td>" . esc_html($data['edu_center']) . "</td>
                    <td>" . esc_html($data['class']) . "</td>
                    <td>" . esc_html($data['section']) . "</td>
                    <td>" . esc_html($data['counts']['P']) . "</td>
                    <td>" . esc_html($data['counts']['L']) . "</td>
                    <td>" . esc_html($data['counts']['A']) . "</td>
                    <td>" . esc_html($data['counts']['F']) . "</td>
                    <td>" . esc_html($data['counts']['H']) . "</td>
                    <td>" . esc_html($percent) . "%</td>";
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $body_html .= "<td>" . esc_html($data['attendance'][$day] ?? '') . "</td>";
                }
                $body_html .= "<td><button class='edu-button edu-button-edit edit-attendance' data-student-id='$student_id' data-date='{$data['last_date']}'>Edit</button></td></tr>";
            }
        } else {
            foreach ($results as $row) {
                $body_html .= "<tr data-sa-id='$row->sa_id'>
                    <td>" . esc_html($row->student_name) . "</td>
                    <td>" . esc_html($row->student_id) . "</td>
                    <td>" . esc_html($row->education_center_id) . "</td>
                    <td>" . esc_html($row->class) . "</td>
                    <td>" . esc_html($row->section) . "</td>
                    <td>" . esc_html($row->date) . "</td>
                    <td>" . esc_html($row->status) . "</td>
                    <td>" . esc_html($row->subject) . "</td>
                    <td>" . esc_html($row->teacher_id) . "</td>
                    <td><button class='edu-button edu-button-edit edit-attendance' data-sa-id='$row->sa_id'>Edit</button></td>
                </tr>";
            }
        }
    } else {
        $body_html = "<tr><td colspan='" . ($month && $year ? (11 + $days_in_month) : 10) . "'>No records found</td></tr>";
    }

    wp_send_json_success([
        'attendance' => $body_html,
        'total' => $total,
        'days_in_month' => $days_in_month,
        'table_head' => $head_html,
    ]);
}

// AJAX Handler: Add Attendance
add_action('wp_ajax_su_p_add_student_attendance', 'su_p_add_student_attendance');
function su_p_add_student_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';

    $data = [
        'education_center_id' => sanitize_text_field($_POST['education_center_id']),
        'student_id' => sanitize_text_field($_POST['student_id']),
        'student_name' => sanitize_text_field($wpdb->get_var($wpdb->prepare("SELECT student_name FROM {$wpdb->prefix}students WHERE student_id = %s", $_POST['student_id']))),
        'class' => sanitize_text_field($_POST['class']),
        'section' => sanitize_text_field($_POST['section']),
        'date' => sanitize_text_field($_POST['date']),
        'status' => sanitize_text_field($_POST['status']),
        'subject' => sanitize_text_field($_POST['subject']),
        'teacher_id' => sanitize_text_field($_POST['teacher_id']),
    ];

    $result = $wpdb->insert($table_name, $data, ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);
    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to add attendance: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Attendance added successfully!');
    }
}

// AJAX Handler: Get Attendance Record for Edit
add_action('wp_ajax_su_p_get_student_attendance', 'su_p_get_student_attendance');
function su_p_get_student_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $student_id = sanitize_text_field($_POST['student_id']);
    $date = sanitize_text_field($_POST['date']);

    $record = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT sa_id, education_center_id, student_id, student_name, class, section, date, status, subject_id, teacher_id FROM $table_name WHERE student_id = %s AND date = %s",
            $student_id,
            $date
        ),
        ARRAY_A
    );

    if ($record) {
        wp_send_json_success($record);
    } else {
        wp_send_json_error('No record found.');
    }
}
add_action('wp_ajax_su_p_get_student_attendance', 'su_p_get_student_attendance');

// AJAX Handler: Update Attendance
add_action('wp_ajax_su_p_update_student_attendance', 'su_p_update_student_attendance');
function su_p_update_student_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';

    $sa_id = intval($_POST['sa_id']);
    $data = [
        'status' => sanitize_text_field($_POST['status']),
        'subject' => sanitize_text_field($_POST['subject']),
        'teacher_id' => sanitize_text_field($_POST['teacher_id']),
    ];

    $result = $wpdb->update($table_name, $data, ['sa_id' => $sa_id], ['%s', '%s', '%s'], ['%d']);
    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update attendance: ' . $wpdb->last_error]);
    } elseif ($result === 0) {
        wp_send_json_error(['message' => 'No changes made.']);
    } else {
        wp_send_json_success('Attendance updated successfully!');
    }
}
function su_p_get_student_attendance_dates() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $student_id = sanitize_text_field($_POST['student_id']);

    $dates = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DISTINCT date FROM $table_name WHERE student_id = %s ORDER BY date DESC",
            $student_id
        ),
        ARRAY_A
    );

    if ($dates) {
        wp_send_json_success(array_map(function($row) { return ['date' => $row['date']]; }, $dates));
    } else {
        wp_send_json_error('No attendance records found for this student.');
    }
}
add_action('wp_ajax_su_p_get_student_attendance_dates', 'su_p_get_student_attendance_dates');

//teachers attendance 
function render_su_p_teacher_attendance() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';
    ob_start();

    // Fetch departments for filtering
    $departments = $wpdb->get_results("SELECT department_id, department_name FROM $departments_table ORDER BY department_name");
    ?>
    <div class="attendance-main-wrapper">
        <div class="attendance-content-wrapper">
            <div id="edu-loader" class="edu-loader" style="display: none;">
                <div class="edu-loader-container">
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                </div>
            </div>
            <div class="attendance-management">
                <h2>Teacher Attendance</h2>
                <div class="search-filters">
                    <input type="text" id="teacher-id-search" placeholder="Search by Teacher ID...">
                    <input type="text" id="teacher-name-search" placeholder="Search by Teacher Name...">
                    <select id="center-filter">
                        <option value="">All Educational Centers</option>
                        <?php
                        $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                    <select id="department-filter">
                        <option value="">All Departments</option>
                        <?php
                        foreach ($departments as $dept) {
                            echo "<option value='" . esc_attr($dept->department_id) . "'>" . esc_html($dept->department_name) . "</option>";
                        }
                        ?>
                    </select>
                    <select id="month-filter">
                        <option value="">Select Month</option>
                        <?php
                        $month_names = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        $current_month = date('n');
                        $current_year = date('Y');
                        $dates = $wpdb->get_results("SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month FROM $table_name ORDER BY year DESC, month DESC");
                        foreach ($dates as $date) {
                            $selected = ($date->month == $current_month && $date->year == $current_year) ? 'selected' : '';
                            echo "<option value='{$date->month}' data-year='{$date->year}' $selected>" . esc_html($month_names[$date->month] . ' ' . $date->year) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="actions">
                    <button id="add-attendance-btn">Add Attendance</button>
                    <label for="attendance-per-page">Show:</label>
                    <select id="attendance-per-page">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <button id="prev-page" disabled>Previous</button>
                    <span id="page-info"></span>
                    <button id="next-page">Next</button>
                    <button id="refresh-data">Refresh</button>
                </div>
                <div class="attendance-table-wrapper">
                    <div class="actions" id="export-tools"></div>
                    <table id="attendance-table">
                        <thead id="attendance-table-head"></thead>
                        <tbody id="attendance-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="edu-modal" id="add-attendance-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-attendance-modal">×</span>
            <h3>Add Teacher Attendance</h3>
            <form id="add-attendance-form">
                <div class="search-filters">
                    <label for="add-center-id">Education Center</label>
                    <select id="add-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-teacher-id">Teacher</label>
                    <select id="add-teacher-id" name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <!-- Populated via JS based on center selection -->
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-department">Department</label>
                    <input type="text" id="add-department" name="department_name" readonly required>
                </div>
                <div class="search-filters">
                    <label for="add-attendance-date">Date</label>
                    <input type="date" id="add-attendance-date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="search-filters">
                    <label for="add-status">Status</label>
                    <select id="add-status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>">
                <button type="button" id="save-attendance">Save Attendance</button>
            </form>
            <div id="add-attendance-message" class="message"></div>
        </div>
    </div>

    <!-- Date Selection Modal for Edit -->
    <div class="edu-modal" id="edit-date-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-date-modal">×</span>
            <h3>Select Attendance Date to Edit</h3>
            <form id="edit-date-form">
                <div class="search-filters">
                    <label for="edit-teacher-id-display">Teacher</label>
                    <input type="text" id="edit-teacher-id-display" readonly>
                    <input type="hidden" id="edit-teacher-id" name="teacher_id">
                </div>
                <div class="search-filters">
                    <label for="edit-date-select">Select Date</label>
                    <select id="edit-date-select" name="date" required>
                        <option value="">Choose a date</option>
                    </select>
                </div>
                <button type="button" id="proceed-edit">Proceed to Edit</button>
            </form>
            <div id="edit-date-message" class="message"></div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="edu-modal" id="edit-attendance-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-attendance-modal">×</span>
            <h3>Edit Teacher Attendance</h3>
            <form id="edit-attendance-form">
                <input type="hidden" id="edit-attendance-id" name="ta_id">
                <div class="search-filters">
                    <label for="edit-center-id">Education Center ID</label>
                    <input type="text" id="edit-center-id" name="education_center_id" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-teacher-id-display">Teacher</label>
                    <!-- <input type="text" id="edit-teacher-id-display" name="teacher_id_display" readonly> -->
                    <input type="text" id="edit-teacher-id-hidden" name="teacher_id"readonly>
                </div>
                <div class="search-filters">
                    <label for="edit-department">Department</label>
                    <input type="text" id="edit-department" name="department_name" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-attendance-date">Date</label>
                    <input type="date" id="edit-attendance-date" name="date" required>
                </div>
                <div class="search-filters">
                    <label for="edit-status">Status</label>
                    <select id="edit-status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>">
                <button type="button" id="update-attendance">Update Attendance</button>
            </form>
            <div id="edit-attendance-message" class="message"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>';

        let currentPage = 1;
        let perPage = 10;
        let teacherIdSearch = '';
        let teacherNameSearch = '';
        let centerFilter = '';
        let departmentFilter = '';
        let monthFilter = '<?php echo date('n'); ?>';
        let yearFilter = '<?php echo date('Y'); ?>';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
            showLoader(); 
            $(modalId).css('display', 'flex'); 
            setTimeout(hideLoader, 100); 
            clearMessages(modalId);
        }
        function closeModal(modalId) { 
            $(modalId).css('display', 'none'); 
            clearMessages(modalId); 
            hideLoader(); 
        }
        function clearMessages(modalId) {
            $(modalId + ' .message').css({'background': '', 'color': ''}).text('');
        }

        $('.edu-modal-close').on('click', function() {
            closeModal('#' + $(this).data('modal'));
        });

        $(document).on('click', function(event) {
            if ($(event.target).hasClass('edu-modal')) {
                closeModal('#' + event.target.id);
            }
        });

        function getTableData() {
            const table = $('#attendance-table')[0];
            if (!table) return [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => 
                Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim())
            );
            return [headers, ...rows];
        }

        function exportToCSV() {
            showLoader();
            const data = getTableData();
            const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `teacher_attendance_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            hideLoader();
        }

        function exportToExcel() {
            if (!window.XLSX) { console.error('XLSX library not loaded'); return; }
            showLoader();
            const data = getTableData();
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Teacher Attendance');
            XLSX.writeFile(wb, `teacher_attendance_${new Date().toISOString().slice(0,10)}.xlsx`);
            hideLoader();
        }

        function generatePDF() {
            showLoader();
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
            const data = getTableData();
            const instituteName = 'Istituto';
            const instituteLogo = '<?php echo esc_js(plugin_dir_url(__DIR__) . 'logo-instituto.jpg'); ?>';
            const pageWidth = doc.internal.pageSize.width;
            const pageHeight = doc.internal.pageSize.height;
            const margin = 10;
            const borderColor = [70, 131, 180];
            const filters = `Filters: Teacher ID: ${teacherIdSearch || 'All'}, Name: ${teacherNameSearch || 'All'}, Center: ${centerFilter || 'All'}, Department: ${departmentFilter || 'All'}, Month: ${monthFilter ? month_names[parseInt(monthFilter)] : 'All'} ${yearFilter}`;

            doc.setDrawColor(...borderColor);
            doc.setLineWidth(1);
            doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);
            if (instituteLogo) {
                try {
                    doc.addImage(instituteLogo, 'JPEG', margin + 5, margin + 5, 20, 20);
                } catch (e) { console.log('Logo loading failed:', e); }
            }
            doc.setFontSize(16);
            doc.setTextColor(...borderColor);
            doc.text(instituteName.toUpperCase(), pageWidth / 2, margin + 15, { align: 'center' });
            doc.setFontSize(12);
            doc.setTextColor(0);
            doc.text('Teacher Attendance Report', pageWidth / 2, margin + 25, { align: 'center' });
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(filters, margin + 5, margin + 35, { maxWidth: pageWidth - 2 * margin - 10 });

            if (typeof doc.autoTable === 'function' && data.length > 1) {
                doc.autoTable({
                    startY: margin + 40,
                    head: [data[0]],
                    body: data.slice(1),
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak', halign: 'center' },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [245, 245, 245] },
                    columnStyles: { 0: { cellWidth: 30 } },
                    didDrawPage: function(data) {
                        const pageNumber = doc.internal.getNumberOfPages();
                        doc.setFontSize(8);
                        doc.text(`Page ${pageNumber}`, pageWidth - margin - 10, pageHeight - margin - 5, { align: 'right' });
                    }
                });
                const finalY = doc.lastAutoTable.finalY || margin + 40;
                doc.setFontSize(9);
                doc.setTextColor(100);
                doc.text(`Generated on ${new Date().toISOString().slice(0,10)}`, pageWidth / 2, finalY + 10, { align: 'center' });
                doc.save(`teacher_attendance_${new Date().toISOString().slice(0,10)}.pdf`);
            } else {
                console.error('jsPDF autoTable plugin not loaded or no data');
                alert('PDF generation failed');
            }
            hideLoader();
        }

        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            `);
            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
        }

        function loadAttendance(page, limit, tid, tname, center, dept, month, year, showLoading = true) {
            if (showLoading) showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_teacher_attendance',
                    page: page,
                    per_page: limit,
                    search_teacher_id: tid,
                    search_teacher_name: tname,
                    center_filter: center,
                    department_filter: dept,
                    month: month,
                    year: year,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (showLoading) hideLoader();
                    if (response.success) {
                        $('#attendance-table-head').html(response.data.table_head);
                        $('#attendance-table-body').html(response.data.attendance);
                        const totalPages = Math.ceil(response.data.total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages} (Total Records: ${response.data.total})`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                        setupExportButtons();
                    } else {
                        $('#attendance-table-body').html('<tr><td colspan="7">No attendance found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    if (showLoading) hideLoader();
                    $('#attendance-table-body').html('<tr><td colspan="7">Error loading attendance: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);

        $('#teacher-id-search').on('input', debounce(function() {
            teacherIdSearch = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        }, 500));

        $('#teacher-name-search').on('input', debounce(function() {
            teacherNameSearch = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        }, 500));

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#department-filter').on('change', function() {
            departmentFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#month-filter').on('change', function() {
            monthFilter = $(this).val();
            yearFilter = $(this).find('option:selected').data('year') || '<?php echo date('Y'); ?>';
            currentPage = 1;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#attendance-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#refresh-data').on('click', function() {
            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter);
        });

        $('#add-attendance-btn').on('click', function() {
            openModal('#add-attendance-modal');
            $('#add-teacher-id').html('<option value="">Select Teacher</option>');
        });

        $('#add-center-id').on('change', function() {
            const centerId = $(this).val();
            if (centerId) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'su_p_fetch_teachers_by_center',
                        center_id: centerId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const $teacherSelect = $('#add-teacher-id');
                            $teacherSelect.html('<option value="">Select Teacher</option>');
                            response.data.teachers.forEach(teacher => {
                                $teacherSelect.append(`<option value="${teacher.teacher_id}" data-department="${teacher.teacher_department}">${teacher.teacher_name} (${teacher.teacher_id})</option>`);
                            });
                        }
                    }
                });
            }
        });

        $('#add-teacher-id').on('change', function() {
            const $option = $(this).find('option:selected');
            $('#add-department').val($option.data('department'));
        });

        $('#save-attendance').on('click', function() {
            const formData = new FormData($('#add-attendance-form')[0]);
            formData.append('action', 'su_p_add_teacher_attendance');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#add-attendance-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#add-attendance-modal');
                            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter, false);
                        }, 2000);
                        setTimeout(() => clearMessages('#add-attendance-modal'), 1500);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data.message || 'Unknown error'));
                        setTimeout(() => clearMessages('#add-attendance-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-attendance-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error adding attendance: ' + error);
                    setTimeout(() => clearMessages('#add-attendance-modal'), 3000);
                }
            });
        });

        $(document).on('click', '.edit-attendance', function() {
            const teacherId = $(this).data('teacher-id');
            const teacherName = $(this).closest('tr').find('td:first').text();
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_get_teacher_attendance_dates',
                    teacher_id: teacherId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#edit-teacher-id').val(teacherId);
                        $('#edit-teacher-id-display').val(teacherName);
                        const $dateSelect = $('#edit-date-select');
                        $dateSelect.empty().append('<option value="">Choose a date</option>');
                        response.data.forEach(date => {
                            $dateSelect.append(`<option value="${date.date}">${date.date}</option>`);
                        });
                        openModal('#edit-date-modal');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error fetching attendance dates: ' + error);
                }
            });
        });

        $('#proceed-edit').on('click', function() {
            const teacherId = $('#edit-teacher-id').val();
            const date = $('#edit-date-select').val();
            if (!date) {
                $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Please select a date.');
                setTimeout(() => clearMessages('#edit-date-modal'), 3000);
                return;
            }
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_get_teacher_attendance',
                    teacher_id: teacherId,
                    date: date,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const record = response.data;
                        $('#edit-attendance-id').val(record.ta_id);
                        $('#edit-center-id').val(record.education_center_id);
                        $('#edit-teacher-id-display').val(`${record.teacher_name} (${record.teacher_id})`);
                        $('#edit-teacher-id-hidden').val(record.teacher_id);
                        $('#edit-department').val(record.department_name);
                        $('#edit-attendance-date').val(record.date);
                        $('#edit-status').val(record.status);
                        closeModal('#edit-date-modal');
                        openModal('#edit-attendance-modal');
                    } else {
                        $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + response.data);
                        setTimeout(() => clearMessages('#edit-date-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error fetching attendance: ' + error);
                    setTimeout(() => clearMessages('#edit-date-modal'), 3000);
                }
            });
        });

        $('#update-attendance').on('click', function() {
            const formData = new FormData($('#edit-attendance-form')[0]);
            formData.append('action', 'su_p_update_teacher_attendance');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#edit-attendance-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#edit-attendance-modal');
                            loadAttendance(currentPage, perPage, teacherIdSearch, teacherNameSearch, centerFilter, departmentFilter, monthFilter, yearFilter, false);
                        }, 2000);
                        setTimeout(() => clearMessages('#edit-attendance-modal'), 1500);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data.message || 'Unknown error'));
                        setTimeout(() => clearMessages('#edit-attendance-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-attendance-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error updating attendance: ' + error);
                    setTimeout(() => clearMessages('#edit-attendance-modal'), 3000);
                }
            });
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        const month_names = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler: Fetch Teacher Attendance
add_action('wp_ajax_su_p_fetch_teacher_attendance', 'su_p_fetch_teacher_attendance');

function su_p_fetch_teacher_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';

    // Sanitize and validate inputs
    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 10);
    $tid = sanitize_text_field($_POST['search_teacher_id'] ?? '');
    $tname = sanitize_text_field($_POST['search_teacher_name'] ?? '');
    $center = sanitize_text_field($_POST['center_filter'] ?? '');
    $dept = sanitize_text_field($_POST['department_filter'] ?? '');
    $month = !empty($_POST['month']) ? intval($_POST['month']) : date('n');
    $year = !empty($_POST['year']) ? intval($_POST['year']) : date('Y');

    // Calculate days in the selected month
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Base query
    $query = "SELECT ta.teacher_id, ta.teacher_name, d.department_name, ta.date, ta.status 
              FROM $table_name ta 
              LEFT JOIN $departments_table d ON ta.department_id = d.department_id 
              WHERE YEAR(ta.date) = %d AND MONTH(ta.date) = %d";
    $args = [$year, $month];

    // Add filters
    if ($tid) {
        $query .= " AND ta.teacher_id LIKE %s";
        $args[] = '%' . $wpdb->esc_like($tid) . '%';
    }
    if ($tname) {
        $query .= " AND ta.teacher_name LIKE %s";
        $args[] = '%' . $wpdb->esc_like($tname) . '%';
    }
    if ($center) {
        $query .= " AND ta.education_center_id = %s";
        $args[] = $center;
    }
    if ($dept) {
        $query .= " AND ta.department_id = %d";
        $args[] = $dept;
    }

    // Total count query (distinct teachers)
    $total_query = "SELECT COUNT(DISTINCT ta.teacher_id) 
                    FROM $table_name ta 
                    WHERE YEAR(ta.date) = %d AND MONTH(ta.date) = %d";
    $total_args = [$year, $month];
    if ($tid) {
        $total_query .= " AND ta.teacher_id LIKE %s";
        $total_args[] = '%' . $wpdb->esc_like($tid) . '%';
    }
    if ($tname) {
        $total_query .= " AND ta.teacher_name LIKE %s";
        $total_args[] = '%' . $wpdb->esc_like($tname) . '%';
    }
    if ($center) {
        $total_query .= " AND ta.education_center_id = %s";
        $total_args[] = $center;
    }
    if ($dept) {
        $total_query .= " AND ta.department_id = %d";
        $total_args[] = $dept;
    }

    // Get total count
    $total = $wpdb->get_var($wpdb->prepare($total_query, $total_args));
    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Database error (total): ' . $wpdb->last_error]);
    }

    // Pagination
    $offset = ($page - 1) * $per_page;
    $query .= " ORDER BY ta.teacher_id LIMIT %d OFFSET %d";
    $args[] = $per_page;
    $args[] = $offset;

    // Fetch results
    $results = $wpdb->get_results($wpdb->prepare($query, $args));
    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Database error (results): ' . $wpdb->last_error]);
    }

    // Process data into calendar format
    $teacher_data = [];
    foreach ($results as $row) {
        $teacher_key = $row->teacher_id;
        if (!isset($teacher_data[$teacher_key])) {
            $teacher_data[$teacher_key] = [
                'name' => $row->teacher_name,
                'department' => $row->department_name ?: 'N/A',
                'attendance' => array_fill(1, $days_in_month, ''),
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'O' => 0]
            ];
        }
        $day = (int) date('j', strtotime($row->date));
        if ($day >= 1 && $day <= $days_in_month) {
            $status_short = '';
            switch ($row->status) {
                case 'Present': $status_short = 'P'; $teacher_data[$teacher_key]['counts']['P']++; break;
                case 'Late': $status_short = 'L'; $teacher_data[$teacher_key]['counts']['L']++; break;
                case 'Absent': $status_short = 'A'; $teacher_data[$teacher_key]['counts']['A']++; break;
                case 'On Leave': $status_short = 'O'; $teacher_data[$teacher_key]['counts']['O']++; break;
            }
            $teacher_data[$teacher_key]['attendance'][$day] = $status_short;
        }
    }

    // Build table header
    $table_head = '<tr><th>Name</th><th>Teacher ID</th><th>Department</th><th>P</th><th>L</th><th>A</th><th>O</th><th>%</th>';
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $day_name = date('D', strtotime($date));
        $table_head .= "<th>$day<br>$day_name</th>";
    }
    $table_head .= '<th>Actions</th></tr>';

    // Build table body
    $attendance = '';
    if (!empty($teacher_data)) {
        foreach ($teacher_data as $teacher_id => $data) {
            $total_days = $days_in_month - $data['counts']['O'];
            $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0;
            $attendance .= "<tr>
                <td>" . esc_html($data['name']) . "</td>
                <td>" . esc_html($teacher_id) . "</td>
                <td>" . esc_html($data['department']) . "</td>
                <td>" . esc_html($data['counts']['P']) . "</td>
                <td>" . esc_html($data['counts']['L']) . "</td>
                <td>" . esc_html($data['counts']['A']) . "</td>
                <td>" . esc_html($data['counts']['O']) . "</td>
                <td>" . esc_html($percent . '%') . "</td>";
            for ($day = 1; $day <= $days_in_month; $day++) {
                $attendance .= "<td>" . esc_html($data['attendance'][$day] ?? '') . "</td>";
            }
            $attendance .= "<td><button class='edit-attendance' data-teacher-id='" . esc_attr($teacher_id) . "'>Edit</button></td>
            </tr>";
        }
    } else {
        $attendance = "<tr><td colspan='" . (8 + $days_in_month) . "'>No attendance records found.</td></tr>";
    }

    // Send JSON response
    wp_send_json_success([
        'table_head' => $table_head,
        'attendance' => $attendance,
        'total' => $total ?: 0
    ]);
}

// AJAX Handler: Fetch Teachers by Center
add_action('wp_ajax_su_p_fetch_teachers_by_center', 'su_p_fetch_teachers_by_center');
function su_p_fetch_teachers_by_center() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    $center_id = sanitize_text_field($_POST['center_id']);
    $teachers = get_teachers_by_center($center_id);
    wp_send_json_success(['teachers' => $teachers]);
}

// AJAX Handler: Add Teacher Attendance
add_action('wp_ajax_su_p_add_teacher_attendance', 'su_p_add_teacher_attendance');
function su_p_add_teacher_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';

    $center_id = sanitize_text_field($_POST['education_center_id']);
    $teacher_id = sanitize_text_field($_POST['teacher_id']);
    $date = sanitize_text_field($_POST['date']);
    $status = sanitize_text_field($_POST['status']);

    $teachers = get_teachers_by_center($center_id);
    $teacher = array_filter($teachers, fn($t) => $t->teacher_id === $teacher_id);
    $teacher = reset($teacher);

    if (!$teacher) {
        wp_send_json_error(['message' => 'Teacher not found']);
    }

    $department_id = get_or_create_department_id($teacher->teacher_department, $center_id);
    $result = $wpdb->insert($table_name, [
        'education_center_id' => $center_id,
        'teacher_id' => $teacher_id,
        'teacher_name' => $teacher->teacher_name,
        'department_id' => $department_id,
        'date' => $date,
        'status' => $status
    ]);

    if ($result !== false) {
        wp_send_json_success('Attendance added successfully');
    } else {
        wp_send_json_error(['message' => $wpdb->last_error]);
    }
}

// AJAX Handler: Get Teacher Attendance Dates
add_action('wp_ajax_su_p_get_teacher_attendance_dates', 'su_p_get_teacher_attendance_dates');
function su_p_get_teacher_attendance_dates() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $teacher_id = sanitize_text_field($_POST['teacher_id']);
    $dates = $wpdb->get_results($wpdb->prepare(
        "SELECT date FROM $table_name WHERE teacher_id = %s ORDER BY date DESC",
        $teacher_id
    ));
    wp_send_json_success($dates);
}

// AJAX Handler: Get Teacher Attendance Record
add_action('wp_ajax_su_p_get_teacher_attendance', 'su_p_get_teacher_attendance');
function su_p_get_teacher_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';

    $teacher_id = sanitize_text_field($_POST['teacher_id']);
    $date = sanitize_text_field($_POST['date']);
    $record = $wpdb->get_row($wpdb->prepare(
        "SELECT ta.*, d.department_name 
         FROM $table_name ta 
         LEFT JOIN $departments_table d ON ta.department_id = d.department_id 
         WHERE ta.teacher_id = %s AND ta.date = %s",
        $teacher_id, $date
    ));

    if ($record) {
        // Ensure both teacher_id and teacher_name are explicitly included
        wp_send_json_success([
            'ta_id' => $record->ta_id,
            'education_center_id' => $record->education_center_id,
            'teacher_id' => $record->teacher_id,
            'teacher_name' => $record->teacher_name,
            'department_name' => $record->department_name ?: 'N/A',
            'date' => $record->date,
            'status' => $record->status
        ]);
    } else {
        wp_send_json_error('No record found');
    }
}
// AJAX Handler: Update Teacher Attendance
add_action('wp_ajax_su_p_update_teacher_attendance', 'su_p_update_teacher_attendance');
function su_p_update_teacher_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';

    $ta_id = intval($_POST['ta_id']);
    $date = sanitize_text_field($_POST['date']);
    $status = sanitize_text_field($_POST['status']);

    $result = $wpdb->update(
        $table_name,
        ['date' => $date, 'status' => $status],
        ['ta_id' => $ta_id],
        ['%s', '%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success('Attendance updated successfully');
    } else {
        wp_send_json_error(['message' => $wpdb->last_error]);
    }
}



// Reuse generate_unique_staff_id from your sample code
// function generate_unique_staff_id($wpdb, $table_name, $education_center_id) {
//     $max_attempts = 5;
//     $prefix = 'STF-';
//     $id_length = 12;

//     for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
//         $time_part = substr(str_replace('.', '', microtime(true)), -10);
//         $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
//         $staff_id = $prefix . $time_part . $random_part;

//         if (strlen($staff_id) > 20) {
//             $staff_id = substr($staff_id, 0, 20);
//         }

//         $exists = $wpdb->get_var($wpdb->prepare(
//             "SELECT COUNT(*) FROM $table_name WHERE staff_id = %s AND education_center_id = %s",
//             $staff_id, $education_center_id
//         ));

//         if ($exists == 0) {
//             return $staff_id;
//         }
//         usleep(10000);
//     }
//     return new WP_Error('staff_id_generation_failed', 'Unable to generate a unique Staff ID after multiple attempts.');
// }

//satff attendance
function render_su_p_staff_attendance() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_attendance';
    $staff_table = $wpdb->prefix . 'staff';
    ob_start();

    // Fetch educational centers for filtering
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ?>
    <div class="attendance-main-wrapper">
        <div class="attendance-content-wrapper">
            <div id="edu-loader" class="edu-loader" style="display: none;">
                <div class="edu-loader-container">
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                </div>
            </div>
            <div class="attendance-management">
                <h2>Staff Attendance</h2>
                <div class="search-filters">
                    <input type="text" id="staff-id-search" placeholder="Search by Staff ID...">
                    <input type="text" id="staff-name-search" placeholder="Search by Staff Name...">
                    <select id="center-filter">
                        <option value="">All Educational Centers</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                    <select id="role-filter">
                        <option value="">All Roles</option>
                        <option value="Accountant">Accountant</option>
                        <option value="Administrator">Administrator</option>
                        <option value="Librarian">Librarian</option>
                        <option value="Support Staff">Support Staff</option>
                    </select>
                    <select id="month-filter">
                        <option value="">Select Month</option>
                        <?php
                        $month_names = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        $current_month = date('n');
                        $current_year = date('Y');
                        $dates = $wpdb->get_results("SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month FROM $table_name ORDER BY year DESC, month DESC");
                        foreach ($dates as $date) {
                            $selected = ($date->month == $current_month && $date->year == $current_year) ? 'selected' : '';
                            echo "<option value='{$date->month}' data-year='{$date->year}' $selected>" . esc_html($month_names[$date->month] . ' ' . $date->year) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="actions">
                    <button id="add-attendance-btn">Add Attendance</button>
                    <label for="attendance-per-page">Show:</label>
                    <select id="attendance-per-page">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <button id="prev-page" disabled>Previous</button>
                    <span id="page-info"></span>
                    <button id="next-page">Next</button>
                    <button id="refresh-data">Refresh</button>
                </div>
                <div class="attendance-table-wrapper">
                    <div class="actions" id="export-tools"></div>
                    <table id="attendance-table">
                        <thead id="attendance-table-head"></thead>
                        <tbody id="attendance-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="edu-modal" id="add-attendance-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-attendance-modal">×</span>
            <h3>Add Staff Attendance</h3>
            <form id="add-attendance-form">
                <div class="search-filters">
                    <label for="add-center-id">Education Center</label>
                    <select id="add-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-staff-id">Staff</label>
                    <select id="add-staff-id" name="staff_id" required>
                        <option value="">Select Staff</option>
                        <!-- Populated via JS based on center selection -->
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-role">Role</label>
                    <input type="text" id="add-role" name="role" readonly required>
                </div>
                <div class="search-filters">
                    <label for="add-attendance-date">Date</label>
                    <input type="date" id="add-attendance-date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="search-filters">
                    <label for="add-status">Status</label>
                    <select id="add-status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>">
                <button type="button" id="save-attendance">Save Attendance</button>
            </form>
            <div id="add-attendance-message" class="message"></div>
        </div>
    </div>

    <!-- Date Selection Modal for Edit -->
    <div class="edu-modal" id="edit-date-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-date-modal">×</span>
            <h3>Select Attendance Date to Edit</h3>
            <form id="edit-date-form">
                <div class="search-filters">
                    <label for="edit-staff-id-display">Staff</label>
                    <input type="text" id="edit-staff-id-display" readonly>
                    <input type="hidden" id="edit-staff-id" name="staff_id">
                </div>
                <div class="search-filters">
                    <label for="edit-date-select">Select Date</label>
                    <select id="edit-date-select" name="date" required>
                        <option value="">Choose a date</option>
                    </select>
                </div>
                <button type="button" id="proceed-edit">Proceed to Edit</button>
            </form>
            <div id="edit-date-message" class="message"></div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="edu-modal" id="edit-attendance-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-attendance-modal">×</span>
            <h3>Edit Staff Attendance</h3>
            <form id="edit-attendance-form">
                <input type="hidden" id="edit-attendance-id" name="attendance_id">
                <div class="search-filters">
                    <label for="edit-center-id">Education Center ID</label>
                    <input type="text" id="edit-center-id" name="education_center_id" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-staff-id-display">Staff</label>
                    <input type="text" id="edit-staff-id-display" name="staff_id_display" readonly>
                    <input type="hidden" id="edit-staff-id-hidden" name="staff_id">
                </div>
                <div class="search-filters">
                    <label for="edit-role">Role</label>
                    <input type="text" id="edit-role" name="role" readonly required>
                </div>
                <div class="search-filters">
                    <label for="edit-attendance-date">Date</label>
                    <input type="date" id="edit-attendance-date" name="date" required>
                </div>
                <div class="search-filters">
                    <label for="edit-status">Status</label>
                    <select id="edit-status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>">
                <button type="button" id="update-attendance">Update Attendance</button>
            </form>
            <div id="edit-attendance-message" class="message"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_attendance_nonce'); ?>';

        let currentPage = 1;
        let perPage = 10;
        let staffIdSearch = '';
        let staffNameSearch = '';
        let centerFilter = '';
        let roleFilter = '';
        let monthFilter = '<?php echo date('n'); ?>';
        let yearFilter = '<?php echo date('Y'); ?>';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
            showLoader(); 
            $(modalId).css('display', 'flex'); 
            setTimeout(hideLoader, 100); 
            clearMessages(modalId);
        }
        function closeModal(modalId) { 
            $(modalId).css('display', 'none'); 
            clearMessages(modalId); 
            hideLoader(); 
        }
        function clearMessages(modalId) {
            $(modalId + ' .message').css({'background': '', 'color': ''}).text('');
        }

        $('.edu-modal-close').on('click', function() {
            closeModal('#' + $(this).data('modal'));
        });

        $(document).on('click', function(event) {
            if ($(event.target).hasClass('edu-modal')) {
                closeModal('#' + event.target.id);
            }
        });

        function loadAttendance(page, limit, sid, sname, center, role, month, year, showLoading = true) {
            if (showLoading) showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_staff_attendance',
                    page: page,
                    per_page: limit,
                    search_staff_id: sid,
                    search_staff_name: sname,
                    center_filter: center,
                    role_filter: role,
                    month: month,
                    year: year,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (showLoading) hideLoader();
                    if (response.success) {
                        $('#attendance-table-head').html(response.data.table_head);
                        $('#attendance-table-body').html(response.data.attendance);
                        const totalPages = Math.ceil(response.data.total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages} (Total Records: ${response.data.total})`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                        setupExportButtons();
                    } else {
                        $('#attendance-table-body').html('<tr><td colspan="8">No attendance found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    if (showLoading) hideLoader();
                    $('#attendance-table-body').html('<tr><td colspan="8">Error loading attendance: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        // Export functions (CSV, Excel, PDF) - Reused from previous code
        function setupExportButtons() {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            `);
            tools.find('.export-csv').on('click', exportToCSV);
            tools.find('.export-pdf').on('click', generatePDF);
            tools.find('.export-excel').on('click', exportToExcel);
        }

        function exportToCSV() { /* Implementation from previous code */ }
        function exportToExcel() { /* Implementation from previous code */ }
        function generatePDF() { /* Implementation from previous code */ }

        loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);

        $('#staff-id-search').on('input', debounce(function() {
            staffIdSearch = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        }, 500));

        $('#staff-name-search').on('input', debounce(function() {
            staffNameSearch = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        }, 500));

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#role-filter').on('change', function() {
            roleFilter = $(this).val();
            currentPage = 1;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#month-filter').on('change', function() {
            monthFilter = $(this).val();
            yearFilter = $(this).find('option:selected').data('year') || '<?php echo date('Y'); ?>';
            currentPage = 1;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#attendance-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#refresh-data').on('click', function() {
            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter);
        });

        $('#add-attendance-btn').on('click', function() {
            openModal('#add-attendance-modal');
            $('#add-staff-id').html('<option value="">Select Staff</option>');
        });

        $('#add-center-id').on('change', function() {
            const centerId = $(this).val();
            if (centerId) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'su_p_fetch_staff_by_center',
                        center_id: centerId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const $staffSelect = $('#add-staff-id');
                            $staffSelect.html('<option value="">Select Staff</option>');
                            response.data.staff.forEach(staff => {
                                $staffSelect.append(`<option value="${staff.staff_id}" data-role="${staff.role}">${staff.name} (${staff.staff_id})</option>`);
                            });
                        }
                    }
                });
            }
        });

        $('#add-staff-id').on('change', function() {
            const $option = $(this).find('option:selected');
            $('#add-role').val($option.data('role') || '');
        });

        $('#save-attendance').on('click', function() {
            const formData = new FormData($('#add-attendance-form')[0]);
            formData.append('action', 'su_p_add_staff_attendance');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#add-attendance-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#add-attendance-modal');
                            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter, false);
                        }, 2000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data.message || 'Unknown error'));
                        setTimeout(() => clearMessages('#add-attendance-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#add-attendance-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error adding attendance: ' + error);
                    setTimeout(() => clearMessages('#add-attendance-modal'), 3000);
                }
            });
        });

        $(document).on('click', '.edit-attendance', function() {
            const staffId = $(this).data('staff-id');
            const staffName = $(this).closest('tr').find('td:first').text();
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_get_staff_attendance_dates',
                    staff_id: staffId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        $('#edit-staff-id').val(staffId);
                        $('#edit-staff-id-display').val(staffName); // Pre-populate with name from table
                        const $dateSelect = $('#edit-date-select');
                        $dateSelect.empty().append('<option value="">Choose a date</option>');
                        response.data.forEach(date => {
                            $dateSelect.append(`<option value="${date.date}">${date.date}</option>`);
                        });
                        openModal('#edit-date-modal');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('Error fetching attendance dates: ' + error);
                }
            });
        });

        $('#proceed-edit').on('click', function() {
            const staffId = $('#edit-staff-id').val();
            const date = $('#edit-date-select').val();
            if (!date) {
                $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Please select a date.');
                setTimeout(() => clearMessages('#edit-date-modal'), 3000);
                return;
            }
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_get_staff_attendance',
                    staff_id: staffId,
                    date: date,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const record = response.data;
                        $('#edit-attendance-id').val(record.attendance_id || '');
                        $('#edit-center-id').val(record.education_center_id || '');
                        $('#edit-staff-id-display').val(`${record.name} (${record.staff_id})`);
                        $('#edit-staff-id-hidden').val(record.staff_id || '');
                        $('#edit-role').val(record.role || 'N/A');
                        $('#edit-attendance-date').val(record.date || '');
                        $('#edit-status').val(record.status || 'Present');
                        closeModal('#edit-date-modal');
                        openModal('#edit-attendance-modal');
                    } else {
                        $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data || 'Unknown error'));
                        setTimeout(() => clearMessages('#edit-date-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    console.error('AJAX Error:', status, error, xhr.responseText); // Debug
                    $('#edit-date-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error fetching attendance: ' + error);
                    setTimeout(() => clearMessages('#edit-date-modal'), 3000);
                }
            });
        });

        $('#update-attendance').on('click', function() {
            const formData = new FormData($('#edit-attendance-form')[0]);
            formData.append('action', 'su_p_update_staff_attendance');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#edit-attendance-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#edit-attendance-modal');
                            loadAttendance(currentPage, perPage, staffIdSearch, staffNameSearch, centerFilter, roleFilter, monthFilter, yearFilter, false);
                        }, 2000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data.message || 'Unknown error'));
                        setTimeout(() => clearMessages('#edit-attendance-modal'), 3000);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    $('#edit-attendance-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error updating attendance: ' + error);
                    setTimeout(() => clearMessages('#edit-attendance-modal'), 3000);
                }
            });
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler: Fetch Staff Attendance
add_action('wp_ajax_su_p_fetch_staff_attendance', 'su_p_fetch_staff_attendance');
function su_p_fetch_staff_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_attendance';
    $staff_table = $wpdb->prefix . 'staff';

    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 10);
    $sid = sanitize_text_field($_POST['search_staff_id'] ?? '');
    $sname = sanitize_text_field($_POST['search_staff_name'] ?? '');
    $center = sanitize_text_field($_POST['center_filter'] ?? '');
    $role = sanitize_text_field($_POST['role_filter'] ?? '');
    $month = !empty($_POST['month']) ? intval($_POST['month']) : date('n');
    $year = !empty($_POST['year']) ? intval($_POST['year']) : date('Y');

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $query = "SELECT sa.staff_id, sa.date, sa.status, s.name, s.role 
              FROM $table_name sa 
              LEFT JOIN $staff_table s ON sa.staff_id = s.staff_id 
              WHERE YEAR(sa.date) = %d AND MONTH(sa.date) = %d";
    $args = [$year, $month];

    if ($sid) {
        $query .= " AND sa.staff_id LIKE %s";
        $args[] = '%' . $wpdb->esc_like($sid) . '%';
    }
    if ($sname) {
        $query .= " AND s.name LIKE %s";
        $args[] = '%' . $wpdb->esc_like($sname) . '%';
    }
    if ($center) {
        $query .= " AND sa.education_center_id = %s";
        $args[] = $center;
    }
    if ($role) {
        $query .= " AND s.role = %s";
        $args[] = $role;
    }

    $total_query = "SELECT COUNT(DISTINCT sa.staff_id) 
                    FROM $table_name sa 
                    LEFT JOIN $staff_table s ON sa.staff_id = s.staff_id 
                    WHERE YEAR(sa.date) = %d AND MONTH(sa.date) = %d";
    $total_args = [$year, $month];
    if ($sid) {
        $total_query .= " AND sa.staff_id LIKE %s";
        $total_args[] = '%' . $wpdb->esc_like($sid) . '%';
    }
    if ($sname) {
        $total_query .= " AND s.name LIKE %s";
        $total_args[] = '%' . $wpdb->esc_like($sname) . '%';
    }
    if ($center) {
        $total_query .= " AND sa.education_center_id = %s";
        $total_args[] = $center;
    }
    if ($role) {
        $total_query .= " AND s.role = %s";
        $total_args[] = $role;
    }

    $total = $wpdb->get_var($wpdb->prepare($total_query, $total_args));
    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Database error (total): ' . $wpdb->last_error]);
    }

    $offset = ($page - 1) * $per_page;
    $query .= " ORDER BY sa.staff_id LIMIT %d OFFSET %d";
    $args[] = $per_page;
    $args[] = $offset;

    $results = $wpdb->get_results($wpdb->prepare($query, $args));
    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Database error (results): ' . $wpdb->last_error]);
    }

    $staff_data = [];
    foreach ($results as $row) {
        $staff_key = $row->staff_id;
        if (!isset($staff_data[$staff_key])) {
            $staff_data[$staff_key] = [
                'name' => $row->name,
                'role' => $row->role ?: 'N/A',
                'attendance' => array_fill(1, $days_in_month, ''),
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'O' => 0]
            ];
        }
        $day = (int) date('j', strtotime($row->date));
        if ($day >= 1 && $day <= $days_in_month) {
            $status_short = '';
            switch ($row->status) {
                case 'Present': $status_short = 'P'; $staff_data[$staff_key]['counts']['P']++; break;
                case 'Late': $status_short = 'L'; $staff_data[$staff_key]['counts']['L']++; break;
                case 'Absent': $status_short = 'A'; $staff_data[$staff_key]['counts']['A']++; break;
                case 'On Leave': $status_short = 'O'; $staff_data[$staff_key]['counts']['O']++; break;
            }
            $staff_data[$staff_key]['attendance'][$day] = $status_short;
        }
    }

    $table_head = '<tr><th>Name</th><th>Staff ID</th><th>Role</th><th>P</th><th>L</th><th>A</th><th>O</th><th>%</th>';
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $day_name = date('D', strtotime($date));
        $table_head .= "<th>$day<br>$day_name</th>";
    }
    $table_head .= '<th>Actions</th></tr>';

    $attendance = '';
    if (!empty($staff_data)) {
        foreach ($staff_data as $staff_id => $data) {
            $total_days = $days_in_month - $data['counts']['O'];
            $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0;
            $attendance .= "<tr>
                <td>" . esc_html($data['name']) . "</td>
                <td>" . esc_html($staff_id) . "</td>
                <td>" . esc_html($data['role']) . "</td>
                <td>" . esc_html($data['counts']['P']) . "</td>
                <td>" . esc_html($data['counts']['L']) . "</td>
                <td>" . esc_html($data['counts']['A']) . "</td>
                <td>" . esc_html($data['counts']['O']) . "</td>
                <td>" . esc_html($percent . '%') . "</td>";
            for ($day = 1; $day <= $days_in_month; $day++) {
                $attendance .= "<td>" . esc_html($data['attendance'][$day] ?? '') . "</td>";
            }
            $attendance .= "<td><button class='edit-attendance' data-staff-id='" . esc_attr($staff_id) . "'>Edit</button></td>
            </tr>";
        }
    } else {
        $attendance = "<tr><td colspan='" . (8 + $days_in_month) . "'>No attendance records found.</td></tr>";
    }

    wp_send_json_success([
        'table_head' => $table_head,
        'attendance' => $attendance,
        'total' => $total ?: 0
    ]);
}

// AJAX Handler: Fetch Staff by Center
add_action('wp_ajax_su_p_fetch_staff_by_center', 'su_p_fetch_staff_by_center');
function su_p_fetch_staff_by_center() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $center_id = sanitize_text_field($_POST['center_id']);
    $staff = $wpdb->get_results($wpdb->prepare(
        "SELECT staff_id, name, role FROM {$wpdb->prefix}staff WHERE education_center_id = %s",
        $center_id
    ));
    wp_send_json_success(['staff' => $staff]);
}

// AJAX Handler: Add Staff Attendance
add_action('wp_ajax_su_p_add_staff_attendance', 'su_p_add_staff_attendance');
function su_p_add_staff_attendance() {
    // Verify nonce for security
    check_ajax_referer('su_p_attendance_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_attendance';
    $staff_table = $wpdb->prefix . 'staff';

    // Sanitize inputs
    $center_id = sanitize_text_field($_POST['education_center_id'] ?? '');
    $staff_id = sanitize_text_field($_POST['staff_id'] ?? '');
    $date = sanitize_text_field($_POST['date'] ?? '');
    $status = sanitize_text_field($_POST['status'] ?? '');

    // Validate required fields
    if (empty($center_id) || empty($staff_id) || empty($date) || empty($status)) {
        wp_send_json_error(['message' => 'All fields are required']);
        exit;
    }

    // Check if staff exists
    $staff = $wpdb->get_row($wpdb->prepare(
        "SELECT staff_id FROM $staff_table WHERE staff_id = %s AND education_center_id = %s",
        $staff_id,
        $center_id
    ));

    if (!$staff) {
        wp_send_json_error(['message' => 'Staff not found']);
        exit;
    }

    // Insert attendance (no 'name' column)
    $result = $wpdb->insert(
        $table_name,
        [
            'education_center_id' => $center_id,
            'staff_id' => $staff_id,
            'date' => $date,
            'status' => $status
        ],
        ['%s', '%s', '%s', '%s'] // Data types for prepare
    );

    if ($result === false) {
        error_log('Staff Attendance Insert Error: ' . $wpdb->last_error);
        wp_send_json_error(['message' => 'Failed to add attendance: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Attendance added successfully');
    }

    exit; // Ensure no extra output
}
// AJAX Handler: Get Staff Attendance Dates
add_action('wp_ajax_su_p_get_staff_attendance_dates', 'su_p_get_staff_attendance_dates');
function su_p_get_staff_attendance_dates() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_attendance';
    $staff_id = sanitize_text_field($_POST['staff_id']);
    $dates = $wpdb->get_results($wpdb->prepare(
        "SELECT date FROM $table_name WHERE staff_id = %s ORDER BY date DESC",
        $staff_id
    ));
    wp_send_json_success($dates);
}

// AJAX Handler: Get Staff Attendance Record
add_action('wp_ajax_su_p_get_staff_attendance', 'su_p_get_staff_attendance');
function su_p_get_staff_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_attendance';
    $staff_table = $wpdb->prefix . 'staff';

    $staff_id = sanitize_text_field($_POST['staff_id']);
    $date = sanitize_text_field($_POST['date']);
    $record = $wpdb->get_row($wpdb->prepare(
        "SELECT sa.*, s.name, s.role 
         FROM $table_name sa 
         LEFT JOIN $staff_table s ON sa.staff_id = s.staff_id 
         WHERE sa.staff_id = %s AND sa.date = %s",
        $staff_id, $date
    ));

    if ($record) {
        wp_send_json_success([
            'attendance_id' => $record->attendance_id,
            'education_center_id' => $record->education_center_id,
            'staff_id' => $record->staff_id,
            'name' => $record->name,
            'role' => $record->role ?: 'N/A',
            'date' => $record->date,
            'status' => $record->status
        ]);
    } else {
        wp_send_json_error('No record found');
    }
}

// AJAX Handler: Update Staff Attendance
add_action('wp_ajax_su_p_update_staff_attendance', 'su_p_update_staff_attendance');
function su_p_update_staff_attendance() {
    check_ajax_referer('su_p_attendance_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_attendance';

    $attendance_id = intval($_POST['attendance_id']);
    $date = sanitize_text_field($_POST['date']);
    $status = sanitize_text_field($_POST['status']);

    $result = $wpdb->update(
        $table_name,
        ['date' => $date, 'status' => $status],
        ['attendance_id' => $attendance_id],
        ['%s', '%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success('Attendance updated successfully');
    } else {
        wp_send_json_error(['message' => $wpdb->last_error]);
    }
}

//class sections
// Main Render Function (Unchanged, Modal-Based)
function render_su_p_class_management() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="management-main-wrapper">
        <div class="management-content-wrapper">
            <div id="edu-loader" class="edu-loader" style="display: none;">
                <div class="edu-loader-container">
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                </div>
            </div>
            <div class="management-section">
                <h2>Class Management</h2>
                <div class="search-filters">
                    <input type="text" id="class-name-search" placeholder="Search by Class Name...">
                    <select id="center-filter">
                        <option value="">All Educational Centers</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="actions">
                    <button id="add-class-btn">Add Class</button>
                    <label for="classes-per-page">Show:</label>
                    <select id="classes-per-page">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <button id="prev-page" disabled>Previous</button>
                    <span id="page-info"></span>
                    <button id="next-page">Next</button>
                    <button id="refresh-data">Refresh</button>
                </div>
                <div class="management-table-wrapper">
                    <div class="actions" id="export-tools"></div>
                    <table id="class-table">
                        <thead id="class-table-head"></thead>
                        <tbody id="class-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div class="edu-modal" id="add-class-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-class-modal">×</span>
            <h3>Add New Class</h3>
            <form id="add-class-form">
                <div class="search-filters">
                    <label for="add-center-id">Education Center</label>
                    <select id="add-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-class-name">Class Name</label>
                    <input type="text" id="add-class-name" name="class_name" required>
                </div>
                <div class="search-filters">
                    <label for="add-sections">Sections (e.g., A, B, C)</label>
                    <input type="text" id="add-sections" name="sections" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_class_nonce'); ?>">
                <button type="button" id="save-class">Add Class</button>
            </form>
            <div id="add-class-message" class="message"></div>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div class="edu-modal" id="edit-class-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-class-modal">×</span>
            <h3>Edit Class</h3>
            <form id="edit-class-form">
                <input type="hidden" id="edit-class-id" name="class_id">
                <div class="search-filters">
                    <label for="edit-center-id">Education Center</label>
                    <select id="edit-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="edit-class-name">Class Name</label>
                    <input type="text" id="edit-class-name" name="class_name" required>
                </div>
                <div class="search-filters">
                    <label for="edit-sections">Sections (e.g., A, B, C)</label>
                    <input type="text" id="edit-sections" name="sections" required>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_class_nonce'); ?>">
                <button type="button" id="update-class">Update Class</button>
            </form>
            <div id="edit-class-message" class="message"></div>
        </div>
    </div>

    <script>
    // Global Utility Functions
    function showLoader() { jQuery('#edu-loader').show(); }
    function hideLoader() { jQuery('#edu-loader').hide(); }
    function openModal(modalId) { 
        showLoader(); 
        jQuery(modalId).css('display', 'flex'); 
        setTimeout(hideLoader, 100); 
        clearMessages(modalId);
    }
    function closeModal(modalId) { 
        jQuery(modalId).css('display', 'none'); 
        clearMessages(modalId); 
        hideLoader(); 
    }
    function clearMessages(modalId) {
        jQuery(modalId + ' .message').css({'background': '', 'color': ''}).text('');
    }

    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_class_nonce'); ?>';

        let currentPage = 1;
        let perPage = 10;
        let classNameSearch = '';
        let centerFilter = '';

        $('.edu-modal-close').on('click', function() {
            closeModal('#' + $(this).data('modal'));
        });

        $(document).on('click', function(event) {
            if ($(event.target).hasClass('edu-modal')) {
                closeModal('#' + event.target.id);
            }
        });

        function loadClasses(page, limit, className, center, showLoading = true) {
            if (showLoading) showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: page,
                    per_page: limit,
                    search_class_name: className,
                    center_filter: center,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (showLoading) hideLoader();
                    if (response.success) {
                        $('#class-table-head').html(response.data.table_head);
                        $('#class-table-body').html(response.data.classes);
                        const totalPages = Math.ceil(response.data.total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages} (Total Records: ${response.data.total})`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                        setupExportButtons(response.data.class_data);
                    } else {
                        $('#class-table-body').html('<tr><td colspan="4">No classes found.</td></tr>');
                        $('#export-tools').html('');
                    }
                },
                error: function(xhr, status, error) {
                    if (showLoading) hideLoader();
                    $('#class-table-body').html('<tr><td colspan="4">Error loading classes: ' + error + '</td></tr>');
                    $('#export-tools').html('');
                }
            });
        }

        function setupExportButtons(classData) {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            `);
            tools.find('.export-csv').on('click', () => exportToCSV(classData));
            tools.find('.export-pdf').on('click', () => generatePDF(classData));
            tools.find('.export-excel').on('click', () => exportToExcel(classData));
        }

        function exportToCSV(data) {
            const headers = ['Class ID', 'Class Name', 'Sections', 'Education Center ID'];
            const csvRows = [headers.join(',')];
            data.forEach(row => {
                const values = [row.id, row.class_name, row.sections, row.education_center_id].map(value => `"${value}"`);
                csvRows.push(values.join(','));
            });
            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'class_sections.csv';
            link.click();
        }

        function generatePDF(data) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(16);
            doc.text('Class Sections Report', 10, 10);
            doc.setFontSize(12);
            const tableData = data.map(row => [row.id, row.class_name, row.sections, row.education_center_id]);
            doc.autoTable({
                head: [['Class ID', 'Class Name', 'Sections', 'Education Center ID']],
                body: tableData,
                startY: 20,
                styles: { fontSize: 10, cellPadding: 2 },
                headStyles: { fillColor: [44, 109, 251] }
            });
            doc.save('class_sections.pdf');
        }

        function exportToExcel(data) {
            const worksheetData = data.map(row => ({
                'Class ID': row.id,
                'Class Name': row.class_name,
                'Sections': row.sections,
                'Education Center ID': row.education_center_id
            }));
            const worksheet = XLSX.utils.json_to_sheet(worksheetData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Classes');
            XLSX.writeFile(workbook, 'class_sections.xlsx');
        }

        loadClasses(currentPage, perPage, classNameSearch, centerFilter);

        $('#class-name-search').on('input', debounce(function() {
            classNameSearch = $(this).val();
            currentPage = 1;
            loadClasses(currentPage, perPage, classNameSearch, centerFilter);
        }, 500));

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadClasses(currentPage, perPage, classNameSearch, centerFilter);
        });

        $('#classes-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadClasses(currentPage, perPage, classNameSearch, centerFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadClasses(currentPage, perPage, classNameSearch, centerFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadClasses(currentPage, perPage, classNameSearch, centerFilter);
        });

        $('#refresh-data').on('click', function() {
            loadClasses(currentPage, perPage, classNameSearch, centerFilter);
        });

        $('#add-class-btn').on('click', function() {
            openModal('#add-class-modal');
        });

        $('#save-class').on('click', function() {
            su_p_add_class();
        });

        $('#class-table-body').on('click', '.edit-class', function() {
            const classId = $(this).data('class-id');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_class',
                    class_id: classId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#edit-class-id').val(response.data.id);
                        $('#edit-center-id').val(response.data.education_center_id);
                        $('#edit-class-name').val(response.data.class_name);
                        $('#edit-sections').val(response.data.sections);
                        openModal('#edit-class-modal');
                    } else {
                        alert('Error fetching class: ' + response.data.message);
                    }
                }
            });
        });

        $('#update-class').on('click', function() {
            su_p_edit_class();
        });

        $('#class-table-body').on('click', '.delete-class', function() {
            if (confirm('Are you sure you want to delete this class?')) {
                su_p_delete_class($(this).data('class-id'));
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    });

    function su_p_add_class() {
        const formData = new FormData(jQuery('#add-class-form')[0]);
        formData.append('action', 'su_p_add_class');
        showLoader();
        jQuery.ajax({
            url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoader();
                const $message = jQuery('#add-class-message');
                if (response.success) {
                    $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                    setTimeout(() => {
                        closeModal('#add-class-modal');
                        loadClasses(currentPage, perPage, classNameSearch, centerFilter, false);
                    }, 2000);
                } else {
                    $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                    setTimeout(() => clearMessages('#add-class-modal'), 3000);
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                jQuery('#add-class-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error adding class: ' + error);
            }
        });
    }

    function su_p_edit_class() {
        const formData = new FormData(jQuery('#edit-class-form')[0]);
        formData.append('action', 'su_p_edit_class');
        showLoader();
        jQuery.ajax({
            url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoader();
                const $message = jQuery('#edit-class-message');
                if (response.success) {
                    $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                    setTimeout(() => {
                        closeModal('#edit-class-modal');
                        loadClasses(currentPage, perPage, classNameSearch, centerFilter, false);
                    }, 2000);
                } else {
                    $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                    setTimeout(() => clearMessages('#edit-class-modal'), 3000);
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                jQuery('#edit-class-message').css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error editing class: ' + error);
            }
        });
    }

    function su_p_delete_class(classId) {
        showLoader();
        jQuery.ajax({
            url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            method: 'POST',
            data: {
                action: 'su_p_delete_class',
                class_id: classId,
                nonce: '<?php echo wp_create_nonce('su_p_class_nonce'); ?>'
            },
            success: function(response) {
                hideLoader();
                if (response.success) {
                    loadClasses(currentPage, perPage, classNameSearch, centerFilter, false);
                } else {
                    alert('Error deleting class: ' + (response.data?.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                alert('Error deleting class: ' + error);
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handlers (Unchanged)
add_action('wp_ajax_su_p_fetch_classes', 'su_p_fetch_classes');
function su_p_fetch_classes() {
    check_ajax_referer('su_p_class_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 10);
    $class_name = sanitize_text_field($_POST['search_class_name'] ?? '');
    $center = sanitize_text_field($_POST['center_filter'] ?? '');

    $query = "SELECT * FROM $table_name WHERE 1=1";
    $args = [];

    if ($class_name) {
        $query .= " AND class_name LIKE %s";
        $args[] = '%' . $wpdb->esc_like($class_name) . '%';
    }
    if ($center) {
        $query .= " AND education_center_id = %s";
        $args[] = $center;
    }

    $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE 1=1" . strstr($query, ' AND'), $args));
    if ($wpdb->last_error) wp_send_json_error(['message' => 'Database error (total): ' . $wpdb->last_error]);

    $offset = ($page - 1) * $per_page;
    $query .= " ORDER BY id DESC LIMIT %d OFFSET %d";
    $args[] = $per_page;
    $args[] = $offset;

    $results = $wpdb->get_results($wpdb->prepare($query, $args));
    if ($wpdb->last_error) wp_send_json_error(['message' => 'Database error (results): ' . $wpdb->last_error]);

    $table_head = '<tr><th>ID</th><th>Class Name</th><th>Sections</th><th>Education Center ID</th><th>Actions</th></tr>';
    $classes = '';
    $class_data = [];
    if ($results) {
        foreach ($results as $row) {
            $classes .= "<tr>
                <td>" . esc_html($row->id) . "</td>
                <td>" . esc_html($row->class_name) . "</td>
                <td>" . esc_html($row->sections) . "</td>
                <td>" . esc_html($row->education_center_id) . "</td>
                <td>
                    <button class='edit-class' data-class-id='" . esc_attr($row->id) . "'>Edit</button>
                    <button class='delete-class' data-class-id='" . esc_attr($row->id) . "'>Delete</button>
                </td>
            </tr>";
            $class_data[] = [
                'id' => $row->id,
                'class_name' => $row->class_name,
                'sections' => $row->sections,
                'education_center_id' => $row->education_center_id
            ];
        }
    } else {
        $classes = '<tr><td colspan="5">No classes found.</td></tr>';
    }

    wp_send_json_success([
        'table_head' => $table_head,
        'classes' => $classes,
        'class_data' => $class_data,
        'total' => $total ?: 0
    ]);
}

add_action('wp_ajax_su_p_fetch_class', 'su_p_fetch_class');
function su_p_fetch_class() {
    check_ajax_referer('su_p_class_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $class_id = intval($_POST['class_id'] ?? 0);

    $class = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $class_id));
    if ($class) {
        wp_send_json_success([
            'id' => $class->id,
            'class_name' => $class->class_name,
            'sections' => $class->sections,
            'education_center_id' => $class->education_center_id
        ]);
    } else {
        wp_send_json_error(['message' => 'Class not found']);
    }
}

add_action('wp_ajax_su_p_add_class', 'su_p_add_class');
function su_p_add_class() {
    check_ajax_referer('su_p_class_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    $center_id = sanitize_text_field($_POST['education_center_id'] ?? '');
    $class_name = sanitize_text_field($_POST['class_name'] ?? '');
    $sections = sanitize_text_field($_POST['sections'] ?? '');

    if (empty($center_id) || empty($class_name) || empty($sections)) {
        wp_send_json_error(['message' => 'All fields are required']);
        exit;
    }

    $result = $wpdb->insert(
        $table_name,
        [
            'education_center_id' => $center_id,
            'class_name' => $class_name,
            'sections' => $sections
        ],
        ['%s', '%s', '%s']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to add class: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Class added successfully');
    }
    exit;
}

add_action('wp_ajax_su_p_edit_class', 'su_p_edit_class');
function su_p_edit_class() {
    check_ajax_referer('su_p_class_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';

    $class_id = intval($_POST['class_id'] ?? 0);
    $center_id = sanitize_text_field($_POST['education_center_id'] ?? '');
    $class_name = sanitize_text_field($_POST['class_name'] ?? '');
    $sections = sanitize_text_field($_POST['sections'] ?? '');

    if (empty($class_id) || empty($center_id) || empty($class_name) || empty($sections)) {
        wp_send_json_error(['message' => 'All fields are required']);
        exit;
    }

    $result = $wpdb->update(
        $table_name,
        [
            'education_center_id' => $center_id,
            'class_name' => $class_name,
            'sections' => $sections
        ],
        ['id' => $class_id],
        ['%s', '%s', '%s'],
        ['%d']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update class: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Class updated successfully');
    }
    exit;
}

add_action('wp_ajax_su_p_delete_class', 'su_p_delete_class');
function su_p_delete_class() {
    check_ajax_referer('su_p_class_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $class_id = intval($_POST['class_id'] ?? 0);

    if (empty($class_id)) {
        wp_send_json_error(['message' => 'Invalid class ID']);
        exit;
    }

    $result = $wpdb->delete($table_name, ['id' => $class_id], ['%d']);
    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to delete class: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Class deleted successfully');
    }
    exit;
}

// Modified Standalone Functions
function render_su_p_add_class_form() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Add New Class</h2>
        <div id="add-class-message" class="message"></div>
        <form id="standalone-add-class-form">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="add-center-id">Education Center</label></th>
                    <td>
                        <select id="add-center-id" name="education_center_id" class="regular-text" required>
                            <option value="">Select Center</option>
                            <?php
                            foreach ($centers as $center) {
                                $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                                echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-class-name">Class Name</label></th>
                    <td><input type="text" id="add-class-name" name="class_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-sections">Sections (e.g., A, B, C)</label></th>
                    <td><input type="text" id="add-sections" name="sections" class="regular-text" required></td>
                </tr>
            </table>
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_class_nonce'); ?>">
            <p class="submit"><button type="button" id="save-class" class="button-primary">Add Class</button></p>
        </form>
        <h3>Existing Classes</h3>
        <table class="wp-list-table widefat fixed striped" id="add-class-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Sections</th>
                    <th>Education Center ID</th>
                </tr>
            </thead>
            <tbody id="add-class-table-body"></tbody>
        </table>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_class_nonce'); ?>';

        function loadClasses() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000, // Fetch all for simplicity
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#add-class-table-body').html(response.data.classes.replace(/<td>.*?(Edit|Delete).*?<\/td>/g, '')); // Remove actions
                    } else {
                        $('#add-class-table-body').html('<tr><td colspan="4">No classes found.</td></tr>');
                    }
                }
            });
        }

        loadClasses();

        $('#save-class').on('click', function() {
            const formData = new FormData($('#standalone-add-class-form')[0]);
            formData.append('action', 'su_p_add_class');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const $message = $('#add-class-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        $('#standalone-add-class-form')[0].reset();
                        loadClasses();
                        setTimeout(() => $message.text(''), 4000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                        setTimeout(() => $message.text(''), 4000);
                    }
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

function render_su_p_edit_class_form() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Edit Class Sections</h2>
        <div id="edit-class-message" class="message"></div>
        <table class="wp-list-table widefat fixed striped" id="edit-class-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Sections</th>
                    <th>Education Center ID</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody id="edit-class-table-body"></tbody>
        </table>
        <div id="edit-form-container" style="display: none; margin-top: 20px;">
            <h3>Edit Class Section</h3>
            <form id="standalone-edit-class-form">
                <input type="hidden" id="edit-class-id" name="class_id">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="edit-center-id">Education Center</label></th>
                        <td>
                            <select id="edit-center-id" name="education_center_id" class="regular-text" required>
                                <option value="">Select Center</option>
                                <?php
                                foreach ($centers as $center) {
                                    $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                                    echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-class-name">Class Name</label></th>
                        <td><input type="text" id="edit-class-name" name="class_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-sections">Sections (e.g., A, B, C)</label></th>
                        <td><input type="text" id="edit-sections" name="sections" class="regular-text" required></td>
                    </tr>
                </table>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_class_nonce'); ?>">
                <p class="submit">
                    <button type="button" id="update-class" class="button-primary">Update Class</button>
                    <button type="button" class="button" onclick="jQuery('#edit-form-container').hide()">Cancel</button>
                </p>
            </form>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_class_nonce'); ?>';

        function loadClasses() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#edit-class-table-body').html(response.data.classes);
                    } else {
                        $('#edit-class-table-body').html('<tr><td colspan="5">No classes found.</td></tr>');
                    }
                }
            });
        }

        loadClasses();

        $('#edit-class-table-body').on('click', '.edit-class', function() {
            const classId = $(this).data('class-id');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_class',
                    class_id: classId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#edit-class-id').val(response.data.id);
                        $('#edit-center-id').val(response.data.education_center_id);
                        $('#edit-class-name').val(response.data.class_name);
                        $('#edit-sections').val(response.data.sections);
                        $('#edit-form-container').show();
                    } else {
                        alert('Error fetching class: ' + response.data.message);
                    }
                }
            });
        });

        $('#update-class').on('click', function() {
            const formData = new FormData($('#standalone-edit-class-form')[0]);
            formData.append('action', 'su_p_edit_class');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const $message = $('#edit-class-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        $('#edit-form-container').hide();
                        loadClasses();
                        setTimeout(() => $message.text(''), 4000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                        setTimeout(() => $message.text(''), 4000);
                    }
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

function render_su_p_delete_class_form() {
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Delete Class Sections</h2>
        <div id="delete-class-message" class="message"></div>
        <table class="wp-list-table widefat fixed striped" id="delete-class-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Sections</th>
                    <th>Education Center ID</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id="delete-class-table-body"></tbody>
        </table>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_class_nonce'); ?>';

        function loadClasses() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#delete-class-table-body').html(response.data.classes);
                    } else {
                        $('#delete-class-table-body').html('<tr><td colspan="5">No classes found.</td></tr>');
                    }
                }
            });
        }

        loadClasses();

        $('#delete-class-table-body').on('click', '.delete-class', function() {
            if (confirm('Are you sure you want to delete this class?')) {
                const classId = $(this).data('class-id');
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'su_p_delete_class',
                        class_id: classId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        const $message = $('#delete-class-message');
                        if (response.success) {
                            $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                            loadClasses();
                            setTimeout(() => $message.text(''), 4000);
                        } else {
                            $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                            setTimeout(() => $message.text(''), 4000);
                        }
                    }
                });
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

function render_su_p_student_count() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Student Count per Class and Section</h2>
        <div class="form-group search-form">
            <div class="input-group">
                <span class="input-group-addon">Search</span>
                <input type="text" id="search_text_class_count" placeholder="Search by Class or Section" class="form-control" />
            </div>
        </div>
        <p>Total Students: <span id="total-students">0</span></p>
        <table id="class_count" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Section</th>
                    <th>Student Count</th>
                </tr>
            </thead>
            <tbody id="class-count-body"></tbody>
        </table>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_class_nonce'); ?>';

        function loadStudentCounts() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        let totalStudents = 0;
                        response.data.class_data.forEach(classItem => {
                            const sections = classItem.sections.split(',');
                            sections.forEach(section => {
                                section = section.trim();
                                $.ajax({
                                    url: ajaxUrl,
                                    method: 'POST',
                                    data: {
                                        action: 'su_p_get_student_count',
                                        class_name: classItem.class_name,
                                        section: section,
                                        education_center_id: classItem.education_center_id,
                                        nonce: ajaxNonce
                                    },
                                    async: false, // Synchronous for simplicity
                                    success: function(countResponse) {
                                        if (countResponse.success) {
                                            html += `<tr>
                                                <td>${classItem.class_name}</td>
                                                <td>${section}</td>
                                                <td>${countResponse.data}</td>
                                            </tr>`;
                                            totalStudents += parseInt(countResponse.data);
                                        }
                                    }
                                });
                            });
                        });
                        $('#class-count-body').html(html);
                        $('#total-students').text(totalStudents);
                    } else {
                        $('#class-count-body').html('<tr><td colspan="3">No classes found.</td></tr>');
                    }
                }
            });
        }

        loadStudentCounts();

        $('#search_text_class_count').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#class_count tbody tr').each(function() {
                var className = $(this).find('td').eq(0).text().toLowerCase();
                var section = $(this).find('td').eq(1).text().toLowerCase();
                if (className.indexOf(searchText) > -1 || section.indexOf(searchText) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_su_p_get_student_count', 'su_p_get_student_count');
function su_p_get_student_count() {
    check_ajax_referer('su_p_class_nonce', 'nonce');
    $class_name = sanitize_text_field($_POST['class_name'] ?? '');
    $section = sanitize_text_field($_POST['section'] ?? '');
    $educational_center_id = sanitize_text_field($_POST['education_center_id'] ?? '');

    $args = [
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'class', 'value' => $class_name, 'compare' => '='],
            ['key' => 'section', 'value' => $section, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
        ]
    ];
    $students_query = new WP_Query($args);
    wp_send_json_success($students_query->found_posts);
}
function get_student_count($class_name, $section, $educational_center_id) {
    $args = [
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'class', 'value' => $class_name, 'compare' => '='],
            ['key' => 'section', 'value' => $section, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
        ]
    ];
    $students_query = new WP_Query($args);
    return $students_query->found_posts;
}

function get_total_students($educational_center_id) {
    $args = [
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            ['key' => 'educational_center_id', 'value' => $educational_center_id, 'compare' => '=']
        ]
    ];
    $students_query = new WP_Query($args);
    return $students_query->found_posts;
}

// Enqueue Scripts
add_action('admin_enqueue_scripts', 'enqueue_class_management_scripts');
function enqueue_class_management_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], null, true);
    wp_enqueue_script('jspdf-autotable', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js', ['jspdf'], null, true);
    wp_enqueue_script('sheetjs', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', [], null, true);
}

//exam
function render_su_p_exam_management() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="management-main-wrapper">
        <div class="management-content-wrapper">
            <div id="edu-loader" class="edu-loader" style="display: none;">
                <div class="edu-loader-container">
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                </div>
            </div>
            <div class="management-section">
                <h2>Exam Management</h2>
                <div class="search-filters">
                    <input type="text" id="exam-name-search" placeholder="Search by Exam Name...">
                    <select id="center-filter">
                        <option value="">All Educational Centers</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='$center_id'>" . esc_html($center->post_title) . " ($center_id)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="actions">
                    <button id="add-exam-btn">Add Exam</button>
                    <label for="exams-per-page">Show:</label>
                    <select id="exams-per-page">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <button id="prev-page" disabled>Previous</button>
                    <span id="page-info"></span>
                    <button id="next-page">Next</button>
                    <button id="refresh-data">Refresh</button>
                </div>
                <div class="management-table-wrapper">
                    <div class="actions" id="export-tools"></div>
                    <table id="exam-table">
                        <thead id="exam-table-head"></thead>
                        <tbody id="exam-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Exam Modal -->
    <div class="edu-modal" id="add-exam-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="add-exam-modal">×</span>
            <h3>Add New Exam</h3>
            <form id="add-exam-form">
                <div class="search-filters">
                    <label for="add-center-id">Education Center</label>
                    <select id="add-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="add-exam-name">Exam Name</label>
                    <input type="text" id="add-exam-name" name="exam_name" required>
                </div>
                <div class="search-filters">
                    <label for="add-exam-date">Exam Date</label>
                    <input type="date" id="add-exam-date" name="exam_date" required>
                </div>
                <div class="search-filters">
                    <label for="add-class-id">Class</label>
                    <select id="add-class-id" name="class_id" required>
                        <option value="">Select Class</option>
                    </select>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_exam_nonce'); ?>">
                <button type="button" id="save-exam">Add Exam</button>
            </form>
            <div id="add-exam-message" class="message"></div>
        </div>
    </div>

    <!-- Edit Exam Modal -->
    <div class="edu-modal" id="edit-exam-modal" style="display: none;">
        <div class="edu-modal-content">
            <span class="edu-modal-close" data-modal="edit-exam-modal">×</span>
            <h3>Edit Exam</h3>
            <form id="edit-exam-form">
                <input type="hidden" id="edit-exam-id" name="exam_id">
                <div class="search-filters">
                    <label for="edit-center-id">Education Center</label>
                    <select id="edit-center-id" name="education_center_id" required>
                        <option value="">Select Center</option>
                        <?php
                        foreach ($centers as $center) {
                            $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                            echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-filters">
                    <label for="edit-exam-name">Exam Name</label>
                    <input type="text" id="edit-exam-name" name="exam_name" required>
                </div>
                <div class="search-filters">
                    <label for="edit-exam-date">Exam Date</label>
                    <input type="date" id="edit-exam-date" name="exam_date" required>
                </div>
                <div class="search-filters">
                    <label for="edit-class-id">Class</label>
                    <select id="edit-class-id" name="class_id" required>
                        <option value="">Select Class</option>
                    </select>
                </div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_exam_nonce'); ?>">
                <button type="button" id="update-exam">Update Exam</button>
            </form>
            <div id="edit-exam-message" class="message"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_exam_nonce'); ?>';

        let currentPage = 1;
        let perPage = 10;
        let examNameSearch = '';
        let centerFilter = '';

        function showLoader() { $('#edu-loader').show(); }
        function hideLoader() { $('#edu-loader').hide(); }
        function openModal(modalId) { 
            showLoader(); 
            $(modalId).css('display', 'flex'); 
            setTimeout(hideLoader, 100); 
            clearMessages(modalId);
        }
        function closeModal(modalId) { 
            $(modalId).css('display', 'none'); 
            clearMessages(modalId); 
            hideLoader(); 
        }
        function clearMessages(modalId) {
            $(modalId + ' .message').css({'background': '', 'color': ''}).text('');
        }

        function loadExams(page, limit, examName, center, showLoading = true) {
            if (showLoading) showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exams',
                    page: page,
                    per_page: limit,
                    search_exam_name: examName,
                    center_filter: center,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (showLoading) hideLoader();
                    if (response.success) {
                        $('#exam-table-head').html(response.data.table_head);
                        $('#exam-table-body').html(response.data.exams);
                        const totalPages = Math.ceil(response.data.total / limit);
                        $('#page-info').text(`Page ${page} of ${totalPages} (Total Records: ${response.data.total})`);
                        $('#prev-page').prop('disabled', page === 1);
                        $('#next-page').prop('disabled', page === totalPages);
                        setupExportButtons(response.data.exam_data);
                    } else {
                        $('#exam-table-body').html('<tr><td colspan="5">No exams found.</td></tr>');
                        $('#export-tools').html('');
                    }
                }
            });
        }

        function setupExportButtons(examData) {
            const tools = $('#export-tools');
            tools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
            `);
            tools.find('.export-csv').on('click', () => exportToCSV(examData));
            tools.find('.export-pdf').on('click', () => generatePDF(examData));
            tools.find('.export-excel').on('click', () => exportToExcel(examData));
        }

        function exportToCSV(data) {
            const headers = ['Exam ID', 'Exam Name', 'Exam Date', 'Class ID', 'Education Center ID'];
            const csvRows = [headers.join(',')];
            data.forEach(row => {
                const values = [row.id, row.name, row.exam_date, row.class_id || 'N/A', row.education_center_id].map(value => `"${value}"`);
                csvRows.push(values.join(','));
            });
            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'exams.csv';
            link.click();
        }

        function generatePDF(data) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(16);
            doc.text('Exams Report', 10, 10);
            doc.setFontSize(12);
            const tableData = data.map(row => [row.id, row.name, row.exam_date, row.class_id || 'N/A', row.education_center_id]);
            doc.autoTable({
                head: [['Exam ID', 'Exam Name', 'Exam Date', 'Class ID', 'Education Center ID']],
                body: tableData,
                startY: 20,
                styles: { fontSize: 10, cellPadding: 2 },
                headStyles: { fillColor: [44, 109, 251] }
            });
            doc.save('exams.pdf');
        }

        function exportToExcel(data) {
            const worksheetData = data.map(row => ({
                'Exam ID': row.id,
                'Exam Name': row.name,
                'Exam Date': row.exam_date,
                'Class ID': row.class_id || 'N/A',
                'Education Center ID': row.education_center_id
            }));
            const worksheet = XLSX.utils.json_to_sheet(worksheetData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Exams');
            XLSX.writeFile(workbook, 'exams.xlsx');
        }

        function loadClasses(selectId, centerId) {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000,
                    center_filter: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_class_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const $select = $(selectId);
                        $select.find('option:not(:first)').remove();
                        response.data.class_data.forEach(cls => {
                            $select.append(`<option value="${cls.class_name}">${cls.class_name}</option>`);
                        });
                    }
                }
            });
        }

        loadExams(currentPage, perPage, examNameSearch, centerFilter);

        $('#exam-name-search').on('input', debounce(function() {
            examNameSearch = $(this).val();
            currentPage = 1;
            loadExams(currentPage, perPage, examNameSearch, centerFilter);
        }, 500));

        $('#center-filter').on('change', function() {
            centerFilter = $(this).val();
            currentPage = 1;
            loadExams(currentPage, perPage, examNameSearch, centerFilter);
        });

        $('#exams-per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadExams(currentPage, perPage, examNameSearch, centerFilter);
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadExams(currentPage, perPage, examNameSearch, centerFilter);
        });

        $('#prev-page').on('click', function() {
            currentPage--;
            loadExams(currentPage, perPage, examNameSearch, centerFilter);
        });

        $('#refresh-data').on('click', function() {
            loadExams(currentPage, perPage, examNameSearch, centerFilter);
        });

        $('#add-exam-btn').on('click', function() {
            $('#add-center-id').val('');
            $('#add-class-id').html('<option value="">Select Class</option>');
            openModal('#add-exam-modal');
        });

        $('#add-center-id').on('change', function() {
            loadClasses('#add-class-id', $(this).val());
        });

        $('#save-exam').on('click', function() {
            const formData = new FormData($('#add-exam-form')[0]);
            formData.append('action', 'su_p_add_exam');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#add-exam-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#add-exam-modal');
                            loadExams(currentPage, perPage, examNameSearch, centerFilter, false);
                        }, 2000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                    }
                }
            });
        });

        $('#exam-table-body').on('click', '.edit-exam', function() {
            const examId = $(this).data('exam-id');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exam',
                    exam_id: examId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#edit-exam-id').val(response.data.id);
                        $('#edit-center-id').val(response.data.education_center_id);
                        $('#edit-exam-name').val(response.data.name);
                        $('#edit-exam-date').val(response.data.exam_date);
                        loadClasses('#edit-class-id', response.data.education_center_id);
                        setTimeout(() => $('#edit-class-id').val(response.data.class_id), 500); // Wait for classes to load
                        openModal('#edit-exam-modal');
                    } else {
                        alert('Error fetching exam: ' + response.data.message);
                    }
                }
            });
        });

        $('#edit-center-id').on('change', function() {
            loadClasses('#edit-class-id', $(this).val());
        });

        $('#update-exam').on('click', function() {
            const formData = new FormData($('#edit-exam-form')[0]);
            formData.append('action', 'su_p_edit_exam');
            showLoader();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    const $message = $('#edit-exam-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        setTimeout(() => {
                            closeModal('#edit-exam-modal');
                            loadExams(currentPage, perPage, examNameSearch, centerFilter, false);
                        }, 2000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                    }
                }
            });
        });

        $('#exam-table-body').on('click', '.delete-exam', function() {
            if (confirm('Are you sure you want to delete this exam?')) {
                const examId = $(this).data('exam-id');
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'su_p_delete_exam',
                        exam_id: examId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            loadExams(currentPage, perPage, examNameSearch, centerFilter, false);
                        } else {
                            alert('Error deleting exam: ' + (response.data?.message || 'Unknown error'));
                        }
                    }
                });
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
function render_su_p_add_exam() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Add New Exam</h2>
        <div id="add-exam-message" class="message"></div>
        <form id="standalone-add-exam-form">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="add-center-id">Education Center</label></th>
                    <td>
                        <select id="add-center-id" name="education_center_id" class="regular-text" required>
                            <option value="">Select Center</option>
                            <?php
                            foreach ($centers as $center) {
                                $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                                echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-exam-name">Exam Name</label></th>
                    <td><input type="text" id="add-exam-name" name="exam_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-exam-date">Exam Date</label></th>
                    <td><input type="date" id="add-exam-date" name="exam_date" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-class-id">Class</label></th>
                    <td>
                        <select id="add-class-id" name="class_id" class="regular-text" required>
                            <option value="">Select Class</option>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_exam_nonce'); ?>">
            <p class="submit"><button type="button" id="save-exam" class="button-primary">Add Exam</button></p>
        </form>
        <h3>Existing Exams</h3>
        <table class="wp-list-table widefat fixed striped" id="add-exam-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Exam Date</th>
                    <th>Class ID</th>
                    <th>Education Center ID</th>
                </tr>
            </thead>
            <tbody id="add-exam-table-body"></tbody>
        </table>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_exam_nonce'); ?>';

        function loadExams() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exams',
                    page: 1,
                    per_page: 1000,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#add-exam-table-body').html(response.data.exams.replace(/<td>.*?(Edit|Delete).*?<\/td>/g, ''));
                    } else {
                        $('#add-exam-table-body').html('<tr><td colspan="5">No exams found.</td></tr>');
                    }
                }
            });
        }

        function loadClasses(centerId) {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000,
                    center_filter: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_class_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const $select = $('#add-class-id');
                        $select.find('option:not(:first)').remove();
                        response.data.class_data.forEach(cls => {
                            $select.append(`<option value="${cls.class_name}">${cls.class_name}</option>`);
                        });
                    }
                }
            });
        }

        loadExams();
        $('#add-center-id').on('change', function() {
            loadClasses($(this).val());
        });

        $('#save-exam').on('click', function() {
            const formData = new FormData($('#standalone-add-exam-form')[0]);
            formData.append('action', 'su_p_add_exam');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const $message = $('#add-exam-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        $('#standalone-add-exam-form')[0].reset();
                        loadExams();
                        setTimeout(() => $message.text(''), 4000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                        setTimeout(() => $message.text(''), 4000);
                    }
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
function render_su_p_edit_exam() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Edit Exams</h2>
        <div id="edit-exam-message" class="message"></div>
        <table class="wp-list-table widefat fixed striped" id="edit-exam-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Exam Date</th>
                    <th>Class ID</th>
                    <th>Education Center ID</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody id="edit-exam-table-body"></tbody>
        </table>
        <div id="edit-form-container" style="display: none; margin-top: 20px;">
            <h3>Edit Exam</h3>
            <form id="standalone-edit-exam-form">
                <input type="hidden" id="edit-exam-id" name="exam_id">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="edit-center-id">Education Center</label></th>
                        <td>
                            <select id="edit-center-id" name="education_center_id" class="regular-text" required>
                                <option value="">Select Center</option>
                                <?php
                                foreach ($centers as $center) {
                                    $center_id = get_post_meta($center->ID, 'educational_center_id', true);
                                    echo "<option value='" . esc_attr($center_id) . "'>" . esc_html($center->post_title . " ($center_id)") . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-exam-name">Exam Name</label></th>
                        <td><input type="text" id="edit-exam-name" name="exam_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-exam-date">Exam Date</label></th>
                        <td><input type="date" id="edit-exam-date" name="exam_date" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-class-id">Class</label></th>
                        <td>
                            <select id="edit-class-id" name="class_id" class="regular-text" required>
                                <option value="">Select Class</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('su_p_exam_nonce'); ?>">
                <p class="submit">
                    <button type="button" id="update-exam" class="button-primary">Update Exam</button>
                    <button type="button" class="button" onclick="jQuery('#edit-form-container').hide()">Cancel</button>
                </p>
            </form>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_exam_nonce'); ?>';

        function loadExams() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exams',
                    page: 1,
                    per_page: 1000,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#edit-exam-table-body').html(response.data.exams);
                    } else {
                        $('#edit-exam-table-body').html('<tr><td colspan="6">No exams found.</td></tr>');
                    }
                }
            });
        }

        function loadClasses(centerId) {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_classes',
                    page: 1,
                    per_page: 1000,
                    center_filter: centerId,
                    nonce: '<?php echo wp_create_nonce('su_p_class_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const $select = $('#edit-class-id');
                        $select.find('option:not(:first)').remove();
                        response.data.class_data.forEach(cls => {
                            $select.append(`<option value="${cls.class_name}">${cls.class_name}</option>`);
                        });
                    }
                }
            });
        }

        loadExams();

        $('#edit-exam-table-body').on('click', '.edit-exam', function() {
            const examId = $(this).data('exam-id');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exam',
                    exam_id: examId,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#edit-exam-id').val(response.data.id);
                        $('#edit-center-id').val(response.data.education_center_id);
                        $('#edit-exam-name').val(response.data.name);
                        $('#edit-exam-date').val(response.data.exam_date);
                        loadClasses(response.data.education_center_id);
                        setTimeout(() => $('#edit-class-id').val(response.data.class_id), 500);
                        $('#edit-form-container').show();
                    } else {
                        alert('Error fetching exam: ' + response.data.message);
                    }
                }
            });
        });

        $('#edit-center-id').on('change', function() {
            loadClasses($(this).val());
        });

        $('#update-exam').on('click', function() {
            const formData = new FormData($('#standalone-edit-exam-form')[0]);
            formData.append('action', 'su_p_edit_exam');
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const $message = $('#edit-exam-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        $('#edit-form-container').hide();
                        loadExams();
                        setTimeout(() => $message.text(''), 4000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                        setTimeout(() => $message.text(''), 4000);
                    }
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
function render_su_p_delete_exam() {
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Delete Exams</h2>
        <div id="delete-exam-message" class="message"></div>
        <table class="wp-list-table widefat fixed striped" id="delete-exam-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Exam Date</th>
                    <th>Class ID</th>
                    <th>Education Center ID</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id="delete-exam-table-body"></tbody>
        </table>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_exam_nonce'); ?>';

        function loadExams() {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exams',
                    page: 1,
                    per_page: 1000,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#delete-exam-table-body').html(response.data.exams);
                    } else {
                        $('#delete-exam-table-body').html('<tr><td colspan="6">No exams found.</td></tr>');
                    }
                }
            });
        }

        loadExams();

        $('#delete-exam-table-body').on('click', '.delete-exam', function() {
            if (confirm('Are you sure you want to delete this exam?')) {
                const examId = $(this).data('exam-id');
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'su_p_delete_exam',
                        exam_id: examId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        const $message = $('#delete-exam-message');
                        if (response.success) {
                            $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                            loadExams();
                            setTimeout(() => $message.text(''), 4000);
                        } else {
                            $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                            setTimeout(() => $message.text(''), 4000);
                        }
                    }
                });
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_su_p_fetch_exams', 'su_p_fetch_exams');
function su_p_fetch_exams() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'exams';

    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 10);
    $exam_name = sanitize_text_field($_POST['search_exam_name'] ?? '');
    $center = sanitize_text_field($_POST['center_filter'] ?? '');

    $query = "SELECT * FROM $table_name WHERE 1=1";
    $args = [];

    if ($exam_name) {
        $query .= " AND name LIKE %s";
        $args[] = '%' . $wpdb->esc_like($exam_name) . '%';
    }
    if ($center) {
        $query .= " AND education_center_id = %s";
        $args[] = $center;
    }

    $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE 1=1" . strstr($query, ' AND'), $args));
    $offset = ($page - 1) * $per_page;
    $query .= " ORDER BY id DESC LIMIT %d OFFSET %d";
    $args[] = $per_page;
    $args[] = $offset;

    $results = $wpdb->get_results($wpdb->prepare($query, $args));
    $table_head = '<tr><th>ID</th><th>Exam Name</th><th>Exam Date</th><th>Class ID</th><th>Education Center ID</th><th>Actions</th></tr>';
    $exams = '';
    $exam_data = [];
    if ($results) {
        foreach ($results as $row) {
            $exams .= "<tr>
                <td>" . esc_html($row->id) . "</td>
                <td>" . esc_html($row->name) . "</td>
                <td>" . esc_html($row->exam_date) . "</td>
                <td>" . esc_html($row->class_id ?: 'N/A') . "</td>
                <td>" . esc_html($row->education_center_id) . "</td>
                <td>
                    <button class='edit-exam' data-exam-id='" . esc_attr($row->id) . "'>Edit</button>
                    <button class='delete-exam' data-exam-id='" . esc_attr($row->id) . "'>Delete</button>
                </td>
            </tr>";
            $exam_data[] = [
                'id' => $row->id,
                'name' => $row->name,
                'exam_date' => $row->exam_date,
                'class_id' => $row->class_id,
                'education_center_id' => $row->education_center_id
            ];
        }
    } else {
        $exams = '<tr><td colspan="6">No exams found.</td></tr>';
    }

    wp_send_json_success([
        'table_head' => $table_head,
        'exams' => $exams,
        'exam_data' => $exam_data,
        'total' => $total ?: 0
    ]);
}

add_action('wp_ajax_su_p_fetch_exam', 'su_p_fetch_exam');
function su_p_fetch_exam() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'exams';
    $exam_id = intval($_POST['exam_id'] ?? 0);

    $exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $exam_id));
    if ($exam) {
        wp_send_json_success([
            'id' => $exam->id,
            'name' => $exam->name,
            'exam_date' => $exam->exam_date,
            'class_id' => $exam->class_id,
            'education_center_id' => $exam->education_center_id
        ]);
    } else {
        wp_send_json_error(['message' => 'Exam not found']);
    }
}

add_action('wp_ajax_su_p_add_exam', 'su_p_add_exam');
function su_p_add_exam() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'exams';

    $center_id = sanitize_text_field($_POST['education_center_id'] ?? '');
    $exam_name = sanitize_text_field($_POST['exam_name'] ?? '');
    $exam_date = sanitize_text_field($_POST['exam_date'] ?? '');
    $class_id = sanitize_text_field($_POST['class_id'] ?? '');

    if (empty($center_id) || empty($exam_name) || empty($exam_date) || empty($class_id)) {
        wp_send_json_error(['message' => 'All fields are required']);
    }

    $result = $wpdb->insert(
        $table_name,
        [
            'education_center_id' => $center_id,
            'name' => $exam_name,
            'exam_date' => $exam_date,
            'class_id' => $class_id
        ],
        ['%s', '%s', '%s', '%s']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to add exam: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Exam added successfully');
    }
}

add_action('wp_ajax_su_p_edit_exam', 'su_p_edit_exam');
function su_p_edit_exam() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'exams';

    $exam_id = intval($_POST['exam_id'] ?? 0);
    $center_id = sanitize_text_field($_POST['education_center_id'] ?? '');
    $exam_name = sanitize_text_field($_POST['exam_name'] ?? '');
    $exam_date = sanitize_text_field($_POST['exam_date'] ?? '');
    $class_id = sanitize_text_field($_POST['class_id'] ?? '');

    if (empty($exam_id) || empty($center_id) || empty($exam_name) || empty($exam_date) || empty($class_id)) {
        wp_send_json_error(['message' => 'All fields are required']);
    }

    $result = $wpdb->update(
        $table_name,
        [
            'education_center_id' => $center_id,
            'name' => $exam_name,
            'exam_date' => $exam_date,
            'class_id' => $class_id
        ],
        ['id' => $exam_id],
        ['%s', '%s', '%s', '%s'],
        ['%d']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update exam: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Exam updated successfully');
    }
}

add_action('wp_ajax_su_p_delete_exam', 'su_p_delete_exam');
function su_p_delete_exam() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'exams';
    $exam_id = intval($_POST['exam_id'] ?? 0);

    if (empty($exam_id)) {
        wp_send_json_error(['message' => 'Invalid exam ID']);
    }

    $result = $wpdb->delete($table_name, ['id' => $exam_id], ['%d']);
    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to delete exam: ' . $wpdb->last_error]);
    } else {
        wp_send_json_success('Exam deleted successfully');
    }
}

//results
function render_su_p_exam_results() {
    global $wpdb;
    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1]);
    ob_start();
    ?>
    <div class="wrap" style="padding: 20px;">
        <h2>Manage Exam Results</h2>
        <div id="results-message" class="message"></div>
        <div class="form-group search-form" style="margin-bottom: 20px;">
            <div class="input-group">
                <label for="exam-id" class="input-group-addon">Select Exam</label>
                <select id="exam-id" name="exam_id" class="form-control">
                    <option value="">-- Select Exam --</option>
                    <?php
                    $exams = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exams");
                    foreach ($exams as $exam) {
                        echo "<option value='" . esc_attr($exam->id) . "'>" . esc_html($exam->name) . " (" . esc_html($exam->exam_date) . ")</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div id="results-content">
            <div class="alert alert-info">Please select an exam to view and manage results.</div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const ajaxNonce = '<?php echo wp_create_nonce('su_p_exam_nonce'); ?>';

        function loadResults(examId) {
            if (!examId) {
                $('#results-content').html('<div class="alert alert-info">Please select an exam to view and manage results.</div>');
                return;
            }
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'su_p_fetch_exam_results',
                    exam_id: examId,
                    nonce: ajaxNonce
                },
                beforeSend: function() {
                    $('#results-content').html('<div class="loading">Loading...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        $('#results-content').html(response.data.html);
                        setupExportButtons(response.data.results_data);
                    } else {
                        $('#results-content').html('<div class="alert alert-danger">Error: ' + (response.data?.message || 'No results found') + '</div>');
                    }
                }
            });
        }

        function setupExportButtons(resultsData) {
            const $exportTools = $('<div class="actions" id="export-tools" style="margin-bottom: 10px;"></div>');
            $exportTools.html(`
                <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i> CSV</button>
                <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i> Generate All Results PDF</button>
                <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i> Excel</button>
            `);
            $('#results-content').prepend($exportTools);

            $exportTools.find('.export-csv').on('click', () => exportToCSV(resultsData));
            $exportTools.find('.export-pdf').on('click', () => generateFullPDF(resultsData));
            $exportTools.find('.export-excel').on('click', () => exportToExcel(resultsData));
        }

        function exportToCSV(data) {
            const headers = ['Student ID', 'Student Name', ...data.subjects.map(s => `${s.subject_name} (${s.max_marks})`)];
            const csvRows = [headers.join(',')];
            data.students.forEach(student => {
                const values = [student.student_id, student.name, ...data.subjects.map(s => student.marks[s.id] || '')].map(value => `"${value}"`);
                csvRows.push(values.join(','));
            });
            const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `exam_results_${$('#exam-id').val()}.csv`;
            link.click();
        }

        function generateFullPDF(data) {
            const form = $('<form>', {
                method: 'POST',
                action: ajaxUrl,
                css: { display: 'none' }
            }).append(
                $('<input>', { type: 'hidden', name: 'action', value: 'su_p_generate_all_marksheets' }),
                $('<input>', { type: 'hidden', name: 'exam_id', value: $('#exam-id').val() }),
                $('<input>', { type: 'hidden', name: 'nonce', value: ajaxNonce })
            );
            $('body').append(form);
            form.submit();
            form.remove();
        }

        function exportToExcel(data) {
            const worksheetData = data.students.map(student => {
                const row = { 'Student ID': student.student_id, 'Student Name': student.name };
                data.subjects.forEach(s => {
                    row[`${s.subject_name} (${s.max_marks})`] = student.marks[s.id] || '';
                });
                return row;
            });
            const worksheet = XLSX.utils.json_to_sheet(worksheetData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Results');
            XLSX.writeFile(workbook, `exam_results_${$('#exam-id').val()}.xlsx`);
        }

        $('#exam-id').on('change', function() {
            loadResults($(this).val());
        });

        $('#results-content').on('click', '#submit-grades', function() {
            const formData = new FormData($('#results-form')[0]);
            formData.append('action', 'su_p_submit_exam_results');
            formData.append('nonce', ajaxNonce);
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const $message = $('#results-message');
                    if (response.success) {
                        $message.css({'background': 'var(--success-bg)', 'color': 'var(--success-text)'}).text(response.data);
                        loadResults($('#exam-id').val());
                        setTimeout(() => $message.text(''), 4000);
                    } else {
                        $message.css({'background': 'var(--error-bg)', 'color': 'var(--error-text)'}).text('Error: ' + (response.data?.message || 'Unknown error'));
                        setTimeout(() => $message.text(''), 4000);
                    }
                }
            });
        });

        $('#results-content').on('click', '.generate-pdf', function() {
            const examId = $('#exam-id').val();
            const studentId = $(this).data('student-id');
            const form = $('<form>', {
                method: 'POST',
                action: ajaxUrl,
                css: { display: 'none' }
            }).append(
                $('<input>', { type: 'hidden', name: 'action', value: 'su_p_generate_student_marksheet' }),
                $('<input>', { type: 'hidden', name: 'exam_id', value: examId }),
                $('<input>', { type: 'hidden', name: 'student_id', value: studentId }),
                $('<input>', { type: 'hidden', name: 'nonce', value: ajaxNonce })
            );
            $('body').append(form);
            form.submit();
            form.remove();
        });
    });
    </script>
    <style>
        .loading { text-align: center; padding: 20px; }
        .export-btn { margin-right: 10px; padding: 5px 10px; }
        .form-control { width: 300px; display: inline-block; }
        .input-group-addon { padding: 6px 12px; background: #f5f5f5; border: 1px solid #ccc; border-right: none; }
    </style>
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_su_p_fetch_exam_results', 'su_p_fetch_exam_results');
function su_p_fetch_exam_results() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;

    $exam_id = intval($_POST['exam_id'] ?? 0);
    $exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}exams WHERE id = %d", $exam_id));
    if (!$exam) {
        wp_send_json_error(['message' => 'Exam not found']);
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
        $exam_id
    ));
    if (empty($subjects)) {
        wp_send_json_error(['message' => 'No subjects defined for this exam']);
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.student_id, er.subject_id, er.marks, p.post_title 
         FROM {$wpdb->prefix}exam_results er 
         LEFT JOIN {$wpdb->prefix}postmeta pm 
             ON pm.meta_key = 'student_id' 
             AND pm.meta_value = er.student_id 
         LEFT JOIN {$wpdb->prefix}posts p 
             ON p.ID = pm.post_id 
             AND p.post_type = 'student' 
             AND p.post_status = 'publish' 
         WHERE er.exam_id = %d",
        $exam_id
    ), ARRAY_A);

    $student_marks = [];
    foreach ($results as $result) {
        $student_id = $result['student_id'];
        $student_name = $result['post_title'] ?: 'Unknown (' . esc_html($student_id) . ')';
        $student_marks[$student_id]['name'] = $student_name;
        $student_marks[$student_id]['marks'][$result['subject_id']] = $result['marks'];
    }

    if (empty($student_marks)) {
        $students = $exam->class_id ? $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title 
             FROM {$wpdb->prefix}posts p 
             JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'student' 
             AND p.post_status = 'publish' 
             AND pm.meta_key = 'class_name' 
             AND pm.meta_value = %s",
            $exam->class_id
        )) : [];
        foreach ($students as $student) {
            $student_id = "STU-" . md5($student->ID);
            $student_marks[$student_id]['name'] = $student->post_title;
            $student_marks[$student_id]['marks'] = [];
        }
    }

    ob_start();
    ?>
    <h3>Grades for <?php echo esc_html($exam->name); ?></h3>
    <form id="results-form">
        <div class="table-responsive">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <?php foreach ($subjects as $subject): ?>
                            <th><?php echo esc_html($subject->subject_name); ?> (<?php echo $subject->max_marks; ?>)</th>
                        <?php endforeach; ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($student_marks)) {
                        echo '<tr><td colspan="' . (count($subjects) + 2) . '">No students found for this exam.</td></tr>';
                    } else {
                        foreach ($student_marks as $student_id => $data) {
                            echo '<tr>';
                            echo '<td>' . esc_html($data['name']) . ' (' . esc_html($student_id) . ')</td>';
                            foreach ($subjects as $subject) {
                                $marks = $data['marks'][$subject->id] ?? '';
                                echo '<td><input type="number" name="marks[' . esc_attr($student_id) . '][' . $subject->id . ']" class="regular-text" value="' . esc_attr($marks) . '" step="0.01" min="0" max="' . $subject->max_marks . '"></td>';
                            }
                            echo '<td><button type="button" class="button generate-pdf" data-student-id="' . esc_attr($student_id) . '">Download Mark Sheet</button></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
        <p class="submit"><button type="button" id="submit-grades" class="button-primary">Submit Grades</button></p>
    </form>
    <?php
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'results_data' => [
            'exam_name' => $exam->name,
            'subjects' => array_map(function($s) { return ['id' => $s->id, 'subject_name' => $s->subject_name, 'max_marks' => $s->max_marks]; }, $subjects),
            'students' => array_map(function($id, $data) { return ['student_id' => $id, 'name' => $data['name'], 'marks' => $data['marks']]; }, array_keys($student_marks), $student_marks)
        ]
    ]);
}

add_action('wp_ajax_su_p_submit_exam_results', 'su_p_submit_exam_results');
function su_p_submit_exam_results() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;

    $exam_id = intval($_POST['exam_id'] ?? 0);
    $exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}exams WHERE id = %d", $exam_id));
    if (!$exam) {
        wp_send_json_error(['message' => 'Exam not found']);
    }

    $success = false;
    if (isset($_POST['marks'])) {
        foreach ($_POST['marks'] as $student_id => $subjects) {
            $student_id = sanitize_text_field($student_id);
            foreach ($subjects as $subject_id => $marks) {
                $subject_id = intval($subject_id);
                $marks = floatval($marks);
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}exam_results WHERE exam_id = %d AND subject_id = %d AND student_id = %s",
                    $exam_id, $subject_id, $student_id
                ));
                if ($existing) {
                    $updated = $wpdb->update(
                        $wpdb->prefix . 'exam_results',
                        ['marks' => $marks],
                        ['id' => $existing],
                        ['%f'],
                        ['%d']
                    );
                    if ($updated !== false) $success = true;
                } else {
                    $inserted = $wpdb->insert($wpdb->prefix . 'exam_results', [
                        'exam_id' => $exam_id,
                        'subject_id' => $subject_id,
                        'student_id' => $student_id,
                        'marks' => $marks,
                        'education_center_id' => $exam->education_center_id,
                    ]);
                    if ($inserted) $success = true;
                }
            }
        }
    }

    if ($success) {
        wp_send_json_success('Grades submitted successfully');
    } else {
        wp_send_json_error(['message' => 'Failed to submit grades']);
    }
}

add_action('wp_ajax_su_p_generate_student_marksheet', 'su_p_generate_student_marksheet');
function su_p_generate_student_marksheet() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;

    $dompdf_path =  dirname(__FILE__) .  '/../assets/exam/dompdf/autoload.inc.php';// Adjust path as needed
    if (!file_exists($dompdf_path)) {
        wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
    }
    require_once $dompdf_path;

    $exam_id = intval($_POST['exam_id'] ?? 0);
    $student_id = sanitize_text_field($_POST['student_id'] ?? '');
    if (!$exam_id || !$student_id) {
        wp_die('Invalid exam or student ID');
    }

    $exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}exams WHERE id = %d", $exam_id));
    if (!$exam) {
        wp_die('Exam not found');
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
        $exam_id
    ));
    if (empty($subjects)) {
        wp_die('No subjects found');
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.student_id, er.subject_id, er.marks, p.post_title 
         FROM {$wpdb->prefix}exam_results er 
         LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.meta_key = 'student_id' AND pm.meta_value = er.student_id 
         LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id AND p.post_type = 'student' AND p.post_status = 'publish' 
         WHERE er.exam_id = %d AND er.student_id = %s",
        $exam_id, $student_id
    ), ARRAY_A);

    $student_marks = [];
    foreach ($results as $result) {
        $sid = $result['student_id'];
        $student_marks[$sid]['id'] = $sid;
        $student_marks[$sid]['name'] = $result['post_title'] ?: 'Unknown (' . esc_html($sid) . ')';
        $student_marks[$sid]['marks'][$result['subject_id']] = $result['marks'];
    }

    if (!isset($student_marks[$student_id])) {
        // Fallback student lookup from your sample
        $student = get_posts([
            'post_type' => 'student',
            'meta_query' => [
                ['key' => 'student_id', 'value' => $student_id, 'compare' => '=']
            ],
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ]);

        if (empty($student)) {
            $student = $wpdb->get_row($wpdb->prepare(
                "SELECT p.* 
                 FROM {$wpdb->prefix}posts p 
                 JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                 WHERE pm.meta_key = 'student_id' 
                 AND pm.meta_value = %s 
                 AND p.post_status = 'publish'",
                $student_id
            ));
        }

        if ($student) {
            $student_marks[$student_id] = [
                'id' => $student_id,
                'name' => $student->post_title ?: 'Unknown (' . esc_html($student_id) . ')',
                'marks' => [] // Populate with empty marks if no results yet
            ];
        } else {
            wp_die('Student not found');
        }
    }

    $center = get_posts([
        'post_type' => 'educational-center',
        'meta_key' => 'educational_center_id',
        'meta_value' => $exam->education_center_id,
        'posts_per_page' => 1
    ]);
    $institute_name = $center ? $center[0]->post_title : 'Unknown Center';
    $logo = get_field('institute_logo', $center[0]->ID ?? 0);
    $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
    $logo_path = $logo_url ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $logo_url))) : '';

    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('chroot', ABSPATH);
    $options->set('tempDir', sys_get_temp_dir());
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf\Dompdf($options);

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { margin: 15mm; border: 2px solid #1a2b5f; padding: 10mm; }
        body { font-family: Helvetica, sans-serif; font-size: 12pt; color: #333; line-height: 1.4; }
        .container { width: 100%; padding: 15px; background-color: #fff; }
        .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #1a2b5f; margin-bottom: 20px; }
        .header img { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; object-fit: cover; }
        .header h1 { font-size: 24pt; color: #1a2b5f; margin: 0; text-transform: uppercase; }
        .header .subtitle { font-size: 14pt; color: #666; margin: 5px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table td { padding: 8px; border: 1px solid #ddd; }
        .details-table .label { font-weight: bold; color: #1a2b5f; width: 150px; background-color: #f9f9f9; }
        .marks-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .marks-table th, .marks-table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .marks-table th { background-color: #1a2b5f; color: white; font-weight: bold; }
        .marks-table tr:nth-child(even) { background-color: #f9f9f9; }
        .marks-table .total-row td { font-weight: bold; background-color: #e6f0fa; }
        .footer { text-align: center; margin-top: 30px; font-size: 10pt; color: #666; }
        .footer .generated-by { font-size: 8pt; margin-top: 10px; color: #999; }
        .signature { margin-top: 20px; }
    </style></head><body><div class="container">';

    $html .= generate_su_p_marksheet_content($exam, $subjects, $student_marks[$student_id], $logo_path, $institute_name);
    $html .= '</div></body></html>';

    try {
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="marksheet_' . $exam_id . '_' . $student_id . '.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: no-cache');

        echo $pdf_content;
        flush();
        exit;
    } catch (Exception $e) {
        error_log('Dompdf Error: ' . $e->getMessage());
        wp_die('Error generating PDF: ' . $e->getMessage());
    }
}

add_action('wp_ajax_su_p_generate_all_marksheets', 'su_p_generate_all_marksheets');
function su_p_generate_all_marksheets() {
    check_ajax_referer('su_p_exam_nonce', 'nonce');
    global $wpdb;

    $dompdf_path = dirname(__FILE__) .  '/../assets/exam/dompdf/autoload.inc.php'; // Adjust path as needed
    if (!file_exists($dompdf_path)) {
        wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
    }
    require_once $dompdf_path;

    $exam_id = intval($_POST['exam_id'] ?? 0);
    if (!$exam_id) {
        wp_die('Invalid exam ID');
    }

    $exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}exams WHERE id = %d", $exam_id));
    if (!$exam) {
        wp_die('Exam not found');
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
        $exam_id
    ));
    if (empty($subjects)) {
        wp_die('No subjects found');
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.student_id, er.subject_id, er.marks, p.post_title 
         FROM {$wpdb->prefix}exam_results er 
         LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.meta_key = 'student_id' AND pm.meta_value = er.student_id 
         LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id AND p.post_type = 'student' AND p.post_status = 'publish' 
         WHERE er.exam_id = %d",
        $exam_id
    ), ARRAY_A);

    $student_marks = [];
    foreach ($results as $result) {
        $sid = $result['student_id'];
        $student_marks[$sid]['id'] = $sid;
        $student_marks[$sid]['name'] = $result['post_title'] ?: 'Unknown (' . esc_html($sid) . ')';
        $student_marks[$sid]['marks'][$result['subject_id']] = $result['marks'];
    }

    if (empty($student_marks)) {
        wp_die('No results found');
    }

    $center = get_posts([
        'post_type' => 'educational-center',
        'meta_key' => 'educational_center_id',
        'meta_value' => $exam->education_center_id,
        'posts_per_page' => 1
    ]);
    $institute_name = $center ? $center[0]->post_title : 'Unknown Center';
    $logo = get_field('institute_logo', $center[0]->ID ?? 0);
    $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
    $logo_path = $logo_url ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $logo_url))) : '';

    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('chroot', ABSPATH);
    $options->set('tempDir', sys_get_temp_dir());
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf\Dompdf($options);

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { margin: 15mm; border: 2px solid #1a2b5f; padding: 10mm; }
        body { font-family: Helvetica, sans-serif; font-size: 12pt; color: #333; line-height: 1.4; }
        .container { width: 100%; padding: 15px; background-color: #fff; }
        .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #1a2b5f; margin-bottom: 20px; }
        .header img { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; object-fit: cover; }
        .header h1 { font-size: 24pt; color: #1a2b5f; margin: 0; text-transform: uppercase; }
        .header .subtitle { font-size: 14pt; color: #666; margin: 5px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table td { padding: 8px; border: 1px solid #ddd; }
        .details-table .label { font-weight: bold; color: #1a2b5f; width: 150px; background-color: #f9f9f9; }
        .marks-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .marks-table th, .marks-table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .marks-table th { background-color: #1a2b5f; color: white; font-weight: bold; }
        .marks-table tr:nth-child(even) { background-color: #f9f9f9; }
        .marks-table .total-row td { font-weight: bold; background-color: #e6f0fa; }
        .footer { text-align: center; margin-top: 30px; font-size: 10pt; color: #666; }
        .footer .generated-by { font-size: 8pt; margin-top: 10px; color: #999; }
        .signature { margin-top: 20px; }
        .page-break { page-break-before: always; }
    </style></head><body>';

    $first = true;
    foreach ($student_marks as $sid => $data) {
        if (!$first) $html .= '<div class="page-break"></div>';
        $html .= '<div class="container">';
        $html .= generate_su_p_marksheet_content($exam, $subjects, $data, $logo_path, $institute_name);
        $html .= '</div>';
        $first = false;
    }

    $html .= '</body></html>';

    try {
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="marksheet_' . $exam_id . '_all.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: no-cache');

        echo $pdf_content;
        flush();
        exit;
    } catch (Exception $e) {
        error_log('Dompdf Error: ' . $e->getMessage());
        wp_die('Error generating PDF: ' . $e->getMessage());
    }
}

function generate_su_p_marksheet_content($exam, $subjects, $student_data, $logo_path, $institute_name) {
    global $wpdb;

    $student_id = $student_data['id'] ?? 'Unknown';
    $student = get_posts([
        'post_type' => 'student',
        'meta_query' => [
            ['key' => 'student_id', 'value' => $student_id, 'compare' => '=']
        ],
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ]);

    if (empty($student)) {
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT p.* 
             FROM {$wpdb->prefix}posts p 
             JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
             WHERE pm.meta_key = 'student_id' 
             AND pm.meta_value = %s 
             AND p.post_status = 'publish'",
            $student_id
        ));
    }

    if ($student) {
        $student_name = get_field('student_name', $student->ID) ?: $student->post_title ?: 'N/A';
        $student_roll = get_field('roll_number', $student->ID) ?: 'N/A';
        $student_dob = get_field('date_of_birth', $student->ID) ?: 'N/A';
        $student_gender = get_field('gender', $student->ID) ?: 'N/A';
    } else {
        $student_name = $student_data['name'] ?? 'Unknown (' . esc_html($student_id) . ')';
        $student_roll = 'N/A';
        $student_dob = 'N/A';
        $student_gender = 'N/A';
        error_log("Student not found for ID: $student_id");
    }

    $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

    $html = '
        <div class="header">
            ' . ($logo_path ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">' . esc_html($exam->name) . ' Marksheet</div>
        </div>
        <table class="details-table">
            <tr>
                <td class="label">Student Name</td>
                <td>' . esc_html($student_name) . '</td>
            </tr>
            <tr>
                <td class="label">Student ID</td>
                <td>' . esc_html($student_id) . '</td>
            </tr>
            <tr>
                <td class="label">Roll Number</td>
                <td>' . esc_html($student_roll) . '</td>
            </tr>
            <tr>
                <td class="label">Date of Birth</td>
                <td>' . esc_html($student_dob) . '</td>
            </tr>
            <tr>
                <td class="label">Gender</td>
                <td>' . esc_html($student_gender) . '</td>
            </tr>
            <tr>
                <td class="label">Examination</td>
                <td>' . esc_html($exam->name) . '</td>
            </tr>
            <tr>
                <td class="label">Class/Course</td>
                <td>' . esc_html($exam->class_id ?: 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Academic Year</td>
                <td>' . $exam_year . '</td>
            </tr>
            <tr>
                <td class="label">Exam Date</td>
                <td>' . esc_html($exam->exam_date ?: 'N/A') . '</td>
            </tr>
        </table>
        <table class="marks-table">
            <tr>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Maximum Marks</th>
            </tr>';

    $total = 0;
    $max_total = 0;
    $marks = $student_data['marks'] ?? [];
    foreach ($subjects as $subject) {
        $mark = $marks[$subject->id] ?? '-';
        $total += is_numeric($mark) ? floatval($mark) : 0;
        $max_total += floatval($subject->max_marks);
        $html .= '
            <tr>
                <td>' . esc_html($subject->subject_name) . '</td>
                <td>' . (is_numeric($mark) ? $mark : '-') . '</td>
                <td>' . $subject->max_marks . '</td>
            </tr>';
    }

    $percentage = $max_total > 0 ? round(($total / $max_total) * 100, 2) : 0;

    $html .= '
            <tr class="total-row">
                <td>Total</td>
                <td>' . $total . '</td>
                <td>' . $max_total . '</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Percentage</td>
                <td>' . $percentage . '%</td>
            </tr>
        </table>
        <div class="footer">
            <p>This is an official marksheet issued by ' . esc_html($institute_name) . '</p>
            <p>Generated on ' . date('Y-m-d') . '</p>
            <div class="signature">
                <p>___________________________</p>
                <p>Registrar / Authorized Signatory</p>
            </div>
            <div class="generated-by">Managed by Instituto Educational Center Management System</div>
        </div>';

    return $html;
}

//reports
$dompdf_path =  dirname(__FILE__) .  '/../assets/exam/dompdf/autoload.inc.php';
if (file_exists($dompdf_path)) {
    require_once $dompdf_path;
} else {
    wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
}

use Dompdf\Dompdf;
use Dompdf\Options;

function su_p_reports_dashboard_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();

    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return '<p>You must be logged in as an administrator to view this dashboard.</p>';
    }

    $centers = get_posts([
        'post_type' => 'educational-center',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
    if (empty($centers)) {
        return '<div class="alert alert-danger">No Educational Centers found.</div>';
    }

    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9 p-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Reports Dashboard</h3>
                        <div class="d-flex">
                            <select id="center-filter" class="form-select me-2" style="width: 200px;">
                                <option value="">All Centers</option>
                                <?php foreach ($centers as $center) {
                                    $center_id = get_field('educational_center_id', $center->ID) ?: $center->ID;
                                    echo '<option value="' . esc_attr($center_id) . '">' . esc_html($center->post_title) . '</option>';
                                } ?>
                            </select>
                            <input type="text" id="search-filter" class="form-control me-2" placeholder="Search exams...">
                        </div>
                    </div>
                    <div class="card-body">
                        <h4>Available Reports</h4>
                        <div id="exam-list-container">
                            <?php
                            // Initial load of exams (unfiltered)
                            $exams = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exams");
                            if (empty($exams)) {
                                echo '<div class="alert alert-info">No exams found to generate reports for.</div>';
                            } else {
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-bordered table-striped">';
                                echo '<thead><tr><th>Exam Name</th><th>Center</th><th>Average Score</th><th>Students</th><th>Actions</th></tr></thead>';
                                echo '<tbody>';
                                foreach ($exams as $exam) {
                                    $center = array_filter($centers, function($c) use ($exam) {
                                        return (get_field('educational_center_id', $c->ID) ?: $c->ID) == $exam->education_center_id;
                                    });
                                    $center_name = $center ? reset($center)->post_title : 'Unknown';
                                    $results = $wpdb->get_results($wpdb->prepare(
                                        "SELECT AVG(marks) as avg_marks, COUNT(DISTINCT student_id) as student_count 
                                         FROM {$wpdb->prefix}exam_results 
                                         WHERE exam_id = %d",
                                        $exam->id
                                    ));
                                    $avg_marks = $results[0]->avg_marks ? round($results[0]->avg_marks, 2) : 'N/A';
                                    $student_count = $results[0]->student_count ?: 0;
                                    echo '<tr>';
                                    echo '<td>' . esc_html($exam->name) . '</td>';
                                    echo '<td>' . esc_html($center_name) . '</td>';
                                    echo '<td>' . $avg_marks . '</td>';
                                    echo '<td>' . $student_count . '</td>';
                                    echo '<td>';
                                    echo '<button class="btn btn-sm btn-primary me-2 generate-report-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('su_p_generate_report_' . $exam->id) . '">Generate Report</button>';
                                    echo '<button class="btn btn-sm btn-info view-exam-details-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('su_p_view_exam_details_' . $exam->id) . '">View Details</button>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table></div>';
                            }
                            ?>
                        </div>
                        <div id="exam-details-container" class="mt-4" style="display: none;"></div>
                        <div id="edu-loader" class="edu-loader" style="display: none;">
                            <div class="edu-loader-container">
                                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .edu-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .edu-loader-container {
            text-align: center;
        }
        .edu-loader-png {
            width: 100px; /* Adjust size as needed */
            height: auto;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        function loadExams(centerFilter, searchQuery) {
            var $container = $('#exam-list-container');
            var $loader = $('#edu-loader');

            $loader.show();
            $container.hide();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_filter_exams',
                    center_filter: centerFilter,
                    search_query: searchQuery,
                    nonce: '<?php echo wp_create_nonce('su_p_filter_exams'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data).show();
                    } else {
                        $container.html('<div class="alert alert-danger">' + response.data + '</div>').show();
                    }
                    $loader.hide();
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading exams.</div>').show();
                    $loader.hide();
                }
            });
        }

        // Filter exams on center or search change
        $('#center-filter').on('change', function() {
            var centerFilter = $(this).val();
            var searchQuery = $('#search-filter').val();
            loadExams(centerFilter, searchQuery);
        });

        $('#search-filter').on('input', function() {
            var centerFilter = $('#center-filter').val();
            var searchQuery = $(this).val();
            loadExams(centerFilter, searchQuery);
        });

        // Event delegation for Generate Report buttons
        $(document).on('click', '.generate-report-btn', function(e) {
            e.preventDefault();
            var $button = $(this);
            var examId = $button.data('exam-id');
            var nonce = $button.data('nonce');
            var $loader = $('#edu-loader');

            $button.prop('disabled', true).text('Generating...');
            $loader.show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_generate_report_preview',
                    exam_id: examId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=su_p_download_report_pdf&exam_id=' + examId + '&nonce=' + nonce;
                    } else {
                        alert('Error: ' + response.data);
                    }
                    $button.prop('disabled', false).text('Generate Report');
                    $loader.hide();
                },
                error: function() {
                    alert('An error occurred while generating the report.');
                    $button.prop('disabled', false).text('Generate Report');
                    $loader.hide();
                }
            });
        });

        // Event delegation for View Details buttons
        $(document).on('click', '.view-exam-details-btn', function(e) {
            e.preventDefault();
            var $button = $(this);
            var examId = $button.data('exam-id');
            var nonce = $button.data('nonce');
            var $container = $('#exam-details-container');
            var $loader = $('#edu-loader');

            $button.prop('disabled', true).text('Loading...');
            $loader.show();
            $container.hide().html('');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_view_exam_details',
                    exam_id: examId,
                    nonce: nonce,
                    center_filter: $('#center-filter').val(),
                    search_query: $('#search-filter').val(),
                    student_search: ''
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data).slideDown();
                    } else {
                        $container.html('<div class="alert alert-danger">' + response.data + '</div>').slideDown();
                    }
                    $button.prop('disabled', false).text('View Details');
                    $loader.hide();
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading exam details.</div>').slideDown();
                    $button.prop('disabled', false).text('View Details');
                    $loader.hide();
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
// AJAX Handler for Filtering Exams
add_action('wp_ajax_su_p_filter_exams', 'su_p_filter_exams');
function su_p_filter_exams() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'su_p_filter_exams')) {
        wp_send_json_error('Security check failed.');
    }

    global $wpdb;
    $center_filter = isset($_POST['center_filter']) ? sanitize_text_field($_POST['center_filter']) : '';
    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';

    $exams_query = "SELECT * FROM {$wpdb->prefix}exams WHERE 1=1";
    $query_args = [];
    if ($center_filter) {
        $exams_query .= " AND education_center_id = %s";
        $query_args[] = $center_filter;
    }
    if ($search_query) {
        $exams_query .= " AND name LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($search_query) . '%';
    }

    if (!empty($query_args)) {
        $exams = $wpdb->get_results($wpdb->prepare($exam_query, $exam_id));
    } else {
        $exams = $wpdb->get_results($exams_query);
    }

    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1, 'post_status' => 'publish']);

    ob_start();
    if (empty($exams)) {
        echo '<div class="alert alert-info">' . ($search_query || $center_filter ? 'No exams found matching your filters.' : 'No exams found to generate reports for.') . '</div>';
    } else {
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th>Exam Name</th><th>Center</th><th>Average Score</th><th>Students</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($exams as $exam) {
            $center = array_filter($centers, function($c) use ($exam) {
                return (get_field('educational_center_id', $c->ID) ?: $c->ID) == $exam->education_center_id;
            });
            $center_name = $center ? reset($center)->post_title : 'Unknown';
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT AVG(marks) as avg_marks, COUNT(DISTINCT student_id) as student_count 
                 FROM {$wpdb->prefix}exam_results 
                 WHERE exam_id = %d",
                $exam->id
            ));
            $avg_marks = $results[0]->avg_marks ? round($results[0]->avg_marks, 2) : 'N/A';
            $student_count = $results[0]->student_count ?: 0;
            echo '<tr>';
            echo '<td>' . esc_html($exam->name) . '</td>';
            echo '<td>' . esc_html($center_name) . '</td>';
            echo '<td>' . $avg_marks . '</td>';
            echo '<td>' . $student_count . '</td>';
            echo '<td>';
            echo '<button class="btn btn-sm btn-primary me-2 generate-report-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('su_p_generate_report_' . $exam->id) . '">Generate Report</button>';
            echo '<button class="btn btn-sm btn-info view-exam-details-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('su_p_view_exam_details_' . $exam->id) . '">View Details</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
    $html = ob_get_clean();
    wp_send_json_success($html);
}
add_shortcode('su_p_reports_dashboard', 'su_p_reports_dashboard_shortcode');
// AJAX Handler for Exam Details
add_action('wp_ajax_su_p_view_exam_details', 'su_p_view_exam_details');
function su_p_view_exam_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'su_p_view_exam_details_' . $_POST['exam_id'])) {
        wp_send_json_error('Security check failed.');
    }

    $exam_id = intval($_POST['exam_id']);
    $student_search = isset($_POST['student_search']) ? sanitize_text_field($_POST['student_search']) : '';
    $center_filter = isset($_POST['center_filter']) ? sanitize_text_field($_POST['center_filter']) : '';
    global $wpdb;

    $exam_query = "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d";
    if ($center_filter) {
        $exam_query .= " AND education_center_id = %s";
        $exam = $wpdb->get_row($wpdb->prepare($exam_query, $exam_id, $center_filter));
    } else {
        $exam = $wpdb->get_row($wpdb->prepare($exam_query, $exam_id));
    }

    if (!$exam) {
        wp_send_json_error('Exam not found or does not match the selected center.');
    }

    $subjects = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d", $exam_id));
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.*, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d",
        $exam_id
    ), ARRAY_A);

    if (empty($results)) {
        wp_send_json_error('No results available for this exam.');
    }

    $student_marks = [];
    foreach ($results as $result) {
        $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
    }

    $centers = get_posts(['post_type' => 'educational-center', 'posts_per_page' => -1, 'post_status' => 'publish']);
    $center = array_filter($centers, function($c) use ($exam) {
        return (get_field('educational_center_id', $c->ID) ?: $c->ID) == $exam->education_center_id;
    });
    $institute_name = $center ? reset($center)->post_title : 'Unknown Center';
    $logo = get_field('institute_logo', reset($center)->ID ?? 0);
    $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
    $logo_path = $logo_url ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $logo_url))) : '';

    $filtered_results = array_filter($student_marks, function($data) use ($wpdb, $student_search) {
        if (!$student_search) return true;
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT post_title FROM {$wpdb->prefix}posts p 
             JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
             WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s 
             AND p.post_status = 'publish'",
            $data['student_id']
        ));
        $student_name = $student ? $student->post_title : 'Unknown (' . $data['student_id'] . ')';
        return stripos($student_name, $student_search) !== false;
    }, ARRAY_FILTER_USE_KEY);

    ob_start();
    ?>
    <hr class="my-4">
    <h4>Exam Details: <?php echo esc_html($exam->name); ?></h4>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <p><strong>Exam Name:</strong> <?php echo esc_html($exam->name); ?></p>
                    <p><strong>Exam Date:</strong> <?php echo esc_html($exam->exam_date ?: 'N/A'); ?></p>
                    <p><strong>Class/Course:</strong> <?php echo esc_html($exam->class_id ?: 'N/A'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <p><strong>Total Subjects:</strong> <?php echo count($subjects); ?></p>
                    <p><strong>Total Students:</strong> <?php echo count($student_marks); ?></p>
                    <p><strong>Center:</strong> <?php echo esc_html($institute_name); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Performance Preview</h5>
        <div class="d-flex">
            <input type="text" id="student-search-input" class="form-control me-2" placeholder="Search students..." value="<?php echo esc_attr($student_search); ?>">
            <button id="student-search-btn" class="btn btn-primary" data-exam-id="<?php echo esc_attr($exam->id); ?>" data-nonce="<?php echo wp_create_nonce('su_p_view_exam_details_' . $exam->id); ?>" data-center="<?php echo esc_attr($center_filter); ?>">Search</button>
        </div>
    </div>
    <?php if (empty($filtered_results)) { ?>
        <div class="alert alert-info">No students found matching your search.</div>
    <?php } else { ?>
        <div class="row">
            <?php
            foreach ($filtered_results as $student_id => $data) {
                $student = $wpdb->get_row($wpdb->prepare(
                    "SELECT post_title FROM {$wpdb->prefix}posts p 
                     JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                     WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s 
                     AND p.post_status = 'publish'",
                    $student_id
                ));
                $student_name = $student ? $student->post_title : 'Unknown (' . esc_html($student_id) . ')';
                $avg_marks = !empty($student_marks[$student_id]['marks']) ? array_sum($student_marks[$student_id]['marks']) / count($student_marks[$student_id]['marks']) : 0;
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo esc_html($student_name); ?></h5>
                            <p class="card-text">Average Marks: <span class="text-primary"><?php echo round($avg_marks, 2); ?></span></p>
                            <button class="btn btn-sm btn-info view-student-details-btn" data-student-id="<?php echo esc_attr($student_id); ?>" data-exam-id="<?php echo esc_attr($exam->id); ?>" data-nonce="<?php echo wp_create_nonce('su_p_view_student_details_' . $student_id); ?>">View Details</button>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    <?php } ?>

    <div class="text-center mt-3">
        <button class="btn btn-primary generate-report-btn" data-exam-id="<?php echo esc_attr($exam->id); ?>" data-nonce="<?php echo wp_create_nonce('su_p_generate_report_' . $exam->id); ?>">Download PDF Report</button>
        <button class="btn btn-secondary close-exam-details ms-2">Close</button>
    </div>

    <div id="student-details-container" class="mt-4" style="display: none;"></div>

    <script>
    jQuery(document).ready(function($) {
        $('#student-search-btn').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var examId = $button.data('exam-id');
            var nonce = $button.data('nonce');
            var centerFilter = $button.data('center');
            var studentSearch = $('#student-search-input').val();
            var $container = $('#exam-details-container');
            var $loader = $('#loader');

            $button.prop('disabled', true).text('Searching...');
            $loader.show();
            $container.hide().html('');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_view_exam_details',
                    exam_id: examId,
                    nonce: nonce,
                    center_filter: centerFilter,
                    student_search: studentSearch
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data).slideDown();
                    } else {
                        $container.html('<div class="alert alert-danger">' + response.data + '</div>').slideDown();
                    }
                    $button.prop('disabled', false).text('Search');
                    $loader.hide();
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading exam details.</div>').slideDown();
                    $button.prop('disabled', false).text('Search');
                    $loader.hide();
                }
            });
        });

        $(document).on('click', '.view-student-details-btn', function(e) {
            e.preventDefault();
            var $button = $(this);
            var studentId = $button.data('student-id');
            var examId = $button.data('exam-id');
            var nonce = $button.data('nonce');
            var $container = $('#student-details-container');
            var $loader = $('#loader');

            $button.prop('disabled', true).text('Loading...');
            $loader.show();
            $container.hide().html('');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_view_student_details',
                    exam_id: examId,
                    student_id: studentId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data).slideDown();
                    } else {
                        $container.html('<div class="alert alert-danger">' + response.data + '</div>').slideDown();
                    }
                    $button.prop('disabled', false).text('View Details');
                    $loader.hide();
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading student details.</div>').slideDown();
                    $button.prop('disabled', false).text('View Details');
                    $loader.hide();
                }
            });
        });

        $('.close-exam-details').on('click', function() {
            $('#exam-details-container').slideUp();
        });
    });
    </script>
    <?php
    $html = ob_get_clean();
    wp_send_json_success($html);
}
// AJAX Handler for Student Details
add_action('wp_ajax_su_p_view_student_details', 'su_p_view_student_details');
function su_p_view_student_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'su_p_view_student_details_' . $_POST['student_id'])) {
        wp_send_json_error('Security check failed.');
    }

    $exam_id = intval($_POST['exam_id']);
    $student_id = sanitize_text_field($_POST['student_id']);
    global $wpdb;

    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d",
        $exam_id
    ));
    if (!$exam) {
        wp_send_json_error('Exam not found.');
    }

    $detail_results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.marks, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d AND er.student_id = %s",
        $exam_id, $student_id
    ), ARRAY_A);

    if (empty($detail_results)) {
        wp_send_json_error('No details found for this student.');
    }

    $student = $wpdb->get_row($wpdb->prepare(
        "SELECT post_title FROM {$wpdb->prefix}posts p 
         JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
         WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s 
         AND p.post_status = 'publish'",
        $student_id
    ));
    $student_name = $student ? $student->post_title : 'Unknown (' . esc_html($student_id) . ')';

    $html = '<div class="card border-primary"><div class="card-header bg-primary text-white"><h5 class="card-title mb-0">Detailed Report: ' . esc_html($student_name) . ' (' . esc_html($exam->name) . ')</h5></div><div class="card-body">';
    $html .= '<ul class="list-group">';
    foreach ($detail_results as $result) {
        $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">' . esc_html($result['subject_name']) . '<span class="badge bg-primary rounded-pill">' . $result['marks'] . '</span></li>';
    }
    $html .= '</ul>';
    $html .= '<button class="btn btn-secondary mt-3 close-details">Close</button></div></div>';
    $html .= '<script>jQuery(".close-details").on("click", function() { jQuery("#student-details-container").slideUp(); });</script>';

    wp_send_json_success($html);
}
add_shortcode('su_p_reports_dashboard', 'su_p_reports_dashboard_shortcode');
function su_p_generate_detailed_pdf($exam, $subjects, $student_marks, $logo_path, $institute_name, $filtered_results = null) {
    global $wpdb;

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('chroot', ABSPATH);
    $options->set('tempDir', sys_get_temp_dir());
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { 
                margin: 10mm; 
                border: 4px solid #1a2b5f;
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
                max-width: 100%;
                padding: 15px;
                border: 1px solid #ccc;
                background-color: #fff;
                box-sizing: border-box;
            }
            .header {
                text-align: center;
                padding-bottom: 10px;
                border-bottom: 2px solid #1a2b5f;
                margin-bottom: 10px;
            }
            .header h1 {
                font-size: 18pt;
                color: #1a2b5f;
                margin: 0;
                text-transform: uppercase;
            }
            .header .subtitle {
                font-size: 12pt;
                color: #666;
                margin: 0;
            }
            .header img {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                margin-bottom: 4px;
                object-fit: cover;
            }
            .details-table, .marks-table {
                width: 100%;
                max-width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 11pt;
                table-layout: fixed;
            }
            .details-table td, .marks-table td, .marks-table th {
                padding: 6px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .details-table td.label {
                width: 25%;
                font-weight: bold;
                background-color: #f5f5f5;
                color: #1a2b5f;
            }
            .marks-table th, .marks-table td {
                width: auto;
                text-align: center;
            }
            .marks-table th {
                background-color: #1a2b5f;
                color: white;
                font-weight: bold;
            }
            .marks-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .marks-table .total-row td {
                font-weight: bold;
                background-color: #e6f0fa;
            }
            .footer {
                text-align: center;
                font-size: 9pt;
                color: #666;
                margin-top: 20px;
            }
            .report-details {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                background-color: #f9f9f9;
            }
            .report-details .detail-item {
                margin-bottom: 10px;
                display: flex;
            }
            .report-details .label {
                font-weight: bold;
                color: #1a2b5f;
                width: 150px;
                flex-shrink: 0;
            }
            .page-break {
                page-break-before: always;
            }
            @media print {
                .container {
                    width: 100%;
                    max-width: 100%;
                }
                .details-table, .marks-table {
                    width: 100%;
                    max-width: 100%;
                }
            }
        </style>
    </head>
    <body>';

    $html .= '
    <div class="container">
        <div class="header">
            ' . ($logo_path && file_exists(str_replace('file://', '', $logo_path)) ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">Exam Performance Report</div>
        </div>
        <div class="report-details">
            <div class="detail-item">
                <span class="label">Exam Name:</span>
                <span>' . esc_html($exam->name) . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Exam Date:</span>
                <span>' . esc_html($exam->exam_date ?: 'N/A') . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Class/Course:</span>
                <span>' . esc_html($exam->class_id ?: 'N/A') . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Total Subjects:</span>
                <span>' . count($subjects) . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Total Students:</span>
                <span>' . count($student_marks) . '</span>
            </div>
        </div>
    </div>';

    $students_to_process = $filtered_results ?: array_keys($student_marks);
    if (empty($students_to_process)) {
        $html .= '<div class="container"><p>No students available to report.</p></div>';
    } else {
        foreach ($students_to_process as $student_id) {
            if (!is_scalar($student_id)) {
                error_log('Invalid student_id: ' . print_r($student_id, true));
                continue;
            }

            $html .= '<div class="page-break"></div>';

            $student = get_posts([
                'post_type' => 'student',
                'meta_query' => [
                    ['key' => 'student_id', 'value' => $student_id, 'compare' => '=']
                ],
                'posts_per_page' => 1,
                'post_status' => 'publish'
            ]);

            if (empty($student)) {
                $student = $wpdb->get_row($wpdb->prepare(
                    "SELECT p.* 
                     FROM {$wpdb->prefix}posts p 
                     JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                     WHERE pm.meta_key = 'student_id' 
                     AND pm.meta_value = %s 
                     AND p.post_status = 'publish'",
                    $student_id
                ));
            }

            $student_post = $student ? (is_array($student) ? $student[0] : $student) : null;
            $student_name = $student_post ? $student_post->post_title : 'Unknown (' . esc_html($student_id) . ')';
            $student_roll = $student_post ? (get_field('roll_number', $student_post->ID) ?: 'N/A') : 'N/A';
            $student_dob = $student_post ? (get_field('date_of_birth', $student_post->ID) ?: 'N/A') : 'N/A';
            $student_gender = $student_post ? (get_field('gender', $student_post->ID) ?: 'N/A') : 'N/A';
            $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

            $html .= '
            <div class="container">
                <div class="header">
                    ' . ($logo_path && file_exists(str_replace('file://', '', $logo_path)) ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
                    <h1>' . esc_html($institute_name) . '</h1>
                    <div class="subtitle">' . esc_html($exam->name) . '</div>
                </div>
                <table class="details-table">
                    <tr><td class="label">Student Name</td><td>' . esc_html($student_name) . '</td></tr>
                    <tr><td class="label">Student ID</td><td>' . esc_html($student_id) . '</td></tr>
                    <tr><td class="label">Roll Number</td><td>' . esc_html($student_roll) . '</td></tr>
                    <tr><td class="label">Date of Birth</td><td>' . esc_html($student_dob) . '</td></tr>
                    <tr><td class="label">Gender</td><td>' . esc_html($student_gender) . '</td></tr>
                    <tr><td class="label">Examination</td><td>' . esc_html($exam->name) . '</td></tr>
                    <tr><td class="label">Class/Course</td><td>' . esc_html($exam->class_id ?: 'N/A') . '</td></tr>
                    <tr><td class="label">Academic Year</td><td>' . $exam_year . '</td></tr>
                    <tr><td class="label">Exam Date</td><td>' . esc_html($exam->exam_date ?: 'N/A') . '</td></tr>
                </table>
                <table class="marks-table">
                    <tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th></tr>';

            $total = 0;
            $max_total = 0;
            foreach ($subjects as $subject) {
                $mark = isset($student_marks[$student_id]['marks'][$subject->id]) ? $student_marks[$student_id]['marks'][$subject->id] : '-';
                $total += is_numeric($mark) ? floatval($mark) : 0;
                $max_total += floatval($subject->max_marks);
                $html .= '
                    <tr>
                        <td>' . esc_html($subject->subject_name) . '</td>
                        <td>' . $mark . '</td>
                        <td>' . $subject->max_marks . '</td>
                    </tr>';
            }

            $percentage = $max_total > 0 ? round(($total / $max_total) * 100, 2) : 0;

            $html .= '
                    <tr class="total-row">
                        <td>Total</td>
                        <td>' . $total . '</td>
                        <td>' . $max_total . '</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2">Percentage</td>
                        <td>' . $percentage . '%</td>
                    </tr>
                </table>
                <div class="footer">
                    <p>This is an Online Generated Report issued by ' . esc_html($institute_name) . '</p>
                    <p>Generated on ' . date('Y-m-d') . '</p>
                    <div class="signature">
                        <p>___________________________</p>
                        <p>Registrar / Authorized Signatory</p>
                    </div>
                    <div class="generated-by">
                        Managed by Instituto Educational Center Management System
                    </div>
                </div>
            </div>';
        }
    }

    $html .= '</body></html>';

    try {
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="exam_report_' . $exam->id . '_detailed.pdf"');
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

add_action('wp_ajax_su_p_generate_report_preview', 'su_p_generate_report_preview');
function su_p_generate_report_preview() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'su_p_generate_report_' . $_POST['exam_id'])) {
        wp_send_json_error('Security check failed.');
    }

    $exam_id = intval($_POST['exam_id']);
    global $wpdb;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d",
        $exam_id
    ));
    if (!$exam) {
        wp_send_json_error('Exam not found.');
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.*, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d",
        $exam_id
    ), ARRAY_A);

    if (empty($results)) {
        wp_send_json_error('No results available for this exam.');
    }

    wp_send_json_success('Report ready for download.');
}

add_action('wp_ajax_su_p_download_report_pdf', 'su_p_download_report_pdf');
function su_p_download_report_pdf() {
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'su_p_generate_report_' . $_GET['exam_id'])) {
        wp_die('Security check failed.');
    }

    $exam_id = intval($_GET['exam_id']);
    global $wpdb;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d",
        $exam_id
    ));
    if (!$exam) {
        wp_die('Exam not found.');
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
        $exam_id
    ));
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.*, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d",
        $exam_id
    ), ARRAY_A);

    if (empty($results)) {
        wp_die('No results available to generate a report.');
    }

    $student_marks = [];
    foreach ($results as $result) {
        $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
    }

    $centers = get_posts([
        'post_type' => 'educational-center',
        'meta_key' => 'educational_center_id',
        'meta_value' => $exam->education_center_id,
        'posts_per_page' => 1
    ]);
    $institute_name = $centers ? $centers[0]->post_title : 'Unknown Center';
    $logo = get_field('institute_logo', $centers[0]->ID ?? 0);
    $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
    $logo_path = $logo_url ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $logo_url))) : '';

    su_p_generate_detailed_pdf($exam, $subjects, $student_marks, $logo_path, $institute_name);
}

add_action('wp_ajax_su_p_view_report_details', 'su_p_view_report_details');
function su_p_view_report_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'su_p_view_details_' . $_POST['exam_id'])) {
        wp_send_json_error('Security check failed.');
    }

    $exam_id = intval($_POST['exam_id']);
    global $wpdb;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d",
        $exam_id
    ));
    if (!$exam) {
        wp_send_json_error('Exam not found.');
    }

    $detail_results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.student_id, er.marks, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d",
        $exam_id
    ), ARRAY_A);

    if (empty($detail_results)) {
        wp_send_json_error('No detailed results found for this exam.');
    }

    $student_marks = [];
    foreach ($detail_results as $result) {
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT post_title FROM {$wpdb->prefix}posts p JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
             WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s AND p.post_status = 'publish'",
            $result['student_id']
        ));
        $student_name = $student ? $student->post_title : 'Unknown (' . esc_html($result['student_id']) . ')';
        $student_marks[$student_name][$result['subject_name']] = $result['marks'];
    }

    $html = '<div class="card border-primary"><div class="card-header bg-primary text-white"><h5 class="card-title mb-0">Detailed Report: ' . esc_html($exam->name) . '</h5></div><div class="card-body">';
    foreach ($student_marks as $student_name => $marks) {
        $html .= '<h6>' . esc_html($student_name) . '</h6><ul class="list-group mb-3">';
        foreach ($marks as $subject => $mark) {
            $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">' . esc_html($subject) . '<span class="badge bg-primary rounded-pill">' . $mark . '</span></li>';
        }
        $html .= '</ul>';
    }
    $html .= '<button class="btn btn-secondary mt-3 close-details">Close</button></div></div>';
    $html .= '<script>jQuery(".close-details").on("click", function() { jQuery("#report-details-container").slideUp(); });</script>';

    wp_send_json_success($html);
}

add_shortcode('su_p_reports_dashboard', 'su_p_reports_dashboard_shortcode');

//Library
// Super Admin Library Management Shortcode
function su_p_library_management_dashboard_shortcode() {
    // Check if user is logged in and is a super admin
    // if (!is_user_logged_in() || !is_super_admin()) {
    //     return '<p>You must be logged in as a Super Administrator to view this dashboard.</p>';
    // }

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #e6f7ff, #cce5ff); min-height: 100vh;">
        <div class="row">
            <div class="col-md-3 col-lg-2 p-4" style="background: #f8f9fa;">
                <h4 class="mb-4"><i class="bi bi-book me-2"></i>Super Admin Dashboard</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="#" class="nav-link active filter-link" data-filter="library-list">Library List</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link filter-link" data-filter="library-add">Add Book</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link filter-link" data-filter="library-transactions">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link filter-link" data-filter="library-overdue">Overdue Books</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-9 col-lg-10 p-4">
                <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
                        <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Library Management Dashboard</h3>
                        <div class="d-flex">
                            <select id="su-p-edu-center-filter" class="form-select me-2" style="width: 200px;">
                                <option value="">All Educational Centers</option>
                                <?php
                                $centers = get_educational_centers(); // Placeholder function
                                foreach ($centers as $center) {
                                    echo '<option value="' . esc_attr($center->id) . '">' . esc_html($center->name) . '</option>';
                                }
                                ?>
                            </select>
                            <input type="text" id="su-p-search-filter" class="form-control" placeholder="Search..." style="border-radius: 20px;">
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="su-p-dashboard-content">
                            <?php echo su_p_library_list_content(); ?>
                        </div>
                        <div id="edu-loader" class="edu-loader" style="display: none;">
                            <div class="edu-loader-container">
                                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .edu-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .edu-loader-container {
            text-align: center;
        }
        .edu-loader-png {
            width: 100px;
            height: auto;
        }
        .modal-content {
            background: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        .modal-close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var currentFilter = 'library-list';

        function loadDashboardContent(filter, eduCenterId, searchQuery) {
            var $container = $('#su-p-dashboard-content');
            var $loader = $('#edu-loader');

            $loader.show();
            $container.hide();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_library_filter',
                    filter: filter,
                    edu_center_id: eduCenterId,
                    search_query: searchQuery,
                    nonce: '<?php echo wp_create_nonce('su_p_library_filter'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data).show();
                    } else {
                        $container.html('<div class="alert alert-danger">' + response.data + '</div>').show();
                    }
                    $loader.hide();
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading content.</div>').show();
                    $loader.hide();
                }
            });
        }

        // Sidebar filter links
        $('.filter-link').on('click', function(e) {
            e.preventDefault();
            $('.filter-link').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            var eduCenterId = $('#su-p-edu-center-filter').val();
            var searchQuery = $('#su-p-search-filter').val();
            loadDashboardContent(currentFilter, eduCenterId, searchQuery);
        });

        // Educational center filter
        $('#su-p-edu-center-filter').on('change', function() {
            var eduCenterId = $(this).val();
            var searchQuery = $('#su-p-search-filter').val();
            loadDashboardContent(currentFilter, eduCenterId, searchQuery);
        });

        // Search filter
        $('#su-p-search-filter').on('input', function() {
            var eduCenterId = $('#su-p-edu-center-filter').val();
            var searchQuery = $(this).val();
            loadDashboardContent(currentFilter, eduCenterId, searchQuery);
        });

        // Event delegation for dynamic actions
        $(document).on('click', '.edit-book-btn', function() {
            var bookId = $(this).data('book-id');
            var nonce = $(this).data('nonce');
            loadDashboardContent('library-edit', '', '', { book_id: bookId, nonce: nonce });
        });

        $(document).on('click', '.delete-book-btn', function() {
            var bookId = $(this).data('book-id');
            var nonce = $(this).data('nonce');
            loadDashboardContent('library-delete', '', '', { book_id: bookId, nonce: nonce });
        });

        $(document).on('click', '.transact-book-btn', function() {
            var bookId = $(this).data('book-id');
            var nonce = $(this).data('nonce');
            loadDashboardContent('library-transaction', '', '', { book_id: bookId, nonce: nonce });
        });

        $(document).on('click', '.return-book-btn', function() {
            var transactionId = $(this).data('transaction-id');
            var bookId = $(this).data('book-id');
            var userId = $(this).data('user-id');
            var nonce = $(this).data('nonce');
            loadDashboardContent('library-return', '', '', { transaction_id: transactionId, book_id: bookId, user_id: userId, nonce: nonce });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('su_p_library_management_dashboard', 'su_p_library_management_dashboard_shortcode');

// Library List Content
function su_p_library_list_content($edu_center_id = '', $search_query = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'library';
    $query = "SELECT * FROM $table_name";
    $args = [];
    if ($edu_center_id) {
        $query .= " WHERE education_center_id = %s";
        $args[] = $edu_center_id;
    }
    if ($search_query) {
        $query .= $edu_center_id ? " AND" : " WHERE";
        $query .= " (title LIKE %s OR author LIKE %s OR isbn LIKE %s)";
        $args[] = '%' . $wpdb->esc_like($search_query) . '%';
        $args[] = '%' . $wpdb->esc_like($search_query) . '%';
        $args[] = '%' . $wpdb->esc_like($search_query) . '%';
    }
    $books = $wpdb->get_results($wpdb->prepare($query, $args));

    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th>ID</th>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Center ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($books)) {
                    echo '<tr><td colspan="8" class="text-center py-4">No books found.</td></tr>';
                } else {
                    foreach ($books as $book) {
                        echo '<tr>';
                        echo '<td>' . esc_html($book->book_id) . '</td>';
                        echo '<td>' . esc_html($book->isbn) . '</td>';
                        echo '<td>' . esc_html($book->title) . '</td>';
                        echo '<td>' . esc_html($book->author) . '</td>';
                        echo '<td>' . esc_html($book->quantity) . '</td>';
                        echo '<td>' . ($book->available < 1 ? '<span class="badge bg-danger">Out</span>' : esc_html($book->available)) . '</td>';
                        echo '<td>' . esc_html($book->education_center_id) . '</td>';
                        echo '<td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary transact-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('transact_book_' . $book->book_id) . '">Transact</button>
                                <button class="btn btn-sm btn-warning edit-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('edit_book_' . $book->book_id) . '">Edit</button>
                                <button class="btn btn-sm btn-danger delete-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('delete_book_' . $book->book_id) . '">Delete</button>
                            </div>
                        </td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// AJAX Handler for Filters
add_action('wp_ajax_su_p_library_filter', 'su_p_library_filter');
function su_p_library_filter() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'su_p_library_filter')) {
        wp_send_json_error('Security check failed.');
    }

    $filter = sanitize_text_field($_POST['filter']);
    $edu_center_id = sanitize_text_field($_POST['edu_center_id']);
    $search_query = sanitize_text_field($_POST['search_query']);
    $extra_data = isset($_POST['extra']) ? $_POST['extra'] : [];

    switch ($filter) {
        case 'library-list':
            $html = su_p_library_list_content($edu_center_id, $search_query);
            break;
        case 'library-add':
            $html = su_p_library_add_content($edu_center_id);
            break;
        case 'library-edit':
            $book_id = sanitize_text_field($extra_data['book_id']);
            $nonce = sanitize_text_field($extra_data['nonce']);
            $html = su_p_library_edit_content($book_id, $nonce);
            break;
        case 'library-delete':
            $book_id = sanitize_text_field($extra_data['book_id']);
            $nonce = sanitize_text_field($extra_data['nonce']);
            $html = su_p_library_delete_content($book_id, $nonce);
            break;
        case 'library-transaction':
            $book_id = sanitize_text_field($extra_data['book_id']);
            $nonce = sanitize_text_field($extra_data['nonce']);
            $html = su_p_library_transaction_content($book_id, $nonce);
            break;
        case 'library-return':
            $transaction_id = sanitize_text_field($extra_data['transaction_id']);
            $book_id = sanitize_text_field($extra_data['book_id']);
            $user_id = sanitize_text_field($extra_data['user_id']);
            $nonce = sanitize_text_field($extra_data['nonce']);
            $html = su_p_library_return_content($transaction_id, $book_id, $user_id, $nonce);
            break;
        case 'library-transactions':
            $html = su_p_library_transactions_content($edu_center_id, $search_query);
            break;
        case 'library-overdue':
            $html = su_p_library_overdue_content($edu_center_id);
            break;
        default:
            $html = su_p_library_list_content($edu_center_id, $search_query);
    }

    wp_send_json_success($html);
}

// Library Add Content
function su_p_library_add_content($edu_center_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'library';
    $new_book_id = generate_unique_id($wpdb, $table_name, 'BOOK-', $edu_center_id); // Assuming this generates a unique ID
    $message = is_wp_error($new_book_id) ? '<div class="alert alert-danger">' . esc_html($new_book_id->get_error_message()) . '</div>' : '';

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 800px; margin: 0 auto; border: 3px solid #28a745; background: #f8fff8;">
        <div class="card-header bg-success text-white text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-book-fill me-2"></i>Add New Book</h3>
        </div>
        <div class="card-body p-4">
            <?php echo $message; ?>
            <form id="su-p-add-library-form" class="needs-validation" novalidate>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="book_id" class="form-label fw-bold">Book ID</label>
                        <input type="text" name="book_id" id="book_id" class="form-control" value="<?php echo esc_attr($new_book_id); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="edu_center_id" class="form-label fw-bold">Educational Center</label>
                        <select name="education_center_id" id="edu_center_id" class="form-control" required>
                            <option value="">Select Center</option>
                            <?php
                            $centers = get_educational_centers();
                            foreach ($centers as $center) {
                                echo '<option value="' . esc_attr($center->id) . '">' . esc_html($center->name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="isbn" class="form-label fw-bold">ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" placeholder="Enter ISBN">
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label fw-bold">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label fw-bold">Author</label>
                        <input type="text" name="author" id="author" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                    </div>
                </div>
                <?php wp_nonce_field('library_nonce', 'nonce'); ?>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg">Add Book</button>
                    <button type="button" class="btn btn-outline-secondary btn-lg ms-2 back-to-list">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#su-p-add-library-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $loader = $('#edu-loader');
            var formData = new FormData(this);
            formData.append('action', 'su_p_add_library_book');

            $loader.show();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $form.before('<div class="alert alert-success">Book added successfully!</div>');
                        setTimeout(function() {
                            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
                        }, 1500);
                    } else {
                        $form.before('<div class="alert alert-danger">' + (response.data.message || 'Error adding book') + '</div>');
                    }
                    $loader.hide();
                },
                error: function() {
                    $form.before('<div class="alert alert-danger">An error occurred.</div>');
                    $loader.hide();
                }
            });
        });

        $('.back-to-list').on('click', function() {
            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Library Edit Content
function su_p_library_edit_content($book_id, $nonce) {
    global $wpdb;
    if (!wp_verify_nonce($nonce, 'edit_book_' . $book_id)) {
        return '<div class="alert alert-danger">Invalid security token.</div>';
    }

    $book = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s", $book_id));
    if (!$book) {
        return '<div class="alert alert-danger">Book not found.</div>';
    }

    ob_start();
    ?>
    <div class="modal-content">
        <span class="modal-close back-to-list">×</span>
        <h5 class="bg-warning text-dark p-3 mb-3" style="border-radius: 8px 8px 0 0;">Edit Library Book</h5>
        <form id="su-p-edit-library-form" class="needs-validation" novalidate>
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="isbn" class="form-label fw-bold">ISBN</label>
                    <input type="text" name="isbn" id="isbn" class="form-control" value="<?php echo esc_attr($book->isbn); ?>">
                </div>
                <div class="col-md-6">
                    <label for="title" class="form-label fw-bold">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo esc_attr($book->title); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="author" class="form-label fw-bold">Author</label>
                    <input type="text" name="author" id="author" class="form-control" value="<?php echo esc_attr($book->author); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="quantity" class="form-label fw-bold">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo esc_attr($book->quantity); ?>" min="0" required>
                </div>
                <div class="col-md-6">
                    <label for="available" class="form-label fw-bold">Available</label>
                    <input type="number" name="available" id="available" class="form-control" value="<?php echo esc_attr($book->available); ?>" min="0" max="<?php echo esc_attr($book->quantity); ?>" required>
                </div>
            </div>
            <input type="hidden" name="book_id" value="<?php echo esc_attr($book_id); ?>">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('library_edit_nonce'); ?>">
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary back-to-list me-2">Close</button>
                <button type="submit" class="btn btn-warning">Update Book</button>
            </div>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#su-p-edit-library-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $loader = $('#edu-loader');
            var formData = new FormData(this);
            formData.append('action', 'su_p_edit_library_book');

            $loader.show();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $form.before('<div class="alert alert-success">Book updated successfully!</div>');
                        setTimeout(function() {
                            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
                        }, 1500);
                    } else {
                        $form.before('<div class="alert alert-danger">' + (response.data.message || 'Error updating book') + '</div>');
                    }
                    $loader.hide();
                },
                error: function() {
                    $form.before('<div class="alert alert-danger">An error occurred.</div>');
                    $loader.hide();
                }
            });
        });

        $('.back-to-list').on('click', function() {
            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Library Delete Content
function su_p_library_delete_content($book_id, $nonce) {
    global $wpdb;
    if (!wp_verify_nonce($nonce, 'delete_book_' . $book_id)) {
        return '<div class="alert alert-danger">Invalid security token.</div>';
    }

    $book = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s", $book_id));
    if (!$book) {
        return '<div class="alert alert-danger">Book not found.</div>';
    }

    ob_start();
    ?>
    <div class="modal-content">
        <span class="modal-close back-to-list">×</span>
        <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
        <div>
            <p>Are you sure you want to delete the following book?</p>
            <ul>
                <li><strong>ID:</strong> <?php echo esc_html($book->book_id); ?></li>
                <li><strong>ISBN:</strong> <?php echo esc_html($book->isbn); ?></li>
                <li><strong>Title:</strong> <?php echo esc_html($book->title); ?></li>
                <li><strong>Author:</strong> <?php echo esc_html($book->author); ?></li>
                <li><strong>Quantity:</strong> <?php echo esc_html($book->quantity); ?></li>
                <li><strong>Available:</strong> <?php echo esc_html($book->available); ?></li>
            </ul>
        </div>
        <div class="mt-3 text-end">
            <button type="button" class="btn btn-secondary back-to-list me-2">Cancel</button>
            <button type="button" class="btn btn-danger confirm-delete-btn" data-book-id="<?php echo esc_attr($book_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">Delete</button>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('.confirm-delete-btn').on('click', function() {
            var $button = $(this);
            var bookId = $button.data('book-id');
            var nonce = $button.data('nonce');
            var $loader = $('#edu-loader');

            $button.prop('disabled', true).text('Deleting...');
            $loader.show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_delete_library_book',
                    book_id: bookId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.parent().before('<div class="alert alert-success">Book deleted successfully!</div>');
                        setTimeout(function() {
                            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
                        }, 1500);
                    } else {
                        $button.parent().before('<div class="alert alert-danger">' + (response.data.message || 'Error deleting book') + '</div>');
                        $button.prop('disabled', false).text('Delete');
                    }
                    $loader.hide();
                },
                error: function() {
                    $button.parent().before('<div class="alert alert-danger">An error occurred.</div>');
                    $button.prop('disabled', false).text('Delete');
                    $loader.hide();
                }
            });
        });

        $('.back-to-list').on('click', function() {
            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Library Transaction Content
function su_p_library_transaction_content($book_id, $nonce) {
    global $wpdb;
    if (!wp_verify_nonce($nonce, 'transact_book_' . $book_id)) {
        return '<div class="alert alert-danger">Invalid security token.</div>';
    }

    $book = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s", $book_id));
    if (!$book) {
        return '<div class="alert alert-danger">Book not found.</div>';
    }

    $students = get_posts([
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            ['key' => 'educational_center_id', 'value' => $book->education_center_id, 'compare' => '=']
        ]
    ]);

    $staff = $wpdb->get_results($wpdb->prepare(
        "SELECT staff_id, name FROM {$wpdb->prefix}staff WHERE education_center_id = %s",
        $book->education_center_id
    ));

    ob_start();
    ?>
    <div class="modal-content">
        <span class="modal-close back-to-list">×</span>
        <h5 class="bg-info text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Process Transaction</h5>
        <form id="su-p-library-transaction-form" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="user_select" class="form-label fw-bold">Select User (Optional)</label>
                <select name="user_select" id="user_select" class="form-select select2-transact" style="width: 100%;">
                    <option value="" selected>Select a student or staff member</option>
                    <optgroup label="Students">
                        <?php foreach ($students as $student) {
                            $student_id = get_field('student_id', $student->ID);
                            if ($student_id) {
                                echo '<option value="' . esc_attr($student_id) . '" data-type="Student">' . esc_html($student_id . ' - ' . $student->post_title) . '</option>';
                            }
                        } ?>
                    </optgroup>
                    <optgroup label="Staff">
                        <?php foreach ($staff as $staff_member) {
                            echo '<option value="' . esc_attr($staff_member->staff_id) . '" data-type="Staff">' . esc_html($staff_member->staff_id . ' - ' . $staff_member->name) . '</option>';
                        } ?>
                    </optgroup>
                </select>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label fw-bold">User ID</label>
                <input type="text" name="user_id" id="user_id" class="form-control" placeholder="Enter User ID or select from above" required>
            </div>
            <div class="mb-3">
                <label for="user_type" class="form-label fw-bold">User Type</label>
                <select name="user_type" id="user_type" class="form-control" required>
                    <option value="" disabled selected>Select user type</option>
                    <option value="Student">Student</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label fw-bold">Due Date</label>
                <input type="datetime-local" name="due_date" id="due_date" class="form-control" min="<?php echo date('Y-m-d\TH:i', current_time('timestamp')); ?>" required>
            </div>
            <input type="hidden" name="book_id" value="<?php echo esc_attr($book_id); ?>">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('library_transaction_nonce'); ?>">
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary back-to-list me-2">Close</button>
                <button type="submit" class="btn btn-info">Process</button>
            </div>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#user_select').select2({
            placeholder: 'Search for a student or staff member...',
            allowClear: true,
            width: '100%'
        });

        $('#user_select').on('change', function() {
            var userId = $(this).val();
            if (userId) {
                var $selectedOption = $(this).find(':selected');
                var userType = $selectedOption.data('type');
                $('#user_id').val(userId);
                $('#user_type').val(userType);
            } else {
                $('#user_id').val('');
                $('#user_type').val('');
            }
        });

        $('#user_id').on('input', function() {
            $('#user_select').val(null).trigger('change');
        });

        $('#su-p-library-transaction-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $loader = $('#edu-loader');
            var formData = new FormData(this);
            formData.append('action', 'su_p_process_library_transaction');

            $loader.show();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $form.before('<div class="alert alert-success">Transaction processed successfully!</div>');
                        setTimeout(function() {
                            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
                        }, 1500);
                    } else {
                        $form.before('<div class="alert alert-danger">' + (response.data.message || 'Error processing transaction') + '</div>');
                    }
                    $loader.hide();
                },
                error: function() {
                    $form.before('<div class="alert alert-danger">An error occurred.</div>');
                    $loader.hide();
                }
            });
        });

        $('.back-to-list').on('click', function() {
            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Library Return Content
function su_p_library_return_content($transaction_id, $book_id, $user_id, $nonce) {
    global $wpdb;
    if (!wp_verify_nonce($nonce, 'return_book_' . $transaction_id)) {
        return '<div class="alert alert-danger">Invalid security token.</div>';
    }

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';
    $transaction = $wpdb->get_row($wpdb->prepare(
        "SELECT t.*, l.title 
         FROM $trans_table t 
         JOIN $library_table l ON t.book_id = l.book_id 
         WHERE t.transaction_id = %d AND t.book_id = %s AND t.user_id = %s AND t.return_date IS NULL",
        $transaction_id, $book_id, $user_id
    ));

    if (!$transaction) {
        return '<div class="alert alert-danger">Transaction not found or already returned.</div>';
    }

    ob_start();
    ?>
    <div class="modal-content">
        <span class="modal-close back-to-list">×</span>
        <h5 class="bg-primary text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Return</h5>
        <div>
            <p>Are you sure you want to return the following book?</p>
            <ul>
                <li><strong>Book ID:</strong> <?php echo esc_html($transaction->book_id); ?></li>
                <li><strong>Title:</strong> <?php echo esc_html($transaction->title); ?></li>
                <li><strong>User ID:</strong> <?php echo esc_html($transaction->user_id); ?></li>
                <li><strong>User Type:</strong> <?php echo esc_html($transaction->user_type); ?></li>
                <li><strong>Issue Date:</strong> <?php echo esc_html($transaction->issue_date); ?></li>
                <li><strong>Due Date:</strong> <?php echo esc_html($transaction->due_date); ?></li>
            </ul>
        </div>
        <div class="mt-3 text-end">
            <button type="button" class="btn btn-secondary back-to-list me-2">Cancel</button>
            <button type="button" class="btn btn-primary confirm-return-btn" 
                    data-transaction-id="<?php echo esc_attr($transaction_id); ?>" 
                    data-book-id="<?php echo esc_attr($book_id); ?>" 
                    data-user-id="<?php echo esc_attr($user_id); ?>" 
                    data-nonce="<?php echo esc_attr($nonce); ?>">Return Book</button>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('.confirm-return-btn').on('click', function() {
            var $button = $(this);
            var transactionId = $button.data('transaction-id');
            var bookId = $button.data('book-id');
            var userId = $button.data('user-id');
            var nonce = $button.data('nonce');
            var $loader = $('#edu-loader');

            $button.prop('disabled', true).text('Returning...');
            $loader.show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'su_p_process_book_return',
                    transaction_id: transactionId,
                    book_id: bookId,
                    user_id: userId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.parent().before('<div class="alert alert-success">Book returned successfully!</div>');
                        setTimeout(function() {
                            loadDashboardContent('library-transactions', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
                        }, 1500);
                    } else {
                        $button.parent().before('<div class="alert alert-danger">' + (response.data.message || 'Error returning book') + '</div>');
                        $button.prop('disabled', false).text('Return Book');
                    }
                    $loader.hide();
                },
                error: function() {
                    $button.parent().before('<div class="alert alert-danger">An error occurred.</div>');
                    $button.prop('disabled', false).text('Return Book');
                    $loader.hide();
                }
            });
        });

        $('.back-to-list').on('click', function() {
            loadDashboardContent('library-transactions', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Library Transactions Content
function su_p_library_transactions_content($edu_center_id, $search_query = '') {
    global $wpdb;
    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';
    $query = "SELECT t.*, l.title 
              FROM $trans_table t 
              JOIN $library_table l ON t.book_id = l.book_id";
    $args = [];
    if ($edu_center_id) {
        $query .= " WHERE l.education_center_id = %s";
        $args[] = $edu_center_id;
    }
    if ($search_query) {
        $query .= $edu_center_id ? " AND" : " WHERE";
        $query .= " (t.book_id LIKE %s OR t.user_id LIKE %s OR l.title LIKE %s)";
        $args[] = '%' . $wpdb->esc_like($search_query) . '%';
        $args[] = '%' . $wpdb->esc_like($search_query) . '%';
        $args[] = '%' . $wpdb->esc_like($search_query) . '%';
    }
    $query .= " ORDER BY t.issue_date DESC";
    $transactions = $wpdb->get_results($wpdb->prepare($query, $args));

    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead style="background: #e6f3ff;">
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>User ID</th>
                    <th>User Type</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Fine</th>
                    <th>Center ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($transactions)) {
                    echo '<tr><td colspan="10" class="text-center py-4">No transactions recorded.</td></tr>';
                } else {
                    foreach ($transactions as $trans) {
                        $is_overdue = !$trans->return_date && strtotime($trans->due_date) < current_time('timestamp');
                        $days_overdue = $is_overdue ? max(0, (current_time('timestamp') - strtotime($trans->due_date)) / (60 * 60 * 24)) : 0;
                        $potential_fine = $days_overdue * 0.50;

                        echo '<tr>';
                        echo '<td>' . esc_html($trans->book_id) . '</td>';
                        echo '<td>' . esc_html($trans->title) . '</td>';
                        echo '<td>' . esc_html($trans->user_id) . '</td>';
                        echo '<td>' . esc_html($trans->user_type) . '</td>';
                        echo '<td>' . esc_html($trans->issue_date) . '</td>';
                        echo '<td>' . esc_html($trans->due_date) . '</td>';
                        echo '<td>' . ($trans->return_date ? esc_html($trans->return_date) : '<span class="badge bg-warning">Pending</span>') . '</td>';
                        echo '<td>' . ($trans->return_date ? number_format($trans->fine, 2) : ($is_overdue ? number_format($potential_fine, 2) . ' (Pending)' : '0.00')) . '</td>';
                        echo '<td>' . esc_html($trans->education_center_id) . '</td>';
                        echo '<td>';
                        if (!$trans->return_date) {
                            echo '<button class="btn btn-sm btn-primary return-book-btn" 
                                    data-transaction-id="' . esc_attr($trans->transaction_id) . '" 
                                    data-book-id="' . esc_attr($trans->book_id) . '" 
                                    data-user-id="' . esc_attr($trans->user_id) . '" 
                                    data-nonce="' . wp_create_nonce('return_book_' . $trans->transaction_id) . '">Return</button>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Library Overdue Content
function su_p_library_overdue_content($edu_center_id) {
    global $wpdb;
    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';
    $query = "SELECT t.*, l.title 
              FROM $trans_table t 
              JOIN $library_table l ON t.book_id = l.book_id 
              WHERE t.return_date IS NULL AND t.due_date < %s";
    $args = [current_time('mysql')];
    if ($edu_center_id) {
        $query .= " AND l.education_center_id = %s";
        $args[] = $edu_center_id;
    }
    $overdue = $wpdb->get_results($wpdb->prepare($query, $args));

    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead style="background: #ffe6e6;">
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>User ID</th>
                    <th>User Type</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Days Overdue</th>
                    <th>Fine</th>
                    <th>Center ID</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($overdue)) {
                    echo '<tr><td colspan="9" class="text-center py-4">No overdue books.</td></tr>';
                } else {
                    foreach ($overdue as $item) {
                        $days_overdue = max(0, (strtotime(current_time('mysql')) - strtotime($item->due_date)) / (60 * 60 * 24));
                        $fine = $days_overdue * 0.50;
                        echo '<tr>';
                        echo '<td>' . esc_html($item->book_id) . '</td>';
                        echo '<td>' . esc_html($item->title) . '</td>';
                        echo '<td>' . esc_html($item->user_id) . '</td>';
                        echo '<td>' . esc_html($item->user_type) . '</td>';
                        echo '<td>' . esc_html($item->issue_date) . '</td>';
                        echo '<td>' . esc_html($item->due_date) . '</td>';
                        echo '<td>' . esc_html($days_overdue) . '</td>';
                        echo '<td>' . number_format($fine, 2) . '</td>';
                        echo '<td>' . esc_html($item->education_center_id) . '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-outline-danger mt-3 back-to-list">Back to Library</button>
    <script>
    jQuery(document).ready(function($) {
        $('.back-to-list').on('click', function() {
            loadDashboardContent('library-list', $('#su-p-edu-center-filter').val(), $('#su-p-search-filter').val());
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handlers
add_action('wp_ajax_su_p_add_library_book', 'su_p_add_library_book');
function su_p_add_library_book() {
    global $wpdb;
    check_ajax_referer('library_nonce', 'nonce');

    $table_name = $wpdb->prefix . 'library';
    $book_id = sanitize_text_field($_POST['book_id']);
    $education_center_id = sanitize_text_field($_POST['education_center_id']);
    $isbn = sanitize_text_field($_POST['isbn']);
    $title = sanitize_text_field($_POST['title']);
    $author = sanitize_text_field($_POST['author']);
    $quantity = intval($_POST['quantity']);

    if (empty($book_id) || empty($education_center_id) || empty($title) || empty($author) || $quantity < 0) {
        wp_send_json_error(['message' => 'Invalid input data']);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if ($exists > 0) {
        wp_send_json_error(['message' => 'Book ID already exists for this center']);
    }

    $wpdb->insert(
        $table_name,
        [
            'book_id' => $book_id,
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'quantity' => $quantity,
            'available' => $quantity,
            'education_center_id' => $education_center_id
        ],
        ['%s', '%s', '%s', '%s', '%d', '%d', '%s']
    );

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Failed to add book: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book added successfully']);
}

add_action('wp_ajax_su_p_edit_library_book', 'su_p_edit_library_book');
function su_p_edit_library_book() {
    global $wpdb;
    check_ajax_referer('library_edit_nonce', 'nonce');

    $table_name = $wpdb->prefix . 'library';
    $book_id = sanitize_text_field($_POST['book_id']);
    $isbn = sanitize_text_field($_POST['isbn']);
    $title = sanitize_text_field($_POST['title']);
    $author = sanitize_text_field($_POST['author']);
    $quantity = intval($_POST['quantity']);
    $available = intval($_POST['available']);

    $updated = $wpdb->update(
        $table_name,
        [
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'quantity' => $quantity,
            'available' => $available
        ],
        ['book_id' => $book_id],
        ['%s', '%s', '%s', '%d', '%d'],
        ['%s']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to update book: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book updated successfully']);
}

add_action('wp_ajax_su_p_delete_library_book', 'su_p_delete_library_book');
function su_p_delete_library_book() {
    global $wpdb;
    $book_id = sanitize_text_field($_POST['book_id']);
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'delete_book_' . $book_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $deleted = $wpdb->delete(
        $wpdb->prefix . 'library',
        ['book_id' => $book_id],
        ['%s']
    );

    if ($deleted === false || $deleted === 0) {
        wp_send_json_error(['message' => 'Failed to delete book']);
    }

    wp_send_json_success(['message' => 'Book deleted successfully']);
}

add_action('wp_ajax_su_p_process_library_transaction', 'su_p_process_library_transaction');
function su_p_process_library_transaction() {
    global $wpdb;
    check_ajax_referer('library_transaction_nonce', 'nonce');

    $library_table = $wpdb->prefix . 'library';
    $trans_table = $wpdb->prefix . 'library_transactions';

    $book_id = sanitize_text_field($_POST['book_id']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_type = sanitize_text_field($_POST['user_type']);
    $due_date = sanitize_text_field($_POST['due_date']);

    if (empty($book_id) || empty($user_id) || empty($user_type) || empty($due_date)) {
        wp_send_json_error(['message' => 'Missing required fields']);
    }

    if (!in_array($user_type, ['Student', 'Staff'])) {
        wp_send_json_error(['message' => 'Invalid user type']);
    }

    $due_timestamp = strtotime($due_date);
    $current_timestamp = current_time('timestamp');
    if ($due_timestamp <= $current_timestamp) {
        wp_send_json_error(['message' => 'Due date must be in the future']);
    }
    $due_date_mysql = date('Y-m-d H:i:s', $due_timestamp);

    $book = $wpdb->get_row($wpdb->prepare("SELECT * FROM $library_table WHERE book_id = %s", $book_id));
    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    if ($book->available <= 0) {
        wp_send_json_error(['message' => 'No copies available to borrow']);
    }

    $result = $wpdb->insert(
        $trans_table,
        [
            'book_id' => $book_id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'issue_date' => current_time('mysql'),
            'due_date' => $due_date_mysql,
            'return_date' => null,
            'fine' => 0.00
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%f']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to record transaction: ' . $wpdb->last_error]);
    }

    $new_available = $book->available - 1;
    $update_result = $wpdb->update(
        $library_table,
        ['available' => $new_available],
        ['book_id' => $book_id],
        ['%d'],
        ['%s']
    );

    if ($update_result === false) {
        wp_send_json_error(['message' => 'Failed to update book availability: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book borrowed successfully']);
}

add_action('wp_ajax_su_p_process_book_return', 'su_p_process_book_return');
function su_p_process_book_return() {
    global $wpdb;
    check_ajax_referer('return_book_' . sanitize_text_field($_POST['transaction_id']), 'nonce');

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';

    $transaction_id = sanitize_text_field($_POST['transaction_id']);
    $book_id = sanitize_text_field($_POST['book_id']);
    $user_id = sanitize_text_field($_POST['user_id']);

    $trans = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $trans_table WHERE transaction_id = %d AND book_id = %s AND user_id = %s AND return_date IS NULL",
        $transaction_id, $book_id, $user_id
    ));

    if (!$trans) {
        wp_send_json_error(['message' => 'No active borrowing record found']);
    }

    $return_date = current_time('mysql');
    $days_overdue = max(0, (strtotime($return_date) - strtotime($trans->due_date)) / (60 * 60 * 24));
    $fine = $days_overdue * 0.50;

    $result = $wpdb->update(
        $trans_table,
        ['return_date' => $return_date, 'fine' => $fine],
        ['transaction_id' => $transaction_id],
        ['%s', '%f'],
        ['%d']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update transaction: ' . $wpdb->last_error]);
    }

    $book = $wpdb->get_row($wpdb->prepare("SELECT available FROM $library_table WHERE book_id = %s", $book_id));
    $new_available = $book->available + 1;

    $update_result = $wpdb->update(
        $library_table,
        ['available' => $new_available],
        ['book_id' => $book_id],
        ['%d'],
        ['%s']
    );

    if ($update_result === false) {
        wp_send_json_error(['message' => 'Failed to update book availability: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book returned successfully']);
}

// Placeholder function for educational centers
function get_educational_centers() {
    // Replace with actual logic to fetch educational centers
    return [
        (object) ['id' => 'center1', 'name' => 'Center 1'],
        (object) ['id' => 'center2', 'name' => 'Center 2'],
    ];
}




// Role & Permissions Management (Stub)
function render_su_p_roles() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Role & Permissions</div>
        <div class="card-body">
            <p>Role and permissions management to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Timetable Management (Stub)
function render_su_p_timetable() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Timetable Management</div>
        <div class="card-body">
            <p>Timetable supervision to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Notifications Management (Stub)
function render_su_p_notifications() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Notifications</div>
        <div class="card-body">
            <p>Notification broadcasting to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// System Settings (Stub)
function render_su_p_settings() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">System Settings</div>
        <div class="card-body">
            <p>Global system settings to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Audit Logs (Stub)
function render_su_p_audit_logs() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Audit Logs</div>
        <div class="card-body">
            <p>Audit log tracking to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Support & Troubleshooting (Stub)
function render_su_p_support() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Support & Troubleshooting</div>
        <div class="card-body">
            <p>Support ticket management to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Add su_p Capability
add_action('init', function() {
    $role = get_role('administrator');
    if ($role) {
        $role->add_cap('super_admin');
    }
});

// Enqueue Bootstrap and Font Awesome
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
});