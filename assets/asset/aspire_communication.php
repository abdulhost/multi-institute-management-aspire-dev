<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Database table creation
global $wpdb;
$charset_collate = $wpdb->get_charset_collate();


// Enqueue Styles and Scripts (Updated to ensure jQuery is loaded)
add_action('wp_enqueue_scripts', 'aspire_communication_enqueue');
function aspire_communication_enqueue() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], null, true); // Ensure jQuery is loaded
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], null, true);
    wp_add_inline_style('bootstrap', '
        .chat-sidebar { max-height: 80vh; overflow-y: auto; border-right: 1px solid #ddd; }
        .chat-message { padding: 10px; margin: 5px; border-radius: 5px; max-width: 70%; }
        .chat-message.sent { background: #d4edda; align-self: flex-end; }
        .chat-message.received { background: #f8f9fa; align-self: flex-start; }
        .chat-header { background: #17a2b8; color: white; padding: 10px; }
    ');
}

// Updated Main Shortcode
function aspire_communication_shortcode() {
    global $wpdb;
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '<div class="alert alert-danger">Please log in to access the communication system.</div>';
    }

    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'inbox';
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Get unread message count
    $unread_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}messages 
         WHERE recipient_id = %d AND status = 'sent' AND type = 'message'",
        $user_id
    ));

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #fff3e6, #ffe6cc); min-height: 100vh;">
        <div class="row">
            <?php 
            $active_section = $section;
            include plugin_dir_path(__FILE__) . '../sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <div class="mb-3">
                    <form method="GET" class="input-group">
                        <input type="hidden" name="section" value="<?php echo esc_attr($section); ?>">
                        <input type="text" name="search" class="form-control" placeholder="Search messages..." value="<?php echo esc_attr($search_query); ?>">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                <?php
                switch ($section) {
                    case 'announcements':
                        echo render_announcements($user_id, $search_query);
                        break;
                    case 'inbox':
                        echo render_chat_inbox($user_id, $search_query, $unread_count);
                        break;
                    case 'compose':
                        echo render_compose($user_id);
                        break;
                    case 'groups':
                        echo render_group_management($user_id);
                        break;
                    default:
                        echo '<div class="alert alert-warning">Section not found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        function checkNewMessages() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'check_new_messages',
                    user_id: <?php echo $user_id; ?>
                },
                success: function(response) {
                    if (response.success && response.data.new_messages > 0) {
                        // Refresh sidebar and show notification
                        $('.chat-sidebar').load(window.location.href + ' .chat-sidebar > *');
                        console.log('New messages: ' + response.data.new_messages);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error: ' + error);
                }
            });
        }
        
        // Poll every 10 seconds
        setInterval(checkNewMessages, 10000);
        checkNewMessages(); // Initial check
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_communication', 'aspire_communication_shortcode');

// Updated AJAX Handler: Check New Messages
add_action('wp_ajax_check_new_messages', 'check_new_messages_callback');
function check_new_messages_callback() {
    global $wpdb;
    $user_id = intval($_POST['user_id']);
    
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
        wp_die();
    }

    $new_messages = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}messages 
         WHERE recipient_id = %d AND status = 'sent' AND type = 'message'",
        $user_id
    ));

    if ($new_messages === false) {
        wp_send_json_error('Database query failed');
    } else {
        wp_send_json_success(['new_messages' => intval($new_messages)]);
    }
    wp_die();
}

// Updated Function: Mark Message as Read
function mark_message_as_read($message_id, $user_id) {
    global $wpdb;
    $updated = $wpdb->update(
        "{$wpdb->prefix}messages",
        ['status' => 'read'],
        [
            'id' => $message_id,
            'recipient_id' => $user_id,
            'status' => 'sent'
        ],
        ['%s'],
        ['%d', '%d', '%s']
    );

    // Debugging
    if ($updated === false) {
        error_log("Failed to mark message $message_id as read for user $user_id: " . $wpdb->last_error);
    } elseif ($updated === 0) {
        error_log("No message updated for message $message_id and user $user_id - already read or not found");
    }
    return $updated;
}

// Updated Chat Inbox
function render_chat_inbox($user_id, $search_query = '', $unread_count = 0) {
    global $wpdb;
    
    $conversations = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT IF(sender_id = %d, recipient_id, sender_id) AS other_user, 
                MAX(created_at) AS last_message,
                SUM(CASE WHEN status = 'sent' AND recipient_id = %d THEN 1 ELSE 0 END) AS unread
         FROM {$wpdb->prefix}messages 
         WHERE (sender_id = %d OR recipient_id = %d) AND type = 'message' 
         GROUP BY other_user 
         ORDER BY last_message DESC",
        $user_id, $user_id, $user_id, $user_id
    ));

    $selected_user = isset($_GET['chat_with']) ? intval($_GET['chat_with']) : ($conversations ? $conversations[0]->other_user : 0);
    
    $where_clause = $selected_user ? "WHERE ((m.sender_id = %d AND m.recipient_id = %d) OR (m.sender_id = %d AND m.recipient_id = %d)) AND m.type = 'message'" : '';
    if ($search_query) {
        $where_clause .= $where_clause ? " AND m.content LIKE %s" : "WHERE m.content LIKE %s";
    }
    
    $messages = $selected_user ? $wpdb->get_results($wpdb->prepare(
        "SELECT m.*, 
                IFNULL(u.display_name, IFNULL(p.post_title, IFNULL(s.name, 'Unknown User'))) AS sender_name,
                IFNULL(ur.display_name, IFNULL(pr.post_title, IFNULL(sr.name, 'Unknown User'))) AS recipient_name,
                m.sender_id,
                m.recipient_id,
                m.status
         FROM {$wpdb->prefix}messages m 
         LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID 
         LEFT JOIN {$wpdb->posts} p ON m.sender_id = p.ID AND p.post_type IN ('teacher', 'students', 'parent')
         LEFT JOIN {$wpdb->prefix}staff s ON m.sender_id = s.staff_id
         LEFT JOIN {$wpdb->users} ur ON m.recipient_id = ur.ID 
         LEFT JOIN {$wpdb->posts} pr ON m.recipient_id = pr.ID AND pr.post_type IN ('teacher', 'students', 'parent')
         LEFT JOIN {$wpdb->prefix}staff sr ON m.recipient_id = sr.staff_id
         " . $where_clause . " 
         ORDER BY m.created_at ASC",
        $search_query ? array_merge([$user_id, $selected_user, $selected_user, $user_id], ["%$search_query%"]) : [$user_id, $selected_user, $selected_user, $user_id]
    )) : [];

    // Mark messages as read when viewed
    if ($selected_user && $messages) {
        foreach ($messages as $msg) {
            if ($msg->recipient_id == $user_id && $msg->status == 'sent') {
                mark_message_as_read($msg->id, $user_id);
            }
        }
        // Refresh messages after marking as read (optional, could use AJAX instead)
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, 
                    IFNULL(u.display_name, IFNULL(p.post_title, IFNULL(s.name, 'Unknown User'))) AS sender_name,
                    IFNULL(ur.display_name, IFNULL(pr.post_title, IFNULL(sr.name, 'Unknown User'))) AS recipient_name,
                    m.sender_id,
                    m.recipient_id,
                    m.status
             FROM {$wpdb->prefix}messages m 
             LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID 
             LEFT JOIN {$wpdb->posts} p ON m.sender_id = p.ID AND p.post_type IN ('teacher', 'students', 'parent')
             LEFT JOIN {$wpdb->prefix}staff s ON m.sender_id = s.staff_id
             LEFT JOIN {$wpdb->users} ur ON m.recipient_id = ur.ID 
             LEFT JOIN {$wpdb->posts} pr ON m.recipient_id = pr.ID AND pr.post_type IN ('teacher', 'students', 'parent')
             LEFT JOIN {$wpdb->prefix}staff sr ON m.recipient_id = sr.staff_id
             " . $where_clause . " 
             ORDER BY m.created_at ASC",
            $search_query ? array_merge([$user_id, $selected_user, $selected_user, $user_id], ["%$search_query%"]) : [$user_id, $selected_user, $selected_user, $user_id]
        ));
    }

    ob_start();
    ?>
    <div class="card shadow-sm" style="border: 2px solid #17a2b8;">
        <div class="chat-header d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0"><i class="bi bi-chat me-2"></i>Inbox <?php if ($unread_count) { ?><span class="badge bg-danger"><?php echo $unread_count; ?></span><?php } ?></h3>
            <a href="?section=compose" class="btn btn-light btn-sm">New Message</a>
        </div>
        <div class="card-body p-0 d-flex">
            <div class="col-4 chat-sidebar p-3">
                <?php 
                if (empty($conversations)) {
                    echo '<p class="text-muted">No conversations yet</p>';
                } else {
                    foreach ($conversations as $conv) {
                        $display_name = 'Unknown User';
                        $wp_user = get_user_by('ID', $conv->other_user);
                        if ($wp_user) {
                            $display_name = esc_html($wp_user->display_name);
                        } else {
                            $post = get_post($conv->other_user);
                            if ($post && in_array($post->post_type, ['teacher', 'students', 'parent'])) {
                                $display_name = esc_html($post->post_title);
                            } else {
                                $staff_name = $wpdb->get_var($wpdb->prepare(
                                    "SELECT name FROM {$wpdb->prefix}staff WHERE staff_id = %d",
                                    $conv->other_user
                                ));
                                $display_name = $staff_name ? esc_html($staff_name) : 'Unknown User';
                            }
                        }
                        ?>
                        <a href="?section=inbox&chat_with=<?php echo $conv->other_user; ?>" 
                           class="d-block p-2 mb-2 bg-light rounded <?php echo $conv->other_user == $selected_user ? 'border-primary' : ''; ?>">
                            <?php echo $display_name; ?>
                            <?php if ($conv->unread > 0) { ?><span class="badge bg-success ms-2"><?php echo $conv->unread; ?></span><?php } ?>
                            <small class="text-muted d-block"><?php echo esc_html(date('H:i, M d', strtotime($conv->last_message))); ?></small>
                        </a>
                    <?php 
                    }
                } ?>
            </div>
            <div class="col-8 p-3 d-flex flex-column">
                <?php if ($selected_user) { 
                    $selected_display_name = 'Unknown User';
                    $wp_user = get_user_by('ID', $selected_user);
                    if ($wp_user) {
                        $selected_display_name = esc_html($wp_user->display_name);
                    } else {
                        $post = get_post($selected_user);
                        if ($post && in_array($post->post_type, ['teacher', 'students', 'parent'])) {
                            $selected_display_name = esc_html($post->post_title);
                        } else {
                            $staff_name = $wpdb->get_var($wpdb->prepare(
                                "SELECT name FROM {$wpdb->prefix}staff WHERE staff_id = %d",
                                $selected_user
                            ));
                            $selected_display_name = $staff_name ? esc_html($staff_name) : 'Unknown User';
                        }
                    }
                    ?>
                    <div class="border-bottom pb-2 mb-2">
                        <strong>Chat with <?php echo $selected_display_name; ?></strong>
                    </div>
                    <div class="flex-grow-1 overflow-auto" style="max-height: 60vh;">
                        <?php 
                        if (empty($messages)) {
                            echo '<p class="text-muted">No messages yet</p>';
                        } else {
                            foreach ($messages as $msg) { 
                                $sender_name = $msg->sender_name;
                                ?>
                                <div class="chat-message <?php echo $msg->sender_id == $user_id ? 'sent' : 'received'; ?> <?php echo $msg->status == 'sent' ? 'font-weight-bold' : ''; ?>">
                                    <small><?php echo esc_html($sender_name); ?> - <?php echo esc_html($msg->created_at); ?>
                                        <?php if ($msg->recipient_id == $user_id) { ?>
                                            (<?php echo $msg->status; ?>)
                                        <?php } ?>
                                    </small>
                                    <p><?php echo esc_html($msg->content); ?></p>
                                </div>
                            <?php 
                            }
                        } ?>
                    </div>
                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mt-2">
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="type" value="message">
                        <input type="hidden" name="recipient_id" value="<?php echo $selected_user; ?>">
                        <div class="input-group">
                            <textarea name="content" class="form-control" rows="2" placeholder="Type a message..." required></textarea>
                            <button type="submit" class="btn btn-success"><i class="bi bi-send"></i></button>
                        </div>
                        <?php wp_nonce_field('comm_nonce', 'nonce'); ?>
                    </form>
                <?php } else { ?>
                    <p class="text-muted text-center">Select a conversation to start chatting.</p>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
// Announcement Rendering
function render_announcements($user_id) {
    global $wpdb;
    $groups = get_user_meta($user_id, 'aspire_groups', true) ?: [];
    $groups = array_map('sanitize_text_field', (array)$groups);
    $is_admin = current_user_can('administrator') || current_user_can('institute_admin');

    $query = "SELECT * FROM {$wpdb->prefix}messages WHERE type = 'announcement' AND status = 'sent'";
    if (!$is_admin && !empty($groups)) {
        $group_placeholders = implode(',', array_fill(0, count($groups), '%s'));
        $query .= " AND (group_target IS NULL OR group_target IN ($group_placeholders))";
        $announcements = $wpdb->get_results($wpdb->prepare($query, $groups));
    } else {
        $announcements = $wpdb->get_results($query);
    }

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
                                echo '<td>' . esc_html($ann->title ?? 'No Title') . '</td>';
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

// Compose Form
function render_compose($user_id) {
    global $wpdb;
    $is_admin = current_user_can('administrator') || current_user_can('institute_admin');
    $education_center_id = get_educational_center_data();

    $categories = [
        'teacher' => 'Teachers',
        'students' => 'Students',
        'parent' => 'Parents',
        'staff' => 'Staff'
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
                    <select name="type" id="type" class="form-select" onchange="toggleFields(this)" required>
                        <option value="message">Message</option>
                        <?php if ($is_admin) { ?>
                            <option value="announcement">Announcement</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 recipient-field">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select" onchange="fetchRecipients(this.value)" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $key => $label) { ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 recipient-field">
                    <label for="recipient_ids" class="form-label">Recipients</label>
                    <select name="recipient_ids[]" id="recipient_ids" class="form-select select2-recipients" multiple required>
                        <option value="">Select a category first</option>
                    </select>
                </div>
                <div class="col-md-6 group-field" style="display: none;">
                    <label for="group_target" class="form-label">Target Group</label>
                    <input type="text" name="group_target" id="group_target" class="form-control" placeholder="e.g., All Students">
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
    <script>
    jQuery(document).ready(function($) {
        $('.select2-recipients').select2({
            placeholder: 'Select recipients',
            allowClear: true,
            width: '100%'
        });
    });

    function toggleFields(select) {
        const recipientFields = document.querySelectorAll('.recipient-field');
        const groupField = document.querySelector('.group-field');
        if (select.value === 'announcement') {
            recipientFields.forEach(field => field.style.display = 'none');
            groupField.style.display = 'block';
            document.getElementById('recipient_ids').removeAttribute('required');
        } else {
            recipientFields.forEach(field => field.style.display = 'block');
            groupField.style.display = 'none';
            document.getElementById('recipient_ids').setAttribute('required', 'required');
        }
    }

    function fetchRecipients(category) {
        if (!category) {
            jQuery('#recipient_ids').html('<option value="">Select a category first</option>').trigger('change');
            return;
        }

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'fetch_recipients',
                category: category,
                education_center_id: '<?php echo esc_js($education_center_id); ?>'
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let options = '<option value="all">All ' + category.charAt(0).toUpperCase() + category.slice(1) + 's</option>';
                    options += response.data.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
                    jQuery('#recipient_ids').html(options).trigger('change');
                } else {
                    jQuery('#recipient_ids').html('<option value="">No ' + category + 's found</option>').trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error);
                jQuery('#recipient_ids').html('<option value="">Error loading recipients</option>').trigger('change');
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

// Updated AJAX Handler for Fetching Recipients
add_action('wp_ajax_fetch_recipients', 'fetch_recipients_callback');
function fetch_recipients_callback() {
    global $wpdb;
    $category = sanitize_text_field($_POST['category']);
    $education_center_id = sanitize_text_field($_POST['education_center_id']);
    
    $recipients = [];
    
    if (in_array($category, ['teacher', 'students', 'parent'])) {
        $posts = get_posts([
            'post_type' => $category,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'educational_center_id',
                    'value' => $education_center_id,
                    'compare' => '='
                ]
            ]
        ]);
        
        foreach ($posts as $post) {
            $user_id = get_post_meta($post->ID, 'user_id', true);
            $name = $post->post_title;
            
            if ($user_id) {
                $wp_user = get_user_by('ID', $user_id);
                if ($wp_user) {
                    $recipients[] = [
                        'id' => $user_id,
                        'name' => $wp_user->display_name
                    ];
                } else {
                    $recipients[] = [
                        'id' => $post->ID,
                        'name' => $name
                    ];
                }
            } else {
                $recipients[] = [
                    'id' => $post->ID,
                    'name' => $name
                ];
            }
        }
    } elseif ($category === 'staff') {
        $staff_members = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT staff_id, name FROM {$wpdb->prefix}staff WHERE education_center_id = %d",
                $education_center_id
            )
        );
        
        foreach ($staff_members as $staff) {
            $recipients[] = [
                'id' => $staff->staff_id,
                'name' => $staff->name
            ];
        }
    }

    if (empty($recipients)) {
        wp_send_json_error('No recipients found');
    } else {
        wp_send_json_success($recipients);
    }
    wp_die();
}

// Group Management
function render_group_management($user_id) {
    global $wpdb;
    $groups = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}message_groups WHERE creator_id = %d", $user_id));

    ob_start();
    ?>
    <div class="card shadow-sm" style="border: 2px solid #007bff;">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="bi bi-people me-2"></i>Manage Groups</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mb-4">
                <input type="hidden" name="action" value="create_group">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="group_name" class="form-label">Group Name</label>
                        <input type="text" name="group_name" id="group_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="group_members" class="form-label">Members</label>
                        <select name="group_members[]" id="group_members" class="form-select select2-members" multiple required>
                            <?php foreach (get_users() as $user) { if ($user->ID != $user_id) { ?>
                                <option value="<?php echo $user->ID; ?>"><?php echo esc_html($user->display_name); ?></option>
                            <?php }} ?>
                        </select>
                    </div>
                    <?php wp_nonce_field('group_nonce', 'nonce'); ?>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Create Group</button>
                    </div>
                </div>
            </form>
            <h5>Your Groups</h5>
            <ul class="list-group">
                <?php foreach ($groups as $group) {
                    $members = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}message_group_members WHERE group_id = %d", $group->id));
                    ?>
                    <li class="list-group-item">
                        <?php echo esc_html($group->name); ?> (<?php echo count($members); ?> members)
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('.select2-members').select2({
            placeholder: 'Select members',
            allowClear: true,
            width: '100%'
        });
    });
    </script>
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
    $content = sanitize_textarea_field($_POST['content']);
    $recipient_ids = isset($_POST['recipient_ids']) ? array_map('intval', (array)$_POST['recipient_ids']) : (isset($_POST['recipient_id']) ? [intval($_POST['recipient_id'])] : []);
    $group_target = $type === 'announcement' ? sanitize_text_field($_POST['group_target']) : null;

    if (empty($content) || ($type === 'message' && empty($recipient_ids))) {
        wp_die('Invalid input data.', 'Validation Error', ['back_link' => true]);
    }

    if ($type === 'message') {
        if (in_array('all', $recipient_ids)) {
            $category = sanitize_text_field($_POST['category']);
            $education_center_id = get_educational_center_data();
            
            $args = [
                'meta_query' => [
                    [
                        'key' => 'educational_center_id',
                        'value' => $education_center_id,
                        'compare' => '='
                    ]
                ]
            ];

            switch ($category) {
                case 'teacher':
                    $args['role__in'] = ['teacher', 'administrator'];
                    break;
                case 'student':
                    $args['role'] = 'student';
                    break;
                case 'parent':
                    $args['role'] = 'parent';
                    break;
                case 'staff':
                    $args['role__in'] = ['staff', 'employee'];
                    break;
            }

            $users = get_users($args);
            $recipient_ids = array_map(function($user) { return $user->ID; }, $users);
        }
        
        foreach ($recipient_ids as $recipient_id) {
            $wpdb->insert(
                "{$wpdb->prefix}messages",
                [
                    'sender_id' => $sender_id,
                    'recipient_id' => $recipient_id,
                    'type' => $type,
                    'title' => 'Chat Message', // Default title for chat messages
                    'content' => $content,
                    'status' => 'sent'
                ],
                ['%d', '%d', '%s', '%s', '%s', '%s']
            );
        }
    } else {
        $wpdb->insert(
            "{$wpdb->prefix}messages",
            [
                'sender_id' => $sender_id,
                'type' => $type,
                'title' => 'Announcement', // Default title for announcements
                'content' => $content,
                'group_target' => $group_target,
                'status' => 'sent'
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );
    }

    wp_redirect(home_url('/institute-dashboard/communication/?section=' . ($type === 'message' ? 'inbox' : 'announcements')));
    exit;
}

// Group Creation Handler
add_action('admin_post_create_group', 'handle_create_group');
function handle_create_group() {
    global $wpdb;
    if (!wp_verify_nonce($_POST['nonce'], 'group_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $creator_id = get_current_user_id();
    $group_name = sanitize_text_field($_POST['group_name']);
    $members = array_map('intval', (array)$_POST['group_members']);

    $wpdb->insert(
        "{$wpdb->prefix}message_groups",
        ['name' => $group_name, 'creator_id' => $creator_id],
        ['%s', '%d']
    );
    $group_id = $wpdb->insert_id;

    foreach ($members as $user_id) {
        $wpdb->insert(
            "{$wpdb->prefix}message_group_members",
            ['group_id' => $group_id, 'user_id' => $user_id],
            ['%d', '%d']
        );
    }

    wp_redirect(home_url('/institute-dashboard/communication/?section=groups'));
    exit;
}