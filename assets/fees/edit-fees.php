<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the shortcode
add_shortcode('edit_fees_frontend', 'edit_fees_frontend_shortcode');

function edit_fees_frontend_shortcode() {
    global $wpdb;
    
    // Check if user is logged in (optional, remove if not needed)
    if (!is_user_logged_in()) {
        return '<p>Please log in to manage student fees.</p>';
    }

    // Tables
    $student_fees_table = $wpdb->prefix . 'student_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';

    // Initialize variables
    $output = '';
    $student_id = '';
    $fee_records = [];
    $message = '';

    // Fetch all available templates for dropdown
    $templates = $wpdb->get_results("SELECT id, name, amount FROM $templates_table");

    // Handle form submission
    if (isset($_POST['submit_fee_update']) && check_admin_referer('update_fee_nonce')) {
        $student_id = sanitize_text_field($_POST['student_id']);
        $fee_ids = $_POST['fee_id'] ?? [];
        $template_ids = $_POST['template_id'] ?? [];
        $amounts = $_POST['amount'] ?? [];
        $statuses = $_POST['status'] ?? [];
        $paid_dates = $_POST['paid_date'] ?? [];
        $payment_methods = $_POST['payment_method'] ?? [];
        $cheque_numbers = $_POST['cheque_number'] ?? [];

        foreach ($fee_ids as $index => $fee_id) {
            $data = [
                'template_id' => sanitize_text_field($template_ids[$index]),
                'amount' => floatval($amounts[$index]),
                'status' => sanitize_text_field($statuses[$index]),
                'paid_date' => !empty($paid_dates[$index]) ? sanitize_text_field($paid_dates[$index]) : null,
                'payment_method' => sanitize_text_field($payment_methods[$index]),
                'cheque_number' => !empty($cheque_numbers[$index]) ? sanitize_text_field($cheque_numbers[$index]) : null,
            ];

            $where = ['id' => intval($fee_id)];
            $updated = $wpdb->update($student_fees_table, $data, $where);

            if ($updated !== false) {
                $message = '<div class="fee-update-success" style="color: green; margin-bottom: 15px;">Fees updated successfully!</div>';
            } else {
                $message = '<div class="fee-update-error" style="color: red; margin-bottom: 15px;">Error updating fees. Please try again.</div>';
            }
        }
    }

    // Get student ID from form or URL parameter
    if (isset($_POST['search_student'])) {
        $student_id = sanitize_text_field($_POST['student_id']);
    } elseif (isset($_GET['student_id'])) {
        $student_id = sanitize_text_field($_GET['student_id']);
    }

    // Fetch student data if student_id is provided
    if (!empty($student_id)) {
        $student = get_posts([
            'post_type' => 'students',
            'meta_key' => 'student_id', // ACF field
            'meta_value' => $student_id,
            'posts_per_page' => 1,
        ]);

        if ($student) {
            $student_name = $student[0]->post_title;
            $fee_records = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT sf.*, ft.name as template_name 
                    FROM $student_fees_table sf 
                    LEFT JOIN $templates_table ft ON sf.template_id = ft.id 
                    WHERE sf.student_id = %s 
                    ORDER BY sf.month_year DESC",
                    $student_id
                )
            );
        } else {
            $message = '<div class="fee-error" style="color: red; margin-bottom: 15px;">Student not found.</div>';
        }
    }

    // Start output buffering
    ob_start();
    ?>
    <div class="fee-management-frontend" style="max-width: 900px; margin: 20px auto; font-family: Arial, sans-serif;">
        <?php echo $message; ?>

        <!-- Student Search Form -->
        <form method="post" style="margin-bottom: 20px;">
            <label for="student_id" style="font-weight: bold;">Enter Student ID:</label>
            <input type="text" name="student_id" id="student_id" value="<?php echo esc_attr($student_id); ?>" 
                   style="padding: 5px; margin-right: 10px;" required>
            <input type="submit" name="search_student" value="Search" 
                   style="padding: 5px 15px; background: #0073aa; color: white; border: none; cursor: pointer;">
        </form>

        <?php if (!empty($fee_records)): ?>
            <h2 style="margin-bottom: 15px;">Fee Records for <?php echo esc_html($student_name); ?> (ID: <?php echo esc_html($student_id); ?>)</h2>
            <form method="post">
                <?php wp_nonce_field('update_fee_nonce'); ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f1f1f1;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Month/Year</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Template</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Amount</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Paid Date</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Payment Method</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Cheque Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fee_records as $index => $fee): ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->month_year); ?>
                                    <input type="hidden" name="fee_id[<?php echo $index; ?>]" value="<?php echo esc_attr($fee->id); ?>">
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <select name="template_id[<?php echo $index; ?>]" class="template-select" style="width: 100%;">
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?php echo esc_attr($template->id); ?>" 
                                                    data-amount="<?php echo esc_attr($template->amount); ?>" 
                                                    <?php selected($fee->template_id, $template->id); ?>>
                                                <?php echo esc_html($template->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <input type="number" step="0.01" name="amount[<?php echo $index; ?>]" 
                                           class="amount-input" value="<?php echo esc_attr($fee->amount); ?>" 
                                           style="width: 100%;" required>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <select name="status[<?php echo $index; ?>]" style="width: 100%;">
                                        <option value="pending" <?php selected($fee->status, 'pending'); ?>>Pending</option>
                                        <option value="paid" <?php selected($fee->status, 'paid'); ?>>Paid</option>
                                    </select>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <input type="date" name="paid_date[<?php echo $index; ?>]" 
                                           value="<?php echo esc_attr($fee->paid_date); ?>" style="width: 100%;">
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <select name="payment_method[<?php echo $index; ?>]" style="width: 100%;">
                                        <option value="">Select</option>
                                        <option value="cod" <?php selected($fee->payment_method, 'cod'); ?>>COD</option>
                                        <option value="cheque" <?php selected($fee->payment_method, 'cheque'); ?>>Cheque</option>
                                        <option value="cash" <?php selected($fee->payment_method, 'cash'); ?>>Cash</option>
                                    </select>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <input type="text" name="cheque_number[<?php echo $index; ?>]" 
                                           value="<?php echo esc_attr($fee->cheque_number); ?>" style="width: 100%;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="submit" name="submit_fee_update" value="Update Fees" 
                       style="padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer;">
            </form>

            <!-- JavaScript to update amount based on template selection -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const templateSelects = document.querySelectorAll('.template-select');
                    templateSelects.forEach(select => {
                        select.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const amount = selectedOption.getAttribute('data-amount');
                            const row = this.closest('tr');
                            const amountInput = row.querySelector('.amount-input');
                            amountInput.value = amount;
                        });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}