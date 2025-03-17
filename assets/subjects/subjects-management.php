<?php
add_action('rest_api_init', function () {
    register_rest_route('subjects/v1', '/view', array(
        'methods' => 'POST',
        'callback' => 'fetch_subjects_view',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

function fetch_subjects_view($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $params = $request->get_params();
    
    // Get these values from the request parameters
    $educational_center_id = sanitize_text_field($params['educational_center_id'] ?? '');
    $current_teacher_id = intval($params['teacher_id'] ?? 0);
    $subject_name = sanitize_text_field($params['subject_name'] ?? '');

    // Validate educational_center_id
    if (empty($educational_center_id)) {
        return ['success' => false, 'message' => 'Educational center ID is required'];
    }

    $query = "SELECT * FROM $table_name WHERE education_center_id = %s";
    $query_args = [$educational_center_id];

    if (!empty($subject_name)) {
        $query .= " AND subject_name LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($subject_name) . '%';
    }

    $results = $wpdb->get_results($wpdb->prepare($query, $query_args));

    ob_start();
    ?>
    <table class="subjects-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Education Center ID</th>
                <th>Subject Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)) : ?>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row->subject_id); ?></td>
                        <td><?php echo esc_html($row->education_center_id); ?></td>
                        <td><?php echo esc_html($row->subject_name); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3">No subjects found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $table_html = ob_get_clean();

    return ['success' => true, 'data' => ['html' => $table_html]];
}

function display_subjects_view($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    
    if (!$educational_center_id) {
        return '<p>Unable to retrieve educational center information.</p>';
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
          if (is_teacher($atts)) { 
        } else {
        $active_section = 'view-subjects';
        $main_section = 'student';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');}
        ?>
        <div class="subjects-wrapper">
            <div class="subjects-content">
                <h2>View Subjects</h2>
                <div class="search-filters">
                    <input type="text" id="view-subject-name" placeholder="Subject Name">
                    <button id="view-search-button">Search</button>
                </div>
                <div id="subjects-view-table">
                    <p class="loading-message">Loading subjects...</p>
                </div>
            </div>
        </div>
    </div>
    <style>
        .subjects-wrapper { padding: 20px; }
        .subjects-content { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .search-filters { margin-bottom: 20px; }
        .search-filters input, .search-filters select { margin-right: 10px; padding: 5px; }
        .actions { margin-bottom: 20px; }
        .actions button { margin-right: 10px; padding: 5px 10px; }
        .subjects-table { width: 100%; border-collapse: collapse; }
        .subjects-table th, .subjects-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .subjects-table th { background: #f5f5f5; }
        .loading-message { text-align: center; padding: 20px; }
        .crud-form, .add-form { margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
        .crud-form input, .add-form input { margin: 5px 0; padding: 5px; width: 200px; }
        .crud-form button, .add-form button { margin-top: 10px; padding: 5px 10px; }
    </style>
    <script>
    jQuery(document).ready(function($) {
        function fetchSubjects() {
            $('#subjects-view-table').html('<p class="loading-message">Loading subjects...</p>');
            $.ajax({
                url: '<?php echo esc_url(rest_url('subjects/v1/view')); ?>',
                method: 'POST',
                data: { 
                    subject_name: $('#view-subject-name').val(),
                    educational_center_id: '<?php echo esc_js($educational_center_id); ?>',
                    teacher_id: '<?php echo esc_js($current_teacher_id); ?>'
                },
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' },
                success: function(response) {
                    if (response.success) {
                        $('#subjects-view-table').html(response.data.html);
                    } else {
                        $('#subjects-view-table').html('<p>Error loading data: ' + (response.message || 'Unknown error') + '</p>');
                    }
                },
                error: function() {
                    $('#subjects-view-table').html('<p>Error loading data</p>');
                }
            });
        }
        $('#view-search-button').on('click', fetchSubjects);
        fetchSubjects();
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('subjects_view', 'display_subjects_view');