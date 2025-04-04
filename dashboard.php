<?php
// Shortcode to display educational center dashboard with sidebar
function institute_dashboard_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view your dashboard.</p>';
    }

    // Start buffering the output
    ob_start();

    // Fetch the logo and title for the sidebar and dashboard
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    // Query the Educational Center based on the admin_id
    $args = array(
        'post_type' => 'educational-center',
        'meta_query' => array(
            array(
                'key' => 'admin_id',
                'value' => $admin_id,
                'compare' => '='
            )
        )
    );

    $educational_center = new WP_Query($args);

    if ($educational_center->have_posts()) {
        $educational_center->the_post();
        $post_id = get_the_ID();
        $logo = get_field('institute_logo', $post_id);
        $title = get_the_title($post_id);
    } else {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    ?>

    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <div class="institute-dashboard-wrapper"> -->
            <?php
        $active_section = 'dashboard';
        echo render_admin_header(wp_get_current_user());
        include(plugin_dir_path(__FILE__) . 'assets/sidebar.php');
            ?>
        <!-- </div> -->
    <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
    <?php ob_start();
    ?>
    <div class="institute-dashboard-container">
        <!-- Institute Logo -->
        <div class="institute-logo-container">
            <?php if ($logo): ?>
                <div class="institute-logo" style="position: relative;">
                    <img id="institute-logo-image" src="<?php echo esc_url($logo['url']); ?>" alt="Institute Logo" style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover;">
                    <span class="edit-logo-icon" onclick="document.getElementById('logo-file-input').click();">&#128247;</span>
                </div>
            <?php else: ?>
                <div class="upload-logo-placeholder" onclick="document.getElementById('logo-file-input').click();">
                    <span class="upload-logo-icon">&#128247;</span>
                    <span class="upload-logo-text">Upload Logo</span>
                </div>
            <?php endif; ?>
        </div>

        <h2 style="text-transform:capitalize"><?php echo esc_html($title); ?></h2>

        <!-- Editable Fields -->
        <form method="POST" enctype="multipart/form-data">
            <label for="institute_name">Institute Name</label>
            <input type="text" name="institute_name" value="<?php echo esc_html($title); ?>" required />
<br>
            <label for="mobile_number">Mobile Number</label>
            <input type="text" name="mobile_number" value="<?php echo get_field('mobile_number', $post_id); ?>" required />
            <br>

            <label for="email_id">Email ID</label>
            <input type="email" name="email_id" value="<?php echo get_field('email_id', $post_id); ?>" required />
            <br>

            <!-- Editable Logo File Upload (hidden by default) -->
            <input type="file" name="institute_logo" accept="image/*" id="logo-file-input" style="display: none;" onchange="previewLogo(event)" />

            <input type="submit" name="update_center" value="Update Center" />
        </form>

        <?php
        // Handle form submission
        if (isset($_POST['update_center'])) {
            // Update the data
            $new_institute_name = sanitize_text_field($_POST['institute_name']);
            $post_data = array(
                'ID' => $post_id,
                'post_title' => $new_institute_name,
            );
            wp_update_post($post_data);

            // Update other fields
            update_field('mobile_number', sanitize_text_field($_POST['mobile_number']), $post_id);
            update_field('email_id', sanitize_email($_POST['email_id']), $post_id);

            // Handle logo upload
            if (isset($_FILES['institute_logo']) && $_FILES['institute_logo']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['institute_logo']['size'] > 1048576) {
                    echo '<p class="error-message">Logo size must be less than 1 MB.</p>';
                } else {
                    $file = $_FILES['institute_logo'];
                    $upload_dir = wp_upload_dir();
                    $target_file = $upload_dir['path'] . '/' . basename($file['name']);

                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        $file_type = wp_check_filetype($target_file);
                        $attachment = array(
                            'guid' => $upload_dir['url'] . '/' . basename($file['name']),
                            'post_mime_type' => $file_type['type'],
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        $attach_id = wp_insert_attachment($attachment, $target_file, $post_id);
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata($attach_id, $target_file);
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        update_field('institute_logo', $attach_id, $post_id);

                        echo "<script>
                            document.getElementById('institute-logo-image').src = '" . wp_get_attachment_url($attach_id) . "'; 
                        </script>";
                    }
                }
            }
            header("Refresh:0");
            echo '<p class="success-message">Educational Center updated successfully.</p>';
        }
        ?>
    </div>

    <?php
    return ob_get_clean();?>
    </div>
    </div>


    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('institute_dashboard', 'institute_dashboard_shortcode');

// Function to render the Dashboard section
// function render_dashboard_section($post_id, $logo, $title) {
   
// }



function render_admin_header($admin_user) {
    global $wpdb;

    // Ensure $admin_user is a WP_User object
    if (!($admin_user instanceof WP_User)) {
        $admin_user = wp_get_current_user();
    }
    $admin_id = $admin_user->user_login;
    // $user = wp_get_current_user();
    // $username = $user->user_login;
    // Query the Educational Center based on the admin_id
    $args = [
        'post_type' => 'educational-center',
        'meta_query' => [
            ['key' => 'admin_id', 'value' => $admin_id, 'compare' => '=']
        ],
        'posts_per_page' => 1
    ];
    $educational_center = new WP_Query($args);

    if (!$educational_center->have_posts()) {
        return '<div class="alert alert-danger">No Educational Center found for this Admin ID.</div>';
    }

    $educational_center->the_post();
    $post_id = get_the_ID();
    // $institute_name = get_the_title($post_id);
    // $logo_url = get_field('institute_logo', $post_id) ? esc_url(get_field('institute_logo', $post_id)['url']) : 'https://via.placeholder.com/150';
    $user_name = $admin_user->display_name;
    $user_email = $admin_user->user_email;
    $avatar_url = get_field('institute_logo', $post_id) ? esc_url(get_field('institute_logo', $post_id)['url']) : 'https://via.placeholder.com/150';

    wp_reset_postdata();

    $dashboard_link = esc_url(home_url('/institute-dashboard'));
    $profile_link = esc_url(home_url('/institute-dashboard?section=profile'));
    $communication_link = esc_url(home_url('/institute-dashboard?section=communication'));
    $notifications_link = esc_url(home_url('/institute-dashboard?section=notifications'));
    $settings_link = esc_url(home_url('/institute-dashboard?section=settings'));
    $logout_link =  get_secure_logout_url_by_role();
    $edu_center_id = $post_id;
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

    // Messages count and data
    $messages_table = $wpdb->prefix . 'aspire_messages';
    $messages_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $messages_table 
         WHERE education_center_id = %s 
         AND (receiver_id = %s OR receiver_id IN ('all', 'admins')) 
         AND status = 'sent' 
         AND sender_id != %s 
         AND timestamp > %s",
        $edu_center_id, $admin_id, $admin_id, $seven_days_ago
    ));

    $unread_messages = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $messages_table 
         WHERE education_center_id = %s 
         AND (receiver_id = %s OR receiver_id IN ('all', 'admins')) 
         AND status = 'sent' 
         AND sender_id != %s 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $edu_center_id, $admin_id, $admin_id, $seven_days_ago
    ));

    // Notifications count and data
    $announcements_table = $wpdb->prefix . 'aspire_announcements';
    $notifications_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $announcements_table 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'admins') 
         AND timestamp > %s",
        $edu_center_id, $seven_days_ago
    ));

    $unread_announcements = $wpdb->get_results($wpdb->prepare(
        "SELECT sender_id, message, timestamp 
         FROM $announcements_table 
         WHERE education_center_id = %s 
         AND receiver_id IN ('all', 'admins') 
         AND timestamp > %s 
         ORDER BY timestamp DESC 
         LIMIT 5",
        $edu_center_id, $seven_days_ago
    ));

    ob_start();
    ?>
    <header class="admin-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?php echo $dashboard_link; ?>" class="header-logo">
                <img decoding="async" class="logo-image" src="<?php echo plugin_dir_url(__FILE__) . 'logo instituto.jpg'; ?>">
                <span class="logo-text">Instituto</span>
             </a>
                <div class="header-search">
                    <input type="text" placeholder="Search Admin Panel" class="search-input" id="header-search-input" aria-label="Search">
                    <div class="search-dropdown" id="search-results">
                        <ul class="results-list" id="search-results-list"></ul>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <nav class="header-nav">
                    <a href="<?php echo $dashboard_link; ?>" class="nav-item active">Dashboard</a>
                    <a href="<?php echo esc_url(home_url('/institute-dashboard?section=students')); ?>" class="nav-item">Students</a>
                    <a href="<?php echo esc_url(home_url('/institute-dashboard?section=teachers')); ?>" class="nav-item">Teachers</a>
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
                                <li><a href="<?php echo esc_url(home_url('/institute-dashboard?section=reports')); ?>" class="dropdown-link">Reports</a></li>
                                <li><a href="<?php echo esc_url(home_url('/institute-dashboard?section=attendance')); ?>" class="dropdown-link">Attendance</a></li>
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
                            action: 'search_admin_sections',
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
                    action: 'aspire_admin_mark_messages_read',
                    nonce: '<?php echo wp_create_nonce('aspire_admin_header_nonce'); ?>'
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

// AJAX Handlers for Admin
add_action('wp_ajax_search_admin_sections', 'search_admin_sections_callback');
function search_admin_sections_callback() {
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $sections = [
        ['title' => 'Dashboard', 'url' => esc_url(home_url('/institute-dashboard')), 'keywords' => ['dashboard', 'home']],
        ['title' => 'Profile', 'url' => esc_url(home_url('/institute-dashboard?section=profile')), 'keywords' => ['profile', 'account']],
        ['title' => 'Students', 'url' => esc_url(home_url('/institute-dashboard?section=students')), 'keywords' => ['students', 'pupils']],
        ['title' => 'Teachers', 'url' => esc_url(home_url('/institute-dashboard?section=teachers')), 'keywords' => ['teachers', 'staff']],
        ['title' => 'Messages', 'url' => esc_url(home_url('/institute-dashboard?section=communication')), 'keywords' => ['messages', 'communication']],
        ['title' => 'Notifications', 'url' => esc_url(home_url('/institute-dashboard?section=notifications')), 'keywords' => ['notifications', 'announcements']],
        ['title' => 'Reports', 'url' => esc_url(home_url('/institute-dashboard?section=reports')), 'keywords' => ['reports', 'analytics']],
        ['title' => 'Attendance', 'url' => esc_url(home_url('/institute-dashboard?section=attendance')), 'keywords' => ['attendance', 'presence']]
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

// add_action('wp_ajax_aspire_admin_mark_messages_read', 'aspire_admin_mark_messages_read');
// function aspire_admin_mark_messages_read() {
//     check_ajax_referer('aspire_admin_header_nonce', 'nonce');

//     global $wpdb;
//     $user = wp_get_current_user();
//     $admin_id = $user->user_login;
//     $edu_center_id = get_posts([
//         'post_type' => 'educational-center',
//         'meta_query' => [['key' => 'admin_id', 'value' => $admin_id, 'compare' => '=']],
//         'posts_per_page' => 1
//     ])[0]->ID;
//     $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

//     $messages_table = $wpdb->prefix . 'aspire_messages';
//     $wpdb->query($wpdb->prepare(
//         "UPDATE $messages_table 
//          SET status = 'read' 
//          WHERE education_center_id = %s 
//          AND receiver_id = %s 
//          AND status = 'sent' 
//          AND timestamp > %s",
//         $edu_center_id, $admin_id, $seven_days_ago
//     ));

//     $wpdb->query($wpdb->prepare(
//         "UPDATE $messages_table 
//          SET status = 'read' 
//          WHERE education_center_id = %s 
//          AND receiver_id IN ('all', 'admins') 
//          AND status = 'sent' 
//          AND sender_id != %s 
//          AND timestamp > %s",
//         $edu_center_id, $admin_id, $seven_days_ago
//     ));

//     wp_send_json_success();
// }

// Enqueue jQuery
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], '3.6.0', true);
});

   
?>
