<?php
// Register REST endpoint for viewing
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
    $educational_center_id = get_educational_center_data();
    $subject_name = sanitize_text_field($params['subject_name'] ?? '');

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

function display_subjects_view() {
    ob_start();
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
    <script>
    jQuery(document).ready(function($) {
        function fetchSubjects() {
            $('#subjects-view-table').html('<p class="loading-message">Loading subjects...</p>');
            $.ajax({
                url: '<?php echo esc_url(rest_url('subjects/v1/view')); ?>',
                method: 'POST',
                data: { subject_name: $('#view-subject-name').val() },
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' },
                success: function(response) {
                    if (response.success) {
                        $('#subjects-view-table').html(response.data.html);
                    } else {
                        $('#subjects-view-table').html('<p>Error loading data</p>');
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