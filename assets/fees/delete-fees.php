<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the shortcode
add_shortcode('delete_fees_institute_dashboard', 'delete_fees_institute_dashboard_shortcode');

function delete_fees_institute_dashboard_shortcode() {
    global $wpdb;

    // Check if user is logged in (optional, adjust based on your needs)
    if (!is_user_logged_in()) {
        // wp_redirect(home_url('/login'));
        //     exit();
        return false;
    }

    // Tables
    $student_fees_table = $wpdb->prefix . 'student_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';

    // Initialize variables
    $student_id = '';
    $fee_records = [];
    $message = '';

    // Handle deletion
    if (isset($_POST['delete_fee']) && check_admin_referer('delete_fee_nonce')) {
        $student_id = sanitize_text_field($_POST['student_id']);
        $fee_ids = $_POST['fee_ids'] ?? [];

        if (!empty($fee_ids)) {
            $fee_ids = array_map('intval', $fee_ids); // Sanitize to integers
            $ids_placeholder = implode(',', array_fill(0, count($fee_ids), '%d'));
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $student_fees_table WHERE id IN ($ids_placeholder)",
                    ...$fee_ids
                )
            );

            if ($deleted !== false) {
                $message = '<div class="fee-delete-success" style="color: green; margin-bottom: 15px;">Selected fee records deleted successfully!</div>';
            } else {
                $message = '<div class="fee-delete-error" style="color: red; margin-bottom: 15px;">Error deleting fee records. Please try again.</div>';
            }
        } else {
            $message = '<div class="fee-delete-warning" style="color: orange; margin-bottom: 15px;">No records selected for deletion.</div>';
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
    <div class="fee-delete-dashboard" style="max-width: 800px; margin: 20px auto; font-family: Arial, sans-serif;">
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
            <form method="post" onsubmit="return confirm('Are you sure you want to delete the selected fee records? This action cannot be undone.');">
                <?php wp_nonce_field('delete_fee_nonce'); ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f1f1f1;">
                            <th style="padding: 10px; border: 1px solid #ddd;">
                                <input type="checkbox" id="select-all" title="Select All">
                            </th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Month/Year</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Template</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Amount</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Paid Date</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fee_records as $fee): ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                    <input type="checkbox" name="fee_ids[]" value="<?php echo esc_attr($fee->id); ?>" class="fee-checkbox">
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->month_year); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->template_name); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->amount); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->status); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->paid_date ? $fee->paid_date : '-'); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html($fee->payment_method ? $fee->payment_method : '-'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="submit" name="delete_fee" value="Delete Selected" 
                       style="padding: 10px 20px; background: #dc3232; color: white; border: none; cursor: pointer;">
            </form>

            <!-- JavaScript for select all and row highlighting -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const selectAll = document.getElementById('select-all');
                    const checkboxes = document.querySelectorAll('.fee-checkbox');

                    // Select All functionality
                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                            checkbox.closest('tr').style.backgroundColor = this.checked ? '#ffe6e6' : '';
                        });
                    });

                    // Individual checkbox highlighting
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            this.closest('tr').style.backgroundColor = this.checked ? '#ffe6e6' : '';
                        });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}