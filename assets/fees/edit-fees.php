<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register REST endpoints
add_action('rest_api_init', function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_fees';

    // REST endpoint for getting months
    register_rest_route('fees/v2', '/get-months', array(
        'methods' => 'POST',
        'callback' => function ($request) use ($table_name) {
            global $wpdb;
            $student_id = sanitize_text_field($request->get_param('student_id'));
            $education_center_id = sanitize_text_field($request->get_param('education_center_id'));

            $months = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT SUBSTRING(month_year, 1, 7) as month 
                 FROM $table_name 
                 WHERE student_id = %s 
                 AND education_center_id = %s 
                 ORDER BY month DESC",
                $student_id,
                $education_center_id
            ), ARRAY_A);

            if ($months) {
                foreach ($months as &$month) {
                    $month['display'] = date('F Y', strtotime($month['month'] . '-01'));
                }
                return rest_ensure_response(['success' => true, 'data' => $months]);
            }
            return rest_ensure_response(['success' => true, 'data' => []]);
        },
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    // REST endpoint for getting month-year records
    register_rest_route('fees/v2', '/get-month-years', array(
        'methods' => 'POST',
        'callback' => function ($request) use ($table_name) {
            global $wpdb;
            $student_id = sanitize_text_field($request->get_param('student_id'));
            $month = sanitize_text_field($request->get_param('month'));
            $education_center_id = sanitize_text_field($request->get_param('education_center_id'));

            $query = "SELECT sf.month_year, sf.status, ft.name AS template_name 
                      FROM $table_name sf 
                      LEFT JOIN {$wpdb->prefix}fee_templates ft ON sf.template_id = ft.id 
                      WHERE sf.student_id = %s 
                      AND sf.education_center_id = %s";
            $params = [$student_id, $education_center_id];

            if ($month) {
                $query .= " AND SUBSTRING(sf.month_year, 1, 7) = %s";
                $params[] = $month;
            }

            $query .= " ORDER BY sf.month_year DESC";
            $records = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

            if ($records) {
                return rest_ensure_response(['success' => true, 'data' => $records]);
            }
            return rest_ensure_response(['success' => true, 'data' => []]);
        },
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

// Register shortcode
add_shortcode('edit_fees_new', 'edit_fees_new_frontend_shortcode');
function edit_fees_new_frontend_shortcode($atts = []) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return '<p>Unable to identify user. Please log in again.</p>';
    }

    $educational_center_id = get_educational_center_data();
    if (!$educational_center_id) {
        return '<p>Unable to retrieve educational center information.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_fees';
    $output = '';

    // Display messages from submission
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'success';
        $output .= "<div>$message ($type)</div>";
    }

    // Get students
    $students = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT sf.student_id, 
                        (SELECT post_title FROM {$wpdb->posts} WHERE ID = 
                            (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'student_id' AND meta_value = sf.student_id LIMIT 1)) as student_name,
                        (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = 
                            (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'student_id' AND meta_value = sf.student_id LIMIT 1) 
                            AND meta_key = 'class') as class,
                        (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = 
                            (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'student_id' AND meta_value = sf.student_id LIMIT 1) 
                            AND meta_key = 'section') as section
         FROM $table_name sf
         WHERE sf.education_center_id = %s 
         ORDER BY student_name",
        $educational_center_id
    ));

    $selected_student = isset($_POST['select_student_id']) ? sanitize_text_field($_POST['select_student_id']) : '';
    $selected_month = isset($_POST['select_month']) ? sanitize_text_field($_POST['select_month']) : '';
    $selected_month_year = isset($_POST['select_month_year']) ? sanitize_text_field($_POST['select_month_year']) : '';

    // Get edit record if selected
    $edit_record = null;
    if ($selected_student && $selected_month_year) {
        $edit_record = $wpdb->get_row($wpdb->prepare(
            "SELECT sf.*, ft.name AS template_name 
             FROM $table_name sf 
             LEFT JOIN {$wpdb->prefix}fee_templates ft ON sf.template_id = ft.id 
             WHERE sf.student_id = %s 
             AND sf.month_year = %s 
             AND sf.education_center_id = %s",
            $selected_student,
            $selected_month_year,
            $educational_center_id
        ));
    }

    ob_start();
    ?>
    <div>
        <?php
        $active_section = 'update-fees';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');
        ?>
        <div>
            <?php echo $output; ?>

            <!-- Selection Form -->
            <form method="post" action="<?php echo esc_url(get_permalink()); ?>">
                <h2>Select Student, Month, and Record</h2>
                <div>
                    <label for="select_student_id">Select Student:</label>
                    <select name="select_student_id" id="select_student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $student) : ?>
                            <option value="<?php echo esc_attr($student->student_id); ?>" 
                                    <?php selected($selected_student, $student->student_id); ?>>
                                <?php echo esc_html("{$student->student_name} ({$student->student_id} - {$student->class} {$student->section})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="month_select_container" style="<?php echo $selected_student ? '' : 'display: none;'; ?>">
                    <label for="select_month">Filter by Month (Optional):</label>
                    <select name="select_month" id="select_month">
                        <option value="">-- All Months --</option>
                        <?php if ($selected_student) : ?>
                            <?php
                            $months = $wpdb->get_results($wpdb->prepare(
                                "SELECT DISTINCT SUBSTRING(month_year, 1, 7) as month 
                                 FROM $table_name 
                                 WHERE student_id = %s 
                                 AND education_center_id = %s 
                                 ORDER BY month DESC",
                                $selected_student,
                                $educational_center_id
                            ));
                            foreach ($months as $month) : ?>
                                <option value="<?php echo esc_attr($month->month); ?>" 
                                        <?php selected($selected_month, $month->month); ?>>
                                    <?php echo esc_html(date('F Y', strtotime($month->month . '-01'))); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div id="month_year_select_container" style="<?php echo $selected_student ? '' : 'display: none;'; ?>">
                    <label for="select_month_year">Select Fee Record:</label>
                    <select name="select_month_year" id="select_month_year">
                        <option value="">-- Select Record --</option>
                        <?php if ($selected_student) : ?>
                            <?php
                            $query = "SELECT sf.month_year, sf.status, ft.name AS template_name 
                                      FROM $table_name sf 
                                      LEFT JOIN {$wpdb->prefix}fee_templates ft ON sf.template_id = ft.id 
                                      WHERE sf.student_id = %s 
                                      AND sf.education_center_id = %s";
                            $params = [$selected_student, $educational_center_id];
                            if ($selected_month) {
                                $query .= " AND SUBSTRING(sf.month_year, 1, 7) = %s";
                                $params[] = $selected_month;
                            }
                            $query .= " ORDER BY sf.month_year DESC";
                            $records = $wpdb->get_results($wpdb->prepare($query, $params));
                            foreach ($records as $record) : ?>
                                <option value="<?php echo esc_attr($record->month_year); ?>" 
                                        <?php selected($selected_month_year, $record->month_year); ?>>
                                    <?php echo esc_html("{$record->month_year} ({$record->status} - {$record->template_name})"); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <input type="submit" name="select_fee_record" value="Load Record">
            </form>

            <!-- Edit Form -->
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="edit_fee_submit_new">
                <?php wp_nonce_field('edit_fee_submit_new', 'edit_fee_nonce'); ?>
                <h2><?php echo $edit_record ? 'Edit Fee Record' : 'No Record Selected'; ?></h2>
                <?php if ($edit_record) : ?>
                    <input type="hidden" name="fee_id" value="<?php echo esc_attr($edit_record->id); ?>">

                    <div>
                        <label for="edu_center_id">Education Center ID:</label>
                        <input type="text" name="edu_center_id" id="edu_center_id" 
                               value="<?php echo esc_attr($edit_record->education_center_id); ?>" 
                               readonly required>
                    </div>

                    <div>
                        <label for="student_id">Student ID:</label>
                        <input type="text" name="student_id" id="student_id" 
                               value="<?php echo esc_attr($edit_record->student_id); ?>" 
                               readonly required>
                    </div>

                    <div>
                        <label for="month_year">Month-Year:</label>
                        <input type="text" name="month_year" id="month_year" 
                               value="<?php echo esc_attr($edit_record->month_year); ?>" 
                               readonly required>
                    </div>

                    <div>
                        <label for="template_id">Fee Template:</label>
                        <select name="template_id" id="template_id" required>
                            <option value="">-- Select Template --</option>
                            <?php
                            $templates = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}fee_templates WHERE education_center_id = %s",
                                $educational_center_id
                            ));
                            foreach ($templates as $template) : ?>
                                <option value="<?php echo esc_attr($template->id); ?>" 
                                        <?php selected($edit_record->template_id, $template->id); ?>>
                                    <?php echo esc_html("{$template->name} (\${$template->amount})"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="amount">Amount:</label>
                        <input type="number" name="amount" id="amount" step="0.01" 
                               value="<?php echo esc_attr($edit_record->amount); ?>" required>
                    </div>

                    <div>
                        <label for="status">Status:</label>
                        <select name="status" id="status" required>
                            <option value="paid" <?php selected($edit_record->status, 'paid'); ?>>Paid</option>
                            <option value="pending" <?php selected($edit_record->status, 'pending'); ?>>Pending</option>
                            <option value="overdue" <?php selected($edit_record->status, 'overdue'); ?>>Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_method">Payment Method:</label>
                        <select name="payment_method" id="payment_method" required>
                            <option value="cod" <?php selected($edit_record->payment_method, 'cod'); ?>>Cash on Delivery</option>
                            <option value="cheque" <?php selected($edit_record->payment_method, 'cheque'); ?>>Cheque</option>
                            <option value="cash" <?php selected($edit_record->payment_method, 'cash'); ?>>Cash</option>
                        </select>
                    </div>

                    <div id="cheque_number_wrapper" style="<?php echo $edit_record && $edit_record->payment_method === 'cheque' ? '' : 'display: none;'; ?>">
                        <label for="cheque_number">Cheque Number:</label>
                        <input type="text" name="cheque_number" id="cheque_number" 
                               value="<?php echo esc_attr($edit_record->cheque_number ?? ''); ?>">
                    </div>

                    <div>
                        <label for="paid_date">Paid Date (if applicable):</label>
                        <input type="date" name="paid_date" id="paid_date" 
                               value="<?php echo esc_attr($edit_record->paid_date ?? ''); ?>">
                    </div>

                    <input type="submit" name="edit_fee_action" value="Update Fee Record">
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function loadMonthYears(studentId, month) {
            $.ajax({
                url: '<?php echo esc_url(rest_url('fees/v2/get-month-years')); ?>',
                type: 'POST',
                data: {
                    student_id: studentId,
                    month: month,
                    education_center_id: '<?php echo esc_js($educational_center_id); ?>',
                    _wpnonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var records = response.data;
                        var $monthYearSelect = $('#select_month_year');
                        $monthYearSelect.empty().append('<option value="">-- Select Record --</option>');
                        $.each(records, function(index, record) {
                            $monthYearSelect.append('<option value="' + record.month_year + '">' + record.month_year + ' (' + record.status + ' - ' + record.template_name + ')</option>');
                        });
                        $monthYearSelect.prop('required', true);
                        $('#month_year_select_container').show();
                    } else {
                        $('#month_year_select_container').html('<p>No records found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('REST Error (loadMonthYears):', status, error, xhr.responseText);
                    $('#month_year_select_container').html('<p>Error loading records.</p>');
                }
            });
        }

        $('#select_student_id').on('change', function() {
            var studentId = $(this).val();
            if (studentId) {
                $.ajax({
                    url: '<?php echo esc_url(rest_url('fees/v2/get-months')); ?>',
                    type: 'POST',
                    data: {
                        student_id: studentId,
                        education_center_id: '<?php echo esc_js($educational_center_id); ?>',
                        _wpnonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var months = response.data;
                            var $monthSelect = $('#select_month');
                            $monthSelect.empty().append('<option value="">-- All Months --</option>');
                            $.each(months, function(index, month) {
                                $monthSelect.append('<option value="' + month.month + '">' + month.display + '</option>');
                            });
                            $('#month_select_container').show();
                            loadMonthYears(studentId, '');
                        } else {
                            $('#month_select_container').html('<p>No months found.</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('REST Error (get_fee_months):', status, error, xhr.responseText);
                        $('#month_select_container').html('<p>Error loading months.</p>');
                    }
                });
            } else {
                $('#month_select_container, #month_year_select_container').hide();
                $('#select_month_year').prop('required', false);
            }
        });

        $('#select_month').on('change', function() {
            var studentId = $('#select_student_id').val();
            var month = $(this).val();
            if (studentId) {
                loadMonthYears(studentId, month);
            }
        });

        $('#payment_method').on('change', function() {
            $('#cheque_number_wrapper').toggle(this.value === 'cheque');
        });

        $('form.fees-select-form').on('submit', function(e) {
            var monthYear = $('#select_month_year').val();
            if (!monthYear) {
                e.preventDefault();
                alert('Please select a fee record to edit.');
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Handle admin-post submission
add_action('admin_post_edit_fee_submit_new', 'handle_edit_fee_submission_new');
function handle_edit_fee_submission_new() {
    if (!isset($_POST['edit_fee_nonce']) || !wp_verify_nonce($_POST['edit_fee_nonce'], 'edit_fee_submit_new')) {
        wp_die('Security check failed.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'student_fees';
    $message = '';
    $type = 'success';

    $data = [
        'template_id' => intval($_POST['template_id']),
        'amount' => floatval($_POST['amount']),
        'status' => sanitize_text_field($_POST['status']),
        'payment_method' => sanitize_text_field($_POST['payment_method']),
        'cheque_number' => $_POST['payment_method'] === 'cheque' ? sanitize_text_field($_POST['cheque_number']) : '',
        'paid_date' => $_POST['status'] === 'paid' && !empty($_POST['paid_date']) ? sanitize_text_field($_POST['paid_date']) : null,
    ];

    $educational_center_id = get_educational_center_data();
    if ($_POST['edu_center_id'] !== $educational_center_id) {
        $message = 'Invalid educational center ID.';
        $type = 'error';
    } else {
        $fee_id = intval($_POST['fee_id']);
        $result = $wpdb->update(
            $table_name,
            $data,
            ['id' => $fee_id],
            ['%d', '%f', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            $message = 'Update failed: ' . $wpdb->last_error;
            $type = 'error';
        } elseif ($result === 0) {
            $message = 'No changes made to fee record.';
            $type = 'error';
        } else {
            $message = 'Fee record updated successfully!';
        }
    }

    $redirect_url = add_query_arg(
        array('message' => urlencode($message), 'type' => $type),
        wp_get_referer() ?: home_url('/institute-dashboard/edit-fees/')
    );
    wp_safe_redirect($redirect_url);
    exit;
}
?>