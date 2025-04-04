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
        $active_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
        include(plugin_dir_path(__FILE__) . 'su-p-sidebar.php');
        ?>
        <div class="su_p-content-wrapper" style="flex: 1; padding: 20px;">
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
                case 'roles':
                    echo render_su_p_roles();
                    break;
                case 'classes':
                    echo render_su_p_classes();
                    break;
                case 'exams':
                    echo render_su_p_exams();
                    break;
                case 'attendance':
                    echo render_su_p_attendance();
                    break;
                case 'timetable':
                    echo render_su_p_timetable();
                    break;
                case 'reports':
                    echo render_su_p_reports();
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

// Class & Subject Management (Stub)
function render_su_p_classes() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Classes & Subjects</div>
        <div class="card-body">
            <p>Class and subject management across centers to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Exam Management (Stub)
function render_su_p_exams() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Exam Management</div>
        <div class="card-body">
            <p>Exam oversight and approval to be implemented.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Attendance Oversight (Stub)
function render_su_p_attendance() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Attendance Oversight</div>
        <div class="card-body">
            <p>Attendance monitoring across centers to be implemented.</p>
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

// Reports & Analytics (Stub)
function render_su_p_reports() {
    ob_start();
    ?>
    <div class="card shadow-sm" style="margin-top: 80px;">
        <div class="card-header bg-primary text-white">Reports & Analytics</div>
        <div class="card-body">
            <p>System-wide reports and analytics to be implemented.</p>
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