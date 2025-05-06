<?php
// Shortcode to display educational center dashboard with conditional sidebar and reusable expired message
// Reusable function to get educational center by admin ID
function get_educational_center_by_admin_id($admin_id) {
    global $wpdb;
    
    $edu_center_table = $wpdb->prefix . 'educational_center';
    $institute_admins_table = $wpdb->prefix . 'institute_admins';
    
    // Query to join educational_center and institute_admins tables
    $query = $wpdb->prepare(
        "SELECT ec.* 
         FROM $edu_center_table ec
         INNER JOIN $institute_admins_table ia 
         ON ec.educational_center_id = ia.education_center_id
         WHERE ia.institute_admin_id = %s",
        $admin_id
    );
    
    $educational_center = $wpdb->get_row($query);
    
    return $educational_center;
}

function aspire_institute_dashboard_shortcode() {
    global $wpdb;
    
    if (!function_exists('wp_get_current_user')) {
        return '<div class="alert alert-danger">Some Error occurred</div>';
    }

    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    if (!$current_user->ID) {
        return '<div class="alert alert-danger">Some Error occurred</div>';
    }

    // Use the reusable function to get the educational center
    $educational_center = get_educational_center_by_admin_id($admin_id);

    if (!$educational_center) {
        return '<div class="alert alert-warning">Educational Center not found. Please contact administration.</div>';
    }

    $educational_center_id = $educational_center->educational_center_id;
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #f0f8ff, #e6e6fa); min-height: 100vh;">
        <div class="row">
            <?php
            $active_section = $section;
            $active_action = $action;
            echo render_admin_header($current_user);
            
            if (!is_center_subscribed($educational_center_id)) {
                return render_subscription_expired_message($educational_center_id);
            }
            
            include plugin_dir_path(__FILE__) . 'assets/sidebar.php';
            ?>
            
            <div class="main-content">
                <?php
                ob_start();
                
                switch ($section) {
                    case 'overview':
                        echo render_institute_dashboard($current_user, $educational_center);
                        break;
                    
                    case 'subscription':
                        if ($action === 'renew-subscription') {
                            echo render_subscription_renewal_form($educational_center_id);
                        } elseif ($action === 'extend-subscription') {
                            echo render_subscription_extension_form($educational_center_id);
                        } else {
                            echo render_institute_subscription_management($educational_center_id);
                        }
                        break;
                    
                    default:
                        echo render_institute_dashboard($current_user, $educational_center);
                        break;
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Helper function to render institute dashboard
function render_institute_dashboard($current_user, $educational_center) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'educational_center';
    $educational_center_id = $educational_center->educational_center_id;
    global $wpdb;
    $data = $wpdb->get_results( 
        $wpdb->prepare( 
            "SELECT * FROM wp_educational_center WHERE educational_center_id = %s", 
            $educational_center_id 
        ) 
    );
    error_log('data='. $data);

        ob_start();
    ?>
    <div class="institute-dashboard-container">
        <div class="institute-logo-container">
            <?php if ($educational_center->institute_logo): ?>
                <div class="institute-logo" style="position: relative;">
                    <img id="institute-logo-image" src="<?php echo esc_url($educational_center->institute_logo); ?>" alt="Institute Logo" style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover;">
                    <span class="edit-logo-icon" onclick="document.getElementById('logo-file-input').click();">📷</span>
                </div>
            <?php else: ?>
                <div class="upload-logo-placeholder" onclick="document.getElementById('logo-file-input').click();">
                    <span class="upload-logo-icon">📷</span>
                    <span class="upload-logo-text">Upload Logo</span>
                </div>
            <?php endif; ?>
        </div>

        <h2 style="text-transform:capitalize"><?php echo esc_html($educational_center->edu_center_name); ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <label for="institute_name">Institute Name</label>
            <input type="text" name="institute_name" value="<?php echo esc_html($educational_center->edu_center_name); ?>" required />
            <br>
            <label for="mobile_number">Mobile Number</label>
            <input type="text" name="mobile_number" value="<?php echo esc_html($educational_center->mobile_number); ?>" required />
            <br>
            <label for="email_id">Email ID</label>
            <input type="email" name="email_id" value="<?php echo esc_html($educational_center->email_id); ?>" required />
            <br>
            <input type="file" name="institute_logo" accept="image/*" id="logo-file-input" style="display: none;" onchange="previewLogo(event)" />
            <input type="submit" name="update_center" value="Update Center" />
        </form>

        <?php
        if (isset($_POST['update_center'])) {
            $new_institute_name = sanitize_text_field($_POST['institute_name']);
            $new_mobile_number = sanitize_text_field($_POST['mobile_number']);
            $new_email_id = sanitize_email($_POST['email_id']);
            
            $update_data = array(
                'edu_center_name' => $new_institute_name,
                'mobile_number' => $new_mobile_number,
                'email_id' => $new_email_id
            );

            // Handle logo upload
            if (isset($_FILES['institute_logo']) && $_FILES['institute_logo']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['institute_logo']['size'] > 1048576) {
                    echo '<p class="error-message">Logo size must be less than 1 MB.</p>';
                } else {
                    $file = $_FILES['institute_logo'];
                    $upload_dir = wp_upload_dir();
                    $target_file = $upload_dir['path'] . '/' . basename($file['name']);

                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        $update_data['institute_logo'] = $upload_dir['url'] . '/' . basename($file['name']);
                    }
                }
            }

            // Update database
            $wpdb->update(
                $table_name,
                array_filter($update_data),
                array('educational_center_id' => $educational_center_id),
                array('%s', '%s', '%s', '%s'),
                array('%s')
            );

            header("Refresh:0");
            echo '<p class="success-message">Educational Center updated successfully.</p>';
        }
        ?>
    </div>

    <script>
    function previewLogo(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('institute-logo-image').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    </script>
    <?php
    wp_enqueue_script('jquery');
    return ob_get_clean();
}

add_shortcode('aspire_institute_dashboard', 'aspire_institute_dashboard_shortcode');

// Reusable Helper Function for Subscription Expired Message with AJAX
function render_subscription_expired_message($center_id) {
    ob_start();
    ?>
    <div class="subscription-expired payment-methods-wrap">
        <h1 class="payment-title">Subscription Required</h1>
        <p>Your subscription has expired or is not active. Please renew or extend it to access the full features.</p>
        <div class="subscription-options">
            <button class="payment-button" onclick="loadSubscriptionManagement('<?php echo esc_attr($center_id); ?>')">Manage Subscription</button>
            <a href="<?php echo wp_logout_url(home_url()); ?>" class="payment-button secondary">Logout</a>
            <a href="<?php echo wp_login_url(); ?>" class="payment-button secondary">Re-Login</a>
            <a href="mailto:support@example.com" class="payment-button secondary">Contact Support</a>
        </div>
        <div id="subscription-content" class="subscription-form-container"></div>
    </div>
    <script>
    function loadSubscriptionManagement(centerId) {
        const container = jQuery('#subscription-content');
        container.html('<p>Loading...</p>');

        jQuery.ajax({
            url: '<?php echo rest_url('institute/v1/subscriptions'); ?>',
            method: 'POST',
            data: {
                action: 'management',
                center_id: centerId,
                nonce: '<?php echo wp_create_nonce('subscription_nonce'); ?>'
            },
            headers: {'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'},
            success: function(response) {
                if (response.success) {
                    container.html(response.data.html);
                } else {
                    container.html('<p>Error: ' + response.data + '</p>');
                }
            },
            error: function(xhr) {
                container.html('<p>Error: ' + xhr.responseText + '</p>');
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

// Updated REST API Handler to Support 'management' Action
add_action('rest_api_init', function () {
    register_rest_route('institute/v1', '/subscriptions', [
        'methods' => 'POST',
        'callback' => 'handle_institute_subscriptions_rest',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);
});

function handle_institute_subscriptions_rest($request) {
    global $wpdb;
    $action = $request->get_param('action');
    $nonce = $request->get_param('nonce');
    $center_id = $request->get_param('center_id');

    if (!wp_verify_nonce($nonce, 'subscription_nonce')) {
        return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
    }

    $center_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'educational_center_id' AND meta_value = %s",
        $center_id
    ));
    if (!$center_exists) {
        return new WP_Error('invalid_center', 'Invalid Educational Center ID', ['status' => 404]);
    }

    switch ($action) {
        case 'management':
            return rest_ensure_response(['success' => true, 'data' => ['html' => render_institute_subscription_management($center_id)]]);
        case 'renew-form':
            return rest_ensure_response(['success' => true, 'data' => ['html' => render_subscription_renewal_form($center_id)]]);
        case 'extend-form':
            return rest_ensure_response(['success' => true, 'data' => ['html' => render_subscription_extension_form($center_id)]]);
        case 'renew':
            // Existing renew logic unchanged
            $plan_type = sanitize_text_field($request->get_param('plan_type'));
            $payment_method = sanitize_text_field($request->get_param('payment_method'));
            $transaction_id = sanitize_text_field($request->get_param('transaction_id')) ?: 'PENDING-' . time();
            // ... (rest of renew logic)
            return rest_ensure_response(['success' => true, 'data' => 'Subscription renewal requested. Awaiting verification.']);
        case 'extend':
            // Existing extend logic unchanged
            $subscription_id = intval($request->get_param('subscription_id'));
            $extend_duration = sanitize_text_field($request->get_param('extend_duration'));
            $payment_method = sanitize_text_field($request->get_param('payment_method'));
            $transaction_id = sanitize_text_field($request->get_param('transaction_id')) ?: 'PENDING-' . time();
            // ... (rest of extend logic)
            return rest_ensure_response(['success' => true, 'data' => 'Subscription extension requested. Awaiting verification.']);
        default:
            return new WP_Error('invalid_action', 'Invalid action', ['status' => 400]);
    }
}




function render_admin_header($admin_user) {
    global $wpdb;

    // Ensure $admin_user is a WP_User object
    if (!($admin_user instanceof WP_User)) {
        $admin_user = wp_get_current_user();
    }
    $admin_id = $admin_user->user_login;

    // Get the educational center using the reusable function
    $educational_center = get_educational_center_by_admin_id($admin_id);

    if (!$educational_center) {
        return '<div class="alert alert-danger">No Educational Center found for this Admin ID.</div>';
    }

    $user_name = $admin_user->display_name;
    $user_email = $admin_user->user_email;
    $avatar_url = $educational_center->institute_logo ? esc_url($educational_center->institute_logo) : 'https://via.placeholder.com/150';

    $dashboard_link = esc_url(home_url('/institute-dashboard'));
    $profile_link = esc_url(home_url('/institute-dashboard?section=profile'));
    $communication_link = esc_url(home_url('/institute-dashboard?section=communication'));
    $notifications_link = esc_url(home_url('/institute-dashboard?section=notifications'));
    $settings_link = esc_url(home_url('/institute-dashboard?section=settings'));
    $logout_link = get_secure_logout_url_by_role();
    $edu_center_id = $educational_center->educational_center_id;
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

    $unread_notifications = $wpdb->get_results($wpdb->prepare(
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
                    <img decoding="async" class="logo-image" src="<?php echo plugin_dir_url(__FILE__) . 'logo_instituto.jpg'; ?>">
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
                                        <?php
                                        // Encode 'enigma_overlord' as 'Super Admin' for frontend display
                                        $display_sender = ($msg->sender_id === 'enigma_overlord') ? 'Super Admin' : $msg->sender_id;
                                        ?>
                                        <li>
                                            <span class="msg-content">
                                                <span class="msg-sender"><?php echo esc_html($display_sender); ?></span>:
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
                                        <?php
                                        // Encode 'enigma_overlord' as 'Super Admin' for frontend display
                                        $display_sender = ($ann->sender_id === 'enigma_overlord') ? 'Super Admin' : $ann->sender_id;
                                        ?>
                                        <li>
                                            <span class="msg-content">
                                                <span class="msg-sender"><?php echo esc_html($display_sender); ?></span>:
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


// Enqueue jQuery
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], '3.6.0', true);
});

   
?>
