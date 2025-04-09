<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register custom REST endpoint for transport enrollments
add_action('rest_api_init', function () {
    register_rest_route('transport_enrollments/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_transport_enrollments_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

// Fetch transport enrollments data via REST
function fetch_transport_enrollments_data($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'transport_enrollments';
    $params = $request->get_params();
    // $educational_center_id = get_educational_center_data(); // Assuming this function exists

    // if (!$educational_center_id) {
    //     return array(
    //         'success' => false,
    //         'data' => array('html' => '<p>Educational center ID not found</p>')
    //     );
    // }
 // Get current user
 $current_user = wp_get_current_user();
 // $is_teacher_user = is_teacher($current_user->ID);

 if (is_teacher($current_user->ID)) { 
     $educational_center_id = educational_center_teacher_id();
     $current_teacher_id = aspire_get_current_teacher_id();
 } else {
     $educational_center_id = get_educational_center_data();
     $current_teacher_id = get_current_teacher_id();
 }
 if (empty($educational_center_id)) {
    wp_redirect(home_url('/login'));
    exit();

}
    $student_id = sanitize_text_field($params['student_id'] ?? '');
    $student_name = sanitize_text_field($params['student_name'] ?? '');
    $year = !empty($params['year']) ? intval($params['year']) : '';
    $status = sanitize_text_field($params['status'] ?? '');

    // Build dynamic query
    $query = "SELECT * FROM $table_name WHERE education_center_id = %s";
    $query_args = [$educational_center_id];

    if (!empty($student_id)) {
        $query .= " AND student_id LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($student_id) . '%';
    }
    if (!empty($student_name)) {
        $query .= " AND student_id IN (
            SELECT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = 'student_id' 
            AND post_id IN (
                SELECT ID 
                FROM {$wpdb->posts} 
                WHERE post_type = 'students' 
                AND post_title LIKE %s
            )
        )";
        $query_args[] = '%' . $wpdb->esc_like($student_name) . '%';
    }
    if ($year > 0) {
        $query .= " AND YEAR(enrollment_date) = %d";
        $query_args[] = $year;
    }
    if ($status !== '') { // Since active is TINYINT(1), we use 0 or 1
        $query .= " AND active = %d";
        $query_args[] = $status === 'active' ? 1 : 0;
    }

    $query .= " ORDER BY enrollment_date DESC";
    $enrollments = $wpdb->get_results($wpdb->prepare($query, $query_args));

    $student_posts = get_posts([
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'educational_center_id',
                'value' => $educational_center_id,
                'compare' => '='
            ]
        ]
    ]);

    $students_lookup = [];
    foreach ($student_posts as $student_post) {
        $student_id = get_post_meta($student_post->ID, 'student_id', true);
        $students_lookup[$student_id] = [
            'name' => get_the_title($student_post->ID),
            'class' => get_post_meta($student_post->ID, 'class', true) ?: 'Unknown Class',
            'section' => get_post_meta($student_post->ID, 'section', true) ?: 'Unknown Section',
        ];
    }

    $student_enrollments = [];
    foreach ($enrollments as $enrollment) {
        $student_key = $enrollment->student_id;
        if (!isset($student_enrollments[$student_key])) {
            $student_info = $students_lookup[$student_key] ?? ['name' => 'Unknown Student', 'class' => 'Unknown Class', 'section' => 'Unknown Section'];
            $student_enrollments[$student_key] = [
                'name' => $student_info['name'],
                'student_id' => $student_key,
                'class' => $student_info['class'],
                'section' => $student_info['section'],
                'enrollments' => [],
                'counts' => ['Active' => 0, 'Inactive' => 0]
            ];
        }
        $student_enrollments[$student_key]['enrollments'][] = $enrollment;
        $student_enrollments[$student_key]['counts'][$enrollment->active ? 'Active' : 'Inactive']++;
    }

    ob_start();
    ?>
    <table id="transport-enrollments-table" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>View</th>
                <th>Name</th>
                <th>Student ID</th>
                <th>Class</th>
                <th>Active Enrollments</th>
                <th>Inactive Enrollments</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($student_enrollments)) : ?>
                <?php foreach ($student_enrollments as $student_id => $data) : ?>
                    <tr class="enrollment-row" data-student-id="<?php echo esc_attr($student_id); ?>">
                        <td>
                            <button class="expand-details" data-student-id="<?php echo esc_attr($student_id); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                            </button>
                        </td>
                        <td><?php echo esc_html($data['name']); ?></td>
                        <td><?php echo esc_html($student_id); ?></td>
                        <td><?php echo esc_html($data['class'] . ' - ' . $data['section']); ?></td>
                        <td><?php echo esc_html($data['counts']['Active']); ?></td>
                        <td><?php echo esc_html($data['counts']['Inactive']); ?></td>
                    </tr>
                    <tr class="enrollment-details" id="enrollment-details-<?php echo esc_attr($student_id); ?>" style="display: none;">
                        <td colspan="6">
                            <div class="enrollment-details-content">
                                <div class="enrollment-detail-header">
                                    <h3>Transport Enrollment Summary for <?php echo esc_html($data['name']); ?></h3>
                                    <div class="summary-stats">
                                        <div class="stat-box">
                                            <span class="stat-label">Total Enrollments:</span>
                                            <span class="stat-value"><?php echo esc_html($data['counts']['Active'] + $data['counts']['Inactive']); ?></span>
                                        </div>
                                        <div class="stat-box">
                                            <span class="stat-label">Active Enrollments:</span>
                                            <span class="stat-value"><?php echo esc_html($data['counts']['Active']); ?></span>
                                        </div>
                                        <div class="stat-box">
                                            <span class="stat-label">Inactive Enrollments:</span>
                                            <span class="stat-value"><?php echo esc_html($data['counts']['Inactive']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="toggle-details-section">
                                    <button class="toggle-details-btn" data-student-id="<?php echo esc_attr($student_id); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                                    </button>View Details
                                </div>
                                <div class="enrollment-detail-entries" id="enrollment-entries-<?php echo esc_attr($student_id); ?>" style="display: none;">
                                    <?php if (!empty($data['enrollments'])) : ?>
                                        <?php foreach ($data['enrollments'] as $enrollment) : ?>
                                            <div class="enrollment-detail-entry">
                                                <div class="entry-grid">
                                                    <div class="entry-item">
                                                        <span class="entry-label">Enrollment Date:</span>
                                                        <span class="entry-value"><?php echo esc_html($enrollment->enrollment_date); ?></span>
                                                    </div>
                                                    <div class="entry-item">
                                                        <span class="entry-label">Status:</span>
                                                        <span class="entry-value <?php echo $enrollment->active ? 'active' : 'inactive'; ?>">
                                                            <?php echo $enrollment->active ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="no-records">No enrollment records found for this student.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6">No transport enrollments found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $table_html = ob_get_clean();

    return array(
        'success' => true,
        'data' => array('html' => $table_html)
    );
}

// Frontend Interface for Transport Enrollments
function view_transport_enrollments_shortcode() {
    global $wpdb;
    // $educational_center_id = get_educational_center_data();

    // if (empty($educational_center_id)) {
    //     return '<p>No Educational Center found.</p>';
    // }
 // Get current user
 $current_user = wp_get_current_user();
 // $is_teacher_user = is_teacher($current_user->ID);

 if (is_teacher($current_user->ID)) { 
     $educational_center_id = educational_center_teacher_id();
     $current_teacher_id = aspire_get_current_teacher_id();
 } else {
     $educational_center_id = get_educational_center_data();
     $current_teacher_id = get_current_teacher_id();
 }
 if (empty($educational_center_id)) {
    wp_redirect(home_url('/login'));
    exit();

}
    $years = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT YEAR(enrollment_date) AS year FROM {$wpdb->prefix}transport_enrollments WHERE education_center_id = %s ORDER BY year DESC",
        $educational_center_id
    ));

    ob_start();
    ?>
    <div class="attendance-main-wrapper">
        <div class="form-container attendance-content-wrapper">
           
            <div class="enrollments-management">
                <h2>Transport Enrollments Management</h2>
                <div class="filters-card">
                    <div class="search-filters">
                        <label for="search-student-id">Student ID:</label>
                        <input type="text" id="search-student-id" placeholder="Student ID" class="filter-input">
                        <label for="search-student-name">Student Name:</label>
                        <input type="text" id="search-student-name" placeholder="Student Name" class="filter-input">
                        <label for="search-year">Year:</label>
                        <select id="search-year" class="filter-select">
                            <option value="">All Years</option>
                            <?php foreach ($years as $year) : ?>
                                <option value="<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="search-status">Status:</label>
                        <select id="search-status" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button id="search-button" class="search-btn">Search</button>
                    </div>
                </div>
                <div class="enrollments-table-wrapper" id="transport-enrollments-table-container">
                    <p class="loading-message">Loading transport enrollments data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="transport-enrollment-details-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <div id="modal-content-inner"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function loadTransportEnrollmentsData() {
            $('#transport-enrollments-table-container').html('<p class="loading-message">Loading transport enrollments data...</p>');
            $.ajax({
                url: '<?php echo esc_url(rest_url('transport_enrollments/v1/fetch')); ?>',
                method: 'POST',
                data: {
                    student_id: $('#search-student-id').val(),
                    student_name: $('#search-student-name').val(),
                    year: $('#search-year').val(),
                    status: $('#search-status').val(),
                    _wpnonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#transport-enrollments-table-container').html(response.data.html);
                        setupTableEvents();
                    } else {
                        $('#transport-enrollments-table-container').html('<p>Error loading transport enrollments data</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#transport-enrollments-table-container').html('<p>Error: ' + error + '</p>');
                }
            });
        }

        function setupTableEvents() {
            $('.expand-details').off('click').on('click', function() {
                var studentId = $(this).data('student-id');
                var $detailsRow = $('#enrollment-details-' + studentId);
                var $modal = $('#transport-enrollment-details-modal');
                var $modalContent = $('#modal-content-inner');

                // Clone the details content exactly as it was
                $modalContent.html($detailsRow.find('.enrollment-details-content').clone());
                $modal.css('display', 'block');
                
                // Setup enrollment details toggle within modal
                $modalContent.find('.toggle-details-btn').on('click', function(e) {
                    e.stopPropagation();
                    var studentId = $(this).data('student-id');
                    var $enrollmentEntries = $modalContent.find('#enrollment-entries-' + studentId);
                    $(this).toggleClass('active');
                    $enrollmentEntries.slideToggle(300);
                });

                // Keep the original row hidden but present in DOM
                $detailsRow.css('display', 'none');
            });

            // Modal close handler
            $('.modal-close').on('click', function() {
                $('#transport-enrollment-details-modal').css('display', 'none');
                $('#modal-content-inner').empty();
            });

            // Close modal when clicking outside
            $(window).on('click', function(event) {
                if (event.target == $('#transport-enrollment-details-modal')[0]) {
                    $('#transport-enrollment-details-modal').css('display', 'none');
                    $('#modal-content-inner').empty();
                }
            });
        }

        loadTransportEnrollmentsData();

        $('#search-button').on('click', function(e) {
            e.preventDefault();
            loadTransportEnrollmentsData();
        });

        var timeoutId;
        $('#search-student-id, #search-student-name').on('keyup', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(loadTransportEnrollmentsData, 500);
        });

        $('#search-year, #search-status').on('change', function() {
            loadTransportEnrollmentsData();
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('view_transport_enrollments', 'view_transport_enrollments_shortcode');
?>