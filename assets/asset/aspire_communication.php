<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Activation Hook: Create tables
register_activation_hook(__FILE__, 'aspire_communication_activate');
function aspire_communication_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Messages table
    $messages_table = $wpdb->prefix . 'messages';
    $sql = "CREATE TABLE $messages_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        sender_id BIGINT(20) UNSIGNED NOT NULL,
        recipient_id BIGINT(20) UNSIGNED DEFAULT NULL,
        type ENUM('announcement', 'message') DEFAULT 'message',
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        group_target VARCHAR(50) DEFAULT NULL,
        status ENUM('draft', 'sent', 'read') DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY sender_id (sender_id),
        KEY recipient_id (recipient_id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Groups table (optional, can be hardcoded instead)
    $groups_table = $wpdb->prefix . 'message_groups';
    $sql = "CREATE TABLE $groups_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY name (name)
    ) $charset_collate;";
    dbDelta($sql);

    // Populate initial groups (optional)
    $wpdb->insert($groups_table, ['name' => 'All Teachers']);
    $wpdb->insert($groups_table, ['name' => 'All Students']);
    $wpdb->insert($groups_table, ['name' => 'All Parents']);
    $wpdb->insert($groups_table, ['name' => 'Class 10A']);
}

// Enqueue Styles
add_action('wp_enqueue_scripts', 'aspire_communication_enqueue');
function aspire_communication_enqueue() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
}

// Main Shortcode
function aspire_communication_shortcode() {
    global $wpdb;
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '<div class="alert alert-danger">Please log in to access the communication system.</div>';
    }

    $education_center_id = get_educational_center_data(); // Assuming this exists
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'announcements';

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #fff3e6, #ffe6cc); min-height: 100vh;">
        <div class="row">
            <?php 
            $active_section = $section;
            include plugin_dir_path(__FILE__) . '../sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'announcements':
                        echo render_announcements($user_id);
                        break;
                    case 'inbox':
                        echo render_inbox($user_id);
                        break;
                    case 'compose':
                        echo render_compose($user_id);
                        break;
                    default:
                        echo '<div class="alert alert-warning">Section not found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_communication', 'aspire_communication_shortcode');

// Dashboard Widget
add_action('wp_dashboard_setup', 'aspire_comm_dashboard_widget_setup');
function aspire_comm_dashboard_widget_setup() {
    wp_add_dashboard_widget('aspire_comm_widget', 'Recent Announcements', 'aspire_comm_dashboard_widget');
}
function aspire_comm_dashboard_widget() {
    global $wpdb;
    $announcements = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}messages WHERE type = 'announcement' AND status = 'sent' ORDER BY created_at DESC LIMIT 5");
    if (empty($announcements)) {
        echo '<p>No recent announcements.</p>';
    } else {
        echo '<ul class="list-unstyled">';
        foreach ($announcements as $ann) {
            echo '<li><strong>' . esc_html($ann->title) . '</strong>: ' . esc_html(wp_trim_words($ann->content, 10)) . '</li>';
        }
        echo '</ul>';
    }
}

// Announcement Rendering
function render_announcements($user_id) {
    global $wpdb;
    $user = wp_get_current_user();
    $groups = get_user_meta($user_id, 'aspire_groups', true) ?: [];
    $is_admin = in_array('administrator', $user->roles);

    $base_query = "SELECT * FROM {$wpdb->prefix}messages WHERE type = 'announcement' AND status = 'sent'";
    if ($is_admin) {
        $query = "$base_query ORDER BY created_at DESC";
    } else {
        $groups = array_map('sanitize_text_field', (array)$groups);
        if (!empty($groups)) {
            $group_placeholders = implode(',', array_fill(0, count($groups), '%s'));
            $query = $wpdb->prepare("$base_query AND (group_target IS NULL OR group_target IN ($group_placeholders)) ORDER BY created_at DESC", $groups);
        } else {
            $query = "$base_query AND group_target IS NULL ORDER BY created_at DESC";
        }
    }
    $announcements = $wpdb->get_results($query);

    ob_start();
    ?>
    <div class="card shadow-sm" style="border: 2px solid #fd7e14;">
        <div class="card-header bg-orange text-white d-flex justify-content-between align-items-center" style="background-color: #fd7e14;">
            <h3 class="card-title"><i class="bi bi-megaphone me-2"></i>Announcements</h3>
            <?php if ($is_admin) { ?>
                <a href="?section=compose" class="btn btn-light btn-sm">Post Announcement</a>
            <?php } ?>
        </div>
        <div class="card-body">
            <p class="text-muted">"Stay updated with school-wide or group-specific notices."</p>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Group</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($announcements)) {
                            echo '<tr><td colspan="4" class="text-center">No announcements found.</td></tr>';
                        } else {
                            foreach ($announcements as $ann) {
                                echo '<tr>';
                                echo '<td>' . esc_html($ann->title) . '</td>';
                                echo '<td>' . esc_html(wp_trim_words($ann->content, 20)) . '</td>';
                                echo '<td>' . ($ann->group_target ? esc_html($ann->group_target) : 'All') . '</td>';
                                echo '<td>' . esc_html($ann->created_at) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Inbox Rendering
function render_inbox($user_id) {
    global $wpdb;
    $groups = get_user_meta($user_id, 'aspire_groups', true) ?: [];
    $groups = array_map('sanitize_text_field', (array)$groups);

    $query = "SELECT m.*, u.display_name AS sender_name 
              FROM {$wpdb->prefix}messages m 
              JOIN {$wpdb->users} u ON m.sender_id = u.ID 
              WHERE (m.recipient_id = %d AND m.type = 'message') 
                 OR (m.type = 'announcement' AND m.status = 'sent' AND (m.group_target IS NULL";
    if (!empty($groups)) {
        $group_placeholders = implode(',', array_fill(0, count($groups), '%s'));
        $query .= " OR m.group_target IN ($group_placeholders)";
    }
    $query .= ")) ORDER BY m.created_at DESC";

    $params = array_merge([$user_id], $groups);
    $messages = $wpdb->get_results($wpdb->prepare($query, $params));

    ob_start();
    ?>
    <div class="card shadow-sm" style="border: 2px solid #17a2b8;">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="bi bi-envelope me-2"></i>Inbox</h3>
            <a href="?section=compose" class="btn btn-light btn-sm">Compose</a>
        </div>
        <div class="card-body">
            <p class="text-muted">"View your private messages and announcements."</p>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($messages)) {
                            echo '<tr><td colspan="5" class="text-center">No messages found.</td></tr>';
                        } else {
                            foreach ($messages as $msg) {
                                echo '<tr>';
                                echo '<td>' . esc_html($msg->sender_name) . '</td>';
                                echo '<td>' . esc_html($msg->title) . '</td>';
                                echo '<td>' . esc_html(ucfirst($msg->type)) . '</td>';
                                echo '<td>' . esc_html($msg->created_at) . '</td>';
                                echo '<td>' . ($msg->status === 'read' ? '<span class="badge bg-success">Read</span>' : '<span class="badge bg-warning">Unread</span>') . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Compose Form
function render_compose($user_id) {
    global $wpdb;
    $user = wp_get_current_user();
    $is_admin = in_array('administrator', $user->roles);

    // Hardcoded groups (replace with $wpdb->get_results() from wp_message_groups if using table)
    $groups = [
        'all_teachers' => 'All Teachers',
        'all_students' => 'All Students',
        'all_parents' => 'All Parents',
        'class_10a' => 'Class 10A'
    ];

    ob_start();
    ?>
    <div class="card shadow-sm" style="border: 2px solid #28a745;">
        <div class="card-header bg-success text-white">
            <h3 class="card-title"><i class="bi bi-pencil-square me-2"></i>Compose</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="row g-3">
                <input type="hidden" name="action" value="send_message">
                <div class="col-md-6">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="message">Message</option>
                        <?php if ($is_admin) { ?>
                            <option value="announcement">Announcement</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="group_target" class="form-label">Send To</label>
                    <select name="group_target" id="group_target" class="form-select" required>
                        <option value="">Select Group</option>
                        <?php foreach ($groups as $key => $label) { ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="title" class="form-label">Subject</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="content" class="form-label">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                </div>
                <?php wp_nonce_field('comm_nonce', 'nonce'); ?>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">Send</button>
                    <a href="?section=inbox" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Message Handler
add_action('admin_post_send_message', 'handle_send_message');
function handle_send_message() {
    global $wpdb;
    if (!wp_verify_nonce($_POST['nonce'], 'comm_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $sender_id = get_current_user_id();
    $type = sanitize_text_field($_POST['type']);
    $title = sanitize_text_field($_POST['title']);
    $content = sanitize_textarea_field($_POST['content']);
    $group_target = sanitize_text_field($_POST['group_target']);

    if (empty($title) || empty($content) || empty($group_target)) {
        wp_die('Invalid input data.', 'Validation Error', ['back_link' => true]);
    }

    if ($type === 'message') {
        // Fetch users in the group (simplified here; expand with wp_message_group_members if needed)
        $group_users = [];
        switch ($group_target) {
            case 'all_teachers':
                $group_users = get_users(['role' => 'teacher']); // Adjust role as per your setup
                break;
            case 'all_students':
                $group_users = get_users(['role' => 'student']);
                break;
            case 'all_parents':
                $group_users = get_users(['role' => 'parent']);
                break;
            case 'class_10a':
                $group_users = get_users(['meta_key' => 'aspire_groups', 'meta_value' => 'class_10a']);
                break;
        }

        foreach ($group_users as $user) {
            $wpdb->insert(
                "{$wpdb->prefix}messages",
                [
                    'sender_id' => $sender_id,
                    'recipient_id' => $user->ID,
                    'type' => $type,
                    'title' => $title,
                    'content' => $content,
                    'group_target' => $group_target,
                    'status' => 'sent'
                ],
                ['%d', '%d', '%s', '%s', '%s', '%s', '%s']
            );
        }
    } else {
        $wpdb->insert(
            "{$wpdb->prefix}messages",
            [
                'sender_id' => $sender_id,
                'recipient_id' => null,
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'group_target' => $group_target,
                'status' => 'sent'
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s']
        );
    }

    if ($wpdb->last_error) {
        wp_die('Failed to send: ' . esc_html($wpdb->last_error), 'Database Error', ['back_link' => true]);
    }

    wp_redirect(home_url('/institute-dashboard/communication/?section=' . ($type === 'message' ? 'inbox' : 'announcements')));
    exit;
}

// REST API for Push Notifications (Future Use)
add_action('rest_api_init', 'register_push_endpoint');
function register_push_endpoint() {
    register_rest_route('aspire/v1', '/send-push', [
        'methods' => 'POST',
        'callback' => 'send_push_notification',
        'permission_callback' => function () { return current_user_can('manage_options'); }
    ]);
}
function send_push_notification($request) {
    $title = sanitize_text_field($request['title']);
    $body = sanitize_textarea_field($request['body']);
    $group = sanitize_text_field($request['group'] ?? '');
    return new WP_REST_Response('Push notification queued (requires FCM setup)', 200);
}