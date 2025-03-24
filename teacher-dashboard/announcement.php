<?php

// Enqueue scripts and styles
function aspire_notice_board_enqueue_scripts() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style('aspire-notice-board', plugins_url('aspire-notice-board.css', __FILE__));
    wp_enqueue_script('jquery', false, [], false, true);
    wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js', [], '2.29.1', true);
}
add_action('wp_enqueue_scripts', 'aspire_notice_board_enqueue_scripts');

// Shared function to get announcements
function aspire_get_announcements($username, $role = 'teacher') {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_announcements';
    $edu_center_id = ($role === 'teacher') ? educational_center_teacher_id() : get_educational_center_data();

    $query = "SELECT * FROM $table WHERE education_center_id = %s";
    $query_args = [$edu_center_id];

    if ($role === 'teacher') {
        // Teachers see announcements to 'all', 'teachers', or sent by themselves
        $query .= " AND (receiver_id IN ('all', 'teachers') OR sender_id = %s)";
        $query_args[] = $username;
    } elseif ($role === 'admin') {
        // Admins see all announcements in their center
        // No additional filtering needed
    }
    

    $query .= " ORDER BY timestamp DESC";
    $prepared_query = $wpdb->prepare($query, $query_args);
    error_log("$role announcements query: $prepared_query");
    $results = $wpdb->get_results($prepared_query);
    error_log("$role announcements for $username: " . print_r($results, true));
    return $results;
}

// Shared function to send announcements
function aspire_send_announcement($sender_id, $receiver_id, $message, $edu_center_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'aspire_announcements';
    $data = [
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'message' => sanitize_text_field($message),
        'education_center_id' => $edu_center_id,
        'timestamp' => current_time('mysql')
    ];
    $result = $wpdb->insert($table, $data);
    error_log("Send announcement result for $sender_id to $receiver_id: " . ($result ? 'Success' : 'Failed - ' . $wpdb->last_error));
    return $result;
}

// Shared recipient options
function aspire_get_recipient_options() {
    return [
        'all' => 'Everyone',
        'teachers' => 'Teachers',
        'students' => 'Students',
        'parents' => 'Parents',
        'institute_admins' => 'Admins'
    ];
}

// Teacher Notice Board Shortcode
function aspire_teacher_notice_board_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view the notice board.</p>';
    }
    
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = educational_center_teacher_id();
    
    if (!aspire_is_teacher($user)) {
        return '<p>You do not have permission to view this notice board.</p>';
    }

    $announcements = aspire_get_announcements($username, 'teacher');
    $recipients = aspire_get_recipient_options();
    ob_start();
    ?>
    <div id="aspire-teacher-notice-board" class="notice-board-container">
        <div class="notice-board-header">Teacher Notice Board</div>
        <div id="announcement-list" class="announcement-list">
            <?php foreach ($announcements as $ann): ?>
                <div class="announcement-item">
                    <div class="announcement-content">
                        <span class="announcement-meta">
                            <?php echo esc_html($ann->sender_id === $username ? 'You' : $ann->sender_id); ?> 
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
        <form id="aspire-teacher-announcement-form" class="announcement-form">
            <div class="input-group">
                <textarea id="announcement-input" class="form-control" placeholder="Post a new announcement..." required></textarea>
                <select id="announcement-target" class="form-select">
                    <?php foreach ($recipients as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Post</button>
            </div>
            <?php wp_nonce_field('aspire_teacher_notice_nonce', 'aspire_teacher_notice_nonce_field'); ?>
        </form>
    </div>
    <?php
    $script = "
    jQuery(document).ready(function($) {
        function fetchAnnouncements() {
            $.ajax({
                url: '" . admin_url('admin-ajax.php') . "',
                method: 'POST',
                data: {
                    action: 'aspire_teacher_fetch_announcements',
                    nonce: $('#aspire_teacher_notice_nonce_field').val()
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

        $('#aspire-teacher-announcement-form').on('submit', function(e) {
            e.preventDefault();
            var message = $('#announcement-input').val();
            var target = $('#announcement-target').val();
            var nonce = $('#aspire_teacher_notice_nonce_field').val();
            console.log('Submitting announcement: ' + message + ' to ' + target);
            $.ajax({
                url: '" . admin_url('admin-ajax.php') . "',
                method: 'POST',
                data: {
                    action: 'aspire_teacher_send_announcement',
                    message: message,
                    target: target,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Announcement posted successfully');
                        $('#announcement-input').val('');
                        fetchAnnouncements();
                    } else {
                        console.log('Post failed: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX post error: ' + error);
                }
            });
        });

        fetchAnnouncements();
        setInterval(fetchAnnouncements, 30000);
        updateTimestamps();
    });
    ";
    wp_add_inline_script('moment', $script, 'after');
    return ob_get_clean();
}
add_shortcode('aspire_teacher_notice_board', 'aspire_teacher_notice_board_shortcode');

// Admin Notice Board Shortcode
function aspire_admin_notice_board_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view the notice board.</p>';
    }
    
    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = get_educational_center_data();
    
    if (!aspire_is_institute_admin($username, $edu_center_id)) {
        return '<p>You do not have permission to view this notice board.</p>';
    }

    $announcements = aspire_get_announcements($username, 'admin');
    $recipients = aspire_get_recipient_options();
    ob_start();
    ?>
    <div id="aspire-admin-notice-board" class="notice-board-container">
        <div class="notice-board-header">Admin Notice Board</div>
        <div id="announcement-list" class="announcement-list">
            <?php foreach ($announcements as $ann): ?>
                <div class="announcement-item">
                    <div class="announcement-content">
                        <span class="announcement-meta">
                            <?php echo esc_html($ann->sender_id === $username ? 'You' : $ann->sender_id); ?> 
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
        <form id="aspire-admin-announcement-form" class="announcement-form">
            <div class="input-group">
                <textarea id="announcement-input" class="form-control" placeholder="Post a new announcement..." required></textarea>
                <select id="announcement-target" class="form-select">
                    <?php foreach ($recipients as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Post</button>
            </div>
            <?php wp_nonce_field('aspire_admin_notice_nonce', 'aspire_admin_notice_nonce_field'); ?>
        </form>
    </div>
    <?php
    $script = "
    jQuery(document).ready(function($) {
        function fetchAnnouncements() {
            $.ajax({
                url: '" . admin_url('admin-ajax.php') . "',
                method: 'POST',
                data: { action: 'aspire_admin_fetch_announcements', nonce: $('#aspire_admin_notice_nonce_field').val() },
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

        $('#aspire-admin-announcement-form').on('submit', function(e) {
            e.preventDefault();
            var message = $('#announcement-input').val();
            var target = $('#announcement-target').val();
            var nonce = $('#aspire_admin_notice_nonce_field').val();
            console.log('Submitting announcement: ' + message + ' to ' + target);
            $.ajax({
                url: '" . admin_url('admin-ajax.php') . "',
                method: 'POST',
                data: {
                    action: 'aspire_admin_send_announcement',
                    message: message,
                    target: target,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Announcement posted successfully');
                        $('#announcement-input').val('');
                        fetchAnnouncements();
                    } else {
                        console.log('Post failed: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX post error: ' + error);
                }
            });
        });

        fetchAnnouncements();
        setInterval(fetchAnnouncements, 30000);
        updateTimestamps();
    });
    ";
    wp_add_inline_script('moment', $script, 'after');
    return ob_get_clean();
}
add_shortcode('aspire_admin_notice_board', 'aspire_admin_notice_board_shortcode');

// Teacher AJAX Handlers
function aspire_teacher_fetch_announcements() {
    check_ajax_referer('aspire_teacher_notice_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;
    $announcements = aspire_get_announcements($username, 'teacher');
    $recipients = aspire_get_recipient_options();

    $output = '';
    foreach ($announcements as $ann) {
        $output .= '<div class="announcement-item">';
        $output .= '<div class="announcement-content">';
        $output .= '<span class="announcement-meta">' . esc_html($ann->sender_id === $username ? 'You' : $ann->sender_id) . ' to ';
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
add_action('wp_ajax_aspire_teacher_fetch_announcements', 'aspire_teacher_fetch_announcements');

function aspire_teacher_send_announcement() {
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'aspire_teacher_notice_nonce')) {
        error_log("Teacher send failed: Invalid nonce - $nonce");
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = educational_center_teacher_id();
    $message = sanitize_text_field($_POST['message'] ?? '');
    $target = sanitize_text_field($_POST['target'] ?? '');
    $valid_targets = array_keys(aspire_get_recipient_options());

    error_log("Teacher send announcement: username=$username, target=$target, message=$message, nonce=$nonce");

    if (!aspire_is_teacher($user)) {
        error_log("Teacher send failed: Not a teacher");
        wp_send_json_error(['message' => 'Permission denied']);
    }

    if (!in_array($target, $valid_targets)) {
        error_log("Teacher send failed: Invalid target - $target");
        wp_send_json_error(['message' => 'Invalid target']);
    }

    if (empty($message)) {
        error_log("Teacher send failed: Empty message");
        wp_send_json_error(['message' => 'Message cannot be empty']);
    }

    $result = aspire_send_announcement($username, $target, $message, $edu_center_id);
    if ($result) {
        error_log("Teacher send success: Announcement posted");
        wp_send_json_success();
    } else {
        error_log("Teacher send failed: Database insert error - " . $GLOBALS['wpdb']->last_error);
        wp_send_json_error(['message' => 'Failed to post announcement']);
    }
}
add_action('wp_ajax_aspire_teacher_send_announcement', 'aspire_teacher_send_announcement');

// Admin AJAX Handlers
function aspire_admin_fetch_announcements() {
    check_ajax_referer('aspire_admin_notice_nonce', 'nonce');
    $user = wp_get_current_user();
    $username = $user->user_login;
    $announcements = aspire_get_announcements($username, 'admin');
    $recipients = aspire_get_recipient_options();

    $output = '';
    foreach ($announcements as $ann) {
        $output .= '<div class="announcement-item">';
        $output .= '<div class="announcement-content">';
        $output .= '<span class="announcement-meta">' . esc_html($ann->sender_id === $username ? 'You' : $ann->sender_id) . ' to ';
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
add_action('wp_ajax_aspire_admin_fetch_announcements', 'aspire_admin_fetch_announcements');

function aspire_admin_send_announcement() {
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'aspire_admin_notice_nonce')) {
        error_log("Admin send failed: Invalid nonce - $nonce");
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    $user = wp_get_current_user();
    $username = $user->user_login;
    $edu_center_id = get_educational_center_data();
    $message = sanitize_text_field($_POST['message'] ?? '');
    $target = sanitize_text_field($_POST['target'] ?? '');
    $valid_targets = array_keys(aspire_get_recipient_options());

    error_log("Admin send announcement: username=$username, target=$target, message=$message, nonce=$nonce");

    if (!aspire_is_institute_admin($username, $edu_center_id)) {
        error_log("Admin send failed: Not an admin");
        wp_send_json_error(['message' => 'Permission denied']);
    }

    if (!in_array($target, $valid_targets)) {
        error_log("Admin send failed: Invalid target - $target");
        wp_send_json_error(['message' => 'Invalid target']);
    }

    if (empty($message)) {
        error_log("Admin send failed: Empty message");
        wp_send_json_error(['message' => 'Message cannot be empty']);
    }

    $result = aspire_send_announcement($username, $target, $message, $edu_center_id);
    if ($result) {
        error_log("Admin send success: Announcement posted");
        wp_send_json_success();
    } else {
        error_log("Admin send failed: Database insert error - " . $GLOBALS['wpdb']->last_error);
        wp_send_json_error(['message' => 'Failed to post announcement']);
    }
}
add_action('wp_ajax_aspire_admin_send_announcement', 'aspire_admin_send_announcement');