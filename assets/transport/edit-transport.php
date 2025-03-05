<?php
if (!defined('ABSPATH')) {
    exit;
}

function edit_transport_fees_shortcode() {
    global $wpdb;
    $transport_fees_table = $wpdb->prefix . 'transport_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';
    $student_id = isset($_POST['student_id']) ? sanitize_text_field($_POST['student_id']) : '';
    $message = '';
    $fees = [];

    if (isset($_POST['update_transport_fees']) && check_admin_referer('update_transport_fees_nonce')) {
        $fee_ids = $_POST['fee_id'] ?? [];
        foreach ($fee_ids as $index => $fee_id) {
            $data = [
                'template_id' => sanitize_text_field($_POST['template_id'][$index]),
                'amount' => floatval($_POST['amount'][$index]),
                'status' => sanitize_text_field($_POST['status'][$index]),
                'paid_date' => !empty($_POST['paid_date'][$index]) ? sanitize_text_field($_POST['paid_date'][$index]) : null,
                'payment_method' => sanitize_text_field($_POST['payment_method'][$index]),
                'cheque_number' => !empty($_POST['cheque_number'][$index]) ? sanitize_text_field($_POST['cheque_number'][$index]) : null,
            ];
            $wpdb->update($transport_fees_table, $data, ['id' => intval($fee_id)]);
        }
        $message = '<div class="alert alert-success">Fees updated successfully!</div>';
    }

    if ($student_id) {
        $fees = $wpdb->get_results($wpdb->prepare(
            "SELECT tf.*, ft.name as template_name 
             FROM $transport_fees_table tf 
             LEFT JOIN $templates_table ft ON tf.template_id = ft.id 
             WHERE tf.student_id = %s 
             ORDER BY tf.month_year DESC",
            $student_id
        ));
    }

    $templates = $wpdb->get_results("SELECT id, name, amount FROM $templates_table WHERE name LIKE 'transport_%'");

    ob_start();
    ?>
    <div class="container">
        <h2 class="mb-4">Edit Transport Fees</h2>
        <?php echo $message; ?>
        <form method="post" class="mb-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" name="student_id" class="form-control" placeholder="Enter Student ID" value="<?php echo esc_attr($student_id); ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <?php if ($student_id && !empty($fees)): ?>
            <form method="post">
                <?php wp_nonce_field('update_transport_fees_nonce'); ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Month/Year</th>
                            <th>Template</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid Date</th>
                            <th>Payment Method</th>
                            <th>Cheque Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $index => $fee): ?>
                            <tr>
                                <td><?php echo esc_html($fee->month_year); ?><input type="hidden" name="fee_id[<?php echo $index; ?>]" value="<?php echo esc_attr($fee->id); ?>"></td>
                                <td>
                                    <select name="template_id[<?php echo $index; ?>]" class="form-select" onchange="updateAmount(this, <?php echo $index; ?>)">
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?php echo esc_attr($template->id); ?>" data-amount="<?php echo esc_attr($template->amount); ?>" <?php selected($fee->template_id, $template->id); ?>>
                                                <?php echo esc_html($template->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" name="amount[<?php echo $index; ?>]" class="form-control amount-input-<?php echo $index; ?>" value="<?php echo esc_attr($fee->amount); ?>" required></td>
                                <td>
                                    <select name="status[<?php echo $index; ?>]" class="form-select">
                                        <option value="pending" <?php selected($fee->status, 'pending'); ?>>Pending</option>
                                        <option value="paid" <?php selected($fee->status, 'paid'); ?>>Paid</option>
                                    </select>
                                </td>
                                <td><input type="date" name="paid_date[<?php echo $index; ?>]" class="form-control" value="<?php echo esc_attr($fee->paid_date); ?>"></td>
                                <td>
                                    <select name="payment_method[<?php echo $index; ?>]" class="form-select">
                                        <option value="">Select</option>
                                        <option value="cod" <?php selected($fee->payment_method, 'cod'); ?>>COD</option>
                                        <option value="cheque" <?php selected($fee->payment_method, 'cheque'); ?>>Cheque</option>
                                        <option value="cash" <?php selected($fee->payment_method, 'cash'); ?>>Cash</option>
                                    </select>
                                </td>
                                <td><input type="text" name="cheque_number[<?php echo $index; ?>]" class="form-control" value="<?php echo esc_attr($fee->cheque_number); ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_transport_fees" class="btn btn-primary">Update Fees</button>
            </form>
            <script>
                function updateAmount(select, index) {
                    const amount = select.options[select.selectedIndex].getAttribute('data-amount');
                    document.querySelector('.amount-input-' + index).value = amount;
                }
            </script>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}