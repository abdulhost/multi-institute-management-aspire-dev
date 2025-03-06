<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_dashboard_shortcode() {
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'view-fees';
    $education_center_id = get_educational_center_data();

    ob_start();
    ?>
    <div class="erp-dashboard" style="display: flex;">
        <?php
        // Dynamically set active section for sidebar
        $active_section = str_replace('-', '-', $section); // e.g., "add-fee" -> "add_fee"
        include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path
        ?>
        <div class="dashboard-content" style="flex: 1; padding: 20px;">
            <?php
            switch ($section) {
                case 'view-fees':
                    echo fees_institute_dashboard_shortcode();
                    break;
                case 'add-fees':
                    echo add_fees_institute_dashboard_shortcode();
                    break;
                case 'update-fees':
                    echo edit_fees_frontend_shortcode();
                    break;
                case 'delete-fees':
                    echo delete_fees_institute_dashboard_shortcode();
                    break;
                case 'fees-templates':
                    echo fee_templates_institute_dashboard_shortcode();
                    break;
                case 'add-fees-template':
                    echo add_fee_templates_institute_dashboard_shortcode();
                    break;
                case 'update-fees-template':
                    echo edit_fee_templates_institute_dashboard_shortcode();
                    break;
                case 'delete-fees-template':
                    echo delete_fee_templates_institute_dashboard_shortcode();
                    break;
                default:
                    echo '<p>Section not found. Please select a valid option.</p>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('my_education_erp_dashboard', 'my_education_erp_dashboard_shortcode');

// Register custom REST endpoint for fees
add_action('rest_api_init', function () {
    register_rest_route('fees/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_fees_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

// Fetch fees data via REST (monthly aggregation)
function fetch_fees_data($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_fees';
    $params = $request->get_params();
    $educational_center_id = get_educational_center_data();
    
    if (!$educational_center_id) {
        return array(
            'success' => false,
            'data' => array('html' => '<p>Educational center ID not found</p>')
        );
    }

    $student_id = sanitize_text_field($params['student_id'] ?? '');
    $student_name = sanitize_text_field($params['student_name'] ?? '');
    $year = !empty($params['year']) ? intval($params['year']) : date('Y');
    $status = sanitize_text_field($params['status'] ?? '');

    // Build dynamic query
    $query = "SELECT sf.*, ft.name AS template_name 
              FROM $table_name sf 
              LEFT JOIN {$wpdb->prefix}fee_templates ft ON sf.template_id = ft.id 
              WHERE sf.education_center_id = %s";
    $query_args = [$educational_center_id];

    if (!empty($student_id)) {
        $query .= " AND sf.student_id LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($student_id) . '%';
    }
    if (!empty($student_name)) {
        $query .= " AND sf.student_id IN (
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
        $query .= " AND SUBSTRING(sf.month_year, 1, 4) = %d";
        $query_args[] = $year;
    }
    if (!empty($status)) {
        $query .= " AND sf.status = %s";
        $query_args[] = $status;
    }

    $fees = $wpdb->get_results($wpdb->prepare($query, $query_args));

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
        $admission_date = get_post_meta($student_post->ID, 'admission_date', true); // Assuming this exists
        $students_lookup[$student_id] = [
            'name' => get_the_title($student_post->ID),
            'class' => get_post_meta($student_post->ID, 'class', true) ?: 'Unknown Class',
            'section' => get_post_meta($student_post->ID, 'section', true) ?: 'Unknown Section',
            'admission_date' => $admission_date ?: date('Y-m') // Default to current month if not set
        ];
    }

    $student_fees = [];
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    $current_date = new DateTime(date('Y-m')); // Real current date

    foreach ($fees as $fee) {
        $student_key = $fee->student_id;
        $month_num = (int)substr($fee->month_year, 5, 2);
        if (!isset($student_fees[$student_key])) {
            $student_info = $students_lookup[$student_key] ?? ['name' => 'Unknown Student', 'class' => 'Unknown Class', 'section' => 'Unknown Section', 'admission_date' => date('Y-m')];
            $admission_date = new DateTime($student_info['admission_date']);
            
            // Calculate pending months
            $pending_months = [];
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($admission_date, $interval, $current_date);
            $paid_months = [];
            
            foreach ($fees as $f) {
                if ($f->student_id === $student_key && $f->status === 'paid') {
                    $paid_months[] = $f->month_year;
                }
            }
            
            foreach ($period as $dt) {
                $month_year = $dt->format('Y-m');
                if (!in_array($month_year, $paid_months) && $dt <= $current_date) {
                    $pending_months[] = $month_year;
                }
            }

            $student_fees[$student_key] = [
                'name' => $student_info['name'],
                'admission_no' => $student_key,
                'class' => $student_info['class'],
                'section' => $student_info['section'],
                'counts' => ['Paid' => 0, 'Pending' => 0, 'Overdue' => 0],
                'monthly' => array_fill_keys(array_keys($months), ''),
                'total_paid' => 0,
                'total_amount' => 0,
                'pending_months' => $pending_months,
                'pending_count' => count($pending_months)
            ];
        }
        $status_short = '';
        switch ($fee->status) {
            case 'paid':
                $status_short = 'P';
                $student_fees[$student_key]['counts']['Paid']++;
                $student_fees[$student_key]['total_paid'] += floatval($fee->amount);
                break;
            case 'pending':
                $status_short = 'L';
                $student_fees[$student_key]['counts']['Pending']++;
                break;
            default:
                $status_short = 'A';
                $student_fees[$student_key]['counts']['Overdue']++;
                break;
        }
        $student_fees[$student_key]['total_amount'] += floatval($fee->amount);
        $student_fees[$student_key]['monthly'][$month_num] = $status_short;
    }

    ob_start();
    ?>
    <table id="fees-table" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>View</th>
                <th>Name</th>
                <th>Student ID</th>
                <th>Class</th>
                <th>Paid (P)</th>
                <th>Pending (L)</th>
                <th>Overdue (A)</th>
                <?php foreach ($months as $month_num => $month_name) : ?>
                    <th><?php echo esc_html($month_name); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($student_fees)) : ?>
                <?php foreach ($student_fees as $student_id => $data) : ?>
                    <?php
                    $total_fees = $data['counts']['Paid'] + $data['counts']['Pending'] + $data['counts']['Overdue'];
                    $percent = $total_fees > 0 ? round(($data['counts']['Paid'] / $total_fees) * 100) : 0;
                    ?>
                    <tr class="fee-row" data-student-id="<?php echo esc_attr($student_id); ?>">
                        <td>
                            <button class="expand-details" data-student-id="<?php echo esc_attr($student_id); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                            </button>
                        </td>
                        <td><?php echo esc_html($data['name']); ?></td>
                        <td><?php echo esc_html($student_id); ?></td>
                        <td><?php echo esc_html($data['class'] . ' - ' . $data['section']); ?></td>
                        <td><?php echo esc_html($data['counts']['Paid']); ?></td>
                        <td><?php echo esc_html($data['counts']['Pending']); ?></td>
                        <td><?php echo esc_html($data['counts']['Overdue']); ?></td>
                        <?php foreach ($months as $month_num => $month_name) : ?>
                            <td><?php echo esc_html($data['monthly'][$month_num] ?? ''); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr class="fee-details" id="fee-details-<?php echo esc_attr($student_id); ?>" style="display: none;">
                        <td colspan="<?php echo 8 + count($months); ?>">
                            <div class="fee-details-content">
                                <div class="fee-detail-header">
                                    <h3>Fee Summary for <?php echo esc_html($data['name']); ?></h3>
                                    <div class="summary-stats">
                                        <div class="stat-box">
                                            <span class="stat-label">Total Amount:</span>
                                            <span class="stat-value"><?php echo esc_html(number_format($data['total_amount'], 2)); ?></span>
                                        </div>
                                        <div class="stat-box">
                                            <span class="stat-label">Total Paid:</span>
                                            <span class="stat-value"><?php echo esc_html(number_format($data['total_paid'], 2)); ?></span>
                                        </div>
                                        <div class="stat-box pending-months-box">
                                            <span class="stat-label">Pending Months:</span>
                                            <span class="stat-value">
                                                <?php echo esc_html($data['pending_count']); ?>
                                                <?php if ($data['pending_count'] > 0) : ?>
                                                    <button class="expand-pending-details" data-student-id="<?php echo esc_attr($student_id); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z"/></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </span>
                                            <div class="pending-details-dropdown" id="pending-details-<?php echo esc_attr($student_id); ?>" style="display: none;">
                                                <?php if (!empty($data['pending_months'])) : ?>
                                                    <?php foreach ($data['pending_months'] as $pending_month) : ?>
                                                        <div class="pending-month-item">
                                                            <?php echo esc_html(date('F Y', strtotime($pending_month))); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else : ?>
                                                    <div class="pending-month-item">No pending months</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="toggle-details-section">
                                    <button class="toggle-details-btn" data-student-id="<?php echo esc_attr($student_id); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                                    </button>View Details
                                </div>
                                <div class="fee-detail-entries" id="fee-entries-<?php echo esc_attr($student_id); ?>" style="display: none;">
                                    <?php
                                    $student_fees_details = $wpdb->get_results($wpdb->prepare(
                                        "SELECT sf.*, ft.name AS template_name 
                                         FROM {$wpdb->prefix}student_fees sf 
                                         LEFT JOIN {$wpdb->prefix}fee_templates ft ON sf.template_id = ft.id 
                                         WHERE sf.student_id = %s AND sf.education_center_id = %s AND SUBSTRING(sf.month_year, 1, 4) = %d 
                                         ORDER BY sf.month_year",
                                        $student_id, $educational_center_id, $year
                                    ));
                                    if (!empty($student_fees_details)) {
                                        foreach ($student_fees_details as $fee_detail) {
                                            ?>
                                            <div class="fee-detail-entry">
                                                <div class="entry-grid">
                                                    <div class="entry-item">
                                                        <span class="entry-label">Month:</span>
                                                        <span class="entry-value"><?php echo esc_html(date('F', mktime(0, 0, 0, (int)substr($fee_detail->month_year, 5, 2), 1))); ?></span>
                                                    </div>
                                                    <div class="entry-item">
                                                        <span class="entry-label">Fee Type:</span>
                                                        <span class="entry-value"><?php echo esc_html($fee_detail->template_name ?? 'N/A'); ?></span>
                                                    </div>
                                                    <div class="entry-item">
                                                        <span class="entry-label">Amount:</span>
                                                        <span class="entry-value"><?php echo esc_html(number_format((float)$fee_detail->amount, 2)); ?></span>
                                                    </div>
                                                    <div class="entry-item">
                                                        <span class="entry-label">Status:</span>
                                                        <span class="entry-value <?php echo esc_attr($fee_detail->status); ?>">
                                                            <?php echo esc_html($fee_detail->status); ?>
                                                        </span>
                                                    </div>
                                                    <div class="entry-item">
                                                        <span class="entry-label">Payment Method:</span>
                                                        <span class="entry-value"><?php echo esc_html($fee_detail->payment_method ?: 'N/A'); ?></span>
                                                    </div>
                                                    <?php if ($fee_detail->cheque_number) : ?>
                                                        <div class="entry-item">
                                                            <span class="entry-label">Cheque Number:</span>
                                                            <span class="entry-value"><?php echo esc_html($fee_detail->cheque_number); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($fee_detail->paid_date) : ?>
                                                        <div class="entry-item">
                                                            <span class="entry-label">Paid Date:</span>
                                                            <span class="entry-value"><?php echo esc_html($fee_detail->paid_date); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo '<p class="no-records">No detailed fee records found for this student in the selected year.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="<?php echo 8 + count($months); ?>">No fees found</td></tr>
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

// Frontend Interface
function fees_institute_dashboard_shortcode() {
    global $wpdb;
    $educational_center_id = get_educational_center_data();

    if (empty($educational_center_id)) {
        return '<p>No Educational Center found.</p>';
    }

    $years = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT SUBSTRING(month_year, 1, 4) AS year FROM {$wpdb->prefix}student_fees WHERE education_center_id = %s ORDER BY year DESC",
        $educational_center_id
    ));

    ob_start();
    ?>
    <div class="attendance-main-wrapper">
        <div class="form-container attendance-content-wrapper">
            <div class="custom-wrap">
                <div class="dashboard-header" style="margin-bottom: 15px;">
                    <a href="<?php echo esc_url(home_url('/institute-dashboard/fees/?section=add-fees')); ?>" 
                       class="add-fee-btn">+ Add Fee</a>
                </div>
                <div class="actions" style="margin-bottom: 20px;">
                    <button id="bulk-import-button">Bulk Import</button>
                </div>
            </div>  
            <div class="fees-management">
                <h2>Fees Management</h2>
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
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                        </select>
                        <button id="search-button" class="search-btn">Search</button>
                    </div>
                </div>
                <div class="fees-table-wrapper" id="fees-table-container">
                    <p class="loading-message">Loading fees data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="fee-details-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <div id="modal-content-inner"></div>
        </div>
    </div>

   
    <script>
    jQuery(document).ready(function($) {
        function loadFeesData() {
            $('#fees-table-container').html('<p class="loading-message">Loading fees data...</p>');
            $.ajax({
                url: '<?php echo esc_url(rest_url('fees/v1/fetch')); ?>',
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
                        $('#fees-table-container').html(response.data.html);
                        setupTableEvents();
                    } else {
                        $('#fees-table-container').html('<p>Error loading fees data</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#fees-table-container').html('<p>Error: ' + error + '</p>');
                }
            });
        }

        function setupTableEvents() {
            $('.expand-details').off('click').on('click', function() {
                var studentId = $(this).data('student-id');
                var $detailsRow = $('#fee-details-' + studentId);
                var $modal = $('#fee-details-modal');
                var $modalContent = $('#modal-content-inner');

                // Clone the details content exactly as it was
                $modalContent.html($detailsRow.find('.fee-details-content').clone());
                $modal.css('display', 'block');
                
                // Setup pending details toggle within modal
                $modalContent.find('.expand-pending-details').on('click', function(e) {
                    e.stopPropagation();
                    var studentId = $(this).data('student-id');
                    var $pendingDetails = $modalContent.find('#pending-details-' + studentId);
                    $(this).toggleClass('active');
                    $pendingDetails.slideToggle(200);
                });

                // Setup fee details toggle within modal
                $modalContent.find('.toggle-details-btn').on('click', function(e) {
                    e.stopPropagation();
                    var studentId = $(this).data('student-id');
                    var $feeEntries = $modalContent.find('#fee-entries-' + studentId);
                    $(this).toggleClass('active');
                    $feeEntries.slideToggle(300);
                });

                // Close dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.pending-months-box').length) {
                        $modalContent.find('.pending-details-dropdown').slideUp(200);
                        $modalContent.find('.expand-pending-details').removeClass('active');
                    }
                });

                // Keep the original row hidden but present in DOM
                $detailsRow.css('display', 'none');
            });

            // Modal close handler
            $('.modal-close').on('click', function() {
                $('#fee-details-modal').css('display', 'none');
                $('#modal-content-inner').empty();
            });

            // Close modal when clicking outside
            $(window).on('click', function(event) {
                if (event.target == $('#fee-details-modal')[0]) {
                    $('#fee-details-modal').css('display', 'none');
                    $('#modal-content-inner').empty();
                }
            });
        }

        loadFeesData();

        $('#search-button').on('click', function(e) {
            e.preventDefault();
            loadFeesData();
        });

        var timeoutId;
        $('#search-student-id, #search-student-name').on('keyup', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(loadFeesData, 500);
        });

        $('#search-year, #search-status').on('change', function() {
            loadFeesData();
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('fees_institute_dashboard', 'fees_institute_dashboard_shortcode');
?>