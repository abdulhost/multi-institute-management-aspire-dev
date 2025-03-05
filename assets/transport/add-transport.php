<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_transport_fees_shortcode() {
    global $wpdb;
    $transport_fees_table = $wpdb->prefix . 'transport_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';
    $education_center_id = get_educational_center_data();
    $message = '';

    if (isset($_POST['add_transport_fee']) && check_admin_referer('add_transport_fee_nonce')) {
        $data = [
            'student_id' => sanitize_text_field($_POST['student_id']),
            'education_center_id' => $education_center_id,
            'template_id' => sanitize_text_field($_POST['template_id']),
            'amount' => floatval($_POST['amount']),
            'month_year' => sanitize_text_field($_POST['month_year']),
            'status' => sanitize_text_field($_POST['status']),
            'paid_date' => !empty($_POST['paid_date']) ? sanitize_text_field($_POST['paid_date']) : null,
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'cheque_number' => !empty($_POST['cheque_number']) ? sanitize_text_field($_POST['cheque_number']) : null,
        ];

        $inserted = $wpdb->insert($transport_fees_table, $data);
        $message = $inserted ? '<div class="alert alert-success">Fee added successfully!</div>' : '<div class="alert alert-danger">Error adding fee.</div>';
    }

    $templates = $wpdb->get_results("SELECT id, name, amount FROM $templates_table WHERE name LIKE 'transport_%'");

    ob_start();
    ?>
    <div class="container">
        <h2 class="mb-4">Add Transport Fee</h2>
        <?php echo $message; ?>
        <form method="post" class="row g-3">
            <?php wp_nonce_field('add_transport_fee_nonce'); ?>
            <div class="col-md-6">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" name="student_id" id="student_id" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="template_id" class="form-label">Template</label>
                <select name="template_id" id="template_id" class="form-select" required onchange="updateAmount(this)">
                    <option value="">Select Template</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo esc_attr($template->id); ?>" data-amount="<?php echo esc_attr($template->amount); ?>">
                            <?php echo esc_html($template->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="month_year" class="form-label">Month/Year (YYYY-MM)</label>
                <input type="text" name="month_year" id="month_year" class="form-control" placeholder="e.g., 2025-03" required>
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="paid_date" class="form-label">Paid Date</label>
                <input type="date" name="paid_date" id="paid_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-select">
                    <option value="">Select</option>
                    <option value="cod">COD</option>
                    <option value="cheque">Cheque</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="cheque_number" class="form-label">Cheque Number</label>
                <input type="text" name="cheque_number" id="cheque_number" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" name="add_transport_fee" class="btn btn-primary">Add Fee</button>
            </div>
        </form>
        <script>
            function updateAmount(select) {
                const amount = select.options[select.selectedIndex].getAttribute('data-amount');
                document.getElementById('amount').value = amount || '';
            }
        </script>
    </div>
    <?php
    return ob_get_clean();
}