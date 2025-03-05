<?php
if (!defined('ABSPATH')) {
    exit;
}

function delete_transport_fees_shortcode() {
    global $wpdb;
    $transport_fees_table = $wpdb->prefix . 'transport_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';
    $student_id = isset($_POST['student_id']) ? sanitize_text_field($_POST['student_id']) : '';
    $message = '';
    $fees = [];

    if (isset($_POST['delete_transport_fees']) && check_admin_referer('delete_transport_fees_nonce')) {
        $fee_ids = $_POST['fee_ids'] ?? [];
        if (!empty($fee_ids)) {
            $fee_ids = array_map('intval', $fee_ids);
            $ids_placeholder = implode(',', array_fill(0, count($fee_ids), '%d'));
            $deleted = $wpdb->query($wpdb->prepare("DELETE FROM $transport_fees_table WHERE id IN ($ids_placeholder)", ...$fee_ids));
            $message = $deleted !== false ? '<div class="alert alert-success">Fees deleted successfully!</div>' : '<div class="alert alert-danger">Error deleting fees.</div>';
        } else {
            $message = '<div class="alert alert-warning">No fees selected.</div>';
        }
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

    ob_start();
    ?>
    <div class="container">
        <h2 class="mb-4">Delete Transport Fees</h2>
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
            <form method="post" onsubmit="return confirm('Are you sure you want to delete the selected fees?');">
                <?php wp_nonce_field('delete_transport_fees_nonce'); ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Month/Year</th>
                            <th>Template</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><input type="checkbox" name="fee_ids[]" value="<?php echo esc_attr($fee->id); ?>" class="fee-checkbox"></td>
                                <td><?php echo esc_html($fee->month_year); ?></td>
                                <td><?php echo esc_html($fee->template_name); ?></td>
                                <td><?php echo esc_html($fee->amount); ?></td>
                                <td><?php echo esc_html($fee->status); ?></td>
                                <td><?php echo esc_html($fee->paid_date ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="delete_transport_fees" class="btn btn-danger">Delete Selected</button>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const selectAll = document.getElementById('select-all');
                    const checkboxes = document.querySelectorAll('.fee-checkbox');
                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(cb => {
                            cb.checked = this.checked;
                            cb.closest('tr').classList.toggle('table-danger', this.checked);
                        });
                    });
                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', function() {
                            this.closest('tr').classList.toggle('table-danger', this.checked);
                        });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}