<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register REST endpoint for fetching student months
add_action('rest_api_init', function () {
    register_rest_route('fees/v1', '/get-student-months', array(
        'methods' => 'POST',
        'callback' => 'get_student_available_months',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

// Callback for REST endpoint to get available months
function get_student_available_months($request) {
    $student_post_id = intval($request->get_param('student_id'));
    if (!$student_post_id) {
        return new WP_Error('invalid_student', 'Invalid student ID', array('status' => 400));
    }

    $admission_date = get_post_meta($student_post_id, 'admission_date', true);
    if (!$admission_date) {
        $admission_date = date('Y-m'); // Default to current month if not set
    }

    $start_date = new DateTime($admission_date);
    $current_date = new DateTime(date('Y-m'));
    $end_date = (clone $current_date)->modify('+12 months');

    global $wpdb;
    $paid_months = $wpdb->get_col($wpdb->prepare(
        "SELECT month_year FROM {$wpdb->prefix}student_fees WHERE student_id = %s AND status = 'paid'",
        get_post_meta($student_post_id, 'student_id', true)
    ));

    $months = [];
    $interval = DateInterval::createFromDateString('1 month');
    $period = new DatePeriod($start_date, $interval, $end_date);

    foreach ($period as $dt) {
        $month_year = $dt->format('Y-m');
        if (!in_array($month_year, $paid_months)) {
            $months[] = [
                'value' => $month_year,
                'label' => $dt->format('F Y')
            ];
        }
    }

    return [
        'success' => true,
        'data' => [
            'months' => $months
        ]
    ];
}

function add_fees_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    
    if (empty($education_center_id)) {
        return '<p>No Educational Center found.</p>';
    }

    // Handle form submission
    if (isset($_POST['add_fee']) && wp_verify_nonce($_POST['nonce'], 'add_fee_nonce')) {
        $student_post_id = intval($_POST['student_id']);
        $template_id = intval($_POST['template_id']);
        $months = array_map('sanitize_text_field', (array)$_POST['months']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $cheque_number = $payment_method === 'cheque' ? sanitize_text_field($_POST['cheque_number']) : '';

        $student_id = get_post_meta($student_post_id, 'student_id', true);
        if (!$student_id) {
            return '<p>Error: Invalid student selected.</p>';
        }

        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fee_templates WHERE id = %d AND education_center_id = %d",
            $template_id, $education_center_id
        ));

        if (!$template) {
            return '<p>Error: Invalid fee template selected.</p>';
        }

        $success_count = 0;
        foreach ($months as $month) {
            $result = $wpdb->insert($wpdb->prefix . 'student_fees', [
                'student_id' => $student_id,
                'education_center_id' => $education_center_id,
                'template_id' => $template_id,
                'amount' => floatval($template->amount),
                'month_year' => $month,
                'status' => $payment_method === 'cheque' ? 'pending' : 'paid',
                'paid_date' => $payment_method === 'cheque' ? null : current_time('Y-m-d'),
                'payment_method' => $payment_method,
                'cheque_number' => $cheque_number,
            ], [
                '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s'
            ]);

            if ($result !== false) {
                $success_count++;
            }
        }

        wp_redirect(home_url('/institute-dashboard/fees/?section=fees-templates'));
        exit;
    }

    ob_start();
    ?>
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Add Fee</h3>
            </div>
            <div class="card-body">
                <form method="POST" id="add-fee-form">
                    <div class="accordion" id="feeAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#fee-details" aria-expanded="true" aria-controls="fee-details">
                                    Fee Details
                                </button>
                            </h2>
                            <div id="fee-details" class="accordion-collapse collapse show" data-bs-parent="#feeAccordion">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">Student:</label>
                                        <select name="student_id" id="student_id" class="student-select form-select" required>
                                            <option value="">Select a student</option>
                                            <?php
                                            $args = [
                                                'post_type' => 'students',
                                                'posts_per_page' => -1,
                                                'meta_query' => [
                                                    [
                                                        'key' => 'educational_center_id',
                                                        'value' => $education_center_id,
                                                        'compare' => '='
                                                    ]
                                                ]
                                            ];
                                            $student_query = new WP_Query($args);
                                            if ($student_query->have_posts()) {
                                                while ($student_query->have_posts()) {
                                                    $student_query->the_post();
                                                    $student_id = get_post_meta(get_the_ID(), 'student_id', true);
                                                    $student_name = get_the_title();
                                                    $display_text = $student_id ? "$student_id | $student_name" : $student_name;
                                                    echo '<option value="' . esc_attr(get_the_ID()) . '">' . esc_html($display_text) . '</option>';
                                                }
                                                wp_reset_postdata();
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="template_id" class="form-label">Fee Template:</label>
                                        <select name="template_id" id="template_id" class="form-select" required>
                                            <option value="">Select a template</option>
                                            <?php
                                            $templates = $wpdb->get_results($wpdb->prepare(
                                                "SELECT * FROM {$wpdb->prefix}fee_templates WHERE education_center_id = %d",
                                                $education_center_id
                                            ));
                                            foreach ($templates as $template) {
                                                echo '<option value="' . esc_attr($template->id) . '">' . 
                                                     esc_html($template->name) . ' ($' . number_format($template->amount, 2) . ')</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Months:</label>
                                        <div id="months-container" class="months-grid">
                                            <p class="text-muted">Please select a student to see available months.</p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method:</label>
                                        <select name="payment_method" id="payment_method" class="form-select" required>
                                            <option value="cod">Cash on Delivery</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="cash">Cash</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="cheque_number_wrapper" style="display:none;">
                                        <label for="cheque_number" class="form-label">Cheque Number:</label>
                                        <input type="text" name="cheque_number" id="cheque_number" class="form-control" placeholder="Enter cheque number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php wp_nonce_field('add_fee_nonce', 'nonce'); ?>
                    <button type="submit" name="add_fee" class="btn btn-primary mt-3">Add Fee</button>
                </form>
            </div>
        </div>
    </div>

   
    <script>
    jQuery(document).ready(function($) {
        // Initialize Select2
        $('.student-select').select2({
            placeholder: 'Search for a student',
            allowClear: true,
            width: '100%'
        });

        // Show/hide cheque number field
        $('#payment_method').on('change', function() {
            $('#cheque_number_wrapper').toggle(this.value === 'cheque');
        });

        // Fetch available months when student is selected
        $('#student_id').on('change', function() {
            var studentId = $(this).val();
            if (!studentId) {
                $('#months-container').html('<p class="text-muted">Please select a student to see available months.</p>');
                return;
            }

            $.ajax({
                url: '<?php echo esc_url(rest_url('fees/v1/get-student-months')); ?>',
                method: 'POST',
                data: {
                    student_id: studentId,
                    _wpnonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.months) {
                        var html = '';
                        $.each(response.data.months, function(index, month) {
                            html += '<label class="month-checkbox">' +
                                    '<input type="checkbox" name="months[]" value="' + month.value + '">' +
                                    '<span class="checkmark"></span>' +
                                    month.label +
                                    '</label>';
                        });
                        $('#months-container').html(html);
                    } else {
                        $('#months-container').html('<p class="text-muted">No available months found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#months-container').html('<p class="text-danger">Error fetching months. Please try again.</p>');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('add_fees_institute_dashboard', 'add_fees_institute_dashboard_shortcode');
?>