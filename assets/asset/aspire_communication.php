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
                        echo aspire_admin_notice_board_shortcode();
                        break;
                    case 'inbox':
                        echo aspire_admin_prochat_shortcode();
                        break;
                    case 'compose':
                        echo aspire_admin_prochat_shortcode();
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


// Helper: Check if user is an institute admin
function aspire_admin_is_institute_admin($username, $education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'institute_admins';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE institute_admin_id = %s AND education_center_id = %s",
        $username,
        $education_center_id
    ));
    return $count > 0;
}

// Helper: Get Institute Admins
function aspire_admin_get_admins($education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'institute_admins';
    $admins = $wpdb->get_results($wpdb->prepare(
        "SELECT institute_admin_id AS id, name FROM $table WHERE education_center_id = %s",
        $education_center_id
    ), ARRAY_A);
    return $admins ?: [];
}

// Helper: Get Educational Center ID (assuming this matches previous versions)
if (!function_exists('get_educational_center_data')) {
    function get_educational_center_data() {
        return 'AFC46B9CEE17'; // Adjust as needed
    }
}

// Admin: Send Message
function aspire_admin_send_message($sender_id, $receiver_id, $message, $education_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';

    error_log("Admin sending message: sender_id=$sender_id, receiver_id=$receiver_id, message=$message, education_center_id=$education_center_id");

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

// Admin: Mark Messages as Read
function aspire_admin_mark_messages_read($username, $conversation_with) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';

    $wpdb->update(
        $table,
        ['status' => 'read'],
        [
            'receiver_id' => $username,
            'sender_id' => $conversation_with,
            'status' => 'sent'
        ],
        ['%s'],
        ['%s', '%s', '%s']
    );
}

// Admin: Get Unread Count
function aspire_admin_get_unread_count($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = get_educational_center_data();

    $group_receivers = ['all', 'institute_admins'];
    $placeholders = implode(',', array_fill(0, count($group_receivers), '%s'));
    $query_args = array_merge([$edu_center_id, $username], $group_receivers);

    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table 
         WHERE education_center_id = %s 
         AND (receiver_id = %s OR receiver_id IN ($placeholders)) 
         AND status = 'sent'",
        $query_args
    );

    return (int) $wpdb->get_var($query);
}

// Admin: AJAX Send Message
add_action('wp_ajax_aspire_admin_send_message', 'aspire_admin_ajax_send_message');
function aspire_admin_ajax_send_message() {
    error_log("aspire_admin_ajax_send_message function called");
    check_ajax_referer('aspire_admin_nonce', 'nonce');

    $edu_center_id = get_educational_center_data();
    $user = wp_get_current_user();
    $sender_id = $user->user_login;
    $message = sanitize_text_field($_POST['message'] ?? '');
    $target_value = sanitize_text_field($_POST['target_value'] ?? '');

    if (!$edu_center_id || !$sender_id || !$message || !$target_value) {
        error_log("Missing fields: edu_center_id=$edu_center_id, sender_id=$sender_id, message=$message, target_value=$target_value");
        wp_send_json_error(['error' => 'Missing required fields']);
        return;
    }

    $group_types = ['all', 'institute_admins'];
    $success = false;
    
    if (in_array($target_value, $group_types)) {
        $receiver_id = $target_value;
        error_log("AJAX send group: sender_id=$sender_id, receiver_id=$receiver_id");
        $success = aspire_admin_send_message($sender_id, $receiver_id, $message, $edu_center_id);
    } else {
        $receiver_id = $target_value;
        error_log("AJAX send individual: sender_id=$sender_id, receiver_id=$receiver_id");
        $success = aspire_admin_send_message($sender_id, $receiver_id, $message, $edu_center_id);
    }

    if ($success) {
        wp_send_json_success(['success' => 'Message sent!']);
    } else {
        wp_send_json_error(['error' => 'Failed to send message']);
    }
}

// Admin: AJAX Fetch Messages
// Admin: AJAX Fetch Messages
add_action('wp_ajax_aspire_admin_fetch_messages', 'aspire_admin_ajax_fetch_messages');
function aspire_admin_ajax_fetch_messages() {
    ob_start();
    check_ajax_referer('aspire_admin_nonce', 'nonce');

    $user = wp_get_current_user();
    $username = $user->user_login;
    $conversation_with = sanitize_text_field($_POST['conversation_with'] ?? '');

    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $edu_center_id = get_educational_center_data();
    
    $group_types = ['all', 'institute_admins']; // Removed 'enigma_overlord'
    $group_names = [
        'all' => 'Everyone in Center',
        'institute_admins' => 'Institute Admins'
    ];

    $query = "SELECT * FROM $table WHERE education_center_id = %s";
    $query_args = [$edu_center_id];

    if ($conversation_with) {
        if (in_array($conversation_with, $group_types)) {
            $query .= " AND (";
            $query .= " (receiver_id = %s)"; // Messages sent to this group
            $query .= " OR (sender_id = %s AND receiver_id = %s)"; // Messages sent by user to this group
            $query .= ")";
            $query .= " ORDER BY timestamp ASC LIMIT 50";
            $query_args[] = $conversation_with;
            $query_args[] = $username;
            $query_args[] = $conversation_with;
        } else {
            $query .= " AND ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s))";
            $query .= " ORDER BY timestamp ASC LIMIT 50";
            $query_args[] = $username;
            $query_args[] = $conversation_with;
            $query_args[] = $conversation_with;
            $query_args[] = $username;
            aspire_admin_mark_messages_read($username, $conversation_with);
        }
    }

    $prepared_query = $wpdb->prepare($query, $query_args);
    $messages = $wpdb->get_results($prepared_query);

    // Debugging: Log query and results
    error_log("Fetch Messages Query: " . $wpdb->last_query);
    error_log("Messages Fetched: " . print_r($messages, true));

    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);
    $admins = aspire_admin_get_admins($edu_center_id);

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
    foreach ($admins as $admin) {
        $contact_map[$admin['id']] = "{$admin['name']} (Admin)";
    }
    $contact_map['enigma_overlord'] = 'Instituto Admin';

    $output = '';
    foreach ($messages as $msg) {
        if (in_array($msg->receiver_id, $group_types)) {
            $sender_name = ($msg->sender_id === $username) ? 'You' : ($contact_map[$msg->sender_id] ?? (get_user_by('login', $msg->sender_id)->display_name ?? 'Unknown'));
            $receiver_display = "Group: " . ($group_names[$msg->receiver_id] ?? $msg->receiver_id);
        } else {
            $sender_name = ($msg->sender_id === $username) ? 'You' : ($contact_map[$msg->sender_id] ?? (get_user_by('login', $msg->sender_id)->display_name ?? 'Unknown'));
            $receiver_display = $contact_map[$msg->receiver_id] ?? (get_user_by('login', $msg->receiver_id)->display_name ?? 'Unknown');
        }
        
        $initials = strtoupper(substr($sender_name === 'You' ? $user->display_name : $sender_name, 0, 2));
        $output .= '<div class="chat-message ' . ($msg->sender_id === $username ? 'sent' : 'received') . ' ' . ($msg->status === 'sent' ? 'unread' : '') . '">';
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
    wp_send_json_success(['html' => $output, 'unread' => aspire_admin_get_unread_count($username)]);
}
// Admin: AJAX Fetch Conversations
// Admin: AJAX Fetch Conversations
add_action('wp_ajax_aspire_admin_fetch_conversations', 'aspire_admin_ajax_fetch_conversations');
function aspire_admin_ajax_fetch_conversations() {
    ob_start();
    check_ajax_referer('aspire_admin_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = get_educational_center_data();

    global $wpdb;
    $table = $wpdb->prefix . 'aspire_messages';
    $group_names = [
        'all' => 'Everyone in Center',
        'institute_admins' => 'Institute Admins'
        // Removed 'enigma_overlord' from group names
    ];

    $query = "
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = %s THEN receiver_id 
                WHEN receiver_id = %s THEN sender_id 
            END AS conversation_with
        FROM $table 
        WHERE education_center_id = %s 
        AND (sender_id = %s OR receiver_id = %s)
    ";
    $query_args = [$username, $username, $edu_center_id, $username, $username];
    $active_conversations = $wpdb->get_results($wpdb->prepare($query, $query_args));

    // Fetch contacts for mapping
    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);
    $admins = aspire_admin_get_admins($edu_center_id);

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
    foreach ($admins as $admin) {
        $contact_map[$admin['id']] = "{$admin['name']} (Admin)";
    }
    $contact_map['enigma_overlord'] = 'Instituto Admin'; // Treated as individual contact

    $output = '';
    $has_conversations = false;
    $seen_conversations = [];
    foreach ($active_conversations as $conv) {
        $conv_with = $conv->conversation_with;
        if (!$conv_with || isset($seen_conversations[$conv_with])) continue;
        $seen_conversations[$conv_with] = true;

        if (isset($group_names[$conv_with])) {
            $name = "Group: " . $group_names[$conv_with];
        } else {
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
// Admin: Shortcode
function aspire_admin_prochat_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to use this chat.</p>';
    }
    
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = get_educational_center_data();
    
    if (!aspire_admin_is_institute_admin($username, $edu_center_id)) {
        return '<p>You do not have the required permissions to use this chat.</p>';
    }
    
    $unread_count = aspire_admin_get_unread_count($username);
    $contacts = get_posts([
        'post_type' => ['teacher', 'students', 'parent'],
        'posts_per_page' => -1,
        'meta_key' => 'educational_center_id',
        'meta_value' => $edu_center_id,
    ]);
    $admins = aspire_admin_get_admins($edu_center_id);

    ob_start();
    ?>
    <div id="aspire-admin-prochat" class="chat-container">
        <div class="chat-wrapper">
            <div class="chat-sidebar">
                <div class="sidebar-header">
                    <h4>Inbox <span id="unread-badge" class="badge bg-danger"><?php echo $unread_count ?: ''; ?></span></h4>
                </div>
                <ul id="aspire-admin-conversations" class="conversation-list"></ul>
            </div>
            <div class="chat-main">
                <div class="chat-header">
                    <h5 id="current-conversation">Select a conversation</h5>
                    <div>
                        <button id="refresh-chat" class="btn btn-outline-info btn-sm"><i class="bi bi-arrow-repeat"></i> Refresh</button>
                        <button id="new-chat" class="btn btn-outline-primary btn-sm">New Chat</button>
                        <button id="clear-conversation" class="btn btn-outline-secondary btn-sm" style="display:none;">Clear</button>
                    </div>
                </div>
                <div id="aspire-admin-message-list" class="chat-messages"></div>
                <form id="aspire-admin-send-form" class="chat-form">
                    <div class="input-group">
                        <textarea id="aspire-admin-message-input" class="form-control" placeholder="Type your message..." rows="1" required></textarea>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
                    </div>
                    <div id="recipient-select" class="recipient-select" style="display:none;">
                        <select id="aspire-admin-target-value" class="form-select">
                            <option value="">Select a recipient</option>
                            <option value="all">All</option>
                            <option value="institute_admins">All Admins</option>
                            <option value="enigma_overlord">Instituto Admin</option>
                            <?php
                            $unique_contacts = [];
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
                                if (!$contact_id || $contact_id === $username) continue;
                                if (!isset($unique_contacts[$contact_id])) {
                                    $unique_contacts[$contact_id] = $label;
                                    echo '<option value="' . esc_attr($contact_id) . '">' . esc_html($label) . '</option>';
                                }
                            }
                            foreach ($admins as $admin) {
                                if ($admin['id'] !== $username && !isset($unique_contacts[$admin['id']])) {
                                    $unique_contacts[$admin['id']] = true;
                                    echo '<option value="' . esc_attr($admin['id']) . '">' . esc_html($admin['name'] . ' (Admin)') . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php wp_nonce_field('aspire_admin_nonce', 'aspire_admin_nonce_field'); ?>
                </form>
            </div>
        </div>
    </div>

    <style>
        .chat-loading { display: none; text-align: center; padding: 20px; color: #666; }
        .chat-loading.active { display: block; }
        .spinner { width: 30px; height: 30px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        let selectedConversation = localStorage.getItem('aspire_admin_selected_conversation') || '';
        let currentRecipient = '';

        function fetchMessages(conversationWith) {
            $('#aspire-admin-message-list').html('<div class="chat-loading active"><div class="spinner"></div><p>Loading messages...</p></div>');
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_admin_fetch_messages',
                    conversation_with: conversationWith,
                    nonce: $('#aspire_admin_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-admin-message-list').html(response.data.html);
                        $('#unread-badge').text(response.data.unread || '');
                        const chatMessages = document.querySelector('#aspire-admin-message-list');
                        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
                        updateTimestamps();
                    } else {
                        $('#aspire-admin-message-list').html('<p>Error loading messages.</p>');
                        console.error('Fetch messages failed:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $('#aspire-admin-message-list').html('<p>Network error occurred.</p>');
                    console.error('AJAX error fetching messages:', status, error);
                }
            });
        }

        function updateConversations() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_admin_fetch_conversations',
                    nonce: $('#aspire_admin_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-admin-conversations').html(response.data);
                        if (selectedConversation) {
                            $(`#aspire-admin-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
                        }
                    } else {
                        console.error('Failed to fetch conversations:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error fetching conversations:', status, error);
                }
            });
        }

        function updateTimestamps() {
            $('.chat-message .meta').each(function() {
                const timestamp = $(this).data('timestamp');
                $(this).text(`${$(this).text().split(' - ')[0]} - ${moment(timestamp).fromNow()}`);
            });
        }

        $(document).on('click', '#aspire-admin-conversations li', function() {
            $('#aspire-admin-conversations li').removeClass('active');
            $(this).addClass('active');
            selectedConversation = $(this).data('conversation-with');
            currentRecipient = selectedConversation;
            localStorage.setItem('aspire_admin_selected_conversation', selectedConversation);
            $('#current-conversation').text($(this).text());
            $('#clear-conversation').show();
            $('#new-chat').show();
            $('#recipient-select').hide();
            fetchMessages(selectedConversation);
        });

        $('#refresh-chat').click(function() {
            if (selectedConversation) {
                fetchMessages(selectedConversation); // Refresh messages for current conversation
            }
            updateConversations(); // Refresh conversation list
        });

        $('#clear-conversation').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_admin_selected_conversation');
            $('#current-conversation').text('Select a conversation');
            $('#clear-conversation').hide();
            $('#new-chat').show();
            $('#recipient-select').hide();
            $('#aspire-admin-message-list').empty();
            $('#aspire-admin-conversations li').removeClass('active');
        });

        $('#new-chat').click(function() {
            selectedConversation = '';
            currentRecipient = '';
            localStorage.removeItem('aspire_admin_selected_conversation');
            $('#current-conversation').text('New Conversation');
            $('#clear-conversation').show();
            $('#new-chat').hide();
            $('#recipient-select').show();
            $('#aspire-admin-message-list').empty();
            $('#aspire-admin-conversations li').removeClass('active');
            $('#aspire-admin-target-value').val('');
        });

        $('#aspire-admin-send-form').submit(function(e) {
            e.preventDefault();
            const message = $('#aspire-admin-message-input').val().trim();
            if (!message) return;

            let targetValue = $('#recipient-select').is(':visible') ? $('#aspire-admin-target-value').val() : currentRecipient;
            if (!targetValue) {
                alert('Please select a recipient.');
                return;
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'aspire_admin_send_message',
                    message: message,
                    target_value: targetValue,
                    nonce: $('#aspire_admin_nonce_field').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#aspire-admin-message-input').val('');
                        if ($('#recipient-select').is(':visible')) {
                            currentRecipient = targetValue;
                            $('#current-conversation').text($(`#aspire-admin-target-value option[value="${targetValue}"]`).text());
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
                    console.error('AJAX error sending message:', status, error);
                    alert('Network error occurred. Please try again.');
                }
            });
        });

        updateConversations();
        if (selectedConversation) {
            $(`#aspire-admin-conversations li[data-conversation-with="${selectedConversation}"]`).addClass('active');
            $('#current-conversation').text($(`#aspire-admin-conversations li[data-conversation-with="${selectedConversation}"]`).text());
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
add_shortcode('aspire_admin_prochat', 'aspire_admin_prochat_shortcode');